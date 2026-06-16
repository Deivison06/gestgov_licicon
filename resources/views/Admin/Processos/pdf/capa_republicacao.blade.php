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
<div>
    <h4 style="text-align: center;">
        AVISO DE REPUBLICAÇÃO DE EDITAL
    </h4>
    <p style="text-align: justify; text-indent: 30px">
        A Prefeitura Municipal de
        <span style="font-weight: bold">
            {{ $processo->prefeitura->cidade }}
        </span>, por meio de
        <span style="font-weight: bold">seu Agente de Contratação</span>,
        torna público, para conhecimento dos interessados, a
        <span style="font-weight: bold">
            REPUBLICAÇÃO do Edital referente ao
            @if ($processo->modalidade !== \App\Enums\ModalidadeEnum::DISPENSA)
                {{ $processo->modalidade->getDisplayName() }}
            @else
                DISPENSA DE LICITAÇÃO
            @endif
            nº {{ $processo->numero_procedimento }},
        </span>
        cujo objeto é {!! strip_tags($processo->objeto) !!}.
        Em virtude das alterações promovidas, fica reaberto o prazo para apresentação
        das propostas/documentos, nos termos do edital republicado.
        @php
    $dataFormatada = \Carbon\Carbon::parse($dataSelecionada)->translatedFormat('d \d\e F \d\e Y');
@endphp
{{ $processo->prefeitura->cidade }}, {{ $dataFormatada }}.
    </p>


    <h6 style="text-align: center">{{ $detalhe->agente_contratacao ?? $detalhe->pregoeiro ?? '____________________' }}</h6>

</div>

</body>

</html>
