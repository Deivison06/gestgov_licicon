<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    use HasFactory;

    protected $table = 'documentos';

    protected $fillable = [
        'processo_id',
        'tipo_documento',
        'data_selecionada',
        'caminho',
        'gerado_em',
        'valor_total',           // <-- ADICIONAR
        'quantidade_itens',      // <-- ADICIONAR
        'campos',                // JSON com campos do contrato
        'assinantes',            // JSON com assinantes
        'contratacoes_selecionadas' // JSON com IDs das contratações
    ];

    protected $casts = [
        'campos' => 'array',
        'assinantes' => 'array',
        'contratacoes_selecionadas' => 'array',
        'valor_total' => 'decimal:2',
        'gerado_em' => 'datetime'
    ];

    // Relacionamento com Processo
    public function processo()
    {
        return $this->belongsTo(Processo::class);
    }

    // Acessor para campos do contrato
    public function getNumeroContratoAttribute()
    {
        return $this->campos['numero_contrato'] ?? null;
    }

    public function getDataAssinaturaAttribute()
    {
        return $this->campos['data_assinatura_contrato'] ?? null;
    }

    public function getNumeroExtratoAttribute()
    {
        return $this->campos['numero_extrato'] ?? null;
    }

    public function getComarcaAttribute()
    {
        return $this->campos['comarca'] ?? null;
    }

    public function getFonteRecursoAttribute()
    {
        return $this->campos['fonte_recurso'] ?? null;
    }

    public function getSubcontratacaoAttribute()
    {
        return $this->campos['subcontratacao'] ?? null;
    }
}