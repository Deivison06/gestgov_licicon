<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Avisos - Processo {{ $processo->numero_processo ?? $processo->id }}</title>
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
    // Verifica se a variável $assinantes existe e tem itens
    $hasSelectedAssinantes = isset($assinantes) && count($assinantes) > 0;

    // Define o primeiro assinante, se existir
    $primeiroAssinante = $hasSelectedAssinantes ? $assinantes[0] : null;

    // Extrai o nome do município removendo "Prefeitura Municipal de" ou "Prefeitura de"
    $municipio =  $processo->prefeitura->cidade;

    // Define a data formatada em português
    $dataFormatada = \Carbon\Carbon::parse($dataSelecionada)
    ->locale('pt_BR')
    ->translatedFormat('d \d\e F \d\e Y');
    @endphp
    {{-- ====================================================================== --}}
    {{-- BLOCO 1: CAPA DO DOCUMENTO --}}
    {{-- ====================================================================== --}}
    <div id="cover-page">
        <img src="{{ public_path('icons/capa-documento.png') }}" alt="Martelo da Justiça" class="cover-image">
        <div class="cover-title">
            AVISOS DE LICITAÇÃO
        </div>
    </div>

    {{-- QUEBRA DE PÁGINA --}}
    <div class="page-break"></div>

    <div>
        <p style="text-align: center; font-weight: bold;">
            PROCESSO ADMINISTRATIVO Nº {{ $processo->numero_processo }} <br>
            {{ $processo->modalidade->getDisplayName() }} Nº {{ $processo->numero_procedimento }} <br>
            TIPO: MENOR PREÇO {{ $processo->tipo_contratacao->getDisplayName() }}
        </p>
        <p style="text-align: justify; text-justify: inter-word; line-height: 1; margin: 0;">
            O Município de {{ $processo->prefeitura->cidade }}, através de seu Agente de Contratação / Pregoeiro e equipe de Apoio
            instituída pela Portaria nº {{ $primeiroAssinante['numero_portaria'] }}, de {{
                !empty($primeiroAssinante['data_portaria'])
                    ? \Carbon\Carbon::parse($primeiroAssinante['data_portaria'])->translatedFormat('d \d\e F \d\e Y')
                    : '____________________'
            }}, torna público, para conhecimento dos
            interessados que realizará procedimento licitatório na modalidade {{ $processo->modalidade->getDisplayName() }}, tipo {{ $processo->tipo_contratacao->getDisplayName() }}, em
            sessão pública, mediante as condições estabelecidas em Edital, conforme as normas Gerais da Lei Federal nº.
            14.133/2021, Decretos Municipais, Lei Complementar nº 123/06, alterada pela Lei Complementar nº 147/2014,
            de 07 de agosto de 2014 e demais normas regulamentares aplicáveis à espécie.
        </p>
        <p style="text-align: justify; text-justify: inter-word; line-height: 1; margin-top: 10;">
            Objeto:<br> {!! strip_tags($processo->objeto) !!}
        </p>
        <p style="text-align: justify; text-justify: inter-word; line-height: 1; margin-top: 10;">
            O EDITAL e maiores informações poderão ser solicitadas no Setor de Licitações na {{ $processo->prefeitura->endereco }}, no horário de 07:30h às 13:00h.
        </p>
        <p style="text-align: justify; text-justify: inter-word; line-height: 1; margin-top: 10;">
            ABERTURA DAS PROPOSTAS: dia {{ $detalhe->data_hora->translatedFormat('d \d\e F \d\e Y') }}, às {{ $detalhe->data_hora->format('H:i') }}hs ({{ $detalhe->data_hora->locale('pt_BR')->translatedFormat('l') }}),
            na plataforma {{ $detalhe->portal }}.
        </p>

    </div>

    {{-- Bloco de data e assinatura --}}
    <div class="footer-signature">
        {{ $municipio }}, {{ $dataFormatada }}
    </div>

    @if ($hasSelectedAssinantes)
    {{-- Renderiza apenas o primeiro assinante --}}
    <div style="margin-top:40px; text-align:center;">
        <div class="signature-block" style="display:inline-block; margin:0 40px;">
            ___________________________________<br>
            <p style="font-size:10pt; line-height:1.2; margin:0;">
                {{ $primeiroAssinante['responsavel'] }}<br>
                <span style="color:#4b5563;">{{ $primeiroAssinante['unidade_nome'] }}</span>
            </p>
        </div>
    </div>
    @else
    {{-- Fallback (sem assinantes selecionados) --}}
    <div class="signature-block" style="margin-top:40px; text-align:center;">
        ___________________________________<br>
        <p style="font-size:10pt; line-height:1.2; margin:0;">
            {{ $processo->prefeitura->autoridade_competente ?? '____________________' }}<br>
            <span style="color:red;">[Cargo/Título Padrão - A ser ajustado]</span>
        </p>
    </div>
    @endif
    {{-- QUEBRA DE PÁGINA --}}
    <div class="page-break"></div>

    <div>
        <p style="text-align: center; font-weight: bold;">
            PROCESSO ADMINISTRATIVO Nº {{ $processo->numero_processo }}<br>
            {{ $processo->modalidade->getDisplayName() }} Nº {{ $processo->numero_procedimento }}
        </p>
        <p style="text-align: center; font-weight: bold; font-size: 14pt;">
            RESUMO DE LICITAÇÃO
        </p>
        <p style="text-align: justify; text-justify: inter-word; line-height: 1; margin: 0;">
            O Município de {{ $processo->prefeitura->cidade }}, através de seu Agente de Contratação / Pregoeiro e equipe de Apoio instituída pela Portaria nº {{ $primeiroAssinante['numero_portaria'] }}, de {{ !empty($primeiroAssinante['data_portaria'])
            ? \Carbon\Carbon::parse($primeiroAssinante['data_portaria'])->translatedFormat('d \d\e F \d\e Y')
            : '____________________' }}, torna público, para conhecimento dos interessados que realizará procedimento licitatório na modalidade
             {{ $processo->modalidade->getDisplayName() }}, tipo {{ $processo->tipo_contratacao->getDisplayName() }}, objeto: {!! strip_tags($processo->objeto) !!},
             conforme as normas Gerais da Lei Federal nº. 14.133/2021, Decretos Municipais, Lei Complementar nº 123/06, alterada pela Lei Complementar nº 147/2014, de 07 de agosto de 2014 e
             demais normas regulamentares aplicáveis à espécie. Informações pelo E-mail: {{ $processo->prefeitura->email }}, e/ou na sede da Prefeitura no horário de 07:30hs às 13:00hs {{ $processo->prefeitura->endereco }}, {{ $detalhe->data_hora->translatedFormat('d \d\e F \d\e Y') }}.
        </p>

    </div>

</body>

</html>
