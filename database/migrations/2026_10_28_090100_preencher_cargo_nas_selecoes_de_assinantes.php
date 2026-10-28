<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Corrige seleções de assinantes gravadas depois que uma unidade do prefeito foi
 * renomeada para nome de órgão (ex.: "Prefeito Municipal" -> "Secretaria
 * Municipal"): o documento passou a mostrar o órgão embaixo da assinatura.
 *
 * Só grava `cargo` no assinante quando TODAS as condições valem — nos demais
 * casos o documento continua exatamente igual:
 *   - o assinante ainda não tem cargo;
 *   - a unidade é identificada (por unidade_id, ou por nome + responsável sem ambiguidade);
 *   - o unidade_nome gravado é o nome ATUAL da unidade (seleção feita após o rename);
 *   - a unidade tem cargo preenchido e diferente do nome.
 */
return new class extends Migration
{
    public function up(): void
    {
        $unidades = DB::table('unidades')->whereNotNull('cargo')->where('cargo', '!=', '')->get();
        if ($unidades->isEmpty()) {
            return;
        }

        $porId = $unidades->keyBy('id');
        $porNomeResponsavel = $unidades->groupBy(fn ($u) => $this->normalizar($u->nome).'|'.$this->normalizar($u->servidor_responsavel));

        DB::table('documento_selecao_assinantes')->orderBy('id')->chunkById(500, function ($selecoes) use ($porId, $porNomeResponsavel) {
            foreach ($selecoes as $selecao) {
                $assinantes = json_decode($selecao->assinantes, true);
                if (! is_array($assinantes)) {
                    continue;
                }

                $alterou = false;
                foreach ($assinantes as $i => $assinante) {
                    if (! is_array($assinante) || trim((string) ($assinante['cargo'] ?? '')) !== '') {
                        continue;
                    }

                    $unidade = ! empty($assinante['unidade_id']) ? ($porId[(int) $assinante['unidade_id']] ?? null) : null;
                    if (! $unidade) {
                        $candidatas = $porNomeResponsavel[$this->normalizar($assinante['unidade_nome'] ?? '').'|'.$this->normalizar($assinante['responsavel'] ?? '')] ?? collect();
                        $unidade = $candidatas->pluck('cargo')->unique()->count() === 1 ? $candidatas->first() : null;
                    }

                    if (! $unidade
                        || $this->normalizar($assinante['unidade_nome'] ?? '') !== $this->normalizar($unidade->nome)
                        || $this->normalizar($unidade->cargo) === $this->normalizar($unidade->nome)) {
                        continue;
                    }

                    $assinantes[$i]['cargo'] = $unidade->cargo;
                    $alterou = true;
                }

                if ($alterou) {
                    DB::table('documento_selecao_assinantes')->where('id', $selecao->id)
                        ->update(['assinantes' => json_encode($assinantes)]);
                }
            }
        });
    }

    public function down(): void
    {
        // Não desfaz: o cargo gravado corresponde ao que o documento deveria mostrar,
        // e remover a chave voltaria a exibir o nome do órgão na assinatura.
    }

    private function normalizar(?string $texto): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', (string) $texto)));
    }
};
