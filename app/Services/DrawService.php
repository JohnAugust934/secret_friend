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
     *
     * CONTRATO DE CONCORRÊNCIA: Este método DEVE ser invocado dentro de uma
     * transação de banco de dados com o registro do grupo bloqueado via
     * lockForUpdate(). Sem esse lock, requisições concorrentes podem disparar
     * sorteios duplicados para o mesmo grupo.
     *
     * Exemplo de uso correto (ver GroupController::draw()):
     *   DB::transaction(function () use ($group, $drawService) {
     *       $lockedGroup = Group::whereKey($group->id)->lockForUpdate()->first();
     *       $drawService->draw($lockedGroup);
     *   });
     *
     * @throws \Exception Se for matematicamente impossível realizar o sorteio
     *                    com as restrições de exclusão configuradas.
     */
    public function draw(Group $group)
    {
        // 1. Prepara os dados
        $participants = $group->members->pluck('id')->toArray();

        $exclusionMap = [];
        foreach ($group->exclusions as $exclusion) {
            $exclusionMap[$exclusion->user_id][] = $exclusion->excluded_id;
        }

        // 2. Executa Backtracking
        shuffle($participants);

        $matches = [];
        $usedGiftees = [];

        if (! $this->backtrack(0, $participants, $usedGiftees, $matches, $exclusionMap)) {
            throw new \Exception('Matematicamente impossível realizar o sorteio com as restrições atuais.');
        }

        // 3. Formata para salvar
        $pairsToInsert = [];
        foreach ($matches as $santaId => $gifteeId) {
            $pairsToInsert[] = [
                'group_id' => $group->id,
                'santa_id' => $santaId,
                'giftee_id' => $gifteeId,
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ];
        }

        // 4. Salva no banco
        $this->savePairingsBatch($group, $pairsToInsert);

        return true;
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
        $candidates = $participants;
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
            $usedGiftees[] = $candidate;

            if ($this->backtrack($currentIndex + 1, $participants, $usedGiftees, $matches, $exclusionMap)) {
                return true;
            }

            unset($matches[$currentSanta]);
            array_pop($usedGiftees);
        }

        return false;
    }

    private function savePairingsBatch(Group $group, array $pairs)
    {
        DB::transaction(function () use ($group, $pairs) {
            Pairing::where('group_id', $group->id)->delete();

            if (count($pairs) > 0) {
                Pairing::insert($pairs);
            }

            // SEGURANÇA: forceFill() é intencional — is_drawn foi removido do $fillable
            // para bloquear mass assignment por usuários, mas aqui é definido pelo serviço
            // internamente. DB::raw('true') garante compatibilidade com PostgreSQL.
            $group->forceFill(['is_drawn' => DB::raw('true')])->save();
        });
    }
}
