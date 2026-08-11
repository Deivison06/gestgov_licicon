<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomologacaoDesistenciaAnexo extends Model
{
    protected $table = 'homologacao_desistencia_anexos';

    protected $fillable = [
        'homologacao_desistencia_id',
        'caminho',
        'nome_original',
    ];

    public function desistencia(): BelongsTo
    {
        return $this->belongsTo(HomologacaoDesistencia::class, 'homologacao_desistencia_id');
    }
}
