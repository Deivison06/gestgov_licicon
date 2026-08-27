<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Atesto de Correção - Ocorrência {{ $ocorrencia->numero_ocorrencia }}</title>
    @php
        $timbre = $ocorrencia->prefeitura->timbre ?? '';
        $timbrePath = public_path($timbre);
        $base64Timbre = '';
        if ($timbre && file_exists($timbrePath)) {
            $type = pathinfo($timbrePath, PATHINFO_EXTENSION);
            $data = file_get_contents($timbrePath);
            $base64Timbre = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        $info = $ocorrencia->contrato_info;
        $dataExtenso = now()->translatedFormat('d \d\e F \d\e Y');
    @endphp
    <style>
        @page {
            size: A4 portrait;
            margin: {{ $base64Timbre ? '70mm' : '32mm' }} 15mm {{ $base64Timbre ? '40mm' : '20mm' }} 15mm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9.5pt;
            line-height: 1.45;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }

        .timbre-bg {
            position: fixed;
            top: -{{ $base64Timbre ? '70mm' : '32mm' }};
            left: -15mm;
            width: 210mm;
            height: 297mm;
            z-index: -1000;
        }

        /* Cabeçalho Fixo */
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

        /* Títulos */
        .main-title-container {
            text-align: center;
            margin-bottom: 12px;
            padding-bottom: 4px;
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
            font-size: 8.5pt;
            color: #475569;
            margin: 2px 0 0 0;
        }

        /* Tabela de Dados (Meta) */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .meta-table th, .meta-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 7px;
            font-size: 8.5pt;
        }
        .meta-table th {
            background-color: #f1f5f9;
            color: #0f2942;
            font-weight: bold;
            text-align: left;
            text-transform: uppercase;
            width: 25%;
        }
        .meta-table td {
            background-color: #ffffff;
            color: #1e293b;
        }

        /* Seções de Conteúdo */
        .section-header {
            background-color: #f1f5f9;
            border-left: 3.5px solid #0f2942;
            padding: 3px 6px;
            font-size: 8.5pt;
            font-weight: bold;
            color: #0f2942;
            text-transform: uppercase;
            margin-top: 8px;
            margin-bottom: 4px;
            letter-spacing: 0.3px;
        }
        .text-box {
            background-color: #fafafa;
            border: 1px solid #e2e8f0;
            border-radius: 2px;
            padding: 5px 8px;
            font-size: 8.5pt;
            color: #1e293b;
            line-height: 1.35;
            text-align: justify;
        }
        .card-conclusao {
            background-color: #f0fdf4;
            border: 1.5px solid #bbf7d0;
            border-left: 4px solid #16a34a;
            border-radius: 3px;
            padding: 6px 9px;
            margin-top: 6px;
            margin-bottom: 6px;
            font-size: 8.5pt;
            color: #14532d;
            line-height: 1.35;
            text-align: justify;
        }
        .indent { text-indent: 16px; text-align: justify; margin: 3px 0; }
        .mt-2 { margin-top: 4px; }
        .mt-4 { margin-top: 8px; }

        /* Fotografias / Anexos */
        .anexos-grid {
            width: 100%;
            margin-top: 4px;
        }
        .anexo-card {
            display: inline-block;
            width: 48%;
            margin-right: 2%;
            margin-bottom: 6px;
            vertical-align: top;
            box-sizing: border-box;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 4px;
            background-color: #ffffff;
            text-align: center;
        }
        .anexo-card img {
            max-width: 100%;
            max-height: 110px;
            object-fit: contain;
            border-radius: 2px;
        }
        .anexo-card-legend {
            font-size: 7.5pt;
            color: #475569;
            margin-top: 2px;
            font-weight: 500;
        }

        /* Assinaturas */
        .signatures-wrapper {
            margin-top: 15px;
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
            padding: 8px;
        }
        .sig-cell-single {
            width: 100%;
            text-align: center;
            vertical-align: top;
            padding: 8px;
        }
        .sig-line {
            border-top: 1px solid #334155;
            width: 220px;
            margin: 0 auto 4px auto;
        }
        .sig-name {
            font-weight: bold;
            font-size: 8.5pt;
            color: #0f2942;
            text-transform: uppercase;
            margin: 0;
        }
        .sig-role {
            font-size: 7.5pt;
            color: #475569;
            margin: 1px 0 0 0;
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
                $pdf->page_text(45, $yPosition, "Atesto de Correção | Emissão: " . date('d/m/Y H:i'), $font, $size, $color);
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
                        <div class="inst-name">{{ $ocorrencia->prefeitura->nome ?? 'PREFEITURA MUNICIPAL' }}</div>
                        <div class="inst-sub">MÓDULO DE FISCALIZAÇÃO CONTRATUAL (LEI Nº 14.133/2021)</div>
                    @else
                        <div class="inst-sub">FISCALIZAÇÃO (LEI Nº 14.133/2021)</div>
                    @endif
                </td>
                <td class="doc-badge-cell">
                    @if(!$base64Timbre)
                        <div class="doc-type-badge">ATESTO DE CORREÇÃO</div>
                        <div class="doc-num">OCORRÊNCIA Nº {{ $ocorrencia->numero_ocorrencia }}</div>
                    @else
                        <div>
                            <span class="doc-type-badge">ATESTO DE CORREÇÃO</span>
                            <span class="doc-num-inline">Nº {{ $ocorrencia->numero_ocorrencia }}</span>
                        </div>
                    @endif
                </td>
            </tr>
        </table>
    </header>

    {{-- Título Principal --}}
    <div class="main-title-container">
        <h1 class="main-title">ATESTO DE CORREÇÃO DE OCORRÊNCIA</h1>
        <div class="main-subtitle">Declaração técnica de regularização e saneamento de anomalia contratual</div>
    </div>

    {{-- Tabela Grid de Dados --}}
    <table class="meta-table">
        <tr>
            <th>Nº Ocorrência</th>
            <td><strong>{{ $ocorrencia->numero_ocorrencia }}</strong></td>
            <th>Data do Atesto</th>
            <td>{{ $ocorrencia->correcao_data?->format('d/m/Y') ?? date('d/m/Y') }}</td>
        </tr>
        <tr>
            <th>Contrato</th>
            <td>{{ $info['numero_contrato'] }}</td>
            <th>Contratada</th>
            <td><strong>{{ $info['razao_social'] }}</strong> ({{ $info['cnpj'] }})</td>
        </tr>
        <tr>
            <th>Objeto Contratual</th>
            <td colspan="3">{{ $info['objeto'] }}</td>
        </tr>
        <tr>
            <th>Fiscal Responsável</th>
            <td colspan="3">{{ $ocorrencia->user->name ?? '—' }}</td>
        </tr>
    </table>

    {{-- Seção 1: Da Irregularidade Identificada --}}
    <div class="section-header">1. Da Irregularidade Identificada</div>
    <div class="text-box">
        <p style="margin: 0 0 4px 0;">Durante o acompanhamento e fiscalização da execução do Contrato nº <strong>{{ $info['numero_contrato'] }}</strong>, foi identificada a seguinte ocorrência:</p>
        <p style="margin: 0 0 4px 0;"><strong>Descrição do Fato:</strong> {!! nl2br(e($ocorrencia->descricao_fato)) !!}</p>
        <p style="margin: 0;"><strong>Data da Constatação:</strong> {{ $ocorrencia->data_ocorrencia?->format('d/m/Y') ?? '—' }}</p>
    </div>

    <p class="indent mt-2">
        Em razão da ocorrência, a empresa contratada foi formalmente comunicada por meio da Notificação
        @if($ocorrencia->notificacao_numero)
            nº <strong>{{ $ocorrencia->notificacao_numero }}</strong>,
        @endif
        expedida em <strong>{{ $ocorrencia->notificacao_expedida_em?->format('d/m/Y') ?? $ocorrencia->data_ocorrencia?->format('d/m/Y') }}</strong>, para que adotasse as providências necessárias à sanidade do objeto.
    </p>

    {{-- Seção 2: Da Correção Realizada --}}
    <div class="section-header">2. Da Correção Realizada pela Contratada</div>
    <div class="text-box">
        <p style="margin: 0 0 4px 0;">Após a notificação, a empresa contratada adotou as seguintes providências corretivas:</p>
        <p style="margin: 0 0 4px 0;"><strong>Providências:</strong> {!! nl2br(e($ocorrencia->correcao_descricao)) !!}</p>
        <p style="margin: 0;"><strong>Data Efetiva da Correção:</strong> {{ $ocorrencia->correcao_data?->format('d/m/Y') ?? '—' }}</p>
    </div>

    @if($ocorrencia->correcao_elementos_comprobatorios)
        <div class="text-box mt-2">
            <strong>Elementos Comprobatórios Apresentados:</strong><br>
            {!! nl2br(e($ocorrencia->correcao_elementos_comprobatorios)) !!}
        </div>
    @endif

    {{-- Fotografias / Documentos da Correção --}}
    @php
        $anexosCorrecao = $ocorrencia->anexosCorrecao()->get();
        $imagensCorrecao = $anexosCorrecao->filter(fn ($a) => in_array(strtolower(pathinfo($a->caminho, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp']));
        $documentosCorrecao = $anexosCorrecao->diff($imagensCorrecao);
    @endphp

    @if($anexosCorrecao->isNotEmpty())
        <div class="section-header" style="margin-top: 8px;">Comprovação Gráfica / Anexos do Saneamento</div>
        @if($imagensCorrecao->isNotEmpty())
            <div class="anexos-grid">
                @foreach($imagensCorrecao as $anexo)
                    @php
                        $caminhoAbsAnexo = public_path($anexo->caminho);
                        $anexoBase64 = '';
                        if (file_exists($caminhoAbsAnexo)) {
                            $extAnexo = pathinfo($caminhoAbsAnexo, PATHINFO_EXTENSION);
                            $anexoBase64 = 'data:image/' . $extAnexo . ';base64,' . base64_encode(file_get_contents($caminhoAbsAnexo));
                        }
                    @endphp
                    @if($anexoBase64)
                        <div class="anexo-card">
                            <img src="{{ $anexoBase64 }}" alt="{{ $anexo->nome_original }}">
                            <div class="anexo-card-legend">{{ $anexo->nome_original ?? basename($anexo->caminho) }}</div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

        @if($documentosCorrecao->isNotEmpty())
            <div class="text-box" style="margin-top: 4px;">
                <strong>Documentos Complementares Anexados à Correção:</strong>
                <ul style="margin: 3px 0 0 15px; padding: 0;">
                    @foreach($documentosCorrecao as $anexo)
                        <li>{{ $anexo->nome_original ?? basename($anexo->caminho) }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    @endif

    {{-- Seção 3: Do Atesto do Fiscal --}}
    <div class="section-header">3. Do Atesto Técnico da Fiscalização</div>
    <div class="card-conclusao">
        <p style="margin: 0 0 4px 0;"><strong>PARECER DE SANEAMENTO:</strong></p>
        <p style="margin: 0 0 4px 0;">
            Após nova verificação <em>in loco</em> e análise da documentação acostada, <strong>ATESTO</strong> que a irregularidade apontada
            @if($ocorrencia->notificacao_numero)
                na Notificação nº <strong>{{ $ocorrencia->notificacao_numero }}</strong>
            @endif
            foi devidamente corrigida e sanada pela empresa contratada, encontrando-se a execução contratual regularizada no que tange a esta anomalia.
        </p>
        <p style="margin: 0;">
            Dessa forma, considero <strong>SANADA A IRREGULARIDADE</strong> para fins de acompanhamento da execução do objeto. O presente atesto não exime a contratada de suas responsabilidades legais e garantias técnicas contratuais.
        </p>
    </div>

    <p class="mt-4" style="text-align: right;">
        {{ $ocorrencia->prefeitura->cidade ?? 'Local' }}, {{ $dataExtenso }}
    </p>

    {{-- Seção de Assinaturas --}}
    <div class="signatures-wrapper">
        @if(!empty($ocorrencia->assinantes))
            <table class="sig-table">
                @foreach(array_chunk($ocorrencia->assinantes, 2) as $linhaAssinantes)
                    <tr>
                        @foreach($linhaAssinantes as $assinante)
                            <td class="{{ count($linhaAssinantes) == 1 ? 'sig-cell-single' : 'sig-cell' }}">
                                <div class="sig-line"></div>
                                <p class="sig-name">{{ $assinante['nome'] ?? '' }}</p>
                                @php
                                    $cargo = trim($assinante['cargo'] ?? '') ?: 'Fiscal de Contrato';
                                    $detalhe = array_filter([$cargo, $assinante['unidade'] ?? null]);
                                @endphp
                                @if(!empty($detalhe))
                                    <p class="sig-role">{{ implode(' — ', $detalhe) }}</p>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </table>
        @else
            <table class="sig-table">
                <tr>
                    <td class="sig-cell-single">
                        <div class="sig-line"></div>
                        <p class="sig-name">{{ $ocorrencia->user->name ?? 'FISCAL DO CONTRATO' }}</p>
                        <p class="sig-role">Fiscal do Contrato Administrativo</p>
                    </td>
                </tr>
            </table>
        @endif
    </div>
</body>
</html>
