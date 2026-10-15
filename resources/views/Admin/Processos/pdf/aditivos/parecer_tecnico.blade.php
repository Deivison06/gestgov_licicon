<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parecer Técnico</title>
    <style>
        @include('Admin.Processos.pdf.aditivos.partials.estilo-base')
    </style>
</head>
<body>
    @php
        \Carbon\Carbon::setLocale('pt_BR');
        $resumo = $vm->resumoFinanceiro;
    @endphp

    <div class="title">Parecer Técnico</div>

    <div class="section-title">I – DO OBJETO</div>
    <div class="clause-content">
        Trata-se de análise técnica realizada pela Engenharia do Município acerca da necessidade de celebração de
        {{ $vm->tituloTipoAlteracao() }} ao Contrato nº {{ $vm->numeroContrato }}, cujo objeto é {{ $vm->objetoContrato }},
        firmado entre o Município de {{ $prefeitura->cidade ?? 'Município' }} – {{ $prefeitura->uf ?? 'PI' }} e a empresa
        {{ $contrato->dados_contratante['razao_social'] ?? 'CONTRATADA' }}.
    </div>

    <div class="section-title">II – DA EXECUÇÃO CONTRATUAL</div>
    <div class="clause-content">
        A obra encontra-se com percentual de execução física de
        {{ $incidente->percentual_execucao_fisica !== null ? number_format((float) $incidente->percentual_execucao_fisica, 2, ',', '.') . '%' : 'XX%' }}
        e sua execução está {{ $vm->situacaoExecucao?->getDisplayName() ?? '(situação não informada)' }}.
    </div>

    @if($vm->envolvePrazo())
        <div class="clause-content">
            Os fatores que afetaram o prazo de execução foram:
            {{ collect($vm->fatoresAtraso)->map(fn($f) => $f->getDisplayName())->implode(', ') ?: '(não informado)' }}.
        </div>
        <div class="clause-content">
            {!! nl2br(e($incidente->descricao_fatores_atraso)) !!}
        </div>
    @endif

    <div class="section-title">III – {{ $vm->envolveValor() && $vm->envolvePrazo() ? 'DO ADITIVO DE VALOR E DE PRAZO' : ($vm->envolveValor() ? 'DO ADITIVO DE VALOR' : 'DO ADITIVO DE PRAZO') }}</div>

    @if($vm->envolveValor())
        <div class="clause-content">
            O aditivo de valor proposto decorre da necessidade de inclusão/adequação de serviços tecnicamente justificáveis,
            os quais guardam estrita relação com o objeto originalmente contratado, não o descaracterizam, e foram
            devidamente quantificados e orçados com base em planilha orçamentária atualizada e referenciais oficiais
            (SINAPI, SEINFRA, SICRO ou equivalente).
        </div>
        <div class="clause-content">
            A alteração observa os termos do art. 124, inciso I, alínea "{{ $vm->alineaArt124() }}", da Lei nº 14.133/2021
            (alteração quantitativa), dentro do limite de
            {{ $vm->limiteLegalObras?->getDisplayName() ?? '(limite legal não informado)' }}, considerando que os acréscimos
            acumulados (deste e dos aditivos anteriores) correspondem a {{ number_format($resumo['percentual_acumulado_acrescimo'] ?? 0, 2, ',', '.') }}%
            e as supressões acumuladas a {{ number_format($resumo['percentual_acumulado_supressao'] ?? 0, 2, ',', '.') }}% do valor inicial atualizado do contrato.
        </div>
    @endif

    @if($vm->envolvePrazo())
        <div class="clause-content">
            A prorrogação do prazo contratual mostra-se tecnicamente necessária e plenamente justificada, encontrando
            amparo no {{ $vm->artigoFundamentacaoPrazo() }} da Lei nº 14.133/2021.
        </div>
    @endif

    <div class="section-title">IV – DA VANTAJOSIDADE E DO INTERESSE PÚBLICO</div>
    <div class="clause-content">
        @if($incidente->texto_vantajosidade_tecnica)
            {!! nl2br(e($incidente->texto_vantajosidade_tecnica)) !!}
        @else
            Sob o ponto de vista técnico, a celebração do {{ $vm->tituloTipoAlteracao() }} é mais vantajosa para a
            Administração do que a rescisão e nova contratação, evitando retrabalho, desperdício de recursos públicos e
            descontinuidade da obra/serviço, garantindo a entrega do objeto conforme os padrões técnicos exigidos e
            preservando o interesse público e a economicidade do contrato.
        @endif
    </div>

    <div class="section-title">V – CONCLUSÃO TÉCNICA</div>
    <div class="clause-content">
        @if($incidente->conclusao_tecnica)
            {!! nl2br(e($incidente->conclusao_tecnica)) !!}
        @else
            Diante do exposto, esta Engenharia manifesta-se FAVORAVELMENTE à celebração do {{ $vm->tituloTipoAlteracao() }}
            ao Contrato nº {{ $vm->numeroContrato }}, por entender que há justificativa técnica suficiente, que os
            serviços/prazos são necessários e legais, que foram observados os limites legais e que estão preservados os
            princípios da legalidade, eficiência, economicidade e continuidade do serviço público.
        @endif
    </div>
    <div class="clause-content">É o parecer técnico.</div>

    <div style="margin-top: 40px; text-align: left;">
        {{ $prefeitura->cidade ?? 'Cidade' }}, {{ \Carbon\Carbon::parse($data_selecionada ?? now())->translatedFormat('d \d\e F \d\e Y') }}.
    </div>

    @if(isset($documentoSelecao) && is_array($documentoSelecao->assinantes) && count($documentoSelecao->assinantes) > 0)
        @include('Admin.Processos.pdf.aditivos.partials.bloco-assinaturas')
    @else
        <div class="signature-block">
            ___________________________________<br>
            <strong>{{ mb_strtoupper($incidente->nome_engenheiro_parecer_tecnico ?? 'Engenheiro(a) Responsável') }}</strong><br>
            {{ $incidente->cargo_engenheiro_parecer_tecnico ?? 'Engenheiro(a) Responsável' }}<br>
            {{ $incidente->registro_profissional_parecer_tecnico ?? 'CREA/CAU nº XXXXXXX' }}
        </div>
    @endif
</body>
</html>
