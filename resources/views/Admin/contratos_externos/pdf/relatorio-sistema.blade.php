<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Contratos do Sistema</title>
    <style>
        @page {
            margin: 1.5cm 1.5cm 2cm 1.5cm;
            size: A4 landscape;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #000;
            line-height: 1.4;
        }

        /* ── Cabeçalho ── */
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #1a3a4a;
            padding-bottom: 12px;
        }

        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin: 0 0 4px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #1a3a4a;
        }

        .header h2 {
            font-size: 11px;
            font-weight: normal;
            margin: 0;
            color: #444;
            text-transform: uppercase;
        }

        .header .data-emissao {
            font-size: 9px;
            color: #666;
            margin-top: 6px;
        }

        /* ── Seções ── */
        .section {
            margin-bottom: 18px;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #1a3a4a;
            border-bottom: 1px solid #1a3a4a;
            padding-bottom: 4px;
            margin-bottom: 10px;
        }

        /* ── Tabela de filtros ── */
        .filtros-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        .filtros-table td {
            padding: 6px 10px;
            border: 1px solid #d0d0d0;
            font-size: 9.5px;
            vertical-align: top;
            width: 33.33%;
        }

        .filtros-table td .label {
            font-weight: bold;
            display: block;
            margin-bottom: 2px;
            color: #333;
        }

        .filtros-table td .value {
            color: #555;
        }

        /* ── Resumo ── */
        .resumo-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        .resumo-table td {
            padding: 8px 12px;
            border: 1px solid #d0d0d0;
            font-size: 9.5px;
            vertical-align: top;
            width: 33.33%;
        }

        .resumo-table td .label {
            font-weight: bold;
            display: block;
            margin-bottom: 3px;
            color: #333;
        }

        .resumo-table td .value {
            font-size: 13px;
            font-weight: bold;
            color: #1a3a4a;
        }

        .resumo-table td .sub {
            font-size: 8px;
            color: #888;
            margin-top: 2px;
        }

        /* ── Tabela de contratos ── */
        .contratos-table {
            width: 100%;
            border-collapse: collapse;
        }

        .contratos-table thead tr {
            background-color: #1a3a4a;
            color: #fff;
        }

        .contratos-table thead th {
            padding: 7px 6px;
            font-size: 9px;
            text-align: left;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .bg-odd { background-color: #ffffff; }
        .bg-even { background-color: #f5f8fa; }

        .contratos-table tbody td {
            padding: 5px 6px;
            font-size: 8.5px;
            vertical-align: middle;
        }

        .border-no-bottom td {
            border-bottom: none !important;
        }

        .linha-objeto td {
            border-bottom: 1px solid #e0e0e0;
            padding-top: 2px !important;
            padding-bottom: 8px !important;
            color: #444;
        }

        .contratos-table tfoot tr {
            background-color: #e8f0f5;
            font-weight: bold;
        }

        .contratos-table tfoot td {
            padding: 6px 6px;
            font-size: 9px;
            border-top: 2px solid #1a3a4a;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* ── Rodapé ── */
        .footer {
            margin-top: 20px;
            border-top: 1px solid #ccc;
            padding-top: 6px;
            font-size: 8px;
            color: #888;
            text-align: center;
        }
    </style>
</head>
<body>

    {{-- ════════════════════════════════════════ --}}
    {{-- CABEÇALHO --}}
    {{-- ════════════════════════════════════════ --}}
    <div class="header">
        <h1>Relatório de Contratos do Sistema</h1>
        @if($prefeituraNome)
            <h2>{{ $prefeituraNome }}</h2>
        @endif
        <div class="data-emissao">Emitido em: {{ now()->format('d/m/Y \à\s H:i') }}</div>
    </div>

    {{-- ════════════════════════════════════════ --}}
    {{-- SEÇÃO 1 — FILTROS APLICADOS --}}
    {{-- ════════════════════════════════════════ --}}
    <div class="section">
        <div class="section-title">1. Filtros Aplicados</div>
        <table class="filtros-table">
            <tr>
                <td>
                    <span class="label">Pesquisa livre</span>
                    <span class="value">{{ $filtros['pesquisa_livre'] ?? '—' }}</span>
                </td>
                <td>
                    <span class="label">Prefeitura</span>
                    <span class="value">{{ $filtros['prefeitura'] ?? 'Todas' }}</span>
                </td>
                <td>
                    <span class="label">Modalidade</span>
                    <span class="value">{{ $filtros['modalidade'] ?? 'Todas' }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Vencedor</span>
                    <span class="value">{{ $filtros['vencedor'] ?? 'Todos' }}</span>
                </td>
                <td>
                    <span class="label">Data de referência</span>
                    <span class="value">{{ now()->format('d/m/Y') }}</span>
                </td>
                <td></td>
            </tr>
        </table>
    </div>

    {{-- ════════════════════════════════════════ --}}
    {{-- SEÇÃO 2 — RESUMO DA CONSULTA --}}
    {{-- ════════════════════════════════════════ --}}
    <div class="section">
        <div class="section-title">2. Resumo da Consulta</div>
        <table class="resumo-table">
            <tr>
                <td>
                    <span class="label">Total de contratos</span>
                    <span class="value">{{ $totalContratos }}</span>
                    <span class="sub">registros encontrados</span>
                </td>
                <td>
                    <span class="label">Valor global dos contratos</span>
                    <span class="value">R$ {{ number_format($valorGlobal, 2, ',', '.') }}</span>
                    <span class="sub">somatório dos valores homologados</span>
                </td>
                <td>
                    <span class="label">Período de assinatura</span>
                    @if($dataAssinaturaMin || $dataAssinaturaMax)
                        <span class="value" style="font-size:11px;">
                            {{ $dataAssinaturaMin ? $dataAssinaturaMin->format('d/m/Y') : '—' }}
                            até
                            {{ $dataAssinaturaMax ? $dataAssinaturaMax->format('d/m/Y') : '—' }}
                        </span>
                    @else
                        <span class="value" style="font-size:11px;">—</span>
                    @endif
                    <span class="sub">menor e maior data de assinatura</span>
                </td>
            </tr>
        </table>
    </div>

    {{-- ════════════════════════════════════════ --}}
    {{-- SEÇÃO 3 — RELAÇÃO DE CONTRATOS --}}
    {{-- ════════════════════════════════════════ --}}
    <div class="section">
        <div class="section-title">3. Relação dos Contratos</div>

        @if($processos->isEmpty())
            <p style="color:#888; font-style:italic; text-align:center; padding:20px 0;">
                Nenhum contrato encontrado com os filtros aplicados.
            </p>
        @else
            <table class="contratos-table">
                <thead>
                    <tr>
                        <th style="width:4%">Nº</th>
                        <th style="width:12%">Processo</th>
                        <th style="width:14%">Modalidade</th>
                        <th style="width:14%">Contrato</th>
                        <th style="width:24%">Contratada</th>
                        <th style="width:12%" class="text-center">Vigência</th>
                        <th style="width:12%" class="text-right">Valor Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($processos as $i => $processo)
                        @php
                            $bgClass = $i % 2 === 0 ? 'bg-odd' : 'bg-even';

                            $contratada = $processo->contrato->homologacao?->razao_social
                                ?? $processo->vencedores->first()?->razao_social;
                            $contratadaCnpj = $processo->contrato->homologacao?->cnpj_empresa_vencedora
                                ?? $processo->vencedores->first()?->cnpj_formatado;

                            $valor = $processo->contrato->homologacao?->valor_total
                                ?? $processo->vencedores->sum('valor_total');
                        @endphp

                        <tr class="{{ $bgClass }} border-no-bottom">
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td>
                                {{ $processo->numero_processo }}
                                @if($processo->numero_procedimento)
                                    <br><span style="font-size:7.5px;color:#888;">Proc: {{ $processo->numero_procedimento }}</span>
                                @endif
                            </td>
                            <td>{{ $processo->modalidade?->getDisplayName() ?? '—' }}</td>
                            <td>
                                {{ $processo->contrato->numero_contrato ?? '—' }}
                                @if($processo->contrato->data_assinatura_contrato)
                                    <br><span style="font-size:7.5px;color:#888;">Assinatura: {{ $processo->contrato->data_assinatura_contrato->format('d/m/Y') }}</span>
                                @endif
                            </td>
                            <td>
                                {{ $contratada ?? '—' }}
                                @if($contratadaCnpj)
                                    <br><span style="font-size:7.5px;color:#888;">{{ $contratadaCnpj }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                {{ $processo->detalhe->prazo_vigencia_texto ?? '—' }}
                            </td>
                            <td class="text-right">
                                R$ {{ number_format($valor, 2, ',', '.') }}
                            </td>
                        </tr>
                        <tr class="{{ $bgClass }} linha-objeto">
                            <td colspan="7">
                                <strong>Objeto:</strong> {{ trim(html_entity_decode(strip_tags($processo->objeto ?? ''))) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6" class="text-right">Total geral:</td>
                        <td class="text-right">R$ {{ number_format($valorGlobal, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        @endif
    </div>

</body>
</html>
