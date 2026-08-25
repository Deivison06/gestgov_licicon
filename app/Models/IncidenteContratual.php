<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidenteContratual extends Model
{
    protected $table = 'incidentes_contratuais';

    protected $fillable = [
        'contrato_id',
        'tipo',
        'categoria',
        'meses_prorrogacao',
        'percentual_valor',
        'justificativa',
        'status',
        'arquivo_solicitacao_path',
        'arquivo_orcamento_obra_path',
    ];

    public function contrato()
    {
        return $this->belongsTo(Contrato::class);
    }

    public function itens()
    {
        return $this->hasMany(IncidenteContratualItem::class, 'incidente_contratual_id');
    }
}
