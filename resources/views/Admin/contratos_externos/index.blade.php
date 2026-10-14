@extends('layouts.app')

@section('page-title', 'Gestão de Contratos')
@section('page-subtitle', 'Gerencie os contratos do sistema e manuais')

@section('content')

    {{-- Mensagem de Sucesso --}}
    @if(session('success'))
        <div class="flex items-center p-4 mb-6 text-[#2DC197] border border-[#184e40] rounded-lg bg-[#0d2a23]">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Alerta de Contratos Vencidos --}}
    @if($vencidosCount > 0)
        <div x-data="{ open: true }" x-show="open" class="flex items-center justify-between p-4 mb-6 text-[#ff7b72] border border-[#5a2328] rounded-lg bg-[#2a1315]">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0 text-[#ff7b72]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                <span class="font-medium text-[#e6f0ee]">
                    Você tem <strong class="text-[#ff7b72]">{{ $vencidosCount }}</strong> {{ $vencidosCount === 1 ? 'contrato vencido' : 'contratos vencidos' }}.
                </span>
                <a href="{{ route('admin.contratos.index', ['tipo' => 'manual', 'situacao' => 'vencido']) }}"
                   class="inline-flex items-center gap-1 text-sm font-semibold text-[#ff7b72] underline underline-offset-2 hover:text-[#ffa198] transition-colors">
                    Clique aqui para visualizá-los
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
            <button @click="open = false" class="text-[#7ea29c] hover:text-[#e6f0ee] transition-colors" title="Fechar">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif

    {{-- Abas de Navegação --}}
    <div class="mb-6 bg-[#10201e] border border-[#24403c] shadow-sm rounded-2xl overflow-hidden">
        <div class="border-b border-[#24403c] bg-[#0a1514]">
            <nav class="flex -mb-px" aria-label="Tabs">
                @php
                    // Prepara os parâmetros para as abas
                    $paramsManual = array_merge(request()->except(['tipo', 'page']), ['tipo' => 'manual']);
                    $paramsSistema = array_merge(request()->except(['tipo', 'page']), ['tipo' => 'sistema']);
                @endphp

                <a href="{{ route('admin.contratos.index', $paramsManual) }}"
                   class="{{ $abaAtiva === 'manual' ? 'border-[#2DC197] text-[#2DC197] font-semibold' : 'border-transparent text-[#7ea29c] hover:text-[#e6f0ee]' }} flex-1 py-4 px-6 text-center border-b-2 font-medium text-sm transition-colors duration-200">
                    <div class="flex items-center justify-center gap-2">
                        <i class="fas fa-file-signature"></i>
                        <span>Contratos Manuais</span>
                        @if($tipoContratos === 'manual')
                            <span class="bg-[#2DC197]/20 text-[#2DC197] border border-[#2DC197]/30 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                        {{ $contratos->total() }}
                    </span>
                        @endif
                    </div>
                </a>
                <a href="{{ route('admin.contratos.index', $paramsSistema) }}"
                   class="{{ $abaAtiva === 'sistema' ? 'border-[#2DC197] text-[#2DC197] font-semibold' : 'border-transparent text-[#7ea29c] hover:text-[#e6f0ee]' }} flex-1 py-4 px-6 text-center border-b-2 font-medium text-sm transition-colors duration-200">
                    <div class="flex items-center justify-center gap-2">
                        <i class="fas fa-cogs"></i>
                        <span>Contratos do Sistema</span>
                        @if($tipoContratos === 'sistema')
                            <span class="bg-[#2DC197]/20 text-[#2DC197] border border-[#2DC197]/30 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                        {{ $contratos->total() }}
                    </span>
                        @endif
                    </div>
                </a>
            </nav>
        </div>

        {{-- Conteúdo das Abas --}}
        <div class="p-6">
            {{-- Filtros (diferentes por aba) --}}
            <div class="mb-6">
                <h3 class="mb-4 text-lg font-semibold text-[#e6f0ee]">Filtrar Contratos</h3>
                <form method="GET" action="{{ route('admin.contratos.index') }}" class="flex flex-col gap-4">
                    <input type="hidden" name="tipo" value="{{ $abaAtiva }}">

                    {{-- Campo de Pesquisa Livre --}}
                    <div class="mb-4">
                        <label for="search" class="block mb-2 text-sm font-medium text-[#a2b9b5]">
                            <i class="fas fa-search mr-1 text-[#2DC197]"></i> Pesquisa Livre
                        </label>
                        <div class="relative">
                            <input type="text"
                                   name="search"
                                   id="search"
                                   value="{{ request('search') }}"
                                   placeholder="Digite para pesquisar por processo, contrato, empresa, objeto..."
                                   class="w-full px-4 py-2 pl-10 transition-colors duration-200 bg-[#0a1514] border border-[#24403c] text-[#e6f0ee] rounded-lg focus:ring-[#2DC197] focus:border-[#2DC197]">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <i class="fas fa-search text-[#7ea29c]"></i>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-[#7ea29c]">
                            Pesquise por qualquer informação relacionada ao contrato
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                        {{-- Filtro por Prefeitura (apenas para admin/diretor) --}}
                        <div>
                            <label for="prefeitura_id" class="block mb-1 text-sm font-medium text-[#a2b9b5]">
                                Prefeitura
                            </label>
                            <select name="prefeitura_id" id="prefeitura_id"
                                    class="w-full px-4 py-2 transition-colors duration-200 bg-[#0a1514] border border-[#24403c] text-[#e6f0ee] rounded-lg focus:ring-[#2DC197] focus:border-[#2DC197]"
                                {{ $isPrefeituraUser ? 'disabled' : '' }}>
                                @if($isPrefeituraUser)
                                    <option value="{{ $prefeituras->first()->id }}" selected>
                                        {{ $prefeituras->first()->nome }}
                                    </option>
                                @else
                                    <option value="">Todas as Prefeituras</option>
                                    @foreach($prefeituras as $prefeitura)
                                        <option value="{{ $prefeitura->id }}" {{ request('prefeitura_id') == $prefeitura->id ? 'selected' : '' }}>
                                            {{ $prefeitura->nome }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @if($isPrefeituraUser)
                                <p class="mt-1 text-xs text-[#7ea29c]">
                                    Você está visualizando apenas contratos da sua prefeitura
                                </p>
                            @endif
                        </div>

                        {{-- Filtro por Modalidade (comum às duas abas) --}}
                        <div>
                            <label for="modalidade" class="block mb-1 text-sm font-medium text-[#a2b9b5]">
                                Modalidade
                            </label>
                            <select name="modalidade" id="modalidade" class="w-full px-4 py-2 transition-colors duration-200 bg-[#0a1514] border border-[#24403c] text-[#e6f0ee] rounded-lg focus:ring-[#2DC197] focus:border-[#2DC197]">
                                <option value="">Todas as Modalidades</option>
                                @foreach($modalidades as $modalidade)
                                    <option value="{{ $modalidade->value }}" {{ request('modalidade') == $modalidade->value ? 'selected' : '' }}>
                                        {{ $modalidade->getDisplayName() ?? $modalidade->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @if($abaAtiva === 'manual')
                            {{-- Filtro por Empresa (apenas para contratos manuais) --}}
                            <div>
                                <label for="empresa_id" class="block mb-1 text-sm font-medium text-[#a2b9b5]">
                                    Empresa Contratada
                                </label>
                                <select name="empresa_id" id="empresa_id" class="w-full px-4 py-2 transition-colors duration-200 bg-[#0a1514] border border-[#24403c] text-[#e6f0ee] rounded-lg focus:ring-[#2DC197] focus:border-[#2DC197]">
                                    <option value="">Todas as Empresas</option>
                                    @foreach($empresas as $empresa)
                                        <option value="{{ $empresa->id }}" {{ request('empresa_id') == $empresa->id ? 'selected' : '' }}>
                                            {{ $empresa->razao_social }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        @if($abaAtiva === 'sistema')
                            {{-- Filtro por Vencedor (apenas para contratos do sistema) --}}
                            <div>
                                <label for="vencedor_id" class="block mb-1 text-sm font-medium text-[#a2b9b5]">
                                    Vencedor
                                </label>
                                <select name="vencedor_id" id="vencedor_id" class="w-full px-4 py-2 transition-colors duration-200 bg-[#0a1514] border border-[#24403c] text-[#e6f0ee] rounded-lg focus:ring-[#2DC197] focus:border-[#2DC197]">
                                    <option value="">Todos os Vencedores</option>
                                    @foreach($vencedores as $vencedor)
                                        <option value="{{ $vencedor->id }}" {{ request('vencedor_id') == $vencedor->id ? 'selected' : '' }}>
                                            {{ $vencedor->razao_social ?? 'Sem empresa' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        {{-- Filtro por Situação --}}
                        <div>
                            <label for="situacao" class="block mb-1 text-sm font-medium text-[#a2b9b5]">
                                Situação
                            </label>
                            <select name="situacao" id="situacao" class="w-full px-4 py-2 transition-colors duration-200 bg-[#0a1514] border border-[#24403c] text-[#e6f0ee] rounded-lg focus:ring-[#2DC197] focus:border-[#2DC197]">
                                <option value="">Todas as Situações</option>
                                <option value="vigente"  {{ request('situacao') === 'vigente'  ? 'selected' : '' }}>Vigente</option>
                                <option value="vencido"  {{ request('situacao') === 'vencido'  ? 'selected' : '' }}>Vencido</option>
                                <option value="concluido" {{ request('situacao') === 'concluido' ? 'selected' : '' }}>Concluído</option>
                                <option value="pendente" {{ request('situacao') === 'pendente' ? 'selected' : '' }}>Pendente</option>
                            </select>
                        </div>

                        {{-- Filtro por Vencimento --}}
                        <div>
                            <label for="vencimento" class="block mb-1 text-sm font-medium text-[#a2b9b5]">
                                Vencimento
                            </label>
                            <select name="vencimento" id="vencimento" class="w-full px-4 py-2 transition-colors duration-200 bg-[#0a1514] border border-[#24403c] text-[#e6f0ee] rounded-lg focus:ring-[#2DC197] focus:border-[#2DC197]">
                                <option value="">Todos os Contratos</option>
                                <option value="vencidos" {{ request('vencimento') === 'vencidos' ? 'selected' : '' }}>Vencidos</option>
                                <option value="30" {{ request('vencimento') === '30' ? 'selected' : '' }}>Vencem em 30 dias</option>
                                <option value="60" {{ request('vencimento') === '60' ? 'selected' : '' }}>Vencem em 60 dias</option>
                                <option value="90" {{ request('vencimento') === '90' ? 'selected' : '' }}>Vencem em 90 dias</option>
                            </select>
                        </div>
                    </div>

                    {{-- Botões --}}
                    <div class="flex flex-shrink-0 gap-2">
                        <button type="submit" class="px-4 py-2 font-semibold text-[#041d1a] transition-colors duration-200 rounded-lg bg-[#2DC197] hover:bg-[#26aa84] focus:outline-none focus:ring-2 focus:ring-[#2DC197] focus:ring-offset-2 whitespace-nowrap">
                            <i class="fas fa-filter mr-1"></i> Filtrar
                        </button>
                        <a href="{{ route('admin.contratos.index', ['tipo' => $abaAtiva]) }}" class="px-4 py-2 text-center text-[#a2b9b5] transition-colors duration-200 bg-[#142825] border border-[#24403c] rounded-lg hover:bg-[#1a332f] hover:text-[#e6f0ee] focus:outline-none whitespace-nowrap">
                            <i class="fas fa-times mr-1"></i> Limpar
                        </a>
                        <a href="{{ route('admin.contratos.relatorio-pdf', array_filter(array_merge(['tipo' => $abaAtiva], request()->only(['search', 'prefeitura_id', 'modalidade', 'empresa_id', 'situacao', 'vencedor_id', 'vencimento'])))) }}"
                           target="_blank"
                           class="inline-flex items-center gap-1.5 px-4 py-2 text-white transition-colors duration-200 rounded-lg bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 whitespace-nowrap">
                            <i class="fas fa-file-pdf"></i> Gerar PDF
                        </a>
                    </div>
                </form>
            </div>

            {{-- Botão Novo Contrato (só aparece na aba de contratos manuais) --}}
            @if($abaAtiva === 'manual')
                <div class="flex justify-end mb-4">
                    <a href="{{ route('admin.contratos.create') }}" class="inline-flex items-center justify-center gap-3 px-6 py-3 text-sm font-semibold text-[#041d1a] transition-all duration-200 bg-[#2DC197] rounded-xl hover:bg-[#26aa84] hover:shadow-lg hover:scale-105">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Novo Contrato Manual
                    </a>
                </div>
            @endif

            {{-- Tabela de Contratos --}}
            <div class="overflow-hidden bg-[#10201e] border border-[#24403c] shadow-sm rounded-xl">
                @if($abaAtiva === 'manual')
                    {{-- Tabela para Contratos Manuais --}}
                    @include('Admin.contratos_externos.partials.tabela-manuais')
                @else
                    {{-- Tabela para Contratos do Sistema --}}
                    @include('Admin.contratos_externos.partials.tabela-sistema')
                @endif

                {{-- Paginação --}}
                @if($contratos->hasPages())
                    <div class="px-4 py-4 border-t border-[#24403c] bg-[#0d1b19] sm:px-6">
                        {{ $contratos->appends(array_merge(['tipo' => $abaAtiva], request()->except(['tipo', 'page'])))->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection
