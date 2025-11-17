@extends('layouts.app')
@section('page-title', 'Gestão de Processos')
@section('page-subtitle', 'Gerencie todos os processos licitatórios de forma centralizada')

@section('content')
<div class="py-8">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">

        <!-- Botão Novo Processo -->
        <div class="flex justify-end mb-8">
            <a href="{{ route('admin.processos.create') }}"
                class="inline-flex items-center gap-3 px-6 py-3 text-sm font-semibold text-white transition-all duration-200 bg-gradient-to-r from-[#062F43] to-[#07405c] rounded-xl hover:from-[#07405c] hover:to-[#062F43] hover:shadow-lg hover:scale-105">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg> Novo Processo
            </a>
        </div>

        <!-- Mensagem de Sucesso -->
        @if (session('success'))
        <div class="p-4 mb-8 border border-green-200 shadow-sm rounded-2xl bg-gradient-to-r from-green-50 to-emerald-50">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 13l4 4L19 7"></path>
                </svg>
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        </div>
        @endif

        <!-- Botão Voltar -->
        @if(request('prefeitura_id'))
        <div class="mb-4">
            <a href="{{ route('admin.processos.index') }}"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-[#009496] rounded-lg hover:bg-[#007a7a] transition-all duration-200">
                ← Voltar para todas as prefeituras
            </a>
        </div>
        @endif

        <!-- Prefeituras Cards -->
        @if(!request('prefeitura_id'))
        <div class="mb-8">
            <h2 class="mb-4 text-xl font-semibold text-gray-800">Selecione uma Prefeitura</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($prefeituras as $prefeitura)
                <a href="{{ route('admin.processos.index', ['prefeitura_id' => $prefeitura->id]) }}"
                   class="prefeitura-card group relative p-6 bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-lg hover:border-[#009496] transition-all duration-300 transform hover:-translate-y-1 cursor-pointer block">
                    <div class="absolute transition-opacity duration-300 opacity-0 top-4 right-4 group-hover:opacity-100">
                        <svg class="w-5 h-5 text-[#009496]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                    <div class="flex items-center mb-3">
                        <div class="p-2 rounded-lg bg-[#009496]/10">
                            <svg class="w-6 h-6 text-[#009496]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-6 0H5m2 0h4M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                </path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-base font-semibold text-gray-800 group-hover:text-[#009496] transition-colors duration-300">
                        {{ $prefeitura->nome }}
                    </h3>
                    <p class="mt-1 text-xs text-gray-500">{{ $prefeitura->email }}</p>
                    <div class="pt-3 mt-3 border-t border-gray-100">
                        <span class="text-xs font-medium text-[#009496] bg-[#009496]/10 px-2 py-1 rounded-full">
                            {{ $prefeitura->processos_count }} processos
                        </span>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Tabela de Processos (mostrada apenas quando há filtro de prefeitura) -->
        @if(request('prefeitura_id'))
        <div class="overflow-hidden transition-all duration-300 bg-white border border-gray-100 shadow-sm rounded-2xl">
            <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
                <div class="flex flex-col items-start justify-between lg:flex-row lg:items-center">
                    <h3 class="text-xl font-semibold text-gray-800">
                        Processos da Prefeitura: {{ $prefeituras->find(request('prefeitura_id'))->nome ?? 'Selecionada' }}
                    </h3>
                    <span class="mt-2 text-sm text-gray-500 lg:mt-0">
                        Total: {{ $processos->total() }} processos
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                Modalidade
                            </th>
                            <th class="px-6 py-4 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                Nº Processo
                            </th>
                            <th class="px-6 py-4 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                Nº Procedimento
                            </th>
                            <th class="px-6 py-4 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                Tipo Contratação
                            </th>
                            <th class="px-6 py-4 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                Tipo Procedimento
                            </th>
                            <th class="px-6 py-4 text-xs font-medium tracking-wider text-center text-gray-500 uppercase">
                                Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($processos as $processo)
                        <tr class="transition-colors duration-200 hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <span class="inline-flex px-2.5 py-0.5 text-xs font-medium rounded-full
                                    @if ($processo->modalidade->value === 'dispensa') bg-purple-100 text-purple-800
                                    @elseif($processo->modalidade->value === 'inexigibilidade') bg-pink-100 text-pink-800
                                    @elseif($processo->modalidade->value === 'pregão') bg-blue-100 text-blue-800
                                    @elseif($processo->modalidade->value === 'concorrência') bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ $processo->modalidade->getDisplayName() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-mono text-sm text-gray-900">{{ $processo->numero_processo }}</td>
                            <td class="px-6 py-4 font-mono text-sm text-gray-900">{{ $processo->numero_procedimento }}</td>
                            <td class="px-6 py-4 font-mono text-sm text-gray-900">{{ $processo->tipo_contratacao_nome }}</td>
                            <td class="px-6 py-4 font-mono text-sm text-gray-900">{{ $processo->tipo_procedimento_nome }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center space-x-2">
                                    <a href="{{ route('admin.processos.edit', $processo->id) }}"
                                        class="p-2 text-gray-600 transition-colors duration-200 rounded-lg hover:bg-gray-100 hover:text-[#009496]"
                                        title="Editar processo">✏️</a>
                                    <a href="{{ route('admin.processos.iniciar', $processo->id) }}"
                                        class="p-2 text-white transition-colors duration-200 bg-[#062F43] rounded-lg hover:bg-[#065f8b]"
                                        title="Iniciar processo">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin-="round"
                                                stroke-width="2"
                                                d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z">
                                            </path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </a>
                                    <form action="{{ route('admin.processos.destroy', $processo->id) }}" method="POST"
                                        class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            onclick="return confirm('Tem certeza que deseja excluir este processo?')"
                                            class="p-2 text-gray-600 transition-colors duration-200 rounded-lg hover:bg-red-100 hover:text-red-600"
                                            title="Excluir processo">🗑️</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                    Nenhum processo encontrado para esta prefeitura
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($processos->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $processos->withQueryString()->links() }}
            </div>
            @endif
        </div>
        @endif

    </div>
</div>
@endsection
