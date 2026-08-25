<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidenteContratualItem extends Model
{
    protected $table = 'incidente_contratual_itens';

    protected $fillable = [
        'incidente_contratual_id',
        'lote_contratado_id',
        'quantidade_aditivada',
        'valor_unitario',
        'valor_total_aditivado',
    ];

    public function incidente()
    {
        return $this->belongsTo(IncidenteContratual::class, 'incidente_contratual_id');
    }

    public function loteContratado()
    {
        return $this->belongsTo(LoteContratado::class, 'lote_contratado_id');
    }
}
