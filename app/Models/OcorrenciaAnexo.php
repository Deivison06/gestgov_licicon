<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OcorrenciaAnexo extends Model
{
    protected $table = 'ocorrencia_anexos';

    protected $fillable = [
        'ocorrencia_id',
        'categoria',
        'caminho',
        'nome_original',
    ];

    public function ocorrencia(): BelongsTo
    {
        return $this->belongsTo(Ocorrencia::class);
    }
}
