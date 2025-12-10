<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contrato extends Model
{
    protected $fillable = [
        'processo_id',
        'numero_contrato',
        'data_assinatura_contrato',
        'numero_extrato',
        'comarca',
        'fonte_recurso',
        'subcontratacao'
    ];

    public function processo()
    {
        return $this->belongsTo(Processo::class, 'processo_id');
    }
}
