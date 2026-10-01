<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Projeto basico - Processo {{ $processo->numero_processo ?? $processo->id }}</title>
    <style type="text/css">
        @page {
            margin: 0;
            size: A4;
        }

        body {
            margin: 0;
            padding: 4cm 2cm;
            font-size: 10pt;
            font-family: Arial, Helvetica, sans-serif;
            /* Adiciona o timbre como background */
            background-image: url('{{ public_path($prefeitura->timbre) }}');
            background-repeat: no-repeat;
            background-position: top left;
            background-size: cover;

            text-align: justify;
            text-justify: inter-word;
            line-height: normal;
        }

        /* CLASSE PARA FORÇAR QUEBRA DE PÁGINA (ESSENCIAL PARA PDF) */
        .page-break {
            page-break-after: always;
        }

        /* ---------------------------------- */
        /* ESTILOS - CAPA DO DOCUMENTO (PÁGINA 0) */
        /* ---------------------------------- */
        #cover-page {
            /* Define a área de referência como a página inteira */
            height: 100vh;
            width: 100%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }

        .cover-image {
            /* Tamanho da imagem */
            width: 300px;
            height: 300px;
            margin-bottom: 30px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .cover-title {
            width: 60%;
            font-size: 16pt;
            font-weight: 900;
            border: 2px solid #000;
            display: inline-block;
            line-height: 0.9;
            padding: 8px 40px;
            font-family: Arial, Helvetica, sans-serif;
        }

        .footer-signature {
            margin-top: 24px;
            text-align: right;
        }

        .signature-block {
            margin-top: 24px;
            text-align: center;
            page-break-inside: avoid;
        }

    </style>
</head>

<body>

    {{-- ====================================================================== --}}
    {{-- BLOCO 1: CAPA DO DOCUMENTO --}}
    {{-- ====================================================================== --}}
    <div id="cover-page">
        <img src="{{ public_path('icons/capa-documento.png') }}" alt="Martelo da Justiça" class="cover-image">
        <div class="cover-title">
            PROJETO BÁSICO
        </div>
    </div>

    {{-- QUEBRA DE PÁGINA --}}
    <div class="page-break"></div>

    @php
    // Verifica se a variável $assinantes existe e tem itens
    $hasSelectedAssinantes = isset($assinantes) && count($assinantes) > 0;
    $primeiroAssinante = $hasSelectedAssinantes ? $assinantes[0] : null;
    @endphp

    <div>
        <p style="font-weight: bold; text-align: center;">SECRETARIA MUNICIPAL DE OBRAS</p>

        <p style="text-indent: 30px; text-align: justify;">
            Considerando a necessidade de {!! strip_tags($processo->objeto) !!}, segue em anexo PROJETO BÁSICO desenvolvido por essa secretaria.
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Encaminhe-se à {{ $detalhe->encaminhamento_doacao_orcamentaria }} para a DOTAÇÃO ORÇAMENTÁRIA.
        </p>

        @php
        // Verifica se a variável $assinantes existe e tem itens
        $hasSelectedAssinantes = isset($assinantes) && count($assinantes) > 0;
        @endphp

        @if ($hasSelectedAssinantes)
        {{-- Renderiza APENAS O PRIMEIRO assinante da lista --}}
        @php
        $primeiroAssinante = $assinantes[0]; // Pega o segundo item
        @endphp

        <div style="text-align: center;">
            <div class="signature-block" style="display: inline-block; margin-left: 40px; margin-right: 40px;">
                ___________________________________<br>
                <p style="line-height: 1.2;">
                    {{ $primeiroAssinante['responsavel'] }} <br>
                    <span>{{ $primeiroAssinante['unidade_nome'] }}</span>
                </p>
            </div>
        </div>
        @else
        {{-- Bloco Padrão (Fallback) --}}
        <div class="signature-block">
            ___________________________________<br>
            <p style="line-height: 1.2;">
                {{ $processo->prefeitura->autoridade_competente }} <br>
                <span style="color: red;">[Cargo/Título Padrão - A ser ajustado]</span>
            </p>
        </div>
        @endif
    </div>

</body>

</html>
