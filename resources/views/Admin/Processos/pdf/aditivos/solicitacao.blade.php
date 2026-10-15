<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitação de Termo Aditivo</title>
    <style>
        @include('Admin.Processos.pdf.aditivos.partials.estilo-base')
    </style>
</head>
<body>
    @php
        \Carbon\Carbon::setLocale('pt_BR');

        $resumo = $vm->resumoFinanceiro;
        $objeto = $vm->objetoContrato;
    @endphp

    <div class="title">SOLICITAÇÃO DE TERMO ADITIVO, JUSTIFICATIVA DA NECESSIDADE E DEMONSTRAÇÃO DE VANTAJOSIDADE</div>

    <div class="header-info">
        <p><strong>Contrato n°:</strong> {{ $vm->numeroContrato }}</p>
        <p><strong>Contratada:</strong> {{ $contrato->dados_contratante['razao_social'] ?? 'NOME DA EMPRESA' }}</p>
        <p><strong>Objeto:</strong> {{ $objeto }}</p>
        @if($vm->ehObras())
            <p><strong>Local da obra:</strong> {{ $detalhe->local_obra ?? $prefeitura->endereco ?? 'Não informado' }}</p>
        @endif
    </div>

    <div class="section-title">1. DA SOLICITAÇÃO</div>
    <div class="clause-content">
        A {{ $contrato->dados_contratante['orgao_responsavel'] ?? 'Secretaria Municipal' }}, considerando a necessidade
        @if($vm->envolvePrazo() && $vm->envolveValor())
            de continuidade/conclusão do objeto do Contrato nº {{ $vm->numeroContrato }} e de alteração quantitativa dos serviços necessários à sua adequada execução,
        @elseif($vm->envolvePrazo())
            de conclusão integral do objeto do Contrato nº {{ $vm->numeroContrato }},
        @else
            de adequação quantitativa do objeto do Contrato nº {{ $vm->numeroContrato }} à demanda efetivamente verificada,
        @endif
        vem solicitar a adoção das providências necessárias à formalização de Termo Aditivo que contemple
        @if($vm->envolvePrazo() && $vm->envolveValor())
            , simultaneamente:
        @else
            :
        @endif
    </div>

    <ul class="list-content">
        @if($vm->envolvePrazo())
            <li>a prorrogação do prazo de execução e de vigência por mais {{ $incidente->meses_prorrogacao }} ({{ $incidente->meses_prorrogacao }}) {{ $vm->ehObras() ? 'dias' : 'meses' }};</li>
        @endif
        @if($vm->envolveValor())
            <li>
                a alteração quantitativa do objeto, com natureza de
                <strong>{{ $vm->naturezaAlteracao?->getDisplayName() ?? '(natureza não informada)' }}</strong>,
                com impacto líquido de R$ {{ number_format($resumo['valor_impacto_liquido_neste_aditivo'] ?? 0, 2, ',', '.') }}
                no valor contratual;
            </li>
        @endif
    </ul>

    <div class="clause-content">
        O valor inicial atualizado do contrato é de R$ {{ number_format($resumo['valor_inicial_atualizado'] ?? 0, 2, ',', '.') }}.
    </div>

    @if($vm->temAcrescimo())
        <div class="clause-content">
            O acréscimo pretendido neste aditivo é de R$ {{ number_format($resumo['valor_acrescido_neste_aditivo'] ?? 0, 2, ',', '.') }},
            correspondente a {{ number_format((float) $incidente->percentual_acrescimo, 2, ',', '.') }}% do valor inicial atualizado do contrato.
        </div>
    @endif

    @if($vm->temSupressao())
        <div class="clause-content">
            A supressão pretendida neste aditivo é de R$ {{ number_format($resumo['valor_suprimido_neste_aditivo'] ?? 0, 2, ',', '.') }},
            correspondente a {{ number_format((float) $incidente->percentual_supressao, 2, ',', '.') }}% do valor inicial atualizado do contrato.
        </div>
    @endif

    @if($vm->envolveValor())
        <div class="clause-content">
            Consideradas os aditivos anteriores, os acréscimos acumulados somam {{ number_format($resumo['percentual_acumulado_acrescimo'] ?? 0, 2, ',', '.') }}%
            e as supressões acumuladas {{ number_format($resumo['percentual_acumulado_supressao'] ?? 0, 2, ',', '.') }}% do valor inicial atualizado, passando o valor
            contratual para R$ {{ number_format($resumo['valor_final_contrato'] ?? 0, 2, ',', '.') }}.
        </div>
    @endif

    @if($vm->categoria === 'compras_servicos' && $vm->tipo === 'prazo_valor' && $vm->temValorPeriodoProrrogado)
        <div class="clause-content">
            Há valor estimado para o período prorrogado: R$ {{ number_format((float) $vm->valorPeriodoProrrogado, 2, ',', '.') }}, mantidos os preços unitários contratados.
        </div>
    @endif

    <div class="clause-content">
        A presente solicitação é formulada antes do encerramento do prazo contratual e fundamenta-se nas circunstâncias técnicas e administrativas abaixo demonstradas.
    </div>

    @if($vm->ehObras())
        <div class="section-title">2. DA SITUAÇÃO ATUAL DA OBRA</div>
        <div class="clause-content">
            A obra encontra-se atualmente com percentual de execução física aproximado de
            {{ $incidente->percentual_execucao_fisica !== null ? number_format((float) $incidente->percentual_execucao_fisica, 2, ',', '.') . '%' : 'XX%' }},
            conforme Parecer Técnico da Engenharia.
        </div>
    @endif

    <div class="section-title">{{ $vm->ehObras() ? '3' : '2' }}. DA JUSTIFICATIVA</div>
    <div class="clause-content">
        {!! nl2br(e($incidente->justificativa)) !!}
    </div>

    <div class="section-title">{{ $vm->ehObras() ? '4' : '3' }}. DA DEMONSTRAÇÃO DE VANTAJOSIDADE</div>
    <div class="clause-content">
        Para fins de avaliação da conveniência do aditamento, esta Secretaria comparou a alternativa de
        @if($vm->envolvePrazo())
            manter a execução pela contratada atual
        @else
            formalizar o aditivo
        @endif
        com a realização de nova contratação para o mesmo objeto, considerando:
    </div>
    <ul class="list-content">
        <li>a manutenção da estrutura/equipe já mobilizada pela contratada atual;</li>
        <li>a continuidade e a integração entre o que já foi executado e o que resta executar;</li>
        <li>a redução de custos e prazos de mobilização/desmobilização e de novo procedimento licitatório;</li>
        @if($vm->categoria === 'compras_servicos')
            <li>o risco de descontinuidade do {{ $vm->tipoObjetoContrato?->substantivoAtividade() ?? 'fornecimento/serviço' }} durante a tramitação de novo procedimento;</li>
        @endif
        @if($vm->ehObras())
            <li>o risco de deterioração das estruturas/etapas já executadas e de incompatibilidade técnica entre executores distintos;</li>
        @endif
    </ul>
    <div class="clause-content">
        Diante do exposto, esta Secretaria entende que
        @if($vm->envolvePrazo() && $vm->envolveValor())
            a prorrogação do prazo e a alteração quantitativa pretendidas apresentam
        @elseif($vm->envolvePrazo())
            a prorrogação do prazo apresenta
        @else
            a alteração quantitativa pretendida apresenta
        @endif
        maior vantagem administrativa e operacional para a Administração do que a realização de nova contratação, observados os limites legais, os preços de mercado e a ausência de descaracterização do objeto originalmente contratado.
    </div>

    <div class="clause-content" style="margin-top: 30px;">
        A conclusão definitiva quanto à vantajosidade fica condicionada à manifestação técnica da Engenharia/setor competente
        @if($vm->exigeDotacaoOrcamentaria())
            , à disponibilidade orçamentária
        @endif
        , ao parecer jurídico e à autorização da autoridade competente.
    </div>

    <div style="margin-top: 40px;">
        {{ $prefeitura->cidade ?? 'Cidade' }}, {{ \Carbon\Carbon::parse($data_selecionada ?? now())->translatedFormat('d \d\e F \d\e Y') }}
    </div>

    @include('Admin.Processos.pdf.aditivos.partials.bloco-assinaturas', [
        'nomeFallback' => $incidente->nome_solicitante ?? ($contrato->dados_contratante['orgao_responsavel'] ?? 'SECRETARIA MUNICIPAL'),
        'cargoFallback' => $incidente->cargo_solicitante ?? 'Secretário(a)',
    ])

    <div class="page-break"></div>

    <div style="margin-top: 40px; font-size: 11pt;">
        <p>
            <strong>De:</strong> {{ $prefeitura->autoridade_competente ?? 'Prefeito Municipal' }} <br>
            <strong>Para:</strong> Procuradoria/Assessoria Jurídica
        </p>
        <p style="text-indent: 40px;">Sr. Procurador(a)/Assessor(a) Jurídico(a),</p>
        <p style="text-indent: 40px;">
            Tendo em vista a solicitação da {{ $contrato->dados_contratante['orgao_responsavel'] ?? 'Secretaria' }}, sobre o Aditivo ao Contrato n. {{ $vm->numeroContrato }}, solicitamos a Vossa Senhoria que emita parecer jurídico sobre a legalidade do justificado e requerido.
        </p>
        <p style="text-indent: 40px;">Sem mais, pedimos a maior brevidade possível.</p>
        <p style="text-indent: 40px;">Atenciosamente.</p>
    </div>

    <div class="signature-block">
        ___________________________________<br>
        <strong>{{ $prefeitura->autoridade_competente ?? 'Prefeito Municipal' }}</strong><br>
        {{ $prefeitura->cargo_autoridade ?? 'Prefeito Municipal' }}
    </div>
</body>
</html>
