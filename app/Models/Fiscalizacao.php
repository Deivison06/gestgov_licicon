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
        'designacao' => 'Recebi oficialmente o documento que me designa como fiscal do contrato',
        'acesso_contrato' => 'Tenho acesso ao contrato e aos documentos necessários para a fiscalização',
        'acesso_tr_pb' => 'Tenho acesso ao Termo de Referência ou Projeto Básico do contrato',
        'conhece_objeto' => 'Sei o que a empresa deve fornecer ou executar',
        'conhece_valores' => 'Sei quais são os valores previstos no contrato',
        'conhece_quantitativos' => 'Sei quais são as quantidades contratadas',
        'conhece_prazo' => 'Sei qual é o prazo previsto no contrato',
        'conhece_local_execucao' => 'Sei onde o serviço deve ser realizado ou onde o produto deve ser entregue',
        'identificou_preposto' => 'Sei quem é o representante da empresa responsável pelo acompanhamento do contrato (preposto)',
        'conhece_gestor' => 'Sei quem é o gestor responsável pelo contrato',
        'conhece_condicoes_pagamento' => 'Sei como e quando os pagamentos devem ser realizados',
        'conhece_penalidades' => 'Sei quais medidas podem ser aplicadas se a empresa não cumprir o contrato',
        'conhece_condicoes_recebimento' => 'Sei como os produtos ou serviços devem ser conferidos e recebidos',
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
