<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informação de Disponibilidade Orçamentária</title>
    <style>
        @include('Admin.Processos.pdf.aditivos.partials.estilo-base')
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
        <table class="tabela">
            <tr><td class="rotulo">Dotação Orçamentária</td><td>{{ $dotacao->textoDotacao() }}</td></tr>
            @if($dotacao->valor)
                <tr><td class="rotulo">Valor</td><td>R$ {{ number_format((float) $dotacao->valor, 2, ',', '.') }}</td></tr>
            @endif
        </table>
    @empty
        <table class="tabela">
            <tr><td class="rotulo">Dotação Orçamentária</td><td>XXXXXXXXXXXXXXXXXXX</td></tr>
        </table>
    @endforelse

    @include('Admin.Processos.pdf.aditivos.partials.data-local')

    {{-- Assina quem for selecionado em "Definir Assinantes" (Secretaria de
         Finanças, Fazenda, Contabilidade... varia por prefeitura). --}}
    @if(isset($documentoSelecao) && is_array($documentoSelecao->assinantes) && count($documentoSelecao->assinantes) > 0)
        @include('Admin.Processos.pdf.aditivos.partials.bloco-assinaturas')
    @else
        <div class="signature-block">
            ___________________________________<br>
            <strong>{{ mb_strtoupper($prefeitura->nome ?? 'Prefeitura Municipal') }}</strong><br>
            Setor Financeiro/Contábil
        </div>
    @endif
</body>
</html>
