@extends('layouts.app')
@section('page-title', 'Gestão de Prefeituras')
@section('page-subtitle', 'Atualize os dados da prefeitura e gerencie as unidades')

@section('content')
    <div class="py-6">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="p-4 mb-6 rounded-lg bg-green-50">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            @endif

            <form action="{{ route('admin.prefeituras.update', $prefeitura->id) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="overflow-hidden bg-white shadow-sm rounded-xl">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-700">Informações da Prefeitura</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                            {{-- Nome --}}
                            <div>
                                <label for="nome" class="block text-sm font-medium text-gray-700">Nome</label>
                                <input type="text" name="nome" id="nome"
                                    value="{{ old('nome', $prefeitura->nome) }}"
                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496]">
                                @error('nome')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- CNPJ --}}
                            <div>
                                <label for="cnpj" class="block text-sm font-medium text-gray-700">CNPJ</label>
                                <input type="text" name="cnpj" id="cnpj"
                                    value="{{ old('cnpj', $prefeitura->cnpj) }}"
                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496]">
                                @error('cnpj')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Endereço --}}
                            <div>
                                <label for="endereco" class="block text-sm font-medium text-gray-700">Endereço</label>
                                <input type="text" name="endereco" id="endereco"
                                    value="{{ old('endereco', $prefeitura->endereco) }}"
                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496]">
                                @error('endereco')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Cidade --}}
                            <div>
                                <label for="cidade" class="block text-sm font-medium text-gray-700">Nome da Cidade</label>
                                <input type="text" name="cidade" id="cidade"
                                    value="{{ old('cidade', $prefeitura->cidade) }}"
                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496]">
                                @error('cidade')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Telefone --}}
                            <div>
                                <label for="telefone" class="block text-sm font-medium text-gray-700">Telefone</label>
                                <input type="text" name="telefone" id="telefone"
                                    value="{{ old('telefone', $prefeitura->telefone) }}"
                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496]">
                                @error('telefone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Email --}}
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" name="email" id="email"
                                    value="{{ old('email', $prefeitura->email) }}"
                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496]">
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Autoridade Competente --}}
                            <div>
                                <label for="autoridade_competente"
                                    class="block text-sm font-medium text-gray-700">Autoridade Competente</label>
                                <input type="text" name="autoridade_competente" id="autoridade_competente"
                                    value="{{ old('autoridade_competente', $prefeitura->autoridade_competente) }}"
                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496]">
                                @error('autoridade_competente')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Capa --}}
                            <div>
                                <label for="capa" class="block text-sm font-medium text-gray-700">Capa</label>
                                <input type="file" name="capa" id="capa"
                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496]">
                                @if ($prefeitura->capa)
                                    <img src="{{ asset($prefeitura->capa) }}" alt="Capa atual"
                                        class="h-16 mt-2 rounded shadow">
                                @endif
                                @error('capa')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Timbre --}}
                            <div>
                                <label for="timbre" class="block text-sm font-medium text-gray-700">Timbre</label>
                                <input type="file" name="timbre" id="timbre"
                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496]">
                                @if ($prefeitura->timbre)
                                    <img src="{{ asset($prefeitura->timbre) }}" alt="Timbre atual"
                                        class="h-16 mt-2 rounded shadow">
                                @endif
                                @error('timbre')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            {{-- Capa Edital --}}
                            <div>
                                <label for="capa_edital" class="block text-sm font-medium text-gray-700">Capa Edital</label>
                                <input type="file" name="capa_edital" id="capa_edital"
                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496]">
                                @if ($prefeitura->capa_edital)
                                    <img src="{{ asset($prefeitura->capa_edital) }}" alt="capa_edital atual"
                                        class="h-16 mt-2 rounded shadow">
                                @endif
                                @error('capa_edital')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>

                        <div class="flex justify-end mt-6 space-x-4">
                            <a href="{{ route('admin.prefeituras.index') }}"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#009496]">
                                Cancelar
                            </a>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-[#009496] rounded-md hover:bg-[#244853] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#009496]">
                                Atualizar Prefeitura
                            </button>
                        </div>
                    </div>
                </div>
            </form>


            <!-- Seção para Unidades/Setores/Departamentos -->
            <div class="mt-6 overflow-hidden bg-white shadow-sm rounded-xl">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-700">Unidades/Setores/Departamentos</h3>
                        <button type="button" id="btn-add-unidade"
                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-[#009496] rounded-lg hover:bg-[#244853] transition-colors shadow-sm">
                            Adicionar Unidade
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    @if ($prefeitura->unidades->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Nome
                                        </th>
                                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Servidor
                                            Responsável</th>
                                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Nº
                                            Portaria</th>
                                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Data da
                                            Portaria</th>
                                        <th class="px-6 py-3 text-xs font-medium text-center text-gray-500 uppercase">Ações
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200" id="unidades-table-body">
                                    @foreach ($prefeitura->unidades as $unidade)
                                        <tr id="unidade-row-{{ $unidade->id }}">
                                            <td class="px-6 py-4">{{ $unidade->nome }}</td>
                                            <td class="px-6 py-4">{{ $unidade->servidor_responsavel }}</td>
                                            <td class="px-6 py-4">{{ $unidade->numero_portaria ?? '—' }}</td>
                                            <td class="px-6 py-4">
                                                {{ $unidade->data_portaria ? \Carbon\Carbon::parse($unidade->data_portaria)->format('d/m/Y') : '—' }}
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <button type="button"
                                                    class="text-yellow-600 hover:underline edit-unidade"
                                                    data-id="{{ $unidade->id }}">Editar</button>
                                                <button type="button" class="text-red-600 hover:underline delete-unidade"
                                                    data-id="{{ $unidade->id }}">Excluir</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>

                            </table>
                        </div>
                    @else
                        <p class="text-sm text-gray-500">Nenhuma unidade cadastrada.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Adicionar/Editar Unidade -->
    <div id="unidade-modal" class="fixed inset-0 z-50 hidden overflow-y-auto transition-opacity duration-300 ease-in-out">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Overlay com backdrop blur -->
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-900 bg-opacity-75 backdrop-blur-sm"></div>
            </div>

            <!-- Centralização do modal -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Conteúdo do Modal -->
            <div
                class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl sm:align-middle">
                <!-- Header do Modal -->
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <h3 class="text-xl font-semibold text-gray-800" id="modal-title">
                        <span class="text-[#009496]">+</span> Adicionar Unidade
                    </h3>
                    <button type="button" id="btn-close-modal"
                        class="text-gray-400 transition-colors duration-200 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Formulário -->
                <form id="unidade-form" class="mt-6 space-y-6">
                    <input type="hidden" id="unidade-id" name="id">
                    <input type="hidden" id="prefeitura-id" value="{{ $prefeitura->id }}">

                    <!-- Campo Nome -->
                    <div class="space-y-2">
                        <label for="unidade-nome" class="block text-sm font-medium text-gray-700">
                            <span class="text-[#009496]">*</span> Nome da Unidade
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-6 0H5m2 0h4M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                    </path>
                                </svg>
                            </div>
                            <input type="text" name="nome" id="unidade-nome"
                                class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#009496] focus:border-transparent transition-all duration-200"
                                placeholder="Ex: Secretaria de Educação">
                        </div>
                        <p id="unidade-nome-error" class="hidden text-sm text-red-600 animate-pulse"></p>
                    </div>

                    <!-- Campo Servidor Responsável -->
                    <div class="space-y-2">
                        <label for="unidade-servidor" class="block text-sm font-medium text-gray-700">
                            <span class="text-[#009496]">*</span> Servidor Responsável
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <input type="text" name="servidor_responsavel" id="unidade-servidor"
                                class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#009496] focus:border-transparent transition-all duration-200"
                                placeholder="Ex: João da Silva">
                        </div>
                        <p id="unidade-servidor-error" class="hidden text-sm text-red-600 animate-pulse"></p>
                    </div>

                    {{-- Nº PORTARIA --}}
                    <div>
                        <label for="numero_portaria" class="block text-sm font-medium text-gray-700">Nº da
                            Portaria</label>
                        <input type="text" name="numero_portaria" id="numero_portaria"
                            value="{{ old('numero_portaria') }}"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496]">
                        @error('numero_portaria')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- DATA DA PORTARIA --}}
                    <div>
                        <label for="data_portaria" class="block text-sm font-medium text-gray-700">Data da
                            Portaria</label>
                        <input type="date" name="data_portaria" id="data_portaria"
                            value="{{ old('data_portaria') }}"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496]">
                        @error('data_portaria')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Footer do Modal -->
                    <div class="flex items-center justify-end pt-6 space-x-3 border-t border-gray-100">
                        <button type="button" id="btn-cancel-modal"
                            class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200 transform hover:-translate-y-0.5">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-6 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-[#062F43] to-[#244853] rounded-lg shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#009496] transition-all duration-200 transform hover:-translate-y-0.5 hover:scale-105">
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                                Salvar Unidade
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        #unidade-modal {
            opacity: 0;
            transform: scale(0.95);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        #unidade-modal:not(.hidden) {
            opacity: 1;
            transform: scale(1);
        }

        #unidade-modal .bg-gray-900 {
            transition: opacity 0.3s ease;
        }

        #unidade-modal:not(.hidden) .bg-gray-900 {
            opacity: 0.75;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('unidade-modal');
            const form = document.getElementById('unidade-form');
            const modalTitle = document.getElementById('modal-title');
            const unidadeId = document.getElementById('unidade-id');
            const prefeituraId = document.getElementById('prefeitura-id');
            const unidadeNome = document.getElementById('unidade-nome');
            const unidadeServidor = document.getElementById('unidade-servidor');

            // Fechar modal com ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                    closeModal();
                }
            });

            // Fechar modal clicando no overlay
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeModal();
                }
            });

            // Fechar modal com botão X
            document.getElementById('btn-close-modal').addEventListener('click', closeModal);

            // Fechar modal com botão Cancelar
            document.getElementById('btn-cancel-modal').addEventListener('click', closeModal);

            function closeModal() {
                modal.classList.add('hidden');
                form.reset();
                // Limpar erros
                document.getElementById('unidade-nome-error').classList.add('hidden');
                document.getElementById('unidade-servidor-error').classList.add('hidden');
            }

            function openModal() {
                modal.classList.remove('hidden');
            }

            // Abrir modal para adicionar
            document.getElementById('btn-add-unidade').addEventListener('click', function() {
                modalTitle.innerHTML = '<span class="text-[#009496]">+</span> Adicionar Unidade';
                form.reset();
                unidadeId.value = '';
                openModal();
            });

            // Editar unidade
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('edit-unidade')) {
                    const id = e.target.getAttribute('data-id');

                    fetch(`/admin/unidades/${id}`)
                        .then(response => response.json())
                        .then(data => {
                            modalTitle.innerHTML =
                                '<span class="text-[#009496]">✏️</span> Editar Unidade';
                            unidadeId.value = data.id;
                            unidadeNome.value = data.nome;
                            unidadeServidor.value = data.servidor_responsavel;
                            document.getElementById('numero_portaria').value = data.numero_portaria ??
                                '';
                            document.getElementById('data_portaria').value = data.data_portaria ?? '';
                            openModal();
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Erro ao carregar dados da unidade');
                        });
                }
            });

            // Submit do formulário
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                // Limpar erros anteriores
                document.getElementById('unidade-nome-error').classList.add('hidden');
                document.getElementById('unidade-servidor-error').classList.add('hidden');

                const id = unidadeId.value;
                const url = id ? `/admin/unidades/${id}` :
                    `/admin/prefeituras/${prefeituraId.value}/unidades`;
                const method = id ? 'PUT' : 'POST';

                // Animação de loading no botão
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML =
                    '<span class="flex items-center"><div class="w-4 h-4 mr-2 border-b-2 border-white rounded-full animate-spin"></div> Salvando...</span>';
                submitBtn.disabled = true;

                fetch(url, {
                        method: method,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            nome: unidadeNome.value,
                            servidor_responsavel: unidadeServidor.value,
                            numero_portaria: document.getElementById('numero_portaria').value,
                            data_portaria: document.getElementById('data_portaria').value
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            closeModal();
                            // Recarregar a página para ver as mudanças
                            setTimeout(() => location.reload(), 300);
                        } else if (data.errors) {
                            // Mostrar erros de validação
                            if (data.errors.nome) {
                                const errorElement = document.getElementById('unidade-nome-error');
                                errorElement.textContent = data.errors.nome[0];
                                errorElement.classList.remove('hidden');
                            }
                            if (data.errors.servidor_responsavel) {
                                const errorElement = document.getElementById('unidade-servidor-error');
                                errorElement.textContent = data.errors.servidor_responsavel[0];
                                errorElement.classList.remove('hidden');
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Erro ao salvar unidade');
                    })
                    .finally(() => {
                        // Restaurar botão
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    });
            });
        });

        document.addEventListener("DOMContentLoaded", function() {
            // Máscara de CNPJ
            const cnpjInput = document.getElementById('cnpj');
            if (cnpjInput) {
                IMask(cnpjInput, {
                    mask: '00.000.000/0000-00'
                });
            }

            // Máscara de Telefone (suporta fixo e celular)
            const telefoneInput = document.getElementById('telefone');
            if (telefoneInput) {
                IMask(telefoneInput, {
                    mask: [{
                            mask: '(00) 0000-0000'
                        },
                        {
                            mask: '(00) 00000-0000'
                        }
                    ]
                });
            }
        });

        // Adicione este código dentro do seu event listener DOMContentLoaded, após a parte de edição

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('delete-unidade')) {
                const id = e.target.getAttribute('data-id');

                // Confirmação antes de excluir
                if (confirm('Tem certeza que deseja excluir esta unidade? Esta ação não pode ser desfeita.')) {
                    // Mostrar indicador de carregamento
                    e.target.innerHTML = '<span class="animate-pulse">Excluindo...</span>';
                    e.target.disabled = true;

                    fetch(`/admin/unidades/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Erro na resposta do servidor');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Remover a linha da tabela
                            const row = document.getElementById(`unidade-row-${id}`);
                            if (row) {
                                row.remove();
                            }

                            // Mostrar mensagem de sucesso
                            alert('Unidade excluída com sucesso!');

                            // Verificar se não há mais unidades
                            const tbody = document.getElementById('unidades-table-body');
                            if (tbody && tbody.children.length === 0) {
                                location.reload(); // Recarregar para mostrar mensagem de "nenhuma unidade"
                            }
                        } else if (data.error) {
                            alert('Erro ao excluir: ' + data.error);
                            location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Erro ao excluir unidade. Verifique o console para mais detalhes.');
                        location.reload();
                    })
                    .finally(() => {
                        // Restaurar o botão
                        e.target.innerHTML = 'Excluir';
                        e.target.disabled = false;
                    });
                }
            }
        });
    </script>
@endsection
