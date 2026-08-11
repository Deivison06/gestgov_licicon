<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>TERMO DE REGISTRO E DECISÃO ADMINISTRATIVA - Processo {{ $processo->numero_processo ?? $processo->id }}</title>
    <style type="text/css">
        @font-face {
            font-family: 'Aptos';
            src: url('{{ public_path('storage/fonts/Aptos.ttf') }}') format('truetype');
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
            background-image: url('{{ public_path($prefeitura->timbre) }}');
            background-repeat: no-repeat;
            background-position: top left;
            background-size: cover;
            text-align: justify;
            text-justify: inter-word;
            line-height: 1.4;
        }

        .titulo {
            text-align: center;
            font-weight: bold;
            font-size: 13pt;
            margin-bottom: 20px;
        }

        .cabecalho {
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }

        p {
            text-indent: 30px;
            margin: 0 0 14px 0;
        }

        table.itens {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin: 10px 0 20px 0;
        }

        table.itens th,
        table.itens td {
            border: 1px solid #999;
            padding: 5px;
        }

        table.itens th {
            background-color: #f0f0f0;
            text-align: center;
        }

        .footer-signature {
            margin-top: 60px;
            text-align: right;
        }

        .signature-block {
            margin-top: 60px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="cabecalho">
        PROCESSO ADMINISTRATIVO Nº {{ $processo->numero_processo }} <br>
        PREGÃO ELETRÔNICO Nº {{ $processo->numero_procedimento }}
    </div>

    <div class="titulo">TERMO DE REGISTRO E DECISÃO ADMINISTRATIVA</div>

    <p>
        OBJETO: <span style="font-size: 10pt;">{!! strip_tags($processo->objeto) !!}</span>.
    </p>

    <p>
        Considerando que, após a conclusão do procedimento licitatório, a empresa
        <strong>{{ $vencedor->razao_social }}</strong>, inscrita no CNPJ nº
        {{ $vencedor->cnpj_formatado ?? $vencedor->cnpj }}, vencedora do(s) item(ns)/lote(s) abaixo
        relacionados, foi regularmente convocada para assinatura da respectiva Ata de Registro de
        Preços, tendo o instrumento sido encaminhado em
        {{ \Carbon\Carbon::parse($desistencia->data_solicitacao_assinatura)->format('d/m/Y') }}, sem
        que, até a presente data, tenha sido devolvido devidamente assinado;
    </p>

    <p>
        Considerando que a empresa não apresentou manifestação ou justificativa que impedisse a
        assinatura do instrumento, permanecendo inerte diante da convocação realizada pela
        Administração;
    </p>

    <p>
        Considerando que a ausência de assinatura impossibilita a formalização do registro e o
        prosseguimento das contratações necessárias ao atendimento da demanda administrativa;
    </p>

    <p>
        Considerando, ainda, o disposto no art. 90 da Lei nº 14.133/2021, que prevê a perda do
        direito à contratação quando o licitante vencedor, regularmente convocado, não atende à
        convocação no prazo e condições estabelecidos, bem como possibilita a convocação dos
        licitantes remanescentes, observada a ordem de classificação;
    </p>

    @if (!empty($desistencia->quantidade_lotes_snapshot))
        <table class="itens">
            <thead>
                <tr>
                    <th style="width:10%;">Lote</th>
                    <th style="width:10%;">Item</th>
                    <th>Descrição</th>
                    <th style="width:12%;">Quantidade</th>
                    <th style="width:14%;">Valor Unit.</th>
                    <th style="width:14%;">Valor Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($desistencia->quantidade_lotes_snapshot as $item)
                    <tr>
                        <td style="text-align:center;">{{ $item['lote'] ?? '—' }}</td>
                        <td style="text-align:center;">{{ $item['item'] ?? '—' }}</td>
                        <td>{{ $item['descricao'] ?? '—' }}</td>
                        <td style="text-align:right;">{{ number_format($item['quantidade'] ?? 0, 2, ',', '.') }}</td>
                        <td style="text-align:right;">R$ {{ number_format($item['vl_unit'] ?? 0, 2, ',', '.') }}</td>
                        <td style="text-align:right;">R$ {{ number_format($item['vl_total'] ?? 0, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <p>
        DECIDE-SE registrar a ausência de assinatura da Ata de Registro de Preços pela empresa
        <strong>{{ $vencedor->razao_social }}</strong>, reconhecendo-se a perda do seu direito à
        formalização do respectivo registro quanto ao(s) item(ns).
    </p>

    <p>
        Determina-se, por consequência, o prosseguimento do procedimento com a convocação do
        próximo licitante classificado, observada a ordem de classificação, as condições
        estabelecidas no edital e a legislação aplicável, para manifestação quanto ao interesse
        em assumir o fornecimento.
    </p>

    <p>
        Junte-se aos autos a comprovação da convocação encaminhada à empresa e adotem-se as
        providências necessárias ao regular prosseguimento do processo.
    </p>

    @if ($desistencia->observacao)
        <p><strong>Observação:</strong> {{ $desistencia->observacao }}</p>
    @endif

    <div class="footer-signature">
        {{ $processo->prefeitura->cidade }}, {{ now()->translatedFormat('d \d\e F \d\e Y') }}.
    </div>

    <div class="signature-block">
        ___________________________________<br>
        <p style="text-indent: 0; line-height: 1.2;">
            {{ $homologacao->responsavel ?: $processo->prefeitura->autoridade_competente }} <br>
            <span>{{ $homologacao->cargo_responsavel ?: 'Prefeito(a) Municipal' }}</span>
        </p>
    </div>
</body>

</html>
