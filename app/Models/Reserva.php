<?php

// app/Models/Reserva.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    use HasFactory;

    protected $fillable = [
        'processo_id',
        'razao_social',
        'cnpj',
        'endereco',
        'telefone',
        'email',
        'representante_legal',
        'ordem'
    ];

    public function processo()
    {
        return $this->belongsTo(Processo::class);
    }
}
