<?php

namespace App\Models;

use App\Enums\ConclusaoFiscalEnum;
use App\Enums\TipoFiscalizacaoEnum;
use App\Scopes\PrefeituraScope;
use Illuminate\Database\Eloquent\Model;

class Fiscalizacao extends Model
{
    protected $table = 'fiscalizacoes';

    protected $fillable = [
        'prefeitura_id',
        'fiscalizavel_id',
        'fiscalizavel_type',
        'tipo_contrato',
        'data_fiscalizacao',
        'numero_fiscalizacao',
        'pontualidade_prazos',
        'regularidade_fiscal_trabalhista',
        'comunicacao_atendimento',
        'irregularidade_observada',
        'recomendacoes_gestor',
        'recomendacoes_empresa',
        'conclusao_fiscal',
        'execucao_objeto',
        'qualidade_entregas',
        'observacoes_servidor',
        'metodologia_fiscalizacao',
        'relatorio_fotografico',
        'user_id',
    ];

    protected $casts = [
        'data_fiscalizacao' => 'date',
        'tipo_contrato' => TipoFiscalizacaoEnum::class,
        'conclusao_fiscal' => ConclusaoFiscalEnum::class,
    ];

    protected static function booted()
    {
        static::addGlobalScope(new PrefeituraScope());
    }

    // =========================================
    // Relacionamentos
    // =========================================

    /**
     * Contrato vinculado (polimórfico: Contrato ou ContratoManual)
     */
    public function fiscalizavel()
    {
        return $this->morphTo();
    }

    public function prefeitura()
    {
        return $this->belongsTo(Prefeitura::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // =========================================
    // Accessors
    // =========================================

    /**
     * Retorna o texto completo da conclusão fiscal
     */
    public function getConclusaoTextoAttribute(): string
    {
        return $this->conclusao_fiscal?->getTextoCompleto() ?? '—';
    }

    /**
     * Retorna a classe CSS do badge da conclusão
     */
    public function getConclusaoBadgeClassAttribute(): string
    {
        return $this->conclusao_fiscal?->getBadgeClass() ?? 'bg-gray-100 text-gray-800';
    }
}
