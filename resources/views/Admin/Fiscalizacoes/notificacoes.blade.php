<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Notificações - {{ $fiscalizacao->numero_fiscalizacao }}</title>
    <style>
        @page { margin: 0; size: A4; }
        body {
            margin: 0;
            padding: 3.5cm 1.5cm 2cm 1.5cm;
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
            line-height: 1.5;
            text-align: justify;
        }
        .timbre-bg {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            z-index: -1000;
        }
        .content-body { margin: 0 4rem; }
        .text-center { text-align: center; }
        .uppercase { text-transform: uppercase; }
        .bold { font-weight: bold; }
        .mt-4 { margin-top: 40px; }
        .mt-2 { margin-top: 20px; }
        .mb-2 { margin-bottom: 20px; }
        .page-break { page-break-after: always; }
        p { margin: 5px 0; }
        .indent { text-indent: 25px; }

        .medidas-list {
            margin: 0 0 12px 20px;
            padding: 0;
            list-style-type: none;
        }
        .medidas-list li::before {
            content: "• ";
            font-weight: bold;
        }

        .signature-section { margin-top: 50px; text-align: center; }
        .signature-line { border-top: 1px solid #000; width: 300px; margin: 0 auto; padding-top: 5px; }
    </style>
</head>
<body>
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

    @if($base64Timbre)
        <img class="timbre-bg" src="{{ $base64Timbre }}" alt="Timbre">
    @endif

    <div class="text-center" style="margin-bottom: 20px;">
        <h2 class="bold uppercase">Notificações</h2>
    </div>
    {{-- ========================================== --}}
    {{-- PÁGINA 1: RECOMENDAÇÕES AO GESTOR        --}}
    {{-- ========================================== --}}
    <div class="content-body">
        <div class="header-destinatario">
            <p><span class="bold">Ao(À): Sr.(a) {{ $nomeSecretario }}</span><br>
            <span class="bold">Secretário(a) da: {{ mb_strtoupper($fiscalizacao->prefeitura->nome ?? 'Prefeitura') }}</span></p>

            <p><span class="bold">De:</span> Fiscal do Contrato nº {{ $info['numero_contrato'] }}</p>
            <p><span class="bold">Assunto:</span> Recomendações decorrentes da fiscalização contratual</p>
        </div>

        <p class="mt-4">Senhor(a) Secretário(a),</p>

        <p class="indent">Em cumprimento às atribuições previstas na Lei nº 14.133/2021 e no ato de designação de fiscal do Contrato nº <b>{{ $info['numero_contrato'] }}</b>, firmado com a empresa <b>{{ $info['razao_social'] }}</b>, cujo objeto é <b>{{ $info['objeto'] }}</b>, apresento as seguintes recomendações decorrentes das atividades de acompanhamento e fiscalização realizadas.</p>

        <h3 class="bold uppercase mt-4" style="font-size: 13px;">1. Situação atual do contrato</h3>
        <p class="indent">{{ $fiscalizacao->conclusao_texto }}. Além disso, nota-se que: {{ $fiscalizacao->execucao_objeto }}</p>

        <h3 class="bold uppercase mt-4" style="font-size: 13px;">2. Recomendações do fiscal do contrato</h3>
        <p class="indent">{{ $fiscalizacao->recomendacoes_gestor ?? 'Sem recomendações específicas para o gestor até o momento.' }}</p>

        <p class="mt-4">Considerando as atribuições legais da fiscalização contratual e visando assegurar a correta execução do contrato, a economicidade e o interesse público, encaminham-se as presentes recomendações para análise e deliberação.</p>

        <p class="mt-4">{{ $fiscalizacao->prefeitura->cidade ?? 'Local' }}, {{ $fiscalizacao->data_fiscalizacao->translatedFormat('d \d\e F \d\e Y') }}</p>

        <div class="signature-section">
            <div class="signature-line"></div>
            <p class="bold uppercase" style="margin:0;">{{ $fiscalizacao->user->name }}</p>
            <p style="margin:0;">Fiscal do Contrato</p>
        </div>
    </div>

    <div class="page-break"></div>

    {{-- ========================================== --}}
    {{-- PÁGINA 2: NOTIFICAÇÃO À EMPRESA          --}}
    {{-- ========================================== --}}
    <div class="content-body">
        <div class="header-empresa">
            <p><span class="bold">À Empresa:</span> {{ $info['razao_social'] }}<br>
            <span class="bold">CNPJ:</span> {{ $info['cnpj'] }}<br>
            <span class="bold">Endereço:</span> {{ $info['endereco'] }}</p>

            <p><span class="bold">Ref.:</span> Contrato Administrativo nº {{ $info['numero_contrato'] }}<br>
            <span class="bold">Objeto:</span> {{ $info['objeto'] }}</p>
        </div>

        <p class="mt-4">Prezados Senhores,</p>

        <p class="indent">Na qualidade de Fiscal do Contrato nº <b>{{ $info['numero_contrato'] }}</b>, designado para o exercício das atribuições de acompanhamento e fiscalização da execução contratual, venho por meio deste apresentar as seguintes recomendações decorrentes das verificações realizadas.</p>

        <h3 class="bold uppercase mt-4" style="font-size: 13px;">1. Situação verificada</h3>
        <p class="indent">Durante o acompanhamento da execução do contrato, foram identificadas as seguintes ocorrências:</p>
        <p class="indent" style="background: #f9f9f9; padding: 10px; border-left: 3px solid #ccc;">
            {{ $fiscalizacao->irregularidade_observada ?? 'Nenhuma irregularidade grave registrada nesta data.' }}
        </p>

        <h3 class="bold uppercase mt-4" style="font-size: 13px;">2. Recomendações e determinações</h3>
        <p class="indent">Diante do exposto, <b>RECOMENDA-SE</b> à contratada que adote as seguintes providências:</p>
        <p class="indent">{{ $fiscalizacao->recomendacoes_empresa ?? 'Manter a regularidade na prestação dos serviços/entrega dos produtos conforme edital.' }}</p>

        <p class="mt-4">O não atendimento às recomendações dentro do prazo estabelecido poderá ensejar a adoção das medidas administrativas cabíveis, incluindo:</p>

        <ul class="medidas-list">
            <li>Aplicação de penalidades contratuais;</li>
            <li>Suspensão de pagamentos;</li>
            <li>Rescisão contratual;</li>
            <li>Demais sanções previstas na Lei nº 14.133/2021 e no contrato.</li>
        </ul>

        <p class="mt-4">Solicita-se que a empresa apresente manifestação formal acerca desta notificação, informando as providências adotadas.</p>

        <p class="mt-4">{{ $fiscalizacao->prefeitura->cidade ?? 'Local' }}, {{ $fiscalizacao->data_fiscalizacao->translatedFormat('d \d\e F \d\e Y') }}</p>

        <div class="signature-section">
            <div class="signature-line"></div>
            <p class="bold uppercase" style="margin:0;">{{ $fiscalizacao->user->name }}</p>
            <p style="margin:0;">Fiscal do Contrato</p>
        </div>
    </div>
</body>
</html>