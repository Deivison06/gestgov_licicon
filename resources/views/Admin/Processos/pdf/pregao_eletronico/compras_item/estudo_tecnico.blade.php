<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Estudo Técnico Preliminar - Processo {{ $processo->numero_processo ?? $processo->id }}</title>
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
            font-size: 11pt !important ;
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
            font-size: 18pt;
            font-weight: 900;
            border: 2px solid #000;
            display: inline-block;
            line-height: 0.9;
            padding: 10px 50px;
            font-family: 'AptosExtraBold', sans-serif;
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

        /* .conteudo {
            margin: 0 90px;
        } */

        .title {
            margin-left: -85px;
            font-weight: bold;
            font-size: 20pt;
            background: #bebebe;
            border: 1px solid #7a7a7a;
            padding: 5px 10px;
            display: inline-block;
            margin-bottom: 20px;
        }

        .section {
            margin-bottom: 15px;
        }

        .justify {
            text-align: justify;
            margin-top: 20px;
            text-indent: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td.icon {
            width: 50px;
            text-align: center;
            vertical-align: middle;
        }

        td.content {
            vertical-align: middle;
            padding-left: 10px;
        }

        /* ---------------------------------- */
        /* ESTILOS - PÁGINAS ADICIONAIS */
        /* ---------------------------------- */
        .page-content {
            margin: 0 90px;
            padding-top: 50px;
        }

        .section-title {
            font-weight: bold;

            background: #e0e0e0;
            border: 1px solid #7a7a7a;
            padding: 8px 15px;
            margin-bottom: 15px;
        }

        .content-block {
            margin-bottom: 20px;
        }

        .signature-area {
            margin-top: 100px;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #000;
            width: 300px;
            margin: 0 auto;
            padding-top: 5px;
        }

        .footer-signature {
            margin-top: 60px;
            text-align: right;
        }

        .signature-block {
            margin-top: 60px;
            text-align: center;
        }
    </style>
</head>

<body>

    {{-- ====================================================================== --}}
    {{-- BLOCO 1: CAPA DO DOCUMENTO --}}
    {{-- ====================================================================== --}}
    <div id="cover-page">
        <img src="{{ public_path('icons/capa-documento.png') }}" alt="Martelo da Justiça" class="cover-image">
        <div class="cover-title">
            INSTRUMENTOS DE PLANEJAMENTO <br> ETP E MAPA DE RISCOS
        </div>
    </div>

    {{-- QUEBRA DE PÁGINA --}}
    <div class="page-break"></div>

    {{-- ====================================================================== --}}
    {{-- BLOCO 2: PÁGINA PRINCIPAL DO ESTUDO TÉCNICO --}}
    {{-- ====================================================================== --}}
    <div class="container">
        <div class="conteudo-all">
            <div style="margin: 30px 0 0;">
                <div class="title">ESTUDO TÉCNICO PRELIMINAR – ETP</div>
            </div>
            <div class="conteudo">
                <!-- Unidade Requisitante -->
                <div class="section">
                    <table>
                        <tr>
                            <td class="icon">
                                <img src="{{ public_path('icons/Imagem1.png') }}" width="40">
                            </td>
                            <td class="content">
                                <div style=" font-weight: bold; margin-bottom: 3px;">Unidade
                                    Requisitante</div>
                                <div style="">
                                    {{ $detalhe->secretaria }}</div>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Alinhamento com Planejamento Anual -->
                <div class="section">
                    <table>
                        <tr>
                            <td class="icon">
                                <img src="{{ public_path('icons/Imagem2.png') }}" width="40">
                            </td>
                            <td class="content">
                                <div style=" font-weight: bold; margin-bottom: 3px;">Alinhamento com o Planejamento Anual</div>
                                <div>
                                    @if ($detalhe->prevista_plano_anual == 'sim')
                                        A demanda encontra-se regularmente prevista no Plano Anual de Contratações – PAC
                                    @else
                                        A demanda nao encontra-se regularmente prevista no Plano Anual de Contratações – PAC
                                    @endif
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
                                <img src="{{ public_path('icons/Imagem3.png') }}" width="40">
                            </td>
                            <td class="content">
                                <div style=" font-weight: bold; margin-bottom: 3px;">Equipe de
                                    Planejamento</div>
                                <div style="">
                                    {{ $detalhe->responsavel_equipe_planejamento }}
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Problema Resumido -->
                <div class="section">
                    <table>
                        <tr>
                            <td class="icon">
                                <img src="{{ public_path('icons/Imagem4.png') }}" width="40">
                            </td>
                            <td class="content">
                                <div style=" font-weight: bold; margin-bottom: 3px;">Problema Resumido
                                </div>
                                <div style="text-align: justify; font-size: 11pt;">{!! strip_tags($detalhe->problema_resolvido) !!}</div>
                            </td>
                        </tr>
                    </table>
                </div>

            </div>
            <!-- Texto final -->
            <p class="justify">
                Em atendimento ao inciso I do art. 18 da Lei 14.133/2021, o presente instrumento
                caracteriza a primeira etapa do planejamento do processo de contratação e busca
                atender o interesse público envolvido e buscar a melhor solução para atendimento
                da necessidade aqui descrita.
            </p>
        </div>
    </div>

    {{-- QUEBRA DE PÁGINA --}}
    <div class="page-break"></div>

    {{-- ====================================================================== --}}
    {{-- BLOCO 3: DESCRIÇÃO DA NECESSIDADE --}}
    {{-- ====================================================================== --}}
    <div id="descricao-necessidade">

        <div style="font-weight: 600; margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
                alt="DESCRIÇÃO DA NECESSIDADE">
            DESCRIÇÃO DA NECESSIDADE
        </div>

        <p style=" text-indent: 30px; text-align: justify; font-size: 11pt;">
            A {{ $prefeitura->nome }} enfrenta um problema significativo relacionado à
            {!! strip_tags($processo->objeto) !!}  {!! strip_tags($detalhe->descricao_necessidade) !!}
            expõe a fragilidade atual dos recursos disponíveis.
        </p>

        {!! str_replace('<p>', '<p style="text-indent:30px; text-align: justify; font-size: 11pt;">', $detalhe->justificativa) !!}


        <p style=" text-indent: 30px; text-align: justify;">
            Atender a essa necessidade melhorará a eficiência administrativa. Assim, a formalização
            desta demanda é crucial para assegurar que a Prefeitura possa cumprir seu papel de zelar pelo
            bem-estar da população, reforçando o compromisso com a qualidade e a efetividade dos serviços
            prestados.
        </p>

        @if ($detalhe->inversao_fase == 'sim')
            <div style="font-weight: bold;">
                Recomendação sobre a Ordem das Fases da Licitação
            </div>

            <p style="text-align: justify;">
                Nos termos do art. 17, § 1º, da Lei nº 14.133/2021, a Administração tem a prerrogativa de optar pela
                inversão das fases do processo licitatório, começando com o julgamento das propostas e, posteriormente,
                analisando a habilitação do licitante melhor classificado.<br>

                No entanto, para a presente contratação, recomenda-se <strong>manter a ordem tradicional das fases
                    (habilitação antes do julgamento das propostas)</strong>, com fundamento nos seguintes
                aspectos:<br>

                <strong>Justificativa:</strong>
            </p>

            <ol style="text-align: justify;">
                <li>
                    <strong>Complexidade da habilitação exigida:</strong> Trata-se de contratação que demanda análise
                        detalhada e criteriosa da documentação de habilitação, especialmente quanto à capacidade
                        técnica, regularidade fiscal e requisitos de qualificação econômico-financeira. A avaliação
                        prévia garante maior segurança jurídica e evita que se avance no julgamento de propostas
                        de licitantes que possam ser inabilitados posteriormente.
                </li>
                <li>
                    <strong>Mitigação de riscos:</strong> A habilitação prévia reduz o risco de retrabalho e eventual
                        anulação do procedimento, assegurando que apenas concorram na etapa de propostas os licitantes
                        que efetivamente atendam a todos os requisitos legais e técnicos.
                </li>
                <li>
                    <strong>Transparência e confiabilidade:</strong> A ordem tradicional favorece a credibilidade do
                        processo, uma vez que os participantes e órgãos de controle verificam, desde o início, que
                        apenas
                        empresas habilitadas estarão aptas a disputar.
                </li>
                <li>
                    <strong>Alinhamento com o interesse público:</strong> A opção contribui para maior lisura do
                        certame, assegurando que a Administração se dedique exclusivamente à análise das propostas de
                        licitantes plenamente habilitados, reduzindo riscos de impugnações e contestações posteriores.
                </li>
            </ol>

            <p style="text-align: justify;">
                Nos certames conduzidos por esta Administração, tem-se verificado um <strong>alto índice de
                    licitantes que participam da fase de lances/propostas sem, contudo, apresentar ou
                    comprovar adequadamente a documentação de habilitação</strong>.
            </p>

            <span style="font-weight: bold; display: block; margin-top: 10px; text-align: justify;">
                Essa prática ocasiona:
            </span>

            <ul style="text-align: justify; margin-left: 20px;">
                <li><strong>Retrabalho</strong> para a equipe de apoio e para o pregoeiro, que precisam inabilitar
                    sucessivamente os licitantes melhor classificados por falta de documentos;</li>
                <li><strong>Atrasos na conclusão do certame</strong>, em razão da necessidade de convocar repetidamente
                    os classificados subsequentes;</li>
                <li><strong>Risco de frustração da licitação</strong>, caso não haja concorrentes habilitados ao final
                    do procedimento.</li>
            </ul>

            <p style="text-align: justify;">
                A habilitação prévia garante que <strong>apenas empresas com documentação válida e condições
                    reais de contratar</strong> participem da fase competitiva, aumentando a segurança do processo e
                reduzindo a possibilidade de lances artificiais ou propostas inexequíveis apresentadas por
                licitantes que não têm intenção ou capacidade de assumir o contrato.
            </p>
        @endif


    </div>

    {{-- ====================================================================== --}}
    {{-- BLOCO 4: REQUISITOS DA CONTRATAÇÃO --}}
    {{-- ====================================================================== --}}
    <div id="requisito-necessario" style="margin-top: 20px;">

        <div style="font-weight: 600;  margin-bottom: 20px;">
            <img src="{{ public_path('icons/rquisitos-contratacao.png') }}" width="30px"
                alt="REQUISITOS DA CONTRATAÇÃO">
            REQUISITOS DA CONTRATAÇÃO
        </div>

        <div>
            <p style=" font-style: italic;">
                Para uma contratação mais segura e eficaz, sugerimos como técnica de averiguação, e controle, as
                seguintes exigências mínimas:
            </p>

            <p style="text-align: justify; text-indent: 30px;">
                Os Produtos deverão ser executados de forma parcelada, de acordo com as solicitações da CONTRATANTE, por
                meio de suas respectivas OF.’s;
            </p>

            <p style="text-align: justify; text-indent: 30px;">
                Os Produtos deverão ser entregues em até 48 (quarenta e oito) horas contadas do envio do Pedido de
                Fornecimento/serviço Empenho, devendo a contratada manter estoques compatíveis com as quantidades
                solicitadas durante o prazo de vigência do contrato, evitando atrasos nas entregas/fornecimentos, sem a
                exigência de valor ou quantitativo mínimo e sem custos adicionais.
            </p>

            <p style="text-align: justify; text-indent: 30px;">
                Os produtos deverão ser executados/entregues nas respectivas Unidades e locais de indicação do
                CONTRATANTE, em horários e datas previamente estabelecidas na respectiva Ordem de Serviço;
            </p>

            <p style="text-align: justify; text-indent: 30px;">
                A nota fiscal deverá ser apresentada no ato da entrega informado o número do Contrato correspondente no
                campo “Dados Adicionais” e a ordem de fornecimento.
            </p>

            <p style="text-align: justify; text-indent: 30px;">
                A Contratada deverá arcar com as despesas referentes a entrega dos produtos.
            </p>

            <p style="text-align: justify; text-indent: 30px;">
                Serão exigidas comprovações de localização da sede da empresa, com apresentação de fotos da
                infraestrutura interna, com objetivo precípuo de averiguar a veracidade sobre a real existência da
                empresa, evitando a
                contratação de empresas fantasmas ou de caráter inidôneo.
            </p>

            <p style="text-align: justify; text-indent: 30px;">
                Serão exigidas composições de custos que reflitam a realidade econômica da empresa licitante, a ser
                definido no próprio edital, que estabelecem critérios de custos com despesas diretas e indiretas;
            </p>

            <p style="text-align: justify; text-indent: 30px;">
                Também será exigido garantia de proposta, nos termos do art. 96 e seguintes, visando estabelecer a
                segurança do preço ofertado pelo licitante, garantindo assim, o seguro do custeio realizado pela
                Administração no
                momento da abertura do certame;
            </p>
        </div>

        {!! str_replace(
            '<p>',
            '<p style="text-indent:30px; text-align: justify; font-size: 11pt;">',
            $detalhe->incluir_requisito_cada_caso_concreto,
        ) !!}

        <p style=" text-indent: 30px; text-align: justify;">
            Os requisitos acima foram elaborados buscando a equidade no processo licitatório,
            assegurando que apenas propostas que atendam plenamente às necessidades da
            {{ $processo->prefeitura->nome }} sejam consideradas, respeitando assim os princípios da Lei
            14.133/21.
        </p>

        <div style="font-weight: 600;  margin-bottom: 20px;">
            <img src="{{ public_path('icons/solucoes-diponivel.png') }}" width="30px"
                alt="SOLUÇÕES DISPONÍVEIS NO MERCADO">
            SOLUÇÕES DISPONÍVEIS NO MERCADO
        </div>
        <p style=" text-indent: 30px; font-size: 11pt;">Soluções disponíveis para o problema de {!! strip_tags($processo->objeto) !!} da
            {{ $processo->prefeitura->nome }}: </p>

        {!! preg_replace(
            '/<p([^>]*)>/i',
            '<p$1 style="text-indent:30px; text-align: justify; font-size: 11pt;">',
            $detalhe->solucoes_disponivel_mercado,
        ) !!}


        <p style=" text-indent: 30px;">
            Cada solução apresenta características diferentes em relação a custo, eficiência e
            adaptabilidade, e a escolha deve ser fundamentada nas necessidades específicas da
            {{ $processo->prefeitura->nome }}, bem como na capacidade de cumprimento das metas estabelecidas
            para a melhoria dos serviços essenciais.
        </p>

        <div style="font-weight: 600;  margin-bottom: 20px;">
            <img src="{{ public_path('icons/carrinho.png') }}" width="30px" alt="REQUISITOS DA CONTRATAÇÃO">
            DESCRIÇÃO DA SOLUÇÃO ESCOLHIDA COMO UM TODO
        </div>

        <p style=" text-indent: 30px;">
            A escolha pela <strong>{{ $detalhe->solucao_escolhida }}</strong> é fundamentada em diversos aspectos
            técnicos e operacionais que atendem às necessidades específicas do município.
        </p>

        {!! str_replace(
            '<p>',
            '<p style="text-indent:30px; text-align: justify; font-size: 11pt;">',
            $detalhe->justificativa_solucao_escolhida,
        ) !!}

        <div style="font-weight: 600;  margin-bottom: 20px;">
            <img src="{{ public_path('icons/lista.png') }}" width="30px" alt="REQUISITOS DA CONTRATAÇÃO">
            ITENS E SEUS QUANTITATIVOS
        </div>

        <table border="1" cellspacing="0" cellpadding="4"
            style="border-collapse: collapse; width: 100%; text-align: center; font-size: 10pt;">
            <thead>
                <tr>
                    <th style="width: 8%;">ITEM</th>
                    <th style="width: 70%;">DESCRIÇÃO/ESPECIFICAÇÃO</th>
                    <th style="width: 10%;">UND</th>
                    <th style="width: 12%;">QUANT.</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $itens = json_decode($detalhe->itens_e_seus_quantitativos_xml, true);
                @endphp

                @if ($itens && count($itens) > 0)
                    @foreach ($itens as $item)
                        <tr>
                            <td>{{ $item['numero'] ?? '' }}</td>
                            <td style="text-align: left">{{ $item['descricao'] ?? '' }}</td>
                            <td>{{ $item['und'] ?? '' }}</td>
                            <td>{{ $item['quantidade'] ?? '' }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="4">Nenhum item encontrado</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <p style="">Memória de Cálculo para Justificativa dos Quantitativos </p>
        <p style="">Metodologia de Definição dos Quantitativos </p>

        <div style="">O quantitativo de itens/serviços foi definido a partir da seguinte metodologia:
        </div>
        <ul style="">
            <li>Levantamento da demanda junto às Secretarias/Unidades requisitantes; </li>
            <li>Consideração do histórico de consumo/uso dos últimos [2] anos ou exercícios;</li>
            <li>Análise do estoque atual disponível e das condições de uso dos bens já existentes
                (quando aplicável); </li>
            <li>Adequação à vigência estimada do contrato e à previsão de utilização durante esse
                período. </li>
        </ul>

        <div style="">Dados Utilizados </div>
        <ul style="">
            <li>Unidades/Setores atendidos</li>
            <li>Quantidade de usuários/beneficiários</li>
            <li>Consumo médio histórico</li>
            <li>Estoque atual disponível:</li>
            <li>Déficit identificado: </li>
        </ul>

        <p style="">O quantitativo estimado encontra-se devidamente justificado com base em dados
            oficiais, históricos de consumo, análise de estoque e previsão de demanda, atendendo ao princípio da
            eficiência e
            assegurando o interesse público, nos termos da Lei nº 14.133/2021. </p>

        <div style="font-weight: 600;  margin-bottom: 20px;">
            <img src="{{ public_path('icons/lista.png') }}" width="30px" alt="REQUISITOS DA CONTRATAÇÃO">
            PARCELAMENTO OU NÃO DA CONTRATAÇÃO
        </div>
        <p style="text-align: justify;">
            1.2. A licitação será dividida em itens, conforme tabela constante do Termo de Referência,
            facultando-se ao licitante a participação em quantos itens forem de seu interesse, conforme
            justificativa abaixo:
        </p>
        <ol type="a">
            <li style="margin-bottom: 6px; text-align: justify;">
                O fracionamento do objeto da licitação em itens encontra amparo legal no art. 40, § 1º da
                Lei nº 14.133/2021, que incentiva o parcelamento sempre que viável, desde que não
                comprometa a execução do objeto. A medida visa permitir a ampla participação de
                fornecedores, principalmente de pequeno porte, bem como alcançar melhor resultado
                para a Administração.
            </li>
            <li style="margin-bottom: 6px; text-align: justify;">
                O objeto da presente licitação abrange diversos produtos/serviços com características
                distintas, que podem ser adquiridos, entregues ou executados de forma independente, sem
                prejuízo à integridade da execução contratual.
            </li>
            <li style="margin-bottom: 6px; text-align: justify;">
                A divisão por itens não compromete a obtenção de preços vantajosos, e ao contrário,
                estimula a competitividade, ao permitir que microempresas, empresas locais e
                fornecedores especializados possam concorrer apenas nos itens de sua capacidade
                técnica e logística.
            </li>
            <li style="margin-bottom: 6px; text-align: justify;">
                Com isso, evita-se a concentração do fornecimento em um único fornecedor, promovendo
                maior eficiência, economicidade e mitigação de riscos contratuais.
            </li>
            <li style="margin-bottom: 6px; text-align: justify;">
                A adoção do parcelamento por itens está alinhada ao planejamento da Administração Pública, favorecendo:
                <ul style="margin-top: 5px; margin-bottom: 5px;">
                    <li style="margin-bottom: 5px; text-align: justify;">Atendimento adequado às necessidades específicas de cada unidade administrativa;</li>
                    <li style="margin-bottom: 5px; text-align: justify;">Diversificação de fornecedores e redução do risco de desabastecimento;</li>
                    <li style="margin-bottom: 5px; text-align: justify;">Fortalecimento da economia local/regional; </li>
                    <li style="margin-bottom: 5px; text-align: justify;">Observância ao princípio da isonomia, conforme art. 5º da Lei nº 14.133/2021.</li>
                </ul>
            </li>
            <li style="text-align: justify;">
                Além disso, o parcelamento da contratação em itens favorece uma competição saudável
                entre fornecedores, o que pode resultar em custos mais baixos e condições mais
                vantajosas para a Administração Pública. Ao permitir que empresas ofereçam suas
                propostas por ITEM, a Prefeitura pode beneficiar-se da especialização dos
                fornecedores, garantindo aquisições de melhor qualidade. Essa dinâmica
                também contribui para minimizar riscos, uma vez que cada item pode ser ajustado
                conforme a resposta do mercado e as demandas emergentes facilitando adaptações ao
                longo do fornecimento.
            </li>
        </ol>
    </div>

    {{-- ====================================================================== --}}
    {{-- BLOCO 5: RESULTADOS PRETENDIDOS --}}
    {{-- ====================================================================== --}}
    <div id="resultado-pretendidos">
        <div style="font-weight: 600;  margin-bottom: 20px;">
            <img src="{{ public_path('icons/lista.png') }}" width="30px" alt="REQUISITOS DA CONTRATAÇÃO">
            RESULTADOS PRETENDIDOS
        </div>

        {!! preg_replace(
            '/<p([^>]*)>/i',
            '<p$1 style="text-indent:30px; text-align: justify; font-size: 11pt;">',
            $detalhe->resultado_pretendidos,
        ) !!}

        <div style="font-weight: 600;  margin-bottom: 20px;">
            <img src="{{ public_path('icons/lista.png') }}" width="30px" alt="REQUISITOS DA CONTRATAÇÃO">
            CONTRATAÇÕES CORRELATAS
        </div>

        <p style=" text-indent: 30px;">A Prefeitura possui todos os seus departamentos abrigados em um mesmo
            endereço, e
            possui um único centro de compras, de modo que é possível assegurar com certeza a inexistência
            de contratações correlatas ou interdependentes que possam interferir na futura contratação.
        </p>

        <div style="font-weight: 600;  margin-bottom: 20px;">
            <img src="{{ public_path('icons/lista.png') }}" width="30px" alt="REQUISITOS DA CONTRATAÇÃO">
            IMPACTOS AMBIENTAIS
        </div>

        {!! preg_replace(
            '/<p([^>]*)>/i',
            '<p$1 style="text-indent:30px; text-align: justify; font-size: 11pt;">',
            $detalhe->impacto_ambiental,
        ) !!}

        <div style="font-weight: 600;  margin-bottom: 20px;">
            <img src="{{ public_path('icons/lista.png') }}" width="30px" alt="REQUISITOS DA CONTRATAÇÃO">
            DA VIABILIDADE DA CONTRATAÇÃO
        </div>

        <p style=" text-indent: 30px;">As análises iniciais demonstraram que a contratação da solução aqui
            referida é viável e
            tecnicamente indispensável. Portanto, com base no que foi apresentado, podemos DECLARAR
            que a contratação em questão é PLENAMENTE VIÁVEL.
        </p>

        @if ($detalhe->tipo_srp == 'sim')
            <p >Recomendamos a adoção do <strong>Registro de Preços</strong> é a solução mais
                vantajosa
                para a Administração,
                assegurando eficiência, economicidade e transparência, em conformidade com a legislação vigente
                e com as necessidades do Município.</p>
            <p >A opção pelo Sistema de Registro de Preços (SRP), previsto no art. 82 e seguintes
                da
                Lei nº
                14.133/2021, revela-se a mais adequada para o presente objeto, considerando os seguintes
                aspectos:
            </p>

            <ol style="margin-left: 30px;">
                <li >
                    <strong>Natureza da demanda: </strong>Trata-se de contratação cujo consumo é frequente e necessário
                    em diversas unidades da Administração, mas com <strong>quantidade e periodicidade incertas</strong>,
                    o que inviabiliza uma contratação de fornecimento único e imediato.
                </li>
                <li >
                    <strong>Racionalização administrativa: </strong>O SRP possibilita que a Administração registre
                    preços
                    previamente, garantindo maior <strong>agilidade e eficiência</strong> nas contratações futuras,
                    eliminando
                    a necessidade de instaurar múltiplos processos licitatórios para atender demandas de
                    mesmo objeto.
                </li>
                <li >
                    <strong>Economicidade e vantajosidade: </strong>A sistemática permite maior competitividade e
                    obtenção
                    de preços mais vantajosos, além de possibilitar adesões futuras e ganhos de escala, em
                    conformidade com os princípios da economicidade e eficiência.
                </li>
                <li >
                    <strong>Atendimento descentralizado: </strong>O SRP assegura o atendimento de diversas secretarias e
                    órgãos do Município, de forma planejada e organizada, garantindo padronização do objeto
                    e segurança na contratação.
                </li>
                <li >
                    <strong>Interesse público: </strong>A medida evita desabastecimento, permite atender prontamente
                    situações de necessidade e contribui para a boa continuidade dos serviços públicos.
                </li>
            </ol>

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
                    $primeiroAssinante = $assinantes[0]; // Pega o primeiro item
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
                        <span style="color: red;">[Cargo/Título Padrão - A ser ajustado]</span>
                    </p>
                </div>
            @endif
        @endif
    </div>

    {{-- QUEBRA DE PÁGINA --}}
    <div class="page-break"></div>

    {{-- ====================================================================== --}}
    {{-- BLOCO 6: RESULTADOS PRETENDIDOS --}}
    {{-- ====================================================================== --}}
    <div id="mapa-gerenciamento-risco">
        <p style="text-align: center; font-weight: 700;">MAPA DE GERENCIAMENTO DE RISCOS</p>
        <p style="text-indent: 30px; text-align: justify; font-size: 11pt;">O documento visa a elaboração de um MAPA DE GERANCIAMENTO DE
            RISCOS para a
            {!! strip_tags($processo->objeto) !!}, de forma a melhor atender as necessidades do município de
            {{ $processo->prefeitura->cidade }}.</p>
        <p style=" font-weight: 700; text-indent: 20px;">1- INTRODUÇÃO</p>

        <div style="text-indent: 30px; text-align: justify;">
            O gerenciamento de riscos permite ações contínuas de planejamento, organização e
            controle dos recursos relacionados aos riscos que possam comprometer o sucesso da contratação,
            da execução do objeto e da gestão contratual.
        </div>
        <div style="text-indent: 30px; text-align: justify;">
            O Mapa de Gerenciamento de Riscos deve conter a identificação e a análise dos principais
            riscos, consistindo na compreensão da natureza e determinação do nível de risco, que corresponde
            à combinação do impacto e de suas probabilidades que possam comprometer a efetividade da
            contratação/aquisição, bem como o alcance dos resultados pretendidos com o objeto. Para cada
            risco identificado, define-se: a probabilidade de ocorrência dos eventos, os possíveis danos e
            impacto caso o risco ocorra, possíveis ações preventivas e de contingência (respostas aos riscos),
            a identificação de responsáveis pelas ações, bem como o registro e o acompanhamento das ações
            de tratamento dos riscos. Os riscos identificados no projeto devem ser registrados, avaliados e
            tratados:
        </div>
        <div style="text-indent: 30px; text-align: justify;">
            Durante a fase de planejamento, a equipe de Planejamento da Contratação deve proceder
            às ações de gerenciamento de riscos e produzir o Mapa de Gerenciamento de Riscos; Durante a
            fase de Seleção do Fornecedor, o Integrante Administrativo com apoio dos Integrantes Técnico e
            Requisitante deve proceder às ações de gerenciamento dos riscos e atualizar o Mapa de
            Gerenciamento de Riscos; e, Durante a fase de Gestão do Contrato, a Equipe de Fiscalização do
            Contrato, sob coordenação do Gestor do Contrato, deverá proceder à atualização contínua do Mapa
            de Gerenciamento de Riscos, procedendo à reavaliação dos riscos identificados nas fases
            anteriores com a atualização de suas respectivas ações de tratamento, e à identificação, análise,
            avaliação e tratamento de novos riscos.
        </div>
        <div style="text-indent: 30px; text-align: justify;">
            A seguir são definidos os parâmetros escalares que representam, para o processo de
            contratação em análise, os níveis de probabilidade e impacto, que resultarão nos níveis de risco,
            após a multiplicação. Esses resultados irão nortear as ações relacionadas aos riscos durantes as
            fases de contratação (planejamento, seleção de fornecedor e gestão do contrato).
        </div>

        <table width="100%" border="1" style="border-collapse: collapse; font-size: 10pt; text-align: left;">
            <thead>
                <tr style="background-color: #f2f2f2; text-align: center; font-weight: bold;">
                    <td colspan="3">ESCALA DE PROBABILIDADE</td>
                </tr>
                <tr style="background-color: #f2f2f2; font-weight: bold; text-align: center;">
                    <td style="width: 20%;">PROBABILIDADE</td>
                    <td style="width: 10%;">PESO</td>
                    <td style="width: 70%;">DESCRIÇÃO</td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="background-color: #00B0F0; font-weight: bold;">Muito Baixa</td>
                    <td style="text-align: center;">1</td>
                    <td>Em situações excepcionais o evento poderá até ocorrer, mas não há histórico conhecido do evento
                        ou não há indícios que sinalizem sua ocorrência, portanto, é improvável que aconteça.</td>
                </tr>
                <tr>
                    <td style="background-color: #92D050; font-weight: bold;">Baixa</td>
                    <td style="text-align: center;">2</td>
                    <td>O histórico conhecido aponta para baixa frequência, podendo o evento ocorrer de forma inesperada
                        ou casual.</td>
                </tr>
                <tr>
                    <td style="background-color: #FFFF00; font-weight: bold;">Média</td>
                    <td style="text-align: center;">5</td>
                    <td>Repete-se com frequência razoável ou há indícios que possa ocorrer de alguma forma.</td>
                </tr>
                <tr>
                    <td style="background-color: #F4B083; font-weight: bold;">Alta</td>
                    <td style="text-align: center;">8</td>
                    <td>Repete-se com elevada frequência ou sua ocorrência é até esperada, pois os indícios apontam essa
                        possibilidade.</td>
                </tr>
                <tr>
                    <td style="background-color: #FF0000; color: #fff; font-weight: bold;">Muito Alta</td>
                    <td style="text-align: center;">10</td>
                    <td>Os indícios indicam claramente que o evento ocorrerá, portanto, é praticamente certo.</td>
                </tr>
            </tbody>
        </table>

        <p style="text-indent: 30px">Na tabela a seguir apresentamos a Classificação de impacto, que consiste em um
            instrumento de apoio para a definição de classificação do nível de impacto.</p>

        <table width="100%" border="1" style="border-collapse: collapse; font-size: 10pt; text-align: left;">
            <thead>
                <tr style="background-color: #f2f2f2; text-align: center; font-weight: bold;">
                    <td colspan="3">ESCALA DE IMPACTO</td>
                </tr>
                <tr style="background-color: #f2f2f2; font-weight: bold; text-align: center;">
                    <td style="width: 20%;">PROBABILIDADE</td>
                    <td style="width: 10%;">PESO</td>
                    <td style="width: 70%;">DESCRIÇÃO</td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="background-color: #00B0F0; font-weight: bold;">Muito Baixa</td>
                    <td style="text-align: center;">1</td>
                    <td>Não altera o alcance do objetivo.</td>
                </tr>
                <tr>
                    <td style="background-color: #92D050; font-weight: bold;">Baixa</td>
                    <td style="text-align: center;">2</td>
                    <td>Compromete em alguma medida o alcance do objetivo, mas não impede o alcance
                        da maior parte do atingimento do objetivo.</td>
                </tr>
                <tr>
                    <td style="background-color: #FFFF00; font-weight: bold;">Média</td>
                    <td style="text-align: center;">5</td>
                    <td>Compromete razoavelmente o alcance do objetivo, porém recuperável.</td>
                </tr>
                <tr>
                    <td style="background-color: #F4B083; font-weight: bold;">Alta</td>
                    <td style="text-align: center;">8</td>
                    <td>Compromete a maior parte do atingimento do objetivo, sendo de difícil reversão.</td>
                </tr>
                <tr>
                    <td style="background-color: #FF0000; color: #fff; font-weight: bold;">Muito Alta</td>
                    <td style="text-align: center;">10</td>
                    <td>Compromete totalmente ou quase totalmente o atingimento do objetivo, de forma
                        irreversível..</td>
                </tr>
            </tbody>
        </table>

        <p style="text-indent: 30px">Já na próxima tabela apresentamos a Matriz de Probabilidade x Impacto, que
            consiste em
            um instrumento de apoio para a definição dos critérios de classificação do nível de risco
        </p>
        <div style="margin-bottom: 20px; display: flex; justify-content: flex-end;">
            <table
                style="border-collapse: collapse; margin-bottom: 30px; border: 1px solid black; text-align: center; font-size: 10pt;">
                <thead>
                    <tr>
                        <td colspan="7"
                            style="border: 1px solid black; padding: 5px; font-weight: bold; background-color: #f0f0f0;">
                            MATRIZ DE RISCO
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td rowspan="5" style=" width: 0.5em; padding: 0;">
                            <div
                                style=" transform: rotate(-90deg); transform-origin: 0 0; position: relative; top: 10%; left: 30px; width: 100%; text-align: center; font-weight: bold; ">
                                CLIMPACTO
                            </div>
                        </td>
                        <td style="border: 1px solid black; font-weight: bold; background-color: #ff0000; width: 20%;">
                            MUITO ALTO – 10</td>
                        <td style="border: 1px solid black; background-color: #ffff00;">10<br><br>RM</td>
                        <td style="border: 1px solid black; background-color: #ffff00;">20<br><br>RM</td>
                        <td style="border: 1px solid black; background-color: #cd853f;">50<br><br>RA</td>
                        <td style="border: 1px solid black; background-color: #ff0000;">80<br><br>RE</td>
                        <td style="border: 1px solid black; background-color: #ff0000;">100<br><br>RE</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid black; font-weight: bold; background-color: #cd853f; width: 20%;">
                            ALTO – 8</td>
                        <td style="border: 1px solid black; background-color: #90ee90;">8<br><br>RB</td>
                        <td style="border: 1px solid black; background-color: #ffff00;">16<br><br>RM</td>
                        <td style="border: 1px solid black; background-color: #cd853f;">40<br><br>RA</td>
                        <td style="border: 1px solid black; background-color: #cd853f;">64<br><br>RA</td>
                        <td style="border: 1px solid black; background-color: #ff0000;">80<br><br>RE</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid black; font-weight: bold; background-color: #ffff00;width: 20%;">
                            MÉDIO – 5
                        </td>
                        <td style="border: 1px solid black; background-color: #90ee90;">5<br><br>RB</td>
                        <td style="border: 1px solid black; background-color: #ffff00;">10<br><br>RM</td>
                        <td style="border: 1px solid black; background-color: #ffff00;">25<br><br>RM</td>
                        <td style="border: 1px solid black; background-color: #cd853f;">40<br><br>RA</td>
                        <td style="border: 1px solid black; background-color: #cd853f;">50<br><br>RA</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid black; font-weight: bold; background-color: #90ee90;width: 20%;">
                            BAIXO – 2
                        </td>
                        <td style="border: 1px solid black; background-color: #90ee90;">2<br><br>RB</td>
                        <td style="border: 1px solid black; background-color: #90ee90;">4<br><br>RB</td>
                        <td style="border: 1px solid black; background-color: #ffff00;">10<br><br>RM</td>
                        <td style="border: 1px solid black; background-color: #ffff00;">16<br><br>RM</td>
                        <td style="border: 1px solid black; background-color: #ffff00;">20<br><br>RM</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid black; font-weight: bold; background-color: #00bfff;width: 20%;">
                            MUITO BAIXO
                            – 1</td>
                        <td style="border: 1px solid black; background-color: #90ee90;">1<br><br>RB</td>
                        <td style="border: 1px solid black; background-color: #90ee90;">2<br><br>RB</td>
                        <td style="border: 1px solid black; background-color: #90ee90;">5<br><br>RB</td>
                        <td style="border: 1px solid black; background-color: #90ee90;">8<br><br>RB</td>
                        <td style="border: 1px solid black; background-color: #ffff00;">10<br><br>RM</td>
                    </tr>
                </tbody>
            </table>


            <table style="border-collapse: collapse; width: auto; border: 1px solid black; font-size: 10pt;">
                <thead>

                </thead>
                <tbody>
                    <tr>
                        <td
                            style="border: 1px solid black; padding: 5px 15px; text-align: center; font-weight: bold; background-color: #4CAF50; ">
                            MUITO BAIXA - 1
                        </td>
                        <td
                            style="border: 1px solid black; padding: 5px 15px; text-align: center; font-weight: bold; background-color: #66BB6A;">
                            BAIXA - 2
                        </td>
                        <td
                            style="border: 1px solid black; padding: 5px 15px; text-align: center; font-weight: bold; background-color: #FFEB3B;">
                            MÉDIA - 5
                        </td>
                        <td
                            style="border: 1px solid black; padding: 5px 15px; text-align: center; font-weight: bold; background-color: #FF9800;">
                            ALTA - 8
                        </td>
                        <td
                            style="border: 1px solid black; padding: 5px 15px; text-align: center; font-weight: bold; background-color: #F44336; ">
                            MUITO ALTA - 10
                        </td>
                    </tr>
                </tbody>
            </table>
            <div style="text-align: center; font-weight: bold;  margin-bottom:30px;">PROBABILIDADE</div>

            <table
                style="border-collapse: collapse; width: auto; margin: 0 auto; border: 1px solid black; justify-content: right; font-size: 10pt;">
                <thead>
                    <tr>
                        <td colspan="2"
                            style="border: 1px solid black; padding: 5px; text-align: center; font-weight: bold; background-color: #f0f0f0;">
                            CLASSIFICAÇÃO DE NÍVEL DE RISCO
                        </td>
                    </tr>
                    <tr>
                        <td
                            style="border: 1px solid black; padding: 5px; text-align: center; font-weight: bold; background-color: #f0f0f0;">
                            RISCO
                        </td>
                        <td
                            style="border: 1px solid black; padding: 5px; text-align: center; font-weight: bold; background-color: #f0f0f0;">
                            ESCALA
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td
                            style="border: 1px solid black; padding: 5px; background-color: #90ee90; font-weight: bold;">
                            RB (Risco Baixo)
                        </td>
                        <td style="border: 1px solid black; padding: 5px; text-align: center;">
                            0 – 9
                        </td>
                    </tr>
                    <tr>
                        <td
                            style="border: 1px solid black; padding: 5px; background-color: #ffd700; font-weight: bold;">
                            RM (Risco Médio)
                        </td>
                        <td style="border: 1px solid black; padding: 5px; text-align: center;">
                            10 – 39
                        </td>
                    </tr>
                    <tr>
                        <td
                            style="border: 1px solid black; padding: 5px; background-color: #ff8c00; font-weight: bold;">
                            RA (Risco Alto)
                        </td>
                        <td style="border: 1px solid black; padding: 5px; text-align: center;">
                            40 – 79
                        </td>
                    </tr>
                    <tr>
                        <td
                            style="border: 1px solid black; padding: 5px; background-color: #ff0000; color: white; font-weight: bold;">
                            RE (Risco Extremo)
                        </td>
                        <td style="border: 1px solid black; padding: 5px; text-align: center;">
                            80 – 100
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p style="text-indent: 30px">Cumpre esclarecer se o produto da probabilidade versus impacto de cada risco deve
            se
            enquadrar em uma região da matriz probabilidade x impacto. Assim, caso o risco enquadre-se na
            região verde (1 a 9), seu nível de risco é entendido como baixo, logo, admite-se a aceitação ou
            adoção das medidas preventivas. Caso esteja na região amarela (10 a 39), entende-se como médio;
            se estiver na região laranja (40 a 79) entende-se como alto e se estiver na região vermelha (80 a
            100), entende-se como nível de risco muito alto (risco extremo).
        </p>

        <p style="font-weight: 700; text-indent: 20px;">2- IDENTIFICAÇÃO E ANÁLISE DOS PRINCIPAIS
            RISCOS</p>

        <table
            style="border-collapse: collapse; width: 100%; border: 2px solid black; font-size: 10pt; text-align: center;">
            <thead>
                <tr style="background-color: #f2f2f2; border: 1px solid black;">
                    <th style="border: 1px solid black; padding: 8px;">RISCO</th>
                    <th style="border: 1px solid black; padding: 8px;">DESCRIÇÃO</th>
                    <th style="border: 1px solid black; padding: 8px;">RELACIONADO AO (À)</th>
                    <th style="border: 1px solid black; padding: 8px;">PROBABILIDADE</th>
                    <th style="border: 1px solid black; padding: 8px;">IMPACTO</th>
                    <th style="border: 1px solid black; padding: 8px;">NÍVEL DE RISCO</th>
                </tr>
            </thead>
            <tbody>
                <tr style="border: 1px solid black;">
                    <td style="border: 1px solid black; padding: 8px;">01</td>
                    <td style="border: 1px solid black; padding: 8px; text-align: left;">Problemas no processo de
                        licitação</td>
                    <td style="border: 1px solid black; padding: 8px;">Planejamento da Contratação</td>
                    <td style="border: 1px solid black; padding: 8px;">1</td>
                    <td style="border: 1px solid black; padding: 8px;">8</td>
                    <td
                        style="border: 1px solid black; padding: 8px; background-color: #00cc00; color: white; font-weight: bold;">
                        8</td>
                </tr>
                <tr style="border: 1px solid black;">
                    <td style="border: 1px solid black; padding: 8px;">02</td>
                    <td style="border: 1px solid black; padding: 8px; text-align: left;">Estudos Técnicos Preliminares
                        (ETP), Mapa de Gerenciamento de Risco (MGR) e Termo de Referência (TR) deficientes e/ou
                        inconsistentes.</td>
                    <td style="border: 1px solid black; padding: 8px;">Planejamento da Contratação</td>
                    <td style="border: 1px solid black; padding: 8px;">8</td>
                    <td style="border: 1px solid black; padding: 8px;">8</td>
                    <td
                        style="border: 1px solid black; padding: 8px; background-color: #cc6600; color: white; font-weight: bold;">
                        64</td>
                </tr>
                <tr style="border: 1px solid black;">
                    <td style="border: 1px solid black; padding: 8px;">03</td>
                    <td style="border: 1px solid black; padding: 8px; text-align: left;">Falha na pesquisa de preços
                    </td>
                    <td style="border: 1px solid black; padding: 8px;">Planejamento da Contratação</td>
                    <td style="border: 1px solid black; padding: 8px;">2</td>
                    <td style="border: 1px solid black; padding: 8px;">8</td>
                    <td style="border: 1px solid black; padding: 8px; background-color: #ffff00; font-weight: bold;">16
                    </td>
                </tr>
                <tr style="border: 1px solid black;">
                    <td style="border: 1px solid black; padding: 8px;">04</td>
                    <td style="border: 1px solid black; padding: 8px; text-align: left;">Valores licitados superiores
                        aos estimados para a contratação dos serviços.</td>
                    <td style="border: 1px solid black; padding: 8px;">Seleção do Fornecedor</td>
                    <td style="border: 1px solid black; padding: 8px;">2</td>
                    <td style="border: 1px solid black; padding: 8px;">8</td>
                    <td style="border: 1px solid black; padding: 8px; background-color: #ffff00; font-weight: bold;">16
                    </td>
                </tr>
                <tr style="border: 1px solid black;">
                    <td style="border: 1px solid black; padding: 8px;">05</td>
                    <td style="border: 1px solid black; padding: 8px; text-align: left;">Baixa qualificação técnica da
                        empresa para aquisição/execução do objeto (garantia/suporte técnico).</td>
                    <td style="border: 1px solid black; padding: 8px;">Gestão Contratual</td>
                    <td style="border: 1px solid black; padding: 8px;">8</td>
                    <td style="border: 1px solid black; padding: 8px;">10</td>
                    <td
                        style="border: 1px solid black; padding: 8px; background-color: #ff0000; color: white; font-weight: bold;">
                        80</td>
                </tr>
                <tr style="border: 1px solid black;">
                    <td style="border: 1px solid black; padding: 8px;">06</td>
                    <td style="border: 1px solid black; padding: 8px; text-align: left;">Descumprimento de cláusulas
                        contratuais pela contratada</td>
                    <td style="border: 1px solid black; padding: 8px;">Gestão Contratual</td>
                    <td style="border: 1px solid black; padding: 8px;">2</td>
                    <td style="border: 1px solid black; padding: 8px;">8</td>
                    <td style="border: 1px solid black; padding: 8px; background-color: #ffff00; font-weight: bold;">16
                    </td>
                </tr>
            </tbody>
        </table>

        <p style="font-weight: 700; text-indent: 20px;">3- AVALIAÇÃO E TRATAMENTO DOS RISCOS
            IDENTIFICADOS </p>

        <p style="font-weight: 700; text-indent: 30px;">3.1- Riscos relacionados à fase de Planejamento
            da Contratação:</p>
        {{-- RISCO 01 --}}
        <table style="border-collapse: collapse; width: 100%; border: 2px solid black;  font-size: 8pt;">
            <thead>
                <tr>
                    <th colspan="3"
                        style="border: 1px solid black; padding: 8px; text-align: center; background-color: #f2f2f2; font-weight: bold; border-top: none;">
                        RISCO 01</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="3" style="border: 1px solid black; padding: 4px;"><strong>Descrição:</strong>
                        Problemas no processo de licitação para aquisição/contratação do objeto do Termo de Referência
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="border: 1px solid black; padding: 4px;"><strong>Probabilidade:</strong>
                        Muito Baixa</td>
                </tr>
                <tr>
                    <td colspan="3" style="border: 1px solid black; padding: 4px;"><strong>Impacto:</strong> Alto
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="border: 1px solid black; padding: 4px;"><strong>Dano
                            (Consequência):</strong> Atraso no processo de contratação.</td>
                </tr>
                <tr>
                    <td colspan="3" style="border: 1px solid black; padding: 4px;"><strong>Tratamento:</strong>
                        Mitigar</td>
                </tr>

                <tr>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; width: 5%;"></td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; width: 55%;"><strong>Ações de
                            Tratamento Preventivas</strong></td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; width: 40%;">
                        <strong>Responsável</strong>
                    </td>
                </tr>

                <tr>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top; text-align: center;">01</td>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top;">
                        Cumprir os prazos para contratação, revisar e acompanhar as mudanças nos documentos de
                        planejamento da contratação que influenciam no descumprimento do cronograma.
                    </td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; vertical-align: top;">Equipe
                        de Planejamento da Contratação</td>
                </tr>

                <tr>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top; text-align: center;">02</td>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top;">
                        Elaborar os documentos de planejamento da contratação com estrita observância à legislação e
                        normativos complementares.
                    </td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; vertical-align: top;">Equipe
                        de Planejamento da Contratação</td>
                </tr>

                <tr>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; width: 5%;"></td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; width: 55%;"><strong>Ações de
                            tratamento de Contingência</strong></td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; width: 40%;">
                        <strong>Responsável</strong>
                    </td>
                </tr>
                <tr>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top; text-align: center;">01</td>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top;">
                        Dedicação exclusiva da equipe de planejamento para minimizar os impactos.
                    </td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; vertical-align: top;">Equipe
                        de Planejamento da Contratação</td>
                </tr>
            </tbody>
        </table>
        <br>
        {{-- RISCO 02 --}}
        <table style="border-collapse: collapse; width: 100%; border: 2px solid black; font-size: 8pt;">
            <thead>
                <tr>
                    <th colspan="3"
                        style="border: 1px solid black; padding: 8px; text-align: center; background-color: #f2f2f2; font-weight: bold; border-top: none;">
                        RISCO 02</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="3" style="border: 1px solid black; padding: 4px;">Descrição: Estudos Técnicos
                        Preliminares (ETP), Mapa de Gerenciamento de Risco (MGR) e Termo de
                        Referência (TR) deficientes ou inconsistentes
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="border: 1px solid black; padding: 4px;">Probabilidade: Baixo</td>
                </tr>
                <tr>
                    <td colspan="3" style="border: 1px solid black; padding: 4px;">Impacto: Alto
                </tr>
                <tr>
                    <td colspan="3" style="border: 1px solid black; padding: 4px;">Dano (Consequência):
                        Especificação elaboradas com inconsistências técnicas.</td>
                </tr>
                <tr>
                    <td colspan="3" style="border: 1px solid black; padding: 4px;">Dano (Consequência): Elaboração
                        do ETP, MR e PB com ausência de itens normativamente exigidos.</td>
                </tr>
                <tr>
                    <td colspan="3" style="border: 1px solid black; padding: 4px;">Dano (Consequência): Requisitos
                        técnicos com alto risco de não serem atendidos.</td>
                </tr>
                <tr>
                    <td colspan="3" style="border: 1px solid black; padding: 4px;">Tratamento: Mitigar</td>
                </tr>

                <tr>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; width: 5%;"></td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; width: 55%;"><strong>Ações de
                            Tratamento Preventivas</strong></td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; width: 40%;">
                        <strong>Responsável</strong>
                    </td>
                </tr>

                <tr>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top; text-align: center;">01</td>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top;">
                        Convocação de servidores com conhecimento técnico adequado disponíveis à demanda para a
                        confecção dos artefatos
                    </td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; vertical-align: top;">
                        Autoridade competente
                    </td>
                </tr>

                <tr>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top; text-align: center;">02</td>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top;">
                        (Formação da equipe) Realização de cursos, seminários e palestras pertinentes ao tema.
                    </td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; vertical-align: top;">
                        Autoridade competente</td>
                </tr>
                <tr>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top; text-align: center;">03</td>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top;">
                        Revisão dos artefatos pelos servidores que
                        compõem as áreas envolvidas e, em
                        consequência, maior participação no processo
                        de contratação.
                    </td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; vertical-align: top;">Equipe
                        de Planejamento da Contratação</td>
                </tr>
                <tr>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top; text-align: center;">04</td>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top;">
                        Uso de modelos instrumentais técnicos
                        preestabelecidos pelos órgãos competentes, em
                        especial, SGD/ME.
                    </td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; vertical-align: top;">Equipe
                        de Planejamento da Contratação</td>
                </tr>

                <tr>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; width: 5%;"></td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; width: 55%;"><strong>Ações de
                            tratamento de Contingência</strong></td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; width: 40%;">
                        <strong>Responsável</strong>
                    </td>
                </tr>
                <tr>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top; text-align: center;">01</td>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top;">
                        Revisão de documentos durante o planejamento
                        da contratação.
                    </td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; vertical-align: top;">Equipe
                        de Planejamento da Contratação</td>
                </tr>
                <tr>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top; text-align: center;">01</td>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top;">
                        Suspensão do certame e revisão do processo de
                        planejamento da contratação.
                    </td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; vertical-align: top;">Equipe
                        de Planejamento da Contratação</td>
                </tr>
            </tbody>
        </table>
        <br>
        {{-- RISCO 03 --}}
        <table style="border-collapse: collapse; width: 100%; border: 2px solid black; font-size: 8pt;">
            <thead>
                <tr>
                    <th colspan="3"
                        style="border: 1px solid black; padding: 8px; text-align: center; background-color: #f2f2f2; font-weight: bold; border-top: none;">
                        RISCO 03</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="3" style="border: 1px solid black; padding: 4px;">Descrição: Falha na pesquisa de
                        preços</td>
                </tr>
                <tr>
                    <td colspan="3" style="border: 1px solid black; padding: 4px;">Probabilidade: Alto</td>
                </tr>
                <tr>
                    <td colspan="3" style="border: 1px solid black; padding: 4px;">Impacto: Alto
                </tr>
                <tr>
                    <td colspan="3" style="border: 1px solid black; padding: 4px;">Dano (Consequência): Elevação
                        dos preços ou inexequibilidade das propostas</td>
                </tr>
                <tr>
                    <td colspan="3" style="border: 1px solid black; padding: 4px;">Dano (Consequência):
                        Impossibilidade de contratação</td>
                </tr>
                <tr>
                    <td colspan="3" style="border: 1px solid black; padding: 4px;">Tratamento: Mitigar</td>
                </tr>

                <tr>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; width: 5%;"></td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; width: 55%;"><strong>Ações de
                            Tratamento Preventivas</strong></td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; width: 40%;">
                        <strong>Responsável</strong>
                    </td>
                </tr>

                <tr>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top; text-align: center;">01</td>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top;">
                        Ampliar a pesquisa de preços, não se
                        restringindo a apenas três propostas
                    </td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; vertical-align: top;">
                        Integrante Administrativo
                    </td>
                </tr>

                <tr>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top; text-align: center;">02</td>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top;">
                        Levar em consideração, quando cabível, os
                        questionamentos das empresas concorrentes
                    </td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; vertical-align: top;">
                        Equipe de Planejamento da Contratação</td>
                </tr>
                <tr>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top; text-align: center;">03</td>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top;">
                        Utilizar o Painel de Preços Públicos do Tribunal
                        de Contas do Piauí, para assim buscar por
                        preços já utilizados por outras administrações
                        regionais

                    </td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; vertical-align: top;">
                        Integrante Administrativo</td>
                </tr>

                <tr>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; width: 5%;"></td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; width: 55%;"><strong>Ações de
                            tratamento de Contingência</strong></td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; width: 40%;">
                        <strong>Responsável</strong>
                    </td>
                </tr>
                <tr>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top; text-align: center;">01</td>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top;">
                        Refazer a pesquisa de preços seguindo os
                        procedimentos de acordo com a Lei Federal
                        14.133/2021
                    </td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; vertical-align: top;">
                        Integrante Administrativo</td>
                </tr>
            </tbody>
        </table>
        <br>
        {{-- RISCO 04 --}}
        <table style="border-collapse: collapse; width: 100%; border: 2px solid black; font-size: 8pt;">
            <thead>
                <tr>
                    <th colspan="3"
                        style="border: 1px solid black; padding: 8px; text-align: center; background-color: #f2f2f2; font-weight: bold; border-top: none;">
                        RISCO 04</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="3" style="border: 1px solid black; padding: 4px;">Descrição: Aquisição/Contratação
                        do objeto do Termo e Referência a custos acima da média do mercado</td>
                </tr>
                <tr>
                    <td colspan="3" style="border: 1px solid black; padding: 4px;">Probabilidade: Médio</td>
                </tr>
                <tr>
                    <td colspan="3" style="border: 1px solid black; padding: 4px;">Impacto: Alto</td>
                </tr>
                <tr>
                    <td colspan="3" style="border: 1px solid black; padding: 4px;">Dano (Consequência): Prejuízo ao
                        erário</td>
                </tr>
                <tr>
                    <td colspan="3" style="border: 1px solid black; padding: 4px;">Tratamento: Mitigar </td>
                </tr>

                <tr>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; width: 5%;"></td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; width: 55%;"><strong>Ações de
                            Tratamento Preventivas</strong></td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; width: 40%;">
                        <strong>Responsável</strong>
                    </td>
                </tr>

                <tr>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top; text-align: center;">01</td>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top;">
                        Realizar ampla pesquisa de preço obedecendo
                        a orientação normativa específica para tal fim
                    </td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; vertical-align: top;">
                        Integrante Administrativo e Equipe de Planejamento
                        da Contratação
                    </td>
                </tr>

                <tr>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top; text-align: center;">02</td>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top;">
                        Considerar custos com encargos, tributos,
                        frete e instalação quando for o caso
                    </td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; vertical-align: top;">
                        Equipe de Planejamento da Contratação</td>
                </tr>
                <tr>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top; text-align: center;">03</td>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top;">
                        Observar os orçamentos recebidos, excluindo
                        aqueles com indícios de falhas

                    </td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; vertical-align: top;">
                        Equipe de Planejamento da Contratação </td>
                </tr>

                <tr>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; width: 5%;"></td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; width: 55%;"><strong>Ações de
                            tratamento de Contingência</strong></td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; width: 40%;">
                        <strong>Responsável</strong>
                    </td>
                </tr>
                <tr>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top; text-align: center;">01</td>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top;">
                        Revisar orçamentos recebidos
                    </td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; vertical-align: top;">
                        Equipe de Planejamento da Contratação</td>
                </tr>
                <tr>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top; text-align: center;">01</td>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top;">
                        Observar preços de outras licitações
                    </td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; vertical-align: top;">
                        Equipe de Planejamento da Contratação</td>
                </tr>
                <tr>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top; text-align: center;">01</td>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top;">
                        Não adjudicação dos bens
                    </td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; vertical-align: top;">
                        Agente de Contratação/Pregoeiro</td>
                </tr>
            </tbody>
        </table>
        <br>
        <p style="font-weight: 700; text-indent: 30px;">3.2. Riscos relacionados à fase de Seleção do
            Fornecedor:</p>
        {{-- RISCO 05 --}}
        <table style="border-collapse: collapse; width: 100%; border: 2px solid black; font-size: 8pt;">
            <thead>
                <tr>
                    <th colspan="3"
                        style="border: 1px solid black; padding: 8px; text-align: center; background-color: #f2f2f2; font-weight: bold; border-top: none;">
                        RISCO 05</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="3" style="border: 1px solid black; padding: 4px;">Descrição: Baixa qualificação
                        técnica da empresa fornecedora (garantia/suporte técnico) </td>
                </tr>
                <tr>
                    <td colspan="3" style="border: 1px solid black; padding: 4px;">Probabilidade: Baixo</td>
                </tr>
                <tr>
                    <td colspan="3" style="border: 1px solid black; padding: 4px;">Impacto: Alto</td>
                </tr>
                <tr>
                    <td colspan="3" style="border: 1px solid black; padding: 4px;">Dano (Consequência):
                        Inobservância de termos e condições estabelecidos nos documentos do Planejamento
                        da contratação
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="border: 1px solid black; padding: 4px;">Tratamento: Mitigar </td>
                </tr>

                <tr>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; width: 5%;"></td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; width: 55%;"><strong>Ações de
                            Tratamento Preventivas</strong></td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; width: 40%;">
                        <strong>Responsável</strong>
                    </td>
                </tr>

                <tr>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top; text-align: center;">01</td>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top;">
                        Capacitar servidores para que acompanhem a
                        execução do contrato/Ata
                    </td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; vertical-align: top;">
                        Equipe de Planejamento da Contratação
                    </td>
                </tr>

                <tr>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; width: 5%;"></td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; width: 55%;"><strong>Ações de
                            tratamento de Contingência</strong></td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; width: 40%;">
                        <strong>Responsável</strong>
                    </td>
                </tr>

                <tr>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top; text-align: center;">01</td>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top;">
                        Estabelecer rotinas de controle para o efetivo
                        cumprimento das obrigações estabelecidas no
                        Edital e anexos
                    </td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; vertical-align: top;">
                        Fiscal e Gestor do Contrato</td>
                </tr>
            </tbody>
        </table>
        <br>
        {{-- RISCO 06 --}}
        <table style="border-collapse: collapse; width: 100%; border: 2px solid black; font-size: 8pt;">
            <thead>
                <tr>
                    <th colspan="3"
                        style="border: 1px solid black; padding: 8px; text-align: center; background-color: #f2f2f2; font-weight: bold; border-top: none;">
                        RISCO 06</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="3" style="border: 1px solid black; padding: 4px;">Descrição: Descumprimento de
                        condições e obrigações previstas no Edital e anexos pela contratada </td>
                </tr>
                <tr>
                    <td colspan="3" style="border: 1px solid black; padding: 4px;">Probabilidade: Baixo</td>
                </tr>
                <tr>
                    <td colspan="3" style="border: 1px solid black; padding: 4px;">Impacto: Medio</td>
                </tr>
                <tr>
                    <td colspan="3" style="border: 1px solid black; padding: 4px;">Dano (Consequência):
                        Não entrega dos materiais
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="border: 1px solid black; padding: 4px;">Dano (Consequência):
                        Atraso na entrega dos materiais
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="border: 1px solid black; padding: 4px;">Dano (Consequência):
                        Baixa qualidade dos materiais entregues
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="border: 1px solid black; padding: 4px;">Tratamento: Mitigar </td>
                </tr>

                <tr>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; width: 5%;"></td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; width: 55%;"><strong>Ações de
                            Tratamento Preventivas</strong></td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; width: 40%;">
                        <strong>Responsável</strong>
                    </td>
                </tr>

                <tr>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top; text-align: center;">01</td>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top;">
                        Acompanhar a entrega dos bens aferindo se os
                        requisitos exigidos no Edital e Anexos estão
                        sendo cumpridos de acordo com a qualidade
                        exigida
                    </td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; vertical-align: top;">
                        Fiscal e Gestor do Contrato
                    </td>
                </tr>
                <tr>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top; text-align: center;">02</td>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top;">
                        Avaliar se os materiais entregues estão
                        atendendo as expectativas da contratação
                    </td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; vertical-align: top;">
                        Fiscal e Gestor do Contrato
                    </td>
                </tr>
                <tr>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top; text-align: center;">03</td>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top;">
                        Dimensionamento adequado do corpo de
                        fiscalização e gestão contratual
                    </td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; vertical-align: top;">
                        Autoridade competente
                    </td>
                </tr>

                <tr>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; width: 5%;"></td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; width: 55%;"><strong>Ações de
                            tratamento de Contingência</strong></td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; width: 40%;">
                        <strong>Responsável</strong>
                    </td>
                </tr>

                <tr>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top; text-align: center;">01</td>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top;">
                        Notificar formalmente a Contratada quando
                        cláusulas do contrato forem descumpridas
                    </td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; vertical-align: top;">
                        Fiscal e Gestor do Contrato</td>
                </tr>
                <tr>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top; text-align: center;">02</td>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top;">
                        Aplicar multas e penalidades previstas no
                        instrumento convocatório, de forma a coibir a
                        reincidência
                    </td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; vertical-align: top;">
                        Autoridade competente</td>
                </tr>
                <tr>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top; text-align: center;">03</td>
                    <td style="border: 1px solid black; padding: 4px; vertical-align: top;">
                        Instituir nova equipe de planejamento da
                        contratação e promover uma nova contratação
                        para evitar o comprometimento da continuidade
                        dos serviços da instituição, em caso de
                        dificuldade de resolução das inconformidades
                    </td>
                    <td style="border: 1px solid black; padding: 4px; text-align: center; vertical-align: top;">
                        Autoridade competente</td>
                </tr>
            </tbody>
        </table>
        <br>
        {!! str_replace('<p>', '<p style="text-indent:30px; text-align: justify; font-size: 11pt;">', $detalhe->riscos_extra) !!}
        <p style=" font-weight: 700; text-indent: 20px;">4 - APROVAÇÃO E ASSINATURA</p>
        @php
            // Verifica se a variável $assinantes existe e tem itens
            $hasSelectedAssinantes = isset($assinantes) && count($assinantes) > 0;

            // Define o primeiro assinante, se existir
            $primeiroAssinante = $hasSelectedAssinantes ? $assinantes[0] : null;

            // Extrai o nome do município removendo "Prefeitura Municipal de" ou "Prefeitura de"
            $municipio = $municipio =  $processo->prefeitura->cidade;

            // Define a data formatada em português
            $dataFormatada = \Carbon\Carbon::parse($dataSelecionada)
                ->locale('pt_BR')
                ->translatedFormat('d \d\e F \d\e Y');
        @endphp

        <p style="text-align: justify; text-indent: 30px;">
            O {{ $primeiroAssinante['responsavel'] ?? '____________________' }},
            nos termos da Portaria nº{{  $primeiroAssinante['numero_portaria'] }},
            de
            {{
                !empty($primeiroAssinante['data_portaria'])
                    ? \Carbon\Carbon::parse($primeiroAssinante['data_portaria'])->translatedFormat('d \d\e F \d\e Y')
                    : '____________________'
            }},
            apresenta o Mapa de Gerenciamento de Risco,
            certificando que somos responsáveis pela elaboração do presente documento.
        </p>

        {{-- Bloco de data e assinatura --}}
        <div class="footer-signature">
            {{ $municipio }}, {{ $dataFormatada }}
        </div>

        @if ($hasSelectedAssinantes)
            {{-- Renderiza apenas o primeiro assinante --}}
            <div style="margin-top:40px; text-align:center;">
                <div class="signature-block" style="display:inline-block; margin:0 40px;">
                    ___________________________________<br>
                    <p style="line-height:1.2; margin:0;">
                        {{ $primeiroAssinante['responsavel'] }}<br>
                        <span style="color:#4b5563;">{{ $primeiroAssinante['unidade_nome'] }}</span>
                    </p>
                </div>
            </div>
        @else
            {{-- Fallback (sem assinantes selecionados) --}}
            <div class="signature-block" style="margin-top:40px; text-align:center;">
                ___________________________________<br>
                <p style="line-height:1.2; margin:0;">
                    {{ $processo->prefeitura->autoridade_competente ?? '____________________' }}<br>
                    <span style="color:red;">[Cargo/Título Padrão - A ser ajustado]</span>
                </p>
            </div>
        @endif
    </div>
    {{-- QUEBRA DE PÁGINA --}}
    <div class="page-break"></div>

    {{-- ====================================================================== --}}
    {{-- BLOCO 7: ALINHAMENTO AO PLANO DE CONTRATAÇÃO ANUAL (PCA) --}}
    {{-- ====================================================================== --}}
    <div id="alinhamento-pca">
        <p style="text-align: center; font-weight: 700;">ALINHAMENTO AO PLANO DE CONTRATAÇÃO ANUAL (PCA) </p>
        <p style="text-align: center; font-weight: 700;">DECLARAÇÃO</p>
        @if ($detalhe->prevista_plano_anual == 'sim')
            <p>
                Declaro, para os devidos fins, que a presente demanda referente à
                <span style="font-weight: bold; font-size: 11pt;">{!! strip_tags($processo->objeto) !!}</span>
                encontra-se regularmente <span style="font-weight: bold;">prevista no Plano Anual de Contratações – PAC </span>,
                elaborado nos termos do art. 12 da Lei nº 14.133/2021 e da Instrução Normativa SEGES/ME nº 01/2019, ou
                outro normativo vigente que disciplina a matéria.
            </p>
            <p>
                A previsão no PAC assegura o adequado planejamento da contratação, alinhado às diretrizes estratégicas
                da Administração, em conformidade com os princípios da eficiência, economicidade e transparência,
                garantindo a vinculação desta demanda às metas e prioridades da gestão municipal.
            </p>
        @else
            <p>A demanda não está prevista no Plano de Contratações Anual, porém se justifica pelo(s) seguinte(s)
                motivo(s): </p>
            <div style="border: 1px solid black; padding: 10px; font-size: 8pt;">
                <p>Fundamentação Legal: conforme Artigo 12, VII, da Lei nº 14.133.</p>
                <p style="text-indent: 30px;">
                    É importante ressaltar que a ausência de um plano de contratações anual no município de
                    {{ $processo->prefeitura->cidade }}, se deve a uma
                    série de fatores que limitaram a sua implementação até o momento. Embora
                    a legislação (Artigo 12, VII, da Lei nº 14.133) estabeleça a obrigatoriedade de um plano de
                    contratações
                    anual, é necessário considerar as circunstâncias específicas que podem justificar a sua ausência
                    temporária.
                </p>
                <p style="text-indent: 30px;">
                    Um dos principais fatores que contribuíram para a falta do plano de contratações anual foi a
                    escassez de técnicos disponíveis para a elaboração do referido plano.
                </p>
                <p style="text-indent: 30px;">
                    Entretanto, é importante ressaltar que o município está tomando medidas para resolver essa
                    situação, é válido ressaltar que a elaboração de um plano de contratações anual demanda tempo e
                    esforço, pois é necessário um levantamento minucioso das necessidades da Administração Municipal,
                    bem como a análise de fornecedores e a definição de critérios claros para a contratação.
                </p>
                <p style="text-indent: 30px;">
                    A {{ $processo->prefeitura->nome }}, compromete-se a observar as disposições da Lei
                    Federal n° 14.133/21 e a empenhar todos os esforções necessários para a elaboração e atualização
                    periódica do Plano de Contratação Anual, garantindo total transparência e conformidade com as normas
                    estabelecidas.
                </p>

            </div>
        @endif
        <p
            style="text-align: center; font-weight: 700; border: 1px solid black; padding: 10px; background:#dadada; margin-top:20px;">
            ENCAMINHAMENTO PARA ÓRGÃO DEMANDANTE
        </p>
        <div style="border: 1px solid black; padding: 10px; font-size: 8pt;">
            <p>Em conformidade com a legislação aplicável, encaminhamos o Presente Estudo
                Técnico Preliminar, Mapa
                de Riscos e Alinhamento com o Plano de Contratação Anual (PCA) ao órgão solicitante para análise de
                conveniência e oportunidade para a contratação e demais providências cabíveis. </p>

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
                    $primeiroAssinante = $assinantes[0]; // Pega o primeiro item
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
                        <span style="color: red;">[Cargo/Título Padrão - A ser ajustado]</span>
                    </p>
                </div>
            @endif
        </div>

        {{-- QUEBRA DE PÁGINA --}}
        <div class="page-break"></div>

        <table
            style="border-collapse: collapse; width: 100%; text-align: left; border: 1px solid black; font-size: 10pt;">
            <thead>
                <tr>
                    <td colspan="2"
                        style="border: 1px solid black; text-align: center; font-weight: bold; padding: 5px;">
                        RESUMO DOS DADOS DO PROCESSO
                    </td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="border: 1px solid black; padding: 5px; font-weight: bold; width: 40%;">
                        Nº PROCESSO ADMINISTRATIVO:
                    </td>
                    <td style="border: 1px solid black; padding: 5px;">
                        {{ $processo->numero_processo }}
                    </td>
                </tr>
                <tr>
                    <td style="border: 1px solid black; padding: 5px; font-weight: bold;">
                        Nº PROCESSO DE CONTRATAÇÃO:
                    </td>
                    <td style="border: 1px solid black; padding: 5px;">
                        {{ $processo->numero_procedimento }}
                    </td>
                </tr>
                <tr>
                    <td style="border: 1px solid black; padding: 5px; font-weight: bold;">
                        OBJETO
                    </td>
                    <td style="border: 1px solid black; padding: 5px; font-size: 11pt;">
                        {!! strip_tags($processo->objeto) !!}
                    </td>
                </tr>
                <tr>
                    <td style="border: 1px solid black; padding: 5px; font-weight: bold;">
                        MODALIDADE:
                    </td>
                    <td style="border: 1px solid black; padding: 5px;">
                        {{ $processo->modalidade?->getDisplayName() }}
                    </td>
                </tr>
                <tr>
                    <td style="border: 1px solid black; padding: 5px; font-weight: bold;">
                        ÓRGÃO RESPONSÁVEL:
                    </td>
                    <td style="border: 1px solid black; padding: 5px;">
                        {{ $detalhe->unidade_setor }}
                    </td>
                </tr>
            </tbody>
        </table>

        <p>
            RECEBO O PRESENTE Estudo Técnico Preliminar e após verificar que demanda se encontra
            alinhada com os instrumentos de planejamento e que o objeto que não contém classificação
            direcionada à marca ou empresa e nem possui características de luxo determino:
        </p>
        <p> Encaminhe-se à {{ $detalhe->encaminhamento_pesquisa_preco }} para a REALIZAÇÃO DE PESQUISA DE PREÇOS. </p>
        <p>Encaminhe-se à {{ $detalhe->encaminhamento_doacao_orcamentaria }} para a VERIFICAÇÃO DE DOTACÃO ORÇAMENTÁRIA
            EXISTENTE.
        </p>
        <table style="border-collapse: collapse; width: auto; border: 1px solid black; font-size: 10pt;">
            <tr>
                <td style="border: 1px solid black; padding: 6px; font-weight: normal;">
                    Forma indicada da contratação:
                </td>
            </tr>
            <tr>
                <td style="border: 1px solid black; padding: 8px;">
                    <div style="display: block; margin-bottom: 4px;">
                        <span
                            style="display:inline-block; width:12px; height:12px; border:1px solid #000; margin-right:5px; vertical-align:middle; text-align:center; line-height:10px; font-size:10px; font-weight:bold;">
                            @if ($processo->modalidade === \App\Enums\ModalidadeEnum::DISPENSA)
                                X
                            @endif
                        </span>
                        Dispensa de Licitação;
                    </div>
                    <div style="display: block; margin-bottom: 4px;">
                        <span
                            style="display:inline-block; width:12px; height:12px; border:1px solid #000; margin-right:5px; vertical-align:middle; text-align:center; line-height:10px; font-size:10px; font-weight:bold;">
                            @if ($processo->modalidade === \App\Enums\ModalidadeEnum::PREGAO_ELETRONICO)
                                X
                            @endif
                        </span>
                        Pregão Eletrônico;
                    </div>
                    <div style="display: block; margin-bottom: 4px;">
                        <span
                            style="display:inline-block; width:12px; height:12px; border:1px solid #000; margin-right:5px; vertical-align:middle; text-align:center; line-height:10px; font-size:10px; font-weight:bold;">
                            @if ($processo->modalidade === \App\Enums\ModalidadeEnum::CONCORRENCIA)
                                X
                            @endif
                        </span>
                        Concorrência;
                    </div>
                </td>
            </tr>
        </table>


        <p>
            Após o cumprimento de todas as etapas acima previstas, retorno o procedimento para esta
            secretaria para elaboração de Termo de Referência.
        </p>


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
                $primeiroAssinante = $assinantes[1]; // Pega o segundo item
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
                    <span style="color: red;">[Cargo/Título Padrão - A ser ajustado]</span>
                </p>
            </div>
        @endif
    </div>

    {{-- QUEBRA DE PÁGINA
    <div class="page-break"></div> --}}

</body>

</html>
