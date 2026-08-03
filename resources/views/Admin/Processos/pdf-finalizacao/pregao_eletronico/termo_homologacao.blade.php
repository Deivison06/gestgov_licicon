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
            padding: 4cm 2cm;
            font-size: 11pt;
            font-family: 'Aptos', sans-serif;
            background-image: url('{{ public_path($prefeitura->timbre) }}');
            background-repeat: no-repeat;
            background-position: top left;
            background-size: cover;
            text-align: justify;
            text-justify: inter-word;
            line-height: 1.2;
        }

        .page-break {
            page-break-after: always;
        }

        /* ---------------------------------- */
        /* CAPA DO DOCUMENTO */
        /* ---------------------------------- */
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

        /* ---------------------------------- */
        /* REGRAS DE TABELA PARA PDF */
        /* ---------------------------------- */
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            page-break-inside: auto; /* Permite que a tabela divida entre páginas */
        }

        tr {
            page-break-inside: avoid; /* Evita que uma única linha corte ao meio */
            page-break-after: auto;
        }

        thead {
            display: table-header-group; /* Repete cabeçalho se a tabela quebrar */
        }

        td, th {
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-all; /* Força quebra de palavras/textos longos */
            white-space: normal;
        }

        .table-items {
            font-size: 10pt;
            margin-bottom: 20px;
        }

        .footer-signature {
            margin-top: 40px;
            text-align: right;
            page-break-inside: avoid;
        }

        .signature-block {
            margin-top: 40px;
            text-align: center;
            page-break-inside: avoid;
        }

        strong {
            line-height: 1.3;
            display: block;
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
            TERMO DE HOMOLOGAÇÃO
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

        <div style="text-align: center; font-weight: bold; margin-bottom: 15px;">TERMO DE HOMOLOGAÇÃO</div>

        <table style="margin-bottom: 15px;">
            <tr>
                <td style="width: 100%; padding: 8px; vertical-align: top; text-align: justify;">
                    <strong>OBJETO:</strong>
                    <span style="font-size: 11px;">{!! strip_tags($processo->objeto) !!}</span>, conforme especificações técnicas do Edital, Termo de Referência e Anexos.
                </td>
            </tr>
        </table>

        <p style="text-indent: 30px; text-align: justify;">
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
                            <table class="table-items" border="1">
                                <thead>
                                    <!-- Cabeçalho do Lote -->
                                    <tr>
                                        <th colspan="6" style="text-align:center; font-weight:bold; padding:8px; background-color:#f0f0f0; font-size: 12px;">
                                            LOTE {{ $numeroLote ?? 'NÃO IDENTIFICADO' }} {{ !empty($itensLote->first()->lote_nome) ? ' - ' . $itensLote->first()->lote_nome : '' }}
                                        </th>
                                    </tr>

                                    <!-- Informações do Vencedor -->
                                    <tr>
                                        <th colspan="2" style="padding:6px; font-weight:bold; background-color:#f8f8f8; text-align:left;">
                                            RAZÃO SOCIAL
                                        </th>
                                        <td colspan="4" style="padding:6px; text-align:left;">
                                            {{ $vencedor->razao_social }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <th colspan="2" style="padding:6px; font-weight:bold; background-color:#f8f8f8; text-align:left;">
                                            CNPJ
                                        </th>
                                        <td colspan="4" style="padding:6px; text-align:left;">
                                            {{ $vencedor->cnpj_formatado ?? preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $vencedor->cnpj) }}
                                        </td>
                                    </tr>

                                    <!-- Cabeçalho das Colunas -->
                                    <tr style="background-color:#e0e0e0;">
                                        <th style="padding:6px; text-align:center; width:8%;">ITEM</th>
                                        <th style="padding:6px; text-align:center; width:42%;">DESCRIÇÃO</th>
                                        <th style="padding:6px; text-align:center; width:8%;">UND.</th>
                                        <th style="padding:6px; text-align:center; width:10%;">QUANT.</th>
                                        <th style="padding:6px; text-align:center; width:16%;">VALOR UNT.</th>
                                        <th style="padding:6px; text-align:center; width:16%;">VALOR TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($itensLote as $item)
                                        <tr>
                                            <td style="padding:5px; text-align:center; vertical-align:top;">
                                                {{ $item->item }}
                                            </td>
                                            <td style="padding:5px; vertical-align:top; text-align:justify;">
                                                {{ $item->descricao }}
                                                @if($item->marca || $item->modelo)
                                                    <br>
                                                    <small style="color:#666;">
                                                        @if($item->marca)Marca: {{ $item->marca }}@endif
                                                        @if($item->marca && $item->modelo) - @endif
                                                        @if($item->modelo)Modelo: {{ $item->modelo }}@endif
                                                    </small>
                                                @endif
                                            </td>
                                            <td style="padding:5px; text-align:center; vertical-align:top;">
                                                {{ $item->unidade }}
                                            </td>
                                            <td style="padding:5px; text-align:center; vertical-align:top;">
                                                {{ $item->quantidade_formatada ?? number_format($item->quantidade, 0, ',', '.') }}
                                            </td>
                                            <td style="padding:5px; text-align:right; vertical-align:top;">
                                                {{ $item->valor_unitario_formatado ?? 'R$ ' . number_format($item->vl_unit, 2, ',', '.') }}
                                            </td>
                                            <td style="padding:5px; text-align:right; vertical-align:top; font-weight:bold;">
                                                {{ $item->valor_total_formatado ?? 'R$ ' . number_format($item->vl_total, 2, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach

                                    <!-- Total do Lote -->
                                    @php
                                        $totalLote = $itensLote->sum('vl_total');
                                    @endphp
                                    <tr style="background-color:#f0f0f0; font-weight:bold;">
                                        <td colspan="3" style="padding:6px; text-align:right;">
                                            TOTAL DO LOTE {{ $numeroLote }}:
                                        </td>
                                        <td colspan="3" style="padding:6px; text-align:right; color:#d00;">
                                            R$ {{ number_format($totalLote, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        @endif
                    @endforeach
                @else
                    <table class="table-items" border="1">
                        <tr>
                            <td style="padding:10px; text-align:center; color:#999;">
                                Nenhum lote cadastrado para o vencedor: {{ $vencedor->razao_social }}
                            </td>
                        </tr>
                    </table>
                @endif
            @endforeach
        @else
            {{-- Se NÃO for tipo LOTE --}}
            @foreach ($vencedores as $vencedor)
                <table class="table-items" border="1">
                    <thead>
                        <tr>
                            <th colspan="2" style="padding:6px; font-weight:bold; background-color:#f8f8f8; text-align:left;">
                                RAZÃO SOCIAL
                            </th>
                            <td colspan="4" style="padding:6px; text-align:left;">
                                {{ $vencedor->razao_social }}
                            </td>
                        </tr>
                        <tr>
                            <th colspan="2" style="padding:6px; font-weight:bold; background-color:#f8f8f8; text-align:left;">
                                CNPJ
                            </th>
                            <td colspan="4" style="padding:6px; text-align:left;">
                                {{ $vencedor->cnpj_formatado ?? preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $vencedor->cnpj) }}
                            </td>
                        </tr>
                        <tr style="background-color:#e0e0e0;">
                            <th style="padding:6px; text-align:center; width:8%;">ITEM</th>
                            <th style="padding:6px; text-align:center; width:42%;">DESCRIÇÃO</th>
                            <th style="padding:6px; text-align:center; width:8%;">UND.</th>
                            <th style="padding:6px; text-align:center; width:10%;">QUANT.</th>
                            <th style="padding:6px; text-align:center; width:16%;">VALOR UNT.</th>
                            <th style="padding:6px; text-align:center; width:16%;">VALOR TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vencedor->lotes as $item)
                            <tr>
                                <td style="padding:5px; text-align:center; vertical-align:top;">
                                    {{ $item->item }}
                                </td>
                                <td style="padding:5px; vertical-align:top; text-align:justify;">
                                    {{ $item->descricao }}
                                    @if($item->marca || $item->modelo)
                                        <br>
                                        <small style="color:#666;">
                                            @if($item->marca)Marca: {{ $item->marca }}@endif
                                            @if($item->marca && $item->modelo) - @endif
                                            @if($item->modelo)Modelo: {{ $item->modelo }}@endif
                                        </small>
                                    @endif
                                </td>
                                <td style="padding:5px; text-align:center; vertical-align:top;">
                                    {{ $item->unidade }}
                                </td>
                                <td style="padding:5px; text-align:center; vertical-align:top;">
                                    {{ $item->quantidade_formatada ?? number_format($item->quantidade, 0, ',', '.') }}
                                </td>
                                <td style="padding:5px; text-align:right; vertical-align:top;">
                                    {{ $item->valor_unitario_formatado ?? 'R$ ' . number_format($item->vl_unit, 2, ',', '.') }}
                                </td>
                                <td style="padding:5px; text-align:right; vertical-align:top; font-weight:bold;">
                                    {{ $item->valor_total_formatado ?? 'R$ ' . number_format($item->vl_total, 2, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach

                        @php
                            $totalGeral = $vencedor->lotes->sum('vl_total');
                            $quantidadeTotal = $vencedor->lotes->sum('quantidade');
                        @endphp
                        <tr style="background-color:#f0f0f0; font-weight:bold;">
                            <td colspan="3" style="padding:6px; text-align:right;">
                                TOTAL GERAL:
                            </td>
                            <td style="padding:6px; text-align:center;">
                                {{ number_format($quantidadeTotal, 0, ',', '.') }}
                            </td>
                            <td style="padding:6px; text-align:center;">
                                -
                            </td>
                            <td style="padding:6px; text-align:right; color:#d00;">
                                R$ {{ number_format($totalGeral, 2, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            @endforeach
        @endif

        <p style="text-indent: 30px; text-align: justify;">
            Autorizo ultimar os procedimentos com vista à assinatura do contrato, com o licitante vencedor e
            determino que a Secretária Municipal de Administração providencie o necessário ao cumprimento desta
            homologação.
        </p>

        {{-- Bloco de data e assinatura --}}
        <div class="footer-signature">
            {{ $processo->prefeitura->cidade }},
            {{ \Carbon\Carbon::parse($dataSelecionada)->translatedFormat('d \d\e F \d\e Y') }}
        </div>

        @if ($hasSelectedAssinantes)
            @php
                $primeiroAssinante = $assinantes[0];
            @endphp

            <div class="signature-block">
                <div style="display: inline-block; margin: 0 40px;">
                    ___________________________________<br>
                    <p style="line-height: 1.2;">
                        {{ $primeiroAssinante['responsavel'] }} <br>
                        <span>{{ $primeiroAssinante['unidade_nome'] }}</span>
                    </p>
                </div>
            </div>
        @else
            <div class="signature-block">
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