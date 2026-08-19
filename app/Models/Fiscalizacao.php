<?php

namespace App\Models;

use App\Enums\ConclusaoFiscalEnum;
use App\Enums\TipoFiscalizacaoEnum;
use App\Scopes\PrefeituraScope;
use Illuminate\Database\Eloquent\Model;

class Fiscalizacao extends Model
{
    protected $table = 'fiscalizacoes';

    /**
     * Itens fixos do checklist de verificação inicial (Compras/Serviços),
     * na ordem exigida pelo tribunal. Fonte única usada pelo formulário,
     * pelo show e pelos PDFs — evita repetir a lista em cada camada.
     */
    public const CHECKLIST_ITENS = [
        'designacao' => 'Recebi formalmente minha designação',
        'acesso_contrato' => 'Tenho acesso ao contrato',
        'acesso_tr_pb' => 'Tenho acesso ao Termo de Referência/Projeto Básico',
        'conhece_objeto' => 'Conheço o objeto',
        'conhece_valores' => 'Conheço os valores',
        'conhece_quantitativos' => 'Conheço os quantitativos',
        'conhece_prazo' => 'Conheço o prazo',
        'conhece_local_execucao' => 'Conheço o local de execução',
        'identificou_preposto' => 'Identifiquei o preposto da empresa',
        'conhece_gestor' => 'Conheço o gestor do contrato',
        'conhece_condicoes_pagamento' => 'Conheço as condições de pagamento',
        'conhece_penalidades' => 'Conheço as penalidades',
        'conhece_condicoes_recebimento' => 'Conheço as condições de recebimento',
    ];

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
        'houve_ocorrencia',
        'providencias_adotadas',
        'recomendacoes_gestor',
        'recomendacoes_empresa',
        'conclusao_fiscal',
        'execucao_objeto',
        'qualidade_entregas',
        'observacoes_servidor',
        'metodologia_fiscalizacao',
        'checklist_fiscalizacao',
        'relatorio_fotografico',
        'assinantes',
        'user_id',
    ];

    protected $casts = [
        'data_fiscalizacao' => 'date',
        'tipo_contrato' => TipoFiscalizacaoEnum::class,
        'conclusao_fiscal' => ConclusaoFiscalEnum::class,
        'assinantes' => 'array',
        'checklist_fiscalizacao' => 'array',
        'houve_ocorrencia' => 'boolean',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new PrefeituraScope);
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

    public function fotos()
    {
        return $this->hasMany(FiscalizacaoFoto::class)->orderBy('ordem')->orderBy('id');
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
