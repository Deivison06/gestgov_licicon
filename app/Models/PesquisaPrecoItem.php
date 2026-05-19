<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesquisaPrecoItem extends Model
{
    protected $table = 'pesquisa_preco_itens';

    protected $fillable = [
        'processo_id',
        'numero_item',
        'ano_compra',
        'sequencial_compra',
        'orgao_cnpj',
        'orgao_nome',
        'uf',
        'municipio',
        'data_publicacao',
        'modalidade',
        'descricao',
        'quantidade',
        'unidade_medida',
        'valor_unitario',
        'tipo_valor',
        'valor_total',
        'fornecedor_nome',
        'fornecedor_cnpj',
        'link_pncp',
    ];

    protected $casts = [
        'data_publicacao' => 'date',
        'quantidade'      => 'float',
        'valor_unitario'  => 'float',
        'valor_total'     => 'float',
    ];

    public function processo(): BelongsTo
    {
        return $this->belongsTo(Processo::class);
    }
}
