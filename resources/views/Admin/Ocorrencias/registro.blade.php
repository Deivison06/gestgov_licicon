<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Registro de Ocorrência - {{ $ocorrencia->numero_ocorrencia }}</title>
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

        .page-break {
            page-break-after: always;
        }

        /* Títulos */
        .main-title-container {
            text-align: center;
            margin-bottom: 14px;
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

        /* Tabela de Dados (Meta) */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .meta-table th, .meta-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 9px;
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
            padding: 5px 8px;
            font-size: 9.5pt;
            font-weight: bold;
            color: #0f2942;
            text-transform: uppercase;
            margin-top: 14px;
            margin-bottom: 8px;
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
        .alert-box {
            background-color: #fff1f2;
            border: 1px solid #fecdd3;
            border-left: 4px solid #e11d48;
            padding: 8px 12px;
            border-radius: 2px;
            color: #881337;
            font-size: 9pt;
            margin: 8px 0;
        }

        /* Checklist estilizado */
        .checklist-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        .checklist-table td {
            border: 1px solid #e2e8f0;
            padding: 5px 8px;
            font-size: 8.5pt;
            vertical-align: middle;
        }
        .checkbox-box {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1.5px solid #0f2942;
            border-radius: 2px;
            text-align: center;
            line-height: 11px;
            font-size: 8.5pt;
            font-weight: bold;
            color: #ffffff;
            margin-right: 6px;
            vertical-align: middle;
        }
        .checkbox-box.checked {
            background-color: #0f2942;
        }
        .checklist-pendente {
            color: #94a3b8;
        }

        /* Fotografias / Anexos */
        .anexos-grid {
            width: 100%;
            margin-top: 6px;
        }
        .anexo-card {
            display: inline-block;
            width: 48%;
            margin-right: 2%;
            margin-bottom: 8px;
            vertical-align: top;
            box-sizing: border-box;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 5px;
            background-color: #ffffff;
            text-align: center;
        }
        .anexo-card img {
            max-width: 100%;
            max-height: 120px;
            object-fit: contain;
            border-radius: 2px;
        }
        .anexo-card-legend {
            font-size: 8pt;
            color: #475569;
            margin-top: 4px;
            font-weight: 500;
        }

        /* Assinaturas */
        .signatures-wrapper {
            margin-top: 25px;
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
            width: 220px;
            margin: 0 auto 5px auto;
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
                        <div class="inst-name">{{ $ocorrencia->prefeitura->nome ?? 'PREFEITURA MUNICIPAL' }}</div>
                        <div class="inst-sub">MÓDULO DE FISCALIZAÇÃO CONTRATUAL (LEI Nº 14.133/2021)</div>
                    @else
                        <div class="inst-sub">FISCALIZAÇÃO (LEI Nº 14.133/2021)</div>
                    @endif
                </td>
                <td class="doc-badge-cell">
                    @if(!$base64Timbre)
                        <div class="doc-type-badge">REGISTRO DE OCORRÊNCIA</div>
                        <div class="doc-num">OCORRÊNCIA Nº {{ $ocorrencia->numero_ocorrencia }}</div>
                    @else
                        <div>
                            <span class="doc-type-badge">REGISTRO DE OCORRÊNCIA</span>
                            <span class="doc-num-inline">Nº {{ $ocorrencia->numero_ocorrencia }}</span>
                        </div>
                    @endif
                </td>
            </tr>
        </table>
    </header>

    {{-- Título Principal --}}
    <div class="main-title-container">
        <h1 class="main-title">REGISTRO DE OCORRÊNCIA CONTRATUAL</h1>
        <div class="main-subtitle">Registro formal de fatos e anomalias identificados na execução do contrato</div>
    </div>

    {{-- Tabela Grid de Dados --}}
    <table class="meta-table">
        <tr>
            <th>Nº Ocorrência</th>
            <td><strong>{{ $ocorrencia->numero_ocorrencia }}</strong></td>
            <th>Data da Ocorrência</th>
            <td>{{ $ocorrencia->data_ocorrencia?->format('d/m/Y') ?? '—' }}</td>
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
            <th>Local do Fato</th>
            <td>{{ $ocorrencia->local ?? 'Instalações / Local da Obra/Serviço' }}</td>
            <th>Fiscal Responsável</th>
            <td>{{ $ocorrencia->user->name ?? '—' }}</td>
        </tr>
    </table>

    {{-- Seção 1: Descrição do Fato --}}
    <div class="section-header">1. Descrição Detalhada do Fato / Irregularidade</div>
    <div class="text-box">
        {!! nl2br(e($ocorrencia->descricao_fato)) !!}
    </div>

    {{-- Fotografias / Documentos --}}
    @php
        $anexosFato = $ocorrencia->anexos->where('categoria', 'fato');
        $imagensFato = $anexosFato->filter(fn ($a) => in_array(strtolower(pathinfo($a->caminho, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp']));
        $documentosFato = $anexosFato->diff($imagensFato);
    @endphp

    @if($anexosFato->isNotEmpty())
        <div class="section-header" style="margin-top: 10px;">Elementos Comprobatórios (Anexos do Fato)</div>
        @if($imagensFato->isNotEmpty())
            <div class="anexos-grid">
                @foreach($imagensFato as $anexo)
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

        @if($documentosFato->isNotEmpty())
            <div class="text-box" style="margin-top: 4px;">
                <strong>Documentos Complementares Anexados:</strong>
                <ul style="margin: 3px 0 0 15px; padding: 0;">
                    @foreach($documentosFato as $anexo)
                        <li>{{ $anexo->nome_original ?? basename($anexo->caminho) }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    @endif

    {{-- Seção 2: Obrigações e Enquadramento --}}
    <div class="section-header">2. Enquadramento e Providências Exigidas</div>

    @if($ocorrencia->obrigacao_descumprida)
        <div class="alert-box">
            <strong>Obrigação Contratual Descumprida:</strong><br>
            {{ $ocorrencia->obrigacao_descumprida }}
        </div>
    @endif

    @if($ocorrencia->prazo_resposta)
        <div class="text-box" style="margin-bottom: 8px;">
            <strong>Prazo para Resposta / Solução Exigido:</strong> {{ $ocorrencia->prazo_resposta }}
        </div>
    @endif

    @if(!empty($ocorrencia->tipo_comprovacao))
        <div style="font-weight: bold; font-size: 8.5pt; color: #0f2942; margin-top: 6px; margin-bottom: 2px;">
            MEIOS DE COMPROVAÇÃO UTILIZADOS:
        </div>
        <table class="checklist-table">
            @foreach(collect(\App\Models\Ocorrencia::TIPOS_COMPROVACAO)->chunk(2) as $par)
                <tr>
                    @foreach($par as $chave => $rotulo)
                        @php $marcado = (bool) data_get($ocorrencia->tipo_comprovacao, $chave); @endphp
                        <td style="width: 50%;">
                            <span class="checkbox-box {{ $marcado ? 'checked' : '' }}">{{ $marcado ? 'X' : '' }}</span>
                            <span class="{{ $marcado ? '' : 'checklist-pendente' }}">
                                {{ $chave === 'outros' && $ocorrencia->tipo_comprovacao_outro ? $rotulo.': '.$ocorrencia->tipo_comprovacao_outro : $rotulo }}
                            </span>
                        </td>
                    @endforeach
                    @if($par->count() < 2)
                        <td style="width: 50%;"></td>
                    @endif
                </tr>
            @endforeach
        </table>
    @endif

    <div style="font-weight: bold; font-size: 8.5pt; color: #0f2942; margin-top: 8px; margin-bottom: 2px;">
        SITUAÇÃO ATUAL DA OCORRÊNCIA:
    </div>
    <table class="checklist-table">
        <tr>
            @foreach(\App\Enums\SituacaoOcorrenciaEnum::cases() as $opcaoSituacao)
                @php $marcadoSituacao = $ocorrencia->situacao === $opcaoSituacao; @endphp
                <td style="width: 33.3%;">
                    <span class="checkbox-box {{ $marcadoSituacao ? 'checked' : '' }}">{{ $marcadoSituacao ? 'X' : '' }}</span>
                    <span class="{{ $marcadoSituacao ? '' : 'checklist-pendente' }}">{{ $opcaoSituacao->getDisplayName() }}</span>
                </td>
            @endforeach
        </tr>
    </table>

    {{-- Seção 3: Assinaturas Institucionais --}}
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
