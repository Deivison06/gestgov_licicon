@extends('layouts.app')

@section('page-title', 'Detalhes do Contrato Manual')
@section('page-subtitle', 'Informações completas do contrato')

@section('content')

    <div class="max-w-7xl mx-auto">
        {{-- Botão Voltar --}}
        <div class="mb-6">
            <a href="{{ route('admin.contratos.index', ['tipo' => 'manual']) }}"
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
                        <h2 class="text-2xl font-bold text-gray-900">Contrato Manual</h2>
                        <p class="mt-1 text-sm text-gray-600">ID: {{ $contrato->id }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        {{-- Botão Download --}}
                        @if($contrato->arquivo_contrato)
                            <a href="{{ asset($contrato->arquivo_contrato) }}"
                               target="_blank"
                               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                                <i class="fas fa-download mr-2"></i>
                                Download PDF
                            </a>
                        @endif

                        {{-- Botão Editar --}}
                        <a href="{{ route('admin.contratos.edit', $contrato->id) }}"
                           class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                            <i class="fas fa-edit mr-2"></i>
                            Editar
                        </a>
                    </div>
                </div>
            </div>

            {{-- Informações Gerais --}}
            <div class="p-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    {{-- Coluna 1: Dados do Contrato --}}
                    <div>
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">Informações do Contrato</h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Prefeitura</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $contrato->prefeitura->nome ?? '-' }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Número do Processo</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $contrato->numero_processo }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Número do Contrato</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $contrato->numero_contrato ?? '-' }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Modalidade</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    @if($contrato->modalidade)
                                        {{ $contrato->modalidade->getDisplayName() }}
                                    @else
                                        -
                                    @endif
                                </p>

                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Tipo de Contrato</label>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $contrato->tipo_contrato == 'Serviço' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ $contrato->tipo_contrato }}
                            </span>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Valor Total</label>
                                <p class="mt-1 text-lg font-bold text-gray-900">R$ {{ number_format($contrato->valor_total, 2, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Coluna 2: Vigência e Contratante --}}
                    <div>
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">Vigência e Contratante</h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Status de Vigência</label>
                                @php
                                    $statusColors = [
                                        'VIGENTE' => 'bg-green-100 text-green-800',
                                        'VENCIDO' => 'bg-red-100 text-red-800',
                                        'PENDENTE' => 'bg-yellow-100 text-yellow-800',
                                    ];
                                    $cor = $statusColors[$contrato->situacao] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $cor }}">
                                {{ $contrato->situacao }}
                            </span>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Data de Assinatura</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $contrato->data_assinatura ? $contrato->data_assinatura->format('d/m/Y') : '-' }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Data de Início</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $contrato->data_inicio ? $contrato->data_inicio->format('d/m/Y') : '-' }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Data de Finalização</label>
                                <p class="mt-1 text-sm font-gray-900">{{ $contrato->data_finalizacao ? $contrato->data_finalizacao->format('d/m/Y') : '-' }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Contratante (Secretaria)</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $contrato->secretaria->nome ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Objeto --}}
                <div class="mt-8">
                    <h3 class="mb-2 text-lg font-semibold text-gray-900">Objeto do Contrato</h3>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-gray-700">{{ $contrato->objeto }}</p>
                    </div>
                </div>

                {{-- Dados da Empresa --}}
                <div class="mt-8">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900">Dados da Empresa Contratada</h3>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Razão Social</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $contrato->empresa->razao_social ?? '-' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">CNPJ</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $contrato->empresa->cnpj_formatado ?? '-' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">Representante</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $contrato->empresa->representante ?? '-' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">Endereço</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $contrato->empresa->endereco ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
