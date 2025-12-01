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
        'anexo_publicacoes',

        'orgao_responsavel',
        'cnpj',
        'endereco',
        'responsavel',
        'cpf_responsavel',
        'razao_social',
        'cnpj_empresa_vencedora',
        'endereco_empresa_vencedora',
        'representante_legal_empresa',
        'cpf_representante',
        'valor_total',
    ];

    public function processo()
    {
        return $this->belongsTo(Processo::class, 'processo_id');
    }
}
