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
    ];

    protected $casts = [
        'assinantes' => 'array',
        'data_selecionada' => 'date',
        'gerado_em' => 'datetime',
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
}
