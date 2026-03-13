<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PcaItem extends Model
{
    use HasFactory;

    protected $table = 'pca_items';

    protected $fillable = [
        'pca_id',
        'unidade_requisitante_id',
        'modalidade',
        'descricao_classe_grupo',
        'valor_estimado',
        'grau_prioridade',
        'data_inicio_providencias',
        'data_desejada_conclusao',
        'prorrogacao_contrato',
    ];

    protected $casts = [
        'data_inicio_providencias' => 'date',
        'data_desejada_conclusao' => 'date',
        'prorrogacao_contrato' => 'boolean',
    ];

    public function pca()
    {
        return $this->belongsTo(Pca::class);
    }

    public function unidade()
    {
        return $this->belongsTo(Unidade::class, 'unidade_requisitante_id');
    }
}
