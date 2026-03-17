<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>PCA - {{ $pca->numero_pca ?? $pca->id }}</title>
    <style>
        @page {
            margin: 0;
            size: A4;
        }

        body {
            margin: 0;
            padding: 4cm 1cm;
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
            line-height: 1.3;
            text-align: justify;
        }

        .timbre-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1000;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-sm { font-size: 14px; }
        .uppercase { text-transform: uppercase; }
        .bold { font-weight: bold; }
        .mt-2 { margin-top: 20px; }
        .mt-4 { margin-top: 40px; }

        p { margin: 6px 0; text-indent: 25px; }
        ul { margin: 6px 0; padding-left: 40px; }
        ol { margin: 6px 0; padding-left: 40px; }

        .page-break { page-break-after: always; }

        /* Estilos para Capa e Contra-capa */
        .cover-page {
            padding-top: 100px;
            font-size: 20px;
            line-height: 2;
        }
        .inside-cover {
            padding-top: 50px;
            font-size: 14px;
            line-height: 1.8;
        }

        /* Classe para o corpo de texto com recuo lateral */
        .content-body {
            margin: 0 5rem;
        }

        /* Tabela no DOMPDF */
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 8px;
            table-layout: fixed;
            word-wrap: break-word;
        }
        .table-data th, .table-data td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
        }
        .table-data th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .table-data td.desc {
            text-align: justify;
        }
    </style>
