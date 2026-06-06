<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>RELATÓRIO DE COTAÇÃO - {{ $processo->numero_processo ?? $processo->id }}</title>
    <style type="text/css">
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
        @page {
            margin: 0;
            size: A4;
        }
        body {
            margin: 0;
            padding: 4cm 2cm 2cm;
            font-size: 11pt;
            font-family: 'Aptos', sans-serif;
            background-image: url('{{ public_path($prefeitura->timbre) }}');
            background-repeat: no-repeat;
            background-position: top left;
            background-size: cover;
            text-align: justify;
            text-justify: inter-word;
            line-height: 1.4;
        }
        .page-break { page-break-after: always; }

        .titulo-relatorio {
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 2pt;
            font-family: 'AptosExtraBold', sans-serif;
        }
        .subtitulo-pesquisa {
            text-align: center;
            font-size: 10pt;
            margin-bottom: 14pt;
        }
        .item-titulo {
            font-weight: bold;
            font-size: 10pt;
            margin: 12pt 0 3pt;
        }
        p { margin: 0 0 8pt; }
        ul { padding-left: 20px; margin: 4pt 0 8pt; }
        li { margin-bottom: 4pt; text-align: justify; }

        table {
            border-collapse: collapse;
            width: 100%;
            font-size: 8pt;
            margin-bottom: 8pt;
        }
        table th, table td {
            border: 1px solid black;
            padding: 4px 5px;
            text-align: center;
        }
        table td.left, table th.left { text-align: left; }

        .footer-signature { margin-top: 50px; text-align: right; }
        .signature-block  { margin-top: 50px; text-align: center; }

        /* ── CAPA ── */
        #cover-page {
            height: 100vh;
            width: 100%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }
        .cover-image {
            width: 300px;
            height: 300px;
            margin: 0 auto 30px;
            display: block;
        }
        .cover-title {
            width: 60%;
            font-size: 18pt;
            font-weight: 900;
            border: 2px solid #000;
            display: inline-block;
            line-height: 0.9;
            padding: 10px 50px;
            font-family: 'AptosExtraBold', sans-serif;
        }
    </style>
</head>
<body>

    {{-- ── CAPA ─────────────────────────────────────────────────── --}}
    <div id="cover-page">
        <img src="{{ public_path('icons/capa-documento.png') }}" alt="Capa" class="cover-image">
        <div class="cover-title">
            ANÁLISE DE MERCADO<br>
            (PESQUISA DE PREÇOS)
        </div>
    </div>
    <div class="page-break"></div>

@php
    $tipoRelatorio = $detalhe->tipo_relatorio_analise_mercado ?? 'tce';

    // Calcula intervalo de datas da pesquisa
    $allItems    = $processo->pesquisaPrecoItens ?? collect();
    $datasItems  = $allItems->whereNotNull('data_publicacao')->pluck('data_publicacao');
    $dataFim     = \Carbon\Carbon::parse($dataSelecionada)->format('d/m/Y');
    $dataInicio  = $datasItems->isNotEmpty()
        ? $datasItems->min()->format('d/m/Y')
        : $dataFim;
@endphp

{{-- ── CABEÇALHO ──────────────────────────────────────────────── --}}
<div class="titulo-relatorio">
    RELATÓRIO DE COTAÇÃO: {!! strip_tags($processo->objeto) !!}
</div>
<div class="subtitulo-pesquisa">
    Pesquisa realizada entre {{ $dataInicio }} e {{ $dataFim }}
</div>

{{-- ── TEXTO INTRODUTÓRIO ─────────────────────────────────────── --}}
@if($tipoRelatorio === 'tce')

    <p>Relatório de Pesquisa de Preço obtido junto ao painel de preços do Tribunal de
    Contas do Estado do Piauí, seguindo os parâmetros do Art. 22, da Lei Federal
    14.133/21, instruções normativas e decreto municipal.</p>

@elseif($tipoRelatorio === 'cesta_preco')

    <p>Relatório de Pesquisa de Preço obtido junto ao Portal Nacional de
    Contratação Públicas (PNCP) e junto a Fornecedores Locais, seguindo os
    parâmetros do Art. 22, da Lei Federal 14.133/21, instruções normativas e decreto
    municipal.</p>

@elseif($tipoRelatorio === 'pncp')

    <p>Relatório de Pesquisa de Preço obtido junto ao Portal Nacional de
    Contratação Públicas (PNCP), seguindo os parâmetros do Art. 22, da Lei Federal
    14.133/21, instruções normativas e decreto municipal.</p>

