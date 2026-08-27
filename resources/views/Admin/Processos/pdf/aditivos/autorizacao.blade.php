<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autorização do Prefeito</title>
    <style>
        @font-face {
            font-family: 'Aptos';
            src: url('{{ public_path('storage/fonts/Aptos.ttf') }}') format('truetype');
            font-style: normal;
        }

        @font-face {
            font-family: 'AptosExtraBold';
            src: url('{{ public_path('storage/fonts/Aptos-ExtraBold.ttf') }}') format('truetype');
            font-style: normal;
        }

        @page { margin: 0; size: A4; }

        body {
            margin: 0; padding: 4cm 2cm;
            font-size: 12pt; font-family: 'Aptos', sans-serif;
            background-image: url('{{ public_path($prefeitura->timbre ?? '') }}');
            background-repeat: no-repeat; background-position: top left; background-size: cover;
            text-align: justify; text-justify: inter-word; line-height: 1.5;
        }

        .title {
            text-align: center; font-weight: bold; font-size: 14pt; margin-bottom: 50px;
            font-family: 'AptosExtraBold', sans-serif;
        }

        .clause-content { text-indent: 40px; }
        .signature-block { margin-top: 80px; text-align: center; }
    </style>
</head>
<body>
    @php
        \Carbon\Carbon::setLocale('pt_BR');
    @endphp

    <div class="title">
        AUTORIZAÇÃO DO PREFEITO MUNICIPAL
    </div>

    <div class="clause-content">
        Eu, <strong>{{ mb_strtoupper($prefeitura->autoridade_competente ?? 'Prefeito') }}</strong>, {{ $prefeitura->cargo_autoridade ?? 'Prefeito Municipal' }} de {{ $prefeitura->cidade ?? 'Cidade' }} - {{ $prefeitura->estado ?? 'UF' }}, no uso de minhas atribuições legais, <strong>AUTORIZO</strong> o(a) Agente de Contratação desta Prefeitura Municipal que proceda com o aditivo do Contrato n° {{ $contrato->numero_contrato ?? 'S/N' }}, conforme solicitado pela {{ $contrato->dados_contratante['orgao_responsavel'] ?? 'Secretaria' }}, vinculada a esta Administração.
    </div>

    <div style="margin-top: 60px; text-align: left; margin-left: 40px;">
        {{ $prefeitura->cidade ?? 'Cidade' }} – {{ $prefeitura->estado ?? 'UF' }}, {{ \Carbon\Carbon::parse($data_selecionada ?? now())->translatedFormat('d \d\e F \d\e Y') }}.
    </div>

    @if(isset($documentoSelecao) && is_array($documentoSelecao->assinantes) && count($documentoSelecao->assinantes) > 0)
        @foreach($documentoSelecao->assinantes as $assinante)
            <div class="signature-block" style="margin-top: 40px; text-align: center;">
                ___________________________________<br>
                <strong>{{ mb_strtoupper($assinante['responsavel']) }}</strong><br>
                {{ $assinante['unidade_nome'] }}
                @if(!empty($assinante['portaria']))
                <br>Portaria: {{ $assinante['portaria'] }}
                @endif
            </div>
        @endforeach
    @else
        <div class="signature-block">
            ___________________________________<br>
            <strong>{{ mb_strtoupper($prefeitura->autoridade_competente ?? 'Prefeito') }}</strong><br>
            {{ $prefeitura->cargo_autoridade ?? 'Prefeito Municipal' }}
        </div>
    @endif
</body>
</html>
