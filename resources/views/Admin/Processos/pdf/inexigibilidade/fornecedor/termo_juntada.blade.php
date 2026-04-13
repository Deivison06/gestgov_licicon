<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>DEMONSTRAÇÃO SERVIÇOS TÉCNICOS DE NATUREZA SINGULAR - Processo {{ $processo->numero_processo ?? $processo->id }}</title>
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
            TERMO DE JUNTADA
        </div>
    </div>

    {{-- QUEBRA DE PÁGINA --}}
    <div class="page-break"></div>

    <div>
        <hr>
        <h4 style="text-align: center;">TERMO DE JUNTADA</h4>
        <hr>
        @php
            use Carbon\Carbon;

            $data = Carbon::parse($dataSelecionada);

            $formatter = new \NumberFormatter('pt_BR', \NumberFormatter::SPELLOUT);

            $dia = $data->day;
            $mes = mb_strtoupper($data->translatedFormat('F')); // mês por extenso
            $anoExtenso = $formatter->format($data->year);
        @endphp

        Aos {{ $dia }} dias do mês de {{ $mes }} de {{ $anoExtenso }}, procedi a juntada aos autos
        do processo administrativo {{ $processo->numero_processo }}, as propostas de preço referente a
        {!! strip_tags($processo->objeto) !!} e a documentação das empresas. Com
        este fim e para constar, eu, @if($primeiroAssinante){{ $primeiroAssinante['responsavel'] }}@else XXXXXXXXXXXXXXXXXX @endif, {{ $primeiroAssinante ? 'Portaria nº ' . $primeiroAssinante['numero_portaria'] . ', da(o) ' . $primeiroAssinante['unidade_nome'] : '' }}, lavrei o presente termo
        que vai por mim assinado.

    </div>

</body>

</html>