@elseif($tipoRelatorio === 'fornecedor_local')

    <p>Relatório de Pesquisa de Preço obtido junto a fornecedores do mercado local, em
    observância ao Art. 23, § 1º, inciso IV, da Lei nº 14.133/2021. A opção pela cotação direta
    no mercado regional justifica-se pela necessidade imperiosa de obter uma estimativa
    fidedigna, que contemple as especificidades logísticas, de frete e de fornecimento próprias
    da região de execução do objeto. Tal metodologia encontra pleno amparo na Lei nº
    14.133/2021, especificamente nos seguintes dispositivos:</p>

    <ul>
        <li><strong>Art. 23, § 1º, inciso IV:</strong> Autoriza expressamente a realização de pesquisa mediante
        a consulta direta com fornecedores, desde que as cotações atendam ao prazo de
        validade de até 6 (seis) meses;</li>
        <li><strong>Art. 23, § 2º:</strong> Faculta à Administração a utilização de outros parâmetros de
        balizamento, além dos sistemas oficiais, desde que devidamente justificados,
        visando a busca pelo preço real de mercado;</li>
        <li><strong>Art. 11, inciso I:</strong> Estabelece que o processo licitatório deve assegurar a seleção da
        proposta mais vantajosa, o que pressupõe um orçamento condizente com a
        realidade local.</li>
    </ul>

    <p>A presente instrução prioriza o Princípio da Economicidade, garantindo que o valor
    estimado guarde plena aderência com os custos de mercado praticados na localidade da
    prestação, pautando-se nos seguintes pilares técnicos:</p>

    <ul>
        <li><strong>Particularidades Logísticas:</strong> O mercado local apresenta custos de frete,
        deslocamento e logística que muitas vezes não são contemplados em
        bancos de dados nacionais ou tabelas de referência de outros entes
        federativos. A cotação local garante que o preço estimado inclua essas
        variáveis indispensáveis para a execução do contrato.</li>
        <li><strong>Fidedignidade do Valor Estimado:</strong> Em observância ao Art. 18, inciso II, a fase
        de planejamento exige uma estimativa de despesa precisa. A consulta direta
        aos fornecedores que efetivamente atuam na localidade mitiga o risco de
        orçamentos subestimados (que geram licitações desertas) ou
        superestimados (que causam prejuízo ao erário).</li>
        <li><strong>Garantia de Exequibilidade:</strong> A jurisprudência pátria orienta que a pesquisa de
        preços deve formar uma "cesta de preços" representativa que reflita a
        realidade fática da contratação. Ao consultar o mercado regional, a
        Administração evita distorções comuns em bancos de dados genéricos que
        podem levar à fixação de preços inexequíveis, garantindo a seleção da
        proposta mais vantajosa e evitando a frustração do certame.</li>
    </ul>

    <p>Desta forma, a utilização do mercado local como parâmetro assegura que a
    Administração opere com valores praticáveis, protegendo o processo licitatório de
    eventuais inexecuções contratuais e garantindo o melhor atendimento ao interesse
    público, conforme tabela abaixo:</p>

@endif

{{-- ── TABELA TCE ──────────────────────────────────────────────── --}}
@if($tipoRelatorio === 'tce')
@php
    $painel = is_array($detalhe->painel_preco_tce)
        ? $detalhe->painel_preco_tce
        : json_decode($detalhe->painel_preco_tce ?? '[]', true);
@endphp
<table>
    <thead>
        <tr>
            <th rowspan="2" class="left" style="width:35%;">ITEM</th>
            <th colspan="3">VALOR TCE</th>
            <th rowspan="2" style="width:15%;">MÉDIA<br>GERAL</th>
        </tr>
        <tr>
            <th style="width:15%;">VALOR TCE</th>
            <th style="width:15%;">VALOR TCE</th>
            <th style="width:15%;">VALOR TCE</th>
        </tr>
    </thead>
    <tbody>
        @forelse($painel ?? [] as $item)
        <tr>
            <td class="left">{{ $item['item'] ?? '' }}</td>
            <td>{{ $item['valor_tce_1'] ?? '' }}</td>
            <td>{{ $item['valor_tce_2'] ?? '' }}</td>
            <td>{{ $item['valor_tce_3'] ?? '' }}</td>
            <td><strong>{{ $item['media'] ?? '' }}</strong></td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center;">Nenhum dado disponível</td></tr>
        @endforelse
    </tbody>
</table>

{{-- ── TABELA FORNECEDOR LOCAL ────────────────────────────────── --}}
@elseif($tipoRelatorio === 'fornecedor_local')
@php
    $flPrecos = is_array($detalhe->fornecedor_local_precos)
        ? $detalhe->fornecedor_local_precos
        : (json_decode($detalhe->fornecedor_local_precos ?? '[]', true) ?? []);
    $fmt = fn($v) => ($v !== null && $v !== '') ? 'R$ ' . number_format((float)$v, 2, ',', '.') : '—';
