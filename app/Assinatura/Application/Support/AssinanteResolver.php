<?php

namespace App\Assinatura\Application\Support;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Resolve a lista de "assinantes" enviada pelo frontend para o formato consumido
 * por SolicitacaoService::criarRodada().
 *
 * Aceita dois formatos:
 *   1) Novo (explícito):    [{ user_id: 5, ordem: 1 }, ...]
 *   2) Legado (Seleção UI): [{ responsavel: "João Silva", unidade_nome: "...", ... }, ...]
 *
 * No formato legado, tenta achar um User com `is_assinante=true` cujo `name` bata
 * (case-insensitive) com `responsavel`. Se não achar, o entry é descartado para a
 * rodada digital — o legado de carimbo continua funcionando normalmente.
 *
 * Antes era um trait (ResolveLegacyAssinantesTrait) acoplado via `use`. Agora é uma
 * classe injetável e testável isoladamente. O trait permanece como casca delegando
 * para esta classe, mantendo compatibilidade com quem ainda o utiliza.
 */
class AssinanteResolver
{
    /**
     * @param array  $entradaRequest Lista de entries (formato novo ou legado).
     * @param string $modo           'paralelo' | 'sequencial'
     * @return array Lista normalizada [['user_id'=>int,'ordem'=>int], ...]
     */
    public function resolverParaRodada(array $entradaRequest, string $modo = 'paralelo'): array
    {
        $resultado = [];
        $ordem = 0;

        foreach ($entradaRequest as $entry) {
            // Formato novo: já tem id explícito
            $userId = $entry['user_id'] ?? $entry['id'] ?? null;

            // Formato legado: tem `responsavel` (nome) — resolve para user
            if (!$userId && !empty($entry['responsavel'])) {
                $user = $this->resolverUserPorNome($entry['responsavel']);
                if ($user) {
                    $userId = $user->id;
                }
            }

            if (!$userId) {
                // Não tem como saber quem é — descarta (legado de carimbo segue OK)
                Log::info('Entry de assinante não resolvido para rodada digital', [
                    'entry' => $entry,
                ]);
                continue;
            }

            $resultado[] = [
                'user_id' => (int) $userId,
                'ordem'   => $modo === 'sequencial'
                    ? (int) ($entry['ordem'] ?? ++$ordem)
                    : 0,
            ];
        }

        return $resultado;
    }

    /**
     * Recebe os dados da request e devolve a lista normalizada pronta pro service.
     * Prioriza `rodada_assinantes` (formato novo) e cai pra `assinantes` (legado).
     */
    public function extrairDaRequest(array $requestData): array
    {
        $modo = $requestData['modo'] ?? 'paralelo';

        // Prioridade: rodada_assinantes (formato novo, explícito)
        $rodadaAssinantes = $requestData['rodada_assinantes'] ?? null;
        if (is_string($rodadaAssinantes)) {
            $rodadaAssinantes = json_decode($rodadaAssinantes, true);
        }
        if (!empty($rodadaAssinantes) && is_array($rodadaAssinantes)) {
            return $this->resolverParaRodada($rodadaAssinantes, $modo);
        }

        // Fallback: assinantes (formato legado da Seleção de Assinantes na UI)
        $assinantes = $requestData['assinantes'] ?? null;
        if (is_string($assinantes)) {
            $assinantes = json_decode($assinantes, true);
        }
        if (!empty($assinantes) && is_array($assinantes)) {
            return $this->resolverParaRodada($assinantes, $modo);
        }

        return [];
    }

    public function resolverUserPorNome(string $nome): ?User
    {
        $nome = trim($nome);
        if ($nome === '') {
            return null;
        }

        return User::query()
            ->where('is_assinante', true)
            ->whereRaw('LOWER(name) = ?', [Str::lower($nome)])
            ->first();
    }
}
