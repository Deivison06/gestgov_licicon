<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Para models com a coluna `data_finalizacao` (vencimento do contrato).
 *
 * Filtra por proximidade do vencimento usando a data atual como referência.
 * Reutilizado por Contrato (sistema) e ContratoManual (externos).
 */
trait FiltravelPorVencimento
{
    /**
     * @param  string|null  $filtro  'vencidos' | '30' | '60' | '90' | 'todos' | null
     */
    public function scopeVencimento(Builder $query, ?string $filtro): Builder
    {
        if (empty($filtro) || $filtro === 'todos') {
            return $query;
        }

        $hoje = now()->startOfDay();

        return match ($filtro) {
            'vencidos' => $query
                ->whereNotNull('data_finalizacao')
                ->where('data_finalizacao', '<', $hoje),
            '30', '60', '90' => $query
                ->whereNotNull('data_finalizacao')
                ->whereBetween('data_finalizacao', [$hoje, $hoje->copy()->addDays((int) $filtro)]),
            default => $query,
        };
    }
}
