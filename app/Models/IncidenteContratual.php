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
        'meses_prorrogacao',
        'percentual_valor',
        'justificativa',
        'status',
        'arquivo_solicitacao_path',
        'arquivo_orcamento_obra_path',
        'nome_solicitante',
        'cargo_solicitante',
        'nome_parecerista',
        'oab_parecerista'
    ];

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
