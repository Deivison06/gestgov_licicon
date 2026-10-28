<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autorização do Prefeito</title>
    <style>
        @include('Admin.Processos.pdf.aditivos.partials.estilo-base')
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
        Eu, <strong>{{ \App\Helpers\NomeProprio::formatar($prefeitura->autoridade_competente ?? 'Prefeito') }}</strong>, {{ $prefeitura->cargo_autoridade ?? 'Prefeito Municipal' }} de {{ $prefeitura->cidade ?? 'Cidade' }}, no uso de minhas atribuições legais, <strong>AUTORIZO</strong> o(a) Agente de Contratação desta Prefeitura Municipal que proceda com o aditivo do Contrato n° {{ $vm->numeroContrato }}, conforme solicitado pela {{ $contrato->dados_contratante['orgao_responsavel'] ?? 'Secretaria' }}, vinculada a esta Administração.
    </div>

    @if($atesto)
    <div class="clause-content">
        {{ $atesto }}
    </div>
    @endif

    @include('Admin.Processos.pdf.aditivos.partials.data-local')

    @if(isset($documentoSelecao) && is_array($documentoSelecao->assinantes) && count($documentoSelecao->assinantes) > 0)
        @foreach($documentoSelecao->assinantes as $assinante)
            <div class="signature-block">
                ___________________________________<br>
                <strong>{{ \App\Helpers\NomeProprio::formatar($assinante['responsavel']) }}</strong><br>
                {{ \App\Helpers\Assinatura::cargo($assinante) }}
                @if(!empty($assinante['portaria']))
                <br>Portaria: {{ $assinante['portaria'] }}
                @endif
            </div>
        @endforeach
    @else
        <div class="signature-block">
            ___________________________________<br>
            <strong>{{ \App\Helpers\NomeProprio::formatar($prefeitura->autoridade_competente ?? 'Prefeito') }}</strong><br>
            {{ $prefeitura->cargo_autoridade ?? 'Prefeito Municipal' }}
        </div>
    @endif
</body>
</html>
