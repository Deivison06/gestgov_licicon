@extends('layouts.app')
@section('page-title', 'Gerenciar Itens do ETP')
@section('page-subtitle', 'Cadastro de itens que podem ser utilizados em Estudos Técnicos Preliminares por Lote')

@section('content')
<div class="py-8">
    <div class="flex justify-end mb-8 space-x-3">
        <button type="button" onclick="openModalImport()" class="inline-flex items-center gap-3 px-6 py-3 text-sm font-semibold text-[#009496] transition-all duration-200 bg-[#009496]/10 rounded-xl hover:bg-[#009496]/20 hover:scale-105">
            <i class="fas fa-file-excel text-lg"></i>
            Importar Excel
        </button>
        <button type="button" onclick="openModalPncp('main')" class="inline-flex items-center gap-3 px-6 py-3 text-sm font-semibold text-[#009496] transition-all duration-200 bg-[#009496]/10 rounded-xl hover:bg-[#009496]/20 hover:scale-105">
            <i class="fas fa-search text-lg"></i>
            Buscar no PNCP
        </button>
        <button type="button" onclick="openModalCreate()" class="inline-flex items-center gap-3 px-6 py-3 text-sm font-semibold text-white transition-all duration-200 bg-gradient-to-r from-[#052323] to-[#052323] rounded-xl hover:shadow-lg hover:scale-105">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Novo Item
        </button>
    </div>

    @if (session('success'))
    <div class="p-4 mb-8 border border-green-200 shadow-sm rounded-2xl bg-gradient-to-r from-green-50 to-emerald-50">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
        </div>
    </div>
    @endif
    @if (session('error'))
    <div class="p-4 mb-8 border border-red-200 shadow-sm rounded-2xl bg-gradient-to-r from-red-50 to-red-100">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
        </div>
    </div>
    @endif
    @if ($errors->any())
        <div class="p-4 mb-8 border border-red-200 shadow-sm rounded-2xl bg-gradient-to-r from-red-50 to-red-100">
            <ul class="ml-8 list-disc text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="w-full mb-6">
        <form method="GET" action="{{ route('admin.etp_itens.index') }}" class="flex items-center gap-3">
            <input 
                type="text" 
                name="descricao" 
                value="{{ $descricao ?? '' }}"
                placeholder="Buscar por descrição..."
                class="w-full rounded-xl border-gray-300 shadow-sm focus:border-[#009496] focus:ring-[#009496]"
            >

            <button 
                type="submit"
                class="px-5 py-2 bg-[#009496] text-white rounded-xl font-semibold hover:bg-[#007f7c] transition"
            >
                Buscar
            </button>

            @if(!empty($descricao))
                <a href="{{ route('admin.etp_itens.index') }}" 
                class="px-4 py-2 bg-gray-200 rounded-xl text-sm hover:bg-gray-300">
                    Limpar
                </a>
            @endif
        </form>
    </div>

    <div class="overflow-hidden transition-all duration-300 bg-white border border-gray-100 shadow-sm rounded-2xl flex flex-col items-start justify-between">
        <div class="w-full px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
            <h3 class="text-xl font-semibold text-gray-800">Catálogo de Itens</h3>
        </div>

        <div class="w-full overflow-x-auto">
            <table class="w-full overflow-hidden divide-y divide-gray-200 rounded-lg shadow-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Descrição do Item</th>
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-center text-gray-600 uppercase w-32">Unidade</th>
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-center text-gray-600 uppercase">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($itens as $item)
                    <tr class="transition-colors duration-200 hover:bg-gray-50/80">
                        <td class="px-4 py-3 text-sm text-gray-900 whitespace-pre-wrap">{{ $item->descricao_item }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500 text-center font-medium">{{ $item->unidade_medida ?? '—' }}</td>
                        <td class="px-4 py-3 text-center flex justify-center space-x-2">
                            <button type="button" 
                                    data-id="{{ $item->id }}" 
                                    data-descricao="{{ $item->descricao_item }}" 
                                    data-unidade="{{ $item->unidade_medida ?? '' }}"
                                    onclick="openModalEdit(this)" 
                                    class="inline-flex items-center justify-center w-8 h-8 text-indigo-600 transition-colors duration-200 rounded-md hover:bg-indigo-100 focus:outline-none" 
                                    title="Editar Item">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('admin.etp_itens.destroy', $item->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Tem certeza que deseja excluir este item?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 text-red-600 transition-colors duration-200 rounded-md hover:bg-red-100 focus:outline-none" title="Excluir Item">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-16 text-center text-gray-500">
                            <p class="text-sm font-medium text-gray-700">Nenhum item cadastrado no catálogo.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if ($itens->hasPages())
        <div class="w-full px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $itens->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Cadastrar -->
<div id="modal-create" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh]">
        <div class="p-6 border-b border-gray-200 bg-gray-50 flex justify-between items-center rounded-t-lg">
            <h3 class="text-xl font-bold text-gray-800">Novo Item</h3>
            <button onclick="closeModalCreate()" class="text-gray-400 hover:text-red-500 focus:outline-none transition-colors duration-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="{{ route('admin.etp_itens.store') }}" method="POST" class="overflow-y-auto w-full">
            @csrf
            <div class="p-6">
                <div class="mb-4">
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-sm font-medium leading-6 text-gray-900">Descrição do Item *</label>
                        <button type="button" onclick="openModalPncp('create')" class="text-xs text-[#009496] hover:underline flex items-center gap-1 font-semibold focus:outline-none">
                            <i class="fas fa-search"></i> Buscar no PNCP
                        </button>
                    </div>
                    <textarea name="descricao_item" id="create_descricao_item" rows="4" class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[#009496] sm:text-sm sm:leading-6" required></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium leading-6 text-gray-900 mb-2">Unidade de Medida</label>
                    <input type="text" name="unidade_medida" id="create_unidade_medida" placeholder="Ex: Unidade, Caixa, Kg, Metro" class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[#009496] sm:text-sm sm:leading-6">
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                <button type="button" onclick="closeModalCreate()" class="mr-4 text-sm text-gray-600 hover:text-gray-900 font-medium px-4 py-2 hover:bg-gray-200 rounded-md transition-colors duration-200">Cancelar</button>
                <button type="submit" class="rounded-md bg-[#009496] px-6 py-2 text-sm font-semibold text-white shadow-sm hover:focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#009496] transition-colors duration-200 flex justify-center whitespace-nowrap">Salvar Item</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar -->
<div id="modal-edit" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh]">
        <div class="p-6 border-b border-gray-200 bg-gray-50 flex justify-between items-center rounded-t-lg">
            <h3 class="text-xl font-bold text-gray-800">Editar Item</h3>
            <button onclick="closeModalEdit()" class="text-gray-400 hover:text-red-500 focus:outline-none transition-colors duration-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="form-edit" method="POST" class="overflow-y-auto w-full">
            @csrf
            @method('PUT')
            <div class="p-6">
                <div class="mb-4">
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-sm font-medium leading-6 text-gray-900">Descrição do Item *</label>
                        <button type="button" onclick="openModalPncp('edit')" class="text-xs text-[#009496] hover:underline flex items-center gap-1 font-semibold focus:outline-none">
                            <i class="fas fa-search"></i> Buscar no PNCP
                        </button>
                    </div>
                    <textarea name="descricao_item" id="input_descricao_item" rows="4" class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[#009496] sm:text-sm sm:leading-6" required></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium leading-6 text-gray-900 mb-2">Unidade de Medida</label>
                    <input type="text" name="unidade_medida" id="input_unidade_medida" placeholder="Ex: Unidade, Caixa, Kg, Metro" class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[#009496] sm:text-sm sm:leading-6">
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                <button type="button" onclick="closeModalEdit()" class="mr-4 text-sm text-gray-600 hover:text-gray-900 font-medium px-4 py-2 hover:bg-gray-200 rounded-md transition-colors duration-200">Cancelar</button>
                <button type="submit" class="rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 transition-colors duration-200 flex justify-center whitespace-nowrap">Atualizar Item</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Importar -->
<div id="modal-import" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh]">
        <div class="p-6 border-b border-gray-200 bg-gray-50 flex justify-between items-center rounded-t-lg">
            <h3 class="text-xl font-bold text-gray-800">Importar Itens do Excel</h3>
            <button onclick="closeModalImport()" class="text-gray-400 hover:text-red-500 focus:outline-none transition-colors duration-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="{{ route('admin.etp_itens.importar_excel') }}" method="POST" enctype="multipart/form-data" class="overflow-y-auto w-full">
            @csrf
            <div class="p-6">
                <p class="text-sm text-gray-600 mb-4">
                    Faça o upload de uma planilha contendo os itens. A primeira coluna deve conter a detalhada <strong>Descrição do Item</strong>. A primeira linha será ignorada caso detectada como cabeçalho.
                </p>
                <div class="mb-4">
                    <label class="block text-sm font-medium leading-6 text-gray-900 mb-2">Arquivo (.xlsx, .xls, .csv)</label>
                    <input type="file" name="arquivo_excel" accept=".xlsx,.xls,.csv" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#009496]/10 file:text-[#009496] hover:file:bg-[#009496]/20" required>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                <button type="button" onclick="closeModalImport()" class="mr-4 text-sm text-gray-600 hover:text-gray-900 font-medium px-4 py-2 hover:bg-gray-200 rounded-md transition-colors duration-200">Cancelar</button>
                <button type="submit" class="rounded-md bg-[#009496] px-6 py-2 text-sm font-semibold text-white shadow-sm hover:focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#009496] transition-colors duration-200 flex items-center justify-center whitespace-nowrap">
                    <i class="fas fa-upload mt-0.5 mr-2"></i> Importar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Buscar no PNCP -->
<div id="modal-pncp-search" class="fixed inset-0 z-[60] flex items-center justify-center hidden bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <div class="p-6 border-b border-gray-200 bg-gray-50 flex justify-between items-center rounded-t-lg">
            <div>
                <h3 class="text-xl font-bold text-gray-800">Buscar no PNCP</h3>
                <p class="text-xs text-gray-500 mt-1">Busque descrições oficiais diretamente no Portal Nacional de Contratações Públicas</p>
            </div>
            <button onclick="closeModalPncp()" class="text-gray-400 hover:text-red-500 focus:outline-none transition-colors duration-200">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        
        <div class="p-6 flex flex-col flex-1 overflow-hidden">
            <!-- Barra de Busca -->
            <div class="flex gap-2 mb-4">
                <input 
                    type="text" 
                    id="pncp-modal-search-input" 
                    placeholder="Digite o nome do item para buscar... (mínimo 3 caracteres)"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#009496] focus:ring-[#009496] text-sm"
                >
                <button 
                    type="button" 
                    id="pncp-modal-search-btn"
                    class="px-5 py-2 bg-[#009496] text-white rounded-md font-semibold hover:bg-[#007f7c] transition text-sm flex items-center gap-2 whitespace-nowrap"
                >
                    <i class="fas fa-search"></i> Buscar
                </button>
            </div>

            <!-- Mensagem de Erro/Alerta -->
            <div id="pncp-modal-alert" class="hidden p-3 mb-4 text-sm rounded-lg"></div>

            <!-- Área de Resultados (Scrollable) -->
            <div class="flex-1 overflow-y-auto border border-gray-100 rounded-lg p-2 bg-gray-50/50 min-h-[250px]" id="pncp-modal-results-container">
                <!-- Estado Inicial -->
                <div class="flex flex-col items-center justify-center h-full py-16 text-gray-400" id="pncp-modal-placeholder">
                    <div class="p-4 bg-white rounded-full shadow-sm mb-3">
                        <i class="fas fa-cloud text-3xl text-[#009496]/40"></i>
                    </div>
                    <p class="text-sm font-medium">Digite um termo para iniciar a busca no PNCP</p>
                    <p class="text-xs text-gray-400 mt-1">Ex: Papel sulfite, Cesta básica, Pintura predial</p>
                </div>

                <!-- Loading Spinner -->
                <div class="hidden flex-col items-center justify-center h-full py-16" id="pncp-modal-loading">
                    <div class="w-10 h-10 border-4 border-[#009496]/20 border-t-[#009496] rounded-full animate-spin mb-3"></div>
                    <p class="text-sm text-gray-500 animate-pulse" id="pncp-modal-loading-msg">Consultando contratações...</p>
                </div>

                <!-- Lista de itens encontrados -->
                <div class="hidden space-y-3" id="pncp-modal-results-list"></div>
            </div>
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between items-center">
            <span class="text-xs text-gray-500 font-semibold" id="pncp-modal-results-count"></span>
            <button type="button" onclick="closeModalPncp()" class="text-sm text-gray-600 hover:text-gray-900 font-medium px-4 py-2 hover:bg-gray-200 rounded-md transition-colors duration-200">Fechar</button>
        </div>
    </div>
</div>

<script>
    function openModalCreate() {
        document.getElementById('modal-create').classList.remove('hidden');
    }
    
    function closeModalCreate() {
        document.getElementById('modal-create').classList.add('hidden');
    }

    function openModalEdit(button) {
        const id = button.dataset.id;
        const descricao = button.dataset.descricao;
        const unidade = button.dataset.unidade;

        document.getElementById('modal-edit').classList.remove('hidden');
        document.getElementById('input_descricao_item').value = descricao;
        document.getElementById('input_unidade_medida').value = unidade;
        document.getElementById('form-edit').action = '/admin/etp-itens/' + id;
    }
    
    function closeModalEdit() {
        document.getElementById('modal-edit').classList.add('hidden');
    }

    function openModalImport() {
        document.getElementById('modal-import').classList.remove('hidden');
    }
    
    function closeModalImport() {
        document.getElementById('modal-import').classList.add('hidden');
    }

    // --- Integração com PNCP ---
    let activePncpTarget = 'main';
    let pncpDescriptionsCache = [];
    let currentPncpTerm = '';
    let currentPncpPage = 1;

    function openModalPncp(target = 'main') {
        activePncpTarget = target;
        document.getElementById('modal-pncp-search').classList.remove('hidden');
        document.getElementById('pncp-modal-search-input').focus();
    }

    function closeModalPncp() {
        document.getElementById('modal-pncp-search').classList.add('hidden');
        document.getElementById('pncp-modal-search-input').value = '';
        document.getElementById('pncp-modal-results-list').innerHTML = '';
        document.getElementById('pncp-modal-results-list').classList.add('hidden');
        document.getElementById('pncp-modal-placeholder').classList.remove('hidden');
        document.getElementById('pncp-modal-loading').classList.add('hidden');
        document.getElementById('pncp-modal-alert').classList.add('hidden');
        document.getElementById('pncp-modal-results-count').textContent = '';
        pncpDescriptionsCache = [];
    }

    function highlightText(text, term) {
        if (!term || !text) return text || '';
        const escapedTerm = term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const re = new RegExp(`(${escapedTerm})`, 'gi');
        return text.replace(re, '<mark class="bg-yellow-200 text-yellow-900 rounded px-0.5">$1</mark>');
    }

    async function executePncpSearch(pagina = 1) {
        const input = document.getElementById('pncp-modal-search-input');
        const termo = input.value.trim();

        if (termo.length < 3) {
            showPncpAlert('Por favor, insira pelo menos 3 caracteres para pesquisar.', 'warning');
            return;
        }

        currentPncpTerm = termo;
        currentPncpPage = pagina;

        showPncpLoading('Buscando contratações no PNCP...');
        pncpDescriptionsCache = [];

        try {
            const searchUrl = `{{ route('admin.pncp.mercado.search') }}?termo=${encodeURIComponent(termo)}&pagina=${pagina}`;
            const response = await fetch(searchUrl);
            const json = await response.json();

            if (!json.success) {
                showPncpAlert(json.message || 'Erro ao consultar o PNCP.', 'danger');
                return;
            }

            const contratacoes = json.data;
            if (!contratacoes.data || contratacoes.data.length === 0) {
                showPncpAlert('Nenhuma contratação encontrada para o termo informado.', 'info');
                return;
            }

            showPncpLoading(`Carregando itens de ${contratacoes.data.length} contratação(ões)...`);

            // Busca os itens de todas as contratações em paralelo
            const itemRequests = contratacoes.data.map(async (c) => {
                try {
                    const itemsUrl = `/admin/pncp/items/${c.orgaoEntidade.cnpj}/${c.anoCompra}/${c.sequencialCompra}`;
                    const res = await fetch(itemsUrl);
                    const itemJson = await res.json();
                    return {
                        contratacao: c,
                        itens: itemJson.success ? itemJson.data : []
                    };
                } catch (e) {
                    console.warn(`Erro ao buscar itens de ${c.sequencialCompra}:`, e);
                    return { contratacao: c, itens: [] };
                }
            });

            const results = await Promise.all(itemRequests);
            const allItems = [];

            results.forEach(res => {
                if (res.itens && Array.isArray(res.itens)) {
                    res.itens.forEach(item => {
                        allItems.push({
                            item: item,
                            contratacao: res.contratacao
                        });
                    });
                }
            });

            renderPncpResults(allItems, contratacoes);

        } catch (error) {
            console.error('[PNCP] Erro na busca:', error);
            showPncpAlert('Falha de conexão ao buscar dados do PNCP. Tente novamente.', 'danger');
        }
    }

    function showPncpAlert(message, type = 'warning') {
        const alertEl = document.getElementById('pncp-modal-alert');
        alertEl.classList.remove('hidden', 'bg-yellow-50', 'text-yellow-800', 'border-yellow-200', 'bg-red-50', 'text-red-800', 'border-red-200', 'bg-blue-50', 'text-blue-800', 'border-blue-200');
        
        let colorClasses = '';
        if (type === 'danger') {
            colorClasses = 'bg-red-50 text-red-800 border border-red-200';
        } else if (type === 'info') {
            colorClasses = 'bg-blue-50 text-blue-800 border border-blue-200';
        } else {
            colorClasses = 'bg-yellow-50 text-yellow-800 border border-yellow-200';
        }

        alertEl.className = `p-3 mb-4 text-sm rounded-lg ${colorClasses}`;
        alertEl.textContent = message;
        
        document.getElementById('pncp-modal-loading').classList.add('hidden');
        document.getElementById('pncp-modal-placeholder').classList.add('hidden');
        document.getElementById('pncp-modal-results-list').classList.add('hidden');
    }

    function showPncpLoading(message) {
        document.getElementById('pncp-modal-alert').classList.add('hidden');
        document.getElementById('pncp-modal-placeholder').classList.add('hidden');
        document.getElementById('pncp-modal-results-list').classList.add('hidden');
        
        const loadingEl = document.getElementById('pncp-modal-loading');
        document.getElementById('pncp-modal-loading-msg').textContent = message;
        loadingEl.classList.remove('hidden');
        loadingEl.classList.add('flex');
    }

    function renderPncpResults(allItems, meta) {
        const resultsList = document.getElementById('pncp-modal-results-list');
        resultsList.innerHTML = '';

        document.getElementById('pncp-modal-loading').classList.add('hidden');
        document.getElementById('pncp-modal-loading').classList.remove('flex');

        if (allItems.length === 0) {
            showPncpAlert('As contratações encontradas não possuem itens com descrição disponível.', 'info');
            return;
        }

        const totalItems = allItems.length;
        const totalContratacoes = meta.totalRegistros || 0;
        document.getElementById('pncp-modal-results-count').textContent = 
            `${totalContratacoes.toLocaleString('pt-BR')} contratação(ões) · ${totalItems} item(ns) encontrado(s)`;

        // Adiciona cards à lista
        allItems.forEach((entry, idx) => {
            const item = entry.item;
            const c = entry.contratacao;
            const descricao = item.descricao || '(Sem descrição)';
            
            pncpDescriptionsCache.push({
                descricao: descricao,
                unidade: item.unidadeMedida || ''
            });
            const cacheIndex = pncpDescriptionsCache.length - 1;

            const card = document.createElement('div');
            card.className = 'bg-white border border-gray-100 rounded-xl p-4 shadow-sm hover:border-[#009496]/40 transition duration-200 flex flex-col justify-between';
            
            const tipo = item.tipoItem || (item.materialOuServicoNome) || '';
            const isMaterial = tipo.toLowerCase().includes('material') || tipo === 'M';
            const badgeClass = isMaterial ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-purple-50 text-purple-700 border-purple-200';
            const badgeLabel = tipo || 'Item';

            card.innerHTML = `
                <div class="flex justify-between items-start gap-4 mb-3">
                    <p class="text-sm font-medium text-gray-800 flex-1 leading-relaxed">${highlightText(descricao, currentPncpTerm)}</p>
                    <button 
                        type="button" 
                        onclick="selectPncpDescription(${cacheIndex})" 
                        class="px-4 py-2 text-xs font-bold text-white bg-[#009496] hover:bg-[#007f7c] rounded-lg transition-all duration-200 shadow-sm hover:shadow whitespace-nowrap"
                    >
                        Selecionar
                    </button>
                </div>
                <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-gray-500 border-t border-gray-50 pt-2">
                    <span class="px-2 py-0.5 rounded-full font-bold text-[9px] uppercase tracking-wide border ${badgeClass}">
                        ${badgeLabel}
                    </span>
                    <span class="font-medium text-gray-700 truncate max-w-[200px]" title="${c.orgaoEntidade.razaoSocial}">${c.orgaoEntidade.razaoSocial}</span>
                    <span class="text-gray-300">|</span>
                    <span class="font-semibold text-gray-600">${c.uf || ''}</span>
                    <span>${c.municipio || ''}</span>
                    <span class="text-gray-300">|</span>
                    <span>Qtd: ${item.quantidade ?? '?'} ${item.unidadeMedida || ''}</span>
                </div>
            `;
            resultsList.appendChild(card);
        });

        if (meta.totalPaginas > 1) {
            const paginationContainer = document.createElement('div');
            paginationContainer.className = 'flex justify-center items-center gap-2 pt-4 border-t border-gray-100 mt-4';
            
            const btnClass = 'px-3 py-1.5 text-xs font-bold rounded-lg border transition-all';
            
            if (currentPncpPage > 1) {
                const prevBtn = document.createElement('button');
                prevBtn.type = 'button';
                prevBtn.className = `${btnClass} bg-white text-gray-600 border-gray-200 hover:border-[#009496] hover:text-[#009496]`;
                prevBtn.innerHTML = '← Anterior';
                prevBtn.onclick = () => executePncpSearch(currentPncpPage - 1);
                paginationContainer.appendChild(prevBtn);
            }

            const pageIndicator = document.createElement('span');
            pageIndicator.className = 'text-xs text-gray-500 font-semibold px-2';
            pageIndicator.textContent = `Pág. ${currentPncpPage} / ${meta.totalPaginas}`;
            paginationContainer.appendChild(pageIndicator);

            if (currentPncpPage < meta.totalPaginas) {
                const nextBtn = document.createElement('button');
                nextBtn.type = 'button';
                nextBtn.className = `${btnClass} bg-white text-gray-600 border-gray-200 hover:border-[#009496] hover:text-[#009496]`;
                nextBtn.innerHTML = 'Próxima →';
                nextBtn.onclick = () => executePncpSearch(currentPncpPage + 1);
                paginationContainer.appendChild(nextBtn);
            }

            resultsList.appendChild(paginationContainer);
        }

        resultsList.classList.remove('hidden');
    }

    function selectPncpDescription(cacheIndex) {
        const entry = pncpDescriptionsCache[cacheIndex];
        if (!entry) return;

        if (activePncpTarget === 'create') {
            document.getElementById('create_descricao_item').value = entry.descricao;
            if (entry.unidade) {
                document.getElementById('create_unidade_medida').value = entry.unidade;
            }
        } else if (activePncpTarget === 'edit') {
            document.getElementById('input_descricao_item').value = entry.descricao;
            if (entry.unidade) {
                document.getElementById('input_unidade_medida').value = entry.unidade;
            }
        } else if (activePncpTarget === 'main') {
            document.getElementById('create_descricao_item').value = entry.descricao;
            if (entry.unidade) {
                document.getElementById('create_unidade_medida').value = entry.unidade;
            }
            openModalCreate();
        }

        closeModalPncp();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('pncp-modal-search-input');
        if (searchInput) {
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    executePncpSearch(1);
                }
            });
        }
        
        const searchBtn = document.getElementById('pncp-modal-search-btn');
        if (searchBtn) {
            searchBtn.addEventListener('click', function() {
                executePncpSearch(1);
            });
        }
    });
</script>
@endsection
