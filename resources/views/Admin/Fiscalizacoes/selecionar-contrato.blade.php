@extends('layouts.app')
@section('page-title', 'Iniciar Nova Fiscalização')
@section('page-subtitle', 'Selecione um contrato para registrar a inspeção')

@section('content')
{{-- Definimos a lógica de busca/filtros diretamente no x-data para o Alpine reconhecer --}}
<div x-data="{
        search: '',
        secretaria: '',
        empresaSel: '',
        active: null,

        get filtroAtivo() {
            return this.search !== '' || this.secretaria !== '' || this.empresaSel !== '';
        },

        matchContrato(c) {
            if (this.secretaria && c.secretaria !== this.secretaria) return false;
            if (this.empresaSel && c.empresa_nome !== this.empresaSel) return false;
            if (!this.search) return true;

            const term = this.search.toLowerCase();
            const inEmpresa = c.empresa_nome.toLowerCase().includes(term) || (c.empresa_cnpj || '').includes(term);
            const inContrato = c.numero.toLowerCase().includes(term) || c.objeto.toLowerCase().includes(term);

            return inEmpresa || inContrato;
        },

        empresaVisivel(empresa) {
            return empresa.contratos.some(c => this.matchContrato(c));
        },

        limparFiltrosRapidos() {
            this.search = '';
            this.secretaria = '';
            this.empresaSel = '';
        }
    }">

    {{-- ============================================================ --}}
    {{-- PAINEL DE FILTROS                                            --}}
    {{-- ============================================================ --}}
    <div class="mb-6 card !rounded-2xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3.5 bg-[var(--card-2)]/60 border-b border-[var(--line)]">
            <h3 class="text-xs font-bold text-[var(--ink-2)] uppercase tracking-wider flex items-center gap-2">
                <i class="fas fa-sliders-h text-[var(--mint)]"></i> Filtros
            </h3>
            <div class="flex items-center gap-4">
                <button type="button" @click="limparFiltrosRapidos()" x-show="filtroAtivo" x-cloak
                        class="text-xs font-semibold text-[var(--ink-2)] hover:text-[var(--mint)] transition-colors flex items-center gap-1">
                    <i class="fas fa-eraser"></i> Limpar rápidos
                </button>
                <a href="{{ route('admin.fiscalizacoes.index') }}"
                   class="text-xs font-semibold text-[var(--ink)] hover:text-[var(--mint)] transition-colors flex items-center gap-1">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
        </div>

        <div class="p-5 space-y-4">
            {{-- Filtro de Prefeitura — depende do banco (equipe Licicon, que não está restrita a uma única prefeitura) --}}
            @if(!$isPrefeituraUser)
                <form method="GET" action="{{ route('admin.fiscalizacoes.selecionar-contrato') }}" class="max-w-xs">
                    <label class="block text-xs font-semibold text-[var(--ink-2)] mb-1.5">
                        <i class="fas fa-building-columns mr-1 text-[var(--mint)]"></i> Prefeitura
                    </label>
                    <select name="prefeitura_id" onchange="this.form.submit()"
                            class="w-full pl-3 pr-8 py-2.5 text-sm border border-[var(--line)] rounded-xl bg-[var(--bg)] text-[var(--ink)] focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-all">
                        <option value="">Todas as prefeituras</option>
                        @foreach($prefeituras as $pref)
                            <option value="{{ $pref->id }}" {{ (string) $prefeituraId === (string) $pref->id ? 'selected' : '' }}>
                                {{ $pref->nome }}
                            </option>
                        @endforeach
                    </select>
                    <noscript><button type="submit" class="mt-2 px-4 py-2 text-sm font-bold text-[var(--on-mint)] bg-[var(--mint)] rounded-lg">Filtrar</button></noscript>
                </form>
            @endif

            {{-- Filtros rápidos (client-side, instantâneos) --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-1">
                    <label class="block text-xs font-semibold text-[var(--ink-2)] mb-1.5">
                        <i class="fas fa-search mr-1 text-[var(--mint)]"></i> Buscar
                    </label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-[var(--ink-2)] text-sm"></i>
                        <input type="text" x-model="search"
                               placeholder="Empresa, CNPJ, nº de contrato ou objeto..."
                               class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border border-[var(--line)] bg-[var(--bg)] text-[var(--ink)] placeholder-[var(--ink-2)]/50 focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[var(--ink-2)] mb-1.5">
                        <i class="fas fa-sitemap mr-1 text-[var(--mint)]"></i> Secretaria/Unidade
                    </label>
                    <select x-model="secretaria"
                            class="w-full px-3 py-2.5 text-sm border border-[var(--line)] rounded-xl bg-[var(--bg)] text-[var(--ink)] focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-all">
                        <option value="">Todas as secretarias</option>
                        @foreach($secretariasDisponiveis as $sec)
                            <option value="{{ $sec }}">{{ $sec }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[var(--ink-2)] mb-1.5">
                        <i class="fas fa-building mr-1 text-[var(--mint)]"></i> Empresa
                    </label>
                    <select x-model="empresaSel"
                            class="w-full px-3 py-2.5 text-sm border border-[var(--line)] rounded-xl bg-[var(--bg)] text-[var(--ink)] focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)] transition-all">
                        <option value="">Todas as empresas</option>
                        @foreach($empresasDisponiveis as $emp)
                            <option value="{{ $emp }}">{{ $emp }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- LISTAGEM DE EMPRESAS / CONTRATOS                              --}}
    {{-- ============================================================ --}}
    <div class="space-y-3">
        @forelse($empresas as $empresa)
            <div x-show="empresaVisivel({{ json_encode($empresa) }})"
                 class="bg-[var(--card)] rounded-xl shadow-sm border border-[var(--line)] overflow-hidden transition-all hover:border-[var(--mint)]/40"
                 x-cloak>

                {{-- Header da Empresa (Accordion) --}}
                <div @click="active = (active === {{ $loop->index }} ? null : {{ $loop->index }})"
                     class="px-6 py-4 flex items-center justify-between cursor-pointer hover:bg-[var(--hover-bg)] transition-colors select-none">
                    <div class="flex items-center gap-4 min-w-0">
                        <div class="w-11 h-11 shrink-0 rounded-full bg-[var(--hover-bg)] border border-[var(--line)] text-[var(--mint)] flex items-center justify-center font-bold shadow-sm">
                            {{ substr($empresa['nome'], 0, 1) }}
                        </div>
                        <div class="min-w-0">
                            <h4 class="font-bold text-[var(--ink)] truncate">{{ $empresa['nome'] }}</h4>
                            <span class="text-xs text-[var(--ink-2)] font-medium tracking-wide">CNPJ/CPF: {{ $empresa['cnpj'] }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5 shrink-0">
                        @if($empresa['pendentes'] > 0)
                            <span class="px-2.5 py-1 bg-[var(--warn-bg)] text-amber-400 border border-amber-900/60 rounded-full text-[10px] font-bold uppercase tracking-wide">
                                <i class="fas fa-circle-exclamation mr-1"></i>{{ $empresa['pendentes'] }} pendente{{ $empresa['pendentes'] > 1 ? 's' : '' }}
                            </span>
                        @endif
                        <span class="px-2.5 py-1 bg-[var(--bg)] text-[var(--ink-2)] border border-[var(--line)] rounded-full text-[10px] font-bold uppercase tracking-wide">
                            {{ count($empresa['contratos']) }} contrato{{ count($empresa['contratos']) > 1 ? 's' : '' }}
                        </span>
                        <i class="fas fa-chevron-down text-[var(--ink-2)] text-sm transition-transform duration-300"
                           :class="(active === {{ $loop->index }} || filtroAtivo) ? 'rotate-180' : ''"></i>
                    </div>
                </div>

                {{-- Listagem de Contratos --}}
                <div x-show="active === {{ $loop->index }} || filtroAtivo" x-collapse x-cloak class="border-t border-[var(--line)] bg-[var(--card-2)]/40">
                    <div class="p-4 space-y-2.5">
                        @foreach($empresa['contratos'] as $contrato)
                            <div x-show="matchContrato({{ json_encode($contrato) }})"
                                 class="bg-[var(--bg)] p-4 rounded-xl border {{ !$contrato['ultima_fiscalizacao'] ? 'border-amber-900/50 bg-[var(--warn-bg)]' : 'border-[var(--line)]' }} flex flex-col md:flex-row justify-between items-center gap-4 hover:shadow-md hover:border-[var(--mint)]/50 transition-all">

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                                        <span class="font-bold text-[var(--mint)]">Contrato {{ $contrato['numero'] }}</span>
                                        @if(!$contrato['ultima_fiscalizacao'])
                                            <span class="text-[9px] px-2 py-0.5 rounded-full bg-[var(--warn-bg)] text-amber-400 border border-amber-900/60 font-bold uppercase">Pendente</span>
                                        @endif
                                        <span class="text-[9px] px-2 py-0.5 rounded-full bg-[var(--card)] border border-[var(--line)] text-[var(--ink-2)] font-bold uppercase">{{ $contrato['origem'] }}</span>
                                        <span class="text-[10px] text-[var(--ink-2)] flex items-center gap-1">
                                            <i class="fas fa-sitemap text-[var(--mint)]"></i> {{ $contrato['secretaria'] }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-[var(--ink-2)] line-clamp-2 leading-relaxed">{{ $contrato['objeto'] }}</p>
                                </div>

                                <div class="flex items-center gap-4 shrink-0">
                                    {{-- Se já foi fiscalizado, mostra opções de histórico --}}
                                    @if($contrato['ultima_fiscalizacao'])
                                        <div class="flex items-center gap-2 pr-4 border-r border-[var(--line)]">
                                            @php
                                                $ultimaId = $contrato['ultima_fiscal_id'] ?? $contrato['ultima_fiscalizacao_id'];
                                            @endphp

                                            <a href="{{ route('admin.fiscalizacoes.show', $ultimaId) }}"
                                               class="p-2 text-[var(--mint)] hover:bg-[var(--hover-bg)] rounded-lg transition-colors" title="Ver Última Fiscalização">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.fiscalizacoes.pdf', $ultimaId) }}"
                                               target="_blank"
                                               class="p-2 text-red-400 hover:bg-[var(--bad-bg)] rounded-lg transition-colors" title="Imprimir Relatório Anterior">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        </div>
                                    @endif

                                    {{-- Botão Principal de Ação --}}
                                    <a href="{{ route('admin.fiscalizacoes.create', ['id' => $contrato['id'], 'type' => $contrato['type']]) }}"
                                       class="px-5 py-2.5 bg-[var(--mint)] text-[var(--on-mint)] text-sm font-bold rounded-xl hover:bg-[var(--mint-h)] transition-all flex items-center gap-2 shadow-sm whitespace-nowrap hover:scale-105">
                                        <i class="fas fa-clipboard-check"></i>
                                        {{ $contrato['ultima_fiscalizacao'] ? 'Nova Inspeção' : 'Fiscalizar' }}
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-20 bg-[var(--card)] rounded-xl border border-dashed border-[var(--line)]">
                <i class="fas fa-file-contract text-5xl text-[var(--line)] mb-4 block"></i>
                <h3 class="text-xl font-bold text-[var(--ink-2)]">Nenhum contrato ativo encontrado</h3>
                <p class="text-sm text-[var(--ink-2)]/70 mt-1">Tente escolher outra prefeitura no filtro acima.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