@endphp
<table>
    <thead>
        <tr>
            <th colspan="5">RELATÓRIO FORNECEDORES LOCAIS</th>
        </tr>
        <tr>
            <th class="left" style="width:34%;">ITEM</th>
            <th style="width:16%;">FORNECEDOR<br>1 (UM)</th>
            <th style="width:16%;">FORNECEDOR<br>2 (DOIS)</th>
            <th style="width:16%;">FORNECEDOR<br>3 (TRÊS)</th>
            <th style="width:14%;">MÉDIA<br>GERAL</th>
        </tr>
    </thead>
    <tbody>
        @forelse($flPrecos as $fl)
        @php
            $vals  = collect([$fl['f1_preco'] ?? null, $fl['f2_preco'] ?? null, $fl['f3_preco'] ?? null])
                        ->filter(fn($v) => $v !== null && $v !== '');
            $media = $vals->count() > 0 ? $vals->avg() : null;
        @endphp
        <tr>
            <td class="left">{{ $fl['descricao'] ?? '' }}</td>
            <td>{{ $fmt($fl['f1_preco'] ?? null) }}</td>
            <td>{{ $fmt($fl['f2_preco'] ?? null) }}</td>
            <td>{{ $fmt($fl['f3_preco'] ?? null) }}</td>
            <td><strong>{{ $media !== null ? 'R$ ' . number_format($media, 2, ',', '.') : '—' }}</strong></td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center;">Nenhum dado disponível</td></tr>
        @endforelse
    </tbody>
</table>

{{-- ── TABELA CESTA DE PREÇOS ─────────────────────────────────── --}}
@elseif($tipoRelatorio === 'cesta_preco')
@php
    $todosItens = $processo->pesquisaPrecoItens ?? collect();
    $grupos     = $todosItens->groupBy(fn($i) => $i->etp_item_id
        ? 'id_' . $i->etp_item_id
        : 'desc_' . strtolower(trim($i->descricao)));
    $numGrupo   = 0;
@endphp

@forelse($grupos as $grupoItens)
@php
    $numGrupo++;
    $primeiro    = $grupoItens->first();
    $titulo      = $primeiro->descricao;
    if ($primeiro->etp_item_id && $processo->etp) {
        $etpItem = $processo->etp->all_itens->firstWhere('id', $primeiro->etp_item_id);
        if ($etpItem) $titulo = $etpItem->descricao_item;
    }
    // PNCP primeiro, fornecedor local por último
    $grupoItens = $grupoItens->sortBy(fn($i) => $i->orgao_nome === 'PREÇOS DO FORNECEDOR LOCAL' ? 1 : 0);
    $precos  = $grupoItens->pluck('valor_unitario')->filter();
    $media   = $precos->count() > 0 ? $precos->avg() : null;
    $numRef  = 0;
@endphp

<p class="item-titulo">ITEM {{ $numGrupo }}: {{ $titulo }}</p>
<table>
    <thead>
        <tr>
            <th style="width:8%;">Nº DO<br>PREÇO<br>PESQUISADO</th>
            <th style="width:35%;">ÓRGÃO PÚBLICO E IDENTIFICAÇÃO DO PROCESSO</th>
            <th style="width:23%;">LINK DO PNCP</th>
            <th style="width:12%;">DATA DA<br>LICITAÇÃO</th>
            <th style="width:12%;">PREÇO</th>
        </tr>
    </thead>
    <tbody>
        @foreach($grupoItens as $ref)
        @php $numRef++; @endphp
        @if($ref->orgao_nome === 'PREÇOS DO FORNECEDOR LOCAL')
        <tr>
            <td>{{ $numRef }}</td>
            <td class="left">PREÇOS DO FORNECEDOR LOCAL
                @if($ref->fornecedor_nome) <br><small style="font-size:7pt;">{{ $ref->fornecedor_nome }}</small>@endif
            </td>
            <td>---------</td>
            <td>----------</td>
            <td>R$ {{ number_format($ref->valor_unitario, 2, ',', '.') }}</td>
        </tr>
        @else
        <tr>
            <td>{{ $numRef }}</td>
            <td class="left">{{ $ref->orgao_nome }}
                @php
                    $identificacao = trim(($ref->modalidade ? $ref->modalidade . ' ' : '') . ($ref->numero_processo ?? ''));
                @endphp
                @if($identificacao) <br><small style="font-size:7pt;">{{ $identificacao }}</small>@endif
            </td>
            <td style="font-size:6.5pt; word-break:break-all;">{{ $ref->link_pncp ?? '—' }}</td>
            <td>{{ $ref->data_publicacao ? \Carbon\Carbon::parse($ref->data_publicacao)->format('d/m/Y') : '—' }}</td>
            <td>R$ {{ number_format($ref->valor_unitario, 2, ',', '.') }}</td>
        </tr>
        @endif
        @endforeach
        <tr>
            <td colspan="4" style="text-align:right; font-weight:bold; padding-right:8px;">MÉDIA DOS PREÇOS OBTIDOS</td>
            <td style="font-weight:bold;">{{ $media !== null ? 'R$ ' . number_format($media, 2, ',', '.') : '—' }}</td>
        </tr>
    </tbody>
