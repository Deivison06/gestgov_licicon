{{--
    Lista as desistências/abandonos de assinatura de Ata registrados nesta homologação.

    Variáveis esperadas:
      - $processo: instância do Processo
      - $homologacao: Homologacao
--}}
@if ($homologacao->desistencias->isNotEmpty())
    <div class="px-5 py-4 mt-2 border-t border-amber-100 bg-amber-50/50">
        <h4 class="flex items-center gap-2 mb-3 text-sm font-semibold text-amber-900">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"></path></svg>
            Desistências/Abandonos de Ata registrados
        </h4>

        <div class="space-y-2">
            @foreach ($homologacao->desistencias as $desistencia)
                <div class="flex flex-col gap-2 p-3 bg-white border border-amber-200 rounded-lg sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-sm text-gray-700">
                        <span class="font-semibold text-gray-900">{{ $desistencia->vencedor->razao_social ?? '—' }}</span>
                        <span class="ml-2 text-xs text-gray-500">
                            convocada em {{ \Carbon\Carbon::parse($desistencia->data_solicitacao_assinatura)->format('d/m/Y') }}
                            @if ($desistencia->data_decisao)
                                — decisão em {{ \Carbon\Carbon::parse($desistencia->data_decisao)->format('d/m/Y') }}
                            @endif
                            — registrado em {{ $desistencia->created_at->format('d/m/Y H:i') }}
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button"
                                onclick="abrirEditarDesistencia(this)"
                                data-homologacao-id="{{ $homologacao->id }}"
                                data-desistencia-id="{{ $desistencia->id }}"
                                data-vencedor="{{ $desistencia->vencedor->razao_social ?? '—' }}"
                                data-data-solicitacao="{{ optional($desistencia->data_solicitacao_assinatura)->format('Y-m-d') }}"
                                data-data-decisao="{{ optional($desistencia->data_decisao)->format('Y-m-d') }}"
                                data-observacao="{{ $desistencia->observacao }}"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                                title="Corrigir datas/observação e regerar o Termo">
                            <i class="fas fa-pen"></i> Editar
                        </button>

                        @if ($desistencia->caminho_pdf)
                            <a href="{{ route('admin.processos.finalizacao.homologacoes.desistencias.pdf.baixar', [$processo, $homologacao, $desistencia]) }}"
                               download
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-white bg-amber-600 rounded-md hover:bg-amber-700">
                                <i class="fas fa-file-pdf"></i> Termo de Decisão
                            </a>
                        @endif

                        @foreach ($desistencia->anexos as $anexo)
                            <a href="{{ route('admin.processos.finalizacao.homologacoes.desistencias.anexo.baixar', [$processo, $homologacao, $desistencia, $anexo]) }}"
                               download
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-amber-800 bg-amber-100 rounded-md hover:bg-amber-200"
                               title="{{ $anexo->nome_original }}">
                                <i class="fas fa-paperclip"></i> Comprovante {{ $loop->iteration }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
