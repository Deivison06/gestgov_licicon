<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Declaração de Manutenção - Fracassada</title>
    <style type="text/css">
        @font-face {
            font-family: 'Aptos';
            src: url('{{ public_path('storage/fonts/Aptos.ttf') }}') format('truetype');
            font-style: normal;
        }
        @font-face {
            font-family: 'AptosExtraBold';
            src: url('{{ public_path('storage/fonts/Aptos-ExtraBold.ttf') }}') format('truetype');
            font-style: normal;
        }

        @page { margin: 0; size: A4; }
        body {
            margin: 0; padding: 4cm 2cm; font-size: 11pt; font-family: 'Aptos', sans-serif;
            background-image: url('{{ public_path($prefeitura->timbre) }}'); background-repeat: no-repeat;
            background-position: top left; background-size: cover; text-align: justify;
            text-justify: inter-word; line-height: 1.2;
        }
        .signature-block { margin-top: 60px; text-align: center; }
        p { margin-bottom: 15px; }
        h4, h3 { text-align: center; font-weight: bold; margin-bottom: 20px; font-family: 'AptosExtraBold', sans-serif;}
    </style>
</head>
<body>
    @php
        $dataFormatada = \Carbon\Carbon::parse($dataSelecionada)
            ->locale('pt_BR')
            ->translatedFormat('d \d\e F \d\e Y');
            
        $hasSelectedAssinantes = isset($assinantes) && count($assinantes) > 0;
        $primeiroAssinante = $hasSelectedAssinantes ? $assinantes[0] : null;
        
        $processoFracassado = $detalhe->processoFracassado;
    @endphp

    <p style="text-align: left;">
        <strong>ÓRGÃO/ENTIDADE:</strong> PREFEITURA MUNICIPAL DE {{ strtoupper($prefeitura->cidade) }}<br>
        <strong>PROCESSO ADMINISTRATIVO:</strong> {{ $processo->numero_processo }}<br>
        <strong>PROCESSO DE DISPENSA DE LICITAÇÃO:</strong> {{ $processo->numero_procedimento }}<br>
        <strong>MODALIDADE E Nº DO PROCESSO ADMINISTRATIVO DA LICITAÇÃO ANTERIOR:</strong> {{ $processoFracassado ? $processoFracassado->modalidade->getDisplayName() . ' nº ' . $processoFracassado->numero_procedimento : 'XXX' }}
    </p>
    
    <br>
    
    <h3>DECLARAÇÃO DE MANUTENÇÃO DAS CONDIÇÕES EDITALÍCIAS</h3>
    <h4 style="font-size: 10pt;">(Art. 75, Inciso III, c/c Art. 72 da Lei Federal nº 14.133/2021)</h4>
    
    <p>O Setor Requisitante, por meio de seu(s) representante(s) legal(is) infra-assinado(s), vem, por meio desta, atestar e declarar formalmente, sob as penas da lei, que na instrução do presente Processo de Dispensa de Licitação nº {{ $processo->numero_procedimento }}, para {!! strip_tags($processo->objeto) !!}, <strong>foram integralmente mantidas e preservadas todas as condições técnicas, obrigações, prazos e exigências</strong> originalmente fixadas no Edital e anexos do {{ $processoFracassado ? $processoFracassado->modalidade->getDisplayName() : 'XXX' }} nº {{ $processoFracassado ? $processoFracassado->numero_procedimento : 'XXX' }}, certame anteriormente deflagrado e que restou fracassado.</p>
    
    <p>Adicionalmente, certifica-se e ratifica-se que:</p>
    
    <p>1. O Estudo Técnico Preliminar (ETP) e o Termo de Referência / Projeto Básico não sofreram qualquer alteração em seu escopo, nas especificações do objeto ou nas diretrizes de execução, mantendo-se plenamente adequados para a presente contratação direta.</p>
    
    <p>2. Os critérios de qualificação técnica, habilitação jurídica e regularidade fiscal exigidos para a celebração da futura contratação são os mesmos exigidos no edital do certame anterior fracassado.</p>
    
    <p>3. Os prazos de fornecimento/execução, cronogramas físico-financeiros e locais de entrega permanecem inalterados.</p>
    
    <p>4. As condições, regras e prazos para faturamento e pagamento do contrato seguem os parâmetros já fixados e analisados anteriormente.</p>
    
    <p>5. A presente declaração integra os autos do Processo de Dispensa de Licitação para fins de atendimento ao requisito objetivo previsto na parte final do <em>caput</em> do inciso III do Art. 75 da Lei nº 14.133/2021.</p>
    
    <div style="text-align: center; margin-top: 60px;">
        {{ $prefeitura->cidade }} – PI, {{ $dataFormatada }}.
    </div>

    @if ($hasSelectedAssinantes)
        <div style="margin-top: 40px; text-align: center;">
            <div class="signature-block" style="display: inline-block;">
                ___________________________________<br>
                <p style="line-height: 1.2; margin: 0;">
                    {{ $primeiroAssinante['responsavel'] }}<br>
                    <span>{{ $primeiroAssinante['unidade_nome'] }}</span>
                </p>
            </div>
        </div>
    @else
        <div class="signature-block" style="margin-top: 40px;">
            ___________________________________<br>
            <p style="line-height: 1.2; margin: 0;">
                (Nome e Cargo do Responsável Técnico/Setor Requisitante)<br>
                <span>Setor Requisitante</span>
            </p>
        </div>
    @endif
</body>
</html>
