<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtaRegistroPreco extends Model
{
    protected $table = 'atas_registro_preco';

    protected $fillable = [
        'processo_id',
        'homologacao_id',
        'vencedor_id',
        'numero_ata_registro_precos',
        'cargo_controle_interno',
        'data_selecionada',
        'assinantes',
        'caminho',
        'gerado_em',
        'invalidada_em',
    ];

    protected $casts = [
        'assinantes' => 'array',
        'data_selecionada' => 'date',
        'gerado_em' => 'datetime',
        'invalidada_em' => 'datetime',
    ];

    public function processo(): BelongsTo
    {
        return $this->belongsTo(Processo::class);
    }

    public function homologacao(): BelongsTo
    {
        return $this->belongsTo(Homologacao::class);
    }

    public function vencedor(): BelongsTo
    {
        return $this->belongsTo(Vencedor::class);
    }

    /**
     * Ata invalidada por desistência/abandono da assinatura pela empresa vencedora
     * (ver HomologacaoDesistencia). O arquivo original permanece disponível para
     * download, apenas sinalizado como sem validade na tela.
     */
    public function getIsInvalidadaAttribute(): bool
    {
        return $this->invalidada_em !== null;
    }
}
