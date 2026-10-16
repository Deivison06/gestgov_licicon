{{--
    Relatório de Pesquisa de Preços (10 seções) — tipo_relatorio_analise_mercado = 'ia_claude'.
    Inclui no bloco @elseif($tipoRelatorio === 'ia_claude') de cada analise_mercado.blade.php.
    Herda o <style> e as variáveis $processo/$prefeitura/$detalhe/$dataSelecionada já disponíveis na view pai.
--}}
@php
    $etpItensSecao = $processo->etp?->all_itens ?? collect();
    $memoriaPorItemId = $processo->pesquisaPrecoMemoriaItens->keyBy('etp_item_id');
    $memoriaPorOrdem = $processo->pesquisaPrecoMemoriaItens->keyBy('ordem');

    $valorTotalEstimado = 0;
    $linhasValorEstimado = $etpItensSecao->values()->map(function ($item, $idx) use ($memoriaPorItemId, $memoriaPorOrdem, &$valorTotalEstimado) {
        $memoria = $memoriaPorItemId->get($item->id) ?? $memoriaPorOrdem->get($idx + 1);
        $valorUnitario = $memoria->valor_adotado ?? 0;
        $quantidade = (float) ($item->pivot->quantidade ?? 0);
        $valorTotal = $valorUnitario * $quantidade;
        $valorTotalEstimado += $valorTotal;

        return [
            'numero' => $idx + 1,
            'descricao' => $item->descricao_item,
            'quantidade' => $quantidade,
            'valor_unitario' => $valorUnitario,
            'valor_total' => $valorTotal,
        ];
    });

    $valorTotalExtenso = \App\Helpers\ValorPorExtenso::escrever($valorTotalEstimado);
@endphp

<p class="item-titulo">1. IDENTIFICAÇÃO</p>
<p>
    <strong>Objeto:</strong> {!! strip_tags($processo->objeto) !!}.<br>
    <strong>Data da elaboração:</strong> {{ \Carbon\Carbon::parse($dataSelecionada)->format('d/m/Y') }}
</p>

<p class="item-titulo">2. FINALIDADE DO RELATÓRIO</p>
<p>O presente relatório tem por finalidade demonstrar a metodologia utilizada para a estimativa do valor da
contratação pretendida, com vistas a assegurar que o preço de referência seja compatível com os valores
praticados no mercado, em observância à Lei nº 14.133/2021.</p>
<p>A pesquisa de preços foi realizada para subsidiar a fase preparatória da contratação, permitindo à
Administração Pública avaliar a razoabilidade dos valores estimados, planejar adequadamente a despesa e
definir parâmetro adequado para a análise da aceitabilidade das propostas.</p>

