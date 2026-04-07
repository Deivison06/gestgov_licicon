<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Saldo de Ata - Processo {{ $processo->numero_processo ?? $processo->id }}</title>
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
            padding: 3.5cm 1.5cm 2cm 1.5cm;
            font-size: 10pt;
            font-family: 'Aptos', sans-serif;
            background-image: url('{{ public_path($prefeitura->timbre) }}');
            background-repeat: no-repeat;
            background-position: top left;
            background-size: cover;
            line-height: 1.3;
        }

        .header-title {
            text-align: center;
            font-family: 'AptosExtraBold', sans-serif;
            font-size: 14pt;
            margin-bottom: 20px;
            border-bottom: 1px solid #000;
            padding-bottom: 10px;
        }

        .info-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 4px 0;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .data-table th {
            background-color: #f3f4f6;
            font-family: 'AptosExtraBold', sans-serif;
            text-align: left;
            padding: 8px;
            border: 0.5px solid #d1d5db;
            font-size: 8pt;
            text-transform: uppercase;
        }

        .data-table td {
            padding: 6px 8px;
            border: 0.5px solid #d1d5db;
            font-size: 8pt;
            vertical-align: top;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-family: 'AptosExtraBold', sans-serif; }
        
        .badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 7pt;
            font-weight: bold;
        }
        
        .badge-success { background-color: #dcfce7; color: #166534; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }

        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 9pt;
        }

        .signature-area {
            margin-top: 50px;
            text-align: center;
        }

        .signature-line {
            width: 250px;
            border-top: 1px solid #000;
            margin: 0 auto;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header-title">
        SALDO DISPONÍVEL PARA CONTRATAÇÃO
    </div>

    <table class="info-table">
        <tr>
            <td width="20%"><span class="font-bold">PROCESSO:</span></td>
            <td width="30%">{{ $processo->numero_processo }}</td>
            <td width="20%"><span class="font-bold">MODALIDADE:</span></td>
            <td width="30%">{{ $processo->modalidade->getDisplayName() }} {{ $processo->numero_procedimento }}</td>
        </tr>
        <tr>
            <td><span class="font-bold">PREFEITURA:</span></td>
            <td colspan="3">{{ $processo->prefeitura->nome }}</td>
        </tr>
        <tr>
            <td><span class="font-bold">DATA:</span></td>
            <td colspan="3">{{ now()->format('d/m/Y H:i') }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">Item</th>
                <th width="40%">Descrição</th>
                <th width="15%">Vencedor</th>
                <th width="10%" class="text-center">Licitado</th>
                <th width="10%" class="text-center">Adquirido</th>
                <th width="10%" class="text-center">Saldo</th>
                <th width="10%" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dadosAtas as $item)
                <tr>
                    <td class="text-center">{{ $item['item'] }}</td>
                    <td>{{ $item['descricao'] }}</td>
                    <td style="font-size: 5pt;">{{ $item['vencedor'] }}</td>
                    <td class="text-center">{{ number_format($item['quantidade_total'], 2, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($item['quantidade_utilizada'], 2, ',', '.') }}</td>
                    <td class="text-center font-bold">{{ number_format($item['quantidade_disponivel'], 2, ',', '.') }}</td>
                    <td class="text-center">
                        @if($item['quantidade_disponivel'] > 0)
                            <span class="badge badge-success">DISPONÍVEL</span>
                        @else
                            <span class="badge badge-danger">ESGOTADO</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        {{ $processo->prefeitura->cidade }} - {{ $processo->prefeitura->uf }}, {{ now()->translatedFormat('d \d\e F \d\e Y') }}
    </div>

    <div class="signature-area">
        <div class="signature-line"></div>
        <div class="font-bold">{{ $processo->prefeitura->autoridade_competente ?? 'Responsável' }}</div>
        <div>{{ $processo->prefeitura->nome }}</div>
    </div>
</body>
</html>
