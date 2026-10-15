<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autorização do Prefeito</title>
    <style>
        @include('Admin.Processos.pdf.aditivos.partials.estilo-base')
        .title { margin-bottom: 50px; }
        .signature-block { margin-top: 80px; }
    </style>
</head>
<body>
    @php
        \Carbon\Carbon::setLocale('pt_BR');
        $atesto = $textoJuridico->atestoVantajosidadeAutorizacao($vm);
    @endphp

    <div class="title">
        AUTORIZAÇÃO DO PREFEITO MUNICIPAL
    </div>

    <div class="clause-content">
        Eu, <strong>{{ mb_strtoupper($prefeitura->autoridade_competente ?? 'Prefeito') }}</strong>, {{ $prefeitura->cargo_autoridade ?? 'Prefeito Municipal' }} de {{ $prefeitura->cidade ?? 'Cidade' }}, no uso de minhas atribuições legais, <strong>AUTORIZO</strong> o(a) Agente de Contratação desta Prefeitura Municipal que proceda com o aditivo do Contrato n° {{ $vm->numeroContrato }}, conforme solicitado pela {{ $contrato->dados_contratante['orgao_responsavel'] ?? 'Secretaria' }}, vinculada a esta Administração.
    </div>

    @if($atesto)
    <div class="clause-content">
        {{ $atesto }}
    </div>
    @endif

    <div style="margin-top: 60px; text-align: left; margin-left: 40px;">
        {{ $prefeitura->cidade ?? 'Cidade' }}, {{ \Carbon\Carbon::parse($data_selecionada ?? now())->translatedFormat('d \d\e F \d\e Y') }}.
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
            <strong>{{ mb_strtoupper($prefeitura->autoridade_competente ?? 'Prefeito') }}</strong><br>
            {{ $prefeitura->cargo_autoridade ?? 'Prefeito Municipal' }}
        </div>
    @endif
</body>
</html>
