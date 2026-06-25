<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>DISPONIBILIDADE ORÇAMENTÁRIA - Processo {{ $processo->numero_processo ?? $processo->id }}</title>
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

    {{-- ====================================================================== --}}
    {{-- BLOCO 1: CAPA DO DOCUMENTO --}}
    {{-- ====================================================================== --}}
    <div id="cover-page">
        <img src="{{ public_path('icons/capa-documento.png') }}" alt="Martelo da Justiça" class="cover-image">
        <div class="cover-title">
            DISPONIBILIDADE <br>
            ORÇAMENTÁRIA
        </div>
    </div>

    {{-- QUEBRA DE PÁGINA --}}
    <div class="page-break"></div>

    {{-- ====================================================================== --}}
    {{-- BLOCO 2: DECLARAÇÃO DE COMPATIBILIDADE --}}
    {{-- ====================================================================== --}}
    <div id="declaracao-compatibilidade">
        <p style="text-align: center; font-weight: bold;">DECLARAÇÃO DE COMPATIBILIDADE DA PREVISÃO DE RECURSOS
            ORÇAMENTÁRIOS</p>
        <table style="border-collapse: collapse; width: 100%; text-align: left; border: 1px solid black; margin-top: 20px;">
            <thead>
                <tr>
                    <td colspan="2" style="border: 1px solid black; text-align: center; font-weight: bold; padding: 5px;">
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
        @if ($detalhe->tipo_srp == 'nao')
        <p style="text-indent: 30px; text-align: justify;">
            <span style="font-weight: bold;">DECLARO</span> para os fins de demonstração da compatibilidade da
            previsão de recursos orçamentários, com base no art. 72, IV da Lei 14.133/21, que a despesa da
            respectiva
            contratação estimada em R$ {{ $detalhe->valor_estimado }} possui previsão de saldo orçamentário e
            financeiro
            compatível com a Lei Orçamentária Anual (LOA) e é compatível com o Plano Plurianual (PPA) e com a Lei de
            Diretrizes Orçamentárias (LDO) vigentes.
        </p>

        <p style="text-indent: 30px; text-align: justify;">
            As despesas para atender a presente solicitação da demanda, encontram-se amparadas pelo seguinte
            detalhamento:
        </p>

        <table style="border-collapse: collapse; width: 100%; border: 1px solid black;">
            <tr>
                <!-- Coluna da esquerda -->
                <td style="vertical-align: top; padding: 10px;">
                    {!! str_replace('<p>', '<p style="text-indent:30px; text-align: justify;">', $detalhe->dotacao_orcamentaria) !!}
                </td>
            </tr>
        </table>
        @else
        <p style="text-indent: 30px; text-align: justify;">
            Declaro, para os devidos fins, que a presente licitação será realizada sob a forma de <span style="font-weight: bold;">Sistema de
                Registro de Preços (SRP)</span>, nos termos do art. 82 e seguintes da Lei nº 14.133/2021.<br>
            Por se tratar de procedimento que visa apenas ao registro formal de preços, <span style="font-weight: bold;">não há necessidade de
                indicação de dotação orçamentária nesta fase</span>, ficando a alocação de recursos vinculada e
            obrigatória somente no
            momento da contratação efetiva, mediante emissão da Nota de Empenho correspondente, conforme as demandas
            das
            Secretarias/Órgãos requisitantes.<br>
            Tal medida encontra respaldo legal e visa garantir o adequado planejamento das contratações, respeitando
            os
            princípios da eficiência, economicidade e responsabilidade fiscal.
        </p>
        @endif

        <p>Encaminhe-se ao DEMANDANTE para a elaboração do TERMO DE REFERÊNCIA</p>

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
