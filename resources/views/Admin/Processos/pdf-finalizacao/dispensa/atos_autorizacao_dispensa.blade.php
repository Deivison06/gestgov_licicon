<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>ATO DE AUTORIZAÇÃO DE DISPENSA DE LICITAÇÃO - Processo {{ $processo->numero_processo ?? $processo->id }}</title>
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
            text-align: center;
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
        
    </style>
</head>

<body>

    @php
        // Dados compartilhados pelo Termo de Adjudicação, Termo de Homologação e Ato de Autorização.
        $ehCompras = $processo->tipo_procedimento === \App\Enums\TipoProcedimentoEnum::COMPRAS;
        $textoEscolha = $ehCompras ? 'do fornecedor' : 'do prestador de serviços';
        $textoPreco = 'menor preço global';
        $exibirPrazoExecucao = false;

        $primeiroVencedor = $vencedores->first();
        $contratadoTexto = $primeiroVencedor
            ? "{$primeiroVencedor->razao_social} - {$primeiroVencedor->cnpj}"
            : '';
        $valorTotalTexto = 'R$ ' . number_format($vencedores->sum(fn ($vencedor) => $vencedor->lotes->sum('vl_total')), 2, ',', '.');
        $fundamentoLegal = $processo->detalhe?->is_oriundo_fracassado
            ? 'Art. 75, Inciso III, alínea "a", da Lei Federal nº 14.133/2021'
            : 'Art. 75, Inciso II da Lei Federal 14.133/21';

        $vigencia = is_array($processo->detalhe->prazo_vigencia ?? null)
            ? $processo->detalhe->prazo_vigencia
            : ['12_meses'];

        $outro_vigencia = $processo->detalhe->prazo_vigencia_outro ?? '________________.';

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

    {{-- Gerados junto com o Ato, nesta ordem: Adjudicação → Homologação → Ato de Autorização --}}
    @include('Admin.Processos.pdf-finalizacao.dispensa.partials.termo_adjudicacao_dispensa')
    @include('Admin.Processos.pdf-finalizacao.dispensa.partials.termo_homologacao_dispensa')

    {{-- ====================================================================== --}}
    {{-- BLOCO 1: CAPA DO DOCUMENTO --}}
    {{-- ====================================================================== --}}
    <div id="cover-page">
        <img src="{{ public_path('icons/capa-documento.png') }}" alt="Martelo da Justiça" class="cover-image">
        <div class="cover-title">
            ATO DE AUTORIZAÇÃO DE DISPENSA DE LICITAÇÃO
        </div>
    </div>

    {{-- QUEBRA DE PÁGINA --}}
    <div class="page-break"></div>

    {{-- ====================================================================== --}}
    {{-- BLOCO 2: ATO DE AUTORIZAÇÃO --}}
    {{-- ====================================================================== --}}

    <div>
        <h4 style="text-align: center;">
            ATO DE AUTORIZAÇÃO DE DISPENSA DE LICITAÇÃO <br>
            PROCESSO ADMINISTRATIVO N° {{ $processo->numero_processo }} <br>
            DISPENSA DE LICITAÇÃO N° {{ $processo->numero_procedimento }} <br>
        </h4>

       <p style="text-align: justify;">
            CONSIDERANDO os elementos contidos no presente processo de dispensa de licitação, que foi
            devidamente justificado, tanto pela razão da escolha do prestador de serviços, quanto pela justificativa dos
            preços, vez que a empresa apresentou o menor preço global;
        </p>
        <p style="text-align: justify;">

            CONSIDERANDO que o processo foi instruído com os documentos e requisitos que comprovam que o
            contratado possui habilitação e qualificação mínima para celebrar o contrato, conforme preconizado no
            artigo 72 da Lei Federal 14.133/2021;
        </p>
        <p style="text-align: justify;">
            CONSIDERANDO que o PARECER JURÍDICO atesta que foram cumpridas as exigências legais e os
            requisitos mínimos para a contratação;
        </p>
        <p style="text-align: justify;">
            No uso das atribuições que me foram conferidas, em especial ao disposto no artigo 72, VIII da Lei Federal
            14.133/2021, AUTORIZO A DISPENSA DE LICITAÇÃO {{ $processo->numero_procedimento }}, nos termos descritos abaixo:
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
                    {{ $contratadoTexto }}
                </td>
            </tr>

            <tr>
                <td style="border:1px solid #000; padding:6px; font-weight:bold;">
                    PRAZO DE VIGÊNCIA
                </td>
                <td style="border:1px solid #000; padding:6px;">
                    {{ $textoVigencia }}
                </td>
            </tr>

            <tr>
                <td style="border:1px solid #000; padding:6px; font-weight:bold;">
                    VALOR TOTAL
                </td>
                <td style="border:1px solid #000; padding:6px;">
                    {{ $valorTotalTexto }}
                </td>
            </tr>

            <tr>
                <td style="border:1px solid #000; padding:6px; font-weight:bold;">
                    FUNDAMENTO LEGAL
                </td>
                <td style="border:1px solid #000; padding:6px;">
                    {{ $fundamentoLegal }}
                </td>
            </tr>
        </table>

        @include('Admin.Processos.pdf-finalizacao.dispensa.partials.assinatura_autoridade')
    </div>
</body>

</html>
