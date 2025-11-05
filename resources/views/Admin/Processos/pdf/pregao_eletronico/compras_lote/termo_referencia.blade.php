<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>TERMO DE REFERÊNCIA - Processo {{ $processo->numero_processo ?? $processo->id }}</title>
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
            padding: 4cm 2cm;
            font-size: 11pt;
            font-family: 'Aptos', sans-serif;
            /* Adiciona o timbre como background */
            background-image: url('{{ public_path($prefeitura->timbre) }}');
            background-repeat: no-repeat;
            background-position: top left;
            background-size: cover;

            text-align: justify;
            text-justify: inter-word;
            line-height: 1;
        }

        /* CLASSE PARA FORÇAR QUEBRA DE PÁGINA (ESSENCIAL PARA PDF) */
        .page-break {
            page-break-after: always;
        }

        /* ---------------------------------- */
        /* ESTILOS - CAPA DO DOCUMENTO (PÁGINA 0) */
        /* ---------------------------------- */
        #cover-page {
            /* Define a área de referência como a página inteira */
            height: 100vh;
            width: 100%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }

        .cover-image {
            /* Tamanho da imagem */
            width: 300px;
            height: 300px;
            margin-bottom: 30px;
            display: block;
            margin-left: auto;
            margin-right: auto;
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

        .footer-signature {
            margin-top: 60px;
            text-align: right;
        }

        .signature-block {
            margin-top: 60px;
            text-align: center;
        }

        /* Estilos opcionais para simular as linhas da imagem */
        .line {
            border-top: 2px solid black;
            margin: 10px 0;
            /* Espaçamento entre as linhas */
        }

        .content {
            /* Centraliza o texto como na imagem */
            margin: 40px 0;
            /* Espaçamento acima e abaixo do conteúdo principal */
        }

        strong {
            line-height: 1.5;
            /* Melhora a leitura do texto em várias linhas */
            display: block;
            /* Garante que o strong ocupe a largura total */
        }

        /* ---------------------------------- */
        /* ESTILOS - CONTEÚDO PRINCIPAL */
        /* ---------------------------------- */
        .container {
            position: relative;
            width: 100%;
            height: 100%;
        }

        .conteudo-all {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100%;
            transform: translate(-50%, -50%);
            text-align: left;
        }

        .title {
            margin-left: -85px;
            font-weight: bold;
            font-size: 20pt;
            background: #bebebe;
            border: 1px solid #7a7a7a;
            padding: 5px 50px;
            display: inline-block;
            margin-bottom: 20px;
            text-align: center;
        }

        .section {
            margin-bottom: 15px;
        }

        .justify {
            margin-top: 20px;
            text-indent: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td.icon {
            width: 80px;
            text-align: center;
            vertical-align: middle;
        }

        td.content {
            vertical-align: middle;
            padding-left: 10px;
        }

    </style>
</head>

<body>
    @php
    // Mapeamento das opções para texto legível
    $opcoes_vigencia = [
    '12_meses' => '12 meses',
    '24_meses' => '24 meses',
    '36_meses' => '36 meses',
    'exercicio_financeiro' => 'Exercício financeiro da contratação (até 31/12)',
    'outro' => 'Outro',
    ];

    // Captura o campo (pode ser string ou array)
    $vigencia = $detalhe->prazo_vigencia ?? ['12_meses'];

    // Garante que é array
    $vigencia = is_array($vigencia) ? $vigencia : [$vigencia];

    // Substitui os códigos pelos textos legíveis
    $vigencia_formatada = collect($vigencia)
    ->map(fn($item) => $opcoes_vigencia[$item] ?? ucfirst(str_replace('_', ' ', $item)))
    ->implode(', ');

    $outro_vigencia = $detalhe->prazo_vigencia_outro ?? '________________.';
    $objeto_continuado = strtolower($detalhe->objeto_continuado ?? 'nao');
    @endphp
    {{-- ====================================================================== --}}
    {{-- BLOCO 1: CAPA DO DOCUMENTO --}}
    {{-- ====================================================================== --}}
    <div id="cover-page">
        <img src="{{ public_path('icons/capa-documento.png') }}" alt="Martelo da Justiça" class="cover-image">
        <div class="cover-title">
            TERMO DE REFERÊNCIA
        </div>
    </div>
    {{-- QUEBRA DE PÁGINA --}}
    <div class="page-break"></div>
    {{-- ====================================================================== --}}
    {{-- BLOCO 2: ANEXO I TERMO DE REFERÊNCIA --}}
    {{-- ====================================================================== --}}
    <div class="container">
        <div class="conteudo-all">
            <div style="margin: 30px 0 0;">
                <div class="title">ANEXO I <br> TERMO DE REFERÊNCIA</div>
            </div>
            <div class="conteudo">
                <!-- Objeto -->
                <div class="section">
                    <table>
                        <tr>
                            <td class="icon">
                                <img src="{{ public_path('icons/grafico.png') }}" width="40">
                            </td>
                            <td class="content">
                                <div style=" font-weight: bold; margin-bottom: 3px;">OBJETO</div>
                                <div style="">{!! strip_tags($processo->objeto) !!}</div>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Alinhamento com Planejamento Anual -->
                <div class="section">
                    <table>
                        <tr>
                            <td class="icon">
                                <img src="{{ public_path('icons/dinheiro.png') }}" width="40">
                            </td>
                            <td class="content">
                                <div style=" font-weight: bold; margin-bottom: 3px;">VALOR PREVISTO</div>
                                <div>
                                    R$ {{ $detalhe->valor_estimado }}
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Equipe de Planejamento -->
                <div class="section">
                    <table>
                        <tr>
                            <td class="icon">
                                <img src="{{ public_path('icons/alerta.png') }}" width="40">
                            </td>
                            <td class="content">
                                <div style=" font-weight: bold; margin-bottom: 3px;">PRAZO DE VIGÊNCIA DA CONTRATAÇÃO</div>
                                <div style="">
                                    O prazo de vigência da contratação é de {{ $vigencia_formatada }} contados da assinatura do contrato, na forma do artigo 105 da Lei n° 14.133, de 2021
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>

            </div>
        </div>
    </div>
    {{-- QUEBRA DE PÁGINA --}}
    <div class="page-break"></div>
    <div>
        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 1. OBJETO E CONDIÇÕES DE CONTRATAÇÃO
        </p>

        <p style="text-align: justify;">
            1.1 O objeto da presente licitação consiste {!! strip_tags($processo->objeto) !!}, na modalidade Pregão Eletrônico, nos moldes do art. 28, I da Lei
            14.133/2021.
        </p>
        <p style="text-align: justify;">
            1.2 JUSTIFICATIVA PARA CONTRATAÇÃO {!! strip_tags($detalhe->justificativa) !!}
        </p>
        <p style="text-align: justify;">
            1.3. Este procedimento licitatório adotará como critério de julgamento, a forma de adjudicação por {{ $processo->tipo_contratacao->getDisplayName() }}, com base nas
            justificativas:
        </p>
        <p style="text-align: justify;">
            A licitação será dividida em grupos, formados por um ou mais itens, conforme tabela constante
            do Termo de Referência, facultando-se ao licitante a participação em quantos grupos forem de seu
            interesse, devendo oferecer proposta para todos os itens que os compõem o lote, conforme
            justificativa abaixo:
        </p>
        <p style="text-align: justify;">
            A presente justificativa tem por objetivo demonstrar a vantajosidade da contratação do objeto em
            LOTES, ao invés da aquisição ou contratação individualizada por itens, conforme os princípios e
            diretrizes estabelecidos pela Lei nº 14.133/2021, especialmente no art. 5º (princípios da eficiência
            e planejamento) e no art. 40, §1º, que dispõe:
        </p>
        <p style="text-align: justify;">
            “A administração pública poderá dividir o objeto da contratação em lotes, sempre que técnica e
            economicamente viável, visando à ampliação da competitividade e ao desenvolvimento do
            mercado local, regional ou nacional, conforme o caso.”
        </p>
        <p style="text-indent: 30px;">A contratação por lotes permite:</p>
        <ul>
            <li>
                Melhor organização e gestão contratual, ao reduzir o número de fornecedores e simplificar
                o acompanhamento das entregas ou da prestação dos serviços;
            </li>
            <li>
                Centralização de responsabilidades, evitando múltiplos prazos, locais de entrega e agentes
                executores;
            </li>
            <li>
                Facilidade logística, pois os lotes são organizados por natureza ou destinação dos itens (ex:
                lotes por tipo de material, setor usuário ou região de entrega);
            </li>
            <li>
                Adoção de cronogramas otimizados, com menos risco de atrasos por fragmentação
                excessiva de contratos.
            </li>
        </ul>
        <p style="text-indent: 30px; font-weight: bold;">VANTAGENS ECONÔMICAS</p>
        <ul>
            <li>
                Redução de custos operacionais, tanto para a Administração quanto para os fornecedores
                (ex: transporte, emissão de notas, gestão de pedidos);
            </li>
            <li>
                Estimulação da competitividade saudável, uma vez que empresas de médio porte podem
                participar de lotes especializados, e empresas menores de lotes regionais ou setoriais.
            </li>
        </ul>
        <p style="text-indent: 30px; font-weight: bold;">ANTAGENS NA FISCALIZAÇÃO E CONTROLE </p>
        <ul>
            <li>
                Facilidade de fiscalização: menos contratos a serem monitorados e maior coerência entre
                os itens de cada lote;
            </li>
            <li>
                Redução de inconsistências entre entregas: evitando divergências de padrões ou prazos
                quando múltiplas empresas atuam em paralelo em itens correlatos.
            </li>
        </ul>
        <p style="text-indent: 30px; text-align: justify;">
            A análise técnica e econômica da contratação indica que a divisão do objeto em lotes
            representa a solução mais vantajosa para a Administração Pública, ao permitir:
        </p>
        <ul>
            <li>
                Racionalização da contratação e execução;
            </li>
            <li>
                Maior eficiência administrativa e operacional;
            </li>
            <li>
                Aderência ao planejamento de compras centralizadas;
            </li>
            <li>
                Observância dos princípios da economicidade, eficiência e interesse público.
            </li>
        </ul>
        <p style="text-align: justify;">
            Assim, justifica-se plenamente a adoção da contratação por lotes, em detrimento da contratação
            por itens isolados
        </p>
        <p style="text-align: justify;">
            Por fim, a adoção deste modelo impacta diretamente no atendimento ao interesse público e na
            eficiência da contratação. A estrutura em lotes assegura que as necessidades imediatas da
            população sejam atendidas de maneira mais célere, visto que diferentes tipos de itens poderão
            estar disponíveis simultaneamente. Isso reduz o tempo de espera para o fornecimento, resultando
            em melhorias tangíveis na qualidade do fornecimento pretendido. Assim, a estratégia de licitação
            em lotes representa uma solução prática e eficiente para os desafios enfrentados pela Prefeitura,
            refletindo um compromisso com a transparência e a máxima utilidade dos recursos públicos.
        </p>

        <p style="text-align: justify;">
            1.4 Para a cotação de preços a ser realizada neste certame, esta administração coloca à disposição dos licitantes, as
            informações e preços unitários a seguir:
        </p>
        <table border="1" cellspacing="0" cellpadding="4" style="border-collapse: collapse; width: 100%; text-align: center; font-size: 8pt;">
            <thead>
                <tr>
                    <th style="width: 6%;">ITEM</th>
                    <th style="width: 30%;">ESPECIFICAÇÃO</th>
                    <th style="width: 8%;">UNIDADE</th>
                    <th style="width: 20%;">QUANTIDADE ESTIMADA</th>
                    <th style="width: 18%;">VALOR UNITÁRIO</th>
                    <th style="width: 18%;">VALOR TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @php
                $itens = is_array($detalhe->itens_especificaca_quantitativos_xml)
                ? $detalhe->itens_especificaca_quantitativos_xml
                : json_decode($detalhe->itens_especificaca_quantitativos_xml, true);
                @endphp
                @if ($itens && count($itens) > 0)
                @foreach ($itens as $item)
                <tr>
                    <td>{{ $item['item'] ?? '' }}</td>
                    <td style="text-align: left;">{{ $item['especificacoes'] ?? '' }}</td>
                    <td>{{ $item['unidade'] ?? '' }}</td>
                    <td>{{ $item['quantidade'] ?? '' }}</td>
                    <td>{{ $item['valor_unitario'] ?? '' }}</td>
                    <td>{{ $item['valor_total'] ?? '' }}</td>
                </tr>
                @endforeach
                @else
                <tr>
                    <td colspan="6">Nenhum item encontrado</td>
                </tr>
                @endif
            </tbody>
        </table>


        <p style="text-align: justify;">
            1.5 Com base nos quantitativos e especificações acima, o valor global estimado para esta Licitação será de R$ {{ $detalhe->valor_estimado }}
        </p>

        <p style="text-align: justify;">
            1.6. O prazo de vigência da contratação é de {{ $vigencia_formatada }} contados da assinatura do contrato, na forma do artigo 105 da Lei
            n° 14.133, de 2021.
        </p>
        @if($detalhe->objeto_continuado === 'sim')
            <p style="text-align: justify;">
                1.7. O fornecimento de bens é enquadrado como continuado sendo a vigência
                plurianual mais vantajosa.
            </p>
        @else
            <p style="text-align: justify;">
                1.7. O fornecimento de bens não é enquadrado como continuado sendo a vigência
                plurianual mais vantajosa.
            </p>
        @endif
        <p style="text-align: justify;">
            1.8. O contrato oferece maior detalhamento das regras que serão aplicadas em relação à vigência da contratação.
        </p>
        <p style="text-align: justify;">
            1.9. A pesquisa de preços foi realizada por meio do <span style="font-weight: bold;">Painel de Preços do Tribunal de Contas do Estado (TCE)</span>, ferramenta
            oficial que disponibiliza dados atualizados, consolidados e oriundos de contratações efetivamente realizadas pela
            Administração Pública. Tal metodologia foi adotada em observância ao disposto nos artigos 23, 40 e 41 da Lei nº 14.133/2021,
            que determinam que a estimativa de preços deve ser obtida a partir de fontes idôneas, garantindo fidedignidade e
            transparência ao processo de contratação.
        </p>
        <p style="text-align: justify;">
            1.10. O uso do Painel de Preços do TCE assegura <span style="font-weight: bold;">vantajosidade, economicidade e eficiência</span>, uma vez que contempla
            valores praticados em licitações recentes, com base em contratos já homologados e fiscalizados, permitindo aferir médias
            de mercado consistentes. Ademais, a utilização desta fonte atende às orientações do Tribunal de Contas e às boas práticas
            de planejamento da contratação, mitigando riscos de sobrepreço ou subpreço.
        </p>
        <p style="text-align: justify;">
            1.11. Dessa forma, a escolha pelo <span style="font-weight: bold;">Painel de Preços do TCE</span> como parâmetro principal de pesquisa confere <span style="font-weight: bold;">maior
                confiabilidade</span> ao levantamento de valores, reduz a possibilidade de distorções e fortalece a fundamentação técnica da
            estimativa de custos constante neste Termo de Referência.
        </p>
    </div>
    <div>
        <p style="display: flex; align-items: center; font-weight: bold;">
            <img src="{{ public_path('icons/engrenagem.png') }}" width="20" style="margin-right: 10px;">2. REQUISITOS DA CONTRATAÇÃO
        </p>

        <p style="text-align: justify;">
            2.1 Para uma contratação mais segura e eficaz, sugerimos como técnica de averiguação, e controle, as seguintes exigências
            mínimas:
        </p>
        <p style="text-align: justify;">
            2.1.1. Os produtos deverão ser executados de forma parcelada, de acordo com as solicitações da CONTRATANTE, por meio
            de suas respectivas OF.’s;
        </p>
        <p style="text-align: justify;">
            2.1.2. Os produtos deverão ser entregues em até 48 (quarenta e oito) horas contadas do envio do Pedido de
            Fornecimento/serviço Empenho, devendo a contratada manter estoques compatíveis com as quantidades solicitadas
            durante o prazo de vigência do contrato, evitando atrasos nas entregas/fornecimentos, sem a exigência de valor ou
            quantitativo mínimo e sem custos adicionais.
        </p>
        <p style="text-align: justify;">
            2.1.3. Os produtos deverão ser de boa qualidade e procedência.
        </p>
        <p style="text-align: justify;">
            2.1.4. Os produtos deverão ser executados/entregues nas respectivas Unidades e locais de indicação do CONTRATANTE, em
            horários e datas previamente estabelecidas na respectiva Ordem de Serviço;
        </p>
        <p style="text-align: justify;">
            2.1.5. A nota fiscal deverá ser apresentada no ato da entrega informado o número da ordem de fornecimento correspondente
            no campo “Dados Adicionais”.
        </p>
        <p style="text-align: justify;">
            2.1.6. A Contratada deverá arcar com as despesas de frete, deslocamento e demais despesas referentes às entregas dos
            produtos, inclusive as oriundas da devolução e reposição de mercadorias acessórias ao objeto.
        </p>
        <p style="text-align: justify;">
            2.1.7. A parte contratada sempre deverá atualizar, no período de a cada 03 (três) meses, sua sede central e sede de
            distribuição, assim como, também, sua sede administrativa, visando garantir sua existência física e melhor execução do
            contrato.
        </p>
        <p style="text-align: justify;">
            2.1.8. Serão exigidas comprovações de localização da sede da empresa, com apresentação de fotos da infraestrutura
            interna, com objetivo precípuo de averiguar a veracidade sobre a real existência da empresa, evitando a contratação de
            empresas fantasmas ou de caráter inidôneo.
        </p>
        <p style="text-align: justify;">
            2.1.9. Também serão exigidas as regulamentações e autorizações do órgão competente em relação ao objeto licitado, tais
            como autorizações e permissões em geral;
        </p>
        <p style="text-align: justify;">
            2.1.10. Serão exigidas composições de custos que reflitam a realidade econômica da empresa licitante, a ser definido no
            próprio edital, que estabelecem critérios de custos com despesas diretas e indiretas;
        </p>
        <p style="text-align: justify;">
            2.1.11. Também será exigido garantia de proposta, nos termos do art. 96 e seguintes, visando estabelecer a segurança do
            preço ofertado pelo licitante, garantindo assim, o seguro do custeio realizado pela Administração no momento da abertura
            do certame;

        </p>
        <p style="text-align: justify;">
            2.2. Não é admitida a subcontratação do objeto contratual.
        </p>
    </div>
    <div>
        <p style="display: flex; align-items: center; font-weight: bold;">
            <img src="{{ public_path('icons/check.png') }}" width="20" style="margin-right: 10px;">3. MODELO DE EXECUÇÃO DO OBJETO
        </p>

        <p style="text-align: justify;">
            3.1. O prazo de entrega dos bens é de 02 (dois) dias, contados da Ordem de Fornecimento, em remessa parcelada de acordo
            com a necessidade da Administração.
        </p>
        <p style="text-align: justify;">
            3.2. Caso não seja possível a entrega na data assinalada, a empresa deverá comunicar as razões respectivas com pelo
            menos (01) dias de antecedência para que qualquer pleito de prorrogação de prazo seja analisado, ressalvadas situações de
            caso fortuito e força maior.
        </p>
        <p style="text-align: justify;">
            3.3. Os bens deverão ser entregues na sede da Prefeitura Municipal ou em local indicado pela administração em Ordem de
            Fornecimento.

        </p>
        <p style="text-align: justify;">
            3.4. No caso de produtos perecíveis, o prazo de validade na data da entrega não poderá ser inferior a 180 (cento e oitenta)
        </p>
        <p style="text-align: justify;">
            3.5. O prazo de garantia é aquele estabelecido na Lei nº 8.078, de 11 de setembro de 1990 (Código de Defesa do Consumidor)

        </p>
    </div>
    <div>
        <p style="display: flex; align-items: center; font-weight: bold;">
            <img src="{{ public_path('icons/check.png') }}" width="20" style="margin-right: 10px;">4. MODELO DE GESTÃO DO CONTRATO
        </p>

        <p style="text-align: justify;">
            4.1. O contrato deverá ser executado fielmente pelas partes, de acordo com as cláusulas avençadas e as normas da Lei nº
            14.133, de 2021, e cada parte responderá pelas consequências de sua inexecução total ou parcial.
        </p>
        <p style="text-align: justify;">
            4.2. Em caso de impedimento, ordem de paralisação ou suspensão do contrato, o cronograma de execução será prorrogado
            automaticamente pelo tempo correspondente, anotadas tais circunstâncias mediante simples apostila.
        </p>
        <p style="text-align: justify;">
            4.3. As comunicações entre o órgão ou entidade e a contratada devem ser realizadas por escrito sempre que o ato exigir tal
            formalidade, admitindo-se o uso de mensagem eletrônica para esse fim.
        </p>
        <p style="text-align: justify;">
            4.4. O órgão ou entidade poderá convocar representante da empresa para adoção de providências que devam ser cumpridas
            de imediato.
        </p>
        <p style="text-align: justify;">
            4.5. Após a assinatura do contrato ou instrumento equivalente, o órgão ou entidade poderá convocar o representante da
            empresa contratada para reunião inicial para apresentação do plano de fiscalização, que conterá informações acerca das
            obrigações contratuais, dos mecanismos de fiscalização, das estratégias para execução do objeto, do plano complementar
            de execução da contratada, quando houver, do método de aferição dos resultados e das sanções aplicáveis, dentre outros.
        </p>
        <p style="text-align: justify;">
            4.6. A execução do contrato deverá ser acompanhada e fiscalizada pelo(s) fiscal(is) do contrato, ou pelos respectivos
            substitutos.
        </p>
        <p style="text-align: justify;">
            4.7. O fiscal administrativo do contrato verificará a manutenção das condições de habilitação da contratada, acompanhará
            o empenho, o pagamento, as garantias, as glosas e a formalização de apostilamento e termos aditivos, solicitando quaisquer
            documentos comprobatórios pertinentes, caso necessário.
        </p>
        <p style="text-align: justify;">
            4.8. Caso ocorram descumprimento das obrigações contratuais, o fiscal administrativo do contrato atuará tempestivamente
            na solução do problema, reportando ao gestor do contrato para que tome as providências cabíveis, quando ultrapassar a sua
            competência;
        </p>
        <p style="text-align: justify;">
            4.9. O gestor do contrato coordenará a atualização do processo de acompanhamento e fiscalização do contrato contendo
            todos os registros formais da execução no histórico de gerenciamento do contrato, a exemplo da ordem de serviço, do
            registro de ocorrências, das alterações e das prorrogações contratuais, elaborando relatório com vistas à verificação da
            necessidade de adequações do contrato para fins de atendimento da finalidade da administração.
        </p>
        <p style="text-align: justify;">
            4.10 O gestor do contrato acompanhará a manutenção das condições de habilitação da contratada, para fins de empenho
            de despesa e pagamento, e anotará os problemas que obstem o fluxo normal da liquidação e do pagamento da despesa no
            relatório de riscos eventuais.
        </p>
        <p style="text-align: justify;">
            4.11. O gestor do contrato acompanhará os registros realizados pelos fiscais do contrato, de todas as ocorrências
            relacionadas à execução do contrato e as medidas adotadas, informando, se for o caso, à autoridade superior àquelas que
            ultrapassarem a sua competência
        </p>
        <p style="text-align: justify;">
            4.12. O gestor do contrato emitirá documento comprobatório da avaliação realizada pelos fiscais técnico, administrativo e
            setorial quanto ao cumprimento de obrigações assumidas pelo contratado, com menção ao seu desempenho na execução
            contratual, baseado nos indicadores objetivamente definidos e aferidos, e a eventuais penalidades aplicadas, devendo
            constar do cadastro de atesto de cumprimento de obrigações.
        </p>
        <p style="text-align: justify;">
            4.13. O gestor do contrato tomará providências para a formalização de processo administrativo de responsabilização para
            fins de aplicação de sanções, a ser conduzido pela comissão de que trata o art. 158 da Lei nº 14.133, de 2021, ou pelo agente
            ou pelo setor com competência para tal, conforme o caso.
        </p>
        <p style="text-align: justify;">
            4.14. O fiscal administrativo do contrato comunicará ao gestor do contrato, em tempo hábil, o término do contrato sob sua
            responsabilidade, com vistas à tempestiva renovação ou prorrogação contratual.
        </p>
        <p style="text-align: justify;">
            4.15. O gestor do contrato deverá elaborará relatório final com informações sobre a consecução dos objetivos que tenham
            justificado a contratação e eventuais condutas a serem adotadas para o aprimoramento das atividades da Administração.
        </p>
    </div>
    <div>
        <p style="display: flex; align-items: center; font-weight: bold;">
            <img src="{{ public_path('icons/mao.png') }}" width="20" style="margin-right: 10px;">5. CRITÉRIOS DE RECEBIMENTO E DE PAGAMENTO
        </p>

        <p style="text-align: justify;">
            5.1. Os bens serão recebidos provisoriamente, de forma sumária, no ato da entrega, juntamente com a nota fiscal ou
            instrumento de cobrança equivalente, pelo(a) responsável pelo acompanhamento e fiscalização do contrato, para efeito de
            posterior verificação de sua conformidade com as especificações constantes no Termo de Referência e na proposta.
        </p>
        <p style="text-align: justify;">
            5.2. Os bens poderão ser rejeitados, no todo ou em parte, inclusive antes do recebimento provisório, quando em desacordo
            com as especificações constantes no Termo de Referência e na proposta, devendo ser substituídos no prazo de 02 (dois)
            dias, a contar da notificação da contratada, às suas custas, sem prejuízo da aplicação das penalidades.
        </p>
        <p style="text-align: justify;">
            5.3. O recebimento definitivo ocorrerá no prazo de 05 (cinco) dias úteis, a contar do recebimento da nota fiscal ou instrumento
            de cobrança equivalente pela Administração, após a verificação da qualidade e quantidade do material e consequente
            aceitação mediante termo detalhado.
        </p>
        <p style="text-align: justify;">
            5.4. O prazo para recebimento definitivo poderá ser excepcionalmente prorrogado, de forma justificada, por igual período,
            quando houver necessidade de diligências para a aferição do atendimento das exigências contratuais.

        </p>
        <p style="text-align: justify;">
            5.5. No caso de controvérsia sobre a execução do objeto, quanto à dimensão, qualidade e quantidade, deverá ser observado
            o teor do art. 143 da Lei nº 14.133, de 2021, comunicando-se à empresa para emissão de Nota Fiscal no que pertine à parcela
            incontroversa da execução do objeto, para efeito de liquidação e pagamento.
        </p>
        <p style="text-align: justify;">
            5.6. O prazo para a solução, pelo contratado, de inconsistências na execução do objeto ou de saneamento da nota fiscal ou
            de instrumento de cobrança equivalente, verificadas pela Administração durante a análise prévia à liquidação de despesa,
            não será computado para os fins do recebimento definitivo.
        </p>
        <p style="text-align: justify;">
            5.7. O recebimento provisório ou definitivo não excluirá a responsabilidade civil pela solidez e pela segurança do serviço nem
            a responsabilidade ético-profissional pela perfeita execução do contrato.
        </p>
        <p style="text-align: justify;">
            5.8. Recebida a Nota Fiscal ou documento de cobrança equivalente, correrá o prazo de trinta dias para fins de liquidação, na
            forma desta seção, prorrogáveis por igual período.
        </p>
    </div>
    <div>
        <p style="display: flex; align-items: center; font-weight: bold;">
            <img src="{{ public_path('icons/bolsa.png') }}" width="20" style="margin-right: 10px;">6. DA POSSIBILIDADE DE REAJUSTE
        </p>

        <p style="text-align: justify;">
            6.1. Os preços são fixos e irreajustáveis no prazo de um ano contado da data limite para a apresentação das propostas.
        </p>
        <p style="text-align: justify;">
            6.2. Dentro do prazo de vigência do contrato e mediante solicitação da contratada, os preços contratados poderão sofrer
            reajuste após o interregno de um ano, aplicando-se o índice IPCA/IBGE, exclusivamente para as obrigações iniciadas e
            concluídas após a ocorrência da anualidade, com base na seguinte fórmula:
        </p>
        <p style="text-align: justify;">
            R = V (I – Iº) / Iº, onde: R = Valor do reajuste procurado; V = Valor contratual a ser reajustado; Iº = índice inicial - refere-se ao
            índice de custos ou de preços correspondente à data fixada para entrega da proposta na licitação; I = Índice relativo ao mês
            do reajustamento;
        </p>
        <p style="text-align: justify;">
            6.3. Nos reajustes subsequentes ao primeiro, o interregno mínimo de um ano será contado a partir dos efeitos financeiros do
            último reajuste.
        </p>
        <p style="text-align: justify;">
            6.4. No caso de atraso ou não divulgação do índice de reajustamento, o CONTRATANTE pagará à CONTRATADA a importância
            calculada pela última variação conhecida, liquidando a diferença correspondente tão logo seja divulgado o índice definitivo.
        </p>
        <p style="text-align: justify;">
            6.5. Fica a CONTRATADA obrigada a apresentar memória de cálculo referente ao reajustamento de preços do valor
            remanescente, sempre que este ocorrer.
        </p>
        <p style="text-align: justify;">
            6.7. Caso o índice estabelecido para reajustamento venha a ser extinto ou de qualquer forma não possa mais ser utilizado,
            será adotado, em substituição, o que vier a ser determinado pela legislação então em vigor, levando em consideração a
            natureza do objeto que terá o preço reajustado.
        </p>
        <p style="text-align: justify;">
            6.8. Na ausência de previsão legal quanto ao índice substituto, as partes elegerão novo índice oficial, para reajustamento do
            preço do valor remanescente, por meio de termo aditivo.;
        </p>
    </div>
    <div>
        <p style="display: flex; align-items: center; font-weight: bold;">
            <img src="{{ public_path('icons/selo.png') }}" width="20" style="margin-right: 10px;">7. DA POSSIBILIDADE DE REAJUSTE
        </p>

        <p style="text-align: justify;">
            7.1. Para este procedimento apenas se aplicará a garantia de proposta na fase inicial dos lances.
        </p>
    </div>
    <div>
        <p style="display: flex; align-items: center; font-weight: bold;">
            <img src="{{ public_path('icons/calc.png') }}" width="20" style="margin-right: 10px;">8. ADEQUAÇÃO ORÇAMENTÁRIA
        </p>

        <p style="text-align: justify;">
            8.1. As despesas decorrentes da presente contratação correrão à conta de recursos específicos consignados no Orçamento
            Geral da Município.
        </p>
        <p style="text-align: justify;">
            8.2. A contratação será atendida pela seguinte dotação:
        </p>
        @if ($detalhe->tipo_srp == 'nao')
        <table style="border-collapse: collapse; width: 100%; border: 1px solid black;">
            <tr>
                <!-- Coluna da esquerda -->
                <td style="vertical-align: top; padding: 10px;">
                    {!! str_replace('<p>', '<p style="text-indent:30px; text-align: justify;">', $detalhe->dotacao_orcamentaria) !!}
                </td>
            </tr>
        </table>
        @else
        <p style="text-indent: 30px; text-align: justify;">
            Declaro, para os devidos fins, que a presente licitação será realizada sob a forma de <span style="font-weight: bold;">Sistema de
                Registro de Preços (SRP)</span>, nos termos do art. 82 e seguintes da Lei nº 14.133/2021.<br>
            Por se tratar de procedimento que visa apenas ao registro formal de preços, <span style="font-weight: bold;">não há necessidade de
                indicação de dotação orçamentária nesta fase</span>, ficando a alocação de recursos vinculada e
            obrigatória somente no
            momento da contratação efetiva, mediante emissão da Nota de Empenho correspondente, conforme as demandas
            das
            Secretarias/Órgãos requisitantes.<br>
            Tal medida encontra respaldo legal e visa garantir o adequado planejamento das contratações, respeitando
            os
            princípios da eficiência, economicidade e responsabilidade fiscal.
        </p>
        @endif
        <p style="text-align: justify;">
            8.3. A dotação relativa aos exercícios financeiros subsequentes será indicada após aprovação da Lei Orçamentária
            respectiva e liberação dos créditos correspondentes, mediante apostilamento.
        </p>
    </div>
    {{-- Bloco de data e assinatura --}}
    <div class="footer-signature">
            {{ $processo->prefeitura->cidade }},
            {{ \Carbon\Carbon::parse($dataSelecionada)->translatedFormat('d \d\e F \d\e Y') }}
        </div>

    @php
    // Verifica se a variável $assinantes existe e tem itens
    $hasSelectedAssinantes = isset($assinantes) && count($assinantes) > 0;
    @endphp

    @if ($hasSelectedAssinantes)
    {{-- Renderiza APENAS O PRIMEIRO assinante da lista --}}
    @php
    $primeiroAssinante = $assinantes[0]; // Pega o segundo item
    @endphp

    <div style="margin-top: 40px; text-align: center;">
        <div class="signature-block" style="display: inline-block; margin: 0 40px;">
            ___________________________________<br>
            <p style="line-height: 1.2;">
                {{ $primeiroAssinante['responsavel'] }} <br>
                <span>{{ $primeiroAssinante['unidade_nome'] }}</span>
            </p>
        </div>
    </div>
    @else
    {{-- Bloco Padrão (Fallback) --}}
    <div class="signature-block" style="margin-top: 40px; text-align: center;">
        ___________________________________<br>
        <p style="line-height: 1.2;">
            {{ $processo->prefeitura->autoridade_competente }} <br>
            <span style="color: red;">[Pregoeira/Agente de Contratação]</span>
        </p>
    </div>
    @endif

</body>

</html>
