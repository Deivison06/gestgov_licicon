<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Capa do Processo - {{ $processo->numero_processo }}</title>
    <style type="text/css">
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

        @page {
            margin: 0;
            size: A4;
        }
        body {
            margin: 0;
            padding: 4cm 2cm;
            font-size: 11pt;
            font-family: 'Aptos', sans-serif;
            text-align: justify;
            text-justify: inter-word;
            line-height: 1;
        }
        /* TIMBRE PARA TODAS AS PÁGINAS, MENOS A PRIMEIRA */
        body.timbre {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('{{ public_path($prefeitura->timbre) }}');
            background-repeat: no-repeat;
            background-position: top left;
            background-size: cover;
            z-index: -1;
        }

        /* CLASSE PARA FORÇAR QUEBRA DE PÁGINA (ESSENCIAL PARA PDF) */
        .page-break {
            page-break-after: always;
        }

        .footer-signature {
            margin-top: 60px;
            text-align: right;
        }

        .signature-block {
            margin-top: 60px;
            text-align: center;
        }

        /* ---------------------------------- */
        /* ESTILOS - CONTEÚDO PRINCIPAL */
        /* ---------------------------------- */
        .capa-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }
    </style>
</head>

<body>
    <div>
        <!-- Imagem de fundo da capa -->
        <img src="{{  public_path($prefeitura->capa_edital)}}"
            class="capa-background" alt="Capa da Prefeitura">
        <!-- Conteúdo centralizado -->
        <div style="padding-top:150px">
            <p style="font-weight: bold; text-align: center; font-size: 14pt;">
                PROCESSO ADMINISTRATIVO <br>
                {{ $processo->numero_processo }} <br>
                {{ $processo->modalidade->getDisplayName() }} <br>
                {{ $processo->numero_procedimento }}
            </p>
            <p style="text-align: center; font-size: 14pt;">
                OBJETO:
            </p>
            <div style="text-align: justify;">{!! strip_tags($processo->objeto) !!}</div>
            <p>
                <span style="font-weight: bold;">VALOR TOTAL DA CONTRATAÇÃO</span> <br>
                R$ {{ $detalhe->valor_estimado }}<br>
                <span style="font-weight: bold;">DATA LIMITE PARA ENVIO DE PROPOSTAS</span> <br>
                DIA {{ $detalhe->data_hora_limite_edital->translatedFormat('d \d\e F \d\e Y') }}, às {{ $detalhe->data_hora_limite_edital->format('H:i') }}hs (Horário de Brasília)<br>
                <span style="font-weight: bold;">DATA DA SESSÃO PÚBLICA E FASE DE LANCES</span> <br>
                DIA {{ $detalhe->data_hora_fase_edital->translatedFormat('d \d\e F \d\e Y') }} às {{ $detalhe->data_hora_fase_edital->format('H:i') }}hs (Horário de Brasília)<br>
                <span style="font-weight: bold;">CRITÉRIO DE JULGAMENTO:</span> <br> MENOR PREÇO GLOBAL <br>
                <span style="font-weight: bold;">PORTAL UTILIZADO:</span> {{ $detalhe->portal }} <br>
                <span style="font-weight: bold;">ENDEREÇO DO PORTAL:</span> https://comprasbr.com.br/<br>
                <span style="font-weight: bold;">E-MAIL:</span> {{ $processo->prefeitura->email }}<br><br>
                <span style="font-weight: bold;">AGENTE DE CONTRATAÇÃO</span><br>
                {{ $detalhe->pregoeiro }}<br>
                <span style="font-weight: bold;">AUTORIDADE COMPETENTE</span><br>
                {{ $processo->prefeitura->autoridade_competente }}
            </p>

        </div>
    </div>
</body>

</html>
