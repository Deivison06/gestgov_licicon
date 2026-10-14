<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>EDITAL - Processo {{ $processo->numero_processo ?? $processo->id }}</title>
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

        .footer-signature {
            margin-top: 24px;
            text-align: right;
        }

        .signature-block {
            margin-top: 24px;
            text-align: center;
            page-break-inside: avoid;
        }

    </style>
</head>

<body>
    @include('Admin.Processos.pdf.capa_edital')

    {{-- QUEBRA DE PÁGINA --}}
    <div class="page-break"></div>

    @include("Admin.Processos.pdf.pregao_eletronico.servicos_item.corpo_edital")

</body>

</html>
