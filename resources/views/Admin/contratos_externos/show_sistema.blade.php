@extends('layouts.app')

@section('page-title', 'Detalhes do Contrato do Sistema')
@section('page-subtitle', 'Informações completas do contrato gerado automaticamente')

@section('content')

    <div class="max-w-7xl mx-auto">
        {{-- Botão Voltar --}}
        <div class="mb-6">
            <a href="{{ route('admin.contratos.index', ['tipo' => 'sistema']) }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                <i class="fas fa-arrow-left mr-2"></i>
                Voltar para Lista
            </a>
        </div>

        {{-- Cabeçalho --}}
        <div class="mb-6 bg-white rounded-lg shadow-sm">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Contrato do Sistema</h2>
                        <p class="mt-1 text-sm text-gray-600">Processo: {{ $processo->numero_processo }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        {{-- Botão Concluir --}}
                        @if($processo->contrato && $processo->contrato->situacao !== 'CONCLUÍDO')
                            <form action="{{ route('admin.contratos.concluir.sistema', $processo->id) }}" method="POST" class="inline">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700" onclick="return confirm('Deseja realmente concluir este contrato? Esta ação não poderá ser desfeita.')">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    Concluir Contrato
                                </button>
                            </form>
                        @endif
                        {{-- Botão Download --}}
                        @if($processo->contrato)
                            <a href="{{ route('admin.processos.contrato.download', ['processo' => $processo->id]) }}"
                               target="_blank"
                               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                                <i class="fas fa-download mr-2"></i>
                                Download PDF
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Informações Gerais --}}
            <div class="p-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    {{-- Coluna 1: Dados do Contrato e Processo --}}
                    <div>
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">Informações do Contrato</h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Prefeitura</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $processo->prefeitura->nome ?? '-' }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Número do Processo</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $processo->numero_processo }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Número do Contrato</label>
                                <p class="mt-1 text-sm text-gray-900">{{ optional($processo->contrato)->numero_contrato ?? '-' }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Modalidade</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ $processo->modalidade ? $processo->modalidade->getDisplayName() : '-' }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Tipo de Procedimento</label>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $processo->tipo_procedimento_nome ?? '-' }}
                                </span>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Valor Total Estimado do Processo</label>
                                <p class="mt-1 text-lg font-bold text-gray-900">R$ {{ number_format($processo->valor_total_vencedores ?? 0, 2, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Coluna 2: Vigência --}}
                    <div>
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">Vigência</h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Data de Assinatura</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ optional(optional($processo->contrato)->data_assinatura_contrato)->format('d/m/Y') ?? '-' }}
                                </p>
                            </div>

                            @if($processo->contrato)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Situação</label>
                                    @php
                                        $situacao = $processo->contrato->situacao;
                                        $cores = [
                                            'VIGENTE' => 'bg-green-100 text-green-800',
                                            'VENCIDO' => 'bg-red-100 text-red-800',
                                            'CONCLUÍDO' => 'bg-blue-100 text-blue-800',
                                            'PENDENTE' => 'bg-yellow-100 text-yellow-800'
                                        ];
                                        $cor = $cores[$situacao] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="mt-1 inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $cor }}">
                                        {{ $situacao }}
                                    </span>
                                </div>
                            @endif

                            @php
                                $vigencia = is_array($processo->detalhe->prazo_vigencia ?? null)
                                    ? $processo->detalhe->prazo_vigencia
                                    : ['12_meses'];

                                $outro_vigencia = $processo->detalhe->prazo_vigencia_outro ?? '________________.';
                                $objeto_continuado = strtolower($processo->detalhe->objeto_continuado ?? 'nao');

                                if (in_array('exercicio_financeiro', $vigencia)) {
                                    $textoVigencia = "Até 31/12 do exercício financeiro da contratação";
                                } elseif (in_array('12_meses', $vigencia)) {
                                    $textoVigencia = "12 meses";
                                } elseif (in_array('outro', $vigencia)) {
                                    $textoVigencia = $outro_vigencia;
                                } else {
                                    $textoVigencia = "________________";
                                }
                            @endphp

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Prazo de Vigência</label>
                                <span class="mt-1 inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                                    {{ $textoVigencia }}
                                </span>
                            </div>

                            @if($objeto_continuado == 'sim')
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Prorrogação</label>
                                <p class="mt-1 text-sm text-gray-900">Admite prorrogação (Objeto Continuado)</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Objeto --}}
                <div class="mt-8">
                    <h3 class="mb-2 text-lg font-semibold text-gray-900">Objeto do Contrato</h3>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-gray-700">{!! $processo->objeto ?? 'Não informado' !!}</p>
                    </div>
                </div>

                {{-- Dados da Empresa Vencedora --}}
                <div class="mt-8">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900">Dados das Empresas Vencedoras</h3>

                    @if($processo->vencedores->isNotEmpty())
                        <div class="grid grid-cols-1 gap-6">
                            @foreach($processo->vencedores as $vencedor)
                                <div class="p-5 border border-gray-200 rounded-lg shadow-sm bg-white">
                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Razão Social</label>
                                            <p class="mt-1 text-sm text-gray-900 font-medium">{{ $vencedor->razao_social ?? '-' }}</p>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">CPF/CNPJ</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ $vencedor->cpf_cnpj_formatado ?? ($vencedor->cpf_cnpj ?? ($vencedor->cnpj ?? '-')) }}</p>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Valor Contratado</label>
                                            <p class="mt-1 text-sm font-bold text-green-700">R$ {{ number_format($vencedor->valor_vencedor ?? $vencedor->valor_total, 2, ',', '.') }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-4 bg-yellow-50 text-yellow-800 rounded-lg border border-yellow-200">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Nenhuma empresa vencedora registrada para este processo.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