<p class="item-titulo">3. DESCRIÇÃO DO OBJETO PESQUISADO</p>
<p>A presente pesquisa refere-se à contratação de:</p>
<p>{!! strip_tags($processo->objeto) !!}.</p>
<p>O objeto encontra-se sintetizado nos seguintes termos:</p>
<table>
    <thead>
        <tr style="background-color:#D9E1F2;">
            <th style="width:10%;">Item</th>
            <th style="width:55%;">Descrição</th>
            <th style="width:15%;">Unidade</th>
            <th style="width:20%;">Quantidade</th>
        </tr>
    </thead>
    <tbody>
        @foreach($etpItensSecao as $idx => $item)
        <tr>
            <td>{{ $idx + 1 }}</td>
            <td class="left">{{ $item->descricao_item }}</td>
            <td>{{ $item->pivot->unidade ?? '—' }}</td>
            <td>{{ $item->pivot->quantidade ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
<p>A descrição do objeto foi considerada como referência para a coleta dos preços, observando-se as
características essenciais da contratação pretendida e as condições normalmente praticadas no mercado.</p>

<p class="item-titulo">4. FUNDAMENTAÇÃO LEGAL</p>
<p>A estimativa de preços foi elaborada com fundamento na Lei nº 14.133/2021, especialmente quanto à
necessidade de que o valor estimado da contratação seja compatível com os preços praticados pelo mercado.
Foram aplicados o art. 23, § 1º, incisos II e V, da Lei nº 14.133/2021, o art. 5º, incisos II e V, e o art. 6º
da IN SEGES/ME nº 65/2021 e o art. 5º, incisos I e II e § 1º, da Resolução TCE/PI nº 17/2024, observado o
prazo de 1 (um) ano anterior à data da pesquisa para as contratações similares e para as notas fiscais
eletrônicas.</p>
<p>Para tanto, foram consideradas referências idôneas, atuais e pertinentes ao objeto, observadas as
características técnicas, a unidade de fornecimento, a data dos preços e demais fatores capazes de influenciar
a formação do preço.</p>

<p class="item-titulo">5. METODOLOGIA ADOTADA</p>
<p>A pesquisa de preços foi realizada mediante consulta a fontes compatíveis com o objeto da contratação,
buscando-se obter referências representativas dos valores efetivamente praticados no mercado.</p>
<p>Foram utilizadas as seguintes fontes:</p>
<ol type="a" style="padding-left: 20px; margin: 4pt 0 8pt;">
    <li>bancos públicos de preços;</li>
    <li>contratações públicas similares (contratos registrados em Painéis de Preços Públicos);</li>
    <li>notas fiscais eletrônicas (registros em Painéis de Preços Públicos);</li>
    <li>pesquisa direta junto a fornecedor do ramo (cotação do fornecedor local).</li>
</ol>
<p>As referências obtidas foram analisadas quanto à compatibilidade com o objeto, atualidade, confiabilidade e
aderência às condições da futura contratação.</p>

<p class="item-titulo">6. FONTES CONSULTADAS</p>
<p style="white-space: pre-line;">{{ $detalhe->fontes_consultadas ?: '—' }}</p>

<p class="item-titulo">7. ANÁLISE DOS PREÇOS COLETADOS</p>
<p style="white-space: pre-line;">{{ $detalhe->analise_precos_coletados ?: '—' }}</p>

<p class="item-titulo">8. MEMÓRIA DE CÁLCULO</p>
<p>Para a definição do preço estimado, foi utilizado o seguinte critério:</p>
<p><strong>Média</strong> — média aritmética simples dos valores unitários de cada item, desconsideradas as
fontes sem preço.<br>
<strong>Seleção das referências</strong> — entre os preços equivalentes obtidos, priorizou-se a maior
aderência à especificação, a data mais recente e a diversidade de órgãos.</p>

@foreach($processo->pesquisaPrecoMemoriaItens as $memoriaItem)
@php
    $etpItemMemoria = $memoriaItem->etpItem;
    $descricaoMemoria = $etpItemMemoria?->descricao_item ?? $memoriaItem->titulo;
    $numeroItemMemoria = str_pad((string) ($memoriaItem->numero_item ?? $memoriaItem->ordem), 2, '0', STR_PAD_LEFT);
    $rotuloItemMemoria = ($memoriaItem->lote ? 'Lote '.$memoriaItem->lote.' · ' : '').'Item '.($memoriaItem->numero_item ?? $memoriaItem->ordem);
@endphp
{{--
    dompdf (nesta versão) não respeita <colgroup>/<col> em table-layout:fixed — a largura das
    colunas é definida pela PRIMEIRA linha da tabela. Por isso o título do item fica numa tabela
    separada (coluna única) e a tabela de fontes já nasce com a linha de dados (80%/20%) na primeira posição.
--}}
<table style="margin-bottom: 0; table-layout: fixed;">
    <tr style="background-color:#D9E1F2;">
        <td class="left" style="font-weight:bold; word-break: break-word;">
            {{ $rotuloItemMemoria }} — {{ $memoriaItem->titulo }}: {{ $descricaoMemoria }}
        </td>
    </tr>
</table>
<table style="margin-top: 0; margin-bottom: 10pt; table-layout: fixed;">
    @foreach($memoriaItem->fontes as $fonte)
    @php
        if (($fonte['tipo'] ?? null) === 'painel_tce') {
            $fonteLabel = 'Fonte '.($fonte['ordem'] ?? '-').' · Painel TCE/PI';
            if (! empty($fonte['orgao_nome'])) {
                $fonteLabel .= ' · '.$fonte['orgao_nome'];
            }
            if (! empty($fonte['uf'])) {
                $fonteLabel .= ' — '.$fonte['uf'];
            }
        } elseif (($fonte['tipo'] ?? null) === 'fornecedor_local') {
            $fonteLabel = 'Fonte '.($fonte['ordem'] ?? '-').' · Fornecedor local';
        } else {
            $fonteLabel = 'Fonte '.($fonte['ordem'] ?? '-').' · '.($fonte['orgao_nome'] ?? 'Outra fonte');
        }

        $qtdLinha = null;
        if (! empty($fonte['qtd_comprada'])) {
            $qtdLinha = 'Qtd. comprada: '.$fonte['qtd_comprada'].' '.$fonte['unidade_comprada'];
            if (! empty($fonte['data_referencia'])) {
                $qtdLinha .= ' · Data: '.$fonte['data_referencia'];
            }
            if (! empty($fonte['processo_origem'])) {
                $qtdLinha .= ' · Processo: '.$fonte['processo_origem'];
            }
        }

        $fornecedorLinha = null;
        if (! empty($fonte['fornecedor_nome'])) {
            $fornecedorLinha = $fonte['fornecedor_nome'];
            if (! empty($fonte['fornecedor_cnpj'])) {
                $fornecedorLinha .= ' · CNPJ '.$fonte['fornecedor_cnpj'];
            }
        }

        $ehPainelTce = ($fonte['tipo'] ?? null) === 'painel_tce';
    @endphp
    <tr>
        <td class="left" style="width:80%; word-break: break-word;">
            <strong>{{ $fonteLabel }}</strong><br>
            @if(!empty($fonte['descricao']))<strong>Descrição:</strong> {{ $fonte['descricao'] }}<br>@endif
            @if($qtdLinha){{ $qtdLinha }}<br>@endif
            @if($fornecedorLinha)<strong>Fornecedor:</strong> {{ $fornecedorLinha }}<br>@endif
            @if(!empty($fonte['link']))<strong>Link de acesso:</strong><br><span style="word-break: break-all;">{{ $fonte['link'] }}</span><br>@endif
            @if($ehPainelTce)<strong>Comprovante:</strong> Item {{ $numeroItemMemoria }} - Painel TCE-PI.pdf @endif
        </td>
        <td style="width:20%; vertical-align:middle;">R$ {{ number_format($fonte['valor_unitario'] ?? 0, 2, ',', '.') }}</td>
    </tr>
    @endforeach
    <tr style="background-color:#D9E1F2; font-weight:bold;">
        <td style="width:80%; text-align:center;">VALOR ADOTADO</td>
        <td style="width:20%;">R$ {{ number_format($memoriaItem->valor_adotado, 2, ',', '.') }}</td>
    </tr>
</table>
@endforeach

<p class="item-titulo">9. VALOR ESTIMADO DA CONTRATAÇÃO</p>
<p>Com base nas referências obtidas e na análise dos preços coletados, chegou-se ao seguinte valor estimado:</p>
<table>
    <thead>
        <tr style="background-color:#D9E1F2;">
            <th style="width:8%;">Item</th>
            <th style="width:42%;">Descrição</th>
            <th style="width:15%;">Quantidade</th>
            <th style="width:17%;">Valor unitário estimado</th>
            <th style="width:18%;">Valor total estimado</th>
        </tr>
    </thead>
    <tbody>
        @foreach($linhasValorEstimado as $linha)
        <tr>
            <td>{{ $linha['numero'] }}</td>
            <td class="left">{{ $linha['descricao'] }}</td>
            <td>{{ $linha['quantidade'] }}</td>
            <td>R$ {{ number_format($linha['valor_unitario'], 2, ',', '.') }}</td>
            <td>R$ {{ number_format($linha['valor_total'], 2, ',', '.') }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="4" style="text-align:right; font-weight:bold; padding-right:8px;">VALOR TOTAL ESTIMADO DA CONTRATAÇÃO</td>
            <td style="font-weight:bold;">R$ {{ number_format($valorTotalEstimado, 2, ',', '.') }}</td>
        </tr>
    </tbody>
</table>
<p>O valor estimado acima reflete os preços praticados no mercado para o objeto da contratação, considerando
as informações disponíveis, as fontes consultadas e os parâmetros analisados no momento da pesquisa.</p>

<p class="item-titulo">10. CONCLUSÃO</p>
<p>Diante da pesquisa realizada, conclui-se que o valor estimado de R$ {{ number_format($valorTotalEstimado, 2, ',', '.') }}
({{ $valorTotalExtenso }}) é compatível com os preços praticados no mercado para o objeto pretendido.</p>
<p>As referências utilizadas foram consideradas suficientes e adequadas para representar a realidade
mercadológica da contratação, tendo sido observadas fontes idôneas, atuais e pertinentes ao objeto.</p>
<p>Assim, o presente relatório demonstra a formação do preço estimado e poderá subsidiar a continuidade da
fase preparatória, o planejamento da despesa e a análise da aceitabilidade das propostas no respectivo
procedimento de contratação.</p>
