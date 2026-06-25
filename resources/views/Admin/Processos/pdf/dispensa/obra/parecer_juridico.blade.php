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

    @if ($parecer === 'parecer_1')
    <p style="text-align: center; font-weight: bold;">
        PARECER JURÍDICO
    </p>

    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <!-- Coluna vazia ocupa 70% -->
            <td style="width: 50%;"></td>

            <!-- Coluna do texto ocupa 30% (lado direito) -->
            <td style="width: 50%; text-align: justify; vertical-align: top; padding-top: 20px;">
                EMENTA: DIREITO ADMINISTRATIVO.
                PARECER INICIAL. PROCESSO
                ADMINISTRATIVO Nº {{ $processo->numero_processo }}.
                PREGÃO ELETRÔNICO Nº {{ $processo->numero_procedimento }}.
                PREFEITURA MUNICIPAL.
                OBSERVÂNCIA DA LEI 14.133/2021.
                OPINATIVO PELA APROVAÇÃO DA
                FASE INTERNA.
            </td>
        </tr>
    </table>

    <p style="font-weight: bold;">RELATÓRIO</p>

    <p style="text-indent: 30px;  text-align: justify;">
        Submeteu-se ao crivo dessa assessoria a análise dos aspectos jurídicos relativos à abertura do PROCESSO
        ADMINISTRATIVO N° {{ $processo->numero_processo }}, PREGÃO ELETRÔNICO N°
        {{ $processo->numero_procedimento }} cujo objeto é a:
        “{!! strip_tags($processo->objeto) !!}”
    </p>
    <p style="text-indent: 30px;  text-align: justify;">
        Seguindo a liturgia de praxe, os autos foram submetidos à análise desta assessoria jurídica,
        a fim de que fosse verificada a legalidade dos atos da fase interna do procedimento licitatório.
    </p>
    <p style="text-indent: 30px;  text-align: justify;">
        É, em abrupta síntese, o que importa relatar.
    </p>
    <p style="text-indent: 30px;  text-align: justify;">
        Passo a fundamentar, para, ao final, opinar.
    </p>

    <p style="font-weight: bold;">FUNDAMENTAÇÃO</p>

    <p style="text-indent: 30px;  text-align: justify;">
        Registre-se, de pórtico, que o presente parecer tem por objeto analisar a fase preparatória
        do procedimento licitatório, visando verificar os aspectos jurídicos da minuta elaborada, em
        conformidade com o que preceitua o art. 53 da Lei 14.133/21.
    </p>
    <p style="text-indent: 30px;  text-align: justify;">
        Ademais, cumpre salientar que essa Assessoria emite parecer sob o prisma estritamente
        jurídico, não lhe competindo adentrar na conveniência/oportunidade dos atos praticados no âmbito
        da Administração Pública, nem analisar aspectos de natureza eminentemente administrativas, além
        disso, este parecer é de caráter meramente opinativo, não vinculando, portanto, à decisão do gestor
        municipal.
    </p>
    <p style="text-indent: 30px;  text-align: justify;">
        A obrigatoriedade de licitar consta na Constituição Federal de 1988, em seu artigo 37,
        inciso XXI. O procedimento licitatório visa garantir não apenas a seleção da proposta mais
        vantajosa para a Administração, mas sim, visa assegurar o princípio constitucional da isonomia
        entre os potenciais prestadores do serviço, ou fornecedores do objeto pretendido.
    </p>
    <p style="text-indent: 30px;  text-align: justify;">
        Em face do regramento constitucional, em 2021, foi editada a Lei Nacional nº 14.133/2021,
        que instituiu normas gerais para licitações e contratos da Administração Pública, em substituição a
        antiga Lei nº 8.666/93. De acordo com o art. 17 da nova legislação de regência da matéria, o
        processo de licitação observará as seguintes fases, em sequência: (I) preparatória; (II) de divulgação
        do edital de licitação; (III) de apresentação de propostas e lances, quando for o caso; (IV) de
        julgamento; (IV) de habilitação; (VI) recursal; (VII) de homologação.
    </p>
    <p style="text-indent: 30px;  text-align: justify;">
        No caso dos autos, em razão do andamento dos atos praticados até o presente momento,
        somente é possível realizar uma análise dos elementos registrados na fase inicial do procedimento
        licitatório. Por consequência, torna se fundamental atentar para o teor do art. 18 da Lei nº
        14.133/2021, que inaugura o capítulo referente à fase preparatória da licitação, in verbis:
    </p>

    <table style="width: 100%; border-collapse: collapse; page-break-inside: auto;">
        <tr style="page-break-after: auto;">
            <!-- Coluna vazia ocupa 50% -->
            <td style="width: 50%;"></td>

            <!-- Coluna do texto ocupa 50% -->
            <td style="width: 50%; text-align: justify; vertical-align: top; padding-top: 20px; line-height: 1.5;">
                Art. 18. A fase preparatória do processo licitatório é caracterizada pelo planejamento e deve
                compatibilizar-se com o plano de contratações anual de que trata o inciso VII do caput do art.
                12
                desta Lei, sempre que elaborado, e com as leis orçamentárias, bem como abordar todas as
                considerações técnicas, mercadológicas e de gestão que podem interferir na contratação,
                compreendidos: <br><br>
            </td>
        </tr>

        <tr>
            <td></td>
            <td style="text-align: justify;">I - a descrição da necessidade da contratação fundamentada em estudo técnico preliminar que
                caracterize o interesse público envolvido;<br><br></td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">II - a definição do objeto para o atendimento da necessidade, por meio de termo de referência,
                anteprojeto, projeto básico executivo, conforme o caso; ou projeto<br><br></td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">III - a definição das condições de execução e pagamento, das garantias exigidas e ofertadas e
                das
                condições de recebimento;<br><br></td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">IV - o orçamento estimado, com as composições dos preços utilizados para sua formação;<br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">V - a elaboração do edital de licitação;<br><br></td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">VI - a elaboração de minuta de contrato, quando necessária, que constará obrigatoriamente como
                anexo
                do edital de licitação;<br><br></td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">VII - o regime de fornecimento de bens, de prestação de serviços ou de execução de obras e
                serviços
                de engenharia, observados os potenciais de economia de escala;<br><br></td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">VIII - a modalidade de licitação, o critério de julgamento, o modo de disputa e a adequação e
                eficiência da forma de combinação desses parâmetros, para os fins de seleção da proposta apta a
                gerar o resultado de contratação mais vantajoso para a Administração Pública, considerado todo o
                ciclo de vida do objeto;<br><br></td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">IX - a motivação circunstanciada das condições do edital, tais como justificativa de exigências
                de
                qualificação técnica, mediante indicação das parcelas de maior relevância técnica ou valor
                significativo do objeto, e de qualificação econômico-financeira, justificativa dos critérios de
                pontuação e julgamento das propostas técnicas, nas licitações com julgamento por melhor técnica
                ou
                técnica e preço, e justificativa das regras pertinentes à participação de empresas em consórcio;
                <br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">X - a análise dos riscos que possam comprometer o sucesso da licitação e a boa execução
                contratual;
                <br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">XI - a motivação sobre o momento da divulgação do orçamento da licitação, observado o art. 24
                desta
                Lei.<br><br></td>
        </tr>
    </table>
    <p style="text-indent: 30px;  text-align: justify;">
        Compulsando os documentos que instruem os autos do processo de contratação, constata
        se o atendimento ao disposto no dispositivo transcrito alhures, haja vista que estão presentes, dentre
        outros, o Estudo Técnico Preliminar com a descrição da necessidade e estimativa e Termo de
        Referência com a definição do objeto, a fundamentação da contratação, os requisitos da
        contratação, o modelo de execução do objeto, o modelo de gestão do contrato, os critérios de
        medição e pagamento, a forma e critérios de seleção do fornecedor e do fornecimento, da proposta
        e estimativa do valor da contratação, da descrição detalhada dos itens, do contrato e vigência, da
        garantia dos produtos, das responsabilidades do contratante e da contratada.
    </p>
    <p style="text-indent: 30px;  text-align: justify;">
        Ademais, verifica-se a minuta de edital, conta com dois anexos (Termo de Referência e
        Minuta de Contrato), e contempla as exigências de participação, os critérios de apresentação da
        proposta inicial, preenchimento e classificação das propostas, da fase de julgamento, da fase de
        habilitação, dos recursos, das infrações administrativas e sanções, da impugnação ao edital e do
        pedido de esclarecimento, da ata de registro de preços, da formação do cadastro de reserva, dos
        recursos, das infrações administrativas e sanções, da impugnação ao edital e do pedido de
        esclarecimento, em conformidade com o art. 25 da lei disciplinadora do tema.
    </p>
    <p style="text-indent: 30px;  text-align: justify;">
        Ainda quanto ao art. 18 da Lei nº 14.133/2021, cumpre consignar que resta prejudicada a
        análise de compatibilidade da licitação com o plano anual de contratação, uma vez que ainda não
        existe tal plano no âmbito do município. No entanto, a sua ausência não impede o prosseguimento
        do certame, porquanto não é um item obrigatório, mas facultativo, nos termos do art. 12, VII, da
        nova Lei de Licitações:
    </p>

    <table style="width: 100%; border-collapse: collapse; page-break-inside: auto;">
        <tr style="page-break-after: auto;">
            <!-- Coluna vazia ocupa 50% -->
            <td style="width: 50%;"></td>

            <!-- Coluna do texto ocupa 50% -->
            <td style="width: 50%; text-align: justify; vertical-align: top;">
                Art. 12. No processo licitatório, observar-se-á
                o seguinte: <br><br>
            </td>
        </tr>

        <tr>
            <td></td>
            <td style="text-align: justify;">VII - a partir de documentos de formalização
                de demandas, <span style="font-weight: bold;">os órgãos responsáveis pelo
                    planejamento de cada ente federativo
                    poderão, na forma de regulamento, elaborar
                    plano de contratações anual</span>, com o objetivo
                de racionalizar as contratações dos órgãos e
                entidades sob sua competência, garantir o
                alinhamento com o seu planejamento
                estratégico e subsidiar a elaboração das
                respectivas leis orçamentárias. (grifos nossos) <br><br></td>
        </tr>
    </table>

    <p style="text-indent: 30px;  text-align: justify;">
        Em relação à modalidade de licitação, entende-se ser correta a escolha do Pregão
        Eletrônico, tendo em vista ser a modalidade obrigatória para aquisição de bens e serviços comuns,
        conforme previsto no art. 6º, XLI, da Lei nº 14.133/2021. Além disso, é a mais vantajosa para a
        Administração Pública em razão da ampla competitividade de preços dela decorrentes.
    </p>
    <p style="text-indent: 30px;  text-align: justify;">
        Outros sim, é acertado o critério de julgamento por menor preço por {{ $processo->tipo_contratacao->getDisplayName() }}, pois se coaduna o objeto do
        presente certame o {!! strip_tags($processo->objeto) !!}, bem como atende ao disposto no mesmo
        art. 6º, XLI, da Lei nº 14.133/2021:
    </p>

    <table style="width: 100%; border-collapse: collapse; page-break-inside: auto;">
        <tr style="page-break-after: auto;">
            <!-- Coluna vazia ocupa 50% -->
            <td style="width: 50%;"></td>

            <!-- Coluna do texto ocupa 50% -->
            <td style="width: 50%; text-align: justify; vertical-align: top;">
                Art. 6º Para os fins desta Lei, consideram-se: <br><br>
            </td>
        </tr>

        <tr>
            <td></td>
            <td style="text-align: justify;">XLI - pregão: modalidade de licitação
                obrigatória para aquisição de bens e serviços
                comuns, cujo critério de julgamento poderá ser
                o de menor preço ou o de maior desconto; <br><br></td>
        </tr>
    </table>

    <p style="text-indent: 30px;  text-align: justify;">
        No tocante ao dispêndio econômico que se depreende da contratação, esta assessoria
        jurídica destaca que não detém expertise para examinar e aquilatar a correspondência dos valores
        estimados no certame frente ao usualmente praticado pelo mercado. Nada obstante, percebe-se que
        há no processo pesquisa realizada no Painel de Preços do TCE-PI.
    </p>

    <p style="font-weight: bold;">CONCLUSÃO</p>

    <p style="text-indent: 30px;  text-align: justify;">
        Ante o exposto, estando configurada a regularidade do procedimento adotado, com esteio
        na legislação vigente, <span style="font-weight: bold;">OPINO PELA APROVAÇÃO DA FASE INTERNA</span>, a fim de que seja
        autorizado o início da fase externa do referido certame.
    </p>
    <p style="text-indent: 30px; ">
        É, S,M.J., o Parecer, que submeto à análise superior.
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
    @elseif ($parecer === 'parecer_2')
    <p style="text-align: center; font-weight: bold;">
        PARECER JURÍDICO
    </p>

    <p style="font-weight: bold;">PREGÃO ELETRÔNICO Nº {{ $processo->numero_procedimento }}
        <br>
        PROCESSO ADMINISTRATIVO Nº {{ $processo->numero_processo }} <br>
        OBJETO:{!! strip_tags($processo->objeto) !!}
    </p>

    <p style="text-indent: 30px;  text-align: justify;">
        Pelo presente, emitimos nossa opinião jurídica junto a Prefeitura Municipal
        acerca da regularidade da fase interna do certame licitatório acima mencionado,
        nos termos da Lei nº 14.133/2021.
    </p>
    <p style="font-weight: bold; font-size: 14px;">1 – DO RELATÓRIO </p>

    <p style="text-indent: 30px;  text-align: justify;">
        Foram encaminhados a esta assessoria jurídica os autos do processo nº {{ $processo->numero_processo }},
        para que seja feita a análise quanto as formalidades legais do procedimento que se encontra em sua fase interna.
        Constituídas dos seguintes documentos:

        Termo de Referência com descrição e quantitativo dos itens;
        Adequação e existência de saldo orçamentário;
        Minuta do Edital de Licitação e minuta do Contrato;
    </p>
    <ul>
        <li>Documento de Formalização de Demanda (DFD);</li>
        <li>Estudos Técnicos Preliminares (ETP);</li>
        <li>Mapa de Riscos;</li>
        Pesquisa de Preços realizada no Painel de Preços do TCE-PI:
        <li>Termo de Referência com descrição e quantitativo dos itens;</li>
        <li>Adequação e existência de saldo orçamentário;</li>
        <li>Minuta do Edital de Licitação e minuta do Contrato;</li>
    </ul>
    <p style="text-indent: 30px;  text-align: justify;">
        Este é o relatório dos principais documentos constantes nos autos, pelo que se prossegue a
        análise quanto aos requisitos formais legais necessários do ato.
    </p>

    <p style="font-weight: bold; font-size: 14px;">2 - DA FINALIDADE E ABRANGÊNCIA DO PARECER JURÍDICO.</p>

    <p style="text-indent: 30px;  text-align: justify;">
        Antes de adentrar na fundamentação relacionada ao objeto em tela, é importante destacar
        que a corrente exposição jurídica objetiva prestar a devida assistência à autoridade solicitante na
        análise proemial da legalidade, apartando pontos de caráter técnico, econômico e/ou discricionário,
        avaliação fora da competência dessa assessoria jurídica.
        Nestes termos, o Art. 53, §1º, incisos I e II, da Lei 14.133/2021, norteia a análise jurídica
        da seguinte forma:
    </p>
    <p style="text-indent: 30px;  text-align: justify;">
        Nestes termos, o Art. 53, §1º, incisos I e II, da Lei 14.133/2021, norteia a análise jurídica
        da seguinte forma:
    </p>

    <table style="width: 100%; border-collapse: collapse; page-break-inside: auto;">
        <tr style="page-break-after: auto;">
            <!-- Coluna vazia ocupa 50% -->
            <td style="width: 50%;"></td>

            <!-- Coluna do texto ocupa 50% -->
            <td style="width: 50%; text-align: justify; vertical-align: top;">
                Art. 53. Ao final da fase preparatória, o
                processo licitatório seguirá para o órgão de
                assessoramento jurídico da Administração, que
                realizará ontrole prévio de legalidade
                mediante análise jurídica da contratação. <br><br>
            </td>
        </tr>

        <tr>
            <td></td>
            <td style="text-align: justify;">
                § 1º Na elaboração do parecer jurídico, o órgão
                de assessoramento jurídico da Administração
                deverá:
                <br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">
                I - apreciar o processo licitatório conforme
                critérios objetivos prévios de atribuição de
                prioridade;
                <br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">
                II - redigir sua manifestação em linguagem
                simples e compreensível e de forma clara e
                objetiva, com apreciação de todos os elementos
                indispensáveis à contratação e com exposição
                dos pressupostos de fato e de direito levados
                em consideração na análise jurídica;
                <br><br>
            </td>
        </tr>
    </table>

    <p style="text-indent: 30px;  text-align: justify;">
        Como se pode observar do dispositivo legal acima, o controle prévio de legalidade ocorre
        em razão do desempenho da competência da análise jurídica de vindoura contratação, não
        compreendendo os aspectos de natureza técnica, mercadológica, financeira ou de conveniência e
        oportunidade.
    </p>
    <p style="text-indent: 30px;  text-align: justify;">
        Ademais, cabe esclarecer que determinadas observações são feitas sem caráter vinculativo,
        mas em benefício da salvaguarda da autoridade assessorada a quem compete, dentro da margem de
        discricionariedade que lhe é conferida pela lei, avaliar e acatar, ou não, tais considerações.
        No mais, as matérias pertinentes à legalidade serão registradas para a devida revisão. Do
        contrário, o prosseguimento do processo apartado dos reparos necessários será de responsabilidade
        exclusiva da Administração.
    </p>
    <p style="text-indent: 30px;  text-align: justify;">
        À guisa de arremate do tema, o aclaramento acima mostra-se necessário para demonstrar o
        caráter opinativo e não vinculante do parecer jurídico, cabendo ao gestor a decisão final dos atos
        administrativos.
    </p>

    <p style="font-weight: bold;">3 – DA ANÁLISE JURÍDICA</p>
    <p style="font-weight: bold;">3.1 – DOS PRINCÍPIOS DA ADMINISTRAÇÃO PÚBLICA</p>

    <p style="text-indent: 30px;  text-align: justify;">
        A gestão pública é pautada por alguns princípios da Administração, julgados fundamentais
        para garantir uma conduta íntegra e eficiente por parte dos órgãos.
    </p>
    <p style="text-indent: 30px;  text-align: justify;">
        Esses princípios são balizadores usados para orientar as leis administrativas. Eles servem
        para dar um senso maior de direção à Administração Pública, tornando suas ações válidas e fazendo
        com que atendam aos interesses da sociedade. Outrossim, os princípios da administração pública
        estão presentes na Carta Constitucional de 1988, em seu artigo 37, como se vê a seguir:
    </p>

    <table style="width: 100%; border-collapse: collapse; page-break-inside: auto;">
        <tr style="page-break-after: auto;">
            <!-- Coluna vazia ocupa 50% -->
            <td style="width: 50%;"></td>

            <!-- Coluna do texto ocupa 50% -->
            <td style="width: 50%; text-align: justify; vertical-align: top;">
                Art. 37. A administração pública direta e
                indireta de qualquer dos Poderes da União, dos
                Estados, do Distrito Federal e dos Municípios
                obedecerá aos princípios de legalidade,
                impessoalidade, moralidade, publicidade e
                eficiência (...)<br><br>
            </td>
        </tr>
    </table>

    <p style="text-indent: 30px;  text-align: justify;">
        Nesta esteira, as leis infralegais nº 9.784/99 e 14.133/2021 também remetem aos princípios
        da administração pública, demonstrando sua importância e resguardando sua principal finalidade,
        garantir o respeito e a probidade aos atos processuais.
    </p>
    <p style="text-indent: 30px;  text-align: justify;">
        Dentre os princípios basilares, destacamos, para o caso concreto, a Legalidade e a
        Publicidade.
    </p>
    <p style="text-indent: 30px;  text-align: justify;">
        O Princípio da Legalidade, em processos licitatórios, possui atividade totalmente vinculada. A lei
        define as condições da atuação dos Agentes Administrativos, determinando as tarefas e impondo
        condições excludentes de escolhas pessoais ou subjetivas, ressalvados os casos de
        discricionariedade do agente público.
    </p>
    <p style="text-indent: 30px;  text-align: justify;">
        Neste sentido, Matheus Carvalho assim dispõe sobre o caráter discricionário dispensado
        aos agentes públicos
    </p>

    <table style="width: 100%; border-collapse: collapse; page-break-inside: auto;">
        <tr style="page-break-after: auto;">
            <!-- Coluna vazia ocupa 50% -->
            <td style="width: 50%;"></td>

            <!-- Coluna do texto ocupa 50% -->
            <td style="width: 50%; text-align: justify; vertical-align: top;">
                “(...) se faz necessário lembrar que a
                Legalidade não exclui a atuação discricionária
                do agente público, tendo essa que ser levada
                em consideração quando da análise, por esse
                gestor, da conveniência e da oportunidade em prol do interesse público. Como a Administração não
                pode prever todos os casos onde atuará, deverá valer-se da
                discricionariedade para atender a finalidade legal, devendo, todavia, a escolha se pautar em
                critérios que respeitem os princípios constitucionais como a proporcionalidade e razoabilidade
                de
                conduta (...)”<br><br>
            </td>
        </tr>
    </table>

    <p style="text-indent: 30px;  text-align: justify;">
        Portanto, o respeito à Legalidade deve sempre ser observado, mesmo nas práticas de atos
        discricionários, visto que a atividade do administrador só se legitima quando condiz com o
        dispositivo legal.
    </p>
    <p style="text-indent: 30px;  text-align: justify;">
        Com relação ao Princípio da Publicidade, sua principal finalidade é o conhecimento público
        sobre os atos praticados pela administração. Em outras palavras, tudo o que é realizado pelo Estado
        deve ser amplamente franqueado, resguardadas as reservas previstas na Lei nº 12.527/2011.
    </p>
    <p style="text-indent: 30px;  text-align: justify;">
        Para o caso em comento, o Princípio da Publicidade é fundamental, pois trata-se de licitação
        pública, com sessão aberta ao público. Necessário, portanto, que os interessados tenham acesso aos
        atos tomados no curso do processo, inclusive a fase interna, no prazo estabelecido no Art. 55, I,
        “a”, da Lei Federal nº 14.133/2021, qual seja:
    </p>

    <table style="width: 100%; border-collapse: collapse; page-break-inside: auto;">
        <tr style="page-break-after: auto;">
            <!-- Coluna vazia ocupa 50% -->
            <td style="width: 50%;"></td>

            <!-- Coluna do texto ocupa 50% -->
            <td style="width: 50%; text-align: justify; vertical-align: top;">
                Art. 55. Os prazos mínimos para apresentação
                de propostas e lances, contados a partir da data
                de divulgação do edital de licitação, são de: <br><br>
            </td>
        </tr>

        <tr>
            <td></td>
            <td style="text-align: justify;">
                I - para aquisição de bens:
                <br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">
                a) 8 (oito) dias úteis, quando adotados os
                critérios de julgamento de menor preço ou de
                maior desconto;
                <br><br>
            </td>
        </tr>
    </table>

    <p style="text-indent: 30px; text-align: justify;">
        Conforme se extrai dos autos, o processo trata da aquisição de {!! strip_tags($processo->objeto) !!}, cujo
        critério de julgamento é o de menor preço, exigindo, nos moldes legais, o prazo de 8 (oito) dias
        úteis entre a publicação do edital e a apresentação das propostas.
    </p>

    <p style="font-weight: bold; font-size: 14px;">3.2 - DA CONSTITUIÇÃO FEDERAL EM CONSONÂNCIA COM AS LEIS
        INFRALEGAIS.</p>

    <p style="text-indent: 30px; text-align: justify;">
        Inicialmente, deve-se ressaltar que a natureza do processo licitatório é, ordinariamente, o
        atendimento de demandas públicas, tendo como prisma a livre concorrência e o preço justo e mais
        vantajoso para a administração.
    </p>
    <p style="text-indent: 30px; text-align: justify;">
        A Constituição Federal de 1988, em seu artigo 37, inciso XXI, estabelece como regra, que
        as obras, serviços, compras e alienações devem ser contratados mediante processo de licitação
        pública.
    </p>
    <p style="text-indent: 30px; text-align: justify;">
        Nesta senda, convém observar que a Lei nº. 14.133/2021, regulamenta o art. 37, XXI, da
        Constituição Federal, instituindo normas para licitações e contratos da Administração Pública.
        Logo, os processos licitatórios instruídos a partir de janeiro de 2025 devem ser norteados pela lei
        em comento, como é o caso em tela.
    </p>

    <p style="font-weight: bold; font-size: 14px;">3.3. DA ESCOLHA DA MODALIDADE</p>

    <p style="text-indent: 30px; text-align: justify;">
        Os autos em questão revelam que o processo licitatório teve como fulcro o Art. 28, I, e 29
        da Lei de Licitações nº 14.133/2021. Vejamos:
    </p>

    <table style="width: 100%; border-collapse: collapse; page-break-inside: auto;">
        <tr style="page-break-after: auto;">
            <!-- Coluna vazia ocupa 50% -->
            <td style="width: 50%;"></td>

            <!-- Coluna do texto ocupa 50% -->
            <td style="width: 50%; text-align: justify; vertical-align: top;">
                Art. 28. São modalidades de licitação:<br><br>
            </td>
        </tr>

        <tr>
            <td></td>
            <td style="text-align: justify;">
                I - pregão;
                <br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">
                (...)
                <br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">
                Art. 29. A concorrência e o pregão seguem o
                rito procedimental comum a que se refere o art.
                17 desta Lei, adotando-se o pregão sempre que
                o objeto possuir padrões de desempenho e
                qualidade que possam ser objetivamente definidos pelo edital, por meio de especificações usuais
                de
                mercado.
                <br><br>
            </td>
        </tr>
    </table>

    <p style="text-indent: 30px; text-align: justify;">
        Assim, compreende-se que o processo de aquisição aqui apreciado se adequa aos ditames
        do dispositivo legal mencionado acima, pois trata se de “{!! strip_tags($processo->objeto) !!}”, cuja descrição dos
        itens é de fácil identificação, conforme verificado no Termo de Referência. Portanto, a modalidade
        escolhida para o certame licitatório se encaixa ao objeto pretendido.
    </p>

    <p style="font-weight: bold; font-size: 14px;">3.4. DA FASE DE PLANEJAMENTO</p>

    <p style="text-indent: 30px; text-align: justify;">
        Inicialmente, é importante registrar que a Lei nº 14.133/2021 trouxe à baila a necessidade
        de os órgãos e entidades implementarem ações de governança e gestão de riscos, nos moldes do
        Art. 11, Parágrafo Único:
    </p>

    <table style="width: 100%; border-collapse: collapse; page-break-inside: auto;">
        <tr style="page-break-after: auto;">
            <!-- Coluna vazia ocupa 50% -->
            <td style="width: 50%;"></td>

            <!-- Coluna do texto ocupa 50% -->
            <td style="width: 50%; text-align: justify; vertical-align: top;">
                Art. 11. O processo licitatório tem por objetivos:<br><br>
            </td>
        </tr>

        <tr>
            <td></td>
            <td style="text-align: justify;">
                (...)
                <br><br>
            </td>
        </tr>

        <tr>
            <td></td>
            <td style="text-align: justify;">
                Parágrafo único. A alta administração do órgão
                ou entidade é responsável pela governança das
                contratações e deve implementar processos e
                estruturas, inclusive de gestão de riscos e
                controles internos, para avaliar, direcionar e
                monitorar os processos licitatórios e os
                respectivos contratos, com o intuito de alcançar
                os objetivos estabelecidos no caput deste
                artigo, promover um ambiente íntegro e
                confiável, assegurar o alinhamento das
                contratações ao planejamento estratégico e às
                leis orçamentárias e promover eficiência,
                efetividade e eficácia em suas contratações.
                <br><br>
            </td>
        </tr>
    </table>

    <p style="text-indent: 30px; text-align: justify;">
        Nesta esteira, é cediço que a Nova Lei de Licitações tem o condão de empreender medidas,
        instrumentos, de programação e gestão de riscos para as contratações realizadas pela
        Administração.
    </p>
    <p style="text-indent: 30px; text-align: justify;">
        Ademais, a Nova Lei preocupou-se também com o planejamento específico de cada
        contratação a ser realizada, com o intuito de manter um alinhamento com o programa de
        contratações e orçamento do ponto de vista macro.
    </p>
    <p style="text-indent: 30px; text-align: justify;">
        In verbis, a Lei nº 14.133/2021, elenca elementos centrais da fase de planejamento,
        necessários à instrução do processo licitatório quais sejam: Documento de Formalização de
        Demanda, Estudos Técnicos Preliminares, Mapa de Gerenciamento de Riscos, Termo de
        Referência, Edital e Minuta de Contrato.
    </p>

    <p style="font-weight: bold; font-size: 14px;">3.5. DO DOCUMENTO DE FORMALIZAÇÃO DE DEMANDA (DFD)</p>

    <p style="text-indent: 30px; text-align: justify;">
        É o ponto primordial para a aquisição de produtos ou serviços. É a partir desse documento
        que o órgão poderá indicar quais suas necessidades para que, em seguida seja realizado o estudo
        daquela demanda conforme os critérios elencados na NLL.
    </p>
    <p style="text-indent: 30px; text-align: justify;">
        Deve ser preenchido pela unidade requisitante com os seguintes elementos: (i.) justificativa
        da necessidade da contratação; (ii.) quantidade de serviço ou produtos a ser adquirido; (iii.) previsão
        de data em que deve ser iniciada a prestação dos serviços ou recebimento dos produtos; (iv.)
        indicação do setor demandante e do departamento que irá elaborar os Estudos Preliminares.
    </p>
    <p style="text-indent: 30px; text-align: justify;">
        Pelo que se observa dos autos do processo em epígrafe, os requisitos elencados acima foram atendidos.
    </p>

    <p style="font-weight: bold; font-size: 14px;">3.6. DOS ESTUDOS TÉCNICOS PRELIMINARES (ETP)</p>
    <p style="text-indent: 30px; text-align: justify;">
        O ETP é peça cujo objetivo é evidenciar o problema a ser resolvido e a solução mais
        adequada, com o corresponde valor estimado, acompanhado de montantes unitários, memórias de
        cálculo e documentos de suporte.
    </p>
    <p style="text-indent: 30px; text-align: justify;">
        Faz parte da primeira etapa do planejamento de uma contratação que caracteriza
        determinada necessidade, descreve as análises realizadas em termos de requisitos, alternativas,
        escolhas e resultados pretendidos e demais características, dando base ao anteprojeto, ao termo de
        referência ou ao projeto básico, caso se conclua pela viabilidade da contratação.
    </p>
    <p style="text-indent: 30px; text-align: justify;">
        Tem previsão expressa em vários trechos da NLL, mas é no Art. 18, § 1° que estão os itens
        indispensáveis ao seu preenchimento. Vejamos:
    </p>

    <table style="width: 100%; border-collapse: collapse; page-break-inside: auto;">
        <tr style="page-break-after: auto;">
            <!-- Coluna vazia ocupa 50% -->
            <td style="width: 50%;"></td>

            <!-- Coluna do texto ocupa 50% -->
            <td style="width: 50%; text-align: justify; vertical-align: top;">
                Art. 18. A fase preparatória do processo
                licitatório é caracterizada pelo planejamento e
                deve compatibilizar-se com o plano de
                contratações anual de que trata o inciso VII do
                caput do art. 12 desta Lei, sempre que
                elaborado, e com as leis orçamentárias, bem
                como abordar todas as considerações técnicas,
                mercadológicas e de gestão que podem
                interferir na contratação, compreendidos: <br><br>
            </td>
        </tr>

        <tr>
            <td></td>
            <td style="text-align: justify;">
                (...)
                <br><br>
            </td>
        </tr>

        <tr>
            <td></td>
            <td style="text-align: justify;">
                § 1º O estudo técnico preliminar a que se refere
                o inciso I do caput deste artigo deverá
                evidenciar o problema a ser resolvido e a sua
                melhor solução, de modo a permitir a avaliação da
                viabilidade técnica e econômica da
                contratação, e conterá os seguintes elementos:
                <br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="font-weight: bold;">
                I - descrição da necessidade da contratação,
                considerado o problema a ser resolvido sob
                a perspectiva do interesse público;
                <br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">
                II - demonstração da previsão da contratação
                no plano de contratações anual, sempre que
                elaborado, de modo a indicar o seu alinhamento
                com o planejamento da Administração;
                <br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">
                III - requisitos da contratação;
                <br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="font-weight: bold;">
                IV - estimativas das quantidades para a
                contratação, acompanhadas das memórias
                de cálculo e dos documentos que lhes dão
                suporte, que considerem interdependências
                com outras contratações, de modo a
                possibilitar economia de escala;
                <br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">
                V - levantamento de mercado, que consiste na
                análise das alternativas possíveis, e
                justificativa técnica e econômica da escolha do
                tipo de solução a contratar;
                <br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="font-weight: bold;">
                VI - estimativa do valor da contratação,
                acompanhada dos preços unitários
                referenciais, das memórias de cálculo e dos
                documentos que lhe dão suporte, que
                poderão constar de anexo classificado, se a
                Administração optar por preservar o seu
                sigilo até a conclusão da licitação;
                <br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">
                VII - descrição da solução como um todo,
                inclusive das exigências relacionadas à
                manutenção e à assistência técnica, quando for
                o caso;
                <br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="font-weight: bold;">
                VIII - justificativas para o parcelamento ou
                não da contratação;
                <br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">
                IX - demonstrativo dos resultados pretendidos
                em termos de economicidade e de melhor
                aproveitamento dos recursos humanos,
                materiais e financeiros disponíveis;
                <br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">
                X - providências a serem adotadas pela
                Administração previamente à celebração do
                contrato, inclusive quanto à capacitação de
                servidores ou de empregados para fiscalização
                e gestão contratual;
                <br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">
                XI - contratações correlatas e/ou interdependentes;
                <br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">
                XII - descrição de possíveis impactos
                ambientais e respectivas medidas mitigadoras,
                incluídos requisitos de baixo consumo de
                energia e de outros recursos, bem como
                logística reversa para desfazimento e
                reciclagem de bens e refugos, quando aplicável;
                <br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="font-weight: bold;">
                XIII - posicionamento conclusivo sobre a
                adequação da contratação para o
                atendimento da necessidade a que se
                destina. (Grifo Nosso)
                <br><br>
            </td>
        </tr>
    </table>

    <p style="text-indent: 30px; text-align: justify;">
        Nesta senda, a elaboração do ETP deve contemplar os elementos constantes acima, sendo
        os incisos I, IV, VI, VIII e XIII itens obrigatórios à formulação do documento, conforme § 2º do
        Art. 18, da NLL:
    </p>

    <table style="width: 100%; border-collapse: collapse; page-break-inside: auto;">
        <tr style="page-break-after: auto;">
            <!-- Coluna vazia ocupa 50% -->
            <td style="width: 50%;"></td>

            <!-- Coluna do texto ocupa 50% -->
            <td style="width: 50%; text-align: justify; vertical-align: top;">
                § 2º <span style="font-weight: bold">O estudo técnico preliminar deverá
                    conter ao menos os elementos previstos nos
                    incisos I, IV, VI, VIII e XIII do § 1º deste
                    artigo</span> e, quando não contemplar os demais
                elementos previstos no referido parágrafo,
                apresentar as devidas justificativas. (Grifo
                Nosso)<br><br>
            </td>
        </tr>
    </table>

    <p style="text-indent: 30px; text-align: justify;">
        Isto posto, extrai-se dos autos da instrução que o ETP contempla as exigências mínimas
        contidas na Nova Lei de Licitações.
    </p>

    <p style="font-weight: bold; font-size: 14px;">3.7. DO MAPA DE RISCO </p>

    <p style="text-indent: 30px; text-align: justify;">
        Outro instrumento trazido pela NLL, o Mapa de Riscos integra a fase preparatória do
        processo licitatório. Trata-se de uma análise dos riscos que possam comprometer o sucesso da
        licitação e a boa execução contratual.
    </p>
    <p style="text-indent: 30px; text-align: justify;">
        Nesta esteira, os riscos pertinentes à contratação desejada devem ser identificados,
        analisados, tratados, monitorados e comunicados no processo administrativo respectivo, por meio
        do Mapa de Riscos.
    </p>
    <p style="text-indent: 30px; text-align: justify;">
        Diante disso, o documento, a exemplo dos demais que compõem a fase de planejamento,
        também exige alguns cuidados inerentes à sua confecção, visto que materializa as análises
        realizadas, devendo constar o registro das principais etapas do processo de gestão dos riscos
        aplicado na contratação proposta.
    </p>
    <p style="text-indent: 30px; text-align: justify;">
        No caso em comento, o Mapa de Riscos desenvolvido pela {{ $detalhe->secretaria }} analisa riscos relacionados à AQUISIÇÃO DE {!! strip_tags($processo->objeto) !!},
        ponderando situações que contemplam que possam afetar negativamente a contratação pretendida.
    </p>
    <p style="text-indent: 30px; text-align: justify;">
        Tais apontamentos revelam a preocupação do órgão com os possíveis riscos no decorrer do
        processo de aquisição e na fase de execução do contrato. No entender desse parecerista, o Mapa de
        Riscos juntado aos autos está de acordo com os preceitos da NLL, pois busca minimizar possíveis
        ameaças com soluções imediatas.
    </p>
    <p style="font-weight: bold; font-size: 14px;">3.8. DO TERMO DE REFERÊNCIA (TR) </p>

    <p style="text-indent: 30px; text-align: justify;">
        O Termo de Referência, nos termos Art. 6º, inciso XXIII, da Nova Lei de Licitações, é o
        “documento necessário para a contratação de bens e serviços”.
    </p>
    <p style="text-indent: 30px; text-align: justify;">
        Nas palavras do Professor Jair Eduardo Santana (2020, p. 40):
    </p>
    <table style="width: 100%; border-collapse: collapse; page-break-inside: auto;">
        <tr style="page-break-after: auto;">
            <!-- Coluna vazia ocupa 50% -->
            <td style="width: 50%;"></td>

            <!-- Coluna do texto ocupa 50% -->
            <td style="width: 50%; text-align: justify; vertical-align: top;">
                A expressão em análise, Termo de Referência,
                possui, assim, significado comum que nos
                mostra tratar-se de um documento que
                circunscreve limitadamente um objeto e serve
                de fonte para fornecimento das informações
                existentes sobre ele. <br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">
                Em suma: O Termo de Referência é o
                documento mediante o qual a Administração
                explicita o objeto, documentando de forma
                sistemática, detalhada e cabal o objeto da
                contratação que pretende realizar, permitindo,
                de tal modo, dimensionar a decisão e o poder
                do respectivo gestor público. <br><br>
            </td>
        </tr>
    </table>

    <p style="text-indent: 30px; text-align: justify;">
        Conforme se verifica, o Termo de Referência é um documento que serve de fonte para o
        fornecimento de informações necessárias ao conhecimento do objeto que se pretende adquirir.
    </p>
    <p style="text-indent: 30px; text-align: justify;">
        Outrossim, o novo regramento licitatório trouxe em seu bojo (Art. 6º, XXIII), informações
        importantes à sua elaboração. Vejamos:
    </p>

    <table style="width: 100%; border-collapse: collapse; page-break-inside: auto;">
        <tr style="page-break-after: auto;">
            <!-- Coluna vazia ocupa 50% -->
            <td style="width: 50%;"></td>

            <!-- Coluna do texto ocupa 50% -->
            <td style="width: 50%; text-align: justify; vertical-align: top;">
                Art. 6º Para os fins desta Lei, consideram-se:<br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">
                XXIII - termo de referência: documento
                necessário para a contratação de bens e
                serviços, que deve conter os seguintes
                parâmetros e elementos descritivos: <br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">
                a) definição do objeto, incluídos sua natureza,
                os quantitativos, o prazo do contrato e, se for o
                caso, a possibilidade de sua prorrogação; <br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">
                b) fundamentação da contratação, que consiste
                na referência aos estudos técnicos preliminares
                correspondentes ou, quando não for possível
                divulgar esses estudos, no extrato das partes
                que não contiverem informações sigilosas; <br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">
                c) descrição da solução como um todo,
                considerado todo o ciclo de vida do objeto; <br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">
                d) requisitos da contratação;
                <br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">
                e) modelo de execução do objeto, que consiste
                na definição de como o contrato deverá
                produzir os resultados pretendidos desde o seu
                início até o seu encerramento;
                <br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">
                f) modelo de gestão do contrato, que descreve
                como a execução do objeto será acompanhada
                e fiscalizada pelo órgão ou entidade;
                <br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">
                g) critérios de medição e de pagamento;
                <br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">
                h) forma e critérios de seleção do fornecedor;
                <br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">
                i)
                estimativas do valor da contratação,
                acompanhadas dos preços unitários
                referenciais, das memórias de cálculo e dos
                documentos que lhe dão suporte, com os
                parâmetros utilizados para a obtenção dos
                preços e para os respectivos cálculos, que
                devem constar de documento separado e
                classificado;
                <br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">
                j) adequação orçamentária;
                <br><br>
            </td>
        </tr>
    </table>

    <p style="text-indent: 30px; text-align: justify;"">
            Diante do exposto e compulsando os autos do processo ora em análise, verifica-se que o
            Termo de Referência ostenta condições mínimas de detalhamento do objeto pleiteado, apresentando
            um “norte” a ser seguido pelos potenciais fornecedores em relação à documentação necessária à
            participação no certame, assim como as obrigações do contratante e do contratado durante a fase
            de execução do contrato, possíveis penalidades, entre outros.
        </p>

        <p style=" font-weight: bold; font-size: 14px;">3.9. DA PESQUISA DE PREÇOS </p>

    <p style="text-indent: 30px; text-align: justify;">
        A pesquisa de preços é um procedimento indispensável para a verificação de existência de
        recursos suficientes para cobrir despesas decorrentes de contratação pública. Além disso, é utilizada
        para confrontar e examinar as propostas dos licitantes e nortear o preço que a Administração está
        disposta a contratar.
    </p>
    <p style="text-indent: 30px; text-align: justify;">
        É por meio da pesquisa de preços que se constata o preço justo, a existência de recursos
        suficientes para adquirir os bens ou serviços, a definição da modalidade licitatória, a identificação
        de sobrepreços e de propostas inexequíveis e a garantia da seleção da proposta mais vantajosa para
        a Administração.
    </p>
    <p style="font-weight: bold; font-size: 14px;">3.10. DA MINUTA DO EDITAL </p>
    <p style="text-indent: 30px; text-align: justify;">
        A Lei nº 14.133/2021, em seu artigo 25, trata dos requisitos a serem observados por ocasião
        da elaboração da minuta de edital. Vejamos:
    </p>

    <table style="width: 100%; border-collapse: collapse; page-break-inside: auto;">
        <tr style="page-break-after: auto;">
            <!-- Coluna vazia ocupa 50% -->
            <td style="width: 50%;"></td>

            <!-- Coluna do texto ocupa 50% -->
            <td style="width: 50%; text-align: justify; vertical-align: top;">
                Art. 25. O edital deverá conter o objeto da
                licitação e as regras relativas à convocação, ao
                julgamento, à habilitação, aos recursos e às
                penalidades da licitação, à fiscalização e à
                gestão do contrato, à entrega do objeto e às
                condições de pagamento. <br><br>
            </td>
        </tr>
    </table>

    <p style="text-indent: 30px; text-align: justify;">
        Nestes termos, a minuta do edital foi juntada aos autos e reúne cláusulas e condições
        essenciais exigidas nos instrumentos da espécie.
    </p>
    <p style="font-weight: bold; font-size: 14px;">3.10. DA MINUTA DO EDITAL </p>
    <p style="text-indent: 30px; text-align: justify;">
        Conforme se verifica, a minuta do contrato seguiu os requisitos constantes nos artigos 89 a
        95 da Lei nº 14.133/2021, estando livre de qualquer nulidade.
    </p>
    <p style="text-indent: 30px; text-align: justify;">
        Consta no anexo da minuta do edital, a minuta do contrato com cláusulas que geram
        segurança jurídica necessária para as partes envolvidas no processo.
    </p>
    <p style="font-weight: bold; font-size: 14px;">4. CONCLUSÃO</p>
    <p style="text-indent: 30px; text-align: justify;">
        Ante o exposto, salvo melhor juízo, presentes os pressupostos de regularidade jurídica dos
        autos, ressalvado o juízo de mérito da Administração e os aspectos técnicos, econômicos e
        financeiros, que escapam à análise desse Jurídico, essa assessoria manifesta-se FAVORAVEL AO
        PREGÃO ELETRÔNICO, podendo o processo de contratação produzir os efeitos jurídicos
        pretendidos, com fundamento na praxe e regras vigentes.
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
    @elseif($parecer === 'parecer_3')
    <p style="text-align: center; font-weight: bold;">
        PARECER JURÍDICO
    </p>

    <p>
        Processo Administrativo Nº {{ $processo->numero_processo }}
    </p>
    <p>
        REF: Análise de Minuta de Edital de Pregão Eletrônico Nº {{ $processo->numero_procedimento }}.
    </p>

    <p style="text-align: center; font-weight: bold;">
        RELATÓRIO
    </p>

    <p style="text-indent: 30px; text-align: justify;">
        Trata-se de análise jurídica da minuta do edital de licitação pública que visa a futura
        {!! strip_tags($processo->objeto) !!}, a fim de atender as demandas da Prefeitura e
        suas Secretarias Municipais, por meio de licitação, na modalidade pregão, na forma eletrônica, com
        fulcro na Nova Lei de Licitações e Contratos – Lei nº 14.133/2021.
    </p>
    <p style="text-indent: 30px; text-align: justify;">
        Neste cenário, vieram os autos contendo: O documento de formalização da demanda, que
        apresenta a justificativa da necessidade de contratação. Além da autorização para instauração do
        procedimento, o estudo técnico preliminar, a pesquisa de mercado, a previsão do orçamento, o
        termo de referência, a portaria de designação do pregoeiro e da equipe de apoio, bem como a minuta
        do respectivo Edital licitatório.
    </p>
    <p style="text-indent: 30px; text-align: justify;">
        O sistema de contratação adotado para o certame, desde a sua origem, é aquele previsto na
        Lei de Licitações, assim, sob esta perspectiva, o Edital encontra se em perfeita consonância com a
        Lei nº 14.133/2021.
    </p>
    <p style="text-indent: 30px; text-align: justify;">
        Após a instrução processual interna, por meio de vários atos exarados pelos setores
        responsáveis devidamente ratificados pelos seus agentes públicos responsáveis, veio para consulta
        jurídica quanto à legalidade tão somente da minuta do Edital, em seus aspectos estritamente
        jurídicos.
    </p>
    <p style="text-indent: 30px;  font-weight: bold;">
        É o sucinto relatório.
    </p>

    <p style="text-align: center; font-weight: bold;">
        PRELIMINARMENTE
    </p>
    <p style="text-align: center; font-weight: bold;">
        DA NATUREZA OPINATIVA E CONSULTIVA DO PARECER JURÍDICO
    </p>
    <p style="text-indent: 30px; text-align: justify;">
        A presente manifestação se limita à dúvida estritamente jurídica “in abstrato”, ora proposta
        e, aos aspectos jurídicos da matéria, abstendo-se quanto aos aspectos técnicos, administrativos,
        econômico-financeiros e quanto a outras questões não ventiladas ou que exijam o exercício de
        conveniência e discricionariedade da Administração. Ressalte-se que o presente parecer se limita
        aos aspectos legais, não interferindo na discricionariedade da Administração Pública.
    </p>
    <p style="text-align: center; font-weight: bold;">
        DA ANÁLISE JURÍDICA
    </p>
    <p style="text-indent: 30px; text-align: justify;">
        Importante destacar que tanto a abertura de certame quanto a sua instrução será realizada
        sob a responsabilidade do pregoeiro (a) designado (a), bem como pela respectiva equipe de apoio,
        sem qualquer gerência ou intervenção desta Assessoria jurídica ou Procuradoria. Sabe-se que a
        Administração Pública só pode atuar em conformidade com os princípios basilares dispostos na
        Constituição Federal, conforme art. 37, caput, abaixo transcrito:
    </p>

    <table style="width: 100%; border-collapse: collapse; page-break-inside: auto;">
        <tr style="page-break-after: auto;">
            <!-- Coluna vazia ocupa 50% -->
            <td style="width: 50%;"></td>

            <!-- Coluna do texto ocupa 50% -->
            <td style="width: 50%; text-align: justify; vertical-align: top;">
                “Art. 37. A administração pública direta e
                indireta de qualquer dos Poderes da União, dos
                Estados, do Distrito Federal e dos Municípios
                obedecerá aos princípios de legalidade,
                impessoalidade, moralidade, publicidade e
                eficiência...”.<br><br>
            </td>
        </tr>
    </table>

    <p style="text-indent: 30px; text-align: justify;">
        O artigo 18 e incisos da Lei nº 14.133/2021 estabelece todos os elementos que devem ser
        compreendidos nos autos do processo de contratação pública, senão vejamos:
    </p>

    <table style="width: 100%; border-collapse: collapse; page-break-inside: auto;">
        <tr style="page-break-after: auto;">
            <!-- Coluna vazia ocupa 50% -->
            <td style="width: 50%;"></td>

            <!-- Coluna do texto ocupa 50% -->
            <td style="width: 50%; text-align: justify; vertical-align: top;">
                “Art. 18. <span style="font-weight: bold;">A fase preparatória do processo
                    licitatório</span> é caracterizada pelo planejamento e
                deve compatibilizar-se com o plano de
                contratações anual de que trata o inciso VII do
                caput do art. 12 desta Lei, sempre que
                elaborado, e com as leis orçamentárias, bem
                como <span style="font-weight: bold;">abordar todas as considerações
                    técnicas, mercadológicas e de gestão que
                    podem interferir na contratação,
                    compreendidos”: I -a descrição da
                    necessidade da contratação fundamentada
                    em estudo técnico preliminar</span> que caracterize
                o interesse público envolvido; <span style="font-weight: bold;">II - a definição
                    do objeto</span> para o atendimento da necessidade,
                <span style="font-weight: bold;">por meio de termo de referência,</span> anteprojeto,
                projeto básico ou projeto executivo, conforme
                o caso; <span style="font-weight: bold;">III - a definição das condições de
                    execução e pagamento, das garantias
                    exigidas e ofertadas e das condições de
                    recebimento;</span> <br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">
                <span style="font-weight: bold;">
                    I- o orçamento estimado</span>, com as composições
                dos preços utilizados para sua formação;
                <br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">
                II a elaboração do edital de licitação;
                <br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">
                III - a elaboração de minuta de contrato,
                quando necessária, que constará
                obrigatoriamente como anexo do edital de
                licitação;
                <br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">
                IV - o regime de fornecimento de bens, de
                prestação de serviços ou de execução de obras
                e serviços de engenharia, observados os
                potenciais de economia de escala;
                <br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">
                V- a modalidade de licitação, o critério de
                julgamento, o modo de disputa e a adequação e
                eficiência da forma de combinação desses
                parâmetros, para os fins de seleção da proposta
                apta a gerar o resultado de contratação mais
                vantajoso para a Administração Pública,
                considerado todo o ciclo de vida do objeto;
                <br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">
                VI- a motivação circunstanciada das condições
                do edital, tais como justificativa de exigências
                de qualificação técnica, mediante indicação das
                parcelas de maior relevância técnica ou valor
                significativo do objeto, e de qualificação
                econômico-financeira, justificativa dos
                critérios de pontuação e julgamento das
                propostas técnicas, nas licitações com
                julgamento por melhor técnica ou técnica e
                preço, e justificativa das regras pertinentes à
                participação de empresas em consórcio;
                <br><br>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: justify;">
                VII- a análise dos riscos que possam
                comprometer o sucesso da licitação e a boa
                execução contratual”.
                <br><br>
            </td>
        </tr>
    </table>

    <p style="text-indent: 30px; text-align: justify;">
        Analisando os documentos que compõe a instrução do processo de contratação, constata
        se a presença da definição do objeto e das justificativas para a sua contratação, a autorização da
        Autoridade Competente para a instauração do processo de contratação, o estudo técnico preliminar,
        a pesquisa mercadológica, a previsão de dotação orçamentária, o termo de referência, a portaria de
        designação do pregoeiro e da equipe.
    </p>
    <p style="text-indent: 30px; text-align: justify;">
        Neste contexto, é possível aferir que os autos atendem as exigências mínimas legais,
        ficando evidenciada a solução mais adequada para atendimento da necessidade pública. E, nos
        termos apresentados na justificativa de contratação, resta evidente a sua necessidade, tendo em vista
        a prestação de serviço de interesse público realizado pela Prefeitura Municipal, onde os objetos da
        contratação atenderão a demanda interna administrativa, e, a demanda externa, com o atendimento
        ao público.
    </p>
    <p style="text-indent: 30px; text-align: justify;">
        Seguindo a análise, verifica-se que o termo de referência elaborado a partir do estudo
        técnico preliminar, contém os seguintes itens: definição do objeto, justificativa e objetivo da
        licitação, classificação dos objetos comuns, prazo de entrega e condições de execução, condições
        de pagamento, dotação orçamentária, deveres da Contratante e da Contratada, fiscalização do
        contrato, revisão de preços, extinção do contrato e sanções aplicáveis, contendo, por conseguinte,
        todos os elementos exigidos pelo inciso XIII do artigo 6º da Lei nº 14.133/2021.
    </p>
    <p style="text-indent: 30px; text-align: justify;">
        Por sua vez, o estudo técnico preliminar apresentado nos autos possuem os seguintes
        elementos: definição do objeto, necessidade de contratação e justificativa, especificação técnica e
        quantitativo do objeto, alinhamento ao plano institucional, requisitos de habilitação, obrigações
        mínimas do fornecedor, estimativa de preços, resultados pretendidos, justificativa para a formação
        do lote único, riscos e declaração de viabilidade, portanto, encontra-se em perfeita harmonia ao
        mínimo exigido em lei e disposto no §1º e incisos do artigo 18 da NLLC.
    </p>
    <p style="text-indent: 30px; text-align: justify;">
        Sendo constatado que a fase preparatória do certame se encontra em consonância com as
        exigências mínimas exigidas pela NLLC para fins de contratação nesta nova sistemática de
        licitações públicas.
    </p>

    <p style="text-align: center; font-weight: bold;">
        DA MINUTA DO EDITAL
    </p>
    <p style="text-indent: 30px; text-align: justify;">
        Conforme já informado ao norte, a elaboração da minuta do edital é um dos elementos que
        devem ser observados na fase interna da licitação pública, tendo aquele sido submetido à análise
        jurídica contendo dois anexos, quais sejam: o termo de referência e a minuta do contrato.
    </p>
    <p style="text-indent: 30px; text-align: justify;">
        Ademais, a minuta do Edital veio com os seguintes itens descriminados: sessão pública,
        definição do objeto, recursos orçamentários, condições de participação, encaminhamento e
        elementos da proposta, formulação dos lances, aceitabilidade e classificação da proposta,
        habilitação, recurso, adjudicação e homologação do certame, pedido de esclarecimentos e
        impugnação ao edital, disposições finais e foro de julgamento.
    </p>
    <p style="text-indent: 30px; text-align: justify;">
        Diante do apresentado, afere-se que os itens da minuta do Edital estão definidos de forma
        clara e com a devida observância do determinado no artigo 25 da Lei nº 14.133/2021, que assim
        dispõe:
    </p>

    <table style="width: 100%; border-collapse: collapse; page-break-inside: auto;">
        <tr style="page-break-after: auto;">
            <!-- Coluna vazia ocupa 50% -->
            <td style="width: 50%;"></td>

            <!-- Coluna do texto ocupa 50% -->
            <td style="width: 50%; text-align: justify; vertical-align: top;">
                <strong>
                    “Art. 25. O edital deverá conter o objeto da
                    licitação e as regras relativas à convocação,
                    ao julgamento, à habilitação, aos recursos e
                    às penalidades da licitação, à fiscalização e à
                    gestão do contrato, à entrega do objeto e às
                    condições de pagamento”.
                </strong>
                <br><br>
            </td>
        </tr>
    </table>

    <p style="text-indent: 30px; text-align: justify;">
        Por se tratar de fornecimento de objeto de forma contínua, a ser entregue parceladamente,
        de acordo com a necessidade da Contratante, se faz necessário que o acordo firmado seja
        devidamente instrumentalizado em contrato, visto não se enquadrar nas hipóteses de exceção
        quanto a obrigatoriedade do instrumento, conforme disposto no artigo 95 da Lei nº 14.133/2021.
    </p>
    <p style="text-indent: 30px; text-align: justify;">
        Tendo a minuta do contrato as seguintes cláusulas: documentos, objeto, obrigações da
        Contratante e Contratada, fiscalização do contrato, preço, dotação orçamentária, pagamento,
        entrega e recebimento do objeto, alterações, sanções administrativas, vigência, extinção do
        contrato, casos omissos, publicações e eleição de foro.
    </p>
    <p style="text-indent: 30px; text-align: justify;">
        Nesta esteira, o artigo 92 e incisos da NLLC, estabelece as cláusulas que são necessárias
        nos contratos administrativos, senão vejamos. Portanto, a minuta se encontra com as cláusulas
        mínimas devidamente amparadas na Lei nº 14.133/2021.
    </p>
    <p style="text-indent: 30px; text-align: justify;">
        De mais a mais, a minuta do Edital do processo licitatório estabelece a modalidade de
        licitação para a contratação do objeto como sendo o pregão em sua forma eletrônica, o que se
        encontra em perfeita correção uma vez que o objeto se enquadra na categoria de bens comuns, com
        padrões de qualidade e desempenho passíveis de descrição objetiva e usualmente encontrados no
        mercado, cf. o disposto nos incisos XIII e XLI, do artigo 6º da Lei nº 14.133/2021.
    </p>

    <p style="text-align: center; font-weight: bold;">
        CONCLUSÃO
    </p>
    <p style="text-indent: 30px; text-align: justify;">
        Ante a todo o exposto, e com fundamento no artigo 53 da Lei nº 14.133/2021,
        especialmente quanto às minutas apresentadas, verifica-se a devida obediência aos ditames da
        NLLC, razão pela qual conclui-se pela aprovação e opina se pelo prosseguimento do processo, com
        a observância desde já das publicações e do prazo mínimo de 08 (oito) dias úteis para a abertura da
        sessão pública, conforme determinado pelo artigo 55, inciso I, alínea “a” da Lei nº14.133/2021.
    </p>
    <p style="text-indent: 30px; text-align: justify;">
        Em tempo, recomenda-se também que os autos sejam submetidos à Controladoria Geral do
        Município, pois este tem como objetivo principal a ação preventiva, ou seja, antes que ações ilícitas,
        incorretas ou impróprias possam atentar contra os princípios da Constituição da Republica
        Federativa do Brasil, principalmente quanto ao previsto no artigo 37 em seus parágrafos e incisos.
    </p>
    <p style="text-indent: 30px; text-align: justify;">
        São os termos do parecer, reitera-se, meramente opinativo e orientador, que submetemos à
        decisão superior hierárquica.
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

    @endif

</body>

</html>
