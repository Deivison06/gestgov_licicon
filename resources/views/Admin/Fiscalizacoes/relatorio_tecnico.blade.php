<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório Técnico - {{ $fiscalizacao->numero_fiscalizacao }}</title>
    <style>
        @page { margin: 0; size: A4; }
        body {
            margin: 0;
            padding: 3.5cm 1.5cm 3.5cm 1.5cm;
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
        $tipo = $fiscalizacao->tipo_contrato?->value;

        // Labels dinâmicas conforme o tipo de contrato
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
    @endphp

    @if($base64Timbre)
        <img class="timbre-bg" src="{{ $base64Timbre }}" alt="Timbre">
    @endif

    <div class="content-body">
        <div class="text-center" style="margin-bottom: 20px;">
            <h2 class="bold uppercase">Relatório Técnico de Fiscalização</h2>
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

        <div class="signature-section">
            <div class="signature-line"></div>
            <p class="bold uppercase" style="margin:0;">{{ $fiscalizacao->user->name }}</p>
            <p style="margin:0;">Fiscal do Contrato</p>
        </div>
    </div>
</body>
</html>