<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Atesto de Correção - Ocorrência {{ $ocorrencia->numero_ocorrencia }}</title>
    <style>
        @page { margin: 0; size: A4; }
        body {
            margin: 0;
            padding: 3.5cm 1.5cm 3.5cm 1.5cm;
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
            line-height: 1.6;
            text-align: justify;
        }
        .timbre-bg { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1000; }
        .content-body { margin: 0 4rem; }
        .text-center { text-align: center; }
        .uppercase { text-transform: uppercase; }
        .bold { font-weight: bold; }
        .mt-4 { margin-top: 40px; }
        .mt-2 { margin-top: 20px; }
        p { margin: 8px 0; }

        .cabecalho-dados p { margin: 3px 0; }
        h3.secao { font-size: 13px; margin-top: 30px; margin-bottom: 10px; }

        /* Elementos comprobatórios anexados à correção */
        .anexos-fotos { display: flex; flex-wrap: wrap; gap: 10px; margin: 8px 0 4px; }
        .anexo-foto { width: 140px; text-align: center; }
        .anexo-foto img { max-width: 140px; max-height: 110px; border: 1px solid #ccc; border-radius: 3px; display: block; margin: 0 auto 3px; }
        .anexo-foto-legenda { font-size: 9px; color: #666; word-break: break-all; }
        .anexos-lista { margin: 6px 0 0 18px; padding: 0; font-size: 11px; color: #444; }
        .anexos-lista li { margin-bottom: 2px; }

        .signature-section { margin-top: 60px; text-align: center; }
        .signature-line { border-top: 1px solid #000; width: 300px; margin: 0 auto; padding-top: 5px; }
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
        $dataExtenso = now()->translatedFormat('d \d\e F \d\e Y');
    @endphp

    @if($base64Timbre)
        <img class="timbre-bg" src="{{ $base64Timbre }}" alt="Timbre">
    @endif

    <div class="content-body">
        <div class="text-center" style="margin-bottom: 30px;">
            <h2 class="bold uppercase" style="margin-bottom: 5px;">Atesto de Correção</h2>
        </div>

        <div class="cabecalho-dados">
            <p><span class="bold">Contrato nº:</span> {{ $info['numero_contrato'] }}</p>
            <p><span class="bold">Contratada:</span> {{ $info['razao_social'] }}</p>
            <p><span class="bold">CNPJ:</span> {{ $info['cnpj'] }}</p>
            <p><span class="bold">Objeto:</span> {{ $info['objeto'] }}</p>
            <p><span class="bold">Fiscal do Contrato:</span> {{ $ocorrencia->user->name ?? '' }}</p>
        </div>

        <h3 class="bold uppercase secao">1. Da Irregularidade Identificada</h3>
        <p>Durante o acompanhamento e fiscalização da execução do Contrato nº <b>{{ $info['numero_contrato'] }}</b>, foi identificada a seguinte ocorrência/irregularidade:</p>
        <p><span class="bold">Descrição da ocorrência:</span> {{ $ocorrencia->descricao_fato }}</p>
        <p><span class="bold">Data da identificação:</span> {{ $ocorrencia->data_ocorrencia?->format('d/m/Y') }}.</p>

        <p>Em razão da ocorrência, a empresa contratada foi formalmente comunicada/notificada por meio da Notificação
            @if($ocorrencia->notificacao_numero)
                nº <b>{{ $ocorrencia->notificacao_numero }}</b>
            @endif
            expedida em <b>{{ $ocorrencia->notificacao_expedida_em?->format('d/m/Y') ?? $ocorrencia->data_ocorrencia?->format('d/m/Y') }}</b>,
            para que adotasse as providências necessárias à regularização.
        </p>

        <h3 class="bold uppercase secao">2. Da Correção Realizada</h3>
        <p>Após a notificação, a empresa contratada adotou as seguintes providências:</p>
        <p>{{ $ocorrencia->correcao_descricao }}</p>
        <p><span class="bold">Data da correção:</span> {{ $ocorrencia->correcao_data?->format('d/m/Y') }}.</p>
        @if($ocorrencia->correcao_elementos_comprobatorios)
            <p>Foram apresentados e/ou verificados os seguintes elementos comprobatórios da correção:</p>
            <p>{{ $ocorrencia->correcao_elementos_comprobatorios }}</p>
        @endif

        @php
            $anexosCorrecao = $ocorrencia->anexosCorrecao()->get();
            $imagensCorrecao = $anexosCorrecao->filter(fn ($a) => in_array(strtolower(pathinfo($a->caminho, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp']));
            $documentosCorrecao = $anexosCorrecao->diff($imagensCorrecao);
        @endphp
        @if($anexosCorrecao->isNotEmpty())
            <p class="bold" style="margin-top: 15px;">Documentos/imagens anexados como comprovação:</p>
            @if($imagensCorrecao->isNotEmpty())
                <div class="anexos-fotos">
                    @foreach($imagensCorrecao as $anexo)
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
            @if($documentosCorrecao->isNotEmpty())
                <ul class="anexos-lista">
                    @foreach($documentosCorrecao as $anexo)
                        <li>{{ $anexo->nome_original ?? basename($anexo->caminho) }}</li>
                    @endforeach
                </ul>
            @endif
        @endif

        <h3 class="bold uppercase secao">3. Do Atesto do Fiscal</h3>
        <p>Após nova verificação da execução contratual, <b>ATESTO</b> que a irregularidade apontada
            @if($ocorrencia->notificacao_numero)
                na Notificação nº <b>{{ $ocorrencia->notificacao_numero }}</b>
            @endif
            foi devidamente corrigida pela empresa contratada, encontrando-se, quanto ao fato objeto desta ocorrência, a execução regularizada.
        </p>

        <p>Dessa forma, considero <b>SANADA A IRREGULARIDADE</b>, para fins de registro e acompanhamento da execução contratual.</p>

        <p>O presente atesto refere-se exclusivamente à correção da ocorrência acima identificada e <b>não afasta o registro do fato anteriormente constatado</b>, nem eventuais providências administrativas decorrentes de reincidência ou de outras obrigações contratuais.</p>

        <p class="mt-4">{{ $ocorrencia->prefeitura->cidade ?? 'Local' }}, {{ $dataExtenso }}</p>

        {{-- Assinantes selecionados (assinatura física) --}}
        @if(!empty($ocorrencia->assinantes))
            @foreach($ocorrencia->assinantes as $assinante)
                <div class="signature-section">
                    <div class="signature-line"></div>
                    <p class="bold uppercase" style="margin:0;">{{ $assinante['nome'] ?? '' }}</p>
                    @php
                        $cargo = trim($assinante['cargo'] ?? '') ?: 'Fiscal de Contrato';
                        $detalhe = array_filter([$cargo, $assinante['unidade'] ?? null]);
                    @endphp
                    @if(!empty($detalhe))
                        <p style="margin:0; font-size: 11px;">{{ implode(' — ', $detalhe) }}</p>
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
