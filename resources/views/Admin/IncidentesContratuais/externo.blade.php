@extends('layouts.app')

@section('page-title', 'Aditivo Externo')
@section('page-subtitle', 'Registrar aditivo feito fora do sistema')

@section('content')

    @php
        $temPrazo = in_array($incidente->tipo, ['prazo', 'prazo_valor']);
        $temValor = in_array($incidente->tipo, ['valor', 'prazo_valor']);
        $tipoLabel = ['prazo' => 'Prorrogação de Prazo', 'valor' => 'Acréscimo de Valor', 'prazo_valor' => 'Prazo e Valor'][$incidente->tipo] ?? $incidente->tipo;
    @endphp

    <div class="mb-4">
        <a href="{{ $urlVoltar }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
            <i class="fas fa-arrow-left mr-2"></i> Voltar para o {{ $ehManual ? 'Contrato' : 'Processo' }}
        </a>
    </div>

    <div class="mb-4 p-3 text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded-lg flex items-center gap-2">
        <i class="fas fa-circle-info"></i>
        Aditivo externo: feito fora do sistema. Registre as informações principais e anexe o PDF —
        não gera solicitação, parecer nem termo.
    </div>

    @if($errors->any())
        <div class="p-4 mb-4 text-sm text-red-800 bg-red-100 border border-red-200 rounded-lg">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white border border-gray-200 shadow-sm rounded-xl">
        <div class="px-6 py-5 border-b border-gray-200">
            <h3 class="text-xl font-semibold text-gray-800">Aditivo Externo — Contrato Nº {{ $contrato->numero_contrato }}</h3>
            <span class="text-sm text-gray-500">{{ $tipoLabel }}</span>
        </div>

        <form method="POST"
              action="{{ route($rotaSalvar, ['contrato_id' => $contrato->id, 'incidente_id' => $incidente->id]) }}"
              enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf

            <div>
                <label class="block mb-1 text-sm font-medium text-gray-700">Data do Aditivo <span class="text-red-500">*</span></label>
                <input type="date" name="data_aditivo" required
                       value="{{ old('data_aditivo', optional($incidente->data_aditivo)->format('Y-m-d')) }}"
                       class="block w-full max-w-xs px-3 py-2 text-sm border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496]">
            </div>

            @if($temPrazo)
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Meses de Prorrogação <span class="text-red-500">*</span></label>
                    <input type="number" name="meses_prorrogacao" min="1" required
                           value="{{ old('meses_prorrogacao', $incidente->meses_prorrogacao) }}"
                           class="block w-full max-w-xs px-3 py-2 text-sm border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496]">
                    <p class="mt-1 text-xs text-gray-500">
                        Vencimento atual do contrato: {{ optional($contrato->data_finalizacao)->format('d/m/Y') ?? '—' }}
                    </p>
                </div>
            @endif

            @if($temValor)
                @if($ehManual)
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">Valor Acrescido (R$) <span class="text-red-500">*</span></label>
                        <input type="number" name="valor_acrescido" step="0.01" min="0.01" required
                               value="{{ old('valor_acrescido') }}"
                               class="block w-full max-w-xs px-3 py-2 text-sm border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496]">
                        <p class="mt-1 text-xs text-gray-500">
                            Valor atual do contrato: R$ {{ number_format($contrato->valor_total ?? 0, 2, ',', '.') }}
                        </p>
                    </div>
                @else
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Quantidade Aditivada por Item <span class="text-red-500">*</span></label>
                        <p class="mb-2 text-xs text-gray-500">
                            Informe a quantidade aditivada de cada item — reflete direto no Almoxarifado.
                        </p>
                        <div class="overflow-x-auto border border-gray-200 rounded-lg">
                            <table class="min-w-full text-sm divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Item</th>
                                        <th class="px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase">Qtd. Contratada</th>
                                        <th class="w-40 px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase">Qtd. Aditivada</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @forelse($lotesContratados as $lote)
                                        @php $itemAtual = $itensAtuais->get($lote->id); @endphp
                                        <tr>
                                            <td class="px-4 py-2">{{ $lote->lote->descricao ?? '—' }}</td>
                                            <td class="px-4 py-2 text-right">{{ number_format($lote->quantidade_contratada, 2, ',', '.') }}</td>
                                            <td class="px-4 py-2">
                                                <input type="hidden" name="itens[{{ $loop->index }}][lote_contratado_id]" value="{{ $lote->id }}">
                                                <input type="number" step="0.0001" min="0"
                                                       name="itens[{{ $loop->index }}][quantidade_aditivada]"
                                                       value="{{ old("itens.{$loop->index}.quantidade_aditivada", $itemAtual->quantidade_aditivada ?? '') }}"
                                                       class="block w-full px-2 py-1 text-sm text-right border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496]">
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-4 py-3 text-center text-gray-500">Nenhum item contratado encontrado para este contrato.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            @endif

            <div>
                <label class="block mb-1 text-sm font-medium text-gray-700">Justificativa</label>
                <textarea name="justificativa" rows="3"
                          class="block w-full px-3 py-2 text-sm border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496]">{{ old('justificativa', $incidente->justificativa) }}</textarea>
            </div>

            <div>
                <label class="block mb-1 text-sm font-medium text-gray-700">
                    Anexo do Aditivo (PDF) @unless($incidente->arquivo_aditivo_externo_path)<span class="text-red-500">*</span>@endunless
                </label>
                <input type="file" name="arquivo_aditivo_externo" accept=".pdf"
                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-[#009496] file:text-white hover:file:bg-[#007b85]">
                @if($incidente->arquivo_aditivo_externo_path)
                    <a href="{{ Storage::url($incidente->arquivo_aditivo_externo_path) }}" target="_blank" class="inline-block mt-2 text-xs font-medium text-blue-600 hover:underline">
                        <i class="fas fa-file-pdf"></i> Visualizar anexo atual
                    </a>
                @endif
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-200">
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white transition-colors duration-200 bg-[#009496] rounded-md hover:bg-[#007b85]">
                    <i class="fas fa-save"></i> Salvar Aditivo Externo
                </button>
            </div>
        </form>
    </div>
@endsection
