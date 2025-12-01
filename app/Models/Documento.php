<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    use HasFactory;

    protected $table = 'documentos';

    protected $fillable = [
        'processo_id',  // Relacionamento com o processo
        'tipo_documento',
        'data_selecionada',
        'caminho',
        'gerado_em',
    ];

    // Relacionamento com Processo
    public function processo()
    {
        return $this->belongsTo(Processo::class);
    }
}
