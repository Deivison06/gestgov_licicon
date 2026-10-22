<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Capa do Aditivo</title>
    <style>
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

        @page { margin: 0; size: A4; }

        body {
            margin: 0; padding: 6cm 2cm;
            font-size: 14pt; font-family: 'Aptos', sans-serif;
            background-image: url('{{ public_path($prefeitura->timbre ?? '') }}');
            background-repeat: no-repeat; background-position: top left; background-size: cover;
            text-align: center; line-height: 1.3;
        }

        .title {
            font-weight: bold; font-size: 18pt; margin-top: 20px; margin-bottom: 40px;
            font-family: 'AptosExtraBold', sans-serif; text-transform: uppercase;
        }

        table {
            width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14pt;
        }

        td {
            border: 1px solid #000; padding: 8px 10px; text-align: left; vertical-align: middle;
        }

        .td-label {
            width: 30%; font-weight: bold; text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="title">
        {{ $vm->tituloDocumento() }}
    </div>

    <table>
        <tr>
            <td class="td-label">ORGÃO/SETOR</td>
            <td>{{ mb_strtoupper($contrato->dados_contratante['orgao_responsavel'] ?? $prefeitura->nome) }}</td>
        </tr>
        <tr>
            <td class="td-label">OBJETIVO</td>
            <td style="font-weight: bold; text-transform: uppercase;">{{ $vm->tituloTipoAlteracao() }} DO CONTRATO Nº {{ $contrato->numero_contrato ?? 'S/N' }}</td>
        </tr>
    </table>
</body>
</html>
