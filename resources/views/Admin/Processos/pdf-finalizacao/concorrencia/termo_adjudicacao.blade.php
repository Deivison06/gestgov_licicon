<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>TERMO DE ADJUDICAÇÃO - Processo {{ $processo->numero_processo ?? $processo->id }}</title>
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
            TERMO DE ADJUDICAÇÃO
        </div>
    </div>

    {{-- QUEBRA DE PÁGINA --}}
    <div class="page-break"></div>

    {{-- ====================================================================== --}}
    {{-- BLOCO 2: TERMO DE RECEBIMENTO --}}
    {{-- ====================================================================== --}}
    <div>
        <p style="font-weight: bold;">
            PROCESSO ADMINISTRATIVO Nº {{ $processo->numero_processo }} <br>
            PREGÃO ELETRÔNICO Nº. {{ $processo->numero_procedimento }}
        </p>
        <div style="text-align: center;">TERMO DE ADJUDICAÇÃO</div>
        <table style="width:100%; table-layout:fixed; border-collapse:collapse;">
            <tr>
                <td style="width:40%; padding:8px; vertical-align:top; word-wrap:break-word; white-space:normal;">
                <!-- Conteúdo da primeira célula -->
                </td>
                <td style="width:60%; padding:8px; vertical-align:top; word-wrap:break-word; white-space:normal;">
                    OBJETO: <span style="font-size: 12px;">{!! strip_tags($processo->objeto) !!}</span>, conforme especificações técnicas do Edital, Termo de Referência e Anexos.
                </td>
            </tr>
        </table>

        <p style="text-indent: 30px; text-align: justify;">
            O Prefeito Municipal da Prefeitura de {{ $processo->prefeitura->cidade }}, no uso de suas
            atribuições legais, e considerando o Resultado do Processo Administrativo nº {{ $processo->numero_processo }},
            {{ $processo->modalidade->getDisplayName() }} nº {{ $processo->numero_procedimento }}, depois de transcorridas todas as fases do certame, solucionadas
            todas as dúvidas e questionamentos inerentes, conforme apurado no processo de licitação, e
            depois de obedecidas as normas e regulamentações dispostas na Lei Federal Lei Federal nº
            14.133/21, com alterações posteriores, Lei Complementar nº 123/06, alterada pela Lei
            Complementar nº 147/2014, de 07 de agosto de 2014, e demais normas regulamentares aplicáveis
            à espécie e tendo respeitado todos os Princípios Administrativos, resolve ADJUDICAR o certame
            nos seguintes termos:
        </p>
        <table style="width:100%; border-collapse:collapse; font-size:10px; margin-bottom:20px;" border="1">
            <!-- Razão Social -->
            <tr>
                <td style="padding:6px; font-weight:bold; background-color:#f8f8f8; width:60%;">
                    RAZÃO SOCIAL
                </td>
                <td style="padding:6px;">
                    {{ $processo->finalizacao->razao_social }}
                </td>
            </tr>

            <!-- CNPJ -->
            <tr>
                <td style="padding:6px; font-weight:bold; background-color:#f8f8f8;">
                    CNPJ
                </td>
                <td style="padding:6px;">
                    {{ $processo->finalizacao->cnpj }}
                    {{-- {{ $vencedor->cnpj_formatado
                        ?? preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $vencedor->cnpj) }} --}}
                </td>
            </tr>

            <!-- Descrição -->
            <tr>
                <td style="padding:6px; font-weight:bold; background-color:#f8f8f8;">
                    DESCRIÇÃO
                </td>
                <td style="padding:6px; font-weight:bold; background-color:#f8f8f8;">
                    VALOR TOTAL
                </td>

            </tr>

            <!-- Valor Total -->
            <tr>
                <td style="padding:6px;">
                    {!! strip_tags($processo->objeto) !!}
                </td>
                <td style="padding:6px; font-weight:bold; color:#d00;">
                    {{ $processo->finalizacao->valor_total }}
                </td>
            </tr>

        </table>

        <p style="text-indent: 30px; text-align: justify;">
            Os autos do processo licitatório estão com vistas franqueadas aos interessados a partir desta publicação.
        </p>

        {{-- Bloco de data e assinatura --}}
        <div class="footer-signature">
            {{ $processo->prefeitura->cidade }},
            {{ \Carbon\Carbon::parse($dataSelecionada)->translatedFormat('d \d\e F \d\e Y') }}
        </div>

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
        {{-- QUEBRA DE PÁGINA --}}
        <div class="page-break"></div>
    </div>
    <div>
        <p>
            Ao Exmos. Sr. <br>
            Controlador(a) <br>
            Prefeitura de {{ $processo->prefeitura->cidade }}
        </p>
        <p>
            Assunto: Emissão de Parecer da Controladoria Interna.
        </p>
        <p style="text-indent: 30px;">
            Senhor Controlador,
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Visando o controle de legalidade dos atos da licitação, solicitamos o parecer do controle interno
            referente a {!! strip_tags($processo->objeto) !!},
            através do Processo Administrativo nº {{ $processo->numero_processo }}, Modalidade: {{ $processo->modalidade->getDisplayName() }} nº {{ $processo->numero_procedimento }}.
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Devido à complexidade Jurídica no sentido da contratação com base na Lei Federal nº.
            14.133/2021, Lei Complementar nº 123/06, que institui o Estatuto Nacional da Microempresa e da
            Empresa de Pequeno Porte, alterada pela Lei Complementar nº 147/2014, de 07 de agosto de
            2014 e demais normas regulamentares aplicáveis à espécie, indagamos esta Controladoria Interna
            para consulta sobre a legalidade do Procedimento
        </p>

        {{-- Bloco de data e assinatura --}}
        <div style="margin-top: 60px; text-align: right;">
            {{ $processo->prefeitura->cidade }},
            {{ \Carbon\Carbon::parse($dataSelecionada)->translatedFormat('d \d\e F \d\e Y') }}
        </div>

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
