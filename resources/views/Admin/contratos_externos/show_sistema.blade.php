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
                        {{-- Botão Download --}}
                        @if($processo->contrato)
                            <a href="{{ route('admin.processos.contrato.download', ['processo' => $processo->id]) }}"
                               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                                <i class="fas fa-download mr-2"></i>
                                Download Contrato
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Informações Gerais --}}
            <div class="p-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    {{-- Coluna 1: Dados do Processo --}}
                    <div>
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">Informações do Processo</h3>

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
                                <label class="block text-sm font-medium text-gray-500">Número do Procedimento</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $processo->numero_procedimento ?? '-' }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Modalidade</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $processo->modalidade->getDisplayName() }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Tipo de Procedimento</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $processo->tipo_procedimento_nome ?? '-' }}</p>
                            </div>

                            @if($processo->contrato)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Número do Contrato</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $processo->contrato->numero_contrato ?? '-' }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Data de Assinatura</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $processo->contrato->data_assinatura_contrato ? $processo->contrato->data_assinatura_contrato->format('d/m/Y') : '-' }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Coluna 2: Empresa Vencedora --}}
                    <div>
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">Empresa Vencedora</h3>

                        @if($processo->vencedores->isNotEmpty())
                            @foreach($processo->vencedores as $vencedor)
                                <div class="space-y-4 mb-6 p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">Razão Social</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $vencedor->razao_social ?? '-' }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">CPF/CNPJ</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $vencedor->cpf_cnpj_formatado ?? '-' }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">Valor Vencedor</label>
                                        <p class="mt-1 text-lg font-bold text-gray-900">R$ {{ number_format($vencedor->valor_vencedor, 2, ',', '.') }}</p>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-sm text-gray-500 italic">Nenhuma empresa vencedora registrada</p>
                        @endif
                    </div>
                </div>

                {{-- Objeto --}}
                <div class="mt-8">
                    <h3 class="mb-2 text-lg font-semibold text-gray-900">Objeto do Processo</h3>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-gray-700">{!! $processo->objeto ?? 'Não informado' !!}</p>
                    </div>
                </div>

                {{-- Vigência --}}
                <div class="mt-8">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900">Prazo de Vigência</h3>

                    @php
                        $vigencia = is_array($processo->detalhe->prazo_vigencia ?? null)
                            ? $processo->detalhe->prazo_vigencia
                            : ['12_meses'];

                        $outro_vigencia = $processo->detalhe->prazo_vigencia_outro ?? '________________.';

                        $objeto_continuado = strtolower($processo->detalhe->objeto_continuado ?? 'nao');

                        if (in_array('exercicio_financeiro', $vigencia)) {
                            $textoVigencia = "até 31/12 do exercício financeiro da contratação";
                        } elseif (in_array('12_meses', $vigencia)) {
                            $textoVigencia = "12 meses";
                        } elseif (in_array('outro', $vigencia)) {
                            $textoVigencia = $outro_vigencia;
                        } else {
                            $textoVigencia = "________________";
                        }
                    @endphp

                    <div class="p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                            Vigência
                        </span>
                            <p class="text-gray-700">{{ $textoVigencia }}</p>
                        </div>

                        @if($objeto_continuado == 'sim')
                            <div class="mt-2 flex items-center gap-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                            Prorrogação
                        </span>
                                <p class="text-gray-700">Admite prorrogação</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
