@extends('layouts.app')
@section('page-title', 'Registrar Nova Ocorrência')
@section('page-subtitle', 'Selecione um contrato para registrar a ocorrência')

@section('content')
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
        <div class="flex items-center justify-between px-5 py-3.5 bg-[#0d1b19]/60 border-b border-[#24403c]">
            <h3 class="text-xs font-bold text-[#a2b9b5] uppercase tracking-wider flex items-center gap-2">
                <i class="fas fa-sliders-h text-[#2DC197]"></i> Filtros
            </h3>
            <div class="flex items-center gap-4">
                <button type="button" @click="limparFiltrosRapidos()" x-show="filtroAtivo" x-cloak
                        class="text-xs font-semibold text-[#a2b9b5] hover:text-[#2DC197] transition-colors flex items-center gap-1">
                    <i class="fas fa-eraser"></i> Limpar rápidos
                </button>
                <a href="{{ route('admin.ocorrencias.index') }}"
                   class="text-xs font-semibold text-[#e6f0ee] hover:text-[#2DC197] transition-colors flex items-center gap-1">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
        </div>

        <div class="p-5 space-y-4">
            @if(!$isPrefeituraUser)
                <form method="GET" action="{{ route('admin.ocorrencias.selecionar-contrato') }}" class="max-w-xs">
                    <label class="block text-xs font-semibold text-[#a2b9b5] mb-1.5">
                        <i class="fas fa-building-columns mr-1 text-[#2DC197]"></i> Prefeitura
                    </label>
                    <select name="prefeitura_id" onchange="this.form.submit()"
                            class="w-full pl-3 pr-8 py-2.5 text-sm border border-[#24403c] rounded-xl bg-[#0a1514] text-[#e6f0ee] focus:ring-2 focus:ring-[#2DC197] focus:border-[#2DC197] transition-all">
                        <option value="">Todas as prefeituras</option>
                        @foreach($prefeituras as $pref)
                            <option value="{{ $pref->id }}" {{ (string) $prefeituraId === (string) $pref->id ? 'selected' : '' }}>
                                {{ $pref->nome }}
                            </option>
                        @endforeach
                    </select>
                    <noscript><button type="submit" class="mt-2 px-4 py-2 text-sm font-bold text-[#041d1a] bg-[#2DC197] rounded-lg">Filtrar</button></noscript>
                </form>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-1">
                    <label class="block text-xs font-semibold text-[#a2b9b5] mb-1.5">
                        <i class="fas fa-search mr-1 text-[#2DC197]"></i> Buscar
                    </label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-[#a2b9b5] text-sm"></i>
                        <input type="text" x-model="search"
                               placeholder="Empresa, CNPJ, nº de contrato ou objeto..."
                               class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border border-[#24403c] bg-[#0a1514] text-[#e6f0ee] placeholder-[#a2b9b5]/50 focus:ring-2 focus:ring-[#2DC197] focus:border-[#2DC197] transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#a2b9b5] mb-1.5">
                        <i class="fas fa-sitemap mr-1 text-[#2DC197]"></i> Secretaria/Unidade
                    </label>
                    <select x-model="secretaria"
                            class="w-full px-3 py-2.5 text-sm border border-[#24403c] rounded-xl bg-[#0a1514] text-[#e6f0ee] focus:ring-2 focus:ring-[#2DC197] focus:border-[#2DC197] transition-all">
                        <option value="">Todas as secretarias</option>
                        @foreach($secretariasDisponiveis as $sec)
                            <option value="{{ $sec }}">{{ $sec }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#a2b9b5] mb-1.5">
                        <i class="fas fa-building mr-1 text-[#2DC197]"></i> Empresa
                    </label>
                    <select x-model="empresaSel"
                            class="w-full px-3 py-2.5 text-sm border border-[#24403c] rounded-xl bg-[#0a1514] text-[#e6f0ee] focus:ring-2 focus:ring-[#2DC197] focus:border-[#2DC197] transition-all">
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
                 class="bg-[#10201e] rounded-xl shadow-sm border border-[#24403c] overflow-hidden transition-all hover:border-[#2DC197]/40"
                 x-cloak>

                <div @click="active = (active === {{ $loop->index }} ? null : {{ $loop->index }})"
                     class="px-6 py-4 flex items-center justify-between cursor-pointer hover:bg-[#142825] transition-colors select-none">
                    <div class="flex items-center gap-4 min-w-0">
                        <div class="w-11 h-11 shrink-0 rounded-full bg-[#142825] border border-[#24403c] text-[#2DC197] flex items-center justify-center font-bold shadow-sm">
                            {{ substr($empresa['nome'], 0, 1) }}
                        </div>
                        <div class="min-w-0">
                            <h4 class="font-bold text-[#e6f0ee] truncate">{{ $empresa['nome'] }}</h4>
                            <span class="text-xs text-[#a2b9b5] font-medium tracking-wide">CNPJ/CPF: {{ $empresa['cnpj'] }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5 shrink-0">
                        <span class="px-2.5 py-1 bg-[#0a1514] text-[#a2b9b5] border border-[#24403c] rounded-full text-[10px] font-bold uppercase tracking-wide">
                            {{ count($empresa['contratos']) }} contrato{{ count($empresa['contratos']) > 1 ? 's' : '' }}
                        </span>
                        <i class="fas fa-chevron-down text-[#a2b9b5] text-sm transition-transform duration-300"
                           :class="(active === {{ $loop->index }} || filtroAtivo) ? 'rotate-180' : ''"></i>
                    </div>
                </div>

                <div x-show="active === {{ $loop->index }} || filtroAtivo" x-collapse x-cloak class="border-t border-[#24403c] bg-[#0d1b19]/40">
                    <div class="p-4 space-y-2.5">
                        @foreach($empresa['contratos'] as $contrato)
                            <div x-show="matchContrato({{ json_encode($contrato) }})"
                                 class="bg-[#0a1514] p-4 rounded-xl border border-[#24403c] flex flex-col md:flex-row justify-between items-center gap-4 hover:shadow-md hover:border-[#2DC197]/50 transition-all">

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                                        <span class="font-bold text-[#2DC197]">Contrato {{ $contrato['numero'] }}</span>
                                        <span class="text-[9px] px-2 py-0.5 rounded-full bg-[#10201e] border border-[#24403c] text-[#a2b9b5] font-bold uppercase">{{ $contrato['origem'] }}</span>
                                        <span class="text-[10px] text-[#a2b9b5] flex items-center gap-1">
                                            <i class="fas fa-sitemap text-[#2DC197]"></i> {{ $contrato['secretaria'] }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-[#a2b9b5] line-clamp-2 leading-relaxed">{{ $contrato['objeto'] }}</p>
                                </div>

                                <div class="flex items-center gap-4 shrink-0">
                                    @if($contrato['ultima_ocorrencia'])
                                        <div class="flex items-center gap-2 pr-4 border-r border-[#24403c]">
                                            <a href="{{ route('admin.ocorrencias.show', $contrato['ultima_ocorrencia_id']) }}"
                                               class="p-2 text-[#2DC197] hover:bg-[#142825] rounded-lg transition-colors" title="Ver Última Ocorrência">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    @endif

                                    <a href="{{ route('admin.ocorrencias.create', ['id' => $contrato['id'], 'type' => $contrato['type']]) }}"
                                       class="px-5 py-2.5 bg-[#2DC197] text-[#041d1a] text-sm font-bold rounded-xl hover:bg-[#26aa84] transition-all flex items-center gap-2 shadow-sm whitespace-nowrap hover:scale-105">
                                        <i class="fas fa-triangle-exclamation"></i>
                                        Registrar Ocorrência
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-20 bg-[#10201e] rounded-xl border border-dashed border-[#24403c]">
                <i class="fas fa-file-contract text-5xl text-[#24403c] mb-4 block"></i>
                <h3 class="text-xl font-bold text-[#a2b9b5]">Nenhum contrato ativo encontrado</h3>
                <p class="text-sm text-[#a2b9b5]/70 mt-1">Tente escolher outra prefeitura no filtro acima.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
