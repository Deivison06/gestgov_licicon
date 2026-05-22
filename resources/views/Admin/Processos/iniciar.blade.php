@extends('layouts.app')

@section('page-title', 'Iniciar processo ' . $processo->numero_processo)
@section('page-subtitle', 'Cadastrar/Editar detalhes do processo')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>

    @php
        // -----------------------------
        // View helpers / flags
        // -----------------------------
        $isConcorrencia = $processo->modalidade === \App\Enums\ModalidadeEnum::CONCORRENCIA;
        $isPregaoEletronico = $processo->modalidade === \App\Enums\ModalidadeEnum::PREGAO_ELETRONICO;

        $statusValue = $processo->status instanceof \App\Enums\ProcessoStatusEnum
            ? $processo->status->value
            : ($processo->status ?? null);
        $isCancelado = $statusValue === 'CANCELADO';

        $modalidadeBadgeClass = match ($processo->modalidade->value) {
            'dispensa' => 'bg-purple-100 text-purple-800',
            'inexigibilidade' => 'bg-pink-100 text-pink-800',
            'pregão' => 'bg-blue-100 text-blue-800',
            'concorrência' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800',
        };

        // JSON com as unidades para o JS
        $unidadesData = $processo->prefeitura->unidades->map(fn ($unidade) => [
            'id' => $unidade->id,
            'nome' => $unidade->nome,
            'servidor_responsavel' => $unidade->servidor_responsavel,
            'numero_portaria' => $unidade->numero_portaria,
            'data_portaria' => $unidade->data_portaria,
        ]);

        // Coleção de documentos do processo (para consultas no loop)
        $documentosProcesso = $processo->documentos;

        $getDocumentoGerado = function (string $tipo) use ($documentosProcesso, $documentos) {
            // Entradas dinâmicas de republicação carregam documento_id diretamente
            if (isset($documentos[$tipo]['documento_id'])) {
                return $documentosProcesso->firstWhere('id', $documentos[$tipo]['documento_id']);
            }

            return $documentosProcesso
                ->where('tipo_documento', $tipo)
                ->first();
        };

        // Documento de cancelamento (para mostrar o botão no topo quando CANCELADO)
        $cancelamentoDocumento = null;
        if (method_exists($processo, 'cancelamentos')) {
            $cancelamentoDocumento = $processo->cancelamentos()
                ->orderByDesc('gerado_em')
                ->first();
        }

        // Verificar se existe documento de cancelamento gerado
        $temCancelamentoGerado = $cancelamentoDocumento !== null;
    @endphp

    <script>
        const unidadesAssinantes = @json($unidadesData);
    </script>

    <div class="mb-8">
        <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-2xl">
            <!-- Header -->
            <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
                <div class="flex flex-col items-start justify-between gap-3 lg:flex-row lg:items-center">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-800">Processos Licitatórios</h3>
                        <!-- Nova linha para mostrar o responsável -->
                        @if($processo->user)
                            <div class="flex items-center gap-2 mt-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span class="text-sm text-gray-600">
                            Responsável pela elaboração: <strong>{{ $processo->user->name }}</strong>
                        </span>
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        {{-- BOTÃO DE CANCELAMENTO - AGORA SEMPRE VISÍVEL SE EXISTIR DOCUMENTO --}}
                        @if ($temCancelamentoGerado)
                            <a href="{{ route('admin.processo.documento.dowload', ['processo' => $processo->id, 'tipo' => 'minuta_cancelamento']) }}"
                               download
                               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                               title="Baixar Minuta de Cancelamento">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                <span>Baixar Minuta de Cancelamento</span>
                            </a>
                        @endif

                        <button type="button"
                                onclick="abrirModalRepublicarEdital({{ $processo->id }})"
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-purple-600 rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2"
                                title="Republicação de Edital">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <span>Republicação de Edital</span>
                        </button>

                        {{-- BOTÃO PARA GERAR CANCELAMENTO (se o processo não estiver cancelado e não tiver minuta gerada) --}}
                        @if (!$isCancelado && !$temCancelamentoGerado)
                            <button type="button"
                                    onclick="gerarMinutaCancelamento({{ $processo->id }})"
                                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-orange-600 rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2"
                                    title="Gerar Minuta de Cancelamento">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                                <span>Gerar Minuta de Cancelamento</span>
                            </button>
                        @endif
                    </div>
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
                                    <svg class="w-4 h-4 text-[#009496]" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24">
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
                        <td class="px-6 py-4">
                        <span class="inline-flex px-2.5 py-0.5 text-xs font-medium rounded-full {{ $modalidadeBadgeClass }}">
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

    <!-- Seção de Documentos -->
    <div class="mb-8">
        <div class="px-6 py-5">
            <div class="flex flex-col items-start justify-between lg:flex-row lg:items-center">
                <h3 class="text-xl font-semibold text-gray-800">Gerar Documentos</h3>
                <span class="mt-2 text-sm text-gray-500 lg:mt-0">
                    {{ $processo->modalidade->getDisplayName() }}
                </span>
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
                @php
                    $allDocDates = $processo->documentos->pluck('data_selecionada', 'tipo_documento')
                        ->mapWithKeys(fn($d, $t) => ["data_doc_$t" => $d])->toArray();
                    $initialData = array_merge($processo->detalhe?->toArray() ?? [], $allDocDates);
                @endphp
                <tbody class="bg-white divide-y divide-gray-200" x-data="formField({{ json_encode($initialData) }})">
                @foreach ($documentos as $tipo => $doc)
                    @php
                        $pularConcorrencia = $isConcorrencia && in_array($tipo, ['termo_referencia', 'analise_mercado'], true);
                        $pularPregao = $isPregaoEletronico && $tipo === 'projeto_basico';

                        // NÃO MOSTRAR minuta_cancelamento na tabela de documentos (sempre)
                        $pularMinutaCancelamento = $tipo === 'minuta_cancelamento';

                        // Detectar se é uma entrada dinâmica de republicação
                        $isRepublicacao = isset($doc['documento_id']);

                        // Define o ID do input de data
                        $dataId = $doc['data_id'] ?? 'data_' . $tipo;

                        $documentoGerado = $getDocumentoGerado($tipo);
                        $dataSelecionada = $documentoGerado->data_selecionada ?? '';
                        $accordionId = "accordion-collapse-{$tipo}";
                        $temCampos = !empty($doc['campos']);
                    @endphp

                    @continue($pularConcorrencia)
                    @continue($pularPregao)
                    @continue($pularMinutaCancelamento)

                    {{-- Linha principal do documento --}}
                    <tr class="transition-colors duration-150 hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 w-2 h-2 mr-3 rounded-full" style="background-color: {{ $doc['cor'] }};"></div>

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
                            @if ($temCampos)
                                <button type="button"
                                        class="mt-2 text-xs font-medium text-red-600 hover:text-red-800"
                                        data-collapse-toggle="{{ $accordionId }}"
                                        aria-expanded="false"
                                        aria-controls="{{ $accordionId }}">
                                    <span class="collapse-text">Definir Campos e Assinantes</span>
                                </button>
                            @endif
                        </td>

                        <td class="flex gap-2 px-6 py-4 text-center">
                            <input type="date"
                                   class="w-40 px-3 py-2 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                   id="{{ $dataId }}"
                                   x-model="data_doc_{{ $tipo }}"
                                   @blur="saveField('data_doc_{{ $tipo }}')"
                                   value="{{ $dataSelecionada }}">

                            @if ($tipo === 'parecer_juridico' && $isPregaoEletronico)
                                <!-- Dropdown de Parecer -->
                                <select id="parecer_select_{{ $tipo }}"
                                        name="parecer_select_{{ $tipo }}"
                                        class="block w-40 px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                    <option value="">Selecione o Parecer</option>
                                    <option value="parecer_1">Parecer 1</option>
                                    <option value="parecer_2">Parecer 2</option>
                                    <option value="parecer_3">Parecer 3</option>
                                </select>
                            @endif
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex justify-center space-x-2">
                                @php
                                    // Para republicações enviamos o $tipo dinâmico (ex.: republicacao_edital_5)
                                    // para que o JS leia assinantes/parecer DESSE bloco; e passamos documento_id
                                    // para o backend regenerar o PDF da republicação correta.
                                    $documentoIdParam = $isRepublicacao ? $doc['documento_id'] : 'null';
                                @endphp
                                <button type="button"
                                        onclick="gerarPdf('{{ $processo->id }}', '{{ $tipo }}', document.getElementById('{{ $dataId }}').value, event, {{ $documentoIdParam }})"
                                        class="px-4 py-2 text-xs font-medium text-white transition-colors duration-200 bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                    Gerar PDF
                                </button>

                                @if ($tipo === 'edital' && $documentoGerado)
                                    <button type="button"
                                            onclick="abrirModalRepublicarEdital({{ $processo->id }})"
                                            class="px-3 py-1.5 text-xs font-medium text-white transition-colors duration-200 bg-purple-600 rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-1"
                                            title="Republicar Edital">
                                        Republicar
                                    </button>
                                @endif

                                @if ($documentoGerado)
                                    @if ($isRepublicacao)
                                        <a href="{{ route('admin.processo.documento.dowload', ['processo' => $processo->id, 'tipo' => $doc['documento_id']]) }}"
                                           download
                                           class="p-2 text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                                           aria-label="Baixar documento">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                <polyline points="7 10 12 15 17 10"></polyline>
                                                <line x1="12" y1="15" x2="12" y2="3"></line>
                                            </svg>
                                        </a>
                                    @else
                                    <a href="{{ route('admin.processo.documento.dowload', ['processo' => $processo->id, 'tipo' => $tipo]) }}"
                                       download
                                       class="p-2 text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                                       aria-label="Baixar documento">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                            <polyline points="7 10 12 15 17 10"></polyline>
                                            <line x1="12" y1="15" x2="12" y2="3"></line>
                                        </svg>
                                    </a>
                                    @endif
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

                    {{-- Linha do Acordeão (Collapse) - Apenas se tiver campos --}}
                    @if ($temCampos)
                        <tr>
                            <td colspan="3" class="p-0">
                                <div id="{{ $accordionId }}" class="hidden">
                                    <div class="p-4 border-t border-gray-200 bg-gray-50" id="accordion-content-{{ $tipo }}">

                                        <!-- Seção de Assinantes -->
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

                                        <!-- Seção de Campos do Formulário -->
                                        <div>
                                            <h4 class="mb-3 text-sm font-semibold text-gray-700">Campos do Documento</h4>
                                            <form action="{{ route('admin.processos.detalhes.store', $processo) }}"
                                                  method="POST"
                                                  @submit.prevent="submitForm">
                                                @csrf
                                                <input type="hidden" name="processo_id" value="{{ $processo->id }}">

                                                @foreach ($doc['campos'] as $campo)
                                                    @include('Admin.Processos.partials.forms')
                                                @endforeach
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endif
                @endforeach
                </tbody>
            </table>


            <!-- Botão para Baixar Todos os PDFs -->
            <div class="flex flex-col items-center gap-3 p-4 mt-6 border-t border-gray-200 bg-gray-50">
                <button type="button"
                        id="btn-baixar-todos-iniciar"
                        onclick="iniciarDownloadTodos('{{ $processo->id }}', 'iniciar')"
                        class="inline-flex items-center gap-2 px-6 py-3 text-sm font-semibold text-white transition-colors duration-200 bg-green-600 rounded-lg shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    <svg id="icon-download-iniciar" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <svg id="spinner-iniciar" xmlns="http://www.w3.org/2000/svg" class="hidden w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <span id="label-baixar-todos-iniciar">📥 Baixar Todos os PDFs</span>
                </button>

                {{-- Área de progresso --}}
                <div id="progresso-iniciar" class="hidden w-full max-w-md">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-medium text-gray-600" id="msg-progresso-iniciar">Preparando arquivo...</span>
                    </div>
                    <div class="w-full h-2 overflow-hidden bg-gray-200 rounded-full">
                        <div class="h-2 bg-green-500 rounded-full animate-pulse" style="width: 100%"></div>
                    </div>
                    <p class="mt-2 text-xs text-center text-gray-500">
                        ⚠️ Arquivos com muitas páginas podem levar alguns minutos. Não feche esta aba.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Seção de Dados do Contrato (Apenas Inexigibilidade) -->
    @if($processo->modalidade === \App\Enums\ModalidadeEnum::INEXIGIBILIDADE)
        <div class="mb-8">
            <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-2xl">
                <!-- Header -->
                <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
                    <h3 class="text-lg font-semibold text-gray-800">Dados Gerais do Contrato</h3>
                    <p class="mt-1 text-sm text-gray-500">Informações exclusivas que serão salvas e usadas na geração do contrato de inexigibilidade.</p>
                </div>

                <!-- Formulário de Contrato -->
                <div class="p-6">
                    <form action="{{ route('admin.processos.detalhes.store', $processo) }}"
                          method="POST"
                          x-data="formField({{ json_encode(array_merge($processo->detalhe ? $processo->detalhe->toArray() : [], $contrato_iniciar ? $contrato_iniciar->toArray() : [])) }})"
                          @submit.prevent="submitForm">
                        @csrf
                        <input type="hidden" name="processo_id" value="{{ $processo->id }}">

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">

                            <!-- Número do Contrato -->
                            <div>
                                <label for="numero_contrato" class="flex items-center gap-2 mb-2 text-sm font-semibold text-gray-800">
                                    Número do Contrato
                                    <template x-if="confirmed.numero_contrato">
                                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    </template>
                                </label>
                                <div class="relative">
                                    <input type="text" id="numero_contrato" name="numero_contrato" x-model="numero_contrato" @blur="saveField('numero_contrato')"
                                           class="w-full px-4 py-3 text-sm transition-colors border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 hover:bg-white focus:bg-white"
                                           placeholder="Ex: 001/2026">
                                </div>
                            </div>

                            <!-- Data de Assinatura -->
                            <div>
                                <label for="data_assinatura_contrato" class="flex items-center gap-2 mb-2 text-sm font-semibold text-gray-800">
                                    Data de Assinatura
                                    <template x-if="confirmed.data_assinatura_contrato">
                                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    </template>
                                </label>
                                <div class="relative">
                                    <input type="date" id="data_assinatura_contrato" name="data_assinatura_contrato" x-model="data_assinatura_contrato" @blur="saveField('data_assinatura_contrato')"
                                           class="w-full px-4 py-3 text-sm transition-colors border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 hover:bg-white focus:bg-white">
                                </div>
                            </div>

                            <!-- Número do Extrato -->
                            <div>
                                <label for="numero_extrato" class="flex items-center gap-2 mb-2 text-sm font-semibold text-gray-800">
                                    Número do Extrato
                                    <template x-if="confirmed.numero_extrato">
                                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    </template>
                                </label>
                                <div class="relative">
                                    <input type="text" id="numero_extrato" name="numero_extrato" x-model="numero_extrato" @blur="saveField('numero_extrato')"
                                           class="w-full px-4 py-3 text-sm transition-colors border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 hover:bg-white focus:bg-white"
                                           placeholder="Ex: 12345/2026">
                                </div>
                            </div>

                            <!-- Comarca -->
                            <div>
                                <label for="comarca" class="flex items-center gap-2 mb-2 text-sm font-semibold text-gray-800">
                                    Comarca
                                    <template x-if="confirmed.comarca">
                                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    </template>
                                </label>
                                <div class="relative">
                                    <input type="text" id="comarca" name="comarca" x-model="comarca" @blur="saveField('comarca')"
                                           class="w-full px-4 py-3 text-sm transition-colors border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 hover:bg-white focus:bg-white"
                                           placeholder="Ex: Vara Cível">
                                </div>
                            </div>

                            <!-- Fonte de Recurso -->
                            <div>
                                <label for="fonte_recurso" class="flex items-center gap-2 mb-2 text-sm font-semibold text-gray-800">
                                    Fonte de Recurso
                                    <template x-if="confirmed.fonte_recurso">
                                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    </template>
                                </label>
                                <div class="relative">
                                    <input type="text" id="fonte_recurso" name="fonte_recurso" x-model="fonte_recurso" @blur="saveField('fonte_recurso')"
                                           class="w-full px-4 py-3 text-sm transition-colors border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 hover:bg-white focus:bg-white"
                                           placeholder="Ex: Recursos Próprios">
                                </div>
                            </div>

                            <!-- Subcontratação -->
                            <div>
                                <label for="subcontratacao" class="flex items-center gap-2 mb-2 text-sm font-semibold text-gray-800">
                                    Subcontratação
                                    <template x-if="confirmed.subcontratacao">
                                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    </template>
                                </label>
                                <div class="relative">
                                    <select id="subcontratacao" name="subcontratacao" x-model="subcontratacao" @blur="saveField('subcontratacao')"
                                            class="w-full px-4 py-3 text-sm transition-colors border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 hover:bg-white focus:bg-white">
                                        <option value="">Selecione...</option>
                                        <option value="sim">Sim</option>
                                        <option value="nao">Não</option>
                                    </select>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @include('Admin.Processos.modais.republicar_edital')

    {{-- Modal de Seleção de ETP Inteligente --}}
    <div id="modalSelecaoEtp" class="fixed inset-0 z-[100] flex items-center justify-center hidden bg-slate-900/60 backdrop-blur-sm px-4 py-6 sm:px-0">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl flex flex-col max-h-[85vh] overflow-hidden transform transition-all">
            <div class="px-6 py-4 border-b border-gray-100 bg-white flex justify-between items-center shrink-0">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-[#009496]/10 text-[#009496]">
                            <i class="fas fa-file-invoice"></i>
                        </span>
                        Selecionar ETP Inteligente
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">
                        Selecione um ETP aprovado para vincular a este processo.
                    </p>
                </div>
                <button type="button" onclick="fecharModal('modalSelecaoEtp')" class="text-gray-400 hover:text-gray-600 bg-gray-50 hover:bg-gray-100 rounded-full w-8 h-8 flex items-center justify-center transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="p-6 overflow-y-auto flex-1 bg-gray-50/50">
                <div id="lista-etps-disponiveis" class="grid gap-3">
                    {{-- Populado via JS --}}
                </div>

                <div id="alerta-vazio-etp" class="hidden mt-2 p-8 text-center bg-white rounded-2xl border-2 border-dashed border-gray-200">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-folder-open text-gray-400 text-2xl"></i>
                    </div>
                    <h4 class="text-gray-900 font-semibold mb-1">Nenhum ETP disponível</h4>
                    <p class="text-gray-500 text-sm">Não há ETPs aprovados e sem vínculo no momento para sua secretaria.</p>
                </div>
            </div>
            
            <div class="px-6 py-4 bg-white border-t border-gray-100 flex justify-end shrink-0">
                <button type="button" onclick="fecharModal('modalSelecaoEtp')"
                    class="px-5 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all shadow-sm focus:ring-2 focus:ring-offset-2 focus:ring-gray-200">
                    Cancelar
                </button>
            </div>
        </div>
    </div>

    <script>
        // Funções para gerenciar vínculo de ETP
        function abrirModalSelecaoEtp() {
            const modal = document.getElementById('modalSelecaoEtp');
            const lista = document.getElementById('lista-etps-disponiveis');
            const alerta = document.getElementById('alerta-vazio-etp');

            lista.innerHTML = '<div class="col-span-full py-12 text-center"><i class="fas fa-circle-notch fa-spin text-2xl text-[#009496] mb-3"></i><p class="text-gray-500 font-medium">Buscando ETPs disponíveis...</p></div>';
            alerta.classList.add('hidden');
            modal.classList.remove('hidden');

            fetch(`/admin/processos/{{ $processo->id }}/etps-disponiveis`)
                .then(res => res.json())
                .then(response => {
                    if (response.success) {
                        if (response.data.length === 0) {
                            lista.innerHTML = '';
                            alerta.classList.remove('hidden');
                            return;
                        }

                        lista.innerHTML = response.data.map(etp => `
                            <div class="group flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-white border border-gray-200 rounded-xl hover:border-[#009496]/50 hover:shadow-md transition-all gap-4">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1.5">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-[#009496]/10 text-[#009496] border border-[#009496]/20">
                                            ${etp.identificador}
                                        </span>
                                        <span class="text-xs font-medium text-gray-500 truncate flex items-center gap-1">
                                            <i class="fas fa-building text-gray-400"></i> ${etp.secretaria}
                                        </span>
                                    </div>
                                    <h4 class="text-sm font-semibold text-gray-800 line-clamp-2 leading-relaxed" title="${etp.objeto}">
                                        ${etp.objeto}
                                    </h4>
                                    <div class="flex items-center gap-3 mt-2.5">
                                        <div class="flex items-center text-xs font-medium text-gray-600 bg-gray-50 px-2 py-1 rounded-md border border-gray-100">
                                            <i class="fas fa-layer-group text-gray-400 mr-1.5"></i>
                                            ${etp.qtd_itens} ${etp.qtd_itens == 1 ? 'item' : 'itens'}/lotes
                                        </div>
                                    </div>
                                </div>
                                <div class="w-full sm:w-auto shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-gray-100 flex flex-col sm:flex-row gap-2">
                                    <a href="/admin/etps/${etp.id}" target="_blank"
                                       class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 text-sm font-semibold text-gray-600 bg-gray-100 border border-gray-200 rounded-lg hover:bg-gray-200 transition-colors shadow-sm">
                                        <i class="fas fa-eye mr-2"></i> Visualizar
                                    </a>
                                    <button type="button" onclick="vincularEtp(${etp.id})" 
                                            class="w-full sm:w-auto inline-flex justify-center items-center px-5 py-2 text-sm font-semibold text-[#009496] bg-[#009496]/10 border border-[#009496]/20 rounded-lg hover:bg-[#009496] hover:text-white transition-colors focus:ring-2 focus:ring-offset-2 focus:ring-[#009496]">
                                        <i class="fas fa-link mr-2"></i> Vincular
                                    </button>
                                </div>
                            </div>
                        `).join('');
                    } else {
                        showMessage(response.message || 'Erro ao carregar ETPs.', 'error');
                    }
                })
                .catch(() => showMessage('Erro de conexão ao carregar ETPs.', 'error'));
        }

        function vincularEtp(etpId) {
            if (!confirm('Deseja realmente vincular este ETP ao processo? Isso afetará os dados dinâmicos dos documentos.')) return;

            fetch(`/admin/processos/{{ $processo->id }}/vincular-etp`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ etp_id: etpId })
            })
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    fecharModal('modalSelecaoEtp');
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showMessage(response.message, 'error');
                }
            });
        }

        function desvincularEtp() {
            if (!confirm('Deseja realmente desvincular este ETP? O processo deixará de usar os itens dinâmicos do ETP.')) return;

            fetch(`/admin/processos/{{ $processo->id }}/desvincular-etp`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showMessage(response.message, 'error');
                }
            });
        }

        // Verificar se SweetAlert está disponível
        if (typeof Swal === 'undefined') {
            console.warn('SweetAlert2 não está carregado. Carregando agora...');
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
            script.onload = function() {
                console.log('SweetAlert2 carregado com sucesso');
            };
            document.head.appendChild(script);
        }

        function abrirModalRepublicarEdital(processoId) {
            const modal = document.getElementById('modalRepublicarEdital');
            const inputProcesso = document.getElementById('processo_id_republicar_edital');

            if (inputProcesso) {
                inputProcesso.value = processoId;
            }

            if (modal) {
                modal.classList.remove('hidden');
            }
        }

        function fecharModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) modal.classList.add('hidden');
        }

        document.addEventListener('DOMContentLoaded', () => {
            const formRepublicarEdital = document.getElementById('formRepublicarEdital');
            if (!formRepublicarEdital) return;

            formRepublicarEdital.addEventListener('submit', async function (e) {
                e.preventDefault();

                const processoId = document.getElementById('processo_id_republicar_edital').value;
                const formData = new FormData(this);

                try {
                    const response = await fetch(`/admin/processos/${processoId}/republicar-edital`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        fecharModal('modalRepublicarEdital');
                        window.location.reload();
                    } else {
                        alert(data.message || 'Erro ao republicar edital.');
                    }
                } catch (error) {
                    console.error(error);
                    alert('Ocorreu um erro ao republicar o edital. Tente novamente.');
                }
            });
        });
    </script>

    <script>
        // Função para gerar minuta de cancelamento
        function gerarMinutaCancelamento(processoId) {
            // Verificar se SweetAlert está disponível
            if (typeof Swal === 'undefined') {
                alert('Biblioteca SweetAlert não carregada. Por favor, recarregue a página ou aguarde alguns segundos.');
                return;
            }

            Swal.fire({
                title: 'Gerar Minuta de Cancelamento',
                html: `
                    <form id="formMinutaCancelamento">
                        <div class="space-y-4">
                            <div>
                                <label for="data_minuta_cancelamento" class="block text-sm font-medium text-gray-700">
                                    Data do Cancelamento
                                </label>
                                <input type="date" name="data_minuta_cancelamento" id="data_minuta_cancelamento"
                                       value="${new Date().toISOString().split('T')[0]}"
                                       class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"
                                       required>
                            </div>
                            <div>
                                <label for="motivo_cancelamento" class="block text-sm font-medium text-gray-700">
                                    Motivo do Cancelamento
                                </label>
                                <textarea name="motivo_cancelamento" id="motivo_cancelamento" rows="3"
                                          class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"
                                          placeholder="Informe o motivo do cancelamento..."
                                          required></textarea>
                            </div>
                        </div>
                    </form>
                `,
                showCancelButton: true,
                confirmButtonText: 'Gerar Minuta',
                cancelButtonText: 'Cancelar',
                preConfirm: () => {
                    const data = document.getElementById('data_minuta_cancelamento').value;
                    const motivo = document.getElementById('motivo_cancelamento').value;

                    if (!data || !motivo) {
                        Swal.showValidationMessage('Preencha todos os campos obrigatórios');
                        return false;
                    }

                    return { data, motivo };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('data_cancelamento', result.value.data);
                    formData.append('motivo_cancelamento', result.value.motivo);
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                    fetch(`/admin/processos/${processoId}/cancelar-licitacao`, {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Sucesso!',
                                    text: data.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                setTimeout(() => location.reload(), 2000);
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erro',
                                    text: data.message
                                });
                            }
                        })
                        .catch(() => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: 'Ocorreu um erro ao gerar a minuta de cancelamento.'
                            });
                        });
                }
            });
        }
    </script>

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
                    span.textContent = 'Definir Campos e Assinantes';
                } else {
                    targetEl.classList.remove('hidden');
                    button.setAttribute('aria-expanded', 'true');
                    span.textContent = 'Ocultar Campos e Assinantes';
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
            const assinanteDiv = select.closest('.assinante-item') || select.closest('.flex.items-center');

            if (selectedUnidade) {
                const responsavelInput = assinanteDiv.querySelector('.responsavel-input');
                if (responsavelInput) responsavelInput.value = selectedUnidade.servidor_responsavel || '';

                const portariaInput = assinanteDiv.querySelector('.portaria-input');
                if (portariaInput) portariaInput.value = selectedUnidade.numero_portaria || '';

                const dataPortariaInput = assinanteDiv.querySelector('.data-portaria-input');
                if (dataPortariaInput) dataPortariaInput.value = selectedUnidade.data_portaria || '';

                if (select.id === 'unidade_setor') {
                    const servidorResponsavelInput = document.getElementById('servidor_responsavel');
                    if (servidorResponsavelInput) servidorResponsavelInput.value = selectedUnidade.servidor_responsavel || '';
                }
            } else {
                const responsavelInput = assinanteDiv.querySelector('.responsavel-input');
                if (responsavelInput) responsavelInput.value = '';

                const portariaInput = assinanteDiv.querySelector('.portaria-input');
                if (portariaInput) portariaInput.value = '';

                const dataPortariaInput = assinanteDiv.querySelector('.data-portaria-input');
                if (dataPortariaInput) dataPortariaInput.value = '';
            }
        }

        function getAssinantes(tipoDocumento) {
            const container = document.getElementById(`assinantes-container-${tipoDocumento}`);
            const selects = container.querySelectorAll('select[name="assinante_unidade[]"]');
            const assinantes = [];

            selects.forEach((select) => {
                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption.value) {
                    const unidade = unidadesAssinantes.find(u => u.id == select.value);
                    if (unidade) {
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

        function gerarPdf(processoId, documento, data, event, documentoId = null) {
            // Encontrar o input de data correto
            let dataInput;
            let dataSelecionada;

            // Para republicação específica a chave dinâmica vem como `republicacao_edital_{id}`
            // — usamos a data passada por parâmetro (já lida do input daquela linha).
            if (documentoId) {
                dataSelecionada = data;
            } else if (documento === 'edital_republicado') {
                dataInput = document.getElementById('data_republicacao_edital');
            } else {
                // Tenta encontrar pelo ID padrão
                const idPadrao = 'data_' + documento;
                dataInput = document.getElementById(idPadrao);

                // Se não encontrar, tenta pelo valor passado
                if (!dataInput && data) {
                    dataSelecionada = data;
                }
            }

            dataSelecionada = dataSelecionada || (dataInput ? dataInput.value : data);

            if (!dataSelecionada) {
                showMessage('Por favor, selecione uma data antes de gerar o PDF.', 'error');
                return;
            }

            // Assinantes e parecer SEMPRE do bloco onde o botão foi clicado:
            // para republicação isso é o accordion da própria linha "EDITAL REPUBLICADO #N".
            const parecer = document.getElementById('parecer_select_' + documento)?.value || '';
            const assinantes = getAssinantes(documento);

            if (documento !== 'capa' && documento !== 'publicacoes_avisos_licitacao' && assinantes.length < 1) {
                showMessage('Você deve adicionar pelo menos um assinante antes de gerar o PDF.', 'error');
                return;
            }

            const documentosComDoisAssinantes = ['estudo_tecnico'];
            if (documentosComDoisAssinantes.includes(documento) && assinantes.length < 2) {
                showMessage('Este documento requer duas assinaturas obrigatórias.', 'error');
                return;
            }

            // Para chaves dinâmicas de republicação enviamos `edital_republicado` ao backend
            // (já reconhecido pelo PdfService) acompanhado do documento_id da republicação.
            const documentoBackend = documentoId ? 'edital_republicado' : documento;

            const assinantesEncoded = encodeURIComponent(JSON.stringify(assinantes));
            let url = `/admin/processos/${processoId}/pdf?documento=${documentoBackend}&data=${dataSelecionada}`;

            if (documentoId) url += `&documento_id=${documentoId}`;
            if (parecer) url += `&parecer=${parecer}`;
            if (assinantes.length > 0) url += `&assinantes=${assinantesEncoded}`;

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
                        setTimeout(() => window.location.reload(), 2000);
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
    </script>

    <script>
        document.addEventListener('alpine:init', () => {
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

        function formField(existing = {}) {
            const parseDateTime = (dateTimeString, fieldName) => {
                if (!dateTimeString) return { date: '', time: '' };

                try {
                    if (dateTimeString.includes(' ')) {
                        const [datePart, timePart] = dateTimeString.split(' ');
                        const [year, month, day] = datePart.split('-');
                        const [hours, minutes] = timePart.split(':');

                        return { date: `${year}-${month}-${day}`, time: `${hours}:${minutes}` };
                    }

                    if (dateTimeString.includes('T')) {
                        const [datePart, timePart] = dateTimeString.split('T');
                        const [year, month, day] = datePart.split('-');
                        const [hours, minutes] = timePart.split(':');

                        return { date: `${year}-${month}-${day}`, time: `${hours}:${minutes}` };
                    }

                    return { date: '', time: '' };
                } catch (e) {
                    console.error('Erro ao parse data/hora para ' + fieldName + ':', e);
                    return { date: '', time: '' };
                }
            };

            const datetimeFields = ['data_hora', 'data_hora_limite_edital', 'data_hora_fase_edital', 'prazo_inicio_prestacao_servico', 'prazo_final_prestacao_servico'];
            const initialData = {};

            datetimeFields.forEach(field => {
                const parsed = parseDateTime(existing?.[field], field);
                initialData[field + '_date'] = parsed.date;
                initialData[field + '_time'] = parsed.time;
            });

            // Incluir todos os campos de data de documento dinamicamente
            Object.keys(existing || {}).forEach(key => {
                if (key.startsWith('data_doc_')) {
                    initialData[key] = existing[key];
                }
            });

            return {
                ...initialData,
                secretaria: existing?.secretaria ?? '',
                unidade_setor: existing?.unidade_setor ?? '',
                servidor_responsavel: existing?.servidor_responsavel ?? '',
                justificativa: existing?.justificativa ?? '',
                prazo_entrega: existing?.prazo_entrega ?? '',
                local_entrega: existing?.local_entrega ?? '',
                tipo_procedimento: existing?.tipo_procedimento ?? '',
                tipo_contratacao: existing?.tipo_contratacao ?? '',
                contratacoes_anteriores: existing?.contratacoes_anteriores ?? '',
                fiscais: existing?.fiscais ?? '',
                gestor: existing?.gestor ?? '',
                instrumento_vinculativo: existing?.instrumento_vinculativo ?? [],
                instrumento_vinculativo_outro: existing?.instrumento_vinculativo_outro ?? '',
                prazo_vigencia: existing?.prazo_vigencia ?? [],
                prazo_vigencia_outro: existing?.prazo_vigencia_outro ?? '',
                objeto_continuado: existing?.objeto_continuado ?? '',
                itens_e_seus_quantitativos_xml: existing?.itens_e_seus_quantitativos_xml ?? '',
                descricao_e_quantitativos_itens_xml: existing?.descricao_e_quantitativos_itens_xml ?? '',
                nome_equipe_planejamento: existing?.nome_equipe_planejamento ?? '',
                responsavel_equipe_planejamento: existing?.responsavel_equipe_planejamento ?? '',
                descricao_necessidade_autorizacao: existing?.descricao_necessidade_autorizacao ?? '',
                solucoes_disponivel_mercado: existing?.solucoes_disponivel_mercado ?? '',
                incluir_requisito_cada_caso_concreto: existing?.incluir_requisito_cada_caso_concreto ?? '',
                descricao_necessidade: existing?.descricao_necessidade ?? '',
                problema_resolvido: existing?.problema_resolvido ?? '',
                inversao_fase: existing?.inversao_fase ?? '',
                exige_atestado: existing?.exige_atestado ?? '',
                solucao_escolhida: existing?.solucao_escolhida ?? '',
                justificativa_solucao_escolhida: existing?.justificativa_solucao_escolhida ?? '',
                impacto_ambiental: existing?.impacto_ambiental ?? '',
                resultado_pretendidos: existing?.resultado_pretendidos ?? '',
                tipo_srp: existing?.tipo_srp ?? '',
                encaminhamento_pesquisa_preco: existing?.encaminhamento_pesquisa_preco ?? '',
                encaminhamento_doacao_orcamentaria: existing?.encaminhamento_doacao_orcamentaria ?? '',
                prevista_plano_anual: existing?.prevista_plano_anual ?? '',
                painel_preco_tce: existing?.painel_preco_tce ?? '',
                anexo_pdf_analise_mercado: existing?.anexo_pdf_analise_mercado ?? '',
                encaminhamento_elaborar_editais: existing?.encaminhamento_elaborar_editais ?? '',
                encaminhamento_elaborar_projeto_basico: existing?.encaminhamento_elaborar_projeto_basico ?? '',
                encaminhamento_parecer_juridico: existing?.encaminhamento_parecer_juridico ?? '',
                encaminhamento_autorizacao_abertura: existing?.encaminhamento_autorizacao_abertura ?? '',
                encaminhamento_elaborar_termo_referencia: existing?.encaminhamento_elaborar_termo_referencia ?? '',
                encaminhamento_controle_interno: existing?.encaminhamento_controle_interno ?? '',
                valor_estimado: existing?.valor_estimado ?? '',
                dotacao_orcamentaria: existing?.dotacao_orcamentaria ?? '',
                tratamento_diferenciado_MEs_eEPPs: existing?.tratamento_diferenciado_MEs_eEPPs ?? '',
                anexar_minuta: existing?.anexar_minuta ?? '',
                anexo_pdf_publicacoes: existing?.anexo_pdf_publicacoes ?? '',
                riscos_extra: existing?.riscos_extra ?? '',
                anexo_pdf_minuta_contrato: existing?.anexo_pdf_minuta_contrato ?? '',
                itens_especificaca_quantitativos_xml: existing?.itens_especificaca_quantitativos_xml ?? '',
                intervalo_lances: existing?.intervalo_lances ?? '',
                portal: existing?.portal ?? '',
                exigencia_garantia_proposta: existing?.exigencia_garantia_proposta ?? '',
                exigencia_garantia_contrato: existing?.exigencia_garantia_contrato ?? '',
                participacao_exclusiva_mei_epp: existing?.participacao_exclusiva_mei_epp ?? '',
                reserva_cotas_mei_epp: existing?.reserva_cotas_mei_epp ?? '',
                prioridade_contratacao_mei_epp: existing?.prioridade_contratacao_mei_epp ?? '',
                exigencias_tecnicas: existing?.exigencias_tecnicas ?? '',
                qualificacao_economica: existing?.qualificacao_economica ?? '',
                regularidade_fisica: existing?.regularidade_fisica ?? '',
                pregoeiro: existing?.pregoeiro ?? '',
                numero_items: existing?.numero_items ?? '',
                projeto_basico_pdf: existing?.projeto_basico_pdf ?? '',
                agente_contratacao: existing?.agente_contratacao ?? '',
                info_extras: existing?.info_extras ?? '',
                especificacao_servicos_imovel: existing?.especificacao_servicos_imovel ?? '',
                razao_escolha_contratado: existing?.razao_escolha_contratado ?? '',
                obrigacoes_contratado_extras: existing?.obrigacoes_contratado_extras ?? '',
                obrigacoes_contratante_extras: existing?.obrigacoes_contratante_extras ?? '',
                razao_social: existing?.razao_social ?? '',
                cnpj_empresa_vencedora: existing?.cnpj_empresa_vencedora ?? '',
                endereco_empresa_vencedora: existing?.endereco_empresa_vencedora ?? '',
                representante_legal_empresa: existing?.representante_legal_empresa ?? '',
                cpf_representante: existing?.cpf_representante ?? '',
                valor_total: existing?.valor_total ?? '',
                empresa_vencedora_pdf: existing?.empresa_vencedora_pdf ?? '',


                ...initialData,
                data_hora: existing?.data_hora ?? '',
                data_hora_limite_edital: existing?.data_hora_limite_edital ?? '',
                data_hora_fase_edital: existing?.data_hora_fase_edital ?? '',

                // Campos de Contrato
                numero_contrato: existing?.numero_contrato ?? '',
                data_assinatura_contrato: existing?.data_assinatura_contrato ?? '',
                numero_extrato: existing?.numero_extrato ?? '',
                comarca: existing?.comarca ?? '',
                fonte_recurso: existing?.fonte_recurso ?? '',
                subcontratacao: existing?.subcontratacao ?? '',

                // Campos de Imóvel
                orgao_responsavel: existing?.orgao_responsavel ?? '',
                cnpj: existing?.cnpj ?? '',
                endereco: existing?.endereco ?? '',
                responsavel: existing?.responsavel ?? '',
                cpf_responsavel: existing?.cpf_responsavel ?? '',
                endereco_imovel: existing?.endereco_imovel ?? '',
                prazo_inicio_prestacao_servico: existing?.prazo_inicio_prestacao_servico ?? '',
                prazo_final_prestacao_servico: existing?.prazo_final_prestacao_servico ?? '',
                valor_mensal: existing?.valor_mensal ?? '',

                confirmed: {
                    secretaria: !!existing?.secretaria,
                    unidade_setor: !!existing?.unidade_setor,
                    servidor_responsavel: !!existing?.servidor_responsavel,
                    justificativa: !!existing?.justificativa,
                    prazo_entrega: !!existing?.prazo_entrega,
                    local_entrega: !!existing?.local_entrega,
                    tipo_procedimento: !!existing?.tipo_procedimento,
                    tipo_contratacao: !!existing?.tipo_contratacao,
                    contratacoes_anteriores: !!existing?.contratacoes_anteriores,
                    fiscais: !!existing?.fiscais,
                    gestor: !!existing?.gestor,
                    instrumento_vinculativo: existing?.instrumento_vinculativo?.length > 0,
                    prazo_vigencia: existing?.prazo_vigencia?.length > 0,
                    objeto_continuado: !!existing?.objeto_continuado,
                    itens_e_seus_quantitativos_xml: !!existing?.itens_e_seus_quantitativos_xml,
                    descricao_e_quantitativos_itens_xml: !!existing?.descricao_e_quantitativos_itens_xml,
                    nome_equipe_planejamento: !!existing?.nome_equipe_planejamento,
                    responsavel_equipe_planejamento: !!existing?.responsavel_equipe_planejamento,
                    descricao_necessidade_autorizacao: !!existing?.descricao_necessidade_autorizacao,
                    solucoes_disponivel_mercado: !!existing?.solucoes_disponivel_mercado,
                    incluir_requisito_cada_caso_concreto: !!existing?.incluir_requisito_cada_caso_concreto,
                    descricao_necessidade: !!existing?.descricao_necessidade,
                    problema_resolvido: !!existing?.problema_resolvido,
                    inversao_fase: !!existing?.inversao_fase,
                    exige_atestado: !!existing?.exige_atestado,
                    solucao_escolhida: !!existing?.solucao_escolhida,
                    justificativa_solucao_escolhida: !!existing?.justificativa_solucao_escolhida,
                    impacto_ambiental: !!existing?.impacto_ambiental,
                    resultado_pretendidos: !!existing?.resultado_pretendidos,
                    tipo_srp: !!existing?.tipo_srp,
                    encaminhamento_pesquisa_preco: !!existing?.encaminhamento_pesquisa_preco,
                    encaminhamento_doacao_orcamentaria: !!existing?.encaminhamento_doacao_orcamentaria,
                    prevista_plano_anual: !!existing?.prevista_plano_anual,
                    painel_preco_tce: !!existing?.painel_preco_tce,
                    anexo_pdf_analise_mercado: !!existing?.anexo_pdf_analise_mercado,
                    encaminhamento_elaborar_editais: !!existing?.encaminhamento_elaborar_editais,
                    encaminhamento_elaborar_projeto_basico: !!existing?.encaminhamento_elaborar_projeto_basico,
                    encaminhamento_parecer_juridico: !!existing?.encaminhamento_parecer_juridico,
                    encaminhamento_autorizacao_abertura: !!existing?.encaminhamento_autorizacao_abertura,
                    encaminhamento_elaborar_termo_referencia: !!existing?.encaminhamento_elaborar_termo_referencia,
                    encaminhamento_controle_interno: !!existing?.encaminhamento_controle_interno,
                    valor_estimado: !!existing?.valor_estimado,
                    dotacao_orcamentaria: !!existing?.dotacao_orcamentaria,
                    tratamento_diferenciado_MEs_eEPPs: !!existing?.tratamento_diferenciado_MEs_eEPPs,
                    anexar_minuta: !!existing?.anexar_minuta,
                    anexo_pdf_publicacoes: !!existing?.anexo_pdf_publicacoes,
                    riscos_extra: !!existing?.riscos_extra,
                    data_hora: !!existing?.data_hora,
                    itens_especificaca_quantitativos_xml: !!existing?.itens_especificaca_quantitativos_xml,
                    intervalo_lances: !!existing?.intervalo_lances,
                    portal: !!existing?.portal,
                    exigencia_garantia_proposta: !!existing?.exigencia_garantia_proposta,
                    exigencia_garantia_contrato: !!existing?.exigencia_garantia_contrato,
                    participacao_exclusiva_mei_epp: !!existing?.participacao_exclusiva_mei_epp,
                    reserva_cotas_mei_epp: !!existing?.reserva_cotas_mei_epp,
                    prioridade_contratacao_mei_epp: !!existing?.prioridade_contratacao_mei_epp,
                    exigencias_tecnicas: !!existing?.exigencias_tecnicas,
                    qualificacao_economica: !!existing?.qualificacao_economica,
                    regularidade_fisica: !!existing?.regularidade_fisica,
                    anexo_pdf_minuta_contrato: !!existing?.anexo_pdf_minuta_contrato,
                    pregoeiro: !!existing?.pregoeiro,
                    data_hora_limite_edital: !!existing?.data_hora_limite_edital,
                    data_hora_fase_edital: !!existing?.data_hora_fase_edital,
                    numero_items: !!existing?.numero_items,
                    projeto_basico_pdf: !!existing?.projeto_basico_pdf,
                    agente_contratacao: !!existing?.agente_contratacao,
                    info_extras: !!existing?.info_extras,
                    especificacao_servicos_imovel: !!existing?.especificacao_servicos_imovel,
                    razao_escolha_contratado: !!existing?.razao_escolha_contratado,
                    obrigacoes_contratado_extras: !!existing?.obrigacoes_contratado_extras,
                    obrigacoes_contratante_extras: !!existing?.obrigacoes_contratante_extras,
                    razao_social: !!existing?.razao_social,
                    cnpj_empresa_vencedora: !!existing?.cnpj_empresa_vencedora,
                    endereco_empresa_vencedora: !!existing?.endereco_empresa_vencedora,
                    representante_legal_empresa: !!existing?.representante_legal_empresa,
                    cpf_representante: !!existing?.cpf_representante,
                    valor_total: !!existing?.valor_total,
                    empresa_vencedora_pdf: !!existing?.empresa_vencedora_pdf,

                    // Contrato confirmed state
                    numero_contrato: !!existing?.numero_contrato,
                    data_assinatura_contrato: !!existing?.data_assinatura_contrato,
                    numero_extrato: !!existing?.numero_extrato,
                    comarca: !!existing?.comarca,
                    fonte_recurso: !!existing?.fonte_recurso,
                    subcontratacao: !!existing?.subcontratacao,

                    // Campos de Imóvel confirmed state
                    orgao_responsavel: !!existing?.orgao_responsavel,
                    cnpj: !!existing?.cnpj,
                    endereco: !!existing?.endereco,
                    responsavel: !!existing?.responsavel,
                    cpf_responsavel: !!existing?.cpf_responsavel,
                    endereco_imovel: !!existing?.endereco_imovel,
                    prazo_inicio_prestacao_servico: !!existing?.prazo_inicio_prestacao_servico,
                    prazo_final_prestacao_servico: !!existing?.prazo_final_prestacao_servico,
                    valor_mensal: !!existing?.valor_mensal,
                },

                init() {
                    datetimeFields.forEach(field => {
                        this.$watch(field + '_date', () => this.updateDateTimeField(field));
                        this.$watch(field + '_time', () => this.updateDateTimeField(field));
                    });
                },

                updateDateTimeField(field) {
                    const date = this[field + '_date'];
                    const time = this[field + '_time'];

                    if (date && time) {
                        this[field] = `${date} ${time}:00`;
                    } else {
                        this[field] = '';
                    }
                },

                onUnidadeChange() {
                    const selectElement = document.getElementById('unidade_setor');
                    const selectedOption = selectElement.options[selectElement.selectedIndex];

                    if (selectedOption && selectedOption.value) {
                        this.servidor_responsavel = selectedOption.getAttribute('data-responsavel') || '';
                    } else {
                        this.servidor_responsavel = '';
                    }
                },

                toggleConfirm(field) {
                    if (!this.confirmed[field]) {
                        this.saveField(field);
                    } else {
                        this.confirmed[field] = false;
                    }
                },

                async saveField(field) {
                    const formData = new FormData();
                    formData.append('processo_id', {{ $processo->id }});
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                    if (field === 'unidade_setor' && this.servidor_responsavel) {
                        formData.append('servidor_responsavel', this.servidor_responsavel);
                    }

                    const tinyMceFields = [
                        'justificativa', 'descricao_necessidade', 'descricao_necessidade_autorizacao',
                        'solucoes_disponivel_mercado', 'incluir_requisito_cada_caso_concreto',
                        'justificativa_solucao_escolhida', 'impacto_ambiental', 'resultado_pretendidos',
                        'dotacao_orcamentaria', 'tratamento_diferenciado_MEs_eEPPs', 'riscos_extra',
                        'exigencias_tecnicas', 'qualificacao_economica', 'regularidade_fisica', 'info_extras',
                        'especificacao_servicos_imovel', 'razao_escolha_contratado', 'obrigacoes_contratado_extras',
                        'obrigacoes_contratante_extras',
                    ];

                    if (tinyMceFields.includes(field)) {
                        const editor = tinymce.get(field);
                        formData.append(field, editor ? editor.getContent() : this[field]);
                    } else if (this.isFileField(field)) {
                        const fileInput = document.getElementById(field);
                        formData.append(field, fileInput && fileInput.files.length > 0 ? fileInput.files[0] : '');
                    } else if (Array.isArray(this[field])) {
                        if (this[field].length === 0) {
                            formData.append(field, '');
                        } else {
                            this[field].forEach(v => formData.append(field + '[]', v));
                        }

                        if (field === 'instrumento_vinculativo' && this.instrumento_vinculativo_outro) {
                            formData.append('instrumento_vinculativo_outro', this.instrumento_vinculativo_outro);
                        }
                        if (field === 'prazo_vigencia' && this.prazo_vigencia_outro) {
                            formData.append('prazo_vigencia_outro', this.prazo_vigencia_outro);
                        }
                    } else {
                        formData.append(field, this[field]);
                    }

                    try {
                        const response = await fetch("{{ route('admin.processos.detalhes.store', $processo) }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: formData
                        });

                        const responseData = await response.json();

                        if (response.ok) {
                            this.confirmed[field] = true;

                            if (this.isFileField(field)) {
                                const fileInput = document.getElementById(field);
                                if (fileInput && fileInput.files.length > 0) {
                                    showMessage('Arquivo ' + fileInput.files[0].name + ' salvo com sucesso!', 'success');
                                }
                            } else {
                                showMessage('Campo ' + field + ' salvo com sucesso!', 'success');
                            }
                        } else {
                            this.confirmed[field] = false;
                            showMessage('Erro ao salvar ' + field, 'error');
                            console.error('Erro ao salvar campo:', field, responseData);
                        }
                    } catch (error) {
                        this.confirmed[field] = false;
                        showMessage('Erro de rede ao salvar ' + field, 'error');
                        console.error('Erro de rede ao salvar campo:', field, error);
                    }
                },

                isFileField(field) {
                    return [
                        'itens_e_seus_quantitativos_xml',
                        'descricao_e_quantitativos_itens_xml',
                        'itens_especificaca_quantitativos_xml',
                        'painel_preco_tce',
                        'anexo_pdf_analise_mercado',
                        'anexar_minuta',
                        'anexo_pdf_publicacoes',
                        'anexo_pdf_minuta_contrato',
                        'projeto_basico_pdf',
                        'empresa_vencedora_pdf',
                    ].includes(field);
                },

                submitForm() {
                    this.$el.submit();
                }
            };
        }
    </script>

    <script>
        // Gestão do Loader Full Screen - Premium Redesign
        function getOverlay() {
            let overlay = document.getElementById('fs-download-overlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.id = 'fs-download-overlay';
                overlay.className = 'fixed inset-0 z-[9999] flex flex-col items-center justify-center bg-slate-900/60 backdrop-blur-sm hidden transition-all duration-500 opacity-0';
                overlay.innerHTML = `
                    <!-- Glass modal -->
                    <div class="relative flex flex-col items-center w-full max-w-md p-10 mx-4 overflow-hidden bg-white/95 shadow-2xl backdrop-blur-xl rounded-[2rem] border border-white/60 transform transition-all duration-500 translate-y-12 opacity-0" id="fs-download-modal">
                        
                        <!-- Decorative Glow -->
                        <div class="absolute top-0 w-full h-32 opacity-60 bg-gradient-to-b from-blue-50 to-transparent"></div>
                        
                        <!-- Icon Container -->
                        <div class="relative flex items-center justify-center w-28 h-28 mb-6">
                            <!-- Pulsing background rings -->
                            <div id="fs-ring-bounce" class="absolute inset-0 border-4 border-blue-100 rounded-full animate-ping opacity-60"></div>
                            <div id="fs-ring-static" class="absolute inset-0 bg-gradient-to-tr from-blue-50 to-emerald-50 rounded-full animate-pulse shadow-inner"></div>
                            
                            <!-- Rotating dashed ring -->
                            <svg id="fs-spinner-ring" class="absolute inset-0 w-full h-full text-blue-500 animate-[spin_4s_linear_infinite]" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="46" fill="transparent" stroke="currentColor" stroke-width="2.5" stroke-dasharray="25 15" stroke-linecap="round"></circle>
                            </svg>

                            <!-- Center Icon (Document) -->
                            <div class="relative z-10 text-blue-600 transition-colors duration-300" id="fs-center-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"></path>
                                </svg>
                            </div>
                        </div>

                        <h3 class="relative z-10 mb-2 text-2xl font-extrabold tracking-tight text-slate-800 text-center" id="fs-loader-title">Preparando Documentos</h3>
                        
                        <p class="relative z-10 w-full mb-6 text-[15px] font-medium text-center text-slate-500 min-h-[44px] flex items-center justify-center leading-tight" id="fs-loader-msg">Conectando à fila do servidor...</p>
                        
                        <!-- Progress Bar -->
                        <div class="relative z-10 w-full h-2.5 mb-4 overflow-hidden bg-slate-100 rounded-full shadow-inner" id="progress-container">
                            <div id="fs-progress-bar" class="h-full rounded-full bg-gradient-to-r from-blue-500 via-indigo-400 to-teal-400" style="width: 100%; background-size: 200% 100%; animation: fs-gradient-x 2s linear infinite;"></div>
                        </div>
                        
                        <!-- Info Alert -->
                        <div class="relative z-10 flex items-start p-4 mt-2 mb-2 rounded-xl bg-amber-50/80 border border-amber-200/60 text-amber-800 shadow-sm transition-opacity duration-300" id="fs-alert-box">
                            <svg class="w-5 h-5 mr-3 flex-shrink-0 text-amber-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <p class="text-[13px] leading-relaxed font-medium">Por favor, mantenha esta aba aberta. O processamento de processos complexos pode demorar <strong>alguns minutos</strong>.</p>
                        </div>
                        
                        <!-- Action Button -->
                        <button id="fs-loader-close" class="relative z-10 hidden w-full px-6 py-3.5 mt-4 text-[15px] font-bold text-white transition-all transform shadow-md shadow-slate-200 bg-slate-800 rounded-xl hover:bg-slate-700 hover:-translate-y-0.5 active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2" onclick="fecharOverlay()">
                            Voltar e Tentar Novamente
                        </button>
                    </div>
                `;
                
                // Adiciona os keyframes de gradiente customizados
                const style = document.createElement('style');
                style.textContent = `
                    @keyframes fs-gradient-x {
                        0%, 100% { background-position: 0% 50%; }
                        50% { background-position: 100% 50%; }
                    }
                `;
                document.head.appendChild(style);
                document.body.appendChild(overlay);
            }
            return overlay;
        }

        function mostrarOverlay() {
            const overlay = getOverlay();
            const modal = document.getElementById('fs-download-modal');
            const closeBtn = document.getElementById('fs-loader-close');
            
            // Start state
            closeBtn.classList.add('hidden');
            document.getElementById('fs-alert-box').classList.remove('hidden', 'opacity-0');
            document.getElementById('progress-container').classList.remove('hidden');
            
            document.getElementById('fs-loader-title').textContent = 'Processando em Lote';
            document.getElementById('fs-loader-title').className = 'relative z-10 mb-2 text-2xl font-extrabold tracking-tight text-slate-800 text-center';
            
            document.getElementById('fs-center-icon').className = 'relative z-10 text-blue-600 transition-colors duration-300';
            document.getElementById('fs-center-icon').innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"></path>
                </svg>
            `;
            
            document.getElementById('fs-spinner-ring').classList.remove('hidden');
            document.getElementById('fs-ring-bounce').classList.remove('hidden');
            
            // Show overlay
            overlay.classList.remove('hidden');
            
            // Trigger animation
            requestAnimationFrame(() => {
                overlay.classList.remove('opacity-0');
                overlay.classList.add('opacity-100');
                
                modal.classList.remove('translate-y-12', 'opacity-0');
                modal.classList.add('translate-y-0', 'opacity-100');
            });
        }

        function fecharOverlay() {
            const overlay = document.getElementById('fs-download-overlay');
            const modal = document.getElementById('fs-download-modal');
            if (overlay) {
                overlay.classList.remove('opacity-100');
                overlay.classList.add('opacity-0');
                
                modal.classList.remove('translate-y-0', 'opacity-100');
                modal.classList.add('translate-y-12', 'opacity-0');
                
                setTimeout(() => {
                    overlay.classList.add('hidden');
                }, 500); // Matches duration-500
            }
        }

        function atualizarOverlayText(msg) {
            const msgEl = document.getElementById('fs-loader-msg');
            if (msgEl) msgEl.innerHTML = msg;
        }

        function erroOverlay(msg) {
            document.getElementById('fs-loader-title').textContent = 'Ocorreu um Erro';
            document.getElementById('fs-loader-title').className = 'relative z-10 mb-2 text-2xl font-extrabold tracking-tight text-rose-600 text-center';
            document.getElementById('fs-loader-msg').innerHTML = `<span class="text-rose-600">${msg}</span>`;
            
            // Hide progress
            document.getElementById('progress-container').classList.add('hidden');
            document.getElementById('fs-alert-box').classList.add('hidden');
            
            // Update Icon
            document.getElementById('fs-spinner-ring').classList.add('hidden');
            document.getElementById('fs-ring-bounce').classList.add('hidden');
            document.getElementById('fs-center-icon').className = 'relative z-10 text-rose-500';
            document.getElementById('fs-center-icon').innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="w-14 h-14 drop-shadow-md" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            `;
            
            document.getElementById('fs-loader-close').classList.remove('hidden');
        }

        /**
         * Inicia o download de todos os PDFs com processamento assíncrono (Job Queue).
         * @param {string|number} processoId
         * @param {string} fase  'iniciar' | 'finalizar'
         */
        function iniciarDownloadTodos(processoId, fase) {
            mostrarOverlay();
            atualizarOverlayText('Enviando solicitação para a fila...');

            // URL para colocar na fila
            const urlMap = {
                iniciar:   `/admin/processos/${processoId}/documentos/baixar-todos`,
                finalizar: `/admin/processos/${processoId}/finalizacao/documentos/baixar-todos`,
            };
            const url = urlMap[fase];

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.token) {
                        monitorarStatusDownload(data.token, fase);
                    } else {
                        throw new Error(data.message || 'Erro desconhecido ao iniciar processo.');
                    }
                })
                .catch(err => {
                    erroOverlay(err.message);
                });
        }

        function monitorarStatusDownload(token, fase) {
            let tempoDecorrido = 0;
            atualizarOverlayText('Aguardando servidor mesclar os arquivos...');

            const intervalo = setInterval(() => {
                fetch(`/admin/documentos-async/status/${token}`)
                    .then(res => res.json())
                    .then(data => {
                        tempoDecorrido += 5;

                        if (data.status === 'pronto') {
                            clearInterval(intervalo);
                            atualizarOverlayText('Pronto! Iniciando download...');
                            
                            setTimeout(() => {
                                fecharOverlay();
                                window.location.href = `/admin/documentos-async/download/${token}`;
                            }, 1500);

                        } else if (data.status === 'erro') {
                            clearInterval(intervalo);
                            erroOverlay(data.message || 'Erro no processamento em lote.');
                        } else {
                            const minutos = Math.floor(tempoDecorrido / 60);
                            const segundos = tempoDecorrido % 60;
                            const tempoFmt = minutos > 0 ? `${minutos}m ${segundos}s` : `${segundos}s`;
                            
                            const pontos = '.'.repeat((tempoDecorrido / 5) % 4);
                            atualizarOverlayText(`Gerando PDF em Background${pontos} \n(${tempoFmt} decorridos)`);
                        }
                    })
                    .catch(err => {
                        console.warn('Erro momentâneo ao checar status. Tentando...', err);
                    });
            }, 5000); // Poll a cada 5 segundos
        }
    </script>
@endsection
