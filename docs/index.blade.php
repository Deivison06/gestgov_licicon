@extends('layouts.app')

@section('page-title', 'Importação de Contratos')
@section('page-subtitle', 'Integração com Sistema de Licitações')

@section('content')

{{-- Mensagens de Feedback --}}
@if(session('success'))
<div class="flex items-center p-4 mb-6 text-green-700 border border-green-200 rounded-lg bg-green-50">
    <i class="fas fa-check-circle w-5 h-5 mr-2"></i>
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="flex items-center p-4 mb-6 text-red-700 border border-red-200 rounded-lg bg-red-50">
    <i class="fas fa-exclamation-circle w-5 h-5 mr-2"></i>
    {{ session('error') }}
</div>
@endif

{{-- Card Principal --}}
<div class="overflow-hidden bg-white shadow-sm rounded-xl">

    {{-- Cabeçalho do Card --}}
    <div class="px-6 py-4 border-b border-gray-100 bg-[#dafafa] flex items-center justify-between">
        <h3 class="text-2xl font-bold text-gray-700">Contratos Disponíveis na Licitação</h3>

        <a href="{{ route('contratos.index') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-gray-700 transition-all duration-200 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 hover:shadow-sm">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    {{-- Filtros de Pesquisa --}}
    <div class="p-6 border-b border-gray-100 bg-gray-50/50">
        <form action="{{ route('contratos.importacao.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            <div>
                <label for="pesquisa" class="block mb-1 text-xs font-bold text-gray-700 uppercase tracking-wide">Processo / Contrato / Objeto</label>
                <input type="text" name="pesquisa" id="pesquisa" value="{{ request('pesquisa') }}" placeholder="Buscar por número ou objeto..." 
                    class="block w-full border-gray-300 rounded-lg focus:ring-cyan-500 focus:border-cyan-500 text-sm">
            </div>

            <div>
                <label for="prefeitura_nome" class="block mb-1 text-xs font-bold text-gray-700 uppercase tracking-wide">Prefeitura</label>
                <select name="prefeitura_nome" id="prefeitura_nome" class="block w-full border-gray-300 rounded-lg focus:ring-cyan-500 focus:border-cyan-500 text-sm">
                    <option value="">Todas</option>
                    @foreach ($listaPrefeituras as $pref)
                        <option value="{{ $pref }}" {{ request('prefeitura_nome') == $pref ? 'selected' : '' }}>{{ $pref }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="fornecedor" class="block mb-1 text-xs font-bold text-gray-700 uppercase tracking-wide">Fornecedor</label>
                <input type="text" name="fornecedor" id="fornecedor" value="{{ request('fornecedor') }}" placeholder="Nome ou CNPJ..." 
                    class="block w-full border-gray-300 rounded-lg focus:ring-cyan-500 focus:border-cyan-500 text-sm">
            </div>

            <div>
                <label for="situacao" class="block mb-1 text-xs font-bold text-gray-700 uppercase tracking-wide">Situação</label>
                <select name="situacao" id="situacao" class="block w-full border-gray-300 rounded-lg focus:ring-cyan-500 focus:border-cyan-500 text-sm">
                    <option value="">Todas</option>
                    <option value="pendente" {{ request('situacao') == 'pendente' ? 'selected' : '' }}>Pendente de Importação</option>
                    <option value="importado" {{ request('situacao') == 'importado' ? 'selected' : '' }}>Importado</option>
                </select>
            </div>

            <div class="lg:col-span-full flex justify-end gap-2 mt-2">
                <a href="{{ route('contratos.importacao.index') }}" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Limpar</a>
                <button type="submit" class="px-6 py-2 text-sm font-bold text-white bg-cyan-600 rounded-lg hover:bg-cyan-700 shadow-sm transition-colors">
                    <i class="fas fa-search mr-1"></i> Filtrar
                </button>
            </div>
        </form>
    </div>

    {{-- Conteúdo da Tabela --}}
    <div class="overflow-x-auto">
        <div class="inline-block min-w-full align-middle">
            <table class="min-w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 bg-white text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-4">Prefeitura</th>
                        <th class="px-6 py-4">Processo / Origem</th>
                        <th class="px-6 py-4">Fornecedor (Vencedor)</th>
                        <th class="px-6 py-4">Objeto</th>
                        <th class="px-6 py-4 text-right">Valor Total</th>
                        <th class="px-6 py-4 text-center">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @forelse($contratosExternos as $contrato)
                        @php
                            $opacity = $contrato['ja_importado'] ? 'opacity-60 bg-gray-50' : 'hover:bg-gray-5';
                        @endphp

                        <tr class="transition-colors duration-150 border-b border-gray-100 group {{ $opacity }}">

                            {{-- Prefeitura --}}
                            <td class="px-6 pt-5 pb-3 text-sm text-gray-900 align-top">
                                <div class="mb-3 pb-2 border-b border-gray-100 max-w-[220px]">
                                    <div class="text-xs font-bold text-cyan-700 uppercase tracking-wide whitespace-normal leading-tight">
                                        <i class="fas fa-building mr-1"></i>
                                        {{ $contrato['prefeitura_nome'] ?? 'Origem Desconhecida' }}
                                    </div>
                                    <div class="text-[10px] text-gray-400 font-mono mt-1">
                                        CNPJ: {{ $contrato['prefeitura_cnpj'] ?? '---' }}
                                    </div>
                                </div>
                            </td>

                            {{-- Processo --}}
                            <td class="px-6 pt-5 pb-3 whitespace-nowrap text-sm text-gray-900">
                                <div class="font-bold text-gray-800">Proc: {{ $contrato['numero_processo'] }}</div>
                                <div class="text-xs text-gray-500">
                                    Contr: {{ $contrato['numero_contrato'] ?? 'S/N' }}
                                </div>
                            </td>

                            {{-- Fornecedor --}}
                            <td class="px-6 pt-5 pb-3 whitespace-nowrap text-sm text-gray-900">
                                <div class="font-medium text-gray-900 max-w-[200px] truncate" title="{{ $contrato['fornecedor']['razao_social'] }}">
                                    {{ $contrato['fornecedor']['razao_social'] }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    CNPJ: {{ $contrato['fornecedor']['cnpj'] }}
                                </div>
                            </td>

                            {{-- Objeto --}}
                            <td class="px-6 pt-5 pb-3">
                                <div class="max-w-[250px] text-sm text-gray-600 truncate"
                                    title="{{ strip_tags(html_entity_decode($contrato['objeto'])) }}">
                                    {!! Str::limit(strip_tags(html_entity_decode($contrato['objeto'])), 50) !!}
                                </div>
                            </td>

                            {{-- Valor --}}
                            <td class="px-6 pt-5 pb-3 whitespace-nowrap text-right">
                                <span class="font-bold text-gray-800">
                                    R$ {{ number_format($contrato['valor_total_vencedor'], 2, ',', '.') }}
                                </span>
                                <div class="text-xs text-gray-500">
                                    {{ count($contrato['itens']) }} Itens
                                </div>
                            </td>

                            {{-- Ações (Dois Botões) --}}
                            <td class="px-6 pt-5 pb-3 whitespace-nowrap text-center">
                                @if($contrato['ja_importado'])
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-green-700 bg-green-100 rounded-lg">
                                        <i class="fas fa-check"></i> Importado
                                    </span>
                                @else
                                    <div class="flex items-center justify-center gap-2">
                                        {{-- Botão Visualizar (Olho) --}}
                                        <button type="button"
                                                onclick='abrirModalVisualizar(@json($contrato))'
                                                class="p-2 text-blue-600 transition-colors duration-200 bg-blue-50 rounded-lg hover:bg-blue-100 hover:text-blue-800"
                                                title="Ver Detalhes e Itens">
                                            <i class="fas fa-eye fa-lg"></i>
                                        </button>

                                        {{-- Botão Importar (Download) --}}
                                        <button type="button"
                                                onclick='abrirModalImportacao(@json($contrato))'
                                                class="p-2 text-white transition-all duration-200 bg-cyan-600 rounded-lg hover:bg-cyan-700 shadow-sm hover:shadow-md hover:-translate-y-0.5"
                                                title="Importar Contrato">
                                            <i class="fas fa-file-import"></i>
                                        </button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-cloud-download-alt text-4xl text-gray-300 mb-3"></i>
                                    <p class="font-medium">Nenhum contrato disponível para importação.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($contratosExternos->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            {{ $contratosExternos->links() }}
        </div>
    @endif
</div>

{{-- ======================================================================== --}}
{{-- MODAL 1: VISUALIZAR DETALHES (Somente Leitura) --}}
{{-- ======================================================================== --}}
<div id="modalVisualizar" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-view-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-60 backdrop-blur-sm" onclick="fecharModalVisualizar()"></div>

    <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
        <div class="relative overflow-hidden text-left transition-all transform bg-white shadow-2xl rounded-xl sm:my-8 sm:w-full sm:max-w-4xl border border-gray-100">

            {{-- Cabeçalho Visualizar --}}
            <div class="px-6 py-4 bg-blue-50 border-b border-blue-100 flex justify-between items-center">
                <div>
                    <h3 class="text-xl font-bold text-blue-900 flex items-center gap-2">
                        <i class="fas fa-eye text-blue-600"></i>
                        Detalhes do Contrato
                    </h3>
                </div>
                <button type="button" onclick="fecharModalVisualizar()" class="text-gray-400 hover:text-blue-600 transition-colors">
                    <i class="fas fa-times fa-lg"></i>
                </button>
            </div>

            <div class="px-6 py-6 space-y-6">
                {{-- Resumo em Grid --}}
                <div class="bg-white rounded-lg p-4 border border-gray-200 shadow-sm">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <span class="text-xs font-semibold text-gray-400 uppercase">Processo / Modalidade</span>
                            <div class="flex flex-wrap items-center gap-2 mt-1">
                                <div class="font-bold text-gray-800 text-sm" id="viewProcesso">---</div>

                                <div id="viewBadgeTipo"></div>
                            </div>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-gray-400 uppercase">Fornecedor</span>
                            <div class="font-bold text-gray-800 text-sm truncate" id="viewFornecedor">---</div>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-semibold text-gray-400 uppercase">Valor Total</span>
                            <div class="font-bold text-green-700 text-lg" id="viewValor">R$ 0,00</div>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-100">
                         <span class="text-xs font-semibold text-gray-400 uppercase">Objeto</span>
                         <p class="text-sm text-gray-600 italic" id="viewObjeto">---</p>
                    </div>
                </div>

                {{-- Tabela de Itens (Com Scroll) --}}
                <div>
                    <h4 class="text-sm font-bold text-gray-700 mb-2 flex justify-between items-center">
                        <span>Itens do Contrato</span>
                        <span class="text-xs font-normal bg-gray-100 px-2 py-1 rounded text-gray-600" id="viewQtdItens">0 itens</span>
                    </h4>

                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <div class="overflow-y-auto max-h-80 custom-scrollbar">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 sticky top-0 z-10">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase w-16">Lote</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Descrição</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase w-24">Qtd</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase w-28">V. Unit</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase w-28">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 text-sm" id="viewTabelaBody">
                                    {{-- JS preenche aqui --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Rodapé Visualizar --}}
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                <button type="button" onclick="fecharModalVisualizar()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100">
                    Fechar Visualização
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ======================================================================== --}}
{{-- MODAL 2: IMPORTAR (Ação e Formulário) --}}
{{-- ======================================================================== --}}
<div id="modalImportar" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-import-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-60 backdrop-blur-sm" onclick="fecharModalImportacao()"></div>

    <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
        {{-- Modal mais estreito para focar na ação --}}
        <div class="relative overflow-hidden text-left transition-all transform bg-white shadow-xl rounded-xl sm:my-8 sm:w-full sm:max-w-lg border border-gray-100">

            <form action="{{ route('contratos.importacao.store') }}" method="POST">
                @csrf
                <input type="hidden" name="dados_contrato" id="inputDadosContrato">

                <div class="px-6 py-4 bg-[#dafafa] border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-file-import text-cyan-700"></i>
                        Confirmar Importação
                    </h3>
                    <button type="button" onclick="fecharModalImportacao()" class="text-gray-400 hover:text-red-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="px-6 py-6">
                    <p class="text-sm text-gray-600 mb-4">
                        Você está importando o Processo <strong id="impProcesso" class="text-gray-900"></strong>.
                    </p>

                    <div class="p-4 mb-5 rounded-lg bg-cyan-50 border border-cyan-100">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-cyan-600"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-bold text-cyan-800">Vínculo de Secretaria</h3>
                                <div class="mt-1 text-xs text-cyan-700">
                                    <p>Origem: <span class="font-bold uppercase" id="impOrigem">---</span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="secretaria_id" class="block text-sm font-bold text-gray-700">
                            Selecione a Secretaria de Destino <span class="text-red-500">*</span>
                        </label>
                        <select name="secretaria_id" id="secretaria_id" required
                            class="block w-full px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg focus:ring-cyan-500 focus:border-cyan-500 shadow-sm appearance-none">
                            <option value="">-- Carregando sugestões... --</option>
                        </select>
                        <p class="text-xs text-gray-500">O sistema sugeriu secretarias baseadas no CNPJ da prefeitura.</p>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                    <button type="button" onclick="fecharModalImportacao()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100">
                        Cancelar
                    </button>
                    <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-bold text-white bg-cyan-600 rounded-lg hover:bg-cyan-700 shadow-md">
                        <i class="fas fa-check mr-2"></i> Importar Agora
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Scripts --}}
<script>
    const mapaSecretarias = @json($mapaSecretarias);

    const formatadorBRL = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });

    // Mapeamento manual das modalidades
    const mapaModalidades = {
        1: 'Concorrência',
        2: 'Dispensa',
        3: 'Inexigibilidade',
        4: 'Pregão Eletrônico',
    };

    function getModalidadeLabel(valor) {
        if (isNaN(valor)) return valor;
        return mapaModalidades[valor] || 'Outra Modalidade';
    }

    // --- LÓGICA DO MODAL VISUALIZAR (Botão Olho) ---
