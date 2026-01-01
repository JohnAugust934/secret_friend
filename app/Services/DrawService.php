<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Pairing;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DrawService
{
    /**
     * Realiza o sorteio em duas etapas:
     * 1. Encontra uma combinação válida na MEMÓRIA (rápido).
     * 2. Salva no BANCO DE DADOS em lote (uma única conexão).
     */
    public function draw(Group $group)
    {
        // 1. Carrega dados necessários para memória
        // Usamos 'values()' para garantir que as chaves do array sejam resetadas
        $members = $group->members->values();

        // Mapa de exclusões: [user_id => [id_bloqueado1, id_bloqueado2]]
        $exclusionMap = [];
        foreach ($group->exclusions as $exclusion) {
            $exclusionMap[$exclusion->user_id][] = $exclusion->excluded_id;
        }

        // 2. Tenta encontrar a combinação matemática (Loop na CPU, sem tocar no banco)
        $validPairs = null;
        $attempts = 0;
        $maxAttempts = 1000;

        while ($attempts < $maxAttempts) {
            $attempts++;
            try {
                $validPairs = $this->attemptDraw($members, $exclusionMap);
                break; // Sucesso! Sai do loop.
            } catch (\Exception $e) {
                continue; // Falhou, tenta de novo na memória.
            }
        }

        if (!$validPairs) {
            throw new \Exception("Não foi possível encontrar uma combinação válida com as restrições atuais. Tente remover alguns bloqueios.");
        }

        // 3. Salva no banco de dados (Apenas UMA vez)
        $this->savePairingsBatch($group, $validPairs);

        return true;
    }

    /**
     * Tenta gerar pares na memória.
     */
    private function attemptDraw(Collection $members, array $exclusionMap): array
    {
        $givers = $members->pluck('id')->toArray();
        $receivers = $members->pluck('id')->toArray();

        shuffle($receivers); // Embaralha

        $pairs = [];

        foreach ($givers as $index => $giverId) {
            $receiverId = $receivers[$index];

            // Regra 1: Não pode tirar a si mesmo
            if ($giverId === $receiverId) {
                throw new \Exception("Self match");
            }

            // Regra 2: Verificar exclusões
            if (isset($exclusionMap[$giverId]) && in_array($receiverId, $exclusionMap[$giverId])) {
                throw new \Exception("Exclusion match");
            }

            $pairs[] = [
                'group_id' => null, // Será preenchido no save
                'santa_id' => $giverId,
                'giftee_id' => $receiverId,
                'created_at' => now()->toDateTimeString(), // Formato string seguro para Postgres
                'updated_at' => now()->toDateTimeString(),
            ];
        }

        return $pairs;
    }

    /**
     * Salva usando Batch Insert (Inserção em Lote).
     */
    /**
     * Salva usando Batch Insert (Inserção em Lote).
     */
    private function savePairingsBatch(Group $group, array $pairs)
    {
        // Preenche o group_id em todos os pares
        foreach ($pairs as &$pair) {
            $pair['group_id'] = $group->id;
        }

        DB::transaction(function () use ($group, $pairs) {
            // 1. Limpa sorteios anteriores
            Pairing::where('group_id', $group->id)->delete();

            // 2. Insere tudo de uma vez
            if (count($pairs) > 0) {
                Pairing::insert($pairs);
            }

            // 3. Marca como sorteado (CORREÇÃO FINAL PARA POSTGRESQL)
            // Usamos DB::raw('true') para enviar o comando SQL puro, ignorando conversões do PHP
            $group->forceFill(['is_drawn' => DB::raw('true')])->save();
        });
    }
}
