<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Registro de desistência/abandono da assinatura da Ata de Registro de Preços
 * por uma empresa vencedora, dentro de uma Homologação específica.
 *
 * Não substitui nem apaga a AtaRegistroPreco (se já gerada) — apenas a marca
 * como invalidada (ver AtaRegistroPreco::invalidada_em) e zera o saldo dos
 * lotes daquele vencedor, preservando um snapshot dos valores anteriores em
 * `quantidade_lotes_snapshot` para auditoria.
 */
class HomologacaoDesistencia extends Model
{
    protected $table = 'homologacao_desistencias';

    protected $fillable = [
        'homologacao_id',
        'vencedor_id',
        'user_id',
        'data_solicitacao_assinatura',
        'data_decisao',
        'observacao',
        'quantidade_lotes_snapshot',
        'caminho_pdf',
        'gerado_em',
    ];

    protected $casts = [
        'data_solicitacao_assinatura' => 'date',
        'data_decisao' => 'date',
        'quantidade_lotes_snapshot' => 'array',
        'gerado_em' => 'datetime',
    ];

    public function homologacao(): BelongsTo
    {
        return $this->belongsTo(Homologacao::class);
    }

    public function vencedor(): BelongsTo
    {
        return $this->belongsTo(Vencedor::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function anexos(): HasMany
    {
        return $this->hasMany(HomologacaoDesistenciaAnexo::class);
    }
}
