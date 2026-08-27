<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitação de Aditivo</title>
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
            font-size: 12pt; font-family: 'Aptos', sans-serif;
            background-image: url('{{ public_path($prefeitura->timbre ?? '') }}');
            background-repeat: no-repeat; background-position: top left; background-size: cover;
            text-align: justify; text-justify: inter-word; line-height: 1.5;
        }

         /* CLASSE PARA FORÇAR QUEBRA DE PÁGINA (ESSENCIAL PARA PDF) */
        .page-break {
            page-break-after: always;
        }

        .title {
            text-align: center; font-weight: bold; font-size: 14pt; margin-bottom: 30px;
            font-family: 'AptosExtraBold', sans-serif; text-transform: uppercase;
        }

        .header-info { margin-bottom: 25px; }
        .header-info p { margin: 3px 0; }
        .clause-content { text-indent: 40px; }
        .signature-block { margin-top: 60px; text-align: center; }
    </style>
</head>
<body>
    @php
        \Carbon\Carbon::setLocale('pt_BR');
        
        $strTipo = '';
        if ($incidente->tipo === 'prazo') $strTipo = 'prorrogação de prazo por mais ' . $incidente->meses_prorrogacao . ' meses';
        elseif ($incidente->tipo === 'valor') $strTipo = 'acréscimo de valor equivalente a ' . number_format($incidente->percentual_valor, 2, ',', '.') . '%';
        else $strTipo = 'prorrogação de prazo por mais ' . $incidente->meses_prorrogacao . ' meses e acréscimo de valor equivalente a ' . number_format($incidente->percentual_valor, 2, ',', '.') . '%';
    @endphp

    <div class="title">SOLICITAÇÃO</div>

    <div class="header-info">
        <p><strong>Assunto:</strong> Solicitação de Aditivo</p>
        <p><strong>Contrato n°:</strong> {{ $contrato->numero_contrato ?? 'S/N' }}</p>
        <p><strong>Contratada:</strong> {{ $contrato->dados_contratante['razao_social'] ?? 'NOME DA EMPRESA' }}</p>
        <p><strong>Objeto:</strong> {{ trim(html_entity_decode(strip_tags($processo->objeto ?? 'Objeto do Processo'))) }}</p>
    </div>

    <p style="font-weight: bold; margin-bottom: 15px;">Sr(a). {{ $prefeitura->autoridade_competente ?? 'Prefeito(a)' }},</p>

    <div class="clause-content">
        O Contrato nº {{ $contrato->numero_contrato ?? 'S/N' }} tem como objeto a {{ strtolower(trim(html_entity_decode(strip_tags($processo->objeto ?? '')))) }}.
    </div>

    <div class="clause-content">
        Por meio da presente comunicação, solicitamos a autorização de vossa excelência para a realização de {{ $strTipo }} do contrato administrativo n° {{ $contrato->numero_contrato ?? 'S/N' }}.
    </div>

    <div style="font-weight: bold; margin-top: 25px; margin-bottom: 15px;">JUSTIFICATIVA</div>
    <div class="clause-content">
        {!! nl2br(e($incidente->justificativa)) !!}
    </div>

    <div class="clause-content">
        Ante ao exposto, venho através do presente solicitar de Vossa Senhoria tendo em vista o risco de afronta ao princípio da continuidade dos serviços públicos e natureza continuada dos serviços, aditivo referente ao contrato n° {{ $contrato->numero_contrato ?? 'S/N' }}.
    </div>

    <div class="clause-content">
        Segue em anexo aceite e documentação da empresa.
    </div>

    <div style="margin-top: 40px;">
        {{ $prefeitura->cidade ?? 'Cidade' }} – {{ $prefeitura->estado ?? 'UF' }}, {{ \Carbon\Carbon::parse($data_selecionada ?? now())->translatedFormat('d \d\e F \d\e Y') }}
    </div>

    @if(isset($documentoSelecao) && is_array($documentoSelecao->assinantes) && count($documentoSelecao->assinantes) > 0)
        @foreach($documentoSelecao->assinantes as $assinante)
            <div class="signature-block" style="margin-top: 40px; text-align: center;">
                ___________________________________<br>
                <strong>{{ mb_strtoupper($assinante['responsavel']) }}</strong><br>
                {{ $assinante['unidade_nome'] }}
                @if(!empty($assinante['portaria']))
                <br>Portaria: {{ $assinante['portaria'] }}
                @endif
            </div>
        @endforeach
    @else
        <div class="signature-block">
            ___________________________________<br>
            <strong>{{ mb_strtoupper($incidente->nome_solicitante ?? ($contrato->dados_contratante['orgao_responsavel'] ?? 'SECRETARIA MUNICIPAL')) }}</strong><br>
            {{ $incidente->cargo_solicitante ?? 'Secretário(a)' }}
        </div>
    @endif
 {{-- QUEBRA DE PÁGINA --}}
    <div class="page-break"></div>

    <div style="margin-top: 40px; font-size: 11pt;">
        <p>
            <strong>De:</strong> {{ $prefeitura->autoridade_competente ?? 'Prefeito Municipal' }} <br>
            <strong>Para:</strong> Procuradoria/Assessoria Jurídica
        </p>
        <p style="text-indent: 40px;">Sr. Procurador(a)/Assessor(a) Jurídico(a),</p>
        <p style="text-indent: 40px;">
            Tendo em vista a solicitação da {{ $contrato->dados_contratante['orgao_responsavel'] ?? 'Secretaria' }}, sobre o Aditivo ao Contrato n. {{ $contrato->numero_contrato ?? 'S/N' }}, solicitamos a Vossa Senhoria que emita parecer jurídico sobre a legalidade do justificado e requerido.
        </p>
        <p style="text-indent: 40px;">Sem mais, pedimos a maior brevidade possível.</p>
        <p style="text-indent: 40px;">Atenciosamente.</p>
    </div>

    <div class="signature-block" style="margin-top: 40px;">
        ___________________________________<br>
        <strong>{{ $prefeitura->autoridade_competente ?? 'Prefeito Municipal' }}</strong><br>
        {{ $prefeitura->cargo_autoridade ?? 'Prefeito Municipal' }}
    </div>
</body>
</html>
