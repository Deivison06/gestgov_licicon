<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aceite e Documentação de Regularidade</title>
    <style>
        @include('Admin.Processos.pdf.aditivos.partials.estilo-base')
    </style>
</head>
<body>
    @php \Carbon\Carbon::setLocale('pt_BR'); @endphp

    <div class="title">Aceite e Documentação de Regularidade da Contratada</div>

    <div class="header-info">
        <p><strong>Contrato n°:</strong> {{ $vm->numeroContrato }}</p>
        <p><strong>Contratada:</strong> {{ $contrato->dados_contratante['razao_social'] ?? 'NOME DA EMPRESA' }}</p>
    </div>

    <div class="clause-content">
        A contratada manifestou aceite à formalização do presente Termo Aditivo e apresentou a documentação de
        regularidade fiscal, trabalhista e de habilitação exigida, anexada a este processo administrativo.
    </div>

    @include('Admin.Processos.pdf.aditivos.partials.data-local')

    @include('Admin.Processos.pdf.aditivos.partials.bloco-assinaturas', [
        'nomeFallback' => $incidente->nome_solicitante ?? ($contrato->dados_contratante['orgao_responsavel'] ?? 'SECRETARIA MUNICIPAL'),
        'cargoFallback' => $incidente->cargo_solicitante ?? 'Secretário(a)',
    ])
</body>
</html>
