<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Pairing;
use Illuminate\Support\Facades\DB;

class DrawService
{
    /**
     * Realiza o sorteio do grupo usando algoritmo de Backtracking.
     *
     * @param  Group  $group  O grupo deve ter 'members' e 'exclusions' já carregados
     *                        (eager load) antes de chamar este método.
     * @param  int  $lastRound  Round do sorteio anterior (0 = primeiro sorteio).
     *                         Quando > 0, os pares desse round são usados como
     *                         exclusões temporárias para evitar repetição.
     *
     * CONTRATO DE CONCORRÊNCIA: Este método DEVE ser invocado dentro de uma
     * transação de banco de dados com o registro do grupo bloqueado via
     * lockForUpdate(). Sem esse lock, requisições concorrentes podem disparar
     * sorteios duplicados para o mesmo grupo.
     *
     * Exemplo de uso correto (ver GroupController::draw()):
     *   DB::transaction(function () use ($group, $drawService, $lastRound) {
     *       $lockedGroup = Group::whereKey($group->id)->lockForUpdate()->first();
     *       $drawService->draw($lockedGroup, $lastRound);
     *   });
     *
     * @throws \Exception Se for matematicamente impossível realizar o sorteio
     *                    com as restrições de exclusão configuradas.
     */
    public function draw(Group $group, int $lastRound = 0): bool
    {
        // 1. Prepara os dados
        $participants = $group->members->pluck('id')->toArray();

        // Mapa de exclusões permanentes (definidas pelo owner)
        $exclusionMap = [];
        foreach ($group->exclusions as $exclusion) {
            $exclusionMap[$exclusion->user_id][] = $exclusion->excluded_id;
        }

        // 2. Se for re-sorteio, adiciona pares do round anterior como exclusões temporárias
        //    para evitar que alguém tire a mesma pessoa do sorteio anterior.
        if ($lastRound > 0) {
            $historyExclusions = $this->buildHistoryExclusionMap($group, $lastRound);
            foreach ($historyExclusions as $santaId => $gifteeIds) {
                foreach ($gifteeIds as $gifteeId) {
                    // Adiciona ao mapa sem duplicar
                    if (! isset($exclusionMap[$santaId]) || ! in_array($gifteeId, $exclusionMap[$santaId])) {
                        $exclusionMap[$santaId][] = $gifteeId;
                    }
                }
            }
        }

        // 3. Executa Backtracking
        shuffle($participants);

        $matches    = [];
        $usedGiftees = [];

        if (! $this->backtrack(0, $participants, $usedGiftees, $matches, $exclusionMap)) {
            $suffix = $lastRound > 0
                ? ' Tente remover restrições ou aceitar repetições do sorteio anterior.'
                : '';
            throw new \Exception('Matematicamente impossível realizar o sorteio com as restrições atuais.' . $suffix);
        }

        // 4. O round actual é sempre o último + 1
        $currentRound = $lastRound + 1;

        // 5. Formata para salvar
        $pairsToInsert = [];
        foreach ($matches as $santaId => $gifteeId) {
            $pairsToInsert[] = [
                'group_id'   => $group->id,
                'santa_id'   => $santaId,
                'giftee_id'  => $gifteeId,
                'draw_round' => $currentRound,
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ];
        }

        // 6. Salva no banco
        $this->savePairingsBatch($group, $pairsToInsert, $currentRound);

        return true;
    }

    /**
     * Constrói o mapa de exclusões históricas a partir de um round específico.
     * Retorna: [santa_id => [giftee_id, ...]]
     */
    private function buildHistoryExclusionMap(Group $group, int $fromRound): array
    {
        $map = [];

        Pairing::where('group_id', $group->id)
            ->where('draw_round', $fromRound)
            ->get()
            ->each(function (Pairing $pairing) use (&$map) {
                $map[$pairing->santa_id][] = $pairing->giftee_id;
            });

        return $map;
    }

    private function backtrack(
        int $currentIndex,
        array $participants,
        array &$usedGiftees,
        array &$matches,
        array $exclusionMap
    ): bool {
        if ($currentIndex === count($participants)) {
            return true;
        }

        $currentSanta = $participants[$currentIndex];
        $candidates   = $participants;
        shuffle($candidates);

        foreach ($candidates as $candidate) {
            if (in_array($candidate, $usedGiftees)) {
                continue;
            }
            if ($candidate === $currentSanta) {
                continue;
            }
            if (isset($exclusionMap[$currentSanta]) && in_array($candidate, $exclusionMap[$currentSanta])) {
                continue;
            }

            $matches[$currentSanta] = $candidate;
            $usedGiftees[]          = $candidate;

            if ($this->backtrack($currentIndex + 1, $participants, $usedGiftees, $matches, $exclusionMap)) {
                return true;
            }

            unset($matches[$currentSanta]);
            array_pop($usedGiftees);
        }

        return false;
    }

    private function savePairingsBatch(Group $group, array $pairs, int $currentRound): void
    {
        DB::transaction(function () use ($group, $pairs, $currentRound) {
            // Remove apenas os pares do round corrente (segurança para re-tentativas)
            // Rounds anteriores ficam intactos como histórico.
            Pairing::where('group_id', $group->id)
                ->where('draw_round', $currentRound)
                ->delete();

            foreach ($pairs as $pair) {
                Pairing::create($pair);
            }

            // SEGURANÇA: forceFill() é intencional — is_drawn foi removido do $fillable
            // para bloquear mass assignment por usuários, mas aqui é definido pelo serviço
            // internamente. DB::raw('true') garante compatibilidade com PostgreSQL.
            $group->forceFill(['is_drawn' => DB::raw('true')])->save();
        });
    }
}
