<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitação de Parecer Jurídico</title>
    <style>
        @include('Admin.Processos.pdf.aditivos.partials.estilo-base')
    </style>
</head>
<body>
    @php
        \Carbon\Carbon::setLocale('pt_BR');

        // Ofício de encaminhamento à Procuradoria — antes era a 2ª página da
        // Solicitação do Aditivo, o que o colocava antes do aceite e da dotação.
        $orgaoSolicitante = $contrato->dados_contratante['orgao_responsavel'] ?? 'Secretaria';
        $temAssinanteSelecionado = isset($documentoSelecao) && is_array($documentoSelecao->assinantes) && count($documentoSelecao->assinantes) > 0;
        $remetente = $temAssinanteSelecionado
            ? $documentoSelecao->assinantes[0]['responsavel']
            : ($prefeitura->autoridade_competente ?? 'Prefeito Municipal');
    @endphp

    <div class="title">Solicitação de Parecer Jurídico</div>

    <div class="header-info">
        <p><strong>De:</strong> {{ \App\Helpers\NomeProprio::formatar($remetente) }}</p>
        <p><strong>Para:</strong> Procuradoria/Assessoria Jurídica</p>
    </div>

    <div class="clause-content">Sr. Procurador(a)/Assessor(a) Jurídico(a),</div>
    <div class="clause-content">
        Tendo em vista a solicitação da {{ $orgaoSolicitante }}, sobre o Aditivo ao Contrato nº {{ $vm->numeroContrato }}, solicitamos a Vossa Senhoria que emita parecer jurídico sobre a legalidade do justificado e requerido.
    </div>
    <div class="clause-content">Sem mais, pedimos a maior brevidade possível.</div>
    <div class="clause-content">Atenciosamente.</div>

    @include('Admin.Processos.pdf.aditivos.partials.data-local')

    @include('Admin.Processos.pdf.aditivos.partials.bloco-assinaturas', [
        'nomeFallback' => $prefeitura->autoridade_competente ?? 'Prefeito Municipal',
        'cargoFallback' => 'Prefeito Municipal',
    ])
</body>
</html>