</table>
@empty
<p style="text-align:center;">Nenhuma referência de preço coletada.</p>
@endforelse

{{-- ── TABELA PNCP ─────────────────────────────────────────────── --}}
@elseif($tipoRelatorio === 'pncp')
@php
    $itensPncp  = ($processo->pesquisaPrecoItens ?? collect())
                    ->where('orgao_nome', '!=', 'PREÇOS DO FORNECEDOR LOCAL');
    $grupos     = $itensPncp->groupBy(fn($i) => $i->etp_item_id
        ? 'id_' . $i->etp_item_id
        : 'desc_' . strtolower(trim($i->descricao)));
    $numGrupo   = 0;
@endphp

@forelse($grupos as $grupoItens)
@php
    $numGrupo++;
    $primeiro = $grupoItens->first();
    $titulo   = $primeiro->descricao;
    if ($primeiro->etp_item_id && $processo->etp) {
        $etpItem = $processo->etp->all_itens->firstWhere('id', $primeiro->etp_item_id);
        if ($etpItem) $titulo = $etpItem->descricao_item;
    }
    $precos  = $grupoItens->pluck('valor_unitario')->filter();
    $media   = $precos->count() > 0 ? $precos->avg() : null;
    $numRef  = 0;
@endphp

<p class="item-titulo">ITEM {{ $numGrupo }}: {{ $titulo }}</p>
<table>
    <thead>
        <tr>
            <th style="width:8%;">Nº DO<br>PREÇO<br>PESQUISADO</th>
            <th style="width:37%;">ÓRGÃO PÚBLICO E IDENTIFICAÇÃO DO PROCESSO</th>
            <th style="width:23%;">LINK DO PNCP</th>
            <th style="width:12%;">DATA DA<br>LICITAÇÃO</th>
            <th style="width:12%;">PREÇO</th>
        </tr>
    </thead>
    <tbody>
        @foreach($grupoItens as $ref)
        @php $numRef++; @endphp
        <tr>
            <td>{{ $numRef }}</td>
            <td class="left">{{ $ref->orgao_nome }}
                @php
                    $identificacao = trim(($ref->modalidade ? $ref->modalidade . ' ' : '') . ($ref->numero_processo ?? ''));
                @endphp
                @if($identificacao) <br><small style="font-size:7pt;">{{ $identificacao }}</small>@endif
            </td>
            <td style="font-size:6.5pt; word-break:break-all;">{{ $ref->link_pncp ?? '—' }}</td>
            <td>{{ $ref->data_publicacao ? \Carbon\Carbon::parse($ref->data_publicacao)->format('d/m/Y') : '—' }}</td>
            <td>R$ {{ number_format($ref->valor_unitario, 2, ',', '.') }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="4" style="text-align:right; font-weight:bold; padding-right:8px;">MÉDIA DOS PREÇOS OBTIDOS</td>
            <td style="font-weight:bold;">{{ $media !== null ? 'R$ ' . number_format($media, 2, ',', '.') : '—' }}</td>
        </tr>
    </tbody>
</table>
@empty
<p style="text-align:center;">Nenhuma referência de preço coletada no PNCP.</p>
@endforelse

@endif
{{-- /fim tabelas --}}

{{-- ── ASSINATURA ──────────────────────────────────────────────── --}}
<div class="footer-signature">
    {{ $processo->prefeitura->cidade }},
    {{ \Carbon\Carbon::parse($dataSelecionada)->translatedFormat('d \d\e F \d\e Y') }}
</div>

@php $hasSelectedAssinantes = isset($assinantes) && count($assinantes) > 0; @endphp
@if($hasSelectedAssinantes)
    @php $primeiroAssinante = $assinantes[0]; @endphp
    <div style="margin-top: 40px; text-align: center;">
        <div class="signature-block" style="display: inline-block; margin: 0 40px;">
            ___________________________________<br>
            <p style="line-height: 1.2;">
                {{ $primeiroAssinante['responsavel'] }}<br>
                <span>{{ $primeiroAssinante['unidade_nome'] }}</span>
            </p>
        </div>
    </div>
@else
    <div class="signature-block" style="margin-top: 40px; text-align: center;">
        ___________________________________<br>
        <p style="line-height: 1.2;">
            {{ $processo->prefeitura->autoridade_competente }}
        </p>
    </div>
@endif

</body>
</html>