function abrirModalVisualizar(dados) {
        const modalidadeNome = getModalidadeLabel(dados.modalidade);
        const tipo = dados.tipo_contratacao; // Ex: "Por Lote", "Por Item"

        // 1. Processo e Modalidade (Texto Base)
        document.getElementById('viewProcesso').innerText = `${dados.numero_processo} (${modalidadeNome})`;

        // 2. Badge de Tipo de Contratação (Design Melhorado)
        const elBadge = document.getElementById('viewBadgeTipo');
        elBadge.innerHTML = ''; // Limpa

        if (tipo) {
            // Define cores baseadas no tipo para facilitar identificação visual
            let cores = 'bg-gray-100 text-gray-600 border-gray-200'; // Padrão
            let icone = 'fa-file-contract';

            if (tipo === 'Por Item') {
                cores = 'bg-purple-50 text-purple-700 border-purple-100 ring-1 ring-purple-100';
                icone = 'fa-list-ol';
            } else if (tipo === 'Por Lote') {
                cores = 'bg-blue-50 text-blue-700 border-blue-100 ring-1 ring-blue-100';
                icone = 'fa-cubes';
            } else if (tipo === 'Global') {
                cores = 'bg-green-50 text-green-700 border-green-100 ring-1 ring-green-100';
                icone = 'fa-globe';
            }

            elBadge.innerHTML = `
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-medium border ${cores}">
                    <i class="fas ${icone} text-[10px] opacity-70"></i> ${tipo}
                </span>
            `;
        }

        // 3. Fornecedor (Limpo, sem o append anterior)
        document.getElementById('viewFornecedor').innerText = dados.fornecedor.razao_social;
        // Adiciona tooltip caso o nome seja muito grande
        document.getElementById('viewFornecedor').title = dados.fornecedor.razao_social;

        // 4. Valores e Objeto
        document.getElementById('viewValor').innerText = formatadorBRL.format(dados.valor_total_vencedor);

        let div = document.createElement("div");
        div.innerHTML = dados.objeto || '';
        document.getElementById('viewObjeto').innerText = div.textContent || div.innerText || "";

        // 5. Tabela de Itens
        const tbody = document.getElementById('viewTabelaBody');
        tbody.innerHTML = '';
        const itens = dados.itens || [];
        document.getElementById('viewQtdItens').innerText = `${itens.length} itens`;

        if (itens.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="px-4 py-4 text-center text-gray-500">Sem itens.</td></tr>';
        } else {
            itens.forEach(item => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-gray-50';
                tr.innerHTML = `
                    <td class="px-4 py-2 whitespace-nowrap text-gray-500 font-mono text-xs text-center border-r border-gray-100 bg-gray-50/50">${item.lote_numero}</td>
                    <td class="px-4 py-2 text-gray-700 text-xs"><div class="line-clamp-2" title="${item.descricao}">${item.descricao}</div></td>
                    <td class="px-4 py-2 whitespace-nowrap text-right text-gray-700 text-xs font-mono">${parseFloat(item.quantidade).toLocaleString('pt-BR')} ${item.unidade}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-right text-gray-700 text-xs font-mono text-gray-500">${formatadorBRL.format(item.valor_unitario)}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-right font-semibold text-gray-800 text-xs font-mono bg-gray-50/30">${formatadorBRL.format(item.valor_total)}</td>
                `;
                tbody.appendChild(tr);
            });
        }

        document.getElementById('modalVisualizar').classList.remove('hidden');
    }

    function fecharModalVisualizar() {
        document.getElementById('modalVisualizar').classList.add('hidden');
    }

    // --- LÓGICA DO MODAL IMPORTAR (Botão Importar) ---
    function abrirModalImportacao(dados) {
        document.getElementById('inputDadosContrato').value = JSON.stringify(dados);

        // No modal de importar, mantemos simples, mas indicamos o processo
        document.getElementById('impProcesso').innerText = dados.numero_processo;

        document.getElementById('impOrigem').innerText = dados.contratante_origem || 'NÃO INFORMADO';

        const select = document.getElementById('secretaria_id');
        select.innerHTML = '<option value="">-- Selecione a Secretaria --</option>';

        const cnpjPrefeitura = dados.prefeitura_cnpj.replace(/\D/g, '');

        if (mapaSecretarias[cnpjPrefeitura]) {
            mapaSecretarias[cnpjPrefeitura].forEach(sec => {
                const option = document.createElement('option');
                option.value = sec.id;
                option.textContent = sec.nome;
                select.appendChild(option);
            });
        } else {
            const option = document.createElement('option');
            option.textContent = "Nenhuma secretaria encontrada para este CNPJ";
            option.disabled = true;
            select.appendChild(option);
        }

        document.getElementById('modalImportar').classList.remove('hidden');
    }

    function fecharModalImportacao() {
        document.getElementById('modalImportar').classList.add('hidden');
        document.getElementById('secretaria_id').value = "";
    }
</script>
@endsection
