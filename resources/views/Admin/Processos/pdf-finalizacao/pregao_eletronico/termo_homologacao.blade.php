<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>TERMO DE HOMOLOGAÇÃO - Processo {{ $processo->numero_processo ?? $processo->id }}</title>
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
            padding: 3.8cm 1.8cm 2.5cm 1.8cm;
            font-size: 10pt;
            font-family: 'Aptos', sans-serif;
            background-image: url('{{ public_path($prefeitura->timbre) }}');
            background-repeat: no-repeat;
            background-position: top left;
            background-size: cover;
            text-align: justify;
            line-height: 1.2;
        }

        .page-break {
            page-break-after: always;
        }

        /* CAPA DO DOCUMENTO */
        #cover-page {
            height: 100vh;
            width: 100%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }

        .cover-image {
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

        /* BLOCOS E TABELAS */
        .vencedor-box {
            width: 100%;
            border: 1px solid #000;
            margin-top: 15px;
            margin-bottom: -1px; /* Para grudar com a tabela de itens */
            border-collapse: collapse;
        }

        .vencedor-box td {
            padding: 5px 8px;
            font-size: 9pt;
        }

        table.table-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            table-layout: fixed;
        }

        table.table-items th,
        table.table-items td {
            border: 1px solid #000;
            padding: 5px;
            font-size: 8.5pt;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: normal;
        }

        table.table-items th {
            background-color: #e0e0e0;
            font-weight: bold;
            text-align: center;
        }

        .footer-signature {
            margin-top: 30px;
            text-align: right;
            page-break-inside: avoid;
        }

        .signature-block {
            margin-top: 30px;
            text-align: center;
            page-break-inside: avoid;
        }
    </style>
</head>

