<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informação de Disponibilidade Orçamentária</title>
    <style>
        @include('Admin.Processos.pdf.aditivos.partials.estilo-base')
        table.dotacao { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 11pt; }
        table.dotacao td { border: 1px solid #000; padding: 6px; }
        table.dotacao td.label { font-weight: bold; width: 35%; }
    </style>
</head>
<body>
    @php
        \Carbon\Carbon::setLocale('pt_BR');
        $resumo = $vm->resumoFinanceiro;
        $valorDespesa = $vm->temValorPeriodoProrrogado
            ? (float) $vm->valorPeriodoProrrogado
            : ($resumo['valor_acrescido_neste_aditivo'] ?? 0);
    @endphp

    <div class="title">Informação de Disponibilidade Orçamentária</div>

    <div class="header-info">
        <p><strong>Contrato n°:</strong> {{ $vm->numeroContrato }}</p>
        <p><strong>Contratada:</strong> {{ $contrato->dados_contratante['razao_social'] ?? 'NOME DA EMPRESA' }}</p>
    </div>

    <div class="clause-content">
        Em atenção à solicitação de {{ $vm->tituloTipoAlteracao() }} ao Contrato nº {{ $vm->numeroContrato }}, informamos
        que há disponibilidade orçamentária para fazer face à despesa de R$ {{ number_format($valorDespesa, 2, ',', '.') }}
        decorrente do aditivo, que correrá à conta da(s) seguinte(s) dotação(ões):
    </div>

    @forelse($vm->dotacoes as $dotacao)
        <table class="dotacao">
            <tr><td class="label">Dotação Orçamentária</td><td>{{ $dotacao->textoDotacao() }}</td></tr>
            @if($dotacao->valor)
                <tr><td class="label">Valor</td><td>R$ {{ number_format((float) $dotacao->valor, 2, ',', '.') }}</td></tr>
            @endif
        </table>
    @empty
        <table class="dotacao">
            <tr><td class="label">Dotação Orçamentária</td><td>XXXXXXXXXXXXXXXXXXX</td></tr>
        </table>
    @endforelse

    <div style="margin-top: 40px;">
        {{ $prefeitura->cidade ?? 'Cidade' }}, {{ \Carbon\Carbon::parse($data_selecionada ?? now())->translatedFormat('d \d\e F \d\e Y') }}.
    </div>

    <div class="signature-block">
        ___________________________________<br>
        <strong>{{ mb_strtoupper($prefeitura->nome ?? 'Setor de Contabilidade') }}</strong><br>
        Setor de Contabilidade
    </div>
</body>
</html>
