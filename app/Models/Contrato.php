<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contrato extends Model
{
    protected $fillable = [
        'processo_id',
        'homologacao_id',
        'numero_sequencial',
        'numero_contrato',
        'data_assinatura_contrato',
        'numero_extrato',
        'comarca',
        'fonte_recurso',
        'subcontratacao',
        // Snapshot do contratante (sobrescreve os dados globais do processo só neste contrato)
        'dados_contratante',
        // Arquivo PDF próprio deste contrato
        'caminho',
        'data_documento',
        'gerado_em',
    ];

    protected $casts = [
        'data_assinatura_contrato' => 'datetime',
        'data_documento' => 'date',
        'gerado_em' => 'datetime',
        'dados_contratante' => 'array',
    ];

    public function processo(): BelongsTo
    {
        return $this->belongsTo(Processo::class, 'processo_id');
    }

    public function homologacao(): BelongsTo
    {
        return $this->belongsTo(Homologacao::class);
    }

    public function fiscalizacoes()
    {
        return $this->morphMany(Fiscalizacao::class, 'fiscalizavel');
    }
}
