<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Notificações - {{ $fiscalizacao->numero_fiscalizacao }}</title>
    @php
        $timbre = $fiscalizacao->prefeitura->timbre ?? '';
        $timbrePath = public_path($timbre);
        $base64Timbre = '';
        if ($timbre && file_exists($timbrePath)) {
            $type = pathinfo($timbrePath, PATHINFO_EXTENSION);
            $data = file_get_contents($timbrePath);
            $base64Timbre = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        $info = $fiscalizacao->contrato_info;
        $contrato = $fiscalizacao->fiscalizavel;

        $nomeSecretario = '—';
        if ($contrato instanceof \App\Models\ContratoManual) {
            $nomeSecretario = $contrato->secretaria->servidor_responsavel ?? '—';
        } elseif ($contrato instanceof \App\Models\Contrato) {
            $nomeSecretario = $contrato->processo->detalhe->servidor_responsavel
                             ?? $contrato->processo->finalizacao->responsavel
                             ?? '—';
        }
    @endphp
    <style>
        @page {
            size: A4 portrait;
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
            vertical-align: middle;
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

        /* Destinatários */
        .card-destinatario {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-left: 4px solid #0f2942;
            padding: 10px 12px;
            border-radius: 2px;
            margin-bottom: 14px;
            font-size: 9pt;
        }
        .card-destinatario table {
            width: 100%;
            border-collapse: collapse;
        }
        .card-destinatario td {
            padding: 3px 0;
            vertical-align: top;
        }
        .label-dest {
            font-weight: bold;
            color: #0f2942;
            width: 100px;
            text-transform: uppercase;
            font-size: 8.5pt;
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
        .mt-2 { margin-top: 6px; }
        .mt-4 { margin-top: 14px; }
        .indent { text-indent: 20px; text-align: justify; margin: 6px 0; }

        .medidas-list {
            margin: 4px 0 0 16px;
            padding: 0;
            list-style-type: disc;
        }
        .medidas-list li {
            margin-bottom: 2px;
        }

        /* Assinaturas */
        .signatures-wrapper {
            margin-top: 30px;
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
                        <div class="inst-name">{{ $fiscalizacao->prefeitura->nome ?? 'PREFEITURA MUNICIPAL' }}</div>
                        <div class="inst-sub">MÓDULO DE FISCALIZAÇÃO CONTRATUAL (LEI Nº 14.133/2021)</div>
                    @else
                        <div class="inst-sub">FISCALIZAÇÃO (LEI Nº 14.133/2021)</div>
                    @endif
                </td>
                <td class="doc-badge-cell">
                    @if(!$base64Timbre)
                        <div class="doc-type-badge">NOTIFICAÇÕES</div>
                        <div class="doc-num">FISCALIZAÇÃO Nº {{ $fiscalizacao->numero_fiscalizacao }}</div>
                    @else
                        <div>
                            <span class="doc-type-badge">NOTIFICAÇÕES</span>
                            <span class="doc-num-inline">Nº {{ $fiscalizacao->numero_fiscalizacao }}</span>
                        </div>
                    @endif
                </td>
            </tr>
        </table>
    </header>

    {{-- ========================================== --}}
    {{-- PÁGINA 1: RECOMENDAÇÕES AO GESTOR        --}}
    {{-- ========================================== --}}
    <div class="main-title-container">
        <h1 class="main-title">COMUNICAÇÃO INTERNA DE FISCALIZAÇÃO</h1>
        <div class="main-subtitle">Recomendações técnicas dirigidas à Gestão da Secretaria</div>
    </div>

    <div class="card-destinatario">
        <table>
            <tr>
                <td class="label-dest">Ao(À) Sr.(a):</td>
                <td><strong>{{ $nomeSecretario }}</strong><br>Secretário(a) / Gestor(a) da {{ mb_strtoupper($fiscalizacao->prefeitura->nome ?? 'Prefeitura') }}</td>
            </tr>
            <tr>
                <td class="label-dest">De:</td>
                <td>Fiscal do Contrato Administrativo nº <strong>{{ $info['numero_contrato'] }}</strong></td>
            </tr>
            <tr>
                <td class="label-dest">Assunto:</td>
                <td><strong>Recomendações decorrentes da fiscalização e acompanhamento contratual</strong></td>
            </tr>
        </table>
    </div>

    <p class="indent mt-2">Senhor(a) Secretário(a),</p>

    <p class="indent">Em cumprimento às atribuições legais previstas no art. 117 da <strong>Lei Federal nº 14.133/2021</strong> e no ato formal de designação de fiscalização do Contrato nº <b>{{ $info['numero_contrato'] }}</b>, firmado com a empresa <b>{{ $info['razao_social'] }}</b>, cujo objeto é <i>"{{ $info['objeto'] }}"</i>, apresento a V. Sa. as seguintes considerações e recomendações técnicas decorrentes das atividades de acompanhamento realizadas.</p>

    <div class="sec-container mt-4">
        <div class="sec-heading">1. SITUAÇÃO ATUAL DA EXECUÇÃO CONTRATUAL</div>
        <div class="text-box">
            <strong>Síntese do Fiscal:</strong> {{ $fiscalizacao->conclusao_texto }}<br>
            <strong>Detalhamento da Execução:</strong> {{ $fiscalizacao->execucao_objeto }}
        </div>
    </div>

    <div class="sec-container mt-4">
        <div class="sec-heading">2. RECOMENDAÇÕES DIRIGIDAS AO GESTOR DO CONTRATO</div>
        <div class="text-box">
            {{ $fiscalizacao->recomendacoes_gestor ?? 'Sem recomendações específicas para o gestor até o momento.' }}
        </div>
    </div>

    <p class="indent mt-4">Considerando as atribuições de controle e a busca constante pela eficiência e pela boa aplicação dos recursos públicos, submeto o presente relatório à deliberação de V. Sa. para conhecimento e tomada das providências administrativas que julgar cabíveis.</p>

    <p class="mt-4" style="text-align: right;"><strong>{{ $fiscalizacao->prefeitura->cidade ?? 'Local' }}</strong>, {{ $fiscalizacao->data_fiscalizacao->translatedFormat('d \d\e F \d\e Y') }}</p>

    <div class="signatures-wrapper">
        <div class="sig-cell-single">
            <div class="sig-line"></div>
            <p class="sig-name">{{ $fiscalizacao->user->name }}</p>
            <p class="sig-role">Fiscal do Contrato</p>
        </div>
    </div>

    <div class="page-break"></div>

    {{-- ========================================== --}}
    {{-- PÁGINA 2: NOTIFICAÇÃO À EMPRESA          --}}
    {{-- ========================================== --}}
    <div class="main-title-container">
        <h1 class="main-title">NOTIFICAÇÃO / RECOMENDAÇÃO À CONTRATADA</h1>
        <div class="main-subtitle">Comunicação formal do Fiscal do Contrato ao Fornecedor/Prestador</div>
    </div>

    <div class="card-destinatario">
        <table>
            <tr>
                <td class="label-dest">À Empresa:</td>
                <td><strong>{{ $info['razao_social'] }}</strong> &nbsp;(CNPJ/CPF: {{ $info['cnpj'] }})<br>Endereço: {{ $info['endereco'] ?? 'Não informado' }}</td>
            </tr>
            <tr>
                <td class="label-dest">Referência:</td>
                <td>Contrato Administrativo nº <strong>{{ $info['numero_contrato'] }}</strong></td>
            </tr>
            <tr>
                <td class="label-dest">Objeto:</td>
                <td>{{ $info['objeto'] }}</td>
            </tr>
        </table>
    </div>

    <p class="indent mt-2">Prezados Senhores,</p>

    <p class="indent">Na qualidade de Fiscal do Contrato nº <b>{{ $info['numero_contrato'] }}</b>, devidamente designado para o acompanhamento e fiscalização da execução contratual, venho por meio desta notificar formalmente a empresa acerca das verificações e recomendações técnicas apuradas.</p>

    <div class="sec-container mt-4">
        <div class="sec-heading">1. SITUAÇÃO APURADA NA FISCALIZAÇÃO</div>
        <p class="indent">Durante as verificações de acompanhamento, foram registradas as seguintes ocorrências/situações:</p>
        <div class="alert-box">
            <strong>Registro de Ocorrência:</strong><br>
            {{ $fiscalizacao->irregularidade_observada ?? 'Nenhuma irregularidade grave registrada nesta data.' }}
        </div>
    </div>

    <div class="sec-container mt-4">
        <div class="sec-heading">2. RECOMENDAÇÕES E DETERMINAÇÕES À CONTRATADA</div>
        <p class="indent">Diante do exposto, <strong>DETERMINA-SE / RECOMENDA-SE</strong> à contratada a adoção imediata das seguintes providências:</p>
        <div class="text-box">
            {{ $fiscalizacao->recomendacoes_empresa ?? 'Manter a estrita regularidade na prestação dos serviços/entrega dos produtos conforme edital e cláusulas contratuais.' }}
        </div>
    </div>

    <p class="indent mt-4">Ressalta-se que o eventual descumprimento injustificado das determinações desta fiscalização sujeitará a empresa às sanções legais e contratuais previstas na <strong>Lei Federal nº 14.133/2021</strong>, tais como:</p>

    <ul class="medidas-list" style="margin-left: 24px;">
        <li>Advertência formal e registro em cadastro de inadimplência;</li>
        <li>Aplicação de multas moratórias ou compensatórias;</li>
        <li>Retenção ou suspensão preventiva de pagamentos;</li>
        <li>Rescisão unilateral do contrato e instauração de processo administrativo sancionatório.</li>
    </ul>

    <p class="indent mt-4">Solicita-se que a contratada apresente manifestação formal e comprovante de atendimento às providências solicitadas dentro do prazo regulamentar.</p>

    <p class="mt-4" style="text-align: right;"><strong>{{ $fiscalizacao->prefeitura->cidade ?? 'Local' }}</strong>, {{ $fiscalizacao->data_fiscalizacao->translatedFormat('d \d\e F \d\e Y') }}</p>

    <div class="signatures-wrapper">
        <div class="sig-cell-single">
            <div class="sig-line"></div>
            <p class="sig-name">{{ $fiscalizacao->user->name }}</p>
            <p class="sig-role">Fiscal do Contrato</p>
        </div>
    </div>
</body>
</html>