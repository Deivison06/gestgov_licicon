{{--
    Renderiza a tabela de documentos de um escopo (processo ou homologação).

    Variáveis esperadas:
      - $processo: instância do Processo
      - $documentos: array tipo => config (resultado de FinalizacaoDocumentoService)
      - $documentosGerados: Collection de Documento já gerados desse escopo
      - $homologacao: Homologacao|null (null = escopo do processo)
      - $rotaPrefix: string usado nas URLs de download (p.ex. caminho relativo)
--}}
@php
    $homologacaoId = $homologacao?->id;
    $homologacaoQuery = $homologacaoId ? '&homologacao_id=' . $homologacaoId : '';
    $tableId = $homologacaoId ? "tbl-docs-homol-{$homologacaoId}" : 'tbl-docs-processo';
@endphp

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
        $allDocDates = $documentosGerados->pluck('data_selecionada', 'tipo_documento')
            ->mapWithKeys(fn($d, $t) => ["data_doc_$t" => $d])->toArray();
        $fonte = $homologacao ?: $processo->finalizacao;
        $initialData = array_merge($fonte?->toArray() ?? [], $allDocDates);
        $initialData['homologacao_id'] = $homologacaoId;
    @endphp
    <tbody class="bg-white divide-y divide-gray-200" x-data="formField({{ json_encode($initialData) }})">
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
                // Ata por vencedor: chave dinâmica `ata_registro_precos_v{id}` traz
                // `vencedor_id` e `tipo_base` no $doc (preenchidos pelo DocumentoService).
                $isAtaDinamica = ($doc['tipo_base'] ?? null) === 'ata_registro_precos'
                    && !empty($doc['vencedor_id']);
                $vencedorIdDoc = $doc['vencedor_id'] ?? null;
                $tipoBaseDoc = $doc['tipo_base'] ?? $tipo;

                if ($isAtaDinamica) {
                    // Documento gerado vem de atasRegistroPreco (tabela própria), não de documentos.
                    $atasColl = $atasRegistroPreco ?? collect();
                    $documentoGerado = $atasColl->firstWhere('vencedor_id', $vencedorIdDoc);
                } else {
                    $documentoGerado = $documentosGerados->firstWhere('tipo_documento', $tipo);
                }

                $accordionSuffix = $homologacaoId ? "-h{$homologacaoId}" : '-p';
                if ($isAtaDinamica) {
                    $accordionSuffix .= "-v{$vencedorIdDoc}";
                }
                $accordionId = "accordion-collapse-{$tipo}{$accordionSuffix}";
                $requerAssinatura = $doc['requer_assinatura'] ?? false;
                $temCampos = !empty($doc['campos']);
                $idSuffix = $accordionSuffix;
            @endphp

            {{-- Linha principal do documento --}}
            <tr class="transition-colors duration-150 hover:bg-gray-50">
                <td class="px-6 py-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-2 h-2 mr-3 {{ $doc['cor'] }} rounded-full"></div>
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
                    @if ($temCampos || $requerAssinatura)
                        <button type="button"
                                class="mt-2 text-xs font-medium text-red-600 hover:text-red-800"
                                data-collapse-toggle="{{ $accordionId }}"
                                aria-expanded="false"
                                aria-controls="{{ $accordionId }}">
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
                        @if ($isAtaDinamica)
                            {{-- Ata por vencedor: bypass do Alpine state pra não conflitar com várias linhas. --}}
                            <input type="date"
                                   class="w-40 px-3 py-2 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                   id="data_{{ $tipo }}{{ $idSuffix }}"
                                   onblur="saveCampoAta('data_doc_ata_registro_precos', this.value, {{ $homologacaoId ?? 'null' }}, {{ $vencedorIdDoc }})"
                                   value="{{ optional($documentoGerado)->data_selecionada ? \Carbon\Carbon::parse($documentoGerado->data_selecionada)->format('Y-m-d') : '' }}">
                        @else
                            <input type="date"
                                   class="w-40 px-3 py-2 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                   id="data_{{ $tipo }}{{ $idSuffix }}"
                                   x-model="data_doc_{{ $tipo }}"
                                   @blur="saveField('data_doc_{{ $tipo }}')"
                                   value="{{ $documentoGerado->data_selecionada ?? '' }}">
                        @endif

                        @if ($tipo === 'parecer_controle_interno' && $processo->modalidade === \App\Enums\ModalidadeEnum::PREGAO_ELETRONICO)
                            <select id="parecer_select_{{ $tipo }}{{ $idSuffix }}"
                                    name="parecer_select_{{ $tipo }}"
                                    class="block w-40 px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm">
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
                        @php
                            $vencedorIdParam = $vencedorIdDoc ?? 'null';
                            $downloadUrl = route('admin.processos.finalizacao.documento.dowload', [
                                'processo' => $processo->id,
                                'tipo' => $tipo,
                            ]);
                            $downloadQuery = [];
                            if ($homologacaoId) {
                                $downloadQuery[] = 'homologacao_id=' . $homologacaoId;
                            }
                            if ($vencedorIdDoc) {
                                $downloadQuery[] = 'vencedor_id=' . $vencedorIdDoc;
                            }
                            $downloadFull = $downloadUrl . (empty($downloadQuery) ? '' : '?' . implode('&', $downloadQuery));
                        @endphp
                        @if ($requerAssinatura)
                            <button type="button"
                                    onclick="gerarPdf('{{ $processo->id }}', '{{ $tipo }}', document.getElementById('data_{{ $tipo }}{{ $idSuffix }}').value, event, '{{ $idSuffix }}', {{ $homologacaoId ?? 'null' }}, {{ $vencedorIdParam }})"
                                    class="px-4 py-2 text-xs font-medium text-white transition-colors duration-200 bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                Gerar PDF
                            </button>

                            <x-botao-solicitar-assinatura
                                :processo-id="$processo->id"
                                :tipo="$tipo"
                                :homologacao-id="$homologacaoId ?? null"
                                :vencedor-id="$vencedorIdDoc ?? null" />
                        @else
                            <button type="button"
                                    onclick="gerarPdfSemAssinatura('{{ $processo->id }}', '{{ $tipo }}', event, {{ $homologacaoId ?? 'null' }}, {{ $vencedorIdParam }})"
                                    class="px-4 py-2 text-xs font-medium text-white transition-colors duration-200 bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                Gerar PDF
                            </button>
                        @endif

                        @if ($documentoGerado)
                            <a href="{{ $downloadFull }}"
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

            @if ($temCampos || $requerAssinatura)
                <tr>
                    <td colspan="3" class="p-0">
                        <div id="{{ $accordionId }}" class="hidden">
                            <div class="p-4 border-t border-gray-200 bg-gray-50">
                                @if ($requerAssinatura)
                                    <div class="pb-4 mb-6 border-b border-gray-200">
                                        <h4 class="mb-4 text-sm font-semibold text-gray-700">Seleção de Assinantes</h4>

                                        <div id="assinantes-container-{{ $tipo }}{{ $idSuffix }}" class="space-y-3">
                                            <div class="flex flex-col gap-3 p-4 bg-white border border-gray-200 rounded-lg assinante-item">
                                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                                    <div class="flex-1 min-w-[180px]">
                                                        <label class="block mb-1 text-xs font-medium text-gray-600">Unidade</label>
                                                        <select name="assinante_unidade[]"
                                                                class="block w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 unidade-select"
                                                                onchange="updateResponsavel(this, '{{ $tipo }}{{ $idSuffix }}')">
                                                            <option value="">Selecione a Unidade</option>
                                                            @foreach ($processo->prefeitura->unidades as $unidade)
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
                                            <button type="button" onclick="adicionarAssinante('{{ $tipo }}{{ $idSuffix }}')" class="flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-white bg-blue-500 rounded-md shadow hover:bg-blue-600 focus:ring-2 focus:ring-blue-300">
                                                + Adicionar Assinante
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                @if (!empty($doc['campos']))
                                    <div>
                                        <h4 class="mb-3 text-sm font-semibold text-gray-700">Campos do Documento</h4>

                                        @if ($isAtaDinamica)
                                            {{-- Ata por vencedor: campos isolados, sem Alpine state (cada vencedor tem sua Ata). --}}
                                            @php
                                                $unidadesPrefeitura = $processo->prefeitura->unidades;
                                            @endphp
                                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                                <div>
                                                    <label class="block mb-1 text-sm font-medium text-gray-700">Número ATA</label>
                                                    <input type="text"
                                                           id="numero_ata_{{ $vencedorIdDoc }}"
                                                           class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                                           value="{{ optional($documentoGerado)->numero_ata_registro_precos }}"
                                                           onblur="saveCampoAta('numero_ata_registro_precos', this.value, {{ $homologacaoId ?? 'null' }}, {{ $vencedorIdDoc }})">
                                                </div>
                                                <div>
                                                    <label class="block mb-1 text-sm font-medium text-gray-700">Cargo controle interno</label>
                                                    <select id="cargo_controle_interno_{{ $vencedorIdDoc }}"
                                                            class="block w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                                            onchange="saveCampoAta('cargo_controle_interno', this.value, {{ $homologacaoId ?? 'null' }}, {{ $vencedorIdDoc }})">
                                                        <option value="">Selecione um Responsável</option>
                                                        @foreach ($unidadesPrefeitura as $unidade)
                                                            <option value="{{ $unidade->servidor_responsavel }}"
                                                                {{ optional($documentoGerado)->cargo_controle_interno === $unidade->servidor_responsavel ? 'selected' : '' }}>
                                                                {{ $unidade->nome }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        @else
                                            <form action="{{ route('admin.processos.finalizacao.store', $processo) }}" method="POST" @submit.prevent="submitForm">
                                                @csrf
                                                <input type="hidden" name="processo_id" value="{{ $processo->id }}">
                                                @if ($homologacaoId)
                                                    <input type="hidden" name="homologacao_id" value="{{ $homologacaoId }}">
                                                @endif

                                                @foreach ($doc['campos'] as $campo)
                                                    @include('Admin.Processos.partials.forms-finalizacao')
                                                @endforeach
                                            </form>
                                        @endif
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
