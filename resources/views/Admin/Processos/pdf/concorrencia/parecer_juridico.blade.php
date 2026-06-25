<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>PARECER JURÍDICO - Processo {{ $processo->numero_processo ?? $processo->id }}</title>
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
            text-align: center;
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

    </style>
</head>

<body>

    {{-- ====================================================================== --}}
    {{-- BLOCO 1: CAPA DO DOCUMENTO --}}
    {{-- ====================================================================== --}}
    <div id="cover-page">
        <img src="{{ public_path('icons/capa-documento.png') }}" alt="Martelo da Justiça" class="cover-image">
        <div class="cover-title">
            PARECER JURÍDICO
        </div>
    </div>

    {{-- QUEBRA DE PÁGINA --}}
    <div class="page-break"></div>

    <p style="text-align: center; font-weight: bold;">
        PARECER JURÍDICO
    </p>
    <p style="text-align: justify; padding-top: 20px;">
        <span style="font-weight: bold">CONCORRÊNCIA: nº</span> {{ $processo->numero_procedimento }}. <br>
        <span style="font-weight: bold">ADMINISTRATIVO Nº</span> {{ $processo->numero_processo }}.<br>
        <span style="font-weight: bold">DESTINATÁRIO:</span> {{ $processo->prefeitura->nome }} <br>
        <span style="font-weight: bold">EMENTA: </span> DIREITO ADMINISTRATIVO. INTELIGÊNCIA DO ART. 28, INCISO II, DA
        LEI N.º 14.133/21.
    </p>

    <p style="font-weight: bold;">1- RELATÓRIO</p>

    <p style="text-indent: 30px;  text-align: justify;">
        Trata-se de Minuta de Edital de Licitação e Minuta de Contato Administrativo
        enviado para a esta assessoria jurídica, para análise acerca da regularidade
        jurídico-formal do Processo de Concorrência nº {{ $processo->numero_procedimento }}, cujo objeto é a
        “{!! strip_tags($processo->objeto) !!}”
    </p>
    <p style="text-indent: 30px;  text-align: justify;">
        Consta do Processo, ainda em sua fase preparatória o Projeto Básico e
        especificações técnicas, planilhas orçamentárias e modelos diversos que o
        licitante deve observar na licitação. Além disso, consta do Processo Estudo
        Técnico Preliminar, documento obrigatório no processo a partir da nova lei de
        licitações.
    </p>
    <p style="text-indent: 30px;  text-align: justify;">
        Esta Assessoria Jurídica, dessa forma, analisará se a Minuta do Edital e
        Minuta do Contrato atende os objetivos e requisitos do art. 11 e 18 da Lei
        14.133/21, bem como será apreciado nos termos do art. 53 da mencionada lei
        com critérios objetivos e em linguagem simples e compreensível e de forma clara
        e objetiva, com apreciação de todos os elementos indispensáveis à contratação e
        com exposição dos pressupostos de fato e de direito levados em consideração na
        análise jurídica.
    </p>
    <p style="text-indent: 30px;  text-align: justify;">
        É o sucinto relatório, passamos a análise jurídica que o caso requer.
    </p>

    <p style="font-weight: bold;">2- ANÁLISE JURÍDICA</p>

    <p style="text-indent: 30px;  text-align: justify;">
        Antes de se adentrar ao mérito, cumpre registrar que o exame realizado neste
        parecer se restringe aos aspectos jurídicos acerca da possibilidade ou não de se
        contratar, por Concorrência Eletrônica, obra de engenharia para
        {!! strip_tags($processo->objeto) !!}
    </p>
    <p style="text-indent: 30px;  text-align: justify;">
        No tocante a contratação pela Entidade Pública, a nossa Carta Maior
        determina que todas as aquisições de bens ou contratação de serviços com
        terceiros, serão necessariamente precedidas de licitação, de modo a identificar e
        escolher a proposta mais vantajosa para a Administração Pública, devendo
        sempre respeitar o princípio da economicidade.
    </p>
    <p style="text-indent: 30px;  text-align: justify;">
        Desta feita, a licitação tem como regra geral, a necessidade de realizar um
        processo de licitação para que a Administração Pública possa escolher seus
        fornecedores ou prestadores de serviços, colocando em condições de igualdade as
        empresas participantes do certame, conforme preleciona o art. 37, inc. XXI da
        CF/88, combinado com a Lei nº 14.133/21.
    </p>
    <p style="text-indent: 30px;  text-align: justify;">
        O art. 11 da Lei 14.133/21 estabelece que como objetivos do processo
        licitatório assegurar a seleção da proposta apta a gerar o resultado de contratação
        mais vantajoso para a Administração Pública, inclusive no que se refere ao ciclo
        de vida do objeto; assegurar tratamento isonômico entre os licitantes, bem como
        a justa competição; evitar contratações com sobrepreço ou com preços
        manifestamente inexequíveis e superfaturamento na execução dos contratos;
        incentivar a inovação e o desenvolvimento nacional sustentável.
    </p>
    <p style="text-indent: 30px;  text-align: justify;">
        O art. 18 da Lei 14.133/21 dispõe que o processo licitatório é caracterizado
        pelo planejamento, de acordo com o art. 12 inciso VII da mencionada lei, que onde
        deve ser observada a adequação orçamentária a obra a ser realizada, sendo que
        no presente caso existe previsão na Lei orçamentária para realização da obra.
    </p>
    <p style="text-indent: 30px;  text-align: justify;">
        As regras impostas nos incisos do art. 18 constam cumpridas pela minuta do
        edital e minuta do contrato, bem como seus anexos, Projeto Básico e
        especificações técnicas, planilhas orçamentárias.
    </p>
    <p style="text-indent: 30px;  text-align: justify;">
        Verifica-se ainda, de acordo com a minuta do Edital e a Minuta do Contrato
        que a modalidade de licitação escolhida pela Autoridade é a Concorrência.
    </p>
    <p style="text-indent: 30px;  text-align: justify;">
        O art. 29 da mencionada lei dispõe que a concorrência e o pregão seguem o
        rito procedimental comum a que se refere o art. 17 desta Lei, adotando-se o pregão
        sempre que o objeto possuir padrões de desempenho e qualidade que possam ser
        objetivamente definidos pelo edital, por meio de especificações usuais de mercado.
    </p>

    <p style="text-indent: 30px;  text-align: justify;">
        Aplicando-se a Concorrência aos serviços técnicos especializados, como
        dispões o Parágrafo Único do Art. 29 desta Lei, O pregão não se aplica às
        contratações de serviços técnicos especializados de natureza predominantemente
        intelectual e de obras e serviços de engenharia, exceto os serviços de engenharia
        de que trata a alínea “a” do inciso XXI do caput do art. 6º desta Lei.
    </p>
    <p style="text-indent: 30px;  text-align: justify;">
        No presente caso será usada a modalidade Concorrência, já que a obra a ser
        realizada no município de {{ $processo->prefeitura->cidade }} considera-se complexa, de acordo com as
        planilhas orçamentárias anexas ao processo e justificativa constantes dos autos, já que se trata de uma
        obra estruturante a ser realizada, levando em conta os documentos que constam do processo licitatório.
    </p>

    <p style="text-indent: 30px;  text-align: justify;">
        A Minuta do Edital e a Minuta do Contrato estabelecem todos os critérios
        técnicos dispostos na nova lei de licitações por isso não há impedimento para o
        prosseguimento da licitação.
    </p>
    <p style="text-indent: 30px;  text-align: justify;">
        No processo também consta Estudo Técnico Preliminar elaborado com base no
        art. 18 da Nova Lei de Licitações atendendo os requisitos técnicos para realização
        da obra e para realização da licitação pela modalidade concorrência pública.
    </p>

    <p style="font-weight: bold;">3 - DO PARECER</p>

    <p style="text-indent: 30px; text-align: justify;">
        Pelo exposto, esta Assessoria Jurídica OPINA pela possibilidade de realização
        da Licitação na modalidade Concorrência Eletrônica.
    </p>
    <p style="text-indent: 30px; text-align: justify;">
        Inobstante isso, o presente Parecer Jurídico é eminentemente opinativo
        cabendo ao Prefeito Municipal de {{ $processo->prefeitura->cidade }}, usando seu juízo de
        discricionariedade, o poder de decisão sobre a melhor forma de condução do
        processo licitatório.
    </p>
    <p style="text-indent: 30px;">
        É este o parecer. Salvo Melhor Juízo.
    </p>

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
            <span style="color: red;">[Cargo/Título Padrão - A ser ajustado]</span>
        </p>
    </div>
    @endif
    <p style="text-indent: 30px; text-align: justify;">
        Encaminhe-se ao Exmo. Sr. {{ $processo->prefeitura->autoridade_competente }}, Prefeito Municipal para a
        análise e possível AUTORIZAÇÃO DE ABERTURA DE PROCESSO.
    </p>

</body>

</html>
