<?php

namespace App\Services\Aditivos;

use App\Enums\FatorAtrasoEnum;
use App\Enums\LimiteLegalObraEnum;
use App\Enums\NaturezaAlteracaoAditivoEnum;
use App\Enums\ResponsabilidadeAtrasoEnum;
use App\Enums\SituacaoExecucaoContratualEnum;
use App\Enums\TipoObjetoContratoEnum;
use App\Models\Contrato;
use App\Models\ContratoManual;
use App\Models\IncidenteContratual;

class AditivoViewModelFactory
{
    public function __construct(
        private readonly AditivoCalculoFinanceiroService $calculoFinanceiro,
    ) {}

    public function build(IncidenteContratual $incidente, Contrato|ContratoManual $contrato, string $objetoContrato): AditivoViewModel
    {
        $tipo = $incidente->tipo;

        $resumoFinanceiro = $this->calculoFinanceiro->resumoFinanceiro($incidente, $contrato);

        return new AditivoViewModel(
            incidente: $incidente,
            tipo: $tipo,
            categoria: $incidente->categoria,
            naturezaAlteracao: $incidente->natureza_alteracao ? NaturezaAlteracaoAditivoEnum::from($incidente->natureza_alteracao) : null,
            tipoObjetoContrato: $incidente->tipo_objeto_contrato ? TipoObjetoContratoEnum::from($incidente->tipo_objeto_contrato) : null,
            contratoContinuo: $incidente->contrato_continuo,
            responsabilidadeAtraso: $incidente->responsabilidade_atraso ? ResponsabilidadeAtrasoEnum::from($incidente->responsabilidade_atraso) : null,
            limiteLegalObras: $incidente->limite_legal_aditivo_obras ? LimiteLegalObraEnum::from($incidente->limite_legal_aditivo_obras) : null,
            situacaoExecucao: $incidente->situacao_execucao_contratual ? SituacaoExecucaoContratualEnum::from($incidente->situacao_execucao_contratual) : null,
            fatoresAtraso: array_map(fn ($v) => FatorAtrasoEnum::from($v), $incidente->fatores_atraso ?? []),
            numeroOrdinal: $this->calculoFinanceiro->numeroOrdinal($incidente, $contrato),
            resumoFinanceiro: $resumoFinanceiro,
            temValorPeriodoProrrogado: (bool) $incidente->tem_valor_periodo_prorrogado,
            valorPeriodoProrrogado: $incidente->valor_periodo_prorrogado !== null ? (float) $incidente->valor_periodo_prorrogado : null,
            objetoContrato: mb_strtoupper(trim(html_entity_decode(strip_tags($objetoContrato ?? '')))),
            numeroContrato: $contrato->numero_contrato ?? 'S/N',
            dotacoes: $incidente->relationLoaded('dotacoes') ? $incidente->dotacoes : $incidente->dotacoes()->get(),
        );
    }
}
