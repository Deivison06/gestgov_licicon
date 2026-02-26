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
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">ID</th>
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase">Descrição do Item</th>
                        <th class="px-4 py-3 text-xs font-semibold tracking-wider text-center text-gray-600 uppercase">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($itens as $item)
                    <tr class="transition-colors duration-200 hover:bg-gray-50/80">
                        <td class="px-4 py-3 text-sm text-gray-900 font-mono">{{ $item->id }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $item->descricao_item }}</td>
                        <td class="px-4 py-3 text-center flex justify-center space-x-2">
                            <button type="button" onclick="openModalEdit({{ $item->id }}, '{{ addslashes($item->descricao_item) }}')" class="inline-flex items-center justify-center w-8 h-8 text-indigo-600 transition-colors duration-200 rounded-md hover:bg-indigo-100 focus:outline-none" title="Editar Item">
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
                    <label class="block text-sm font-medium leading-6 text-gray-900 mb-2">Descrição do Item *</label>
                    <input type="text" name="descricao_item" class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[#009496] sm:text-sm sm:leading-6" required>
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
                    <label class="block text-sm font-medium leading-6 text-gray-900 mb-2">Descrição do Item *</label>
                    <input type="text" name="descricao_item" id="input_descricao_item" class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[#009496] sm:text-sm sm:leading-6" required>
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

<script>
    function openModalCreate() {
        document.getElementById('modal-create').classList.remove('hidden');
    }
    
    function closeModalCreate() {
        document.getElementById('modal-create').classList.add('hidden');
    }

    function openModalEdit(id, descricao) {
        document.getElementById('modal-edit').classList.remove('hidden');
        document.getElementById('input_descricao_item').value = descricao;
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
</script>
@endsection
