<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FiscalizacaoFoto extends Model
{
    protected $table = 'fiscalizacao_fotos';

    protected $fillable = [
        'fiscalizacao_id',
        'caminho',
        'legenda',
        'ordem',
    ];

    public function fiscalizacao(): BelongsTo
    {
        return $this->belongsTo(Fiscalizacao::class);
    }
}