<body>

    {{-- CAPA DO DOCUMENTO --}}
    <div id="cover-page">
        <img src="{{ public_path('icons/capa-documento.png') }}" alt="Martelo da Justiça" class="cover-image">
        <div class="cover-title">
            TERMO DE HOMOLOGAÇÃO
        </div>
    </div>

    <div class="page-break"></div>

    {{-- CONTEÚDO DO TERMO --}}
    <div>
        <p style="font-weight: bold; margin-bottom: 5px;">
            PROCESSO ADMINISTRATIVO Nº {{ $processo->numero_processo }} <br>
            PREGÃO ELETRÔNICO Nº. {{ $processo->numero_procedimento }}
        </p>

        <div style="text-align: center; font-weight: bold; margin: 10px 0;">TERMO DE HOMOLOGAÇÃO</div>

        <p style="margin-bottom: 10px;">
            <strong>OBJETO:</strong> {!! strip_tags($processo->objeto) !!}, conforme especificações técnicas do Edital, Termo de Referência e Anexos.
        </p>

        <p style="text-indent: 30px; text-align: justify; margin-bottom: 15px;">
            Considerando a decisão do Pregoeiro e membros da Comissão de Licitação, Ata de Abertura e
            julgamento da Documentação e Propostas das empresas licitantes, confirmo a classificação e HOMOLOGO
            o resultado da presente Licitação na modalidade PREGÃO ELETRÔNICO sob o nº {{ $processo->numero_procedimento }}, nos seguintes
            termos e valores:
        </p>

        @if($processo->tipo_contratacao === \App\Enums\TipoContratacaoEnum::LOTE)
            @foreach ($vencedores as $vencedor)
                @php
                    $lotesAgrupados = $vencedor->lotes->groupBy('lote');
                @endphp

                @if($lotesAgrupados->count() > 0)
                    @foreach($lotesAgrupados as $numeroLote => $itensLote)
                        @if($itensLote->count() > 0)
                            
                            {{-- Cabeçalho do Lote e Dados do Vencedor --}}
                            <table class="vencedor-box">
                                <tr>
                                    <td colspan="2" style="text-align:center; font-weight:bold; background-color:#f0f0f0; font-size: 11pt; border: 1px solid #000;">
                                        LOTE {{ $numeroLote ?? 'NÃO IDENTIFICADO' }} {{ !empty($itensLote->first()->lote_nome) ? ' - ' . $itensLote->first()->lote_nome : '' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 20%; font-weight:bold; background-color:#f8f8f8; border: 1px solid #000;">RAZÃO SOCIAL:</td>
                                    <td style="width: 80%; border: 1px solid #000;">{{ $vencedor->razao_social }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight:bold; background-color:#f8f8f8; border: 1px solid #000;">CNPJ:</td>
                                    <td style="border: 1px solid #000;">{{ $vencedor->cnpj_formatado ?? preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $vencedor->cnpj) }}</td>
                                </tr>
                            </table>

                            {{-- Tabela de Itens --}}
                            <table class="table-items">
                                <thead>
                                    <tr>
                                        <th style="width:7%;">ITEM</th>
                                        <th style="width:45%;">DESCRIÇÃO</th>
                                        <th style="width:8%;">UND.</th>
                                        <th style="width:10%;">QUANT.</th>
                                        <th style="width:15%;">VALOR UNT.</th>
                                        <th style="width:15%;">VALOR TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($itensLote as $item)
                                        <tr>
                                            <td style="text-align:center; vertical-align:top;">{{ $item->item }}</td>
                                            <td style="vertical-align:top; text-align:justify;">
                                                {{ $item->descricao }}
                                                @if($item->marca || $item->modelo)
                                                    <br>
                                                    <small style="color:#555;">
                                                        @if($item->marca)Marca: {{ $item->marca }}@endif
                                                        @if($item->marca && $item->modelo) - @endif
                                                        @if($item->modelo)Modelo: {{ $item->modelo }}@endif
                                                    </small>
                                                @endif
                                            </td>
                                            <td style="text-align:center; vertical-align:top;">{{ $item->unidade }}</td>
                                            <td style="text-align:center; vertical-align:top;">{{ $item->quantidade_formatada ?? number_format($item->quantidade, 0, ',', '.') }}</td>
                                            <td style="text-align:right; vertical-align:top;">{{ $item->valor_unitario_formatado ?? 'R$ ' . number_format($item->vl_unit, 2, ',', '.') }}</td>
                                            <td style="text-align:right; vertical-align:top; font-weight:bold;">{{ $item->valor_total_formatado ?? 'R$ ' . number_format($item->vl_total, 2, ',', '.') }}</td>
                                        </tr>
                                    @endforeach

                                    @php
                                        $totalLote = $itensLote->sum('vl_total');
                                    @endphp
                                    <tr style="background-color:#f0f0f0; font-weight:bold;">
                                        <td colspan="4" style="text-align:right;">TOTAL DO LOTE {{ $numeroLote }}:</td>
                                        <td colspan="2" style="text-align:right; color:#d00;">R$ {{ number_format($totalLote, 2, ',', '.') }}</td>
                                    </tr>
                                </tbody>
                            </table>

                        @endif
                    @endforeach
                @endif
            @endforeach
        @else
            {{-- Se NÃO for por LOTE --}}
            @foreach ($vencedores as $vencedor)
                <table class="vencedor-box">
                    <tr>
                        <td style="width: 20%; font-weight:bold; background-color:#f8f8f8; border: 1px solid #000;">RAZÃO SOCIAL:</td>
                        <td style="width: 80%; border: 1px solid #000;">{{ $vencedor->razao_social }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold; background-color:#f8f8f8; border: 1px solid #000;">CNPJ:</td>
                        <td style="border: 1px solid #000;">{{ $vencedor->cnpj_formatado ?? preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $vencedor->cnpj) }}</td>
                    </tr>
                </table>

                <table class="table-items">
                    <thead>
                        <tr>
                            <th style="width:7%;">ITEM</th>
                            <th style="width:45%;">DESCRIÇÃO</th>
                            <th style="width:8%;">UND.</th>
                            <th style="width:10%;">QUANT.</th>
                            <th style="width:15%;">VALOR UNT.</th>
                            <th style="width:15%;">VALOR TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vencedor->lotes as $item)
                            <tr>
                                <td style="text-align:center; vertical-align:top;">{{ $item->item }}</td>
                                <td style="vertical-align:top; text-align:justify;">
                                    {{ $item->descricao }}
                                    @if($item->marca || $item->modelo)
                                        <br>
                                        <small style="color:#555;">
                                            @if($item->marca)Marca: {{ $item->marca }}@endif
                                            @if($item->marca && $item->modelo) - @endif
                                            @if($item->modelo)Modelo: {{ $item->modelo }}@endif
                                        </small>
                                    @endif
                                </td>
                                <td style="text-align:center; vertical-align:top;">{{ $item->unidade }}</td>
                                <td style="text-align:center; vertical-align:top;">{{ $item->quantidade_formatada ?? number_format($item->quantidade, 0, ',', '.') }}</td>
                                <td style="text-align:right; vertical-align:top;">{{ $item->valor_unitario_formatado ?? 'R$ ' . number_format($item->vl_unit, 2, ',', '.') }}</td>
                                <td style="text-align:right; vertical-align:top; font-weight:bold;">{{ $item->valor_total_formatado ?? 'R$ ' . number_format($item->vl_total, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach

                        @php
                            $totalGeral = $vencedor->lotes->sum('vl_total');
                        @endphp
                        <tr style="background-color:#f0f0f0; font-weight:bold;">
                            <td colspan="4" style="text-align:right;">TOTAL GERAL:</td>
                            <td colspan="2" style="text-align:right; color:#d00;">R$ {{ number_format($totalGeral, 2, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            @endforeach
        @endif

        <p style="text-indent: 30px; text-align: justify; margin-top: 15px;">
            Autorizo ultimar os procedimentos com vista à assinatura do contrato, com o licitante vencedor e
            determino que a Secretária Municipal de Administração providencie o necessário ao cumprimento desta
            homologação.
        </p>

        <div class="footer-signature">
            {{ $processo->prefeitura->cidade }},
            {{ \Carbon\Carbon::parse($dataSelecionada)->translatedFormat('d \d\e F \d\e Y') }}
        </div>

        @if ($hasSelectedAssinantes)
            @php $primeiroAssinante = $assinantes[0]; @endphp
            <div class="signature-block">
                ___________________________________<br>
                <p style="line-height: 1.2; margin-top: 5px;">
                    {{ $primeiroAssinante['responsavel'] }} <br>
                    <span>{{ $primeiroAssinante['unidade_nome'] }}</span>
                </p>
            </div>
        @else
            <div class="signature-block">
                ___________________________________<br>
                <p style="line-height: 1.2; margin-top: 5px;">
                    {{ $processo->prefeitura->autoridade_competente }} <br>
                    <span>Prefeito Municipal</span>
                </p>
            </div>
        @endif
    </div>
</body>

</html>