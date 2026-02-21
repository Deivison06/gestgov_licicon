<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>AUTORIZAÇÃO ABERTURA PROCEDIMENTO LICITATÓRIO - Processo {{ $processo->numero_processo ?? $processo->id }}</title>
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
        }

        strong {
            font-size: 18x;
            font-weight: bold;
            line-height: 1.5;
            display: block;
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
            AUTORIZAÇÃO ABERTURA PROCEDIMENTO LICITATÓRIO
        </div>
    </div>

    {{-- QUEBRA DE PÁGINA --}}
    <div class="page-break"></div>

    {{-- ====================================================================== --}}
    {{-- BLOCO 2: AUTORIZAÇÃO DE ABERTURA DE PROCEDIMENTO DE LICITAÇÃO --}}
    {{-- ====================================================================== --}}
    <div id="autorizacao-abertura-procedimento">
        <p style="text-align: center; font-weight: bold">AUTORIZAÇÃO DE ABERTURA DE PROCEDIMENTO DE LICITAÇÃO <br>
            PROCESSO ADMINISTRATIVO N° {{ $processo->numero_processo }}</p>

        <p>
            Ao(À) Ilmo(a). Sr(a).<br>
            <span>{{ $detalhe->agente_contratacao }}</span>
            <br>
            Agente de Contratação / Pregoeiro
            <br>
            <span>
                {{ $processo->prefeitura->nome }}
            </span>
        </p>

        <p>Assunto: Autorização para Abertura de {{ $processo->modalidade->getDisplayName() }} </p>

        <table
            style="border-collapse: collapse; width: 100%; text-align: left; border: 1px solid black;px;">
            <thead>
                <tr>
                    <td colspan="2"
                        style="border: 1px solid black; text-align: center; font-weight: bold; padding: 5px;">
                        RESUMO DOS DADOS DO PROCESSO
                    </td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="border: 1px solid black; padding: 5px; font-weight: bold; width: 40%;">
                        Nº PROCESSO ADMINISTRATIVO:
                    </td>
                    <td style="border: 1px solid black; padding: 5px;">
                        {{ $processo->numero_processo }}
                    </td>
                </tr>
                <tr>
                    <td style="border: 1px solid black; padding: 5px; font-weight: bold;">
                        Nº PROCESSO DE CONTRATAÇÃO:
                    </td>
                    <td style="border: 1px solid black; padding: 5px;">
                        {{ $processo->numero_procedimento }}
                    </td>
                </tr>
                <tr>
                    <td style="border: 1px solid black; padding: 5px; font-weight: bold;">
                        OBJETO
                    </td>
                    <td style="border: 1px solid black; padding: 5px;">
                        {!! strip_tags($processo->objeto) !!}
                    </td>
                </tr>
                <tr>
                    <td style="border: 1px solid black; padding: 5px; font-weight: bold;">
                        MODALIDADE:
                    </td>
                    <td style="border: 1px solid black; padding: 5px;">
                        {{ $processo->modalidade?->getDisplayName() }}
                    </td>
                </tr>
                <tr>
                    <td style="border: 1px solid black; padding: 5px; font-weight: bold;">
                        ÓRGÃO RESPONSÁVEL:
                    </td>
                    <td style="border: 1px solid black; padding: 5px;">
                        {{ $detalhe->unidade_setor }}
                    </td>
                </tr>
            </tbody>
        </table>

        <p style="text-indent: 30px">
            Senhor(a) Agente de Contratação / Pregoeiro
        </p>
        <p style="text-indent: 30px">
            Trata-se de demanda da {{ $detalhe->unidade_setor }}, para contratação de
            {!! strip_tags($processo->objeto) !!}.
        </p>
        <p style="text-indent: 30px">
            O valor estimado para pretendida contratação é de R$ {{ $detalhe->valor_estimado }},
            conforme Projeto Básico.
        </p>

        <p style="text-indent: 30px">
            O Setor de Contabilidade, através da DECLARAÇÃO DE COMPATIBILIDADE DA
            PREVISÃO DE RECURSOS ORÇAMENTÁRIOS, certifica a existência de dotação
            orçamentária para suportar a presente despesa, demonstrando a compatibilidade da
            previsão de recursos orçamentários com o compromisso a ser assumido.
        </p>
        <p style="text-indent: 30px">
            Por todo o exposto, considerando que a instrução do presente processo atende ao
            disposto na Lei nº 14.133, de 2021, aprovo os atos praticados e autorizo que seja
            promovida a abertura de Procedimento de Licitação, na modalidade Concorrência, em
            sua forma Eletrônica, nos termos da Lei nº 14.133, de 2021.
        </p>
        <p style="text-indent: 30px">
            Por fim, declaro, para os efeitos do art. 16, II da Lei Complementar nº 101, de 04 de
            maio de 2000 (Lei de Responsabilidade Fiscal), que a despesa da pretendida
            contratação, possui adequação orçamentária e financeira com a Lei Orçamentária
            Anual (LOA) e compatibilidade com o Plano Plurianual (PPA) e com a Lei de Diretrizes
            Orçamentária (LDO).
        </p>

        <p>
            CLASSIFICAÇÃO DO OBJETO: OBRAS E SERVIÇOS DE ENGENHARIA
        </p>
        <p>
            JUSTIFICATIVA DA CONTRATAÇÃO:
            {!! str_replace('<p>', '<p style="text-align: justify;">', $detalhe->justificativa) !!}
        </p>

        <p>MODALIDADE: {{ $processo->modalidade->getDisplayName() }}</p>
        <p>MODO DE DISPUTA: ABERTO</p>
        <p>TRATAMENTO DIFERENCIA A MEs e EPPs</p>
        <p>
            {!! $detalhe->tratamento_diferenciado_MEs_eEPPs !!}
        </p>
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
