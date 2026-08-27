<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Notificações - Ocorrência {{ $ocorrencia->numero_ocorrencia }}</title>
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
        $contrato = $ocorrencia->fiscalizavel;

        $nomeSecretario = '—';
        if ($contrato instanceof \App\Models\ContratoManual) {
            $nomeSecretario = $contrato->secretaria->servidor_responsavel ?? '—';
        } elseif ($contrato instanceof \App\Models\Contrato) {
            $nomeSecretario = $contrato->processo->detalhe->servidor_responsavel
                             ?? $contrato->processo->finalizacao->responsavel
                             ?? '—';
        }

        $dataExtenso = $ocorrencia->data_ocorrencia?->translatedFormat('d \d\e F \d\e Y') ?? date('d/m/Y');
    @endphp
    <style>
        @page {
            size: A4 portrait;
            margin: {{ $base64Timbre ? '70mm' : '32mm' }} 15mm {{ $base64Timbre ? '40mm' : '20mm' }} 15mm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9pt;
            line-height: 1.3;
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
            margin-bottom: 8px;
            padding-bottom: 3px;
            border-bottom: 1px solid #e2e8f0;
        }
        .main-title {
            font-size: 11.5pt;
            font-weight: bold;
            color: #0f2942;
            text-transform: uppercase;
            margin: 0;
            letter-spacing: 0.5px;
        }
        .main-subtitle {
            font-size: 8pt;
            color: #475569;
            margin: 1px 0 0 0;
        }

        /* Card de Destinatário */
        .card-destinatario {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-left: 4px solid #0f2942;
            border-radius: 3px;
            padding: 6px 9px;
            margin-bottom: 8px;
            font-size: 8.5pt;
        }
        .card-destinatario table {
            width: 100%;
            border-collapse: collapse;
        }
        .card-destinatario td {
            padding: 1px 0;
            vertical-align: top;
        }
        .label-dest {
            font-weight: bold;
            color: #0f2942;
            width: 100px;
            text-transform: uppercase;
            font-size: 8pt;
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
            margin-top: 6px;
            margin-bottom: 3px;
            letter-spacing: 0.3px;
        }
        .text-box {
            background-color: #fafafa;
            border: 1px solid #e2e8f0;
            border-radius: 2px;
            padding: 4px 7px;
            font-size: 8.5pt;
            color: #1e293b;
            line-height: 1.3;
            text-align: justify;
        }
        .alert-box {
            background-color: #fff1f2;
            border: 1px solid #fecdd3;
            border-left: 4px solid #e11d48;
            padding: 4px 7px;
            border-radius: 2px;
            color: #881337;
            font-size: 8.5pt;
            margin: 4px 0;
        }
        .mt-2 { margin-top: 3px; }
        .mt-4 { margin-top: 6px; }
        .indent { text-indent: 14px; text-align: justify; margin: 2.5px 0; }

        .medidas-list {
            margin: 2px 0 0 14px;
            padding: 0;
            list-style-type: disc;
        }
        .medidas-list li {
            margin-bottom: 1px;
        }

        /* Assinaturas */
        .signatures-wrapper {
            margin-top: 6px;
            page-break-inside: avoid;
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
                $pdf->page_text(45, $yPosition, "Documento de Notificação | Emissão: " . date('d/m/Y H:i'), $font, $size, $color);
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
                        <div class="doc-type-badge">NOTIFICAÇÕES</div>
                        <div class="doc-num">OCORRÊNCIA Nº {{ $ocorrencia->numero_ocorrencia }}</div>
                    @else
                        <div>
                            <span class="doc-type-badge">NOTIFICAÇÕES</span>
                            <span class="doc-num-inline">Nº {{ $ocorrencia->numero_ocorrencia }}</span>
                        </div>
                    @endif
                </td>
            </tr>
        </table>
    </header>

    {{-- ========================================== --}}
    {{-- PÁGINA 1: COMUNICAÇÃO AO GESTOR            --}}
    {{-- ========================================== --}}
    <div class="main-title-container">
        <h1 class="main-title">COMUNICAÇÃO INTERNA DE OCORRÊNCIA</h1>
        <div class="main-subtitle">Notificação técnica dirigida à Gestão da Secretaria</div>
    </div>

    <div class="card-destinatario">
        <table>
            <tr>
                <td class="label-dest">Ao(À) Sr.(a):</td>
                <td><strong>{{ $nomeSecretario }}</strong><br>Secretário(a) / Gestor(a) da {{ mb_strtoupper($ocorrencia->prefeitura->nome ?? 'Prefeitura') }}</td>
            </tr>
            <tr>
                <td class="label-dest">De:</td>
                <td>Fiscal do Contrato nº <strong>{{ $info['numero_contrato'] }}</strong></td>
            </tr>
            <tr>
                <td class="label-dest">Assunto:</td>
                <td>Comunicação formal de Ocorrência nº <strong>{{ $ocorrencia->numero_ocorrencia }}</strong></td>
            </tr>
        </table>
    </div>

    <p class="indent">
        Senhor(a) Secretário(a), em cumprimento às atribuições legais previstas na <strong>Lei Federal nº 14.133/2021</strong> e no ato de designação de fiscalização do Contrato nº <strong>{{ $info['numero_contrato'] }}</strong>, firmado com a empresa <strong>{{ $info['razao_social'] }}</strong>, cujo objeto é <em>"{{ $info['objeto'] }}"</em>, comunico a V. Sa. a ocorrência abaixo identificada durante o acompanhamento da execução contratual.
    </p>

    <div class="section-header">1. Ocorrência Identificada</div>
    <div class="text-box">
        {!! nl2br(e($ocorrencia->descricao_fato)) !!}
    </div>

    <div class="section-header">2. Obrigação Descumprida e Prazo de Solução</div>
    <div class="text-box">
        <strong>Obrigação Constatada:</strong> {{ $ocorrencia->obrigacao_descumprida ?? 'Sem obrigação específica registrada até o momento.' }}
    </div>
    @if($ocorrencia->prazo_resposta)
        <div class="text-box mt-2">
            <strong>Prazo Estabelecido para Resposta / Solução:</strong> {{ $ocorrencia->prazo_resposta }}
        </div>
    @endif

    <p class="indent mt-2">
        Considerando as atribuições legais da fiscalização contratual e visando assegurar a correta execução do objeto, a economicidade e o interesse público, encaminha-se o presente documento para conhecimento e providências administrativas cabíveis.
    </p>

    <p class="mt-4" style="text-align: right;">
        {{ $ocorrencia->prefeitura->cidade ?? 'Local' }}, {{ $dataExtenso }}
    </p>

    <div class="signatures-wrapper">
        <table style="width: 100%;">
            <tr>
                <td class="sig-cell-single">
                    <div class="sig-line"></div>
                    <p class="sig-name">{{ $ocorrencia->user->name ?? 'FISCAL DO CONTRATO' }}</p>
                    <p class="sig-role">Fiscal do Contrato Administrativo</p>
                </td>
            </tr>
        </table>
    </div>

    {{-- ========================================== --}}
    {{-- PÁGINA 2: NOTIFICAÇÃO À EMPRESA CONTRATADA --}}
    {{-- ========================================== --}}
    <div class="page-break"></div>

    @if($base64Timbre)
        <img class="timbre-bg" src="{{ $base64Timbre }}" alt="Timbre">
    @endif

    <div class="main-title-container">
        <h1 class="main-title">NOTIFICAÇÃO FORMAL À CONTRATADA</h1>
        <div class="main-subtitle">Comunicação oficial de irregularidade e notificação para regularização</div>
    </div>

    <div class="card-destinatario">
        <table>
            <tr>
                <td class="label-dest">À Empresa:</td>
                <td><strong>{{ $info['razao_social'] }}</strong> (CNPJ: {{ $info['cnpj'] }})<br>Endereço: {{ $info['endereco'] }}</td>
            </tr>
            <tr>
                <td class="label-dest">Ref. Contrato:</td>
                <td>Contrato Administrativo nº <strong>{{ $info['numero_contrato'] }}</strong></td>
            </tr>
            <tr>
                <td class="label-dest">Objeto:</td>
                <td>{{ $info['objeto'] }}</td>
            </tr>
        </table>
    </div>

    <p class="indent">
        Prezados Senhores, na qualidade de Fiscal do Contrato nº <strong>{{ $info['numero_contrato'] }}</strong>, designado para o exercício das atribuições de acompanhamento e fiscalização da execução contratual, venho por meio deste <strong>NOTIFICAR</strong> vossa empresa quanto à ocorrência abaixo discriminada.
    </p>

    <div class="section-header">1. Situação Verificada</div>
    <div class="text-box">
        {!! nl2br(e($ocorrencia->descricao_fato)) !!}
    </div>

    <div class="section-header">2. Obrigação Descumprida e Prazo para Regularização</div>
    <p class="indent">
        Diante do exposto, <strong>NOTIFICA-SE</strong> a contratada para que adote imediatamente as providências necessárias à regularização da seguinte obrigação:
    </p>
    <div class="text-box mt-2">
        {{ $ocorrencia->obrigacao_descumprida ?? 'Conforme cláusulas contratuais aplicáveis e normas legais vigentes.' }}
    </div>

    @if($ocorrencia->prazo_resposta)
        <div class="alert-box">
            <strong>Prazo Improrrogável para Resposta / Solução:</strong> {{ $ocorrencia->prazo_resposta }}
        </div>
    @endif

    <p class="indent mt-2">
        O não atendimento a esta notificação dentro do prazo estabelecido poderá ensejar a adoção das medidas administrativas e sanções cabíveis, nos termos da <strong>Lei Federal nº 14.133/2021</strong>, incluindo:
    </p>

    <div class="text-box">
        <ul class="medidas-list">
            <li>Aplicação de advertência e multas contratuais;</li>
            <li>Suspensão temporária de pagamentos referentes às parcelas pendentes;</li>
            <li>Rescisão unilateral do contrato administrativo;</li>
            <li>Demais sanções legais previstas na Lei nº 14.133/2021 e no instrumento convocatório.</li>
        </ul>
    </div>

    <p class="indent mt-2">
        Solicita-se que a empresa apresente manifestação formal acerca desta notificação dentro do prazo estipulado, protocolando a documentação comprobatória das providências adotadas.
    </p>

    <p class="mt-4" style="text-align: right;">
        {{ $ocorrencia->prefeitura->cidade ?? 'Local' }}, {{ $dataExtenso }}
    </p>

    <div class="signatures-wrapper">
        <table style="width: 100%;">
            <tr>
                <td class="sig-cell-single">
                    <div class="sig-line"></div>
                    <p class="sig-name">{{ $ocorrencia->user->name ?? 'FISCAL DO CONTRATO' }}</p>
                    <p class="sig-role">Fiscal do Contrato Administrativo</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
