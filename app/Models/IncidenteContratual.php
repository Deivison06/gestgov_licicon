<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidenteContratual extends Model
{
    protected $table = 'incidentes_contratuais';

    protected $fillable = [
        'contratavel_id',
        'contratavel_type',
        'tipo',
        'categoria',
        'origem',
        'data_aditivo',
        'data_finalizacao_base',
        'valor_total_base',
        'meses_prorrogacao',
        'percentual_valor',
        'justificativa',
        'status',
        'arquivo_solicitacao_path',
        'arquivo_orcamento_obra_path',
        'arquivo_aditivo_externo_path',
        'nome_solicitante',
        'cargo_solicitante',
        'nome_parecerista',
        'oab_parecerista'
    ];

    protected $casts = [
        'data_aditivo' => 'date',
        'data_finalizacao_base' => 'date',
        'valor_total_base' => 'decimal:2',
    ];

    public function ehExterno(): bool
    {
        return $this->origem === 'externo';
    }

    /**
     * O contrato ao qual este aditivo pertence — pode ser um Contrato do Sistema
     * (App\Models\Contrato) ou um Contrato Manual/Externo (App\Models\ContratoManual).
     * Mesmo padrão morphTo usado por Fiscalizacao/Ocorrencia ('fiscalizavel').
     */
    public function contratavel()
    {
        return $this->morphTo();
    }

    public function itens()
    {
        return $this->hasMany(IncidenteContratualItem::class, 'incidente_contratual_id');
    }
}
