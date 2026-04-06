@extends('layouts.app')
@section('page-title', 'Detalhes da Fiscalização')
@section('page-subtitle', 'Relatório de Fiscalização Nº ' . ($fiscalizacao->numero_fiscalizacao ?? $fiscalizacao->id))

@section('content')

@php
    $tipo = $fiscalizacao->tipo_contrato?->value;
    $info = $fiscalizacao->contrato_info;

    // Labels dinâmicas por tipo (mantidas da lógica anterior)
    $labelExecucao = match($tipo) {
        'compras' => 'Execução Física do Objeto',
        'servicos' => 'Execução do Objeto',
        'obras' => 'Execução do Objeto',
        default => 'Execução do Objeto',
    };
    $labelQualidade = match($tipo) {
        'compras' => 'Qualidade dos Produtos entregues',
        'servicos' => 'Qualidade dos serviços Prestados',
        'obras' => 'Qualidade dos serviços Executados',
        default => 'Qualidade das Entregas',
    };
@endphp

<div class="py-8">
    {{-- BARRA DE AÇÕES --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Fiscalização {{ $fiscalizacao->numero_fiscalizacao }}</h2>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            
            <a href="{{ route('admin.fiscalizacoes.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-gray-700 transition-colors bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0596A2] shadow-sm">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>

            <a href="{{ route('admin.fiscalizacoes.edit', $fiscalizacao->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-gray-700 transition-colors bg-gray-100 border border-gray-200 rounded-lg hover:bg-gray-200 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 shadow-sm">
                <i class="fas fa-edit text-amber-500"></i> Editar Dados
            </a>

            <div class="hidden md:block w-px h-8 bg-gray-300 mx-1"></div> {{-- Separador visual --}}

            <a href="{{ route('admin.fiscalizacoes.relatorio-tecnico', $fiscalizacao->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white transition-colors bg-[#009496] rounded-lg hover:bg-[#062F43] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#009496] shadow-sm" target="_blank">
                <i class="fas fa-file-contract"></i> Relatório Técnico
            </a>

            <a href="{{ route('admin.fiscalizacoes.notificacoes', $fiscalizacao->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white transition-colors bg-red-600 rounded-lg hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 shadow-sm" target="_blank">
                <i class="fas fa-exclamation-triangle"></i> Notificações e Recomendações
            </a>
            
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 space-y-6">

            <div class="bg-white border border-gray-100 shadow-sm rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-800">Dados do Relatório</h3>
                </div>
                <div class="p-6">
                    <dl class="space-y-4">
                        <div class="grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500 col-span-1">Resultado</dt>
                            <dd class="text-sm text-gray-900 col-span-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $fiscalizacao->conclusao_badge_class }}">
                                    {{ $fiscalizacao->conclusao_texto }}
                                </span>
                            </dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 border-t border-gray-50 pt-3">
                            <dt class="text-sm font-medium text-gray-500 col-span-1">Data</dt>
                            <dd class="text-sm text-gray-900 col-span-2">{{ $fiscalizacao->data_fiscalizacao?->format('d/m/Y') }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 border-t border-gray-50 pt-3">
                            <dt class="text-sm font-medium text-gray-500 col-span-1">Tipo</dt>
                            <dd class="text-sm text-gray-900 col-span-2">{{ $fiscalizacao->tipo_contrato?->getDisplayName() ?? 'N/I' }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 border-t border-gray-50 pt-3">
                            <dt class="text-sm font-medium text-gray-500 col-span-1">Fiscal</dt>
                            <dd class="text-sm text-gray-900 col-span-2 font-medium">{{ $fiscalizacao->user->name ?? 'Sistema' }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4 border-t border-gray-50 pt-3">
                            <dt class="text-sm font-medium text-gray-500 col-span-1">Criado em</dt>
                            <dd class="text-sm text-gray-900 col-span-2">{{ $fiscalizacao->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="bg-white border border-gray-100 shadow-sm rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-800">Contrato Vinculado</h3>
                </div>
                <div class="p-6">
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider">Empresa Contratada</dt>
                            <dd class="text-sm font-bold text-gray-900 mt-1">{{ $info['razao_social'] }}</dd>
                            <dd class="text-xs text-gray-500">{{ $info['cnpj'] }}</dd>
                        </div>
                        <div class="pt-3 border-t border-gray-50">
                            <dt class="text-xs font-medium text-gray-400 uppercase">Nº Contrato / Processo</dt>
                            <dd class="text-sm text-gray-700 mt-1">{{ $info['numero_contrato'] }} ({{ $info['numero_processo'] }})</dd>
                        </div>
                        <div class="pt-3 border-t border-gray-50">
                            <dt class="text-xs font-medium text-gray-400 uppercase">Secretaria</dt>
                            <dd class="text-sm text-gray-700 mt-1">{{ $info['secretaria'] }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-white border border-gray-100 shadow-sm rounded-2xl overflow-hidden min-h-full">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-800">Avaliação da Execução</h3>
                </div>

                <div class="p-8 space-y-8">
                    @php
                        $campos = [
                            ['icon' => 'fa-microscope', 'label' => 'Metodologia Aplicada', 'value' => $fiscalizacao->metodologia_fiscalizacao, 'show' => in_array($tipo, ['servicos', 'obras'])],
                            ['icon' => 'fa-box', 'label' => $labelExecucao, 'value' => $fiscalizacao->execucao_objeto, 'show' => true],
                            ['icon' => 'fa-star', 'label' => $labelQualidade, 'value' => $fiscalizacao->qualidade_entregas, 'show' => true],
                            ['icon' => 'fa-clock', 'label' => 'Pontualidade e Prazos', 'value' => $fiscalizacao->pontualidade_prazos, 'show' => true],
                            ['icon' => 'fa-file-invoice-dollar', 'label' => 'Regularidade Fiscal/Trabalhista', 'value' => $fiscalizacao->regularidade_fiscal_trabalhista, 'show' => true],
                            ['icon' => 'fa-comments', 'label' => 'Comunicação e Atendimento', 'value' => $fiscalizacao->comunicacao_atendimento, 'show' => true],
                            ['icon' => 'fa-exclamation-triangle', 'label' => 'Irregularidades Observadas', 'value' => $fiscalizacao->irregularidade_observada, 'show' => true],
                            ['icon' => 'fa-lightbulb', 'label' => 'Recomendações ao Gestor', 'value' => $fiscalizacao->recomendacoes_gestor, 'show' => true],
                            ['icon' => 'fa-building', 'label' => 'Recomendações à Empresa', 'value' => $fiscalizacao->recomendacoes_empresa, 'show' => true],
                        ];
                    @endphp

                    @foreach($campos as $campo)
                        @if($campo['show'] && $campo['value'])
                            <div class="relative pl-8">
                                <div class="absolute left-0 top-1 text-[#0596A2]">
                                    <i class="fas {{ $campo['icon'] }} text-lg"></i>
                                </div>
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ $campo['label'] }}</h4>
                                <div class="mt-2 text-sm text-gray-700 leading-relaxed bg-gray-50/50 p-4 rounded-xl border border-gray-50">
                                    {!! nl2br(e($campo['value'])) !!}
                                </div>
                            </div>
                        @endif
                    @endforeach

                    {{-- CONCLUSÃO FINAL --}}
                    <div class="mt-12 p-6 rounded-2xl border-2 {{ $fiscalizacao->conclusao_badge_class }} border-current bg-opacity-5">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center rounded-full bg-white shadow-sm text-xl font-bold">
                                {{ $fiscalizacao->conclusao_fiscal?->value }}
                            </div>
                            <div>
                                <h4 class="text-sm font-bold uppercase">Conclusão Final do Fiscal</h4>
                                <p class="mt-1 text-sm leading-relaxed font-medium">{{ $fiscalizacao->conclusao_texto }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection