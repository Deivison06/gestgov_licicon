@extends('layouts.app')

@section('page-title', 'Atas de Contratação')
@section('page-subtitle', 'Gerenciar atas de registro de preços')

@section('content')
<div class="py-6">
    <div class="">

        <!-- Cabeçalho com Título e Botão Dashboard -->
        <div class="flex flex-col items-start justify-between mb-6 lg:flex-row lg:items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Atas de Contratação</h1>
                <p class="mt-1 text-sm text-gray-600">Gerencie e visualize atas de registro de preços dos processos</p>
            </div>
            <div class="mt-4 lg:mt-0">
                
            </div>
        </div>

        <!-- Card de Filtros -->
        <div class="mb-6">
            <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Filtrar Processos</h3>
                    <p class="mt-1 text-sm text-gray-500">Use os filtros abaixo para encontrar processos específicos</p>
                </div>

                <form method="GET" action="{{ route('admin.atas.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <!-- Prefeitura -->
                        <div>
                            <label for="prefeitura_id" class="block text-sm font-medium text-gray-700">
                                Prefeitura
                            </label>
                            <div class="mt-1">
                                <select name="prefeitura_id" id="prefeitura_id" 
                                        class="block w-full px-3 py-2 text-base bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="">Todas as Prefeituras</option>
                                    @foreach($prefeituras as $prefeitura)
                                        <option value="{{ $prefeitura->id }}" {{ $prefeituraId == $prefeitura->id ? 'selected' : '' }}>
                                            {{ $prefeitura->cidade }} - {{ $prefeitura->uf }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Processo -->
                        <div>
                            <label for="processo_id" class="block text-sm font-medium text-gray-700">
                                Processo
                            </label>
                            <div class="mt-1">
                                <select name="processo_id" id="processo_id" 
                                        class="block w-full px-3 py-2 text-base bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="">Todos os Processos</option>
                                    @foreach($processos as $proc)
                                        <option value="{{ $proc->id }}" {{ $processoId == $proc->id ? 'selected' : '' }}>
                                            {{ $proc->numero_processo }} - {!! Str::limit(strip_tags($proc->objeto), 100) !!}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex flex-wrap gap-3 pt-2">
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Aplicar Filtros
                        </button>
                        
                        <a href="{{ route('admin.atas.index') }}" 
                           class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Limpar Filtros
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Card de Resultados -->
        <div class="overflow-hidden bg-white border border-gray-200 rounded-lg shadow-sm">
            <!-- Header do Card -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex flex-col items-start justify-between md:flex-row md:items-center">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Processos Disponíveis</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ $processos->count() }} {{ Str::plural('processo', $processos->count()) }} encontrado{{ $processos->count() !== 1 ? 's' : '' }}
                        </p>
                    </div>
                    
                    @if($processos->count() > 0)
                    <div class="mt-2 md:mt-0">
                        <span class="inline-flex items-center px-3 py-1 text-sm font-medium text-green-800 bg-green-100 rounded-full">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ $processos->where('lotesContratados', '>', 0)->count() }} com contratações
                        </span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Conteúdo -->
            <div class="p-6">
                @if($processos->count() > 0)
                    <div class="overflow-x-auto -mx-6">
                        <div class="inline-block min-w-full align-middle">
                            <div class="overflow-hidden border-b border-gray-200">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                Processo
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                Prefeitura
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                Modalidade
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                Itens
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                Status
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                Ações
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($processos as $processo)
                                            @php
                                                $lotesContratados = $processo->lotesContratados->count();
                                                $totalLotes = $processo->lotes->count();
                                                $hasContratacoes = $lotesContratados > 0;
                                            @endphp
                                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                                <!-- Processo -->
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 w-10 h-10">
                                                            <div class="flex items-center justify-center w-10 h-10 rounded-lg {{ $hasContratacoes ? 'bg-blue-100' : 'bg-gray-100' }}">
                                                                <svg class="w-5 h-5 {{ $hasContratacoes ? 'text-blue-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                                </svg>
                                                            </div>
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900">
                                                                {{ $processo->numero_processo }}
                                                            </div>
                                                            <div class="text-sm text-gray-500 line-clamp-1 max-w-xs">
                                                                {!! Str::limit(strip_tags($processo->objeto), 40) !!}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>

                                                <!-- Prefeitura -->
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $processo->prefeitura->cidade }}</div>
                                                    <div class="text-sm text-gray-500">{{ $processo->prefeitura->uf }}</div>
                                                </td>

                                                <!-- Modalidade -->
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="inline-flex px-2.5 py-0.5 text-xs font-medium rounded-full 
                                                        @if($processo->modalidade->value === 'pregão') bg-blue-100 text-blue-800
                                                        @elseif($processo->modalidade->value === 'concorrência') bg-green-100 text-green-800
                                                        @elseif($processo->modalidade->value === 'dispensa') bg-purple-100 text-purple-800
                                                        @elseif($processo->modalidade->value === 'inexigibilidade') bg-pink-100 text-pink-800
                                                        @else bg-gray-100 text-gray-800 @endif">
                                                        {{ $processo->modalidade->getDisplayName() }}
                                                    </span>
                                                </td>

                                                <!-- Itens -->
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                        </svg>
                                                        <div>
                                                            <div class="text-sm font-medium text-gray-900">{{ $totalLotes }} lotes</div>
                                                            @if($hasContratacoes)
                                                                <div class="text-xs text-green-600">{{ $lotesContratados }} com contratação</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>

                                                <!-- Status -->
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($hasContratacoes)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                            </svg>
                                                            Com Contratações
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                            Disponível
                                                        </span>
                                                    @endif
                                                </td>

                                                <!-- Ações -->
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex space-x-2">
                                                        <a href="{{ route('admin.atas.show', $processo) }}" 
                                                           class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                            </svg>
                                                            Visualizar
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Estado Vazio -->
                    <div class="py-12 text-center">
                        <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">Nenhum processo encontrado</h3>
                        <p class="mt-2 text-sm text-gray-500">
                            Não foram encontrados processos para os filtros selecionados.
                        </p>
                        <div class="mt-6">
                            <a href="{{ route('admin.atas.index') }}" 
                               class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-700 bg-blue-100 border border-transparent rounded-md hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Limpar filtros e ver todos
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Carregar processos dinamicamente baseado na prefeitura selecionada
    const prefeituraSelect = document.getElementById('prefeitura_id');
    const processoSelect = document.getElementById('processo_id');
    
    if (prefeituraSelect && processoSelect) {
        prefeituraSelect.addEventListener('change', function() {
            const prefeituraId = this.value;
            
            if (prefeituraId) {
                // Limpar opções atuais
                while (processoSelect.options.length > 1) {
                    processoSelect.remove(1);
                }
                
                // Adicionar opção de carregamento
                const loadingOption = document.createElement('option');
                loadingOption.value = '';
                loadingOption.textContent = 'Carregando processos...';
                processoSelect.appendChild(loadingOption);
                
                // Fazer requisição AJAX
                fetch(`{{ route("admin.atas.processos-by-prefeitura") }}?prefeitura_id=${prefeituraId}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Erro na requisição');
                    return response.json();
                })
                .then(data => {
                    // Remover opção de carregamento
                    processoSelect.remove(processoSelect.options.length - 1);
                    
                    // Adicionar novos processos
                    if (data && data.length > 0) {
                        data.forEach(processo => {
                            const option = document.createElement('option');
                            option.value = processo.id;
                            const objetoTexto = processo.objeto ? processo.objeto.substring(0, 40) : '';
                            option.textContent = `${processo.numero_processo} - ${objetoTexto}${objetoTexto.length > 40 ? '...' : ''}`;
                            processoSelect.appendChild(option);
                        });
                    } else {
                        const option = document.createElement('option');
                        option.value = '';
                        option.textContent = 'Nenhum processo encontrado';
                        processoSelect.appendChild(option);
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar processos:', error);
                    processoSelect.remove(processoSelect.options.length - 1);
                    
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = 'Erro ao carregar processos';
                    processoSelect.appendChild(option);
                });
            } else {
                // Limpar opções se nenhuma prefeitura selecionada
                while (processoSelect.options.length > 1) {
                    processoSelect.remove(1);
                }
            }
        });
    }
    
    // Melhorar acessibilidade dos selects
    const selects = document.querySelectorAll('select');
    selects.forEach(select => {
        select.classList.add('focus:ring-2', 'focus:ring-blue-500', 'focus:border-blue-500');
    });
});
</script>

<style>
.line-clamp-1 {
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
}

/* Melhorar a aparência dos selects */
select:focus {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
</style>
@endpush
@endsection