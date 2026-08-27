<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório Técnico de Fiscalização - {{ $fiscalizacao->numero_fiscalizacao }}</title>
    @php
        $timbre = $fiscalizacao->prefeitura->timbre ?? '';
        $timbrePath = public_path($timbre);
        $base64Timbre = '';
        if ($timbre && file_exists($timbrePath)) {
            $type = pathinfo($timbrePath, PATHINFO_EXTENSION);
            $data = file_get_contents($timbrePath);
            $base64Timbre = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        $fotoRelatorio = $fiscalizacao->relatorio_fotografico ?? '';
        $fotoPath = public_path($fotoRelatorio);
        $base64Foto = '';
        if ($fotoRelatorio && file_exists($fotoPath)) {
            $type = pathinfo($fotoPath, PATHINFO_EXTENSION);
            $data = file_get_contents($fotoPath);
            $base64Foto = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        $info = $fiscalizacao->contrato_info;
        $tipo = $fiscalizacao->tipo_contrato?->value;

        // Labels dinâmicas conforme o tipo de contrato
        $labels = match($tipo) {
            'compras' => [
                'execucao' => 'Execução no Período',
                'qualidade' => 'Qualidade dos Produtos Entregues',
                'obs_servidor' => 'Observações Indicadas por Servidor Próximo à Execução'
            ],
            'servicos' => [
                'execucao' => 'Execução no Período',
                'qualidade' => 'Qualidade dos Serviços Realizados',
                'obs_servidor' => 'Observações Indicadas por Servidor Próximo à Execução'
            ],
            'obras' => [
                'execucao' => 'Execução do Objeto',
                'qualidade' => 'Qualidade dos Serviços Executados',
                'obs_servidor' => 'Observações Indicadas por Servidor Fiscal de Engenharia'
            ],
            default => [
                'execucao' => 'Execução do Objeto',
                'qualidade' => 'Qualidade das Entregas',
                'obs_servidor' => 'Observações do Servidor'
            ]
        };
    @endphp
    <style>
        @page {
            size: A4 portrait;
            /* Quando há timbre (imagem A4 de fundo), expande margem superior (70mm) e inferior (40mm) para total clareza do timbre */
            margin: {{ $base64Timbre ? '70mm' : '32mm' }} 15mm {{ $base64Timbre ? '40mm' : '20mm' }} 15mm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            line-height: 1.45;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }

        /* Timbre Background cobrindo toda a folha A4 a partir de (0,0) */
        .timbre-bg {
            position: fixed;
            top: -{{ $base64Timbre ? '70mm' : '32mm' }};
            left: -15mm;
            width: 210mm;
            height: 297mm;
            z-index: -1000;
        }

        /* Cabeçalho Institucional Fixo */
        @if(!$base64Timbre)
            header {
                position: fixed;
                top: -26mm;
                left: 0;
                right: 0;
                height: 22mm;
                border-bottom: 2px solid #0f2942;
                padding-bottom: 4px;
            }
        @else
            header {
                position: fixed;
                top: -14mm;
                left: 0;
                right: 0;
                height: 10mm;
                border-bottom: 1.5px solid #0f2942;
                padding-bottom: 2px;
            }
        @endif

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-title-cell {
            vertical-align: bottom;
            text-align: left;
            padding-left: 4px;
        }
        .inst-name {
            font-size: 11pt;
            font-weight: bold;
            color: #0f2942;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .inst-sub {
            font-size: 8pt;
            color: #475569;
            text-transform: uppercase;
            margin: 1px 0 0 0;
        }
        .doc-badge-cell {
            vertical-align: bottom;
            text-align: right;
            white-space: nowrap;
        }
        .doc-type-badge {
            background-color: #0f2942;
            color: #ffffff;
            font-size: 8pt;
            font-weight: bold;
            padding: 3px 6px;
            border-radius: 3px;
            display: inline-block;
            text-transform: uppercase;
            vertical-align: middle;
        }
        .doc-num {
            font-size: 8.5pt;
            color: #334155;
            font-weight: bold;
            margin-top: 3px;
        }
        .doc-num-inline {
            font-size: 8pt;
            color: #334155;
            font-weight: bold;
            margin-left: 4px;
            vertical-align: middle;
            white-space: nowrap;
        }

        /* Rodapé Fixo */
        footer {
            position: fixed;
            bottom: {{ $base64Timbre ? '-30mm' : '-15mm' }};
            left: 0;
            right: 0;
            height: 10mm;
            border-top: 1px solid #cbd5e1;
            padding-top: 4px;
            font-size: 7.5pt;
            color: #64748b;
        }

        /* Título Principal */
        .main-title-container {
            text-align: center;
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 1px solid #e2e8f0;
        }
        .main-title {
            font-size: 13pt;
            font-weight: bold;
            color: #0f2942;
            text-transform: uppercase;
            margin: 0;
            letter-spacing: 0.5px;
        }
        .main-subtitle {
            font-size: 9pt;
            color: #475569;
            margin: 2px 0 0 0;
        }

        /* Tabela de Dados e Identificação (Grid) */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .meta-table th, .meta-table td {
            border: 1px solid #cbd5e1;
            padding: 5px 8px;
            font-size: 8.5pt;
        }
        .meta-table th {
            background-color: #f1f5f9;
            color: #0f2942;
            font-weight: bold;
            text-align: left;
            width: 22%;
            text-transform: uppercase;
        }
        .meta-table td {
            background-color: #ffffff;
            color: #1e293b;
        }

        /* Aviso Legal */
        .legal-notice {
            background-color: #f8fafc;
            border-left: 3.5px solid #0f2942;
            border-top: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            padding: 8px 12px;
            font-size: 8.5pt;
            color: #334155;
            margin-bottom: 14px;
            text-align: justify;
            border-radius: 0 3px 3px 0;
        }
        .medidas-list {
            margin: 4px 0 0 16px;
            padding: 0;
            list-style-type: disc;
        }
        .medidas-list li {
            margin-bottom: 2px;
        }

        /* Seções e Caixas */
        .sec-container {
            margin-bottom: 12px;
            page-break-inside: avoid;
        }
        .sec-heading {
            background-color: #f1f5f9;
            color: #0f2942;
            border-left: 3.5px solid #0f2942;
            font-size: 9pt;
            font-weight: bold;
            padding: 4px 8px;
            text-transform: uppercase;
            margin-bottom: 4px;
            letter-spacing: 0.3px;
        }
        .text-box {
            background-color: #fafafa;
            border: 1px solid #e2e8f0;
            border-radius: 2px;
            padding: 7px 10px;
            font-size: 9pt;
            color: #1e293b;
            line-height: 1.4;
            text-align: justify;
        }
        .mt-2 { margin-top: 6px; }

        /* Checklist Table */
        .checklist-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        .checklist-cell {
            width: 50%;
            padding: 4px 6px;
            font-size: 8.5pt;
            vertical-align: middle;
            border: 1px solid #e2e8f0;
            background: #ffffff;
        }
        .checkbox-box {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1.5px solid #0f2942;
            border-radius: 2px;
            text-align: center;
            line-height: 11px;
            font-size: 9px;
            font-weight: bold;
            color: #0f2942;
            margin-right: 6px;
            vertical-align: middle;
        }
        .checkbox-box.checked {
            background-color: #0f2942;
            color: #ffffff;
        }
        .chk-label {
            vertical-align: middle;
        }
        .chk-active {
            color: #0f172a;
            font-weight: 500;
        }
        .chk-inactive {
            color: #94a3b8;
        }

        /* Status Badge */
        .status-badge-box {
            padding: 6px 10px;
            border-radius: 3px;
            font-size: 8.5pt;
            margin-top: 4px;
        }
        .badge-ok {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }
        .badge-alert {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        /* Caixas de Conclusão */
        .conclusion-box {
            background-color: #f0f9ff;
            border: 1.5px solid #0284c7;
            border-radius: 3px;
            padding: 10px 12px;
            margin-top: 14px;
            margin-bottom: 14px;
            page-break-inside: avoid;
        }
        .conclusion-title {
            font-size: 9.5pt;
            font-weight: bold;
            color: #0369a1;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .conclusion-text {
            font-size: 9.5pt;
            font-weight: bold;
            color: #0f2942;
            margin: 0;
            line-height: 1.4;
        }

        /* Foto no resumo */
        .foto-relatorio-container {
            text-align: center;
            margin-top: 8px;
        }
        .foto-relatorio {
            max-width: 100%;
            max-height: 280px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
        }

        /* Assinaturas */
        .signatures-wrapper {
            margin-top: 30px;
            page-break-inside: avoid;
        }
        .sig-table {
            width: 100%;
            border-collapse: collapse;
        }
        .sig-cell {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 10px;
        }
        .sig-cell-single {
            width: 100%;
            text-align: center;
            vertical-align: top;
            padding: 10px;
        }
        .sig-line {
            border-top: 1px solid #334155;
            width: 240px;
            margin: 0 auto 5px auto;
        }
        .sig-name {
            font-weight: bold;
            font-size: 9pt;
            color: #0f2942;
            text-transform: uppercase;
            margin: 0;
        }
        .sig-role {
            font-size: 8pt;
            color: #475569;
            margin: 1px 0 0 0;
        }

        /* Relatório Fotográfico */
        .fotos-section {
            page-break-before: always;
            margin-top: 10px;
        }
        .fotos-header {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            color: #0f2942;
            text-transform: uppercase;
            border-bottom: 2px solid #0f2942;
            padding-bottom: 6px;
            margin-bottom: 16px;
        }
        .foto-card {
            text-align: center;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .foto-card img {
            max-width: 90%;
            max-height: 360px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 3px;
            background-color: #ffffff;
        }
        .foto-caption {
            font-size: 8.5pt;
            color: #475569;
            margin-top: 4px;
            font-style: italic;
        }
    </style>
</head>
<body>
    {{-- Numeração Dinâmica das Páginas --}}
    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->get_font("Helvetica", "normal");
            $size = 8;
            $color = array(0.39, 0.45, 0.55);
            $yPosition = {{ $base64Timbre ? '820' : '810' }};
            $pdf->page_text(485, $yPosition, "Página {PAGE_NUM} de {PAGE_COUNT}", $font, $size, $color);
            @if(!$base64Timbre)
                $pdf->page_text(45, $yPosition, "Módulo de Fiscalização | Emissão: " . date('d/m/Y H:i'), $font, $size, $color);
            @endif
        }
    </script>

    @if($base64Timbre)
        <img class="timbre-bg" src="{{ $base64Timbre }}" alt="Timbre">
    @endif

    {{-- Cabeçalho Fixo --}}
    <header>
        <table class="header-table">
            <tr>
                <td class="header-title-cell">
                    @if(!$base64Timbre)
                        <div class="inst-name">{{ $fiscalizacao->prefeitura->nome ?? 'PREFEITURA MUNICIPAL' }}</div>
                        <div class="inst-sub">MÓDULO DE FISCALIZAÇÃO CONTRATUAL (LEI Nº 14.133/2021)</div>
                    @else
                        <div class="inst-sub">FISCALIZAÇÃO (LEI Nº 14.133/2021)</div>
                    @endif
                </td>
                <td class="doc-badge-cell">
                    @if(!$base64Timbre)
                        <div class="doc-type-badge">RELATÓRIO TÉCNICO</div>
                        <div class="doc-num">FISCALIZAÇÃO Nº {{ $fiscalizacao->numero_fiscalizacao }}</div>
                    @else
                        <div>
                            <span class="doc-type-badge">RELATÓRIO TÉCNICO</span>
                            <span class="doc-num-inline">FISCALIZAÇÃO Nº {{ $fiscalizacao->numero_fiscalizacao }}</span>
                        </div>
                    @endif
                </td>
            </tr>
        </table>
    </header>

    {{-- Conteúdo Principal --}}
    <div class="main-title-container">
        <h1 class="main-title">
            RELATÓRIO TÉCNICO DE FISCALIZAÇÃO CONTRATUAL
            @if($tipo === 'compras') — COMPRAS
            @elseif($tipo === 'servicos') — SERVIÇOS
            @elseif($tipo === 'obras') — OBRAS E ENGENHARIA
            @endif
        </h1>
        <div class="main-subtitle">Documento de instrução técnica submetido ao acompanhamento da Gestão e órgãos de controle</div>
    </div>

    {{-- Tabela Grid de Dados --}}
    <table class="meta-table">
        <tr>
            <th>Nº Fiscalização</th>
            <td><strong>{{ $fiscalizacao->numero_fiscalizacao }}</strong></td>
            <th>Data Inspeção</th>
            <td><strong>{{ $fiscalizacao->data_fiscalizacao->format('d/m/Y') }}</strong></td>
        </tr>
        <tr>
            <th>Contrato</th>
            <td><strong>{{ $info['numero_contrato'] }}</strong></td>
            <th>Tipo Objeto</th>
            <td>{{ strtoupper($tipo ?? 'Geral') }}</td>
        </tr>
        <tr>
            <th>Contratada</th>
            <td colspan="3"><strong>{{ $info['razao_social'] }}</strong> &nbsp;(CNPJ/CPF: {{ $info['cnpj'] }})</td>
        </tr>
        <tr>
            <th>Objeto Contratual</th>
            <td colspan="3">{{ $info['objeto'] }}</td>
        </tr>
        <tr>
            <th>Fiscal Responsável</th>
            <td>{{ $fiscalizacao->user->name }}</td>
            <th>Unidade / Órgão</th>
            <td>{{ $fiscalizacao->prefeitura->nome ?? '—' }}</td>
        </tr>
    </table>

    {{-- Legal Notice --}}
    <div class="legal-notice">
        <strong>Fundamentação Legal e Procedimentos:</strong><br>
        A presente fiscalização contratual foi executada em estrita observância aos ditames da <strong>Lei Federal nº 14.133/2021</strong> e normativos vigentes, valendo-se das seguintes etapas técnicas de instrução:
        <ul class="medidas-list">
            <li>Análise documental e contábil (notas fiscais, relatórios de execução, medições e comprovações);</li>
            <li>Vistoria presencial e verificação <em>in loco</em> (registro fotográfico e aferição física das entregas/serviços);</li>
            <li>Acompanhamento de diretrizes e alinhamento técnico com os representantes da contratada;</li>
            <li>Entrevistas e oitiva dos servidores e equipes operacionais envolvidas no local de execução.</li>
        </ul>
    </div>

    {{-- Resumo Técnico Partial --}}
    @include('Admin.Fiscalizacoes.partials._resumo_tecnico')

    {{-- Conclusão Formal --}}
    <div class="conclusion-box">
        <div class="conclusion-title">&bull; Parecer e Conclusão Técnica do Fiscal:</div>
        <p class="conclusion-text">{{ $fiscalizacao->conclusao_texto }}</p>
    </div>

    @if($base64Foto)
        <div class="sec-container">
            <div class="sec-heading">REGISTRO FOTOGRÁFICO DE DESTAQUE</div>
            <div class="foto-relatorio-container">
                <img class="foto-relatorio" src="{{ $base64Foto }}" alt="Relatório Fotográfico">
            </div>
        </div>
    @endif

    {{-- Assinaturas --}}
    <div class="signatures-wrapper">
        @php
            $assinantesLista = $fiscalizacao->assinantes ?? [];
        @endphp

        @if(!empty($assinantesLista))
            <table class="sig-table">
                @foreach(array_chunk($assinantesLista, 2) as $rowAssinantes)
                    <tr>
                        @foreach($rowAssinantes as $ass)
                            <td class="sig-cell">
                                <div class="sig-line"></div>
                                <p class="sig-name">{{ $ass['nome'] ?? '' }}</p>
                                @php
                                    $cargo = trim($ass['cargo'] ?? '') ?: 'Fiscal de Contrato';
                                    $detalhe = array_filter([$cargo, $ass['unidade'] ?? null]);
                                @endphp
                                @if(!empty($detalhe))
                                    <p class="sig-role">{{ implode(' — ', $detalhe) }}</p>
                                @endif
                            </td>
                        @endforeach
                        @if(count($rowAssinantes) === 1)
                            <td class="sig-cell"></td>
                        @endif
                    </tr>
                @endforeach
            </table>
        @else
            <div class="sig-cell-single">
                <div class="sig-line"></div>
                <p class="sig-name">{{ $fiscalizacao->user->name }}</p>
                <p class="sig-role">Fiscal do Contrato / Responsável Técnico</p>
            </div>
        @endif
    </div>

    {{-- Relatório Fotográfico --}}
    @if($fiscalizacao->fotos->isNotEmpty())
        <div class="fotos-section">
            <div class="fotos-header">Anexo — Relatório Fotográfico Integrante</div>
            @foreach($fiscalizacao->fotos as $foto)
                @php
                    $fotoAbs = public_path($foto->caminho);
                    $fotoB64 = '';
                    if ($foto->caminho && file_exists($fotoAbs)) {
                        $ext = pathinfo($fotoAbs, PATHINFO_EXTENSION);
                        $fotoB64 = 'data:image/' . $ext . ';base64,' . base64_encode(file_get_contents($fotoAbs));
                    }
                @endphp
                @if($fotoB64)
                    <div class="foto-card">
                        <img src="{{ $fotoB64 }}" alt="Foto {{ $loop->iteration }}">
                        @if(!empty($foto->legenda))
                            <div class="foto-caption">Registro {{ $loop->iteration }}: {{ $foto->legenda }}</div>
                        @else
                            <div class="foto-caption">Registro Fotográfico de Vistoria {{ $loop->iteration }}</div>
                        @endif
                    </div>
                @endif
            @endforeach
        </div>
    @endif
</body>
</html>