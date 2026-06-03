@extends('layouts.app')

@section('page-title', 'Pesquisa de Preços')
@section('page-subtitle', 'Consulte itens homologados no PNCP para embasar sua pesquisa de preço de mercado.')

@section('content')
<div class="px-6 pb-10">

    {{-- ── BANNER CONTEXTUAL (só aparece quando vinculado a um processo) ── --}}
    @if($processo)
    <div id="pp_banner_processo"
        class="mb-4 flex items-center justify-between gap-4 px-4 py-3 bg-blue-600 text-white rounded-xl shadow-md">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider opacity-75">Vinculado ao Processo</p>
                <p class="text-sm font-bold">{{ $processo->numero_processo }}</p>
            </div>
            <div class="ml-2 pl-3 border-l border-white/30">
                <p class="text-xs opacity-75">Itens adicionados ao relatório</p>
                <p class="text-lg font-extrabold leading-none">
                    <span id="pp_contador_itens">{{ $processo->pesquisaPrecoItens()->count() }}</span>
                </p>
            </div>
        </div>
        <a href="{{ url()->previous() }}"
            class="flex-shrink-0 px-4 py-2 bg-white/20 hover:bg-white/30 text-white text-xs font-bold rounded-lg border border-white/30 transition-all">
            ← Voltar ao Processo
        </a>
    </div>
    @endif

    {{-- ── CABEÇALHO ────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Análise de Mercado</h1>
            <p class="text-sm text-gray-500 mt-0.5">Pesquisa de Preços via PNCP — Portal Nacional de Contratações Públicas</p>
        </div>
        <div class="flex items-center gap-2">
            <span id="pp_badge_cache" class="hidden inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-full border transition-all"></span>
            <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-green-50 text-green-700 text-xs font-bold rounded-full border border-green-200">
                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                API PNCP Conectada
            </span>
        </div>
    </div>

    {{-- ── BARRA DE PESQUISA ─────────────────────────────────────────── --}}
    @include('Admin.PesquisaPreco.partials._barra-pesquisa')

    {{-- ── FILTROS AVANÇADOS ─────────────────────────────────────────── --}}
    @include('Admin.PesquisaPreco.partials._filtros-avancados')

    {{-- ── ÁREA PRINCIPAL ────────────────────────────────────────────── --}}
    <div id="pp_area">

        {{-- Estado inicial --}}
        <div id="pp_estado_inicial" class="mt-10 flex flex-col items-center justify-center py-20 text-gray-400">
            <div class="p-5 bg-gray-50 rounded-full mb-4">
                <svg class="w-14 h-14 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
            <p class="text-sm font-medium">Digite um item ou serviço para iniciar a pesquisa</p>
            <p class="text-xs mt-1 text-gray-300">Ex: Ar condicionado, Notebook, Serviço de limpeza</p>
        </div>

        {{-- Loading --}}
        <div id="pp_loading" class="mt-10 hidden flex-col items-center justify-center py-20">
            <div class="w-12 h-12 border-4 border-blue-600/20 border-t-blue-600 rounded-full animate-spin mb-4"></div>
            <p class="text-sm text-gray-500 animate-pulse" id="pp_loading_msg">Consultando PNCP...</p>
        </div>

        {{-- Resultados --}}
        <div id="pp_resultados" class="mt-5 hidden">
            <div class="flex items-center justify-between mb-3 px-1">
                <p id="pp_sumario" class="text-xs text-gray-500 font-medium"></p>
                <div id="pp_paginacao" class="flex items-center gap-2"></div>
            </div>
            <div id="pp_lista_itens" class="flex flex-col gap-3"></div>
        </div>

        {{-- Erro --}}
        <div id="pp_erro" class="mt-8 hidden p-4 bg-red-50 border border-red-100 rounded-2xl text-red-600 text-sm text-center"></div>

    </div>
</div>

{{-- Template do card de item (invisível, clonado pelo JS) --}}
<template id="tpl_item_card">
    <div class="item-card bg-white border border-gray-200 rounded-xl overflow-hidden transition-all hover:border-blue-300 hover:shadow-sm">

        {{-- Linha principal --}}
        <div class="px-4 pt-3 pb-2">
            <div class="flex items-start justify-between gap-3">
                <h4 class="font-bold text-gray-800 text-sm leading-snug item-descricao flex-1"></h4>
                <button class="btn-incluir flex-shrink-0 text-xs font-semibold px-3 py-1 border rounded-full transition-all
                    @if($processo)
                        border-green-300 text-green-700 bg-green-50 hover:bg-green-100 cursor-pointer
                    @else
                        border-gray-200 text-gray-400 cursor-not-allowed opacity-60
                    @endif"
                    @if(!$processo) disabled @endif>
                    Incluir no relatório
                </button>
            </div>
        </div>

        {{-- Metadados --}}
        <div class="px-4 pb-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-gray-500">
            <span class="badge-tipo px-2 py-0.5 rounded-full font-bold text-[10px] uppercase tracking-wide"></span>
            <span class="item-orgao font-medium text-gray-700"></span>
            <span class="text-gray-300">|</span>
            <span class="item-uf font-semibold text-gray-600"></span>
            <span class="item-municipio"></span>
            <span class="text-gray-300">|</span>
            <span class="item-qtd"></span>
            <span class="text-gray-300 item-sep-hom hidden">|</span>
            <span class="item-homologado-em hidden"></span>
            <div class="ml-auto flex items-center gap-3">
                <span class="item-estimado text-gray-400"></span>
                <span class="item-homologado font-bold text-base" style="color:#059669"></span>
            </div>
        </div>

        {{-- Fornecedor + botões --}}
        <div class="px-4 pb-3 flex items-center justify-between gap-2">
            <span class="item-fornecedor text-xs text-gray-500 truncate max-w-xs"></span>
            <div class="flex items-center gap-2 flex-shrink-0">
                <button class="btn-ver-objeto inline-flex items-center gap-1 text-xs font-semibold px-3 py-1.5 border border-gray-300 rounded-lg hover:border-blue-400 hover:text-blue-700 transition-all text-gray-600">
                    Ver objeto
                    <svg class="w-3 h-3 chevron-icon transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <a class="link-ver-mais inline-flex items-center gap-1 text-xs font-semibold px-3 py-1.5 bg-blue-50 text-blue-700 border border-blue-200 rounded-lg hover:bg-blue-100 transition-all" target="_blank" rel="noopener">
                    Ver mais
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
            </div>
        </div>

        {{-- Painel expandível --}}
        <div class="painel-expandivel hidden border-t border-gray-100">
            {{-- Loading do painel --}}
            <div class="painel-loading flex items-center justify-center py-6 gap-2 text-xs text-gray-400">
                <div class="w-4 h-4 border-2 border-gray-300 border-t-blue-500 rounded-full animate-spin"></div>
                Carregando detalhes...
            </div>
            {{-- Conteúdo do painel (preenchido pelo JS) --}}
            <div class="painel-conteudo hidden p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    {{-- Detalhes do item --}}
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Detalhes do Item</p>
                        <div class="grid grid-cols-2 gap-x-4 gap-y-1.5 text-xs">
                            <div>
                                <p class="text-gray-400 text-[10px] uppercase font-semibold">Nº Item</p>
                                <p class="font-bold text-gray-700 painel-num-item"></p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-gray-400 text-[10px] uppercase font-semibold">Situação</p>
                                <p class="text-gray-700 painel-situacao"></p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-gray-400 text-[10px] uppercase font-semibold">Fornecedor (Vencedor)</p>
                                <p class="text-gray-700 painel-nome-fornecedor"></p>
                                <p class="font-mono text-gray-500 text-[11px] painel-cnpj-fornecedor mt-0.5"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Contratação --}}
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Contratação</p>
                        <div class="grid grid-cols-2 gap-x-4 gap-y-1.5 text-xs">
                            <div class="col-span-2">
                                <p class="text-gray-400 text-[10px] uppercase font-semibold">Objeto</p>
                                <p class="font-medium text-gray-700 painel-objeto"></p>
                            </div>
                            <div>
                                <p class="text-gray-400 text-[10px] uppercase font-semibold">Órgão</p>
                                <p class="text-gray-700 painel-orgao"></p>
                            </div>
                            <div>
                                <p class="text-gray-400 text-[10px] uppercase font-semibold">Modalidade</p>
                                <p class="text-gray-700 painel-modalidade"></p>
                            </div>
                            <div>
                                <p class="text-gray-400 text-[10px] uppercase font-semibold">Situação</p>
                                <a class="painel-link-pncp text-blue-600 hover:underline" target="_blank" rel="noopener"></a>
                            </div>
                            <div>
                                <p class="text-gray-400 text-[10px] uppercase font-semibold">Processo</p>
                                <p class="font-mono text-gray-700 painel-processo"></p>
                            </div>
                            <div>
                                <p class="text-gray-400 text-[10px] uppercase font-semibold">Publicação</p>
                                <p class="text-gray-700 painel-publicacao"></p>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Valores globais --}}
                <div class="grid grid-cols-2 gap-3 mt-3">
                    <div class="border border-gray-200 rounded-lg p-3 text-center">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Valor Global</p>
                        <p class="text-lg font-bold text-gray-700 painel-valor-global"></p>
                    </div>
                    <div class="border border-green-200 bg-green-50 rounded-lg p-3 text-center">
                        <p class="text-[10px] font-bold text-green-600 uppercase tracking-wider mb-1">Total Homologado</p>
                        <p class="text-lg font-bold text-green-700 painel-valor-homologado"></p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>
