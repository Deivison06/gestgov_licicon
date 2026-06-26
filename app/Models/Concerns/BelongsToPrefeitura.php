<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Para models que possuem a coluna `prefeitura_id`.
 *
 * Substitui os `where('prefeitura_id', $id)` manuais (34 ocorrências) por um
 * escopo único e qualificado pela tabela (evita ambiguidade em joins):
 *
 *   User::daPrefeitura($id)->...
 */
trait BelongsToPrefeitura
{
    public function scopeDaPrefeitura(Builder $query, $prefeituraId): Builder
    {
        return $query->where($this->getTable() . '.prefeitura_id', $prefeituraId);
    }
}
