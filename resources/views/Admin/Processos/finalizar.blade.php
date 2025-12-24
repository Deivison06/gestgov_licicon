@extends('layouts.app')

@section('page-title', 'Finalizar processo ' . $processo->numero_processo)
@section('page-subtitle', 'Cadastrar/Editar detalhes do processo')

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
                            <h3 class="text-xl font-semibold text-gray-800">Processos Licitatórios</h3>
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
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-6 0H5m2 0h4M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                </svg>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $processo->prefeitura->nome }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex px-2.5 py-0.5 text-xs font-medium rounded-full
                                            @if ($processo->modalidade->value === 'dispensa') bg-purple-100 text-purple-800
                                            @elseif($processo->modalidade->value === 'inexigibilidade') bg-pink-100 text-pink-800
                                            @elseif($processo->modalidade->value === 'pregão') bg-blue-100 text-blue-800
                                            @elseif($processo->modalidade->value === 'concorrência') bg-green-100 text-green-800
                                            @else bg-gray-100 text-gray-800 @endif">
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

            <!-- Tabela de Documentos -->
            <div class="overflow-x-auto rounded-lg shadow-sm">
                <!-- Área de Mensagens -->
                <div id="message-container" class="p-4"></div>

                <table class="min-w-full bg-white divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-xs font-semibold tracking-wider text-left text-gray-700 uppercase">
                                Documentos
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
                        @continue(
                        $processo->modalidade === \App\Enums\ModalidadeEnum::CONCORRENCIA
                        && ($tipo === 'termo_referencia' || $tipo === 'analise_mercado')
                        )
                        @continue(
                        $processo->modalidade === \App\Enums\ModalidadeEnum::PREGAO_ELETRONICO
                        && ($tipo === 'projeto_basico')
                        )

                        @php
                        $documentoGerado = $processo->documentos
                        ->where('tipo_documento', $tipo)
                        ->first();
                        $accordionId = "accordion-collapse-{$tipo}";
                        $requerAssinatura = $doc['requer_assinatura'] ?? false;
                        $temCampos = !empty($doc['campos']);
                        @endphp

                        {{-- Linha principal do documento --}}
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
                                @if ($temCampos || $requerAssinatura)
                                <button type="button" class="mt-2 text-xs font-medium text-red-600 hover:text-red-800" data-collapse-toggle="{{ $accordionId }}" aria-expanded="false" aria-controls="{{ $accordionId }}">
                                    <span class="collapse-text">
                                        @if($requerAssinatura && $temCampos)
                                        Definir Assinantes e Campos
                                        @elseif($requerAssinatura)
                                        Definir Assinantes
                                        @else
                                        Definir Campos
                                        @endif
                                    </span>
                                </button>
                                @endif
                            </td>
                            <td class="flex gap-2 px-6 py-4 text-center">
                                @if ($requerAssinatura)
                                <input type="date" class="w-40 px-3 py-2 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500" id="data_{{ $tipo }}" value="{{ $documentoGerado->data_selecionada ?? '' }}">

                                {{-- Adicionar dropdown de parecer para documentos específicos --}}
                                @if ($tipo === 'parecer_controle_interno' && $processo->modalidade === \App\Enums\ModalidadeEnum::PREGAO_ELETRONICO)
                                <!-- Dropdown de Parecer -->
                                <select id="parecer_select_{{ $tipo }}" name="parecer_select_{{ $tipo }}" class="block w-40 px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                    <option value="">Selecione o Parecer</option>
                                    <option value="parecer_1">Parecer 1</option>
                                    <option value="parecer_2">Parecer 2</option>
                                </select>
                                @endif

                                @else
                                <span class="text-sm text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-center space-x-2">
                                    @if ($requerAssinatura)
                                    <button type="button" onclick="gerarPdf('{{ $processo->id }}', '{{ $tipo }}', document.getElementById('data_{{ $tipo }}').value, event)" class="px-4 py-2 text-xs font-medium text-white transition-colors duration-200 bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                        Gerar PDF
                                    </button>
                                    @else
                                    <button type="button" onclick="gerarPdfSemAssinatura('{{ $processo->id }}', '{{ $tipo }}', event)" class="px-4 py-2 text-xs font-medium text-white transition-colors duration-200 bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                        Gerar PDF
                                    </button>
                                    @endif

                                    @if ($documentoGerado)
                                    <a href="{{ route('admin.processo.finalizardocumento.dowload', ['processo' => $processo->id, 'tipo' => $tipo]) }}" download class="p-2 text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2" aria-label="Baixar documento">
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

                        {{-- Linha do Acordeão (Collapse) - Apenas se tem campos OU requer assinatura --}}
                        @if ($temCampos || $requerAssinatura)
                        <tr>
                            <td colspan="3" class="p-0">
                                <div id="{{ $accordionId }}" class="hidden">
                                    <div class="p-4 border-t border-gray-200 bg-gray-50" id="accordion-content-{{ $tipo }}">

                                        <!-- Seção de Assinantes - Apenas se requer assinatura -->
                                        @if ($requerAssinatura)
                                        <div class="pb-4 mb-6 border-b border-gray-200">
                                            <h4 class="mb-4 text-sm font-semibold text-gray-700">Seleção de Assinantes</h4>

                                            <div id="assinantes-container-{{ $tipo }}" class="space-y-3">
                                                <div class="flex flex-col gap-3 p-4 bg-white border border-gray-200 rounded-lg assinante-item">
                                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                                        {{-- Select da Unidade --}}
                                                        <div class="flex-1 min-w-[180px]">
                                                            <label for="assinante_unidade_{{ $tipo }}" class="block mb-1 text-xs font-medium text-gray-600">
                                                                Unidade
                                                            </label>
                                                            <select name="assinante_unidade[]" id="assinante_unidade_{{ $tipo }}" class="block w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 unidade-select" onchange="updateResponsavel(this, '{{ $tipo }}')">
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
                                                                <input type="text" name="assinante_responsavel[]" placeholder="Nome do Responsável" readonly class="block w-full px-3 py-2 text-sm text-gray-700 bg-gray-100 border border-gray-300 rounded-md shadow-sm responsavel-input">
                                                            </div>

                                                            {{-- Número da Portaria --}}
                                                            <div class="flex-1 min-w-[150px]">
                                                                <label class="block mb-1 text-xs font-medium text-gray-600">
                                                                    Nº Portaria
                                                                </label>
                                                                <input type="text" name="assinante_portaria[]" placeholder="Número da Portaria" readonly class="block w-full px-3 py-2 text-sm text-gray-700 bg-gray-100 border border-gray-300 rounded-md shadow-sm portaria-input">
                                                            </div>

                                                            {{-- Data da Portaria --}}
                                                            <div class="flex-1 min-w-[150px]">
                                                                <label class="block mb-1 text-xs font-medium text-gray-600">
                                                                    Data Portaria
                                                                </label>
                                                                <input type="text" name="assinante_data_portaria[]" placeholder="Data da Portaria" readonly class="block w-full px-3 py-2 text-sm text-gray-700 bg-gray-100 border border-gray-300 rounded-md shadow-sm data-portaria-input">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Botão de adicionar assinante --}}
                                            <div class="mt-4">
                                                <button type="button" onclick="adicionarAssinante('{{ $tipo }}')" class="flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-white bg-blue-500 rounded-md shadow hover:bg-blue-600 focus:ring-2 focus:ring-blue-300">
                                                    + Adicionar Assinante
                                                </button>
                                            </div>
                                        </div>
                                        @endif

                                        <!-- Seção de Campos do Formulário (se houver campos) -->
                                        @if (!empty($doc['campos']))
                                        <div>
                                            <h4 class="mb-3 text-sm font-semibold text-gray-700">Campos do Documento</h4>
                                            <div x-data="formField({{ json_encode($processo->finalizacao ?? null) }})">
                                                <form action="{{ route('admin.processos.finalizacao.store', $processo) }}" method="POST" @submit.prevent="submitForm">
                                                    @csrf
                                                    <input type="hidden" name="processo_id" value="{{ $processo->id }}">

                                                    @foreach ($doc['campos'] as $campo)
                                                        @include('Admin.Processos.partials.forms-finalizacao')
                                                    @endforeach
                                                </form>
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

                <!-- Botão para Baixar Todos os PDFs -->
                <div class="flex justify-center p-4 mt-6 border-t border-gray-200 bg-gray-50">
                    <a href="{{ route('admin.processo.finalizardocumento.dowload-all', ['processo' => $processo->id]) }}" class="px-6 py-3 text-sm font-semibold text-white transition-colors duration-200 bg-green-600 rounded-lg shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        📥 Baixar Todos os PDFs
                    </a>
                </div>
            </div>

            @if ($processo->modalidade !== \App\Enums\ModalidadeEnum::CONCORRENCIA)
                <!-- Seção de Vencedores -->
                <div class="mb-8">
                    <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-2xl">
                        <!-- Header -->
                        <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
                            <div class="flex flex-col items-start justify-between lg:flex-row lg:items-center">
                                <h3 class="text-xl font-semibold text-gray-800">Vencedores do Processo</h3>
                                <button type="button"
                                        onclick="abrirModalVencedor()"
                                        class="px-4 py-2 mt-2 text-sm font-medium text-white bg-green-600 rounded-md lg:mt-0 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                    ➕ Adicionar Vencedor
                                </button>
                            </div>
                        </div>

                        <!-- Tabela de Vencedores -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                            Razão Social
                                        </th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                            CNPJ
                                        </th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                            Representante
                                        </th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                            CPF
                                        </th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                            Itens/Lotes
                                        </th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase">
                                            Ações
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200" id="vencedores-tbody">
                                    @if(isset($processo->vencedores) && count($processo->vencedores) > 0)
                                        @foreach($processo->vencedores as $index => $vencedor)
                                        <tr class="vencedor-row" data-vencedor-id="{{ $vencedor->id ?? '' }}">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $vencedor->razao_social }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $vencedor->cnpj }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $vencedor->representante }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $vencedor->cpf }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    @if(isset($vencedor->lotes) && count($vencedor->lotes) > 0)
                                                        {{ count($vencedor->lotes) }} {{ $processo->tipo_contratacao === 'LOTE' ? 'lotes' : 'itens' }}
                                                    @else
                                                        <span class="text-gray-400">Nenhum</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex justify-center space-x-2">
                                                    <button type="button"
                                                            onclick="editarVencedor({{ $index }})"
                                                            class="px-3 py-1 text-sm text-blue-600 bg-blue-100 rounded-md hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                        ✏️ Editar
                                                    </button>
                                                    <button type="button"
                                                            onclick="importarItensVencedor({{ $index }})"
                                                            class="px-3 py-1 text-sm text-green-600 bg-green-100 rounded-md hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-500">
                                                        📊 Importar Itens
                                                    </button>
                                                    <button type="button"
                                                            onclick="removerVencedor({{ $index }})"
                                                            class="px-3 py-1 text-sm text-red-600 bg-red-100 rounded-md hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500">
                                                        🗑️ Remover
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Linha expansível com os itens/lotes do vencedor -->
                                        @if(isset($vencedor->lotes) && count($vencedor->lotes) > 0)
                                        <tr class="bg-gray-50">
                                            <td colspan="6" class="px-6 py-4">
                                                <div class="lotes-container">
                                                    <div class="flex items-center justify-between mb-3">
                                                        <h4 class="text-lg font-semibold text-gray-800">
                                                            {{ $processo->tipo_contratacao === 'LOTE' ? 'Lotes' : 'Itens' }} do Vencedor
                                                        </h4>
                                                        <button type="button"
                                                                onclick="toggleLotes({{ $index }})"
                                                                class="flex items-center text-sm text-blue-600 hover:text-blue-800">
                                                            <span id="toggle-text-{{ $index }}">Mostrar Detalhes</span>
                                                            <svg id="toggle-icon-{{ $index }}" class="w-4 h-4 ml-1 transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                            </svg>
                                                        </button>
                                                    </div>

                                                    <div id="lotes-details-{{ $index }}" class="hidden">
                                                        @if($processo->tipo_contratacao === \App\Enums\TipoContratacaoEnum::LOTE)
                                                            <!-- Estrutura para LOTE - Agrupar por número do lote -->
                                                            @php
                                                                $lotesAgrupados = $vencedor->lotes->groupBy('lote');
                                                            @endphp

                                                            @foreach($lotesAgrupados as $numeroLote => $itensLote)
                                                            <div class="mb-6 border border-gray-200 rounded-lg">
                                                                <div class="px-4 py-3 bg-gray-100 border-b border-gray-200">
                                                                    <h5 class="font-semibold text-gray-800">
                                                                        LOTE {{ $numeroLote }}
                                                                    </h5>
                                                                </div>
                                                                <div class="overflow-x-auto">
                                                                    <table class="min-w-full divide-y divide-gray-200">
                                                                        <thead class="bg-gray-50">
                                                                            <tr>
                                                                                <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                                    Status
                                                                                </th>
                                                                                <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                                    Item
                                                                                </th>
                                                                                <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                                    Descrição
                                                                                </th>
                                                                                <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                                    UNIDADE
                                                                                </th>
                                                                                <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                                    Marca
                                                                                </th>
                                                                                <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                                    Modelo
                                                                                </th>
                                                                                <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                                    Quantidade
                                                                                </th>
                                                                                <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                                    Vl. Unit
                                                                                </th>
                                                                                <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                                    Vl. Total
                                                                                </th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody class="bg-white divide-y divide-gray-200">
                                                                            @foreach($itensLote as $lote)
                                                                            <tr class="hover:bg-gray-50">
                                                                                <td class="px-4 py-2 text-sm text-gray-900">
                                                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                                                                        @if($lote->status === 'HOMOLOGADO') bg-green-100 text-green-800
                                                                                        @elseif($lote->status === 'ADJUDICADO') bg-blue-100 text-blue-800
                                                                                        @else bg-gray-100 text-gray-800 @endif">
                                                                                        {{ $lote->status }}
                                                                                    </span>
                                                                                </td>
                                                                                <td class="px-4 py-2 text-sm text-gray-900">
                                                                                    {{ $lote->item }}
                                                                                </td>
                                                                                <td class="px-4 py-2 text-sm text-gray-900">
                                                                                    <div class="max-w-xs truncate" title="{{ $lote->descricao }}">
                                                                                        {{ $lote->descricao }}
                                                                                    </div>
                                                                                </td>
                                                                                <td class="px-4 py-2 text-sm text-gray-900">
                                                                                    {{ $lote->unidade }}
                                                                                </td>
                                                                                <td class="px-4 py-2 text-sm text-gray-900">
                                                                                    {{ $lote->marca }}
                                                                                </td>
                                                                                <td class="px-4 py-2 text-sm text-gray-900">
                                                                                    {{ $lote->modelo }}
                                                                                </td>
                                                                                <td class="px-4 py-2 text-sm text-right text-gray-900">
                                                                                    {{ number_format($lote->quantidade, 0, ',', '.') }}
                                                                                </td>
                                                                                <td class="px-4 py-2 text-sm text-right text-gray-900">
                                                                                    R$ {{ number_format($lote->vl_unit, 2, ',', '.') }}
                                                                                </td>
                                                                                <td class="px-4 py-2 text-sm font-semibold text-right text-gray-900">
                                                                                    R$ {{ number_format($lote->vl_total, 2, ',', '.') }}
                                                                                </td>
                                                                            </tr>
                                                                            @endforeach
                                                                            <!-- Linha de totais do lote -->
                                                                            <tr class="font-semibold bg-gray-100">
                                                                                <td class="px-4 py-2 text-sm text-gray-900" colspan="6">
                                                                                    TOTAL DO LOTE {{ $numeroLote }}
                                                                                </td>
                                                                                <td class="px-4 py-2 text-sm text-right text-gray-900">
                                                                                    {{ number_format($itensLote->sum('quantidade'), 0, ',', '.') }}
                                                                                </td>
                                                                                <td class="px-4 py-2 text-sm text-right text-gray-900">
                                                                                    -
                                                                                </td>
                                                                                <td class="px-4 py-2 text-sm text-right text-green-700">
                                                                                    R$ {{ number_format($itensLote->sum('vl_total'), 2, ',', '.') }}
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                            @endforeach

                                                            <!-- Total geral do vencedor -->
                                                            <div class="p-4 mt-4 border border-blue-200 rounded-lg bg-blue-50">
                                                                <div class="flex items-center justify-between">
                                                                    <span class="text-lg font-bold text-blue-800">TOTAL GERAL DO VENCEDOR</span>
                                                                    <span class="text-lg font-bold text-blue-800">
                                                                        R$ {{ number_format($vencedor->lotes->sum('vl_total'), 2, ',', '.') }}
                                                                    </span>
                                                                </div>
                                                            </div>

                                                        @else
                                                            <!-- Estrutura para ITEM - Listar todos os itens -->
                                                            <div class="overflow-x-auto">
                                                                <table class="min-w-full divide-y divide-gray-200">
                                                                    <thead class="bg-gray-100">
                                                                        <tr>
                                                                            <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                                Status
                                                                            </th>
                                                                            <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                                Item
                                                                            </th>
                                                                            <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                                Descrição
                                                                            </th>
                                                                            <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                                UNIDADE
                                                                            </th>
                                                                            <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                                Marca
                                                                            </th>
                                                                            <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                                Modelo
                                                                            </th>
                                                                            <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                                Quantidade
                                                                            </th>
                                                                            <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                                Vl. Unit
                                                                            </th>
                                                                            <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                                Vl. Total
                                                                            </th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody class="bg-white divide-y divide-gray-200">
                                                                        @foreach($vencedor->lotes as $lote)
                                                                        <tr class="hover:bg-gray-50">
                                                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                                                                    @if($lote->status === 'HOMOLOGADO') bg-green-100 text-green-800
                                                                                    @elseif($lote->status === 'ADJUDICADO') bg-blue-100 text-blue-800
                                                                                    @else bg-gray-100 text-gray-800 @endif">
                                                                                    {{ $lote->status }}
                                                                                </span>
                                                                            </td>
                                                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                                                {{ $lote->item }}
                                                                            </td>
                                                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                                                <div class="max-w-xs truncate" title="{{ $lote->descricao }}">
                                                                                    {{ $lote->descricao }}
                                                                                </div>
                                                                            </td>
                                                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                                                {{ $lote->unidade }}
                                                                            </td>
                                                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                                                {{ $lote->marca }}
                                                                            </td>
                                                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                                                {{ $lote->modelo }}
                                                                            </td>
                                                                            <td class="px-4 py-2 text-sm text-right text-gray-900">
                                                                                {{ number_format($lote->quantidade, 0, ',', '.') }}
                                                                            </td>
                                                                            <td class="px-4 py-2 text-sm text-right text-gray-900">
                                                                                R$ {{ number_format($lote->vl_unit, 2, ',', '.') }}
                                                                            </td>
                                                                            <td class="px-4 py-2 text-sm font-semibold text-right text-gray-900">
                                                                                R$ {{ number_format($lote->vl_total, 2, ',', '.') }}
                                                                            </td>
                                                                        </tr>
                                                                        @endforeach
                                                                        <!-- Linha de totais -->
                                                                        <tr class="font-semibold bg-gray-100">
                                                                            <td class="px-4 py-2 text-sm text-gray-900" colspan="6">
                                                                                TOTAL GERAL
                                                                            </td>
                                                                            <td class="px-4 py-2 text-sm text-right text-gray-900">
                                                                                {{ number_format($vencedor->lotes->sum('quantidade'), 0, ',', '.') }}
                                                                            </td>
                                                                            <td class="px-4 py-2 text-sm text-right text-gray-900">
                                                                                -
                                                                            </td>
                                                                            <td class="px-4 py-2 text-sm text-right text-green-700">
                                                                                R$ {{ number_format($vencedor->lotes->sum('vl_total'), 2, ',', '.') }}
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @endif
                                        @endforeach
                                    @else
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-sm text-center text-gray-500">
                                            Nenhum vencedor cadastrado
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Seção de Reservas -->
                <div class="mb-8">
                    <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-2xl">
                        <!-- Header -->
                        <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-yellow-50 to-yellow-100">
                            <div class="flex flex-col items-start justify-between lg:flex-row lg:items-center">
                                <h3 class="text-xl font-semibold text-gray-800">Empresas Reservas do Processo</h3>
                                <button type="button"
                                        onclick="abrirModalReserva()"
                                        class="px-4 py-2 mt-2 text-sm font-medium text-white bg-yellow-600 rounded-md lg:mt-0 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2">
                                    ➕ Adicionar Reserva
                                </button>
                            </div>
                        </div>

                        <!-- Tabela de Reservas -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                            Razão Social
                                        </th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                            CNPJ
                                        </th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                            Endereço
                                        </th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                            Telefone
                                        </th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                            E-mail
                                        </th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                            Representante Legal
                                        </th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase">
                                            Ações
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200" id="reservas-tbody">
                                    @if(isset($processo->reservas) && count($processo->reservas) > 0)
                                        @foreach($processo->reservas as $index => $reserva)
                                        <tr class="reserva-row" data-reserva-id="{{ $reserva->id ?? '' }}">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $reserva->razao_social }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $reserva->cnpj }}</div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="max-w-xs text-sm text-gray-900 truncate" title="{{ $reserva->endereco }}">
                                                    {{ $reserva->endereco ?? '-' }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $reserva->telefone ?? '-' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $reserva->email ?? '-' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $reserva->representante_legal ?? '-' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex justify-center space-x-2">
                                                    <button type="button"
                                                            onclick="editarReserva({{ $index }})"
                                                            class="px-3 py-1 text-sm text-blue-600 bg-blue-100 rounded-md hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                        ✏️ Editar
                                                    </button>
                                                    <button type="button"
                                                            onclick="removerReserva({{ $index }})"
                                                            class="px-3 py-1 text-sm text-red-600 bg-red-100 rounded-md hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500">
                                                        🗑️ Remover
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    @else
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-sm text-center text-gray-500">
                                            Nenhuma empresa reserva cadastrada
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal para Adicionar/Editar Vencedor -->
    <div id="vencedorModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="fecharModal()"></div>

            <div class="inline-block w-full max-w-2xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl">
                <div class="px-6 py-4 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium leading-6 text-gray-900" id="modalTitle">
                        Adicionar Vencedor
                    </h3>
                </div>

                <form id="vencedorForm" onsubmit="salvarVencedor(event)">
                    <div class="px-6 py-4">
                        <input type="hidden" id="vencedorIndex" value="">
                        <input type="hidden" id="vencedorId" value="">

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label for="razao_social" class="block text-sm font-medium text-gray-700">Razão Social *</label>
                                <input type="text"
                                    id="razao_social"
                                    name="razao_social"
                                    required
                                    class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Razão Social">
                            </div>
                            <div>
                                <label for="cnpj" class="block text-sm font-medium text-gray-700">CNPJ *</label>
                                <input type="text"
                                    id="cnpj"
                                    name="cnpj"
                                    required
                                    class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 cnpj-mask"
                                    placeholder="00.000.000/0000-00">
                            </div>
                            <div>
                                <label for="representante" class="block text-sm font-medium text-gray-700">Representante *</label>
                                <input type="text"
                                    id="representante"
                                    name="representante"
                                    required
                                    class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Nome do Representante">
                            </div>
                            <div>
                                <label for="cpf" class="block text-sm font-medium text-gray-700">CPF *</label>
                                <input type="text"
                                    id="cpf"
                                    name="cpf"
                                    required
                                    class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 cpf-mask"
                                    placeholder="000.000.000-00">
                            </div>
                            <div>
                                <label for="endereco" class="block text-sm font-medium text-gray-700">Endereco *</label>
                                <input type="text"
                                    id="endereco"
                                    name="endereco"
                                    required
                                    class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Endereço completo">
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 sm:flex sm:flex-row-reverse">
                        <button type="submit"
                                class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Salvar
                        </button>
                        <button type="button"
                                onclick="fecharModal()"
                                class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Importação de Itens por Vencedor -->
    <div id="importarItensModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="fecharImportarModal()"></div>

            <div class="inline-block w-full max-w-2xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl">
                <div class="px-6 py-4 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium leading-6 text-gray-900" id="importarModalTitle">
                        Importar {{ $processo->tipo_contratacao === 'LOTE' ? 'Lotes' : 'Itens' }} para Vencedor
                    </h3>
                </div>

                <div class="px-6 py-4">
                    <input type="hidden" id="importarVencedorIndex">

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">
                            Selecione o arquivo Excel:
                        </label>
                        <input type="file"
                            id="excelFileVencedor"
                            accept=".xlsx,.xls,.csv"
                            class="block w-full mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-xs text-gray-500">
                            Formatos suportados: .xlsx, .xls, .csv
                        </p>
                    </div>

                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" id="sobrescreverVencedor" class="text-blue-600 border-gray-300 rounded shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Sobrescrever dados existentes</span>
                        </label>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 sm:flex sm:flex-row-reverse">
                    <button type="button"
                            onclick="processarExcelVencedor()"
                            class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Importar
                    </button>
                    <button type="button"
                            onclick="fecharImportarModal()"
                            class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Adicionar/Editar Reserva -->
    <div id="reservaModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="fecharModalReserva()"></div>

            <div class="inline-block w-full max-w-2xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl">
                <div class="px-6 py-4 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium leading-6 text-gray-900" id="reservaModalTitle">
                        Adicionar Reserva
                    </h3>
                </div>

                <form id="reservaForm" onsubmit="salvarReserva(event)">
                    <div class="px-6 py-4">
                        <input type="hidden" id="reservaIndex" value="">
                        <input type="hidden" id="reservaId" value="">

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label for="reserva_razao_social" class="block text-sm font-medium text-gray-700">Razão Social *</label>
                                <input type="text"
                                    id="reserva_razao_social"
                                    name="razao_social"
                                    required
                                    class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500"
                                    placeholder="Razão Social">
                            </div>
                            <div>
                                <label for="reserva_cnpj" class="block text-sm font-medium text-gray-700">CNPJ *</label>
                                <input type="text"
                                    id="reserva_cnpj"
                                    name="cnpj"
                                    required
                                    class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 cnpj-mask"
                                    placeholder="00.000.000/0000-00">
                            </div>
                            <div>
                                <label for="reserva_telefone" class="block text-sm font-medium text-gray-700">Telefone</label>
                                <input type="text"
                                    id="reserva_telefone"
                                    name="telefone"
                                    class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 telefone-mask"
                                    placeholder="(00) 00000-0000">
                            </div>
                            <div class="md:col-span-2">
                                <label for="reserva_endereco" class="block text-sm font-medium text-gray-700">Endereço</label>
                                <input type="text"
                                    id="reserva_endereco"
                                    name="endereco"
                                    class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500"
                                    placeholder="Endereço completo">
                            </div>
                            <div>
                                <label for="reserva_email" class="block text-sm font-medium text-gray-700">E-mail</label>
                                <input type="email"
                                    id="reserva_email"
                                    name="email"
                                    class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500"
                                    placeholder="email@exemplo.com">
                            </div>
                            <div>
                                <label for="reserva_representante_legal" class="block text-sm font-medium text-gray-700">Representante Legal</label>
                                <input type="text"
                                    id="reserva_representante_legal"
                                    name="representante_legal"
                                    class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500"
                                    placeholder="Nome do Representante Legal">
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 sm:flex sm:flex-row-reverse">
                        <button type="submit"
                                class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-yellow-600 border border-transparent rounded-md shadow-sm hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Salvar
                        </button>
                        <button type="button"
                                onclick="fecharModalReserva()"
                                class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Dados das reservas
        let reservas = @json($processo->reservas ?? []);

        // Funções do Modal de Reserva
        function abrirModalReserva() {
            document.getElementById('reservaModalTitle').textContent = 'Adicionar Reserva';
            document.getElementById('reservaIndex').value = '';
            document.getElementById('reservaId').value = '';
            document.getElementById('reservaForm').reset();

            const modal = document.getElementById('reservaModal');
            modal.classList.remove('hidden');
            modal.style.display = 'block';
        }

        function editarReserva(index) {
            const reserva = reservas[index];

            document.getElementById('reservaModalTitle').textContent = 'Editar Reserva';
            document.getElementById('reservaIndex').value = index;
            document.getElementById('reservaId').value = reserva.id || '';
            document.getElementById('reserva_razao_social').value = reserva.razao_social;
            document.getElementById('reserva_cnpj').value = reserva.cnpj;
            document.getElementById('reserva_endereco').value = reserva.endereco || '';
            document.getElementById('reserva_telefone').value = reserva.telefone || '';
            document.getElementById('reserva_email').value = reserva.email || '';
            document.getElementById('reserva_representante_legal').value = reserva.representante_legal || '';

            const modal = document.getElementById('reservaModal');
            modal.classList.remove('hidden');
            modal.style.display = 'block';
        }

        function fecharModalReserva() {
            const modal = document.getElementById('reservaModal');
            modal.classList.add('hidden');
            modal.style.display = 'none';
        }

        // Função para salvar reserva
        function salvarReserva(event) {
            event.preventDefault();

            const formData = new FormData(event.target);
            const reservaIndex = document.getElementById('reservaIndex').value;
            const reservaId = document.getElementById('reservaId').value;

            const reservaData = {
                id: reservaId,
                razao_social: formData.get('razao_social'),
                cnpj: formData.get('cnpj'),
                endereco: formData.get('endereco'),
                telefone: formData.get('telefone'),
                email: formData.get('email'),
                representante_legal: formData.get('representante_legal')
            };

            // Criar uma nova lista de reservas que inclui todas as existentes + a nova/editada
            let reservasAtualizadas = [];

            if (reservaIndex !== '') {
                // Se está editando, substitui a reserva na posição correta
                reservasAtualizadas = [...reservas];
                reservasAtualizadas[reservaIndex] = reservaData;
            } else {
                // Se está adicionando nova, adiciona ao final
                reservasAtualizadas = [...reservas, reservaData];
            }

            // Enviar para o servidor TODAS as reservas
            fetch('{{ route("admin.processos.finalizacao.reservas.store", $processo) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    reservas: reservasAtualizadas,
                    reserva_index: reservaIndex
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('Reserva salva com sucesso!', 'success');
                    fecharModalReserva();
                    atualizarTabelaReservas();
                } else {
                    showMessage('Erro ao salvar reserva: ' + data.message, 'error');
                }
            })
            .catch(error => {
                showMessage('Erro ao salvar reserva: ' + error, 'error');
            });
        }

        // Função para remover reserva
        function removerReserva(index) {
            if (!confirm('Tem certeza que deseja remover esta reserva?')) {
                return;
            }

            const reservaId = reservas[index]?.id;

            // Criar nova lista sem a reserva removida
            const reservasAtualizadas = reservas.filter((_, i) => i !== index);

            fetch('{{ route("admin.processos.finalizacao.reservas.store", $processo) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    reservas: reservasAtualizadas,
                    remover_reserva: reservaId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('Reserva removida com sucesso!', 'success');
                    atualizarTabelaReservas();
                } else {
                    showMessage('Erro ao remover reserva: ' + data.message, 'error');
                }
            })
            .catch(error => {
                showMessage('Erro ao remover reserva: ' + error, 'error');
            });
        }

        // Atualizar tabela de reservas
        function atualizarTabelaReservas() {
            fetch('{{ route("admin.processos.finalizacao.reservas.get", $processo) }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        reservas = data.reservas;
                        const tbody = document.getElementById('reservas-tbody');

                        if (reservas.length === 0) {
                            tbody.innerHTML = `
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-sm text-center text-gray-500">
                                        Nenhuma empresa reserva cadastrada
                                    </td>
                                </tr>
                            `;
                            return;
                        }

                        tbody.innerHTML = reservas.map((reserva, index) => {
                            return `
                                <tr class="reserva-row" data-reserva-id="${reserva.id || ''}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">${reserva.razao_social}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">${reserva.cnpj}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="max-w-xs text-sm text-gray-900 truncate" title="${reserva.endereco || ''}">
                                            ${reserva.endereco || '-'}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">${reserva.telefone || '-'}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">${reserva.email || '-'}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">${reserva.representante_legal || '-'}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex justify-center space-x-2">
                                            <button type="button"
                                                    onclick="editarReserva(${index})"
                                                    class="px-3 py-1 text-sm text-blue-600 bg-blue-100 rounded-md hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                ✏️ Editar
                                            </button>
                                            <button type="button"
                                                    onclick="removerReserva(${index})"
                                                    class="px-3 py-1 text-sm text-red-600 bg-red-100 rounded-md hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500">
                                                🗑️ Remover
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        }).join('');
                    }
                })
                .catch(error => {
                    console.error('Erro ao atualizar reservas:', error);
                });
        }

        // Adicionar máscaras
        function aplicarMascarasAdicionais() {
            // Máscara de telefone
            document.querySelectorAll('.telefone-mask').forEach(input => {
                input.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length > 11) {
                        value = value.substring(0, 11);
                    }

                    if (value.length > 10) {
                        value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
                    } else if (value.length > 6) {
                        value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
                    } else if (value.length > 2) {
                        value = value.replace(/(\d{2})(\d{0,5})/, '($1) $2');
                    } else if (value.length > 0) {
                        value = value.replace(/(\d{0,2})/, '($1');
                    }
                    e.target.value = value;
                });
            });
        }

        // Inicializar as máscaras adicionais
        document.addEventListener('DOMContentLoaded', function() {
            aplicarMascarasAdicionais();
        });
    </script>

    <script>
        // Dados dos vencedores
        let vencedores = @json($processo->vencedores ?? []);
        let editandoIndex = null;

        // Funções do Modal de Vencedor
        function abrirModalVencedor() {
            document.getElementById('modalTitle').textContent = 'Adicionar Vencedor';
            document.getElementById('vencedorIndex').value = '';
            document.getElementById('vencedorId').value = '';
            document.getElementById('vencedorForm').reset();

            const modal = document.getElementById('vencedorModal');
            modal.classList.remove('hidden');
            modal.style.display = 'block';
        }

        function editarVencedor(index) {
            const vencedor = vencedores[index];

            document.getElementById('modalTitle').textContent = 'Editar Vencedor';
            document.getElementById('vencedorIndex').value = index;
            document.getElementById('vencedorId').value = vencedor.id || '';
            document.getElementById('razao_social').value = vencedor.razao_social;
            document.getElementById('cnpj').value = vencedor.cnpj;
            document.getElementById('representante').value = vencedor.representante;
            document.getElementById('cpf').value = vencedor.cpf;
            document.getElementById('endereco').value = vencedor.endereco;

            const modal = document.getElementById('vencedorModal');
            modal.classList.remove('hidden');
            modal.style.display = 'block';
        }

        function fecharModal() {
            const modal = document.getElementById('vencedorModal');
            modal.classList.add('hidden');
            modal.style.display = 'none';
        }

        // Funções do Modal de Importação
        function importarItensVencedor(vencedorIndex) {
            document.getElementById('importarVencedorIndex').value = vencedorIndex;
            document.getElementById('importarModalTitle').textContent =
                'Importar {{ $processo->tipo_contratacao === 'LOTE' ? 'Lotes' : 'Itens' }} para Vencedor ' + (parseInt(vencedorIndex) + 1);

            const modal = document.getElementById('importarItensModal');
            modal.classList.remove('hidden');
            modal.style.display = 'block';
        }

        function fecharImportarModal() {
            const modal = document.getElementById('importarItensModal');
            modal.classList.add('hidden');
            modal.style.display = 'none';
        }

        // Função para salvar vencedor
        function salvarVencedor(event) {
            event.preventDefault();

            console.log('=== DEBUG INICIO salvarVencedor ===');

            const formData = new FormData(event.target);
            
            const vencedorIndex = document.getElementById('vencedorIndex').value;
            const vencedorId = document.getElementById('vencedorId').value;

            // Criar objeto apenas com os dados do vencedor atual
            const vencedorData = {
                id: vencedorId || null, // Envia null se for novo
                razao_social: formData.get('razao_social'),
                cnpj: formData.get('cnpj'),
                representante: formData.get('representante'),
                cpf: formData.get('cpf'),
                endereco: formData.get('endereco'),
                lotes: []
            };

            console.log('VencedorData a ser enviado:', vencedorData);

            // Se está editando, preserva os lotes existentes
            if (vencedorIndex !== '' && vencedores[vencedorIndex]) {
                vencedorData.lotes = vencedores[vencedorIndex].lotes || [];
                console.log('Preservando lotes existentes:', vencedorData.lotes);
            }

            // Preparar dados para enviar
            let requestData = {};
            
            if (vencedorIndex !== '') {
                // Se está editando, envia apenas o vencedor específico
                requestData = {
                    vencedor_id: vencedorId, // ID do vencedor sendo editado
                    vencedor_data: vencedorData,
                    vencedor_index: vencedorIndex,
                    operacao: 'editar'
                };
                console.log('Editando vencedor:', requestData);
            } else {
                // Se está adicionando novo
                requestData = {
                    vencedor_data: vencedorData,
                    operacao: 'adicionar'
                };
                console.log('Adicionando novo vencedor:', requestData);
            }

            console.log('=== DEBUG FIM ===');

            // Enviar para o servidor APENAS o vencedor atual
            fetch('{{ route("admin.processos.finalizacao.vencedores.store", $processo) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(requestData)
            })
            .then(response => {
                console.log('Resposta bruta:', response);
                return response.json();
            })
            .then(data => {
                console.log('Resposta JSON:', data);
                if (data.success) {
                    showMessage('Vencedor salvo com sucesso!', 'success');
                    fecharModal();
                    atualizarTabelaVencedores(); // Isso vai recarregar todos os vencedores do servidor
                } else {
                    showMessage('Erro ao salvar vencedor: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
                showMessage('Erro ao salvar vencedor: ' + error, 'error');
            });
        }
        // Função para remover vencedor
        function removerVencedor(index) {
            if (!confirm('Tem certeza que deseja remover este vencedor?')) {
                return;
            }

            const vencedorId = vencedores[index]?.id;

            // Criar nova lista sem o vencedor removido
            const vencedoresAtualizados = vencedores.filter((_, i) => i !== index);

            fetch('{{ route("admin.processos.finalizacao.vencedores.store", $processo) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    vencedores: vencedoresAtualizados,
                    remover_vencedor: vencedorId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('Vencedor removido com sucesso!', 'success');
                    atualizarTabelaVencedores();
                } else {
                    showMessage('Erro ao remover vencedor: ' + data.message, 'error');
                }
            })
            .catch(error => {
                showMessage('Erro ao remover vencedor: ' + error, 'error');
            });
        }

        // Função para processar Excel do vencedor
        function processarExcelVencedor() {
            const vencedorIndex = document.getElementById('importarVencedorIndex').value;
            const fileInput = document.getElementById('excelFileVencedor');
            const file = fileInput.files[0];

            if (!file) {
                showMessage('Por favor, selecione um arquivo Excel.', 'error');
                return;
            }

            const allowedTypes = ['.xlsx', '.xls', '.csv'];
            const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
            if (!allowedTypes.includes(fileExtension)) {
                showMessage('Tipo de arquivo não permitido. Use .xlsx, .xls ou .csv.', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('excel_file', file);
            formData.append('processo_id', {{ $processo->id }});
            formData.append('tipo_contratacao', '{{ $processo->tipo_contratacao }}');
            formData.append('vencedor_index', vencedorIndex);
            formData.append('_token', '{{ csrf_token() }}');

            const importButton = document.querySelector('#importarItensModal button[onclick="processarExcelVencedor()"]');
            const originalText = importButton.textContent;
            importButton.textContent = 'Importando...';
            importButton.disabled = true;

            showMessage('Processando arquivo Excel...', 'info');

            fetch("{{ route('admin.processos.finalizacao.importar-excel', $processo) }}", {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    fecharImportarModal();
                    atualizarTabelaVencedores();
                } else {
                    showMessage(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erro completo:', error);
                showMessage('Erro ao processar arquivo: ' + error.message, 'error');
            })
            .finally(() => {
                importButton.textContent = originalText;
                importButton.disabled = false;
            });
        }

        // Atualizar tabela de vencedores
        function atualizarTabelaVencedores() {
            fetch('{{ route("admin.processos.finalizacao.vencedores.get", $processo) }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        vencedores = data.vencedores;
                        const tbody = document.getElementById('vencedores-tbody');

                        if (vencedores.length === 0) {
                            tbody.innerHTML = `
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-sm text-center text-gray-500">
                                        Nenhum vencedor cadastrado
                                    </td>
                                </tr>
                            `;
                            return;
                        }

                        tbody.innerHTML = vencedores.map((vencedor, index) => {
                            const hasLotes = vencedor.lotes && vencedor.lotes.length > 0;
                            const lotesCount = hasLotes ? vencedor.lotes.length : 0;
                            const tipoItem = '{{ $processo->tipo_contratacao === 'LOTE' ? 'lotes' : 'itens' }}';

                            let lotesHtml = '';
                            if (hasLotes) {
                                // Agrupar por lote se for tipo LOTE
                                if ('{{ $processo->tipo_contratacao === 'LOTE' }}') {
                                    const lotesAgrupados = vencedor.lotes.reduce((acc, lote) => {
                                        const loteNumero = lote.lote || 'Sem Lote';
                                        if (!acc[loteNumero]) {
                                            acc[loteNumero] = [];
                                        }
                                        acc[loteNumero].push(lote);
                                        return acc;
                                    }, {});

                                    const lotesAgrupadosHtml = Object.entries(lotesAgrupados).map(([numeroLote, itensLote]) => {
                                        const totalLote = itensLote.reduce((sum, item) => sum + parseFloat(item.vl_total), 0);
                                        const quantidadeLote = itensLote.reduce((sum, item) => sum + parseFloat(item.quantidade), 0);

                                        return `
                                            <div class="mb-6 border border-gray-200 rounded-lg">
                                                <div class="px-4 py-3 bg-gray-100 border-b border-gray-200">
                                                    <h5 class="font-semibold text-gray-800">
                                                        LOTE ${numeroLote}
                                                    </h5>
                                                </div>
                                                <div class="overflow-x-auto">
                                                    <table class="min-w-full divide-y divide-gray-200">
                                                        <thead class="bg-gray-50">
                                                            <tr>
                                                                <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                    Status
                                                                </th>
                                                                <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                    Item
                                                                </th>
                                                                <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                    Descrição
                                                                </th>
                                                                <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                    UNIDADE
                                                                </th>
                                                                <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                    Marca
                                                                </th>
                                                                <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                    Modelo
                                                                </th>
                                                                <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                    Quantidade
                                                                </th>
                                                                <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                    Vl. Unit
                                                                </th>
                                                                <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                                    Vl. Total
                                                                </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="bg-white divide-y divide-gray-200">
                                                            ${itensLote.map(lote => `
                                                            <tr class="hover:bg-gray-50">
                                                                <td class="px-4 py-2 text-sm text-gray-900">
                                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                                                        ${lote.status === 'HOMOLOGADO' ? 'bg-green-100 text-green-800' :
                                                                        lote.status === 'ADJUDICADO' ? 'bg-blue-100 text-blue-800' :
                                                                        'bg-gray-100 text-gray-800'}">
                                                                        ${lote.status}
                                                                    </span>
                                                                </td>
                                                                <td class="px-4 py-2 text-sm text-gray-900">
                                                                    ${lote.item}
                                                                </td>
                                                                <td class="px-4 py-2 text-sm text-gray-900">
                                                                    <div class="max-w-xs truncate" title="${lote.descricao}">
                                                                        ${lote.descricao}
                                                                    </div>
                                                                </td>
                                                                <td class="px-4 py-2 text-sm text-gray-900">
                                                                    ${lote.unidade}
                                                                </td>
                                                                <td class="px-4 py-2 text-sm text-gray-900">
                                                                    ${lote.marca || '-'}
                                                                </td>
                                                                <td class="px-4 py-2 text-sm text-gray-900">
                                                                    ${lote.modelo || '-'}
                                                                </td>
                                                                <td class="px-4 py-2 text-sm text-right text-gray-900">
                                                                    ${parseFloat(lote.quantidade).toLocaleString('pt-BR')}
                                                                </td>
                                                                <td class="px-4 py-2 text-sm text-right text-gray-900">
                                                                    R$ ${parseFloat(lote.vl_unit).toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                                                                </td>
                                                                <td class="px-4 py-2 text-sm font-semibold text-right text-gray-900">
                                                                    R$ ${parseFloat(lote.vl_total).toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                                                                </td>
                                                            </tr>
                                                            `).join('')}
                                                            <!-- Linha de totais do lote -->
                                                            <tr class="font-semibold bg-gray-100">
                                                                <td class="px-4 py-2 text-sm text-gray-900" colspan="6">
                                                                    TOTAL DO LOTE ${numeroLote}
                                                                </td>
                                                                <td class="px-4 py-2 text-sm text-right text-gray-900">
                                                                    ${quantidadeLote.toLocaleString('pt-BR')}
                                                                </td>
                                                                <td class="px-4 py-2 text-sm text-right text-gray-900">
                                                                    -
                                                                </td>
                                                                <td class="px-4 py-2 text-sm text-right text-green-700">
                                                                    R$ ${totalLote.toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        `;
                                    }).join('');

                                    const totalGeral = vencedor.lotes.reduce((sum, lote) => sum + parseFloat(lote.vl_total), 0);

                                    lotesHtml = `
                                        ${lotesAgrupadosHtml}
                                        <div class="p-4 mt-4 border border-blue-200 rounded-lg bg-blue-50">
                                            <div class="flex items-center justify-between">
                                                <span class="text-lg font-bold text-blue-800">TOTAL GERAL DO VENCEDOR</span>
                                                <span class="text-lg font-bold text-blue-800">
                                                    R$ ${totalGeral.toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                                                </span>
                                            </div>
                                        </div>
                                    `;
                                } else {
                                    // Estrutura para ITEM
                                    const totalGeral = vencedor.lotes.reduce((sum, lote) => sum + parseFloat(lote.vl_total), 0);
                                    const quantidadeGeral = vencedor.lotes.reduce((sum, lote) => sum + parseFloat(lote.quantidade), 0);

                                    lotesHtml = `
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-100">
                                                    <tr>
                                                        <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                            Status
                                                        </th>
                                                        <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                            Item
                                                        </th>
                                                        <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                            Descrição
                                                        </th>
                                                        <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                            UNIDADE
                                                        </th>
                                                        <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                            Marca
                                                        </th>
                                                        <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                            Modelo
                                                        </th>
                                                        <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                            Quantidade
                                                        </th>
                                                        <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                            Vl. Unit
                                                        </th>
                                                        <th class="px-4 py-2 text-xs font-medium tracking-wider text-left text-gray-700 uppercase">
                                                            Vl. Total
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    ${vencedor.lotes.map(lote => `
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="px-4 py-2 text-sm text-gray-900">
                                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                                                ${lote.status === 'HOMOLOGADO' ? 'bg-green-100 text-green-800' :
                                                                lote.status === 'ADJUDICADO' ? 'bg-blue-100 text-blue-800' :
                                                                'bg-gray-100 text-gray-800'}">
                                                                ${lote.status}
                                                            </span>
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-gray-900">
                                                            ${lote.item}
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-gray-900">
                                                            <div class="max-w-xs truncate" title="${lote.descricao}">
                                                                ${lote.descricao}
                                                            </div>
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-gray-900">
                                                            ${lote.unidade}
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-gray-900">
                                                            ${lote.marca || '-'}
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-gray-900">
                                                            ${lote.modelo || '-'}
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-right text-gray-900">
                                                            ${parseFloat(lote.quantidade).toLocaleString('pt-BR')}
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-right text-gray-900">
                                                            R$ ${parseFloat(lote.vl_unit).toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                                                        </td>
                                                        <td class="px-4 py-2 text-sm font-semibold text-right text-gray-900">
                                                            R$ ${parseFloat(lote.vl_total).toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                                                        </td>
                                                    </tr>
                                                    `).join('')}
                                                    <!-- Linha de totais -->
                                                    <tr class="font-semibold bg-gray-100">
                                                        <td class="px-4 py-2 text-sm text-gray-900" colspan="6">
                                                            TOTAL GERAL
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-right text-gray-900">
                                                            ${quantidadeGeral.toLocaleString('pt-BR')}
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-right text-gray-900">
                                                            -
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-right text-green-700">
                                                            R$ ${totalGeral.toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    `;
                                }
                            }

                            return `
                                <tr class="vencedor-row" data-vencedor-id="${vencedor.id || ''}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">${vencedor.razao_social}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">${vencedor.cnpj}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">${vencedor.representante}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">${vencedor.cpf}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            ${hasLotes ?
                                                `${lotesCount} ${tipoItem}` :
                                                '<span class="text-gray-400">Nenhum</span>'}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex justify-center space-x-2">
                                            <button type="button"
                                                    onclick="editarVencedor(${index})"
                                                    class="px-3 py-1 text-sm text-blue-600 bg-blue-100 rounded-md hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                ✏️ Editar
                                            </button>
                                            <button type="button"
                                                    onclick="importarItensVencedor(${index})"
                                                    class="px-3 py-1 text-sm text-green-600 bg-green-100 rounded-md hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-500">
                                                📊 Importar Itens
                                            </button>
                                            <button type="button"
                                                    onclick="removerVencedor(${index})"
                                                    class="px-3 py-1 text-sm text-red-600 bg-red-100 rounded-md hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500">
                                                🗑️ Remover
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                ${hasLotes ? `
                                <tr class="bg-gray-50">
                                    <td colspan="6" class="px-6 py-4">
                                        <div class="lotes-container">
                                            <div class="flex items-center justify-between mb-3">
                                                <h4 class="text-lg font-semibold text-gray-800">
                                                    ${tipoItem === 'lotes' ? 'Lotes' : 'Itens'} do Vencedor
                                                </h4>
                                                <button type="button"
                                                        onclick="toggleLotes(${index})"
                                                        class="flex items-center text-sm text-blue-600 hover:text-blue-800">
                                                    <span id="toggle-text-${index}">Mostrar Detalhes</span>
                                                    <svg id="toggle-icon-${index}" class="w-4 h-4 ml-1 transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                    </svg>
                                                </button>
                                            </div>

                                            <div id="lotes-details-${index}" class="hidden">
                                                ${lotesHtml}
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                ` : ''}
                            `;
                        }).join('');
                    }
                })
                .catch(error => {
                    console.error('Erro ao atualizar vencedores:', error);
                });
        }

        // Função para mostrar/ocultar lotes
        function toggleLotes(index) {
            const details = document.getElementById(`lotes-details-${index}`);
            const toggleText = document.getElementById(`toggle-text-${index}`);
            const toggleIcon = document.getElementById(`toggle-icon-${index}`);

            if (details.classList.contains('hidden')) {
                details.classList.remove('hidden');
                toggleText.textContent = 'Ocultar Detalhes';
                toggleIcon.classList.add('rotate-180');
            } else {
                details.classList.add('hidden');
                toggleText.textContent = 'Mostrar Detalhes';
                toggleIcon.classList.remove('rotate-180');
            }
        }

        // Funções auxiliares
        function aplicarMascaras() {
            // Máscara de CNPJ
            document.querySelectorAll('.cnpj-mask').forEach(input => {
                input.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length <= 14) {
                        value = value.replace(/(\d{2})(\d)/, '$1.$2')
                                    .replace(/(\d{3})(\d)/, '$1.$2')
                                    .replace(/(\d{3})(\d)/, '$1/$2')
                                    .replace(/(\d{4})(\d)/, '$1-$2');
                        e.target.value = value;
                    }
                });
            });

            // Máscara de CPF
            document.querySelectorAll('.cpf-mask').forEach(input => {
                input.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length <= 11) {
                        value = value.replace(/(\d{3})(\d)/, '$1.$2')
                                    .replace(/(\d{3})(\d)/, '$1.$2')
                                    .replace(/(\d{3})(\d)/, '$1-$2');
                        e.target.value = value;
                    }
                });
            });
        }

        // Inicializar
        document.addEventListener('DOMContentLoaded', function() {
            aplicarMascaras();
        });
    </script>

    <script>
        // Funções existentes para documentos (mantidas da view original)
        document.addEventListener('DOMContentLoaded', function() {
            // Inicialização da funcionalidade de acordeão
            const collapseButtons = document.querySelectorAll('[data-collapse-toggle]');
            if (collapseButtons.length > 0) {
                collapseButtons.forEach(button => {
                    button.addEventListener('click', () => {
                        const targetId = button.getAttribute('data-collapse-toggle');
                        const targetEl = document.getElementById(targetId);
                        const isExpanded = button.getAttribute('aria-expanded') === 'true';
                        const span = button.querySelector('.collapse-text');

                        if (isExpanded) {
                            targetEl.classList.add('hidden');
                            button.setAttribute('aria-expanded', 'false');
                            span.textContent = 'Definir Campos e Assinantes';
                        } else {
                            targetEl.classList.remove('hidden');
                            button.setAttribute('aria-expanded', 'true');
                            span.textContent = 'Ocultar Campos e Assinantes';
                        }
                    });
                });
            }

            // Inicialização do TinyMCE
            document.querySelectorAll('textarea[x-ref$="_editor"]').forEach(textarea => {
                tinymce.init({
                    selector: '#' + textarea.id,
                    plugins: 'advlist lists link table code charmap emoticons',
                    toolbar: 'undo redo | bold italic underline | bullist numlist | styleselect | link table | emoticons charmap | code',
                    menubar: false,
                    branding: false,
                    height: 300,
                    advlist_bullet_styles: 'default,circle,square',
                    advlist_number_styles: 'default,lower-alpha,upper-alpha,lower-roman,upper-roman',
                    setup: function (editor) {
                        editor.on('change keyup', function () {
                            textarea.value = editor.getContent();
                            textarea.dispatchEvent(new Event('input', { bubbles: true }));
                        });
                    }
                });
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
            const assinanteDiv = select.closest('.assinante-item') || select.closest('.flex.items-center');

            if (selectedUnidade) {
                // Preenche o campo responsável
                const responsavelInput = assinanteDiv.querySelector('.responsavel-input');
                if (responsavelInput) {
                    responsavelInput.value = selectedUnidade.servidor_responsavel || '';
                }

                // Preenche o número da portaria (se existir o campo)
                const portariaInput = assinanteDiv.querySelector('.portaria-input');
                if (portariaInput) {
                    portariaInput.value = selectedUnidade.numero_portaria || '';
                }

                // Preenche a data da portaria (se existir o campo)
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

        // Função para gerar PDF sem assinatura (para documentos que não requerem assinatura)
        function gerarPdfSemAssinatura(processoId, documento, event) {
            const button = event.currentTarget;
            const originalText = button.textContent;

            button.textContent = 'Gerando...';
            button.disabled = true;

            let url = `/admin/finalizacao/processos/${processoId}/pdf?documento=${documento}`;

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
                    showMessage('Erro ao gerar PDF: ' + error, 'error');
                })
                .finally(() => {
                    button.textContent = originalText;
                    button.disabled = false;
                });
        }

        // Modificar a função gerarPdf existente para incluir validação de assinantes
        function gerarPdf(processoId, documento, data, event) {
            if (!data) {
                showMessage('Por favor, selecione uma data antes de gerar o PDF.', 'error');
                return;
            }

            const parecer = document.getElementById('parecer_select_' + documento)?.value || '';
            const assinantes = getAssinantes(documento);

            // Verificar se há pelo menos um assinante
            if (assinantes.length < 1) {
                showMessage('Você deve adicionar pelo menos um assinante antes de gerar o PDF.', 'error');
                return;
            }

            const assinantesJson = JSON.stringify(assinantes);
            const assinantesEncoded = encodeURIComponent(assinantesJson);

            let url = `/admin/finalizacao/processos/${processoId}/pdf?documento=${documento}&data=${data}`;

            if (parecer) {
                url += `&parecer=${parecer}`;
            }
            if (assinantes.length > 0) {
                url += `&assinantes=${assinantesEncoded}`;
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
                    showMessage('Erro ao gerar PDF: ' + error, 'error');
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

            setTimeout(() => {
                container.innerHTML = '';
            }, 6000);
        }

        // Alpine.js Component
        function formField(existing = {}) {
            return {
                // Campos do formulário
                anexo_atos_sessao: existing?.anexo_atos_sessao ?? '',
                anexo_proposta: existing?.anexo_proposta ?? '',
                anexo_proposta_readequada: existing?.anexo_proposta_readequada ?? '',
                anexo_habilitacao: existing?.anexo_habilitacao ?? '',
                anexo_recurso_contratacoes: existing?.anexo_recurso_contratacoes ?? '',
                anexo_publicacoes: existing?.anexo_publicacoes ?? '',
                orgao_responsavel: existing?.orgao_responsavel ?? '',
                cnpj: existing?.cnpj ?? '',
                endereco: existing?.endereco ?? '',
                responsavel: existing?.responsavel ?? '',
                cpf_responsavel: existing?.cpf_responsavel ?? '',
                razao_social: existing?.razao_social ?? '',
                cnpj_empresa_vencedora: existing?.cnpj_empresa_vencedora ?? '',
                endereco_empresa_vencedora: existing?.endereco_empresa_vencedora ?? '',
                representante_legal_empresa: existing?.representante_legal_empresa ?? '',
                cpf_representante: existing?.cpf_representante ?? '',
                valor_total: existing?.valor_total ?? '',
                numero_ata_registro_precos: existing?.numero_ata_registro_precos ?? '',
                cargo_controle_interno: existing?.cargo_controle_interno ?? '',
                cargo_responsavel: existing?.cargo_responsavel ?? '',
                merenda_escolar: existing?.merenda_escolar ?? '',
                veiculos: existing?.veiculos ?? '',

                // Controle de confirmação
                confirmed: {
                    anexo_atos_sessao: !!existing?.anexo_atos_sessao,
                    anexo_proposta: !!existing?.anexo_proposta,
                    anexo_proposta_readequada: !!existing?.anexo_proposta_readequada,
                    anexo_habilitacao: !!existing?.anexo_habilitacao,
                    anexo_recurso_contratacoes: !!existing?.anexo_recurso_contratacoes,
                    anexo_publicacoes: !!existing?.anexo_publicacoes,
                    orgao_responsavel: !!existing?.orgao_responsavel,
                    cnpj: !!existing?.cnpj,
                    endereco: !!existing?.endereco,
                    responsavel: !!existing?.responsavel,
                    cpf_responsavel: !!existing?.cpf_responsavel,
                    razao_social: !!existing?.razao_social,
                    cnpj_empresa_vencedora: !!existing?.cnpj_empresa_vencedora,
                    endereco_empresa_vencedora: !!existing?.endereco_empresa_vencedora,
                    representante_legal_empresa: !!existing?.representante_legal_empresa,
                    cpf_representante: !!existing?.cpf_representante,
                    valor_total: !!existing?.valor_total,
                    numero_ata_registro_precos: !!existing?.numero_ata_registro_precos,
                    cargo_controle_interno: !!existing?.cargo_controle_interno,
                    cargo_responsavel: !!existing?.cargo_responsavel,
                    merenda_escolar: !!existing?.merenda_escolar,
                    veiculos: !!existing?.veiculos,
                },

                toggleConfirm(field) {
                    if (!this.confirmed[field]) {
                        this.saveField(field);
                    } else {
                        this.confirmed[field] = false;
                    }
                },

                async saveField(field) {
                    console.log('Salvando campo:', field);

                    // Lista de campos permitidos - INCLUIR TODOS OS CAMPOS NECESSÁRIOS
                    const allowedFields = [
                        // Campos de arquivo
                        'anexo_atos_sessao',
                        'anexo_proposta',
                        'anexo_proposta_readequada',
                        'anexo_habilitacao',
                        'anexo_recurso_contratacoes',
                        'anexo_planilha',
                        'anexo_publicacoes',

                        // Campos de texto
                        'orgao_responsavel',
                        'cnpj',
                        'endereco',
                        'responsavel',
                        'cpf_responsavel',
                        'razao_social',
                        'cnpj_empresa_vencedora',
                        'endereco_empresa_vencedora',
                        'representante_legal_empresa',
                        'cpf_representante',
                        'valor_total',
                        'numero_ata_registro_precos',
                        'cargo_controle_interno',
                        'cargo_responsavel',
                        'merenda_escolar',
                        'veiculos',
                    ];

                    if (!allowedFields.includes(field)) {
                        console.error('Campo não permitido:', field);
                        showMessage('Campo não permitido: ' + field, 'error');
                        return;
                    }

                    const formData = new FormData();
                    formData.append('processo_id', {{ $processo->id }});
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                    // Verificar se é um campo de arquivo ou texto
                    if (this.isFileField(field)) {
                        const fileInput = document.getElementById(field);
                        if (fileInput && fileInput.files.length > 0) {
                            formData.append(field, fileInput.files[0]);
                            console.log('Arquivo selecionado:', fileInput.files[0].name);
                        } else {
                            formData.append(field, '');
                            console.log('Nenhum arquivo selecionado, limpando campo');
                        }
                    } else {
                        // Campo de texto - usar o valor do Alpine.js
                        formData.append(field, this[field]);
                        console.log('Valor do campo texto:', this[field]);
                    }

                    try {
                        const response = await fetch("{{ route('admin.processos.finalizacao.store', $processo) }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: formData
                        });

                        const responseData = await response.json();
                        console.log('Resposta do servidor:', responseData);

                        if (response.ok && responseData.success) {
                            this.confirmed[field] = true;

                            if (responseData.data && responseData.data[field]) {
                                this[field] = responseData.data[field];
                            }

                            if (this.isFileField(field)) {
                                const fileInput = document.getElementById(field);
                                if (fileInput && fileInput.files.length > 0) {
                                    showMessage('Arquivo ' + fileInput.files[0].name + ' salvo com sucesso!', 'success');
                                } else {
                                    showMessage('Campo ' + field + ' limpo com sucesso!', 'success');
                                }
                            } else {
                                showMessage('Campo ' + field + ' salvo com sucesso!', 'success');
                            }
                        } else {
                            this.confirmed[field] = false;
                            console.error('Erro ao salvar campo:', field, responseData);
                            const errorMessage = responseData.message || 'Erro ao salvar ' + field;
                            showMessage(errorMessage, 'error');
                        }
                    } catch (error) {
                        this.confirmed[field] = false;
                        console.error('Erro de rede ao salvar campo:', field, error);
                        showMessage('Erro de rede ao salvar ' + field, 'error');
                    }
                },

                isFileField(field) {
                    const fileFields = [
                        'anexo_atos_sessao',
                        'anexo_proposta',
                        'anexo_proposta_readequada',
                        'anexo_habilitacao',
                        'anexo_recurso_contratacoes',
                        'anexo_publicacoes',
                    ];
                    return fileFields.includes(field);
                },

                submitForm() {
                    this.$el.submit();
                }
            };
        }
    </script>
@endsection
