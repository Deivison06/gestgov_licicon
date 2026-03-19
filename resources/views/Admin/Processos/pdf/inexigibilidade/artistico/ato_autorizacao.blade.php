<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>ATO DE AUTORIZAÇÃO DE INEXIGIBILIDADE DE LICITAÇÃO {{ $processo->numero_processo ?? $processo->id }}</title>
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
            /* Adiciona o timbre como background */
            background-image: url('{{ public_path($prefeitura->timbre) }}');
            background-repeat: no-repeat;
            background-position: top left;
            background-size: cover;

            text-align: justify;
            text-justify: inter-word;
            line-height: 1;
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
            font-size: 18pt;
            font-weight: 900;
            border: 2px solid #000;
            display: inline-block;
            line-height: 0.9;
            padding: 10px 50px;
            font-family: 'AptosExtraBold', sans-serif;
        }

        .footer-signature {
            margin-top: 60px;
            text-align: right;
        }

        .signature-block {
            margin-top: 60px;
            text-align: center;
        }

        /* Estilos opcionais para simular as linhas da imagem */
        .line {
            border-top: 2px solid black;
            margin: 10px 0;
            /* Espaçamento entre as linhas */
        }

        .content {
            /* Centraliza o texto como na imagem */
            margin: 40px 0;
            /* Espaçamento acima e abaixo do conteúdo principal */
        }

        strong {
            line-height: 1.5;
            /* Melhora a leitura do texto em várias linhas */
            display: block;
            /* Garante que o strong ocupe a largura total */
        }

        /* ---------------------------------- */
        /* ESTILOS - CONTEÚDO PRINCIPAL */
        /* ---------------------------------- */
        .container {
            position: relative;
            width: 100%;
            height: 100%;
        }

        .conteudo-all {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100%;
            transform: translate(-50%, -50%);
            text-align: left;
        }

        .title {
            margin-left: -85px;
            font-weight: bold;
            font-size: 20pt;
            background: #bebebe;
            border: 1px solid #7a7a7a;
            padding: 5px 50px;
            display: inline-block;
            margin-bottom: 20px;
            text-align: center;
        }

        .section {
            margin-bottom: 15px;
        }

        .justify {
            margin-top: 20px;
            text-indent: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td.icon {
            width: 80px;
            text-align: center;
            vertical-align: middle;
        }

        td.content {
            vertical-align: middle;
            padding-left: 10px;
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
        ATO DE AUTORIZAÇÃO DE INEXIGIBILIDADE DE LICITAÇÃO
    </div>
</div>
{{-- QUEBRA DE PÁGINA --}}
<div class="page-break"></div>

<div>
    <h4 style="text-align: center">
        ATO DE AUTORIZAÇÃO DE INEXIGIBILIDADE DE LICITAÇÃO <br>
        PROCESSO ADMINISTRATIVO N° {{ $processo->numero_processo }}<br>
        INEXIGIBILIDADE DE LICITAÇÃO N° {{ $processo->numero_procedimento }}
    </h4>

    <p style="text-align: justify">
        CONSIDERANDO os elementos contidos no presente processo de inexigibilidade de licitação, que
        foi devidamente justificado, tanto pela razão da escolha do prestador de serviços, quanto pela
        justificativa dos preços, vez que a empresa/profissional apresentou o menor preço global; <br><br>
        CONSIDERANDO que o processo foi instruído com os documentos e requisitos que comprovam
        que o contratado possui habilitação e qualificação mínima para celebrar o contrato, conforme
        preconizado no artigo 72 da Lei Federal 14.133/2021; <br><br>
        CONSIDERANDO que o PARECER TÉCNICO da Comissão de Contratação que prevê que a
        INEXIGIBILIDADE DE LICITAÇÃO está em conformidade ao disposto no artigo 72 c/c 74 da Lei
        Federal 14.133/2021;<br><br>
        CONSIDERANDO que o PARECER JURÍDICO atesta que foram cumpridas as exigências legais e
        os requisitos mínimos para a contratação;<br><br>
        No uso das atribuições que me foram conferidas, em especial ao disposto no artigo 72, VIII da Lei
        Federal 14.133/2021, AUTORIZO A INEXIGIBILIDADE DE LICITAÇÃO N° {{ $processo->numero_procedimento }}, nos termos
        descritos abaixo:
    </p>

    <table style="width:100%; border-collapse:collapse; font-size:10px;">
        <tr>
            <td style="border:1px solid #000; padding:6px; width:30%; font-weight:bold;">
                OBJETO A SER CONTRATADO
            </td>
            <td style="border:1px solid #000; padding:6px; width:70%;">
                {!! strip_tags($processo->objeto) !!}
            </td>
        </tr>
        <tr>
            <td style="border:1px solid #000; padding:6px; font-weight:bold;">
                CONTRATADO
            </td>
            <td style="border:1px solid #000; padding:6px;">
                {{ $processo->detalhe->razao_social }}
            </td>
        </tr>
        <tr>
            <td style="border:1px solid #000; padding:6px; font-weight:bold;">
                PRAZO DE VIGÊNCIA
            </td>
            <td style="border:1px solid #000; padding:6px;">
                @php
                    $vigencia = is_array($processo->detalhe->prazo_vigencia ?? null)
                        ? $processo->detalhe->prazo_vigencia
                        : ['12_meses'];

                    $outro_vigencia = $processo->detalhe->prazo_vigencia_outro ?? '________________.';

                    $objeto_continuado = strtolower($processo->detalhe->objeto_continuado ?? 'nao');

                    // Texto para preencher automaticamente
                    if (in_array('exercicio_financeiro', $vigencia)) {
                        $textoVigencia = "até 31/12 do exercício financeiro da contratação";
                    } elseif (in_array('12_meses', $vigencia)) {
                        $textoVigencia = "12 meses";
                    } elseif (in_array('outro', $vigencia)) {
                        $textoVigencia = $outro_vigencia;
                    } else {
                        $textoVigencia = "________________";
                    }
                @endphp
                {{ $textoVigencia }}
            </td>
        </tr>
        <tr>
            <td style="border:1px solid #000; padding:6px; font-weight:bold;">
                VALOR TOTAL
            </td>
            <td style="border:1px solid #000; padding:6px;">
                R$ {{ number_format($processo->valor_total, 2, ',', '.') }}
            </td>
        </tr>
        <tr>
            <td style="border:1px solid #000; padding:6px; font-weight:bold;">
                FUNDAMENTO LEGAL
            </td>
            <td style="border:1px solid #000; padding:6px;">
                    Artigo 72 c/c 74, inc. III, alínea “c”, da Lei 14.133/21 – Serviços Técnicos Especializados
            </td>

        </tr>
    </table>

    {{-- Bloco de data e assinatura --}}
    <div class="footer-signature">
        {{ $processo->prefeitura->cidade }},
        {{ \Carbon\Carbon::parse($dataSelecionada)->translatedFormat('d \d\e F \d\e Y') }}
    </div>

    @php
        // Verifica se a variável $assinantes existe e tem itens
        $hasSelectedAssinantes = isset($assinantes) && count($assinantes) > 0;
    @endphp

    @if ($hasSelectedAssinantes)
        {{-- Renderiza APENAS O PRIMEIRO assinante da lista --}}
        @php
            $primeiroAssinante = $assinantes[0]; // Pega o segundo item
        @endphp

        <div style="margin-top: 40px; text-align: center;">
            <div class="signature-block" style="display: inline-block; margin: 0 40px;">
                ___________________________________<br>
                <p style="line-height: 1.2;">
                    {{ $primeiroAssinante['responsavel'] }} <br>
                    <span>{{ $primeiroAssinante['unidade_nome'] }}</span>
                </p>
            </div>
        </div>
    @else
        {{-- Bloco Padrão (Fallback) --}}
        <div class="signature-block" style="margin-top: 40px; text-align: center;">
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
