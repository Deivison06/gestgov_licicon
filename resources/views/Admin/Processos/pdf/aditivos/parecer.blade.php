<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parecer Jurídico</title>
    <style>
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
            margin: 0; padding: 4cm 2cm;
            font-size: 11pt; font-family: 'Aptos', sans-serif;
            background-image: url('{{ public_path($prefeitura->timbre ?? '') }}');
            background-repeat: no-repeat; background-position: top left; background-size: cover;
            text-align: justify; text-justify: inter-word; line-height: 1.4;
        }

        .title {
            text-align: center; font-weight: bold; font-size: 14pt; margin-bottom: 25px;
            font-family: 'AptosExtraBold', sans-serif;
        }

        .section-title { font-weight: bold; margin-top: 20px; margin-bottom: 10px; }
        .clause-content { margin-bottom: 10px; text-indent: 40px; }
        .list-content { margin-left: 40px; margin-bottom: 10px; }
        .list-content li { margin-bottom: 5px; }
        .signature-block { margin-top: 60px; text-align: center; }
        
        .check-box { font-family: monospace; font-size: 12pt; }
    </style>
</head>
<body>
    @php
        \Carbon\Carbon::setLocale('pt_BR');
        
        $chkValor = ($incidente->tipo === 'valor' && $incidente->percentual_valor >= 0) ? '( X )' : '(   )';
        $chkSupressao = ($incidente->tipo === 'valor' && $incidente->percentual_valor < 0) ? '( X )' : '(   )';
        $chkPrazo = ($incidente->tipo === 'prazo') ? '( X )' : '(   )';
        $chkAmbos = ($incidente->tipo === 'prazo_valor') ? '( X )' : '(   )';
    @endphp

    <div class="title">PARECER JURÍDICO</div>

    <div class="section-title">I – RELATÓRIO</div>
    <div class="clause-content">
        Trata-se de solicitação de manifestação jurídica acerca da possibilidade de celebração de Termo Aditivo ao Contrato nº {{ $contrato->numero_contrato ?? 'S/N' }}, cujo objeto consiste em {{ strtolower($processo->detalhe->objeto ?? 'execução do objeto') }}, celebrado entre o Município de {{ $prefeitura->cidade ?? 'Município' }} - {{ $prefeitura->estado ?? 'UF' }} e a empresa {{ $contrato->dados_contratante['razao_social'] ?? 'CONTRATADA' }}.
    </div>

    <div class="clause-content" style="text-indent: 0;">
        O aditivo proposto refere-se a:<br>
        <span class="check-box">{{ $chkValor }}</span> acréscimo de quantitativo/valor;<br>
        <span class="check-box">{{ $chkSupressao }}</span> supressão de quantitativo/valor;<br>
        <span class="check-box">{{ $chkPrazo }}</span> prorrogação de prazo;<br>
        <span class="check-box">{{ $chkAmbos }}</span> acréscimo de valor e prorrogação de prazo.
    </div>

    <div class="clause-content">
        Constam nos autos justificativa do setor demandante, planilha demonstrativa do impacto contratual.
    </div>
    <div class="clause-content">
        É o relatório. Passa-se à análise jurídica.
    </div>

    <div class="section-title">II – DA COMPETÊNCIA DA ASSESSORIA JURÍDICA</div>
    <div class="clause-content">
        Nos termos da Lei nº 14.133/2021, compete à Assessoria Jurídica analisar a legalidade do procedimento, restringindo-se aos aspectos jurídico-formais, não adentrando no mérito administrativo ou na conveniência e oportunidade do ato, que são de responsabilidade da Administração.
    </div>

    <div class="section-title">III – DO CABIMENTO DE ADITIVO</div>
    <div class="clause-content">
        A Lei nº 14.133/2021 autoriza expressamente a alteração dos contratos administrativos, inclusive aqueles que tenham por objeto compras e serviços comuns ou obras, desde que atendidos os requisitos legais.
    </div>

    <div class="clause-content">
        <strong>Aditivo de Valor (Acréscimo ou Supressão):</strong><br>
        O art. 124, inciso I, alínea “a”, da Lei nº 14.133/2021, permite a alteração quantitativa do contrato para melhor adequação às finalidades de interesse público, desde que haja justificativa formal, o aditivo esteja relacionado ao objeto originalmente contratado, não ocorra descaracterização do objeto e sejam observados os limites legais previstos no art. 125 da Lei nº 14.133/2021. No caso em análise, não há inovação indevida ou alteração substancial do objeto.
    </div>

    <div class="clause-content">
        <strong>Aditivo de Prazo:</strong><br>
        Nos contratos é juridicamente possível a prorrogação do prazo contratual, desde que haja interesse da Administração, o contrato esteja sendo executado de forma regular, seja mantida a vantajosidade econômica, exista justificativa formal e disponibilidade orçamentária. A prorrogação encontra respaldo no art. 107 da Lei nº 14.133/2021.
    </div>

    <div class="section-title">IV – DA VANTAJOSIDADE, CONTINUIDADE E INTERESSE PÚBLICO</div>
    <div class="clause-content">
        Sob o prisma jurídico, a celebração do Termo Aditivo mostra-se mais vantajosa do que a realização de nova contratação, considerando a manutenção da continuidade do serviço público, a economicidade do ajuste, a preservação do equilíbrio contratual e a ausência de violação aos princípios da legalidade, isonomia, eficiência e planejamento.
    </div>

    <div class="section-title">V – DAS CONDIÇÕES PARA A FORMALIZAÇÃO</div>
    <div class="clause-content" style="text-indent: 0;">
        Para a regularidade do aditivo, recomenda-se que:
        <ul class="list-content" style="list-style-type: none; margin-left: 0;">
            <li>✓ o termo aditivo seja devidamente motivado;</li>
            <li>✓ haja autorização expressa da autoridade competente;</li>
            <li>✓ sejam mantidas as cláusulas essenciais do contrato;</li>
            <li>✓ exista prévia dotação orçamentária;</li>
            <li>✓ o aditivo seja formalizado antes do término da vigência contratual (quando envolver prazo);</li>
            <li>✓ seja providenciada a publicação legal exigida.</li>
        </ul>
    </div>

    <div class="section-title">VI – CONCLUSÃO</div>
    <div class="clause-content">
        Diante do exposto, esta Assessoria Jurídica OPINA FAVORAVELMENTE à celebração de TERMO ADITIVO AO CONTRATO Nº {{ $contrato->numero_contrato ?? 'S/N' }}, nos termos propostos, por estar em conformidade com a Lei nº 14.133/2021, desde que observadas as recomendações acima.
    </div>
    
    <div class="clause-content">
        É o parecer.
    </div>

    <div style="margin-top: 40px; text-align: left; margin-left: 40px;">
        {{ $prefeitura->cidade ?? 'Cidade' }} – {{ $prefeitura->estado ?? 'UF' }}, {{ \Carbon\Carbon::parse($data_selecionada ?? now())->translatedFormat('d \d\e F \d\e Y') }}.
    </div>

    <div class="signature-block">
        ___________________________________<br>
        <strong>PROCURADORIA GERAL DO MUNICÍPIO</strong><br>
        Procurador(a)/Assessor(a) Jurídico(a)
    </div>
</body>
</html>
