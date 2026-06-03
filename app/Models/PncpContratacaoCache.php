<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PncpContratacaoCache extends Model
{
    protected $table = 'pncp_contratacoes_cache';

    protected $fillable = [
        'cnpj',
        'ano_compra',
        'sequencial_compra',
        'modalidade_codigo',
        'modalidade_nome',
        'objeto',
        'uf',
        'municipio',
        'orgao_nome',
        'codigo_situacao_compra',
        'situacao_nome',
        'valor_total_estimado',
        'valor_total_homologado',
        'data_publicacao_pncp',
        'data_resultado_compra',
        'synced_at',
    ];

    protected $casts = [
        'data_publicacao_pncp'  => 'date',
        'data_resultado_compra' => 'date',
        'synced_at'             => 'datetime',
    ];
}
