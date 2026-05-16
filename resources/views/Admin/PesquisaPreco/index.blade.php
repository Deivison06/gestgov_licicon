@extends('layouts.app')

@section('page-title', 'Pesquisa de Preços')
@section('page-subtitle', 'Consulte contratações públicas homologadas no PNCP para embasar sua pesquisa de mercado.')

@section('content')
<div class="px-6 pb-8">

    {{-- ── CABEÇALHO DA PÁGINA ─────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Análise de Mercado</h1>
            <p class="text-sm text-gray-500 mt-0.5">Pesquisa de Preços via PNCP — Portal Nacional de Contratações Públicas</p>
        </div>
        <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-green-50 text-green-700 text-xs font-bold rounded-full border border-green-200">
            <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
            API PNCP Conectada
        </span>
    </div>

    {{-- ── BARRA DE PESQUISA ───────────────────────────────────────── --}}
    @include('Admin.PesquisaPreco.partials._barra-pesquisa')

    {{-- ── FILTROS AVANÇADOS ──────────────────────────────────────── --}}
    @include('Admin.PesquisaPreco.partials._filtros-avancados')

    {{-- ── ÁREA DE RESULTADOS ─────────────────────────────────────── --}}
    <div id="pp_area_resultados">

        {{-- Estado inicial --}}
        <div id="pp_estado_inicial" class="mt-8 flex flex-col items-center justify-center py-20 text-gray-400">
            <div class="p-5 bg-gray-50 rounded-full mb-4">
                <svg class="w-14 h-14 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
            </div>
            <p class="text-sm font-medium">Digite um item ou serviço na barra de pesquisa</p>
            <p class="text-xs mt-1 text-gray-300">Ex: Ar condicionado, Notebook, Serviço de limpeza</p>
        </div>

        {{-- Estado de loading --}}
        <div id="pp_loading" class="mt-8 hidden flex flex-col items-center justify-center py-20">
            <div class="w-14 h-14 border-4 border-blue-600/20 border-t-blue-600 rounded-full animate-spin mb-4"></div>
            <p class="text-sm text-gray-500 animate-pulse">Consultando base nacional do PNCP...</p>
        </div>

        {{-- Resultados --}}
        <div id="pp_resultados" class="mt-6 hidden">

            {{-- Sumário da busca --}}
            <div class="flex items-center justify-between mb-4 px-1">
                <p id="pp_sumario" class="text-xs text-gray-500 font-medium"></p>
                <div id="pp_paginacao" class="flex items-center gap-2"></div>
            </div>

            {{-- Cards de contratações --}}
            <div id="pp_cards" class="grid gap-3"></div>

        </div>

        {{-- Detalhe dos itens --}}
        <div id="pp_detalhe_itens" class="mt-6 hidden">
            @include('Admin.PesquisaPreco.partials._tabela-itens')
        </div>

        {{-- Estado de erro --}}
        <div id="pp_erro" class="mt-8 hidden p-4 bg-red-50 border border-red-100 rounded-2xl text-red-600 text-sm text-center"></div>

    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Referências DOM ─────────────────────────────────────────────
    const inputTermo      = document.getElementById('pp_termo');
    const btnBuscar       = document.getElementById('pp_btn_buscar');
    const estadoInicial   = document.getElementById('pp_estado_inicial');
    const loading         = document.getElementById('pp_loading');
    const resultados      = document.getElementById('pp_resultados');
    const detalheItens    = document.getElementById('pp_detalhe_itens');
    const erro            = document.getElementById('pp_erro');
    const cards           = document.getElementById('pp_cards');
    const sumario         = document.getElementById('pp_sumario');
    const paginacao       = document.getElementById('pp_paginacao');
    const tabelaItens     = document.getElementById('pp_tabela_itens_body');
    const btnVoltarLista  = document.getElementById('pp_btn_voltar');
    const lblContratacao  = document.getElementById('pp_label_contratacao');

    let debounceTimer     = null;
    let paginaAtual       = 1;
    let termoAtual        = '';
    let filtrosAtual      = {};

    // ── Debounce no input ───────────────────────────────────────────
    inputTermo.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        const termo = this.value.trim();
        if (termo.length >= 3) {
            debounceTimer = setTimeout(() => executarBusca(termo, 1), 600);
        }
    });

    btnBuscar.addEventListener('click', function () {
        const termo = inputTermo.value.trim();
        if (termo.length >= 3) executarBusca(termo, 1);
    });

    inputTermo.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            clearTimeout(debounceTimer);
            const termo = this.value.trim();
            if (termo.length >= 3) executarBusca(termo, 1);
        }
    });

    // ── Lógica de exibição de estados ───────────────────────────────
    function mostrarLoading() {
        estadoInicial.classList.add('hidden');
        resultados.classList.add('hidden');
        detalheItens.classList.add('hidden');
        erro.classList.add('hidden');
        loading.classList.remove('hidden');
    }

    function mostrarResultados() {
        loading.classList.add('hidden');
        detalheItens.classList.add('hidden');
        erro.classList.add('hidden');
        resultados.classList.remove('hidden');
    }

    function mostrarDetalhe() {
        loading.classList.add('hidden');
        resultados.classList.add('hidden');
        erro.classList.add('hidden');
        detalheItens.classList.remove('hidden');
    }

    function mostrarErro(msg) {
        loading.classList.add('hidden');
        resultados.classList.add('hidden');
        detalheItens.classList.add('hidden');
        estadoInicial.classList.add('hidden');
        erro.textContent = msg;
        erro.classList.remove('hidden');
    }

    // ── Coleta filtros do formulário ─────────────────────────────────
    function coletarFiltros() {
        return {
            data_inicial: document.getElementById('pp_data_inicial')?.value || null,
            data_final:   document.getElementById('pp_data_final')?.value   || null,
            uf:           document.getElementById('pp_uf')?.value           || null,
            codigo_ibge:  document.getElementById('pp_codigo_ibge')?.value  || null,
            cnpj_orgao:   document.getElementById('pp_cnpj_orgao')?.value   || null,
        };
    }

    // ── Busca principal ─────────────────────────────────────────────
    function executarBusca(termo, pagina) {
        termoAtual  = termo;
        paginaAtual = pagina;
        filtrosAtual = coletarFiltros();

        mostrarLoading();

        const params = new URLSearchParams({ termo, pagina });
        Object.entries(filtrosAtual).forEach(([k, v]) => { if (v) params.append(k, v); });

        fetch(`{{ route('admin.pncp.mercado.search') }}?${params}`)
            .then(r => r.json())
            .then(resp => {
                if (!resp.success) { mostrarErro(resp.message); return; }
                renderizarCards(resp.data);
            })
            .catch(() => mostrarErro('Erro ao conectar com o servidor. Tente novamente.'));
    }

    // ── Renderizar cards de contratações ────────────────────────────
    function renderizarCards(data) {
        if (!data.data || data.data.length === 0) {
            mostrarErro('Nenhum resultado encontrado para este termo. Tente outros termos ou ajuste os filtros.');
            return;
        }

        sumario.textContent = `${data.totalRegistros.toLocaleString('pt-BR')} contratação(ões) encontrada(s) — exibindo página ${data.paginaAtual ?? paginaAtual} de ${data.totalPaginas}`;

        cards.innerHTML = '';
        data.data.forEach(item => {
            const dataPub = item.dataPublicacaoPncp
                ? new Date(item.dataPublicacaoPncp).toLocaleDateString('pt-BR')
                : '—';

            const card = document.createElement('div');
            card.className = 'group p-4 bg-white border border-gray-100 rounded-2xl hover:border-blue-200 hover:shadow-md transition-all cursor-pointer relative overflow-hidden';
            card.innerHTML = `
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-transparent group-hover:bg-blue-600 transition-all rounded-l-2xl"></div>
                <div class="flex justify-between items-start gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                            <span class="px-2 py-0.5 bg-blue-50 text-blue-600 text-[9px] font-bold rounded uppercase tracking-wider">${item.modalidadeNome || 'N/D'}</span>
                            <span class="text-[10px] font-bold text-gray-400">#${item.sequencialCompra}/${item.anoCompra}</span>
                            ${item.uf ? `<span class="px-2 py-0.5 bg-gray-100 text-gray-500 text-[9px] font-bold rounded uppercase">${item.uf}</span>` : ''}
                        </div>
                        <h5 class="font-bold text-gray-800 text-sm mb-1 group-hover:text-blue-700 transition-colors truncate">${item.orgaoEntidade.razaoSocial || 'Órgão não informado'}</h5>
                        <p class="text-xs text-gray-500 line-clamp-2 leading-relaxed">${item.objeto || 'Objeto não informado'}</p>
                        ${item.municipio ? `<p class="text-[10px] text-gray-400 mt-1">${item.municipio}${item.uf ? ' / ' + item.uf : ''}</p>` : ''}
                    </div>
                    <div class="flex-shrink-0 flex flex-col items-end gap-2">
                        <span class="text-[10px] text-gray-400">${dataPub}</span>
                        <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </div>
                </div>`;

            card.addEventListener('click', () =>
                verItens(item.orgaoEntidade.cnpj, item.anoCompra, item.sequencialCompra, item.orgaoEntidade.razaoSocial, item.objeto)
            );
            cards.appendChild(card);
        });

        renderizarPaginacao(data.totalPaginas, data.paginaAtual ?? paginaAtual);
        mostrarResultados();
    }

    // ── Paginação ───────────────────────────────────────────────────
    function renderizarPaginacao(totalPaginas, paginaCorrente) {
        paginacao.innerHTML = '';
        if (totalPaginas <= 1) return;

        const criar = (label, pagina, desabilitado = false) => {
            const btn = document.createElement('button');
            btn.textContent = label;
            btn.className = `px-3 py-1.5 text-xs font-bold rounded-lg border transition-all ${
                desabilitado
                    ? 'bg-blue-600 text-white border-blue-600 cursor-default'
                    : 'bg-white text-gray-600 border-gray-200 hover:border-blue-400 hover:text-blue-600'
            }`;
            if (!desabilitado) btn.addEventListener('click', () => executarBusca(termoAtual, pagina));
            return btn;
        };

        if (paginaCorrente > 1)      paginacao.appendChild(criar('← Anterior', paginaCorrente - 1));
        paginacao.appendChild(criar(`Pág. ${paginaCorrente}`, paginaCorrente, true));
        if (paginaCorrente < totalPaginas) paginacao.appendChild(criar('Próxima →', paginaCorrente + 1));
    }

    // ── Detalhe dos itens ───────────────────────────────────────────
    function verItens(cnpj, ano, sequencial, orgao, objeto) {
        mostrarLoading();

        fetch(`/admin/pncp/items/${cnpj}/${ano}/${sequencial}`)
            .then(r => r.json())
            .then(resp => {
                if (!resp.success) { mostrarErro(resp.message); return; }
                renderizarItens(resp.data, orgao, objeto, cnpj, ano, sequencial);
            })
            .catch(() => mostrarErro('Erro ao carregar itens. Tente novamente.'));
    }

    function renderizarItens(itens, orgao, objeto, cnpj, ano, sequencial) {
        lblContratacao.innerHTML = `
            <span class="font-bold text-gray-800">${orgao}</span>
            <span class="text-gray-400 mx-1">·</span>
            <span class="text-gray-500 text-sm">${objeto}</span>
            <span class="ml-2 text-[10px] text-gray-400">#${sequencial}/${ano}</span>`;

        if (!itens || itens.length === 0) {
            tabelaItens.innerHTML = `<tr><td colspan="6" class="px-4 py-8 text-center text-gray-400 text-sm">Nenhum item encontrado nesta contratação.</td></tr>`;
            mostrarDetalhe();
            return;
        }

        const fmt = v => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v || 0);

        tabelaItens.innerHTML = itens.map(item => {
            const valorUnit  = item.valorUnitarioHomologado ?? item.valorUnitarioEstimado ?? 0;
            const valorTotal = valorUnit * (item.quantidade || 0);
            const tipoLabel  = item.valorUnitarioHomologado
                ? '<span class="px-1.5 py-0.5 bg-green-50 text-green-700 text-[9px] font-bold rounded">Homologado</span>'
                : '<span class="px-1.5 py-0.5 bg-yellow-50 text-yellow-700 text-[9px] font-bold rounded">Estimado</span>';

            return `
            <tr class="hover:bg-blue-50/30 transition-colors">
                <td class="px-4 py-3 text-xs font-bold text-gray-400">${item.numeroItem}</td>
                <td class="px-4 py-3 text-sm text-gray-700 leading-tight max-w-xs">${item.descricao || '—'}</td>
                <td class="px-4 py-3 text-xs text-gray-500">${item.quantidade} <span class="opacity-60">${item.unidadeMedida || ''}</span></td>
                <td class="px-4 py-3 text-right">
                    <div class="font-bold text-green-600 text-sm">${fmt(valorUnit)}</div>
                    <div class="mt-0.5">${tipoLabel}</div>
                </td>
                <td class="px-4 py-3 text-right font-bold text-gray-700 text-sm">${fmt(valorTotal)}</td>
                <td class="px-4 py-3 text-xs text-gray-400">${item.nomeRazaoSocialFornecedor || item.nomeVencedor || '—'}</td>
            </tr>`;
        }).join('');

        mostrarDetalhe();
    }

    // ── Botão voltar ────────────────────────────────────────────────
    btnVoltarLista.addEventListener('click', function () {
        mostrarResultados();
    });

    // ── Botão aplicar filtros ────────────────────────────────────────
    document.getElementById('pp_btn_aplicar_filtros')?.addEventListener('click', function () {
        const termo = inputTermo.value.trim();
        if (termo.length >= 3) executarBusca(termo, 1);
    });

    // ── Toggle filtros avançados ─────────────────────────────────────
    document.getElementById('pp_btn_filtros')?.addEventListener('click', function () {
        const painel = document.getElementById('pp_painel_filtros');
        painel.classList.toggle('hidden');
        this.querySelector('span').textContent = painel.classList.contains('hidden') ? 'Filtros' : 'Ocultar';
    });

});
</script>
@endpush