@endsection

@push('scripts')
<script>
// Contexto do processo (null quando acessado sem vinculação)
const PP_PROCESSO_ID   = @json($processo?->id);
const PP_ETP_ITEM_ID   = @json(request('etp_item_id'));
const PP_STORE_URL     = '{{ route('admin.pesquisa_preco.itens.store') }}';
const PP_DESTROY_URL   = '{{ url('admin/pesquisa-preco/itens') }}'; // + /{id}
const PP_CSRF          = '{{ csrf_token() }}';
const PP_TERMO_INICIAL = @json($termo ?? '');

document.addEventListener('DOMContentLoaded', function () {

    // ── Referências DOM ────────────────────────────────────────────
    const inputTermo     = document.getElementById('pp_termo');
    const btnBuscar      = document.getElementById('pp_btn_buscar');
    const estadoInicial  = document.getElementById('pp_estado_inicial');
    const loading        = document.getElementById('pp_loading');
    const loadingMsg     = document.getElementById('pp_loading_msg');
    const resultados     = document.getElementById('pp_resultados');
    const listaItens     = document.getElementById('pp_lista_itens');
    const sumario        = document.getElementById('pp_sumario');
    const paginacao      = document.getElementById('pp_paginacao');
    const erro           = document.getElementById('pp_erro');
    const tplCard        = document.getElementById('tpl_item_card');

    let debounceTimer    = null;
    let termoAtual       = '';
    let paginaAtual      = 1;
    let todosItensCache  = [];
    let metaAtual        = null;
    let acumulador       = null; // estado do auto-avanço (modo filtrado + termo)
    const VIRTUAL_PAGE_SIZE = 10;
    const MAX_SWEEP_PAGES   = 8; // máx páginas do servidor varridas por clique de "Próxima"

    const fmt = v => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v ?? 0);
    const fmtData = s => {
        if (!s) return null;
        // Aceita "2025-10-06", "2025-10-06T..." ou "06/10/2025"
        const d = new Date(s.includes('T') ? s : s.replace(/(\d{4})-(\d{2})-(\d{2})/, '$1-$2-$3'));
        return isNaN(d) ? s : d.toLocaleDateString('pt-BR');
    };
    const fmtCnpj = v => {
        if (!v) return '—';
        const s = String(v).replace(/\D/g,'').padStart(14,'0');
        return s.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5');
    };

    // ── Filtro de Valor por Referência ────────────────────────────
    function extrairValorItem(item) {
        const hom = item.valorUnitarioHomologado ?? item.valorHomologado ?? null;
        const est = item.valorUnitarioEstimado   ?? item.valorEstimado   ?? null;
        return hom ?? est ?? null;
    }

    function aplicarFiltrosLocais(lista) {
        // UF — filtra pelo campo uf da contratação (client-side, qualquer modo)
        const uf = document.getElementById('pp_uf')?.value;
        if (uf) {
            lista = lista.filter(({ contratacao: c }) => c.uf === uf);
        }

        // Valor de referência + variação %
        const ref = parseFloat(document.getElementById('pp_valor_ref')?.value);
        const pct = parseFloat(document.getElementById('pp_valor_pct')?.value);
        if (!isNaN(ref) && ref > 0 && !isNaN(pct) && pct >= 0) {
            const min = ref * (1 - pct / 100);
            const max = ref * (1 + pct / 100);
            lista = lista.filter(({ item }) => {
                const v = extrairValorItem(item);
                return v !== null && v >= min && v <= max;
            });
        }

        // Termo — só no modo filtrado simples; no modo virtual os itens já foram pré-filtrados pelo termo
        if (metaAtual?.modoFiltrado && !metaAtual?._virtual && termoAtual) {
            const termoLower = termoAtual.toLowerCase();
            lista = lista.filter(({ item }) =>
                (item.descricao || '').toLowerCase().includes(termoLower)
            );
        }

        return lista;
    }

    function atualizarPreviewValor() {
        const ref = parseFloat(document.getElementById('pp_valor_ref')?.value);
        const pct = parseFloat(document.getElementById('pp_valor_pct')?.value);
        const preview = document.getElementById('pp_valor_preview');
        const ativo = !isNaN(ref) && ref > 0 && !isNaN(pct) && pct >= 0;
        if (preview) {
            if (ativo) {
                document.getElementById('pp_valor_preview_min').textContent = fmt(ref * (1 - pct / 100));
                document.getElementById('pp_valor_preview_max').textContent = fmt(ref * (1 + pct / 100));
                preview.classList.remove('hidden');
            } else {
                preview.classList.add('hidden');
            }
        }
        if (metaAtual && todosItensCache.length > 0) {
            renderizarItens(aplicarFiltrosLocais(todosItensCache), metaAtual);
        }
    }

    ['pp_valor_ref', 'pp_valor_pct'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', atualizarPreviewValor);
    });

    // UF — re-busca no cache quando há termo ativo (cache filtra server-side por UF + termo)
    // Fallback: se não há termo, re-filtra apenas os resultados em memória
    document.getElementById('pp_uf')?.addEventListener('change', () => {
        const t = inputTermo.value.trim();
        if (t.length >= 3) {
            executarBusca(t, 1);
        } else if (metaAtual && todosItensCache.length > 0) {
            renderizarItens(aplicarFiltrosLocais(todosItensCache), metaAtual);
        }
    });

    // Modalidade — auto-preenche período (últimos 6 meses) quando datas estão vazias
    function toDateInput(d) {
        const p = n => String(n).padStart(2, '0');
        return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())}`;
    }

    document.getElementById('pp_modalidade')?.addEventListener('change', function () {
        const dataIni  = document.getElementById('pp_data_inicial');
        const dataFim  = document.getElementById('pp_data_final');
        const badge    = document.getElementById('pp_data_auto_badge');

        if (this.value && !dataIni.value && !dataFim.value) {
            const hoje           = new Date();
            const seisMesesAtras = new Date(hoje);
            seisMesesAtras.setMonth(hoje.getMonth() - 6);
            dataIni.value = toDateInput(seisMesesAtras);
            dataFim.value = toDateInput(hoje);
            badge?.classList.remove('hidden');
        } else if (!this.value) {
            badge?.classList.add('hidden');
        }
        coletarFiltros();
    });

    // Esconde o badge quando o usuário ajusta as datas manualmente
    ['pp_data_inicial', 'pp_data_final'].forEach(id => {
        document.getElementById(id)?.addEventListener('change', () => {
            document.getElementById('pp_data_auto_badge')?.classList.add('hidden');
        });
    });

    // ── Highlight do termo buscado ─────────────────────────────────
    function highlight(texto, termo) {
        if (!termo || !texto) return texto || '';
        const re = new RegExp(`(${termo.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        return texto.replace(re, '<mark class="bg-yellow-200 text-yellow-900 rounded px-0.5">$1</mark>');
    }

    // ── Contador de itens incluídos no relatório ──────────────────
    function atualizarContador(delta) {
        const el = document.getElementById('pp_contador_itens');
        if (!el) return;
        el.textContent = Math.max(0, parseInt(el.textContent || '0') + delta);
    }

    // ── Estados de UI ──────────────────────────────────────────────
    function mostrarLoading(msg = 'Consultando PNCP...') {
        estadoInicial.classList.add('hidden');
        resultados.classList.add('hidden');
        erro.classList.add('hidden');
        loadingMsg.textContent = msg;
        loading.classList.remove('hidden');
        loading.classList.add('flex');
    }

    function mostrarResultados() {
        loading.classList.add('hidden');
        loading.classList.remove('flex');
        erro.classList.add('hidden');
        resultados.classList.remove('hidden');
    }

    function mostrarErro(msg) {
        loading.classList.add('hidden');
        loading.classList.remove('flex');
        resultados.classList.add('hidden');
        estadoInicial.classList.add('hidden');
        erro.textContent = msg;
        erro.classList.remove('hidden');
    }

    // ── Coleta filtros e atualiza badge de modo ────────────────────
    function coletarFiltros() {
        const filtros = {
            data_inicial: document.getElementById('pp_data_inicial')?.value || null,
            data_final:   document.getElementById('pp_data_final')?.value   || null,
            uf:           document.getElementById('pp_uf')?.value           || null,
            modalidade:   document.getElementById('pp_modalidade')?.value   || null,
            situacao:     document.getElementById('pp_situacao')?.value     || null,
        };
        atualizarBadgeModo(filtros);
        return filtros;
    }

    function atualizarBadgeModo(filtros) {
        const badge = document.getElementById('pp_badge_modo');
        const nota  = document.getElementById('pp_filtro_nota');
        const aviso = document.getElementById('pp_aviso_modo_filtrado');
        if (!badge) return;
        const modoFiltrado   = filtros.modalidade && filtros.data_inicial && filtros.data_final;
        const temFiltroAtivo = modoFiltrado || !!filtros.situacao;
        badge.textContent = modoFiltrado ? 'Consulta estruturada ativa' : (filtros.situacao ? 'Situação aplicada' : 'Busca textual');
        badge.className   = temFiltroAtivo
            ? 'text-[10px] px-2.5 py-1 rounded-full font-bold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200 transition-all'
            : 'text-[10px] px-2.5 py-1 rounded-full font-bold uppercase tracking-wider bg-gray-100 text-gray-400 border border-gray-200 transition-all';
        if (aviso) aviso.classList.toggle('hidden', !modoFiltrado);
        if (nota) {
            if (modoFiltrado) {
                nota.innerHTML = '<strong class="text-blue-700">Consulta estruturada:</strong> Modalidade + Período definem os contratos buscados no PNCP. '
                    + '<strong class="text-amber-700">O termo filtra apenas os itens carregados nesta página</strong> — o PNCP não aceita busca textual neste modo.';
                nota.className = 'text-[10px] text-blue-600 leading-relaxed';
            } else {
                nota.innerHTML = '<strong class="text-blue-600">Modalidade</strong> ativa a consulta estruturada no PNCP (período preenchido automaticamente). '
                    + '<strong>UF</strong> e <strong>Valor</strong> filtram os resultados carregados instantaneamente.';
                nota.className = 'text-[10px] text-gray-400 leading-relaxed';
            }
        }
    }

    // Atualiza badge ao alterar qualquer filtro
    document.querySelectorAll('.pp-filtro-watch').forEach(el => {
        el.addEventListener('change', () => coletarFiltros());
    });

    // ── Eventos ────────────────────────────────────────────────────
    inputTermo.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        if (this.value.trim().length >= 3) {
            debounceTimer = setTimeout(() => executarBusca(this.value.trim(), 1), 600);
        }
    });

    inputTermo.addEventListener('keydown', e => {
        if (e.key === 'Enter') { clearTimeout(debounceTimer); const t = inputTermo.value.trim(); if (t.length >= 3) executarBusca(t, 1); }
    });

    btnBuscar.addEventListener('click', () => {
        const t = inputTermo.value.trim();
        if (t.length >= 3) executarBusca(t, 1);
    });

    document.getElementById('pp_btn_aplicar_filtros')?.addEventListener('click', () => {
        const t = inputTermo.value.trim();
        if (t.length >= 3) executarBusca(t, 1);
    });

    document.getElementById('pp_btn_limpar_filtros')?.addEventListener('click', () => {
        document.querySelectorAll('#pp_painel_filtros input, #pp_painel_filtros select')
            .forEach(el => { el.value = ''; });
        document.getElementById('pp_data_auto_badge')?.classList.add('hidden');
        coletarFiltros();
        atualizarPreviewValor(); // esconde preview de valor e re-renderiza sem filtros locais
    });

    document.getElementById('pp_btn_filtros')?.addEventListener('click', function () {
        const p      = document.getElementById('pp_painel_filtros');
        p.classList.toggle('hidden');
        const aberto = !p.classList.contains('hidden');
        this.querySelector('span').textContent = aberto ? 'Fechar filtros' : 'Filtros';
        this.className = aberto
            ? 'inline-flex items-center gap-2 px-4 py-3.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 font-semibold text-sm rounded-xl transition-all'
            : 'inline-flex items-center gap-2 px-4 py-3.5 bg-gray-100 hover:bg-gray-200 text-gray-600 font-semibold text-sm rounded-xl transition-all';
    });

    // ── Busca principal — despacha para o modo correto ────────────
    async function executarBusca(termo, paginaVirtual) {
        termoAtual  = termo;
        paginaAtual = paginaVirtual;

        const filtros      = coletarFiltros();
        const modoFiltrado = !!(filtros.modalidade && filtros.data_inicial && filtros.data_final);

        if (modoFiltrado && termo) {
            await buscarAutoAvanço(termo, paginaVirtual, filtros);
        } else {
            acumulador = null; // abandona estado de auto-avanço ao sair desse modo
            await buscarDireta(termo, paginaVirtual, filtros, modoFiltrado);
        }
    }

    // ── Modo direto: uma página do servidor, comportamento original ─
    async function buscarDireta(termo, pagina, filtros, modoFiltrado) {
        mostrarLoading('Buscando contratações no PNCP...');

        const params = new URLSearchParams({ termo, pagina });
        Object.entries(filtros).forEach(([k, v]) => { if (v) params.append(k, v); });

        let contratacoes;
        try {
            console.log('[PNCP] Buscando contratações:', { termo, pagina, filtros });
            const resp = await fetch(`{{ route('admin.pncp.mercado.search') }}?${params}`);
            const json = await resp.json();
            if (!json.success) { mostrarErro(json.message); return; }
            contratacoes = json.data;
            console.log(`[PNCP] ${contratacoes.totalRegistros} contratação(ões), ${contratacoes.data?.length ?? 0} nesta página`);
        } catch (e) {
            console.error('[PNCP] Erro na busca:', e);
            mostrarErro('Erro ao conectar com o servidor. Tente novamente.'); return;
        }

        if (!contratacoes.data || contratacoes.data.length === 0) {
            mostrarErro('Nenhum resultado encontrado para "' + termo + '". Tente outros termos ou ajuste os filtros.');
            return;
        }

        let contratacoesBuscar = contratacoes.data;
        if (filtros.situacao === '8') {
            const antes = contratacoesBuscar.length;
            contratacoesBuscar = modoFiltrado
                ? contratacoesBuscar.filter(c => (c.valorTotalHomologado ?? 0) > 0)
                : contratacoesBuscar.filter(c => c.temResultado === true);
            console.log(`[PNCP] Filtro homologado: ${antes} → ${contratacoesBuscar.length}`);
            if (contratacoesBuscar.length === 0) {
                mostrarErro('Nenhuma contratação com resultado homologado. Tente ampliar o período.');
                return;
            }
        }

        loadingMsg.textContent = `Carregando itens de ${contratacoesBuscar.length} contratação(ões)...`;
        const todosItens = await carregarItensContratacoes(contratacoesBuscar);

        todosItensCache = todosItens;
        metaAtual       = contratacoes;
        renderizarItens(aplicarFiltrosLocais(todosItens), contratacoes);
    }

    // ── Helper: busca itens de um lote de contratações em paralelo ─
    async function carregarItensContratacoes(contratacoes) {
        const itensRequests = contratacoes.map(c => {
            const ctrl = new AbortController();
            const tid  = setTimeout(() => ctrl.abort(), 12000);
            return fetch(`/admin/pncp/items/${c.orgaoEntidade.cnpj}/${c.anoCompra}/${c.sequencialCompra}`, { signal: ctrl.signal })
                .then(r => r.json())
                .then(j => {
                    clearTimeout(tid);
                    const itens = j.success ? j.data : [];
                    console.log(`[PNCP] ${c.orgaoEntidade.cnpj}/${c.anoCompra}/${c.sequencialCompra}: ${itens.length} item(ns)`);
                    return { contratacao: c, itens };
                })
                .catch(err => {
                    clearTimeout(tid);
                    console.warn(`[PNCP] Falha ao buscar itens de ${c.sequencialCompra}:`, err.name === 'AbortError' ? 'timeout' : err);
                    return { contratacao: c, itens: [] };
                });
        });

        const resultadosBatch = await Promise.allSettled(itensRequests);
        const todos = [];
        resultadosBatch.forEach(r => {
            const { contratacao, itens } = r.status === 'fulfilled' ? r.value : r.reason ?? { contratacao: null, itens: [] };
            if (!contratacao || !Array.isArray(itens)) return;
            itens.forEach(item => todos.push({ item, contratacao }));
        });
        return todos;
    }

    // ── Modo auto-avanço: varre páginas do servidor até ter itens suficientes ──
    async function buscarAutoAvanço(termo, paginaVirtual, filtros) {
        const chave = JSON.stringify({ termo, filtros });

        // Novo termo/filtro → recomeça do zero; mesma chave → aproveita acumulador existente
        if (!acumulador || acumulador.chave !== chave) {
            acumulador = { chave, itens: [], proximaServerPagina: 1, totalServerPaginas: null, esgotado: false };
        }

        const targetCount = paginaVirtual * VIRTUAL_PAGE_SIZE;

        // Só busca mais páginas se ainda não temos itens suficientes para esta página virtual
        if (acumulador.itens.length < targetCount && !acumulador.esgotado) {
            mostrarLoading(`Varrendo contratos no PNCP em busca de "${termo}"...`);
            let sweep = 0;

            while (acumulador.itens.length < targetCount && !acumulador.esgotado && sweep < MAX_SWEEP_PAGES) {
                const serverPagina = acumulador.proximaServerPagina;
                loadingMsg.textContent =
                    `Varrendo página ${serverPagina}` +
                    (acumulador.totalServerPaginas ? ` de ${acumulador.totalServerPaginas}` : '') +
                    ` · ${acumulador.itens.length} item(ns) com "${termo}" encontrado(s)`;

                const params = new URLSearchParams({ termo, pagina: serverPagina });
                Object.entries(filtros).forEach(([k, v]) => { if (v) params.append(k, v); });

                let contratacoes;
                try {
                    const resp = await fetch(`{{ route('admin.pncp.mercado.search') }}?${params}`);
                    const json = await resp.json();
                    if (!json.success) { mostrarErro(json.message); return; }
                    contratacoes = json.data;
                } catch (e) {
                    mostrarErro('Erro ao conectar com o servidor. Tente novamente.'); return;
                }

                if (!acumulador.totalServerPaginas && contratacoes.totalPaginas) {
                    acumulador.totalServerPaginas = contratacoes.totalPaginas;
                }

                if (!contratacoes.data?.length) {
                    acumulador.esgotado = true; break;
                }

                let contratacoesBuscar = contratacoes.data;
                if (filtros.situacao === '8') {
                    contratacoesBuscar = contratacoesBuscar.filter(c => (c.valorTotalHomologado ?? 0) > 0);
                }

                const itensNovos = await carregarItensContratacoes(contratacoesBuscar);

                // Aplica filtro de termo sobre a descrição e filtro de valor
                const termoLower = termo.toLowerCase();
                const ref = parseFloat(document.getElementById('pp_valor_ref')?.value);
                const pct = parseFloat(document.getElementById('pp_valor_pct')?.value);
                const temFiltroValor = !isNaN(ref) && ref > 0 && !isNaN(pct) && pct >= 0;
                const vMin = temFiltroValor ? ref * (1 - pct / 100) : null;
                const vMax = temFiltroValor ? ref * (1 + pct / 100) : null;

                const filtrados = itensNovos.filter(({ item }) => {
                    if (!(item.descricao || '').toLowerCase().includes(termoLower)) return false;
                    if (temFiltroValor) {
                        const v = extrairValorItem(item);
                        if (v === null || v < vMin || v > vMax) return false;
                    }
                    return true;
                });

                acumulador.itens = acumulador.itens.concat(filtrados);
                acumulador.proximaServerPagina++;

                if (acumulador.proximaServerPagina > (acumulador.totalServerPaginas ?? Infinity)) {
                    acumulador.esgotado = true;
                }

                sweep++;
            }
        }

        const start       = (paginaVirtual - 1) * VIRTUAL_PAGE_SIZE;
        const itensPagina = acumulador.itens.slice(start, start + VIRTUAL_PAGE_SIZE);
        const totalVirtual = acumulador.esgotado
            ? Math.max(1, Math.ceil(acumulador.itens.length / VIRTUAL_PAGE_SIZE))
            : paginaVirtual + 1;

        const metaVirtual = {
            modoFiltrado:    true,
            _virtual:        true,
            _aberto:         !acumulador.esgotado,
            totalRegistros:  acumulador.totalServerPaginas ?? '?',
            totalPaginas:    totalVirtual,
            paginaAtual:     paginaVirtual,
        };

        if (itensPagina.length === 0) {
            const msg = acumulador.esgotado
                ? `Nenhum item com "${termo}" nas ${acumulador.totalServerPaginas ?? 'todas as'} página(s) de contratos. Tente ampliar o período ou ajustar os filtros.`
                : `Nenhum item com "${termo}" nas ${MAX_SWEEP_PAGES} última(s) página(s) varridas. Tente uma UF ou período mais específico.`;
            mostrarErro(msg); return;
        }

        todosItensCache = itensPagina; // filtros locais (UF, valor) re-filtram só a página virtual atual
        metaAtual       = metaVirtual;
        renderizarItens(aplicarFiltrosLocais(itensPagina), metaVirtual);
    }

    // ── Renderização dos cards ─────────────────────────────────────
    function renderizarItens(lista, meta) {
        listaItens.innerHTML = '';

        const total = lista.length;
        const modoLabel = meta._virtual
            ? ` · Auto-avanço · página virtual ${meta.paginaAtual}`
            : (meta.modoFiltrado ? ' · Filtros avançados' : ' · Busca textual');
        const termoLabel = (meta.modoFiltrado && !meta._virtual && termoAtual) ? ` · filtrado por "${termoAtual}"` : '';
        sumario.textContent = `${(meta.totalRegistros || 0).toLocaleString('pt-BR')} pág(s) no PNCP${modoLabel}${termoLabel} · ${total} item(ns)`;

        if (total === 0) {
            mostrarErro('As contratações encontradas não possuem itens disponíveis no momento.');
            return;
        }

        // Log diagnóstico: mostra estrutura real do primeiro item para verificar campos da API
        if (lista.length > 0) {
            const { item: amostra } = lista[0];
            console.log('[PNCP] Estrutura bruta do item (amostra):', {
                cnpjFornecedor:      amostra.cnpjFornecedor,
                cnpjFornecedorNorm:  amostra.cnpjFornecedorNorm,
                situacaoCompraItem:  amostra.situacaoCompraItem,
                situacaoItem:        amostra.situacaoItem,
                nomeFornecedor:      amostra.nomeFornecedor,
                valorHomologado:     amostra.valorUnitarioHomologado ?? amostra.valorHomologado,
            });
        }

        lista.forEach(({ item, contratacao: c }) => {
            const card  = tplCard.content.cloneNode(true).firstElementChild;
            const cnpj  = c.orgaoEntidade.cnpj;
            const ano   = c.anoCompra;
            const seq   = c.sequencialCompra;

            // Armazena dados no dataset para uso posterior
            card.dataset.cnpj  = cnpj;
            card.dataset.ano   = ano;
            card.dataset.seq   = seq;

            // ── Descrição com highlight ──
            card.querySelector('.item-descricao').innerHTML = highlight(item.descricao || '(sem descrição)', termoAtual);

            // ── Badge tipo ──
            const tipo = item.tipoItem || item.materialOuServico?.nome || item.categoriaItem || null;
            const badgeEl = card.querySelector('.badge-tipo');
            if (tipo) {
                const isMaterial = tipo.toLowerCase().includes('material') || tipo === 'M';
                badgeEl.textContent = tipo.length <= 2 ? (tipo === 'M' ? 'Material' : 'Serviço') : tipo;
                badgeEl.classList.add(isMaterial ? 'bg-blue-100' : 'bg-purple-100', isMaterial ? 'text-blue-700' : 'text-purple-700');
            } else {
                badgeEl.classList.add('hidden');
            }

            // ── Metadados ──
            card.querySelector('.item-orgao').textContent    = c.orgaoEntidade.razaoSocial || '—';
            card.querySelector('.item-uf').textContent       = c.uf || '';
            card.querySelector('.item-municipio').textContent = c.municipio || '';
            card.querySelector('.item-qtd').textContent      = `Qtd: ${item.quantidade ?? '?'} ${item.unidadeMedida || ''}`.trim();

            // ── Valores ──
            const homVal  = item.valorUnitarioHomologado ?? item.valorHomologado ?? null;
            const estVal  = item.valorUnitarioEstimado  ?? item.valorEstimado  ?? null;
            const homEl   = card.querySelector('.item-homologado');
            const estEl   = card.querySelector('.item-estimado');

            if (homVal !== null && homVal > 0) {
                homEl.textContent = fmt(homVal);
                if (estVal !== null && Math.abs(estVal - homVal) > 0.001) {
                    estEl.textContent = `Est: ${fmt(estVal)}`;
                }
            } else if (estVal !== null) {
                homEl.textContent = fmt(estVal);
                estEl.textContent = 'Estimado';
                estEl.classList.add('text-yellow-600');
            } else {
                homEl.textContent = '—';
            }

            // ── Fornecedor ──
            const forn = item.nomeFornecedor || item.nomeRazaoSocialFornecedor || null;
            const fornEl = card.querySelector('.item-fornecedor');
            fornEl.textContent = forn ? `Fornecedor: ${forn}` : '';

            // ── Link Ver mais ──
            const linkVerMais = card.querySelector('.link-ver-mais');
            linkVerMais.href = `https://pncp.gov.br/app/editais/${cnpj}/${ano}/${seq}`;

            // ── Botão "Incluir no relatório" ──
            const btnIncluir = card.querySelector('.btn-incluir');
            if (PP_PROCESSO_ID && btnIncluir && !btnIncluir.disabled) {
                btnIncluir.addEventListener('click', async function () {
                    // Toggle: se já incluído, remove
                    if (this.dataset.savedId) {
                        try {
                            await fetch(`${PP_DESTROY_URL}/${this.dataset.savedId}`, {
                                method: 'DELETE',
                                headers: { 'X-CSRF-TOKEN': PP_CSRF, 'Accept': 'application/json' },
                            });
                            delete this.dataset.savedId;
                            this.textContent = 'Incluir no relatório';
                            this.className = this.className
                                .replace(/bg-green-\d00|text-green-\d00|border-green-\d00/g, '')
                                + ' border-green-300 text-green-700 bg-green-50 hover:bg-green-100';
                            atualizarContador(-1);
                        } catch { /* silencioso */ }
                        return;
                    }

                    this.textContent = 'Salvando...';
                    this.disabled = true;

                    const homVal = item.valorUnitarioHomologado ?? item.valorHomologado ?? null;
                    const estVal = item.valorUnitarioEstimado  ?? item.valorEstimado   ?? null;

                    const payload = {
                        processo_id:       PP_PROCESSO_ID,
                        etp_item_id:       PP_ETP_ITEM_ID,
                        numero_item:       item.numeroItem != null ? String(item.numeroItem) : null,
                        ano_compra:        String(ano),
                        sequencial_compra: String(seq),
                        orgao_cnpj:        String(cnpj),
                        orgao_nome:        c.orgaoEntidade?.razaoSocial || '(órgão não identificado)',
                        uf:                c.uf || null,
                        municipio:         c.municipio || null,
                        data_publicacao:   c.dataPublicacaoPncp ? c.dataPublicacaoPncp.substring(0, 10) : null,
                        modalidade:        c.modalidadeNome || null,
                        descricao:         item.descricao || '(sem descrição)',
                        quantidade:        item.quantidade ?? null,
                        unidade_medida:    item.unidadeMedida || null,
                        valor_unitario:    homVal ?? estVal ?? 0,
                        tipo_valor:        homVal !== null ? 'homologado' : 'estimado',
                        fornecedor_nome:   item.nomeFornecedor || item.nomeRazaoSocialFornecedor || null,
                        fornecedor_cnpj:   item.cnpjFornecedorNorm || item.cnpjFornecedor || null,
                        link_pncp:         `https://pncp.gov.br/app/editais/${cnpj}/${ano}/${seq}`,
                    };

                    try {
                        const resp = await fetch(PP_STORE_URL, {
                            method:  'POST',
                            headers: {
                                'Content-Type':  'application/json',
                                'Accept':        'application/json',
                                'X-CSRF-TOKEN':  PP_CSRF,
                            },
                            body: JSON.stringify(payload),
                        });
                        const json = await resp.json();

                        if (json.success) {
                            this.dataset.savedId = json.id;
                            this.innerHTML = '✓ Incluído — remover';
                            this.className = 'btn-incluir flex-shrink-0 text-xs font-semibold px-3 py-1 border rounded-full transition-all border-green-500 text-green-700 bg-green-100 hover:bg-red-50 hover:text-red-600 hover:border-red-300 cursor-pointer';
                            this.disabled = false;
                            atualizarContador(+1);
                        } else {
                            this.textContent = 'Erro — tente novamente';
                            this.disabled = false;
                        }
                    } catch {
                        this.textContent = 'Erro — tente novamente';
                        this.disabled = false;
                    }
                });
            }

            // ── Preenche dados fixos do painel (item + contratação já disponíveis) ──
            card.querySelector('.painel-num-item').textContent        = item.numeroItem ?? '—';
            
            const pNomeForn = item.nomeFornecedor || item.nomeRazaoSocialFornecedor || null;
            const pCnpjForn = item.cnpjFornecedorNorm || item.cnpjFornecedor || null;
            
            card.querySelector('.painel-nome-fornecedor').textContent = pNomeForn || '—';
            card.querySelector('.painel-cnpj-fornecedor').textContent = pCnpjForn ? fmtCnpj(pCnpjForn) : '';
            
            card.querySelector('.painel-situacao').textContent        = item.situacaoItem || item.situacaoCompraItem?.nome || '—';
            card.querySelector('.painel-objeto').textContent          = c.objeto || '—';
            card.querySelector('.painel-orgao').textContent           = c.orgaoEntidade.razaoSocial || '—';
            card.querySelector('.painel-modalidade').textContent      = c.modalidadeNome || '—';
            card.querySelector('.painel-processo').textContent        = '—';   // sobrescrito pelo detalhe se API retornar
            const pubDate = fmtData(c.dataPublicacaoPncp);
            card.querySelector('.painel-publicacao').textContent      = pubDate || '—';

            // Link PNCP (situação expandida)
            const linkPncp = card.querySelector('.painel-link-pncp');
            linkPncp.textContent = 'Divulgada no PNCP';
            linkPncp.href = `https://pncp.gov.br/app/editais/${cnpj}/${ano}/${seq}`;

            // ── Botão "Ver objeto" — carrega detalhe lazily ──
            let detalhesCarregados = false;
            card.querySelector('.btn-ver-objeto').addEventListener('click', async function () {
                const painel  = card.querySelector('.painel-expandivel');  // outer toggle div
                const chevron = card.querySelector('.chevron-icon');
                const aberto  = !painel.classList.contains('hidden');

                if (aberto) {
                    painel.classList.add('hidden');
                    chevron.style.transform = '';
                    return;
                }

                painel.classList.remove('hidden');
                chevron.style.transform = 'rotate(180deg)';

                if (detalhesCarregados) return;

                // Busca detalhe da contratação e resultados do item em paralelo
                try {
                    const requests = [
                        fetch(`/admin/pncp/contratacao/${cnpj}/${ano}/${seq}`).then(r => r.json()).catch(() => null),
                    ];

                    if (item.numeroItem != null) {
                        requests.push(
                            fetch(`/admin/pncp/contratacao/${cnpj}/${ano}/${seq}/itens/${item.numeroItem}/resultados`)
                                .then(r => r.json()).catch(() => null)
                        );
                    }

                    const [detJson, resJson] = await Promise.all(requests);
                    const det = detJson?.success ? detJson.data : null;
                    const resultados = resJson?.success && Array.isArray(resJson.data) ? resJson.data : [];

                    console.log('[PNCP] Detalhe recebido:', det);
                    console.log('[PNCP] Resultados do item:', resultados);

                    if (det) {
                        if (det.numeroProcesso)       card.querySelector('.painel-processo').textContent            = det.numeroProcesso;
                        if (det.objeto)               card.querySelector('.painel-objeto').textContent              = det.objeto;
                        if (det.modalidadeNome)       card.querySelector('.painel-modalidade').textContent          = det.modalidadeNome;
                        if (det.situacaoCompra)       linkPncp.textContent                                          = det.situacaoCompra;
                        if (det.valorTotalEstimado)   card.querySelector('.painel-valor-global').textContent        = fmt(det.valorTotalEstimado);
                        if (det.valorTotalHomologado) card.querySelector('.painel-valor-homologado').textContent    = fmt(det.valorTotalHomologado);

                        // Atualiza "Homologado em" no card
                        if (det.dataResultadoCompra) {
                            const dataFmt = fmtData(det.dataResultadoCompra);
                            const sepEl   = card.querySelector('.item-sep-hom');
                            const homEmEl = card.querySelector('.item-homologado-em');
                            sepEl.classList.remove('hidden');
                            homEmEl.textContent = `Homologado em: ${dataFmt}`;
                            homEmEl.classList.remove('hidden');
                        }
                    }

                    // Preenche fornecedor com dados do vencedor (resultados é a fonte autoritativa)
                    if (resultados.length > 0) {
                        const vencedor = resultados[0];
                        const nome = vencedor.nomeRazaoSocialFornecedor || null;
                        const cnpjVenc = vencedor.niFornecedor || null;
                        if (nome) card.querySelector('.painel-nome-fornecedor').textContent = nome;
                        if (cnpjVenc) card.querySelector('.painel-cnpj-fornecedor').textContent = fmtCnpj(cnpjVenc);
                    }

                    detalhesCarregados = true;
                } catch (e) {
                    console.warn('[PNCP] Erro ao buscar detalhes:', e);
                } finally {
                    card.querySelector('.painel-loading')?.classList.add('hidden');
                    card.querySelector('.painel-conteudo')?.classList.remove('hidden');
                }
            });

            listaItens.appendChild(card);
        });

        renderizarPaginacao(meta.totalPaginas, meta.paginaAtual ?? paginaAtual, meta._aberto ?? false);
        mostrarResultados();
    }

    // ── Paginação ──────────────────────────────────────────────────
    // aberto=true: total de páginas ainda é desconhecido (auto-avanço em curso)
    function renderizarPaginacao(totalPaginas, paginaCorrente, aberto = false) {
        paginacao.innerHTML = '';
        if (totalPaginas <= 1 && !aberto) return;

        const btn = (label, pg, ativo = false) => {
            const b = document.createElement('button');
            b.innerHTML = label;
            b.className = `px-3 py-1.5 text-xs font-bold rounded-lg border transition-all ${
                ativo ? 'bg-blue-600 text-white border-blue-600 cursor-default'
                      : 'bg-white text-gray-600 border-gray-200 hover:border-blue-400 hover:text-blue-600'
            }`;
            if (!ativo) b.addEventListener('click', () => executarBusca(termoAtual, pg));
            return b;
        };

        if (paginaCorrente > 1) paginacao.appendChild(btn('← Anterior', paginaCorrente - 1));
        const totalLabel = aberto ? '...' : String(totalPaginas);
        paginacao.appendChild(btn(`Pág. ${paginaCorrente} / ${totalLabel}`, paginaCorrente, true));
        if (aberto || paginaCorrente < totalPaginas) paginacao.appendChild(btn('Próxima →', paginaCorrente + 1));
    }

    // ── Auto-busca quando página é aberta com termo pré-definido ──
    if (PP_TERMO_INICIAL && PP_TERMO_INICIAL.length >= 3) {
        inputTermo.value = PP_TERMO_INICIAL;
        executarBusca(PP_TERMO_INICIAL, 1);
    }

    // ── Badge de status do cache local ───────────────────────────
    (async function carregarStatusCache() {
        try {
            const resp = await fetch('{{ route('admin.pncp.cache.status') }}');
            if (!resp.ok) return;
            const status = await resp.json();
            const badge  = document.getElementById('pp_badge_cache');
            if (!badge) return;

            if (!status.ativo) {
                badge.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-full border bg-gray-50 text-gray-400 border-gray-200';
                badge.title     = 'Execute: php artisan pncp:sincronizar --meses=6';
                badge.innerHTML = '<span class="w-2 h-2 bg-gray-300 rounded-full"></span> Cache inativo';
            } else if (status.defasado) {
                badge.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-full border bg-yellow-50 text-yellow-700 border-yellow-300';
                badge.title     = `Último sync: ${status.ultimo_sync ?? '—'} · ${status.total_contratacoes.toLocaleString('pt-BR')} contratos`;
                badge.innerHTML = '<span class="w-2 h-2 bg-yellow-400 rounded-full"></span> Cache desatualizado';
            } else {
                badge.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-full border bg-blue-50 text-blue-700 border-blue-200';
                badge.title     = `Último sync: ${status.ultimo_sync ?? '—'} · ${status.total_contratacoes.toLocaleString('pt-BR')} contratos`;
                badge.innerHTML = `<span class="w-2 h-2 bg-blue-500 rounded-full"></span> Cache: ${status.total_contratacoes.toLocaleString('pt-BR')} contratos`;
            }
            badge.classList.remove('hidden');
        } catch (_) { /* silencioso — cache status é informativo */ }
    })();

});
</script>
@endpush
