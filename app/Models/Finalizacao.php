<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Finalizacao extends Model
{
    protected $fillable = [
        'processo_id',
        'anexo_atos_sessao',
        'anexo_proposta',
        'anexo_proposta_readequada',
        'anexo_habilitacao',
        'anexo_recurso_contratacoes',
        'anexo_planilha',
    ];

    public function processo()
    {
        return $this->belongsTo(Processo::class);
    }
}
