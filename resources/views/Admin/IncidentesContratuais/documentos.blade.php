@extends('layouts.app')

@section('page-title', 'Documentos do Aditivo')
@section('page-subtitle', 'Gerenciar e gerar documentos para o Incidente Contratual')

@section('content')

    @php
        // Contrato do Sistema (App\Models\Contrato) tem Processo; Contrato Manual/Externo
        // (App\Models\ContratoManual) não. As rotas/URLs "voltar" e de ação mudam conforme
        // o tipo — resolvidas aqui uma vez para reuso no resto da view.
        $ehManual = $contrato instanceof \App\Models\ContratoManual;
        $rotaBase = $ehManual ? 'admin.incidentes-manual' : 'admin.incidentes';
        $pathBase = $ehManual ? 'contratos-manuais' : 'contratos';
        $urlVoltar = $ehManual
            ? route('admin.contratos.show.manual', $contrato->id)
            : route('admin.processos.show', $processo->id);

        $docsTotal = count($documentos);
        $docsGerados = 0;
        $documentosProcesso = $documentosGerados; // já filtrado por incidente_id no controller

        foreach ($documentos as $tipoProg => $docProg) {
            $doc = $documentosProcesso->firstWhere('tipo_documento', $tipoProg);
            if ($doc && $doc->gerado_em) $docsGerados++;
        }
        $docsPercent = $docsTotal > 0 ? (int) round(($docsGerados / $docsTotal) * 100) : 0;
    @endphp

    <div class="mb-4">
        <a href="{{ $urlVoltar }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
            <i class="fas fa-arrow-left mr-2"></i> Voltar para o {{ $ehManual ? 'Contrato' : 'Processo' }}
        </a>
    </div>

    @unless($temAssinaturaEletronica)
        <div class="mb-4 p-3 text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded-lg flex items-center gap-2">
            <i class="fas fa-circle-info"></i>
            Contrato Manual/Externo: os documentos deste aditivo são gerados para assinatura física (impressa) — não há rodada de assinatura eletrônica.
        </div>
    @endunless

    {{-- Só aparece para aditivos antigos (criados antes desta atualização,
         sem os campos de abertura) --}}
    @if($ehAditivoLegado)
        <div class="mb-4 p-4 text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded-lg">
            <div class="flex items-center gap-2 mb-3 font-semibold">
                <i class="fas fa-triangle-exclamation"></i>
                Este aditivo foi criado antes da atualização de conformidade com o Tribunal de Contas. Complete os campos abaixo para liberar a geração dos documentos.
            </div>
            <form action="{{ route("$rotaBase.completar-dados-tce", ['contrato_id' => $contrato->id, 'incidente_id' => $incidente->id]) }}" method="POST" class="grid gap-3 sm:grid-cols-2">
                @csrf
                @if(in_array('tipo_objeto_contrato', $camposAberturaFaltantes))
                    <div>
                        <label class="block mb-1 text-xs font-medium text-gray-700">Tipo de Objeto do Contrato</label>
                        <select name="tipo_objeto_contrato" class="block w-full px-3 py-2 text-sm border-gray-300 rounded-md shadow-sm focus:ring-[var(--mint)] focus:border-[var(--mint)]">
                            <option value="">Selecione...</option>
                            <option value="compra">Compra (Fornecimento)</option>
                            <option value="servico">Serviço</option>
                        </select>
                    </div>
                @endif
                @if(in_array('contrato_continuo', $camposAberturaFaltantes))
                    <div>
                        <label class="block mb-1 text-xs font-medium text-gray-700">Contrato Contínuo?</label>
                        <select name="contrato_continuo" class="block w-full px-3 py-2 text-sm border-gray-300 rounded-md shadow-sm focus:ring-[var(--mint)] focus:border-[var(--mint)]">
                            <option value="">Selecione...</option>
                            <option value="1">Sim</option>
                            <option value="0">Não</option>
                        </select>
                    </div>
                @endif
                @if(in_array('natureza_alteracao', $camposAberturaFaltantes))
                    <div>
                        <label class="block mb-1 text-xs font-medium text-gray-700">Natureza da Alteração de Valor</label>
                        <select name="natureza_alteracao" class="block w-full px-3 py-2 text-sm border-gray-300 rounded-md shadow-sm focus:ring-[var(--mint)] focus:border-[var(--mint)]">
                            <option value="">Selecione...</option>
                            <option value="acrescimo">Acréscimo</option>
                            <option value="supressao">Supressão</option>
                            <option value="acrescimo_e_supressao">Acréscimo e Supressão</option>
                        </select>
                    </div>
                @endif
                <div class="sm:col-span-2">
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-[var(--mint)] rounded-md hover:bg-[var(--teal-dark-bg)]">
                        <i class="fas fa-save"></i> Salvar Dados Complementares
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Seção de Documentos -->
    <div class="mb-8">
        <div class="px-6 py-5 bg-white border border-gray-200 shadow-sm rounded-t-xl">
            <div class="flex flex-col items-start justify-between gap-3 lg:flex-row lg:items-center">
                <div>
                    <h3 class="text-xl font-semibold text-gray-800">Gerar Documentos do Aditivo</h3>
                    <span class="text-sm text-gray-500">Contrato Nº {{ $contrato->numero_contrato }}</span>
                </div>
                <div class="flex flex-col items-end gap-3 lg:flex-row lg:items-center w-full lg:w-auto">
                    <div class="w-full lg:w-72">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-medium text-gray-600">Documentos gerados</span>
                            <span class="text-xs font-semibold {{ $docsPercent === 100 ? 'text-green-700' : 'text-gray-700' }}">
                                {{ $docsGerados }}/{{ $docsTotal }}
                            </span>
                        </div>
                        <div class="w-full h-2 overflow-hidden bg-gray-200 rounded-full">
                            <div class="h-2 rounded-full transition-all duration-300 {{ $docsPercent === 100 ? 'bg-green-500' : 'bg-[var(--mint)]' }}"
                                 style="width: {{ $docsPercent }}%"></div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 ml-0 lg:ml-4">
                        @if($docsGerados > 0)
                            <a href="{{ route("$rotaBase.baixar-todos", ['contrato_id' => $contrato->id, 'incidente_id' => $incidente->id]) }}"
                               class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-white transition-colors duration-200 bg-[var(--mint)] rounded-md hover:bg-[var(--teal-dark-bg)]">
                                <i class="fas fa-download"></i> Baixar Todos
                            </a>
                        @endif
                        <form action="{{ route("$rotaBase.destroy", ['contrato_id' => $contrato->id, 'incidente_id' => $incidente->id]) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja reverter e excluir este aditivo? Esta ação não pode ser desfeita.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-red-600 transition-colors duration-200 bg-red-50 border border-red-200 rounded-md hover:bg-red-100 hover:text-red-700">
                                <i class="fas fa-trash-alt"></i> Reverter Aditivo
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

            <div class="overflow-x-auto bg-white border-x border-b border-gray-200 shadow-sm rounded-b-xl">
                <!-- Área de Mensagens -->
                <div id="message-container" class="p-4"></div>

                @if(session('success'))
                    <div class="p-4 mx-4 mt-4 text-sm text-green-800 bg-green-100 border border-green-200 rounded-lg">
                        {{ session('success') }}
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="p-4 mx-4 mt-4 text-sm text-red-800 bg-red-100 border border-red-200 rounded-lg">
                        <ul class="list-disc pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif



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
                        $initialData = $documentosProcesso->pluck('data_selecionada', 'tipo_documento')
                            ->mapWithKeys(fn($d, $t) => ["data_doc_$t" => $d])->toArray();
                    @endphp
                    <tbody class="bg-white divide-y divide-gray-200" x-data="formFieldAditivo({{ json_encode($initialData) }})">
                    @foreach ($documentos as $tipo => $doc)
                        @php
                            $dataId = 'data_' . $tipo;
                            $accordionId = "accordion-collapse-{$tipo}";
                            $requerAssinatura = $doc['requer_assinatura'] ?? false;
                            $temCampos = !empty($doc['campos']) || $tipo === 'dotacao_orcamentaria_aditivo';
                            $documentoGerado = $documentosProcesso->firstWhere('tipo_documento', $tipo);
                            $dataSelecionada = $documentoGerado->data_selecionada ?? '';
                        @endphp

                        <tr class="transition-colors duration-150 hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-2 h-2 mr-3 rounded-full" style="background-color: {{ $doc['cor'] }};"></div>

                                    <div class="text-sm font-semibold text-gray-900">
                                        {{ $doc['titulo'] }}

                                        @if ($documentoGerado && $documentoGerado->gerado_em)
                                            <span class="inline-flex items-center gap-1 ml-2 px-2 py-0.5 text-xs font-medium text-green-700 bg-green-100 rounded-full align-middle">
                                                <i class="fas fa-check-circle"></i>
                                                Gerado em {{ \Carbon\Carbon::parse($documentoGerado->gerado_em)->format('d/m/Y H:i') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                @if ($temCampos || $requerAssinatura)
                                    <button type="button"
                                            class="inline-flex items-center gap-1.5 mt-2 px-2.5 py-1 text-xs font-medium text-[var(--teal-text)] bg-[var(--mint)]/10 rounded-md hover:bg-[var(--mint)]/20 transition-colors"
                                            onclick="document.getElementById('{{ $accordionId }}').classList.toggle('hidden')">
                                        <i class="fas fa-sliders-h text-[10px]"></i>
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

                            <td class="flex items-center gap-2 px-6 py-4 text-center">
                                <div class="relative">
                                    <input type="date"
                                           class="w-40 px-3 py-2 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[var(--mint)] focus:border-[var(--mint)]"
                                           id="{{ $dataId }}"
                                           x-model="data_doc_{{ $tipo }}"
                                           @blur="saveField('data_doc_{{ $tipo }}')"
                                           value="{{ $dataSelecionada }}">
                                    <span x-show="confirmed['data_doc_{{ $tipo }}']" x-cloak
                                          class="absolute -right-1.5 -top-1.5 flex items-center justify-center w-4 h-4 text-white bg-green-500 rounded-full shadow"
                                          title="Data salva">
                                        <i class="fas fa-check text-[8px]"></i>
                                    </span>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center space-x-2">
                                    <button type="button"
                                            onclick="gerarPdfAditivo('{{ $contrato->id }}', '{{ $incidente->id }}', '{{ $tipo }}', document.getElementById('{{ $dataId }}').value, this)"
                                            class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-medium text-white transition-colors duration-200 bg-[var(--mint)] rounded-md hover:bg-[var(--teal-dark-bg)] focus:outline-none focus:ring-2 focus:ring-[var(--mint)] focus:ring-offset-2">
                                        <i class="fas fa-file-pdf"></i> Gerar PDF
                                    </button>
                                    @if ($requerAssinatura && !$ehManual)
                                        <x-botao-solicitar-assinatura
                                            :processo-id="$processo->id"
                                            :tipo="$tipo"
                                            :incidente-id="$incidente->id" />
                                    @endif
                                </div>
                            </td>
                        </tr>

                        @if ($temCampos || $requerAssinatura)
                            <tr>
                                <td colspan="3" class="p-0">
                                    <div id="{{ $accordionId }}" class="{{ $requerAssinatura ? '' : 'hidden' }}">
                                        <div class="p-4 border-t border-gray-200 bg-gray-50">
                                            @if ($requerAssinatura)
                                                <div class="pb-4 mb-6 border-b border-gray-200">
                                                    <h4 class="mb-4 text-sm font-semibold text-gray-700">Seleção de Assinantes</h4>
                                                    <div id="assinantes-container-{{ $tipo }}-i{{ $incidente->id }}" class="space-y-3"
                                                         data-assinante-bridge
                                                         data-tipo="{{ $tipo }}"
                                                         data-incidente-id="{{ $incidente->id }}"
                                                         data-obter-url="{{ $ehManual
                                                             ? route('admin.incidentes-manual.assinante.obter', ['contrato_id' => $contrato->id, 'incidente_id' => $incidente->id])
                                                             : route('admin.processos.assinatura.selecao.obter', $processo->id) }}"
                                                         data-salvar-url="{{ $ehManual
                                                             ? route('admin.incidentes-manual.assinante.salvar', ['contrato_id' => $contrato->id, 'incidente_id' => $incidente->id])
                                                             : route('admin.processos.assinatura.selecao.salvar', $processo->id) }}">
                                                        <div class="flex flex-col gap-3 p-4 bg-white border border-gray-200 rounded-lg assinante-item">
                                                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                                                <div class="flex-1 min-w-[180px]">
                                                                    <label class="block mb-1 text-xs font-medium text-gray-600">Unidade</label>
                                                                    <select name="assinante_unidade[]"
                                                                            class="block w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 unidade-select"
                                                                            onchange="updateResponsavel(this, '{{ $tipo }}-i{{ $incidente->id }}')">
                                                                        <option value="">Selecione a Unidade</option>
                                                                        @foreach ($prefeitura->unidades as $unidade)
                                                                            <option value="{{ $unidade->id }}">{{ $unidade->nome }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div class="flex flex-col flex-1 gap-2 sm:flex-row sm:items-center sm:gap-3">
                                                                    <div class="flex-1 min-w-[200px]">
                                                                        <label class="block mb-1 text-xs font-medium text-gray-600">Responsável</label>
                                                                        <input type="text" name="assinante_responsavel[]" placeholder="Nome do Responsável" readonly class="block w-full px-3 py-2 text-sm text-gray-700 bg-gray-100 border border-gray-300 rounded-md shadow-sm responsavel-input">
                                                                    </div>

                                                                    <div class="flex-1 min-w-[150px]">
                                                                        <label class="block mb-1 text-xs font-medium text-gray-600">Nº Portaria</label>
                                                                        <input type="text" name="assinante_portaria[]" placeholder="Número da Portaria" readonly class="block w-full px-3 py-2 text-sm text-gray-700 bg-gray-100 border border-gray-300 rounded-md shadow-sm portaria-input">
                                                                    </div>

                                                                    <div class="flex-1 min-w-[150px]">
                                                                        <label class="block mb-1 text-xs font-medium text-gray-600">Data Portaria</label>
                                                                        <input type="text" name="assinante_data_portaria[]" placeholder="Data da Portaria" readonly class="block w-full px-3 py-2 text-sm text-gray-700 bg-gray-100 border border-gray-300 rounded-md shadow-sm data-portaria-input">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="mt-4">
                                                        <button type="button" onclick="adicionarAssinante('{{ $tipo }}-i{{ $incidente->id }}')" class="flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-white bg-blue-500 rounded-md shadow hover:bg-blue-600 focus:ring-2 focus:ring-blue-300">
                                                            + Adicionar Assinante
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                            
                                            @if ($tipo === 'dotacao_orcamentaria_aditivo')
                                                <div x-data="dotacaoAditivo({{ json_encode($dotacoesAtuais->map(fn($d) => ['dotacao_orcamentaria' => $d->textoDotacao(), 'valor' => $d->valor])->values()) }}, {{ json_encode($dotacaoSugerida) }})">
                                                    <h4 class="mb-3 text-sm font-semibold text-gray-700">Linhas de Dotação Orçamentária</h4>
                                                    <p class="mb-3 text-xs text-gray-500">Sugerido a partir do cadastro do contrato — confira e ajuste antes de salvar.</p>
                                                    <template x-for="(linha, idx) in linhas" :key="idx">
                                                        <div class="grid grid-cols-1 gap-2 p-3 mb-2 bg-white border border-gray-200 rounded-lg sm:grid-cols-[1fr_16rem]">
                                                            <div>
                                                                <textarea x-model="linha.dotacao_orcamentaria" rows="3" maxlength="3000" placeholder="Dotação Orçamentária" class="w-full px-2 py-1.5 text-sm border-gray-300 rounded-md"></textarea>
                                                                <p class="mt-0.5 text-[11px] text-right text-gray-400"><span x-text="(linha.dotacao_orcamentaria || '').length"></span>/3000</p>
                                                            </div>
                                                            <div class="flex gap-2">
                                                                <input type="number" step="0.01" x-model="linha.valor" placeholder="Valor (opcional)" class="w-full px-2 py-1.5 text-sm border-gray-300 rounded-md">
                                                                <button type="button" @click="linhas.splice(idx, 1)" class="px-2 text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>
                                                            </div>
                                                        </div>
                                                    </template>
                                                    <button type="button" @click="adicionarLinha()" class="inline-flex items-center gap-1 px-3 py-1.5 mb-4 text-xs font-medium text-white bg-blue-500 rounded-md hover:bg-blue-600">+ Adicionar Linha</button>
                                                    <div class="flex justify-end">
                                                        <button type="button" @click="salvar()" :disabled="salvando" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white transition-colors duration-200 bg-[var(--mint)] rounded-md hover:bg-[var(--teal-dark-bg)] disabled:opacity-50">
                                                            <i class="fas fa-save"></i> <span x-text="salvando ? 'Salvando...' : 'Salvar Dotação'"></span>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif

                                            @if ($temCampos && $tipo !== 'dotacao_orcamentaria_aditivo')
                                            <form action="{{ route("$rotaBase.atualizar-campos", ['contrato_id' => $contrato->id, 'incidente_id' => $incidente->id]) }}" method="POST" enctype="multipart/form-data"
                                                  data-campos-form="{{ $tipo }}">
                                                @csrf
                                                <input type="hidden" name="tipo" value="{{ $incidente->tipo }}">
                                                <input type="hidden" name="categoria" value="{{ $incidente->categoria }}">
                                                <h4 class="mb-3 text-sm font-semibold text-gray-700">Campos do Documento</h4>
                                            <div class="grid gap-4">
                                                @foreach ($doc['campos'] as $campo)
                                                    <div>
                                                        <label class="block mb-1 text-sm font-medium text-gray-700">{{ $campo['label'] }}</label>
                                                        
                                                        @if($campo['tipo'] === 'textarea')
                                                            <div class="relative" x-data="{ openIA: false, queryIA: '{{ $campo['value'] }}', loadingIA: false }">
                                                                <textarea name="{{ $campo['name'] }}" id="{{ $tipo }}_{{ $campo['name'] }}" class="block w-full px-3 py-2 text-sm border-gray-300 rounded-md shadow-sm focus:ring-[var(--mint)] focus:border-[var(--mint)]" rows="4" x-model="queryIA">{{ $campo['value'] }}</textarea>
                                                                @if(isset($campo['ia']) && $campo['ia'])
                                                                    <x-ia-popover name="{{ $tipo }}_{{ $campo['name'] }}" :iaCampo="$campo['ia_campo'] ?? $campo['name']" :processoId="$processo?->id" :incidenteContratualId="$incidente->id" />
                                                                @endif
                                                            </div>
                                                        @elseif($campo['tipo'] === 'file')
                                                            <div x-data="{ marcadoParaRemover: false }">
                                                                <input type="file" name="{{ $campo['name'] }}" id="{{ $tipo }}_{{ $campo['name'] }}" accept=".pdf" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-[var(--mint)] file:text-white hover:file:bg-[var(--teal-dark-bg)]" @change="marcadoParaRemover = false">
                                                                @if($campo['value'])
                                                                    <input type="hidden" name="remover_{{ $campo['name'] }}" :value="marcadoParaRemover ? '1' : '0'">
                                                                    <div class="flex items-center gap-3 mt-2">
                                                                        <a x-show="!marcadoParaRemover" href="{{ Storage::url($campo['value']) }}" target="_blank" class="inline-block text-xs font-medium text-blue-600 hover:underline">
                                                                            <i class="fas fa-file-pdf"></i> Visualizar arquivo atual
                                                                        </a>
                                                                        <button x-show="!marcadoParaRemover" type="button" @click="marcadoParaRemover = true" class="inline-flex items-center gap-1 text-xs font-medium text-red-600 hover:underline">
                                                                            <i class="fas fa-trash"></i> Remover arquivo
                                                                        </button>
                                                                        <span x-show="marcadoParaRemover" x-cloak class="inline-flex items-center gap-2 text-xs font-medium text-red-600">
                                                                            <i class="fas fa-triangle-exclamation"></i> Arquivo será removido ao salvar
                                                                            <button type="button" @click="marcadoParaRemover = false" class="font-semibold underline">Desfazer</button>
                                                                        </span>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @elseif($campo['tipo'] === 'number')
                                                            <input type="number" name="{{ $campo['name'] }}" id="{{ $tipo }}_{{ $campo['name'] }}" step="{{ $campo['step'] ?? '1' }}" value="{{ $campo['value'] }}" class="block w-full px-3 py-2 text-sm border-gray-300 rounded-md shadow-sm focus:ring-[var(--mint)] focus:border-[var(--mint)]">
                                                        @elseif($campo['tipo'] === 'string')
                                                            <input type="text" name="{{ $campo['name'] }}" id="{{ $tipo }}_{{ $campo['name'] }}" value="{{ $campo['value'] }}" class="block w-full px-3 py-2 text-sm border-gray-300 rounded-md shadow-sm focus:ring-[var(--mint)] focus:border-[var(--mint)]">
                                                        @elseif($campo['tipo'] === 'select')
                                                            <select name="{{ $campo['name'] }}" id="{{ $tipo }}_{{ $campo['name'] }}" class="block w-full px-3 py-2 text-sm border-gray-300 rounded-md shadow-sm focus:ring-[var(--mint)] focus:border-[var(--mint)]">
                                                                <option value="">Selecione...</option>
                                                                @foreach($campo['opcoes'] as $opcao)
                                                                    <option value="{{ $opcao->value }}" @selected($campo['value'] === $opcao->value)>{{ $opcao->getDisplayName() }}</option>
                                                                @endforeach
                                                            </select>
                                                        @elseif($campo['tipo'] === 'checkbox')
                                                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                                                <input type="hidden" name="{{ $campo['name'] }}" value="0">
                                                                <input type="checkbox" name="{{ $campo['name'] }}" id="{{ $tipo }}_{{ $campo['name'] }}" value="1" @checked($campo['value']) class="rounded border-gray-300 text-[var(--teal-text)] focus:ring-[var(--mint)]">
                                                                Sim
                                                            </label>
                                                        @elseif($campo['tipo'] === 'checkboxes')
                                                            <div class="flex flex-wrap gap-3">
                                                                @foreach($campo['opcoes'] as $opcao)
                                                                    <label class="inline-flex items-center gap-1.5 text-sm text-gray-700">
                                                                        <input type="checkbox" name="{{ $campo['name'] }}[]" value="{{ $opcao->value }}" @checked(in_array($opcao->value, (array) $campo['value'])) class="rounded border-gray-300 text-[var(--teal-text)] focus:ring-[var(--mint)]">
                                                                        {{ $opcao->getDisplayName() }}
                                                                    </label>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                                <div class="flex justify-end mt-4">
                                                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white transition-colors duration-200 bg-[var(--mint)] rounded-md hover:bg-[var(--teal-dark-bg)]">
                                                        <i class="fas fa-save"></i> Salvar Informações
                                                    </button>
                                                </div>
                                            </form>
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

    @include('Admin.Processos.partials.assinante-bridge-script')

    <script>
        const unidadesAssinantes = @json($unidadesData);

        function updateResponsavel(select, chaveDocumento) {
            const selectedUnidade = unidadesAssinantes.find(u => u.id == select.value);
            const item = select.closest('.assinante-item');
            const respInput  = item.querySelector('.responsavel-input');
            const portInput  = item.querySelector('.portaria-input');
            const dataInput  = item.querySelector('.data-portaria-input');
            if (selectedUnidade) {
                if (respInput)  respInput.value  = selectedUnidade.servidor_responsavel || '';
                if (portInput)  portInput.value  = selectedUnidade.numero_portaria || '';
                if (dataInput)  dataInput.value  = selectedUnidade.data_portaria || '';
            } else {
                if (respInput)  respInput.value  = '';
                if (portInput)  portInput.value  = '';
                if (dataInput)  dataInput.value  = '';
            }
        }

        function adicionarAssinante(chaveDocumento) {
            const container = document.getElementById(`assinantes-container-${chaveDocumento}`);
            const novo = document.createElement('div');
            novo.className = 'flex flex-col gap-3 p-4 bg-white border border-gray-200 rounded-lg assinante-item';
            novo.innerHTML = `
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="flex-1 min-w-[180px]">
                        <label class="block mb-1 text-xs font-medium text-gray-600">Unidade</label>
                        <select name="assinante_unidade[]"
                                class="block w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 unidade-select"
                                onchange="updateResponsavel(this, '${chaveDocumento}')">
                            <option value="">Selecione a Unidade</option>
                            ${unidadesAssinantes.map(u => `<option value="${u.id}">${u.nome}</option>`).join('')}
                        </select>
                    </div>
                    <div class="flex flex-col flex-1 gap-2 sm:flex-row sm:items-center sm:gap-3">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block mb-1 text-xs font-medium text-gray-600">Responsável</label>
                            <input type="text" name="assinante_responsavel[]" placeholder="Nome do Responsável" readonly class="block w-full px-3 py-2 text-sm text-gray-700 bg-gray-100 border border-gray-300 rounded-md shadow-sm responsavel-input">
                        </div>
                        <div class="flex-1 min-w-[150px]">
                            <label class="block mb-1 text-xs font-medium text-gray-600">Nº Portaria</label>
                            <input type="text" name="assinante_portaria[]" placeholder="Número da Portaria" readonly class="block w-full px-3 py-2 text-sm text-gray-700 bg-gray-100 border border-gray-300 rounded-md shadow-sm portaria-input">
                        </div>
                        <div class="flex-1 min-w-[150px]">
                            <label class="block mb-1 text-xs font-medium text-gray-600">Data Portaria</label>
                            <input type="text" name="assinante_data_portaria[]" placeholder="Data da Portaria" readonly class="block w-full px-3 py-2 text-sm text-gray-700 bg-gray-100 border border-gray-300 rounded-md shadow-sm data-portaria-input">
                        </div>
                    </div>
                    <div class="flex justify-end mt-1">
                        <button type="button" onclick="removerAssinante(this, '${chaveDocumento}')"
                                class="px-2 py-1 text-xs font-medium text-white bg-red-500 rounded-md hover:bg-red-600">
                            <i class="fas fa-trash"></i> Remover
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(novo);
        }

        function removerAssinante(botao, chaveDocumento) {
            const container = document.getElementById(`assinantes-container-${chaveDocumento}`);
            const item = botao.closest('.assinante-item');
            if (container.querySelectorAll('.assinante-item').length > 1) {
                item.remove();
            } else {
                showMessage('É obrigatório manter pelo menos um assinante.', 'error');
            }
        }
    </script>

    <script>
        function showMessage(message, type) {
            const container = document.getElementById('message-container');
            const alertClass = type === 'success' ? 'bg-green-100 text-green-800 border-green-200' : 'bg-red-100 text-red-800 border-red-200';
            const html = `
                <div class="p-4 mx-4 mt-4 text-sm border rounded-lg ${alertClass}" role="alert">
                    <span class="font-medium">${type === 'success' ? 'Sucesso!' : 'Erro!'}</span> ${message}
                </div>
            `;
            container.innerHTML = html;
            setTimeout(() => container.innerHTML = '', 5000);
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('dotacaoAditivo', (linhasIniciais, sugestao) => ({
                linhas: (linhasIniciais && linhasIniciais.length > 0) ? linhasIniciais : [{ ...sugestao, valor: null }],
                salvando: false,

                adicionarLinha() {
                    this.linhas.push({ dotacao_orcamentaria: '', valor: null });
                },

                salvar() {
                    this.salvando = true;
                    fetch(`{{ route("$rotaBase.dotacoes.atualizar", ['contrato_id' => $contrato->id, 'incidente_id' => $incidente->id]) }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ dotacoes: this.linhas }),
                    })
                    .then(response => response.json().then(data => ({ ok: response.ok, data })))
                    .then(({ ok, data }) => {
                        this.salvando = false;
                        if (ok) {
                            showMessage('Dotação orçamentária salva com sucesso.', 'success');
                        } else {
                            showMessage(data.message || 'Erro ao salvar a dotação orçamentária.', 'error');
                        }
                    })
                    .catch(() => {
                        this.salvando = false;
                        showMessage('Erro ao salvar a dotação orçamentária.', 'error');
                    });
                },
            }));

            Alpine.data('formFieldAditivo', (initialData) => ({
                ...initialData,
                confirmed: {},
                
                saveField(field) {
                    const value = this[field];
                    
                    fetch(`{{ route("$rotaBase.documentos.salvar-campo", ['contrato_id' => $contrato->id, 'incidente_id' => $incidente->id]) }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ [field]: value })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.confirmed[field] = true;
                            setTimeout(() => {
                                this.confirmed[field] = false;
                            }, 2000);
                        } else {
                            showMessage(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showMessage('Erro ao salvar o campo.', 'error');
                    });
                }
            }));
        });

        // Garante que assinantes sejam salvos ANTES do submit de "Salvar Informações"
        // (o debounce do bridge pode não completar antes do page reload causado pelo form submit)
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-campos-form]').forEach(form => {
                form.addEventListener('submit', async function (e) {
                    const accordion = form.closest('[id^="accordion-collapse-"]');
                    if (!accordion) return;
                    const bridge = accordion.querySelector('[data-assinante-bridge]');
                    if (!bridge) return;
                    e.preventDefault();
                    const assinantesItems = bridge.querySelectorAll('.assinante-item');
                    const assinantes = [];
                    assinantesItems.forEach(item => {
                        const sel       = item.querySelector('.unidade-select');
                        const unidadeId = sel?.value ?? '';
                        const unidadeNome = sel?.options[sel.selectedIndex]?.text ?? '';
                        const responsavel  = item.querySelector('.responsavel-input')?.value ?? '';
                        const portaria     = item.querySelector('.portaria-input')?.value ?? '';
                        const dataPortaria = item.querySelector('.data-portaria-input')?.value ?? '';
                        if (unidadeId || responsavel) {
                            assinantes.push({ unidade_id: unidadeId, unidade_nome: unidadeNome, responsavel, portaria, data_portaria: dataPortaria });
                        }
                    });
                    if (assinantes.length > 0) {
                        try {
                            await fetch(bridge.dataset.salvarUrl, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                body: JSON.stringify({ tipo_documento: bridge.dataset.tipo, assinantes }),
                            });
                        } catch (err) { console.warn('[assinantes] falha ao salvar antes do submit', err); }
                    }
                    form.submit();
                });
            });
        });

        async function gerarPdfAditivo(contratoId, incidenteId, tipo, dataSelecionada, btnElement) {
            const ehManual = {{ $ehManual ? 'true' : 'false' }};
            const tr = btnElement.closest('tr').nextElementSibling;
            const bridgeContainer = tr ? tr.querySelector(`[data-assinante-bridge][data-tipo="${tipo}"]`) : null;

            // Salva os campos do formulário do mesmo accordion antes de gerar o PDF.
            // Isso evita que o usuário precise clicar "Salvar Informações" separadamente.
            const camposForm = tr ? tr.querySelector(`[data-campos-form="${tipo}"]`) : null;
            if (camposForm) {
                try {
                    const fd = new FormData(camposForm);
                    await fetch(camposForm.action, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: fd });
                } catch (e) { console.warn('Falha salvar campos antes de gerar PDF', e); }
            }

            if (bridgeContainer) {
                if (ehManual) {
                    // Para contratos manuais: salvar assinantes diretamente via rota própria
                    try {
                        const assinantesItems = bridgeContainer.querySelectorAll('.assinante-item');
                        const assinantes = [];
                        assinantesItems.forEach(item => {
                            const responsavel = item.querySelector('.responsavel-input')?.value ?? '';
                            const unidadeNome = item.querySelector('.unidade-select option:checked')?.text ?? '';
                            const portaria = item.querySelector('.portaria-input')?.value ?? '';
                            const dataPortaria = item.querySelector('.data-portaria-input')?.value ?? '';
                            if (responsavel) {
                                assinantes.push({ responsavel, unidade_nome: unidadeNome, portaria, data_portaria: dataPortaria });
                            }
                        });
                        if (assinantes.length > 0) {
                            await fetch(bridgeContainer.dataset.salvarUrl, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                body: JSON.stringify({ tipo_documento: tipo, assinantes })
                            });
                        }
                    } catch (e) { console.warn('Falha salvar assinantes manual', e); }
                } else if (typeof window.salvarSelecaoAntesDeGerar === 'function') {
                    // Para contratos do sistema: usar o bridge script padrão
                    try {
                        await window.salvarSelecaoAntesDeGerar({{ $processo?->id ?? 'null' }}, tipo, null, null, incidenteId);
                    } catch (e) { console.warn('Falha salvar assinaturas antes de gerar', e); }
                }
            }

            let url = `/admin/{{ $pathBase }}/${contratoId}/incidentes/${incidenteId}/pdf/${tipo}`;
            if (dataSelecionada) {
                // Força o salvamento da data antes de abrir o PDF
                fetch(`{{ route("$rotaBase.documentos.salvar-campo", ['contrato_id' => $contrato->id, 'incidente_id' => $incidente->id]) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ [`data_doc_${tipo}`]: dataSelecionada })
                }).then(() => {
                    window.open(url, '_blank');
                    setTimeout(() => location.reload(), 2000);
                });
            } else {
                window.open(url, '_blank');
                setTimeout(() => location.reload(), 2000);
            }
        }
    </script>
@endsection
