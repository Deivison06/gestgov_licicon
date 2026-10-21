@extends('layouts.app')

@section('page-title', 'Gestão de Contratos')
@section('page-subtitle', 'Gerencie os contratos do sistema e manuais')

@section('content')

    {{-- Mensagem de Sucesso --}}
    @if(session('success'))
        <div class="flex items-center p-4 mb-6 text-[var(--mint)] border border-[var(--line-3)] rounded-lg bg-[var(--card-3)]">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Alerta de Contratos Vencidos --}}
    @if($vencidosCount > 0)
        {{-- x-show="visivel" (não "open"): esse nome colide com o seletor CSS
             [x-show="open"] usado pra estilizar dropdowns/submenus flutuantes
             (background claro + sombra 0 20px 40px rgba(0,0,0,.6)) — esse
             alerta não é um dropdown, só reaproveitava "open" por acaso. --}}
        <div x-data="{ visivel: true }" x-show="visivel" class="flex items-center justify-between p-4 mb-6 text-[var(--bad-strong)] border border-[var(--bad-border)] rounded-lg bg-[var(--bad-bg)]">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0 text-[var(--bad-strong)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                <span class="font-medium text-[var(--ink)]">
                    Você tem <strong class="text-[var(--bad-strong)]">{{ $vencidosCount }}</strong> {{ $vencidosCount === 1 ? 'contrato vencido' : 'contratos vencidos' }}.
                </span>
                <a href="{{ route('admin.contratos.index', ['tipo' => 'manual', 'situacao' => 'vencido']) }}"
                   class="inline-flex items-center gap-1 text-sm font-semibold text-[var(--bad-strong)] underline underline-offset-2 hover:text-[var(--bad-strong)] transition-colors">
                    Clique aqui para visualizá-los
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
            <button @click="visivel = false" class="text-[var(--ink-4)] hover:text-[var(--ink)] transition-colors" title="Fechar">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif

    {{-- Abas de Navegação --}}
    <div class="mb-6 bg-[var(--card)] border border-[var(--line)] shadow-sm rounded-2xl overflow-hidden">
        <div class="border-b border-[var(--line)] bg-[var(--bg)]">
            <nav class="flex -mb-px" aria-label="Tabs">
                @php
                    // Prepara os parâmetros para as abas
                    $paramsManual = array_merge(request()->except(['tipo', 'page']), ['tipo' => 'manual']);
                    $paramsSistema = array_merge(request()->except(['tipo', 'page']), ['tipo' => 'sistema']);
                @endphp

                <a href="{{ route('admin.contratos.index', $paramsManual) }}"
                   class="{{ $abaAtiva === 'manual' ? 'border-[var(--mint)] text-[var(--mint)] font-semibold' : 'border-transparent text-[var(--ink-4)] hover:text-[var(--ink)]' }} flex-1 py-4 px-6 text-center border-b-2 font-medium text-sm transition-colors duration-200">
                    <div class="flex items-center justify-center gap-2">
                        <i class="fas fa-file-signature"></i>
                        <span>Contratos Manuais</span>
                        @if($tipoContratos === 'manual')
                            <span class="bg-[var(--mint)]/20 text-[var(--mint)] border border-[var(--mint)]/30 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                        {{ $contratos->total() }}
                    </span>
                        @endif
                    </div>
                </a>
                <a href="{{ route('admin.contratos.index', $paramsSistema) }}"
                   class="{{ $abaAtiva === 'sistema' ? 'border-[var(--mint)] text-[var(--mint)] font-semibold' : 'border-transparent text-[var(--ink-4)] hover:text-[var(--ink)]' }} flex-1 py-4 px-6 text-center border-b-2 font-medium text-sm transition-colors duration-200">
                    <div class="flex items-center justify-center gap-2">
                        <i class="fas fa-cogs"></i>
                        <span>Contratos do Sistema</span>
                        @if($tipoContratos === 'sistema')
                            <span class="bg-[var(--mint)]/20 text-[var(--mint)] border border-[var(--mint)]/30 text-xs font-semibold px-2.5 py-0.5 rounded-full">
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
                <h3 class="mb-4 text-lg font-semibold text-[var(--ink)]">Filtrar Contratos</h3>
                <form method="GET" action="{{ route('admin.contratos.index') }}" class="flex flex-col gap-4">
                    <input type="hidden" name="tipo" value="{{ $abaAtiva }}">

                    {{-- Campo de Pesquisa Livre --}}
                    <div>
                        <label for="search" class="block mb-2 text-sm font-medium text-[var(--ink-2)]">
                            <i class="fas fa-search mr-1 text-[var(--mint)]"></i> Pesquisa Livre
                        </label>
                        <div class="relative">
                            <input type="text"
                                   name="search"
                                   id="search"
                                   value="{{ request('search') }}"
                                   placeholder="Digite para pesquisar por processo, contrato, empresa, objeto..."
                                   class="w-full px-4 py-2 pl-10 transition-colors duration-200 bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-lg focus:ring-[var(--mint)] focus:border-[var(--mint)]">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <i class="fas fa-search text-[var(--ink-4)]"></i>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-[var(--ink-4)]">
                            Pesquise por qualquer informação relacionada ao contrato
                        </p>
                    </div>

                    {{-- 5 campos (Prefeitura, Modalidade, Empresa/Vencedor, Situação, Vencimento) —
                         lg:grid-cols-5 encaixa todos numa linha só em telas largas, sem sobrar
                         um campo sozinho numa linha incompleta. --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                        {{-- Filtro por Prefeitura (apenas para admin/diretor) --}}
                        <div>
                            <label for="prefeitura_id" class="block mb-1 text-sm font-medium text-[var(--ink-2)]">
                                Prefeitura
                            </label>
                            <select name="prefeitura_id" id="prefeitura_id"
                                    class="w-full px-4 py-2 transition-colors duration-200 bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-lg focus:ring-[var(--mint)] focus:border-[var(--mint)]"
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
                                <p class="mt-1 text-xs text-[var(--ink-4)]">
                                    Você está visualizando apenas contratos da sua prefeitura
                                </p>
                            @endif
                        </div>

                        {{-- Filtro por Modalidade (comum às duas abas) --}}
                        <div>
                            <label for="modalidade" class="block mb-1 text-sm font-medium text-[var(--ink-2)]">
                                Modalidade
                            </label>
                            <select name="modalidade" id="modalidade" class="w-full px-4 py-2 transition-colors duration-200 bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-lg focus:ring-[var(--mint)] focus:border-[var(--mint)]">
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
                                <label for="empresa_id" class="block mb-1 text-sm font-medium text-[var(--ink-2)]">
                                    Empresa Contratada
                                </label>
                                <select name="empresa_id" id="empresa_id" class="w-full px-4 py-2 transition-colors duration-200 bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-lg focus:ring-[var(--mint)] focus:border-[var(--mint)]">
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
                                <label for="vencedor_id" class="block mb-1 text-sm font-medium text-[var(--ink-2)]">
                                    Vencedor
                                </label>
                                <select name="vencedor_id" id="vencedor_id" class="w-full px-4 py-2 transition-colors duration-200 bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-lg focus:ring-[var(--mint)] focus:border-[var(--mint)]">
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
                            <label for="situacao" class="block mb-1 text-sm font-medium text-[var(--ink-2)]">
                                Situação
                            </label>
                            <select name="situacao" id="situacao" class="w-full px-4 py-2 transition-colors duration-200 bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-lg focus:ring-[var(--mint)] focus:border-[var(--mint)]">
                                <option value="">Todas as Situações</option>
                                <option value="vigente"  {{ request('situacao') === 'vigente'  ? 'selected' : '' }}>Vigente</option>
                                <option value="vencido"  {{ request('situacao') === 'vencido'  ? 'selected' : '' }}>Vencido</option>
                                <option value="concluido" {{ request('situacao') === 'concluido' ? 'selected' : '' }}>Concluído</option>
                                <option value="pendente" {{ request('situacao') === 'pendente' ? 'selected' : '' }}>Pendente</option>
                            </select>
                        </div>

                        {{-- Filtro por Vencimento --}}
                        <div>
                            <label for="vencimento" class="block mb-1 text-sm font-medium text-[var(--ink-2)]">
                                Vencimento
                            </label>
                            <select name="vencimento" id="vencimento" class="w-full px-4 py-2 transition-colors duration-200 bg-[var(--bg)] border border-[var(--line)] text-[var(--ink)] rounded-lg focus:ring-[var(--mint)] focus:border-[var(--mint)]">
                                <option value="">Todos os Contratos</option>
                                <option value="vencidos" {{ request('vencimento') === 'vencidos' ? 'selected' : '' }}>Vencidos</option>
                                <option value="30" {{ request('vencimento') === '30' ? 'selected' : '' }}>Vencem em 30 dias</option>
                                <option value="60" {{ request('vencimento') === '60' ? 'selected' : '' }}>Vencem em 60 dias</option>
                                <option value="90" {{ request('vencimento') === '90' ? 'selected' : '' }}>Vencem em 90 dias</option>
                            </select>
                        </div>
                    </div>

                    {{-- Botões --}}
                    <div class="flex flex-wrap items-center justify-end gap-2 pt-4 border-t border-[var(--line-2)]">
                        <a href="{{ route('admin.contratos.relatorio-pdf', array_filter(array_merge(['tipo' => $abaAtiva], request()->only(['search', 'prefeitura_id', 'modalidade', 'empresa_id', 'situacao', 'vencedor_id', 'vencimento'])))) }}"
                           target="_blank"
                           class="inline-flex items-center gap-1.5 px-4 py-2 text-white transition-colors duration-200 rounded-lg bg-[var(--bad-strong)] hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-[var(--bad-strong)] focus:ring-offset-2 whitespace-nowrap order-3 sm:order-1">
                            <i class="fas fa-file-pdf"></i> Gerar PDF
                        </a>
                        <a href="{{ route('admin.contratos.index', ['tipo' => $abaAtiva]) }}" class="px-4 py-2 text-center text-[var(--ink-2)] transition-colors duration-200 bg-[var(--hover-bg)] border border-[var(--line)] rounded-lg hover:bg-[var(--card-3)] hover:text-[var(--ink)] focus:outline-none whitespace-nowrap order-2">
                            <i class="fas fa-times mr-1"></i> Limpar
                        </a>
                        <button type="submit" class="px-4 py-2 font-semibold text-[var(--on-mint)] transition-colors duration-200 rounded-lg bg-[var(--mint)] hover:bg-[var(--mint-h)] focus:outline-none focus:ring-2 focus:ring-[var(--mint)] focus:ring-offset-2 whitespace-nowrap order-1 sm:order-3">
                            <i class="fas fa-filter mr-1"></i> Filtrar
                        </button>
                    </div>
                </form>
            </div>

            {{-- Botão Novo Contrato (só aparece na aba de contratos manuais) --}}
            @if($abaAtiva === 'manual')
                <div class="flex justify-end mb-4">
                    <a href="{{ route('admin.contratos.create') }}" class="inline-flex items-center justify-center gap-3 px-6 py-3 text-sm font-semibold text-[var(--on-mint)] transition-all duration-200 bg-[var(--mint)] rounded-xl hover:bg-[var(--mint-h)] hover:shadow-lg hover:scale-105">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Novo Contrato Manual
                    </a>
                </div>
            @endif

            {{-- Tabela de Contratos --}}
            <div class="overflow-hidden bg-[var(--card)] border border-[var(--line)] shadow-sm rounded-xl">
                @if($abaAtiva === 'manual')
                    {{-- Tabela para Contratos Manuais --}}
                    @include('Admin.contratos_externos.partials.tabela-manuais')
                @else
                    {{-- Tabela para Contratos do Sistema --}}
                    @include('Admin.contratos_externos.partials.tabela-sistema')
                @endif

                {{-- Paginação --}}
                @if($contratos->hasPages())
                    <div class="px-4 py-4 border-t border-[var(--line)] bg-[var(--card-2)] sm:px-6">
                        {{ $contratos->appends(array_merge(['tipo' => $abaAtiva], request()->except(['tipo', 'page'])))->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection
