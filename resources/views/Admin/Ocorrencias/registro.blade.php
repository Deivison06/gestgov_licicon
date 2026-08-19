<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Registro de Ocorrência - {{ $ocorrencia->numero_ocorrencia }}</title>
    <style>
        @page { margin: 0; size: A4; }
        body {
            margin: 0;
            padding: 3.5cm 1.5cm 3.5cm 1.5cm;
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
            line-height: 1.5;
            text-align: justify;
        }
        .timbre-bg { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1000; }
        .content-body { margin: 0 4rem; }
        .text-center { text-align: center; }
        .uppercase { text-transform: uppercase; }
        .bold { font-weight: bold; }
        .mt-4 { margin-top: 40px; }
        .mt-2 { margin-top: 20px; }
        .page-break { page-break-after: always; }
        p { margin: 5px 0; }

        .resumo-item { margin-bottom: 15px; }
        .resumo-label { display: block; font-weight: bold; color: #333; margin-bottom: 2px; text-transform: uppercase; font-size: 11px; }
        .resumo-texto { display: block; margin-left: 0; line-height: 1.4; color: #444; }

        .signature-section { margin-top: 50px; text-align: center; }
        .signature-line { border-top: 1px solid #000; width: 300px; margin: 0 auto; padding-top: 5px; }
        .assinatura-bloco { margin-top: 55px; text-align: center; }
        .assinatura-nome { font-weight: bold; text-transform: uppercase; margin: 0; }
        .assinatura-cargo { margin: 0; font-size: 11px; }

        /* Checklist / meio de comprovação */
        .checklist-table { width: 100%; border-collapse: collapse; margin: 6px 0 4px; }
        .checklist-cell { width: 50%; padding: 3px 12px 3px 0; font-size: 11px; color: #444; vertical-align: top; }
        .checklist-cell-3 { width: 33%; padding: 3px 12px 3px 0; font-size: 11px; color: #444; vertical-align: top; }
        .checkbox-box {
            display: inline-block; width: 11px; height: 11px; border: 1.3px solid #062F43; border-radius: 2px;
            text-align: center; line-height: 10px; font-size: 9px; font-weight: bold; color: #fff; margin-right: 6px;
        }
        .checkbox-box.checked { background: #062F43; }
        .checklist-pendente { color: #999; }

        /* Fotografias / documentos anexados ao fato */
        .anexos-fotos { display: flex; flex-wrap: wrap; gap: 10px; margin: 6px 0 2px; }
        .anexo-foto { width: 140px; text-align: center; }
        .anexo-foto img { max-width: 140px; max-height: 110px; border: 1px solid #ccc; border-radius: 3px; display: block; margin: 0 auto 3px; }
        .anexo-foto-legenda { font-size: 9px; color: #666; word-break: break-all; }
        .anexos-lista { margin: 6px 0 0 18px; padding: 0; font-size: 11px; color: #444; }
        .anexos-lista li { margin-bottom: 2px; }

        /* Capa simples */
        .capa { text-align: center; padding-top: 30%; }
        .capa h1 { font-size: 20px; text-transform: uppercase; margin-bottom: 40px; }
        .capa .capa-info { text-align: left; max-width: 420px; margin: 0 auto; font-size: 13px; }
        .capa .capa-info p { margin: 8px 0; }
    </style>
</head>
<body>
    @php
        $timbre = $ocorrencia->prefeitura->timbre ?? '';
        $timbrePath = public_path($timbre);
        $base64Timbre = '';
        if ($timbre && file_exists($timbrePath)) {
            $type = pathinfo($timbrePath, PATHINFO_EXTENSION);
            $data = file_get_contents($timbrePath);
            $base64Timbre = 'data:image/'.$type.';base64,'.base64_encode($data);
        }

        $info = $ocorrencia->contrato_info;
    @endphp

    @if($base64Timbre)
        <img class="timbre-bg" src="{{ $base64Timbre }}" alt="Timbre">
    @endif

    {{-- ===================== CAPA SIMPLES ===================== --}}
    <div class="content-body capa">
        <h1 class="bold">Registro de Ocorrência Contratual</h1>
        <div class="capa-info">
            <p><span class="bold">Nº da Ocorrência:</span> {{ $ocorrencia->numero_ocorrencia }}</p>
            <p><span class="bold">Contrato nº:</span> {{ $info['numero_contrato'] }}</p>
            <p><span class="bold">Contratada:</span> {{ $info['razao_social'] }}</p>
            <p><span class="bold">Data da Ocorrência:</span> {{ $ocorrencia->data_ocorrencia?->format('d/m/Y') }}</p>
        </div>
    </div>
    <div class="page-break"></div>

    {{-- ===================== CORPO DO REGISTRO ===================== --}}
    @if($base64Timbre)
        <img class="timbre-bg" src="{{ $base64Timbre }}" alt="Timbre">
    @endif

    <div class="content-body">
        <div class="text-center" style="margin-bottom: 30px;">
            <h2 class="bold uppercase" style="margin-bottom: 5px;">Registro de Ocorrência Contratual</h2>
        </div>

        <p class="bold">Data da Ocorrência: <span>{{ $ocorrencia->data_ocorrencia?->format('d/m/Y') }}</span></p>
        <p class="bold">Número da Ocorrência: <span>{{ $ocorrencia->numero_ocorrencia }}</span></p>

        <div class="resumo-item">
            <span class="resumo-label">Contrato e Contratada:</span>
            <span class="resumo-texto">{{ $info['numero_contrato'] }} — {{ $info['razao_social'] }} ({{ $info['cnpj'] }})</span>
        </div>

        <div class="resumo-item">
            <span class="resumo-label">Objeto:</span>
            <span class="resumo-texto">{{ $info['objeto'] }}</span>
        </div>

        @if($ocorrencia->local)
            <div class="resumo-item">
                <span class="resumo-label">Local:</span>
                <span class="resumo-texto">{{ $ocorrencia->local }}</span>
            </div>
        @endif

        <div class="resumo-item">
            <span class="resumo-label">Descrição do Fato:</span>
            <span class="resumo-texto">{{ $ocorrencia->descricao_fato }}</span>
        </div>

        @php
            $anexosFato = $ocorrencia->anexos->where('categoria', 'fato');
            $imagensFato = $anexosFato->filter(fn ($a) => in_array(strtolower(pathinfo($a->caminho, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp']));
            $documentosFato = $anexosFato->diff($imagensFato);
        @endphp
        <div class="resumo-item">
            <span class="resumo-label">Fotografias / Documentos:</span>
            @if($anexosFato->isEmpty())
                <span class="resumo-texto checklist-pendente">Nenhum anexo.</span>
            @else
                @if($imagensFato->isNotEmpty())
                    <div class="anexos-fotos">
                        @foreach($imagensFato as $anexo)
                            @php
                                $caminhoAbsAnexo = public_path($anexo->caminho);
                                $anexoBase64 = '';
                                if (file_exists($caminhoAbsAnexo)) {
                                    $extAnexo = pathinfo($caminhoAbsAnexo, PATHINFO_EXTENSION);
                                    $anexoBase64 = 'data:image/'.$extAnexo.';base64,'.base64_encode(file_get_contents($caminhoAbsAnexo));
                                }
                            @endphp
                            @if($anexoBase64)
                                <div class="anexo-foto">
                                    <img src="{{ $anexoBase64 }}" alt="{{ $anexo->nome_original }}">
                                    <span class="anexo-foto-legenda">{{ $anexo->nome_original ?? basename($anexo->caminho) }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
                @if($documentosFato->isNotEmpty())
                    <ul class="anexos-lista">
                        @foreach($documentosFato as $anexo)
                            <li>{{ $anexo->nome_original ?? basename($anexo->caminho) }}</li>
                        @endforeach
                    </ul>
                @endif
            @endif
        </div>

        @if($ocorrencia->obrigacao_descumprida)
            <div class="resumo-item">
                <span class="resumo-label">Obrigação Descumprida:</span>
                <span class="resumo-texto">{{ $ocorrencia->obrigacao_descumprida }}</span>
            </div>
        @endif

        @if($ocorrencia->prazo_resposta)
            <div class="resumo-item">
                <span class="resumo-label">Prazo para Resposta / Solução:</span>
                <span class="resumo-texto">{{ $ocorrencia->prazo_resposta }}</span>
            </div>
        @endif

        @if(!empty($ocorrencia->tipo_comprovacao))
            <div class="resumo-item">
                <span class="resumo-label">Meio de Comprovação:</span>
                <table class="checklist-table">
                    @foreach(collect(\App\Models\Ocorrencia::TIPOS_COMPROVACAO)->chunk(2) as $par)
                        <tr>
                            @foreach($par as $chave => $rotulo)
                                @php $marcado = (bool) data_get($ocorrencia->tipo_comprovacao, $chave); @endphp
                                <td class="checklist-cell">
                                    <span class="checkbox-box {{ $marcado ? 'checked' : '' }}">{{ $marcado ? 'X' : '' }}</span>
                                    <span class="{{ $marcado ? '' : 'checklist-pendente' }}">
                                        {{ $chave === 'outros' && $ocorrencia->tipo_comprovacao_outro ? $rotulo.': '.$ocorrencia->tipo_comprovacao_outro : $rotulo }}
                                    </span>
                                </td>
                            @endforeach
                            @if($par->count() < 2)
                                <td class="checklist-cell"></td>
                            @endif
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif

        <div class="resumo-item">
            <span class="resumo-label">Situação:</span>
            <table class="checklist-table">
                <tr>
                    @foreach(\App\Enums\SituacaoOcorrenciaEnum::cases() as $opcaoSituacao)
                        @php $marcadoSituacao = $ocorrencia->situacao === $opcaoSituacao; @endphp
                        <td class="checklist-cell-3">
                            <span class="checkbox-box {{ $marcadoSituacao ? 'checked' : '' }}">{{ $marcadoSituacao ? 'X' : '' }}</span>
                            <span class="{{ $marcadoSituacao ? '' : 'checklist-pendente' }}">{{ $opcaoSituacao->getDisplayName() }}</span>
                        </td>
                    @endforeach
                </tr>
            </table>
        </div>

        {{-- Assinantes selecionados (assinatura física) --}}
        @if(!empty($ocorrencia->assinantes))
            @foreach($ocorrencia->assinantes as $assinante)
                <div class="assinatura-bloco">
                    <div class="signature-line"></div>
                    <p class="assinatura-nome">{{ $assinante['nome'] ?? '' }}</p>
                    @php
                        $cargo = trim($assinante['cargo'] ?? '') ?: 'Fiscal de Contrato';
                        $detalhe = array_filter([$cargo, $assinante['unidade'] ?? null]);
                    @endphp
                    @if(!empty($detalhe))
                        <p class="assinatura-cargo">{{ implode(' — ', $detalhe) }}</p>
                    @endif
                </div>
            @endforeach
        @else
            <div class="signature-section">
                <div class="signature-line"></div>
                <p class="bold uppercase" style="margin:0;">{{ $ocorrencia->user->name ?? '' }}</p>
                <p style="margin:0;">Fiscal do Contrato</p>
            </div>
        @endif
    </div>
</body>
</html>
