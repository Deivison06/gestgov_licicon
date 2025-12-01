@extends('layouts.app')

@section('page-title', 'Contrato - ' . $processo->numero_processo)
@section('page-subtitle', 'Gerar contrato do processo licitatório')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>

    {{-- JSON com as unidades para o JS --}}
    @php
        $unidadesData = $processo->prefeitura->unidades->map(function ($unidade) {
            return [
                'id' => $unidade->id,
                'nome' => $unidade->nome,
                'servidor_responsavel' => $unidade->servidor_responsavel,
                'numero_portaria' => $unidade->numero_portaria,
                'data_portaria' => $unidade->data_portaria,
            ];
        });
    @endphp
    <script>
        const unidadesAssinantes = @json($unidadesData);
    </script>
    {{-- Fim JSON --}}

    <div class="py-8">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">

            <!-- Seção de Informações do Processo -->
            <div class="mb-8">
                <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-2xl">
                    <!-- Header -->
                    <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
                        <div class="flex flex-col items-start justify-between lg:flex-row lg:items-center">
                            <h3 class="text-xl font-semibold text-gray-800">Processos Licitatórios - Contrato</h3>
                        </div>
                    </div>

                    <!-- Tabela de Informações -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Prefeitura
                                    </th>
                                    <th class="px-6 py-4 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Modalidade
                                    </th>
                                    <th class="px-6 py-4 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Nº Processo
                                    </th>
                                    <th class="px-6 py-4 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Nº Procedimento
                                    </th>
                                    <th class="px-6 py-4 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Tipo Contratação
                                    </th>
                                    <th class="px-6 py-4 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Tipo Procedimento
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr class="transition-colors duration-200 hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-[#009496]/10 flex items-center justify-center">
                                                <svg class="w-4 h-4 text-[#009496]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-6 0H5m2 0h4M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                                    </path>
                                                </svg>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $processo->prefeitura->nome }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        {{-- Versão SIMPLIFICADA para teste --}}
                                        <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 border border-gray-200">
                                            {{ $processo->modalidade->getDisplayName() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-mono text-sm text-gray-900">
                                        {{ $processo->numero_processo }}
                                    </td>
                                    <td class="px-6 py-4 font-mono text-sm text-gray-900">
                                        {{ $processo->numero_procedimento }}
                                    </td>
                                    <td class="px-6 py-4 font-mono text-sm text-gray-900">
                                        {{ $processo->tipo_contratacao_nome }}
                                    </td>
                                    <td class="px-6 py-4 font-mono text-sm text-gray-900">
                                        {{ $processo->tipo_procedimento_nome }}
                                    </td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td colspan="6" class="px-6 py-4 text-sm text-gray-700">
                                        <strong>Objeto:</strong> {!! strip_tags($processo->objeto) !!}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Seção de Contrato -->
            <div class="mb-8">
                <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-2xl">
                    <!-- Header -->
                    <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100">
                        <div class="flex flex-col items-start justify-between lg:flex-row lg:items-center">
                            <h3 class="text-xl font-semibold text-gray-800">Gerar Contrato</h3>
                            <span class="mt-2 text-sm text-gray-600 lg:mt-0">
                                {{ $processo->modalidade->getDisplayName() }}
                            </span>
                        </div>
                    </div>

                    <!-- Área de Mensagens -->
                    <div id="message-container" class="p-4"></div>

                    <!-- Tabela de Contrato -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-6 py-4 text-xs font-semibold tracking-wider text-left text-gray-700 uppercase">
                                        Documento
                                    </th>
                                    <th class="w-40 px-6 py-4 text-xs font-semibold tracking-wider text-center text-gray-700 uppercase">
                                        Data
                                    </th>
                                    <th class="w-48 px-6 py-4 text-xs font-semibold tracking-wider text-center text-gray-700 uppercase">
                                        Ações
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($documentos as $tipo => $doc)
                                    @php
                                        $documentoGerado = $processo->documentos
                                            ->where('tipo_documento', $tipo)
                                            ->first();
                                        $accordionId = "accordion-collapse-{$tipo}";
                                    @endphp

                                    {{-- Linha principal do contrato --}}
                                    <tr class="transition-colors duration-150 hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 w-2 h-2 mr-3 {{ $doc['cor'] }} rounded-full">
                                                </div>
                                                <div class="text-sm font-semibold text-gray-900">
                                                    {{ $doc['titulo'] }}
                                                    @if ($documentoGerado)
                                                        <span class="ml-2 text-xs font-normal text-green-600">
                                                            ✓ Gerado em
                                                            {{ \Carbon\Carbon::parse($documentoGerado->gerado_em)->format('d/m/Y H:i') }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            {{-- Botão para expandir/colapsar o acordeão --}}
                                            @if ($doc['requer_assinatura'] || !empty($doc['campos']))
                                                <button type="button" class="mt-2 text-xs font-medium text-blue-600 hover:text-blue-800"
                                                        data-collapse-toggle="{{ $accordionId }}"
                                                        aria-expanded="false"
                                                        aria-controls="{{ $accordionId }}">
                                                    <span class="collapse-text">
                                                        @if ($doc['requer_assinatura'] && !empty($doc['campos']))
                                                            Definir Campos e Assinantes
                                                        @elseif($doc['requer_assinatura'])
                                                            Definir Assinantes
                                                        @elseif(!empty($doc['campos']))
                                                            Definir Campos
                                                        @endif
                                                    </span>
                                                </button>
                                            @endif
                                        </td>
                                        <td class="flex gap-2 px-6 py-4 text-center">
                                            <input type="date"
                                                   class="w-40 px-3 py-2 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                   id="data_contrato"
                                                   value="{{ $documentoGerado->data_selecionada ?? now()->format('Y-m-d') }}">
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex justify-center space-x-2">
                                                <button type="button"
                                                        onclick="gerarContrato('{{ $processo->id }}', document.getElementById('data_contrato').value, event)"
                                                        class="px-4 py-2 text-xs font-medium text-white transition-colors duration-200 bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                                    Gerar Contrato
                                                </button>
                                                @if ($documentoGerado)
                                                    <a href="{{ route('admin.processo.contrato.download', ['processo' => $processo->id]) }}"
                                                       download
                                                       class="p-2 text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                                                       aria-label="Baixar contrato">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                            <polyline points="7 10 12 15 17 10"></polyline>
                                                            <line x1="12" y1="15" x2="12" y2="3"></line>
                                                        </svg>
                                                    </a>
                                                @else
                                                    <span class="p-2 text-gray-400 bg-gray-100 rounded-md cursor-not-allowed" aria-hidden="true" title="Aguardando geração">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                            <polyline points="7 10 12 15 17 10"></polyline>
                                                            <line x1="12" y1="15" x2="12" y2="3"></line>
                                                        </svg>
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>

                                    {{-- Linha do Acordeão para Campos e Assinantes --}}
                                    @if ($doc['requer_assinatura'] || !empty($doc['campos']))
                                        <tr>
                                            <td colspan="3" class="p-0">
                                                <div id="{{ $accordionId }}" class="hidden">
                                                    <div class="p-4 border-t border-gray-200 bg-gray-50" id="accordion-content-{{ $tipo }}">

                                                        <!-- Seção de Assinantes -->
                                                        @if ($doc['requer_assinatura'])
                                                        <div class="pb-4">
                                                            <h4 class="mb-4 text-sm font-semibold text-gray-700">Seleção de Assinantes</h4>

                                                            <div id="assinantes-container-{{ $tipo }}" class="space-y-3">
                                                                <div class="flex flex-col gap-3 p-4 bg-white border border-gray-200 rounded-lg assinante-item">
                                                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                                                        {{-- Select da Unidade --}}
                                                                        <div class="flex-1 min-w-[180px]">
                                                                            <label for="assinante_unidade_{{ $tipo }}" class="block mb-1 text-xs font-medium text-gray-600">
                                                                                Unidade
                                                                            </label>
                                                                            <select name="assinante_unidade[]"
                                                                                    id="assinante_unidade_{{ $tipo }}"
                                                                                    class="block w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 unidade-select"
                                                                                    onchange="updateResponsavel(this, '{{ $tipo }}')">
                                                                                <option value="">Selecione a Unidade</option>
                                                                                @foreach ($processo->prefeitura->unidades as $unidade)
                                                                                    <option value="{{ $unidade->id }}">{{ $unidade->nome }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>

                                                                        {{-- Campos do Responsável e Portaria --}}
                                                                        <div class="flex flex-col flex-1 gap-2 sm:flex-row sm:items-center sm:gap-3">
                                                                            {{-- Nome do Responsável --}}
                                                                            <div class="flex-1 min-w-[200px]">
                                                                                <label class="block mb-1 text-xs font-medium text-gray-600">
                                                                                    Responsável
                                                                                </label>
                                                                                <input type="text"
                                                                                       name="assinante_responsavel[]"
                                                                                       placeholder="Nome do Responsável"
                                                                                       readonly
                                                                                       class="block w-full px-3 py-2 text-sm text-gray-700 bg-gray-100 border border-gray-300 rounded-md shadow-sm responsavel-input">
                                                                            </div>

                                                                            {{-- Número da Portaria --}}
                                                                            <div class="flex-1 min-w-[150px]">
                                                                                <label class="block mb-1 text-xs font-medium text-gray-600">
                                                                                    Nº Portaria
                                                                                </label>
                                                                                <input type="text"
                                                                                       name="assinante_portaria[]"
                                                                                       placeholder="Número da Portaria"
                                                                                       readonly
                                                                                       class="block w-full px-3 py-2 text-sm text-gray-700 bg-gray-100 border border-gray-300 rounded-md shadow-sm portaria-input">
                                                                            </div>

                                                                            {{-- Data da Portaria --}}
                                                                            <div class="flex-1 min-w-[150px]">
                                                                                <label class="block mb-1 text-xs font-medium text-gray-600">
                                                                                    Data Portaria
                                                                                </label>
                                                                                <input type="text"
                                                                                       name="assinante_data_portaria[]"
                                                                                       placeholder="Data da Portaria"
                                                                                       readonly
                                                                                       class="block w-full px-3 py-2 text-sm text-gray-700 bg-gray-100 border border-gray-300 rounded-md shadow-sm data-portaria-input">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            {{-- Botão de adicionar assinante --}}
                                                            <div class="mt-4">
                                                                <button type="button"
                                                                        onclick="adicionarAssinante('{{ $tipo }}')"
                                                                        class="flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-white bg-blue-500 rounded-md shadow hover:bg-blue-600 focus:ring-2 focus:ring-blue-300">
                                                                    + Adicionar Assinante
                                                                </button>
                                                            </div>
                                                        </div>
                                                        @endif

                                                        <!-- Seção de Campos do Contrato -->
                                                        @if (!empty($doc['campos']))
                                                        <div class="pb-6 mb-6 border-b border-gray-200">
                                                            <h4 class="mb-4 text-sm font-semibold text-gray-700">Campos do Contrato</h4>
                                                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                                                                @foreach ($doc['campos'] as $campo)
                                                                    @if ($campo === 'numero_contrato')
                                                                        <div class="space-y-1">
                                                                            <label for="numero_contrato" class="block text-xs font-medium text-gray-600">
                                                                                Número do Contrato
                                                                            </label>
                                                                            <input type="text"
                                                                                id="numero_contrato"
                                                                                name="numero_contrato"
                                                                                placeholder="Ex: 001/2024"
                                                                                value="{{ $contrato->numero_contrato ?? '' }}"
                                                                                class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                                                        </div>
                                                                    @elseif($campo === 'data_assinatura_contrato')
                                                                        <div class="space-y-1">
                                                                            <label for="data_assinatura_contrato" class="block text-xs font-medium text-gray-600">
                                                                                Data de Assinatura
                                                                            </label>
                                                                            <input type="date"
                                                                                id="data_assinatura_contrato"
                                                                                name="data_assinatura_contrato"
                                                                                value="{{ $contrato->data_assinatura_contrato ?? '' }}"
                                                                                class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                                                        </div>
                                                                    @elseif($campo === 'numero_extrato')
                                                                        <div class="space-y-1">
                                                                            <label for="numero_extrato" class="block text-xs font-medium text-gray-600">
                                                                                Número do Extrato
                                                                            </label>
                                                                            <input type="text"
                                                                                id="numero_extrato"
                                                                                name="numero_extrato"
                                                                                placeholder="Ex: EXT/001/2024"
                                                                                value="{{ $contrato->numero_extrato ?? '' }}"
                                                                                class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                                                        </div>
                                                                    @elseif($campo === 'comarca')
                                                                        <div class="space-y-1">
                                                                            <label for="comarca" class="block text-xs font-medium text-gray-600">
                                                                                Comarca
                                                                            </label>
                                                                            <input type="text"
                                                                                id="comarca"
                                                                                name="comarca"
                                                                                placeholder="Ex: Comarca de São Paulo"
                                                                                value="{{ $contrato->comarca ?? '' }}"
                                                                                class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                                                        </div>
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Inicialização da funcionalidade de acordeão
        document.querySelectorAll('[data-collapse-toggle]').forEach(button => {
            button.addEventListener('click', () => {
                const targetId = button.getAttribute('data-collapse-toggle');
                const targetEl = document.getElementById(targetId);
                const isExpanded = button.getAttribute('aria-expanded') === 'true';
                const span = button.querySelector('.collapse-text');

                if (isExpanded) {
                    targetEl.classList.add('hidden');
                    button.setAttribute('aria-expanded', 'false');
                    span.textContent = span.textContent.replace('Ocultar', 'Definir');
                } else {
                    targetEl.classList.remove('hidden');
                    button.setAttribute('aria-expanded', 'true');
                    span.textContent = span.textContent.replace('Definir', 'Ocultar');
                }
            });
        });

        // Funções para gerenciar assinantes
        function adicionarAssinante(tipoDocumento) {
            const container = document.getElementById(`assinantes-container-${tipoDocumento}`);
            const novoAssinante = document.createElement('div');
            novoAssinante.className = 'assinante-item flex flex-col gap-3 p-4 mb-3 bg-white border border-gray-200 rounded-lg';
            novoAssinante.innerHTML = `
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    {{-- Select da Unidade --}}
                    <div class="flex-1 min-w-[180px]">
                        <label class="block mb-1 text-xs font-medium text-gray-600">
                            Unidade
                        </label>
                        <select name="assinante_unidade[]"
                                class="block w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 unidade-select"
                                onchange="updateResponsavel(this, '${tipoDocumento}')">
                            <option value="">Selecione a Unidade</option>
                            @foreach ($processo->prefeitura->unidades as $unidade)
                                <option value="{{ $unidade->id }}">{{ $unidade->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Campos do Responsável e Portaria --}}
                    <div class="flex flex-col flex-1 gap-2 sm:flex-row sm:items-center sm:gap-3">
                        {{-- Nome do Responsável --}}
                        <div class="flex-1 min-w-[200px]">
                            <label class="block mb-1 text-xs font-medium text-gray-600">
                                Responsável
                            </label>
                            <input type="text" name="assinante_responsavel[]"
                                placeholder="Nome do Responsável" readonly
                                class="block w-full px-3 py-2 text-sm text-gray-700 bg-gray-100 border border-gray-300 rounded-md shadow-sm responsavel-input">
                        </div>

                        {{-- Número da Portaria --}}
                        <div class="flex-1 min-w-[150px]">
                            <label class="block mb-1 text-xs font-medium text-gray-600">
                                Nº Portaria
                            </label>
                            <input type="text" name="assinante_portaria[]"
                                placeholder="Número da Portaria" readonly
                                class="block w-full px-3 py-2 text-sm text-gray-700 bg-gray-100 border border-gray-300 rounded-md shadow-sm portaria-input">
                        </div>

                        {{-- Data da Portaria --}}
                        <div class="flex-1 min-w-[150px]">
                            <label class="block mb-1 text-xs font-medium text-gray-600">
                                Data Portaria
                            </label>
                            <input type="text" name="assinante_data_portaria[]"
                                placeholder="Data da Portaria" readonly
                                class="block w-full px-3 py-2 text-sm text-gray-700 bg-gray-100 border border-gray-300 rounded-md shadow-sm data-portaria-input">
                        </div>
                    </div>

                    {{-- Botão Remover --}}
                    <div class="flex items-end sm:pt-6">
                        <button type="button" onclick="removerAssinante(this, '${tipoDocumento}')"
                                class="p-2 text-white bg-red-500 rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500">
                            🗑 Remover
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(novoAssinante);
        }

        function removerAssinante(botao, tipoDocumento) {
            const container = document.getElementById(`assinantes-container-${tipoDocumento}`);
            const assinanteDiv = botao.closest('.assinante-item');
            const todosAssinantes = container.querySelectorAll('.assinante-item');

            if (todosAssinantes.length > 1) {
                assinanteDiv.style.transition = 'opacity 0.3s ease';
                assinanteDiv.style.opacity = '0';
                setTimeout(() => assinanteDiv.remove(), 300);
            } else {
                showMessage('É obrigatório ter pelo menos um assinante.', 'error');
            }
        }

        function updateResponsavel(select, tipoDocumento) {
            const selectedUnidadeId = select.value;
            const selectedUnidade = unidadesAssinantes.find(u => u.id == selectedUnidadeId);
            const assinanteDiv = select.closest('.assinante-item');

            if (selectedUnidade) {
                // Preenche o campo responsável
                const responsavelInput = assinanteDiv.querySelector('.responsavel-input');
                if (responsavelInput) {
                    responsavelInput.value = selectedUnidade.servidor_responsavel || '';
                }

                // Preenche o número da portaria
                const portariaInput = assinanteDiv.querySelector('.portaria-input');
                if (portariaInput) {
                    portariaInput.value = selectedUnidade.numero_portaria || '';
                }

                // Preenche a data da portaria
                const dataPortariaInput = assinanteDiv.querySelector('.data-portaria-input');
                if (dataPortariaInput) {
                    dataPortariaInput.value = selectedUnidade.data_portaria || '';
                }
            } else {
                // Limpa os campos se nenhuma unidade for selecionada
                const responsavelInput = assinanteDiv.querySelector('.responsavel-input');
                if (responsavelInput) responsavelInput.value = '';

                const portariaInput = assinanteDiv.querySelector('.portaria-input');
                if (portariaInput) portariaInput.value = '';

                const dataPortariaInput = assinanteDiv.querySelector('.data-portaria-input');
                if (dataPortariaInput) dataPortariaInput.value = '';
            }
        }

       // Função para obter dados dos campos do contrato
        function getCamposContrato() {
            const campos = {};

            // Número do Contrato
            const numeroContratoInput = document.getElementById('numero_contrato');
            if (numeroContratoInput && numeroContratoInput.value.trim() !== '') {
                campos.numero_contrato = numeroContratoInput.value.trim();
            }

            // Data de Assinatura
            const dataAssinaturaInput = document.getElementById('data_assinatura_contrato');
            if (dataAssinaturaInput && dataAssinaturaInput.value) {
                campos.data_assinatura_contrato = dataAssinaturaInput.value;
            }

            // Número do Extrato
            const numeroExtratoInput = document.getElementById('numero_extrato');
            if (numeroExtratoInput && numeroExtratoInput.value.trim() !== '') {
                campos.numero_extrato = numeroExtratoInput.value.trim();
            }

            // Comarca
            const comarcaInput = document.getElementById('comarca');
            if (comarcaInput && comarcaInput.value.trim() !== '') {
                campos.comarca = comarcaInput.value.trim();
            }

            return campos;
        }

        // Função auxiliar para obter os dados dos assinantes
        function getAssinantes(tipoDocumento) {
            const container = document.getElementById(`assinantes-container-${tipoDocumento}`);
            const selects = container.querySelectorAll('select[name="assinante_unidade[]"]');
            const assinantes = [];

            selects.forEach((select, index) => {
                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption.value) {
                    const unidade = unidadesAssinantes.find(u => u.id == select.value);
                    if (unidade) {
                        // Busca os valores dos inputs correspondentes
                        const assinanteDiv = select.closest('.assinante-item');
                        const responsavelInput = assinanteDiv.querySelector('input[name="assinante_responsavel[]"]');
                        const portariaInput = assinanteDiv.querySelector('input[name="assinante_portaria[]"]');
                        const dataPortariaInput = assinanteDiv.querySelector('input[name="assinante_data_portaria[]"]');

                        assinantes.push({
                            unidade_id: unidade.id,
                            unidade_nome: unidade.nome,
                            responsavel: responsavelInput?.value || unidade.servidor_responsavel,
                            numero_portaria: portariaInput?.value || unidade.numero_portaria,
                            data_portaria: dataPortariaInput?.value || unidade.data_portaria,
                        });
                    }
                }
            });
            return assinantes;
        }

        /**
         * Gera o contrato via AJAX
         */
        function gerarContrato(processoId, data, event) {
            if (!data) {
                showMessage('Por favor, selecione uma data antes de gerar o contrato.', 'error');
                return;
            }

            const assinantes = getAssinantes('contrato');

            if (assinantes.length < 1) {
                showMessage('Você deve adicionar pelo menos um assinante antes de gerar o contrato.', 'error');
                return;
            }

            const camposContrato = getCamposContrato();
            const assinantesJson = JSON.stringify(assinantes);
            const assinantesEncoded = encodeURIComponent(assinantesJson);
            const camposContratoJson = JSON.stringify(camposContrato);
            const camposContratoEncoded = encodeURIComponent(camposContratoJson);

            // CORREÇÃO: Usar a URL correta da rota definida
            let url = `/admin/contrato/processos/${processoId}/pdf?data=${data}`;

            if (assinantes.length > 0) {
                url += `&assinantes=${assinantesEncoded}`;
            }

            if (Object.keys(camposContrato).length > 0) {
                url += `&campos=${camposContratoEncoded}`;
            }

            const button = event.currentTarget;
            const originalText = button.textContent;

            button.textContent = 'Gerando...';
            button.disabled = true;

            fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage(data.message, 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        showMessage(data.message, 'error');
                    }
                })
                .catch(error => {
                    showMessage('Erro ao gerar contrato: ' + error, 'error');
                })
                .finally(() => {
                    button.textContent = originalText;
                    button.disabled = false;
                });
        }

        function showMessage(message, type) {
            const container = document.getElementById('message-container');
            const bgColor = type === 'success' ? 'bg-green-100 border-green-400' : 'bg-red-100 border-red-400';
            const textColor = type === 'success' ? 'text-green-800' : 'text-red-800';
            const icon = type === 'success' ? '✅' : '❌';

            container.innerHTML = `
                <div class="p-4 mb-4 border-l-4 rounded-md ${bgColor} ${textColor}">
                    <div class="flex items-center">
                        <span class="mr-2 text-lg">${icon}</span>
                        <span class="font-semibold">${message}</span>
                    </div>
                </div>
            `;

            // Remove a mensagem automaticamente após 6 segundos
            setTimeout(() => {
                container.innerHTML = '';
            }, 6000);
        }
    </script>
@endsection