</head>
<body>
    @php
        $timbre = $pca->prefeitura->timbre ?? '';
        $timbrePath = public_path($timbre);
        $base64Timbre = '';

        if ($timbre && file_exists($timbrePath)) {
            $type = pathinfo($timbrePath, PATHINFO_EXTENSION);
            $data = file_get_contents($timbrePath);
            $base64Timbre = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
    @endphp

    @if($base64Timbre)
        <img class="timbre-bg" src="{{ $base64Timbre }}" alt="Timbre">
    @endif

    <div class="cover-page text-center uppercase bold">
        {{ $pca->prefeitura->nome ?? 'PREFEITURA NÃO INFORMADA' }} / PI<br>
    </div>
    <div class="cover-page text-center uppercase bold" style="padding-top: 8rem;">
        Plano de Contratações Anual<br>
        Exercício {{ $pca->exercicio }}
    </div>

    <div class="page-break"></div>

    <div class="bold text-sm text-center uppercase mt-4">Equipe de Elaboração PCA</div>
    <div class="text-center mt-2">
        @if(!empty($pca->equipe_elaboracao) && is_array($pca->equipe_elaboracao))
            @foreach($pca->equipe_elaboracao as $membro)
                <div style="margin-bottom: 1rem;">
                    <span class="bold uppercase">{{ $membro['responsavel'] ?? 'N/I' }}</span><br>
                    <span style="font-size: 10px;">{{ \App\Models\Unidade::find($membro['unidade_id'])->nome ?? 'N/I' }}</span>
                </div>
            @endforeach
        @else
            <div class="text-center">Nenhum membro informado.</div>
        @endif
    </div>

    <div class="page-break"></div>

    <div class="content-body">
        <div class="bold text-sm uppercase">REGULAMENTAÇÃO:</div>
        <p style="text-indent: 0;">Lei n.º 14.133/2021, art. 12, inciso VII – a partir de documentos de formalização de demandas, os órgãos responsáveis pelo planejamento de cada ente federativo poderão, na forma de regulamento, elaborar plano de contratação anual, com o objetivo de racionalizar as contratações dos órgãos e entidades sob sua competencia, garantir o alinhamento com o seu planejamento estratégico e subsidiar a elaboração das respectivas leis orçamentárias.</p>

        <div class="bold text-sm uppercase mt-2">INFORMAÇÕES DA UNIDADE</div>
        <div style="margin-top: 5px; margin-bottom: 20px;">
            <span class="bold">Órgão:</span> – {{ mb_strtoupper($pca->prefeitura->nome ?? 'PREFEITURA NÃO INFORMADA') }}<br>
            <span class="bold">Período Elaboração do PCA:</span>
            @if($pca->periodo_elaboracao_inicio && $pca->periodo_elaboracao_fim)
                {{ $pca->periodo_elaboracao_inicio->translatedFormat('F') }} a {{ $pca->periodo_elaboracao_fim->translatedFormat('F \d\e Y') }}
            @else
                Não informado
            @endif
        </div>

        <div>
            <p>O Plano de Contratação Anual – PCA trata-se de uma importante inovação trazida pela Nova Lei de Licitações. O PCA deve ser elaborado pelos órgãos responsáveis pelo planejamento de cada ente federativo, visando racionalizar as contratações dos órgãos e entidades sob sua competência, garantir o alinhamento com o seu planejamento estratégico e subsidiar a elaboração das respectivas leis orçamentárias.</p>
            <p>Referido plano de contratações anual deverá ser divulgado e mantido à disposição do público em sítio eletrônico oficial e será observado pelo ente federativo na realização de licitações e na execução dos contratos.</p>
            <p>A elaboração do plano tem caráter obrigatório e altamente recomendável, pelo seu potencial de contribuir para reduzir desperdícios e falhas, aprimorar a gestão de aquisições e contratos e conferir maior realismo à elaboração dos orçamentos.</p>
            <p>Assim sendo, este Plano de Contratação Anual - PCA materializa-se como o fruto de uma gestão comprometida com resultados e com a transparência dos gastos públicos.</p>
            <p>É um documento que consolida todas as contratações que a Prefeitura Municipal pretende realizar no exercício financeiro de {{ $pca->exercicio }}.</p>
            <p>Com vistas ao planejamento dos gastos municipais que busca aperfeiçoar a governança e a gestão das contratações, possibilitando a maximização dos resultados institucionais e o uso racional dos recursos públicos, porquanto sua construção vincula as despesas previstas neste PCA com as disponibilidades orçamentárias de acordo com os limites por Ação Orçamentária, Fonte de Recurso e Subelemento de Despesa.</p>
            <p>De acordo com o Decreto Municipal, são objetivos do PCA:</p>
            <ol type="I">
                <li>Racionalizar as contratações, por meio da promoção de contratações centralizadas e compartilhadas, a fim de obter economia de escala, padronização e redução de custos processuais;</li>
                <li>Garantir o alinhamento com o planejamento estratégico, o plano diretor de logística sustentável e outros instrumentos de governança existentes;</li>
                <li>Subsidiar a elaboração das leis orçamentárias;</li>
                <li>Evitar o fracionamento de despesas;</li>
                <li>Sinalizar intenções ao mercado, potencializando o diálogo pertinente, com consequente ganho em competitividade.</li>
            </ol>
            <p>A primeira etapa da elaboração do Plano de Contratação Anual - PCA se iniciou com a apresentação a todos os setores do executivo municipal da importância de sua elaboração e implementação para a administração pública municipal, para as empresas fornecedoras de bens e serviços para a administração e para a sociedades, sob três perspectivas:</p>
            <ol type="a">
                <li>quanto ao aprendizado e crescimento da equipe municipal com o aperfeiçoamento das competências gerenciais e técnicas para as compras e contratações.</li>
                <li>sob os aspectos dos recursos públicos, aprimorando a gestão e a execução dos gastos públicos.</li>
                <li>sob a perspectiva de resultados com a otimização da disponibilidade e do desempenho dos objetos a serem adquiridos.</li>
            </ol>
            <p>A segunda etapa consistiu no levantamento das necessidades junto a cada um dos setores da Prefeitura Municipal. Cada setor ou unidade equivalente levantou suas necessidades e relacionou sua proposta de compras e contratações identificando àquelas de natureza continuada e as que serão renovadas para no exercício seguinte, alinhando seu planejamento às diretrizes definidas neste Plano de Contratação Anual - PCA.</p>
            <p>A terceira etapa consistiu na consolidação e tratamento das necessidades levantadas pelo Controle Interno e a Comissão Permanente de Licitações, o documento foi avaliado quanto à conveniência e oportunidade para tomada de decisão pela Autoridade Competente.</p>
            <p>O presente documento (Plano de Contratação Anual - PCA) foi elaborado sob a coordenação do Controle Interno, Licitações e Assessoria Jurídicas, com o apoio dos demais Setores, viabilizando a realização de licitações conjuntas, otimizando custos, agilizando procedimentos e facilitando o controle das despesas.</p>
            <p>O monitoramento do plano será realizado pelo Controle Interno, a cada 3 (três) meses, através do acompanhamento da execução do plano anual de contratações, com o objetivo de avaliar o andamento das contratações de forma a identificar tempestivamente contingências que possam comprometer o cumprimento do plano.</p>
        </div>

        <div class="bold text-sm mt-2 uppercase">OBJETIVOS</div>
        <ol>
            <li>Fortalecer a cultura de planejamento das necessidades de suprimento de materiais e serviços nas Secretarias e Órgãos da Prefeitura.</li>
            <li>Aperfeiçoar a gestão interna das compras por meio da previsibilidade das demandas com vistas à eficiência dos estoques em almoxarifados, com redução de desperdícios e com a economicidade e racionalização de gastos;</li>
            <li>Propor alternativas de atuação e modelos de aquisições e contratações corporativas, proporcionando a redução do número de processos licitatórios;</li>
            <li>Ampliar a transparência com a divulgação das estimativas de aquisição de todas as unidades compradoras no Portal da Transparência do Município;</li>
            <li>Divulgar as expectativas de compras para o mercado fornecedor, fomentando, sobretudo a participação, das micro e pequenas empresas (MPE’s) nos processos licitatórios, e, por consequência, o desenvolvimento econômico local.</li>
        </ol>
    </div>
    <div class="page-break"></div>

    <div class="bold text-sm text-center uppercase mt-2">DETALHAMENTO DO PLANO</div>

    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 5%;">ID PCA</th>
                <th style="width: 13%;">Unid. Requisitante</th>
                <th style="width: 10%;">MODALIDADE</th>
                <th style="width: 33%;">Descrição (Classe/Grupo)</th>
                <th style="width: 10%;">Valor total estimado (R$)</th>
                <th style="width: 7%;">Grau de prioridade</th>
                <th style="width: 8%;">Data p/ Início das Providências</th>
                <th style="width: 8%;">Data desejada p/ conclusão (Até)</th>
                <th style="width: 6%;">Prorrogação de contrato</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pca->itens as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}/{{ $pca->exercicio }}</td>
                    <td>{{ $item->unidade->nome ?? 'N/I' }}</td>
                    <td>{{ $item->modalidade ?? 'N/I' }}</td>
                    <td class="desc">{{ $item->descricao_classe_grupo }}</td>
                    <td class="text-right">R$ {{ number_format($item->valor_estimado, 2, ',', '.') }}</td>
                    <td>{{ ucfirst($item->grau_prioridade) }}</td>
                    <td>{{ $item->data_inicio_providencias ? \Carbon\Carbon::parse($item->data_inicio_providencias)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $item->data_desejada_conclusao ? \Carbon\Carbon::parse($item->data_desejada_conclusao)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $item->prorrogacao_contrato ? 'Sim' : 'Não' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">Nenhum item adicionado ao plano.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="bold text-sm text-center uppercase mt-4">Equipe de Elaboração</div>
    <div class="text-center mt-2">
        @if(!empty($pca->equipe_elaboracao) && is_array($pca->equipe_elaboracao))
            @foreach($pca->equipe_elaboracao as $membro)
                <div style="margin-bottom: 5rem;">
                    <span class="bold uppercase">{{ $membro['responsavel'] ?? 'N/I' }}</span><br>
                    <span style="font-size: 10px;">{{ \App\Models\Unidade::find($membro['unidade_id'])->nome ?? 'N/I' }}</span>
                </div>
            @endforeach
        @else
            <div class="text-center">Nenhum membro informado.</div>
        @endif
    </div>

</body>
</html>
