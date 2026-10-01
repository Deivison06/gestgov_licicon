<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Atas de Registro de Preços</title>
    <style>
        @page {
            margin: 1.2cm 1.2cm 1.5cm 1.2cm;
            size: A4 landscape;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            color: #1e293b;
            line-height: 1.35;
            background-color: #fff;
            margin: 0;
            padding: 0;
        }

        /* ── Cabeçalho ── */
        .header {
            text-align: center;
            margin-bottom: 14px;
            border-bottom: 2px solid #0f374a;
            padding-bottom: 10px;
        }

        .header h1 {
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 3px 0;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #0f374a;
        }

        .header h2 {
            font-size: 11px;
            font-weight: 600;
            margin: 0;
            color: #334155;
            text-transform: uppercase;
        }

        .header .data-emissao {
            font-size: 8.5px;
            color: #64748b;
            margin-top: 4px;
        }

        /* ── Seções ── */
        .section {
            margin-bottom: 14px;
        }

        .section-title {
            font-size: 10px;
            font-weight: bold;
            color: #0f374a;
            border-bottom: 1px solid #0f374a;
            padding-bottom: 3px;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ── Tabela de filtros ── */
        .filtros-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }

        .filtros-table td {
            padding: 5px 8px;
            border: 1px solid #cbd5e1;
            font-size: 8.5px;
            vertical-align: top;
            width: 25%;
            background-color: #f8fafc;
        }

        .filtros-table td .label {
            font-weight: bold;
            display: block;
            margin-bottom: 2px;
            color: #475569;
            font-size: 8px;
            text-transform: uppercase;
        }

        .filtros-table td .value {
            color: #0f172a;
            font-weight: 500;
        }

        /* ── Resumo Financeiro ── */
        .resumo-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }

        .resumo-table td {
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
            width: 25%;
            background-color: #f8fafc;
        }

        .resumo-table td .label {
            font-weight: bold;
            display: block;
            margin-bottom: 3px;
            color: #475569;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .resumo-table td .value {
            font-size: 13px;
            font-weight: bold;
            display: block;
        }

        .resumo-table td .sub {
            font-size: 7.5px;
            color: #64748b;
            margin-top: 2px;
            display: block;
        }

        /* ── Tabela de Atas ── */
        .atas-table {
            width: 100%;
            border-collapse: collapse;
        }

        .atas-table thead {
            display: table-header-group;
        }

        .atas-table thead tr {
            background-color: #0f374a;
            color: #ffffff;
        }

        .atas-table thead th {
            padding: 6px 6px;
            font-size: 8.5px;
            text-align: left;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border: 1px solid #0f374a;
        }

        /* Agrupamento por processo para evitar quebra de página no meio do registro */
        .processo-group {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .bg-odd { background-color: #ffffff; }
        .bg-even { background-color: #f8fafc; }

        .linha-principal td {
            padding: 6px 6px 3px 6px;
            font-size: 8.5px;
            vertical-align: top;
            border-left: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            border-top: 1px solid #cbd5e1;
        }

        .linha-objeto td {
            padding: 3px 6px 6px 6px;
            font-size: 8px;
            color: #475569;
            border-left: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            border-bottom: 1px solid #cbd5e1;
            background-color: inherit;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* Lista de Vencedores */
        .vencedores-container {
            margin: 0;
            padding: 0;
        }

        .vencedor-card {
            padding: 2px 0;
            border-bottom: 1px dashed #cbd5e1;
            margin-bottom: 2px;
        }

        .vencedor-card:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .vencedor-nome {
            font-weight: 600;
            color: #0f172a;
            font-size: 8.5px;
            display: block;
        }

        .vencedor-doc {
            font-size: 7.5px;
            color: #64748b;
            display: block;
        }

        .badge-lotes {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 7px;
            font-weight: 600;
            background-color: #e2e8f0;
            color: #334155;
            margin-top: 2px;
        }

        /* ── Tabela de Total Geral (Somente no final) ── */
        .total-geral-wrapper {
            margin-top: 12px;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .total-geral-table {
            width: 100%;
            border-collapse: collapse;
        }

        .total-geral-table td {
            padding: 8px 8px;
            font-size: 9.5px;
            font-weight: bold;
            background-color: #0f374a;
            color: #ffffff;
            border: 1px solid #0f374a;
        }

        /* ── Rodapé ── */
        .footer {
            margin-top: 18px;
            border-top: 1px solid #cbd5e1;
            padding-top: 5px;
            font-size: 7.5px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>

    {{-- ════════════════════════════════════════ --}}
    {{-- CABEÇALHO --}}
    {{-- ════════════════════════════════════════ --}}
    <div class="header">
        <h1>Relatório de Atas de Registro de Preços</h1>
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
                    <span class="label">Prefeitura</span>
                    <span class="value">{{ $filtros['prefeitura'] ?? 'Todas as Prefeituras' }}</span>
                </td>
                <td>
                    <span class="label">Processo</span>
                    <span class="value">{{ $filtros['processo'] ?? 'Todos os Processos' }}</span>
                </td>
                <td>
                    <span class="label">Pesquisa Livre</span>
                    <span class="value">{{ $filtros['pesquisa_livre'] ?? '—' }}</span>
                </td>
                <td>
                    <span class="label">Data de Referência</span>
                    <span class="value">{{ now()->format('d/m/Y') }}</span>
                </td>
            </tr>
        </table>
    </div>

    {{-- ════════════════════════════════════════ --}}
    {{-- SEÇÃO 2 — RESUMO FINANCEIRO CONSOLIDADO --}}
    {{-- ════════════════════════════════════════ --}}
    <div class="section">
        <div class="section-title">2. Resumo Consolidado da Consulta</div>
        <table class="resumo-table">
            <tr>
                <td>
                    <span class="label">Total de Processos / Atas</span>
                    <span class="value" style="color: #0f374a;">{{ $totais['total_processos'] }}</span>
                    <span class="sub">processos SRP encontrados</span>
                </td>
                <td>
                    <span class="label">Valor Total Licitado</span>
                    <span class="value" style="color: #0f374a;">R$ {{ number_format($totais['valor_licitado'], 2, ',', '.') }}</span>
                    <span class="sub">somatório total homologado em atas</span>
                </td>
                <td>
                    <span class="label">Valor Contratado</span>
                    <span class="value" style="color: #059669;">R$ {{ number_format($totais['valor_contratado'], 2, ',', '.') }}</span>
                    <span class="sub">total formalizado em contratos ({{ $totais['valor_licitado'] > 0 ? number_format(($totais['valor_contratado'] / $totais['valor_licitado']) * 100, 1, ',', '.') : 0 }}%)</span>
                </td>
                <td>
                    <span class="label">Saldo a Contratar</span>
                    <span class="value" style="color: #0284c7;">R$ {{ number_format($totais['saldo_a_contratar'], 2, ',', '.') }}</span>
                    <span class="sub">saldo disponível para novas contratações</span>
                </td>
            </tr>
        </table>
    </div>

    {{-- ════════════════════════════════════════ --}}
    {{-- SEÇÃO 3 — RELAÇÃO DAS ATAS --}}
    {{-- ════════════════════════════════════════ --}}
    <div class="section">
        <div class="section-title">3. Relação Detalhada dos Processos e Atas</div>

        @if(empty($processos) || count($processos) === 0)
            <p style="color:#64748b; font-style:italic; text-align:center; padding:25px 0;">
                Nenhum processo ou ata encontrado com os filtros aplicados.
            </p>
        @else
            <table class="atas-table">
                <thead>
                    <tr>
                        <th style="width:3%" class="text-center">Nº</th>
                        <th style="width:14%">Processo / Procedimento</th>
                        <th style="width:15%">Prefeitura</th>
                        <th style="width:26%">Vencedor(es) da Ata</th>
                        <th style="width:14%" class="text-right">Valor Total Licitado</th>
                        <th style="width:14%" class="text-right">Valor Contratado</th>
                        <th style="width:14%" class="text-right">Saldo a Contratar</th>
                    </tr>
                </thead>
                @foreach($processos as $i => $item)
                    @php
                        $processo = $item['processo'];
                        $valorLicitado = $item['valor_licitado'];
                        $valorContratado = $item['valor_contratado'];
                        $saldoAContratar = $item['saldo_a_contratar'];
                        $bgClass = $i % 2 === 0 ? 'bg-odd' : 'bg-even';

                        $lotesContratadosCount = $processo->lotesContratados->where('status', 'CONTRATADO')->count();
                        $totalLotes = $processo->lotes->count();
                        $vencedoresCount = $processo->vencedores ? $processo->vencedores->count() : 0;
                    @endphp

                    <tbody class="processo-group {{ $bgClass }}">
                        <tr class="linha-principal">
                            <td class="text-center">
                                <strong>{{ $i + 1 }}</strong>
                            </td>
                            <td>
                                <strong style="color: #0f374a;">{{ $processo->numero_processo }}</strong>
                                @if($processo->numero_procedimento)
                                    <br><span style="font-size:7.5px; color:#475569;">Proc: {{ $processo->numero_procedimento }}</span>
                                @endif
                                <br>
                                <span class="badge-lotes">
                                    {{ $totalLotes }} {{ Str::plural('lote', $totalLotes) }} ({{ $lotesContratadosCount }} com contrato)
                                </span>
                            </td>
                            <td>
                                <strong>{{ $processo->prefeitura->cidade ?? $processo->prefeitura->nome ?? '—' }}</strong>
                                @if(!empty($processo->prefeitura->uf))
                                    <span style="color:#64748b;">- {{ $processo->prefeitura->uf }}</span>
                                @endif
                            </td>
                            <td>
                                @if($vencedoresCount > 0)
                                    <div class="vencedores-container">
                                        @foreach($processo->vencedores as $vencedor)
                                            <div class="vencedor-card">
                                                <span class="vencedor-nome">
                                                    {{ $vencedor->razao_social ?? $vencedor->nome }}
                                                </span>
                                                @if(!empty($vencedor->cnpj))
                                                    <span class="vencedor-doc">
                                                        CNPJ: {{ $vencedor->cnpj_formatado ?? $vencedor->cnpj }}
                                                    </span>
                                                @elseif(!empty($vencedor->cpf))
                                                    <span class="vencedor-doc">
                                                        CPF: {{ $vencedor->cpf_formatado ?? $vencedor->cpf }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span style="color:#94a3b8; font-style:italic;">Sem vencedor registrado</span>
                                @endif
                            </td>
                            <td class="text-right" style="font-weight: bold; color: #0f374a;">
                                R$ {{ number_format($valorLicitado, 2, ',', '.') }}
                            </td>
                            <td class="text-right" style="font-weight: bold; color: #059669;">
                                R$ {{ number_format($valorContratado, 2, ',', '.') }}
                                @if($valorLicitado > 0)
                                    <br><span style="font-size:7.5px; font-weight:normal; color:#059669;">
                                        ({{ number_format(($valorContratado / $valorLicitado) * 100, 1, ',', '.') }}%)
                                    </span>
                                @endif
                            </td>
                            <td class="text-right" style="font-weight: bold; color: #0284c7;">
                                R$ {{ number_format($saldoAContratar, 2, ',', '.') }}
                            </td>
                        </tr>
                        <tr class="linha-objeto">
                            <td colspan="7">
                                <strong>Objeto:</strong> {{ trim(html_entity_decode(strip_tags($processo->objeto ?? '—'))) }}
                            </td>
                        </tr>
                    </tbody>
                @endforeach
            </table>

            {{-- ── Total Geral (Renderizado apenas uma vez no final da listagem) ── --}}
            <div class="total-geral-wrapper">
                <table class="total-geral-table">
                    <tr>
                        <td style="width:58%; text-align:right;">
                            TOTAL GERAL ({{ $totais['total_processos'] }} {{ $totais['total_processos'] === 1 ? 'processo' : 'processos' }}):
                        </td>
                        <td style="width:14%; text-align:right;">
                            R$ {{ number_format($totais['valor_licitado'], 2, ',', '.') }}
                        </td>
                        <td style="width:14%; text-align:right; color:#a7f3d0;">
                            R$ {{ number_format($totais['valor_contratado'], 2, ',', '.') }}
                        </td>
                        <td style="width:14%; text-align:right; color:#bae6fd;">
                            R$ {{ number_format($totais['saldo_a_contratar'], 2, ',', '.') }}
                        </td>
                    </tr>
                </table>
            </div>
        @endif
    </div>

    {{-- ════════════════════════════════════════ --}}
    {{-- RODAPÉ --}}
    {{-- ════════════════════════════════════════ --}}
    <!-- <div class="footer">
        Relatório gerado pelo Sistema GestGov em {{ now()->format('d/m/Y \à\s H:i:s') }}
    </div> -->

</body>
</html>
