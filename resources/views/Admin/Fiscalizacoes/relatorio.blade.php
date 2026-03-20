<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Fiscalização - {{ $fiscalizacao->numero_fiscalizacao }}</title>
    <style>
        @page { margin: 0; size: A4; }
        body {
            margin: 0;
            padding: 4cm 1.5cm 2cm 1.5cm;
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

        /* Estilos para a seção de Resumo Técnico */
        .resumo-item { margin-bottom: 15px; }
        .resumo-label { display: block; font-weight: bold; color: #333; margin-bottom: 2px; text-transform: uppercase; font-size: 11px; }
        .resumo-texto { display: block; margin-left: 0; line-height: 1.4; color: #444; }

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
        $tipo = $fiscalizacao->tipo_contrato?->value;

        // Labels dinâmicas conforme o tipo de contrato (conforme o PDF de requisitos e create.blade)
        $labels = match($tipo) {
            'compras' => [
                'execucao' => 'Execução Física do Objeto',
                'qualidade' => 'Qualidade dos Produtos entregues',
                'obs_servidor' => 'Observações indicadas por servidor próximo a execução'
            ],
            'servicos' => [
                'execucao' => 'Execução do Objeto',
                'qualidade' => 'Qualidade dos serviços Prestados',
                'obs_servidor' => 'Observações indicadas por servidor próximo a execução'
            ],
            'obras' => [
                'execucao' => 'Execução do Objeto',
                'qualidade' => 'Qualidade dos serviços Executados',
                'obs_servidor' => 'Observações indicadas por servidor Fiscal de Engenharia'
            ],
            default => [
                'execucao' => 'Execução do Objeto',
                'qualidade' => 'Qualidade das Entregas',
                'obs_servidor' => 'Observações do Servidor'
            ]
        };

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

    {{-- ========================================== --}}
    {{-- PÁGINA 0: RESUMO DA AVALIAÇÃO TÉCNICA    --}}
    {{-- ========================================== --}}

    <div class="content-body">
        <div class="text-center" style="margin-bottom: 30px;">
            <h2 class="bold uppercase" style="margin-bottom: 5px;">Relatório de Fiscalização</h2>
        </div>
        <p class="bold">Data da Inspeção: <span>{{ $fiscalizacao->data_fiscalizacao->format('d/m/Y') }}</span> </p>
        <p class="bold">Número da Fiscalização: <span>{{ $fiscalizacao->numero_fiscalizacao }}</span> </p>


        <p>A fiscalização contratual foi realizada conforme as diretrizes da Lei nº 14.133/2021, por meio de:</p>
        <ul class="medidas-list">
            <li>Análise documental (notas fiscais, relatórios de execução, comprovantes, etc.);</li>
            <li>Verificação presencial (vistorias in loco, fotografias, contato com usuários);</li>
            <li>Reuniões e comunicações com representantes da contratada;</li>
            <li>Reuniões com funcionários e equipes da secretaria.</li>
        </ul>

        <div class="resumo-item">
            <span class="resumo-label">Contrato e Contratada:</span>
            <span class="resumo-texto">{{ $info['numero_contrato'] }} — {{ $info['razao_social'] }} ({{ $info['cnpj'] }})</span>
        </div>

        @if($fiscalizacao->metodologia_fiscalizacao)
            <div class="resumo-item">
                <span class="resumo-label">Metodologia Aplicada:</span>
                <span class="resumo-texto">{{ $fiscalizacao->metodologia_fiscalizacao }}</span>
            </div>
        @endif

        <div class="resumo-item">
            <span class="resumo-label">{{ $labels['execucao'] }}:</span>
            <span class="resumo-texto">{{ $fiscalizacao->execucao_objeto ?? 'Não informado.' }}</span>
        </div>

        <div class="resumo-item">
            <span class="resumo-label">{{ $labels['qualidade'] }}:</span>
            <span class="resumo-texto">{{ $fiscalizacao->qualidade_entregas ?? 'Não informado.' }}</span>
        </div>

        <div class="resumo-item">
            <span class="resumo-label">Pontualidade / Cumprimento dos Prazos:</span>
            <span class="resumo-texto">{{ $fiscalizacao->pontualidade_prazos ?? 'Não informado.' }}</span>
        </div>

        <div class="resumo-item">
            <span class="resumo-label">Regularidade Fiscal e Trabalhista:</span>
            <span class="resumo-texto">{{ $fiscalizacao->regularidade_fiscal_trabalhista ?? 'Não informado.' }}</span>
        </div>

        <div class="resumo-item">
            <span class="resumo-label">Comunicação e Atendimento:</span>
            <span class="resumo-texto">{{ $fiscalizacao->comunicacao_atendimento ?? 'Não informado.' }}</span>
        </div>

        <div class="resumo-item">
            <span class="resumo-label">{{ $labels['obs_servidor'] }}:</span>
            <span class="resumo-texto">{{ $fiscalizacao->observacoes_servidor ?? 'Sem observações adicionais.' }}</span>
        </div>

        <div class="resumo-item">
            <span class="resumo-label">Irregularidades Observadas:</span>
            <span class="resumo-texto">{{ $fiscalizacao->irregularidade_observada ?? 'Nenhuma irregularidade observada durante o período.' }}</span>
        </div>

        <div class="resumo-item" style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 10px;">
            <span class="resumo-label">Conclusão do Fiscal:</span>
            <p class="bold" style="color: #062F43;">{{ $fiscalizacao->conclusao_texto }}</p>
        </div>

        {{-- <div class="signature-section">
            <div class="signature-line"></div>
            <p class="bold uppercase" style="margin:0;">{{ $fiscalizacao->user->name }}</p>
            <p style="margin:0;">Fiscal do Contrato</p>
        </div> --}}
    </div>

    <div class="page-break"></div>

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

        <p>Senhor(a) Secretário(a),</p>

        <p class="indent">Em cumprimento às atribuições previstas na Lei nº 14.133/2021 e no ato de designação de fiscal do Contrato nº <b>{{ $info['numero_contrato'] }}</b>, firmado com a empresa <b>{{ $info['razao_social'] }}</b>, cujo objeto é <b>{{ $info['objeto'] }}</b>, apresento as seguintes recomendações decorrentes das atividades de acompanhamento e fiscalização realizadas.</p>

        <h3 class="bold uppercase" style="font-size: 13px;">1. Situação atual do contrato</h3>
        <p class="indent">{{ $fiscalizacao->conclusao_texto }}. Além disso, nota-se que: {{ $fiscalizacao->execucao_objeto }}</p>

        <h3 class="bold uppercase mt-4" style="font-size: 13px;">2. Recomendações do fiscal do contrato</h3>
        <p class="indent">{{ $fiscalizacao->recomendacoes_gestor ?? 'Sem recomendações específicas para o gestor até o momento.' }}</p>

        <p class="mt-2">Considerando as atribuições legais da fiscalização contratual e visando assegurar a correta execução do contrato, a economicidade e o interesse público, encaminham-se as presentes recomendações para análise e deliberação.</p>

        <p class="mt-4">{{ $fiscalizacao->prefeitura->cidade ?? 'Local' }}, {{ $fiscalizacao->data_fiscalizacao->translatedFormat('d \d\e F \d\e Y') }}</p>

        <div class="signature-section">
            <div class="signature-line"></div>
            <p class="bold uppercase" style="margin:0;">{{ $fiscalizacao->user->name }}</p>
            <p style="margin:0;">Fiscal do Contrato</p>
            {{-- <p style="margin:0; font-size: 10px;">Matrícula: {{ $fiscalizacao->user->cpf ?? '—' }}</p> --}}
        </div>

        <div class="page-break"></div>

        {{-- ========================================== --}}
        {{-- PÁGINA 2: NOTIFICAÇÃO À EMPRESA          --}}
        {{-- ========================================== --}}
        @if($base64Timbre)
            <img class="timbre-bg" src="{{ $base64Timbre }}" alt="Timbre">
        @endif

        <div class="header-empresa">
            <p><span class="bold">À Empresa:</span> {{ $info['razao_social'] }}<br>
            <span class="bold">CNPJ:</span> {{ $info['cnpj'] }}<br>
            <span class="bold">Endereço:</span> {{ $info['endereco'] }}</p>

            <p><span class="bold">Ref.:</span> Contrato Administrativo nº {{ $info['numero_contrato'] }}<br>
            <span class="bold">Objeto:</span> {{ $info['objeto'] }}</p>
        </div>

        <p>Prezados Senhores,</p>

        <p class="indent">Na qualidade de Fiscal do Contrato nº <b>{{ $info['numero_contrato'] }}</b>, designado para o exercício das atribuições de acompanhamento e fiscalização da execução contratual, venho por meio deste apresentar as seguintes recomendações decorrentes das verificações realizadas.</p>

        <h3 class="bold uppercase" style="font-size: 13px;">1. Situação verificada</h3>
        <p class="indent">Durante o acompanhamento da execução do contrato, foram identificadas as seguintes ocorrências:</p>
        <p class="indent" style="background: #f9f9f9; padding: 10px; border-left: 3px solid #ccc;">
            {{ $fiscalizacao->irregularidade_observada ?? 'Nenhuma irregularidade grave registrada nesta data.' }}
        </p>

        <h3 class="bold uppercase mt-4" style="font-size: 13px;">2. Recomendações e determinações</h3>
        <p class="indent">Diante do exposto, <b>RECOMENDA-SE</b> à contratada que adote as seguintes providências:</p>
        <p class="indent">{{ $fiscalizacao->recomendacoes_empresa ?? 'Manter a regularidade na prestação dos serviços/entrega dos produtos conforme edital.' }}</p>

        <p class="mt-2">O não atendimento às recomendações dentro do prazo estabelecido poderá ensejar a adoção das medidas administrativas cabíveis, incluindo:</p>

        <ul class="medidas-list">
            <li>Aplicação de penalidades contratuais;</li>
            <li>Suspensão de pagamentos;</li>
            <li>Rescisão contratual;</li>
            <li>Demais sanções previstas na Lei nº 14.133/2021 e no contrato.</li>
        </ul>

        <p class="mt-2">Solicita-se que a empresa apresente manifestação formal acerca desta notificação, informando as providências adotadas.</p>

        <p class="mt-4">{{ $fiscalizacao->prefeitura->cidade ?? 'Local' }}, {{ $fiscalizacao->data_fiscalizacao->translatedFormat('d \d\e F \d\e Y') }}</p>

        <div class="signature-section">
            <div class="signature-line"></div>
            <p class="bold uppercase" style="margin:0;">{{ $fiscalizacao->user->name }}</p>
            <p style="margin:0;">Fiscal do Contrato</p>
        </div>

    </div>

</body>
</html>
