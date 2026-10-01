<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Avisos - Processo {{ $processo->numero_processo ?? $processo->id }}</title>
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
