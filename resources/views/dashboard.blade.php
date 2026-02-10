@extends('layouts.app')

@section('title', 'Dashboard - GestGov')

@section('content')
    <div class="py-6">
        <!-- Cabeçalho com Filtro de Cidade -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">
                        @if($prefeitura)
                            Painel da GestGov - {{ $prefeitura->nome }}
                        @else
                            Painel Geral da GestGov
                        @endif
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">Visão geral dos processos - {{ now()->format('d/m/Y') }}</p>
                </div>

                <!-- Filtro de Cidade -->
                <div class="w-full md:w-64">
                    <form action="{{ route('admin.dashboard') }}" method="GET">
                        <select name="cidade" onchange="this.form.submit()"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#0596A2] focus:border-transparent">
                            <option value="">Todas as Cidades</option>
                            @foreach($prefeituras as $pref)
                                <option value="{{ $pref->id }}" {{ $prefeitura && $prefeitura->id == $pref->id ? 'selected' : '' }}>
                                    {{ $pref->nome }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
        </div>

        <!-- Cartões de Estatísticas de Processos -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Cartão Pregões -->
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-700">Pregões</h3>
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                        <i class="fas fa-gavel text-blue-600"></i>
                    </div>
                </div>
                <div class="text-center">
                    <p class="text-3xl font-bold text-gray-800 mb-1">{{ $pregioes }}</p>
                    <p class="text-sm text-gray-500">Total de pregões</p>
                </div>
            </div>

            <!-- Cartão Dispensas -->
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-700">Dispensas</h3>
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                        <i class="fas fa-file-signature text-green-600"></i>
                    </div>
                </div>
                <div class="text-center">
                    <p class="text-3xl font-bold text-gray-800 mb-1">{{ $dispensas }}</p>
                    <p class="text-sm text-gray-500">Total de dispensas</p>
                </div>
            </div>

            <!-- Cartão Inexigibilidades -->
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-700">Inexigibilidades</h3>
                    <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                        <i class="fas fa-ban text-purple-600"></i>
                    </div>
                </div>
                <div class="text-center">
                    <p class="text-3xl font-bold text-gray-800 mb-1">{{ $inexigibilidades }}</p>
                    <p class="text-sm text-gray-500">Total de inexigibilidades</p>
                </div>
            </div>
            <!-- Cartão CONCORRENCIA -->
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-700">CONCORRENCIAS</h3>
                    <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                        <i class="fas fa-ban text-purple-600"></i>
                    </div>
                </div>
                <div class="text-center">
                    <p class="text-3xl font-bold text-gray-800 mb-1">{{ $concorrencia }}</p>
                    <p class="text-sm text-gray-500">Total de concorrencias</p>
                </div>
            </div>

            <!-- Cartão Em Andamento -->
{{--            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">--}}
{{--                <div class="flex items-center justify-between mb-4">--}}
{{--                    <h3 class="text-lg font-medium text-gray-700">Em Andamento</h3>--}}
{{--                    <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center">--}}
{{--                        <i class="fas fa-spinner text-yellow-600"></i>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="text-center">--}}
{{--                    <p class="text-3xl font-bold text-gray-800 mb-1">{{ $emAndamento }}</p>--}}
{{--                    <p class="text-sm text-gray-500">Processos em análise</p>--}}
{{--                </div>--}}
{{--            </div>--}}

{{--            <!-- Cartão Concluídos -->--}}
{{--            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">--}}
{{--                <div class="flex items-center justify-between mb-4">--}}
{{--                    <h3 class="text-lg font-medium text-gray-700">Concluídos</h3>--}}
{{--                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">--}}
{{--                        <i class="fas fa-check-circle text-green-600"></i>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="text-center">--}}
{{--                    <p class="text-3xl font-bold text-gray-800 mb-1">{{ $concluido }}</p>--}}
{{--                    <p class="text-sm text-gray-500">Processos aprovados</p>--}}
{{--                </div>--}}
{{--            </div>--}}
        </div>

        <!-- Lista de Processos -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-700">
                        @if($prefeitura)
                            Processos - {{ $prefeitura->nome }}
                        @else
                            Processos Recentes
                        @endif
                    </h3>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-500">Total: {{ $processos->count() }}</span>
                        @if(!$prefeitura)
                            <i class="fas fa-building text-gray-400"></i>
                        @endif
                    </div>
                </div>
            </div>

            <div class="divide-y divide-gray-100">
                @forelse ($processos->sortByDesc('created_at')->take(10) as $processo)
                    <div class="px-6 py-4 transition-colors hover:bg-gray-50">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <p class="font-medium text-gray-800">
                                        Processo #{{ $processo->numero_processo }}
                                    </p>

                                    <!-- Badge da Modalidade -->
                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                {{ $processo->modalidade->value == 4 ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $processo->modalidade->value == 2 ? 'bg-green-100 text-green-800' : '' }}
                                {{ $processo->modalidade->value == 3 ? 'bg-purple-100 text-purple-800' : '' }}">
                                {{ $processo->modalidade->getDisplayName() }}
                            </span>

                                    <!-- Badge do Status -->
                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                {{ $processo->status->value == 'analise' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $processo->status->value == 'aprovado' ? 'bg-green-100 text-green-800' : '' }}">
                                {{ $processo->status->label() }}
                            </span>
                                </div>
                                <div class="text-sm text-gray-600 space-y-1">
                                    <p>
                                        <i class="fas fa-building mr-2 text-gray-400"></i>
                                        {{ $processo->prefeitura->nome ?? 'Prefeitura não vinculada' }}
                                    </p>
                                    <p>
                                        <i class="fas fa-tag mr-2 text-gray-400"></i>
                                        Objeto: {!! strip_tags($processo->objeto) !!}
                                    </p>
                                    <div class="flex items-center gap-4 mt-2">
                                <span class="flex items-center text-xs text-gray-500">
                                    <i class="far fa-clock mr-1"></i>
                                    Criado: {{ $processo->created_at->format('d/m/Y') }}
                                </span>
                                    </div>
                                </div>
                            </div>
                            <div class="md:text-right">
                                <a href="{{ route('admin.processos.iniciar', $processo->id) }}"
                                   class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-[#0596A2] rounded-lg hover:bg-[#047a85] transition-colors">
                                    <i class="fas fa-eye mr-2"></i>
                                    Ver Detalhes
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                            <i class="fas fa-clipboard-list text-gray-400 text-2xl"></i>
                        </div>
                        <p class="text-gray-500 mb-2">Nenhum processo encontrado.</p>
                    </div>
                @endforelse
            </div>

            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-500">
                        Mostrando {{ min(10, $processos->count()) }} de {{ $processos->count() }} processos
                    </div>
                    <a href="{{ route('admin.processos.index') }}"
                       class="inline-flex items-center text-sm font-medium text-[#0596A2] hover:text-[#047a85] transition-colors">
                        Ver todos os processos
                        <i class="ml-2 text-xs fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Estilo simples para animação dos cards -->
    <style>
        .bg-white {
            animation: fadeInUp 0.5s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endsection
