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

    @php
        $dataDfd = $processo->documentos->where('tipo_documento', 'formalizacao')->first();
        $dataParecer = $processo->documentos->where('tipo_documento', 'parecer_juridico')->first();
        $dataAdjudicacao = $processo->documentos->where('tipo_documento', 'termo_adjudicacao')->first();
    @endphp

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

    {{-- ====================================================================== --}}
    {{-- BLOCO 2: TERMO DE RECEBIMENTO --}}
    {{-- ====================================================================== --}}

    <div>
        <p style="text-align: center; font-weight: bold;">
            PARECER JURÍDICO
        </p>
        <p>
            Interessado: {{ $processo->prefeitura->nome }} <br>
            Assunto:{!! strip_tags($processo->objeto) !!}
        </p>
        
        <table style="width:100%; table-layout:fixed; border-collapse:collapse;">
            <tr>
                <td style="width:40%; padding:8px; vertical-align:top; word-wrap:break-word; white-space:normal;">
                <!-- Conteúdo da primeira célula -->
                </td>
                <td style="width:60%; padding:8px; vertical-align:top; word-wrap:break-word; white-space:normal; font-weight: bold;">
                    PRINCÍPIO DA LEGALIDADE. EXAME DA
                    POSSIBILIDADE LEGAL DE CONTRATAÇÃO
                    DIRETA. DISPENSA DE LICITAÇÃO COM
                    FUNDAMENTO NO ARTIGO 75, INCISO I, DA
                    LEI Nº 14.133/2021. CONTROLE PREVENTIVO
                    DA LEGALIDADE, ARTIGO 53, §1º, INCISO I E
                    II C/C 72, INCISO III, DA LEI N° 14.133/2021.
                    CUMPRIMENTO DAS NORMAS E PRINCÍPIOS
                    NORTEADORES DA LICITAÇÃO.
                </td>
            </tr>
        </table>
        <h4>OBJETO DA CONSULTA</h4>
        <p style="text-align: justify;">
            Trata-se de solicitação exarada do Prefeito Municipal de {{ $processo->prefeitura->cidade }}, conforme
            requerimento do Secretário Municipal de {{ str_replace('Secretaria Municipal de ', '', $detalhe->unidade_setor ?? 'Unidade 1') }}, acerca da
            {!! strip_tags($processo->objeto) !!}, de acordo com os documentos que
            integram o processo administrativo {{ $processo->numero_processo }}, o qual requer o processamento de
            dispensa de licitação com fundamentos na Nova Lei de Licitações (Lei nº
            14.133/2021) 
        </p>
        <h4>MERITO DA CONSULTA</h4>
        <p style="text-align: justify;">
            Preambularmente é importante destacar que a submissão das dispensas de
            licitações, na Lei 14.133/2021, possui amparo, respectivamente, em seu artigo 53,
            §1º, inciso I e II c/c o artigo 72, inciso III, que assim dispõem:
        </p>

        <table style="width: 100%; border-collapse: collapse; page-break-inside: auto;">
            <tr style="page-break-after: auto;">
                <td style="width: 40%;"></td>
                <!-- Conteúdo da primeira célula -->
                </td>
                <td style="width: 50%; text-align: justify; vertical-align: top;">
                    “Art. 53. Ao final da fase preparatória, o
                    processo licitatório seguirá para o órgão de
                    assessoramento jurídico da Administração,
                    que realizará controle prévio de legalidade
                    mediante análise jurídica da contratação.
                    <br><br>
                </td>
            </tr>
            <tr>
                <td></td>
                <td style="text-align: justify;">
                    §1º - Na elaboração do parecer jurídico, o
                    órgão de assessoramento jurídico da
                    Administração deverá: <br>
                    I - Apreciar o processo licitatório conforme
                    critérios objetivos prévios de atribuição de
                    prioridade; <br>
                    II - redigir sua manifestação em linguagem
                    simples e compreensível e de forma clara e
                    objetiva, com apreciação de todos os
                    elementos indispensáveis à contratação e
                    com exposição dos pressupostos de fato e
                    de direito levados em consideração na
                    análise jurídica. ” 
                    <br><br>
                </td>
            </tr>
             <tr>
                <td></td>
                <td style="text-align: justify;">
                    “Art. 72. O processo de contratação direta,
                    que compreende os casos de inexigibilidade
                    e de dispensa de licitação, deverá ser
                    instruído com os seguintes documentos:
                    <br><br>
                </td>
            </tr>
             <tr>
                <td></td>
                <td style="text-align: justify;">
                    III - parecer jurídico e pareceres técnicos, se
                    for o caso, que demonstrem o atendimento
                    dos requisitos exigidos”
                </td>
            </tr>
        </table>

        <p style="text-indent: 30px; text-align: justify; ">
            Nesse sentido, a presente análise tem a finalidade de verificar a
            conformidade do procedimento, com as disposições fixadas na nova Lei de
            licitações, em especial no que tange a possibilidade legal de contratação direta dos
            serviços, tendo por fundamento o artigo 75, inciso I, da Lei nº 14.133/2021.
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Preliminarmente, cumpre esclarecer que, a presente manifestação
            limitar-se--á à dúvida estritamente jurídica “in abstrato”, ora proposta e, aos
            aspectos jurídicos da matéria, abstendo-se quanto aos aspectos técnicos,
            administrativos, econômico-financeiros e quanto a outras questões não ventiladas
            ou que exijam o exercício de conveniência e discricionariedade da Administração.
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Por essa razão, a emissão deste parecer não significa endosso ao
            mérito administrativo, tendo em vista que é relativo à área jurídica, não adentrando
            à competência técnica da Administração, em atendimento à recomendação da
            Consultoria Geral da União, por meio das Boas Práticas Consultivas – BCP nº 07,
            qual seja:
        </p>

        <table style="width: 100%; border-collapse: collapse; page-break-inside: auto;">
            <tr style="page-break-after: auto;">
                <td style="width: 40%;"></td>
                <!-- Conteúdo da primeira célula -->
                </td>
                <td style="width: 50%; text-align: justify; vertical-align: top;">
                    O Órgão Consultivo não deve emitir
                    manifestações conclusivas sobre temas não
                    jurídicos, tais como os técnicos,
                    administrativos ou de conveniência ou
                    oportunidade, sem prejuízo da possibilidade
                    de emitir opinião ou fazer recomendações
                    sobre tais questões, apontando tratar-se de
                    juízo discricionário, se aplicável. Ademais, caso
                    adentre em questão jurídica que possa ter
                    reflexo significativo em aspecto técnico deve
                    apontar e esclarecer qual a situação jurídica
                    existente que autoriza sua manifestação
                    naquele ponto.
                    <br><br>
                </td>
            </tr>
        </table>

        <p style="text-indent: 30px; text-align: justify;">
            A norma citada acima é fundamental para assegurar a correta
            aplicação do princípio da legalidade, para que os atos administrativos não
            contenham estipulações que contravenham à lei, posto que, o preceito da
            legalidade é, singularmente, relevante nos atos administrativos
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Assim, se faz necessário o exame prévio, para que a Administração
            não se sujeite a violar um princípio de direito, o que é severamente tão grave como
            transgredir uma norma. uma norma.
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Por esse motivo, a Constituição Federal em seu artigo 37 estabelece
            que, a Administração Pública observará os Princípios da Legalidade,
            Impessoalidade, Moralidade, Publicidade e Eficiência
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Já no que tange a inafastabilidade do procedimento licitatório, o
            inciso XXI do artigo retro mencionado assevera que, ressalvados os casos
            especificados na legislação, as obras, serviços, compras e alienações serão
            contratados mediante processo de licitação pública que assegure igualdade de
            condições a todos os concorrentes, com cláusulas que estabeleçam obrigações de
            pagamento, mantidas as condições efetivas da proposta, nos termos da lei, o qual
            somente permitirá as exigências de qualificação técnica e econômica,
            indispensáveis à garantia do cumprimento das obrigações.
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Conforme despacho de solicitação e autorização do Secretário de
            {{ str_replace('Secretaria Municipal de ', '', $detalhe->unidade_setor ?? 'Unidade 1') }}, e considerando que o serviço requisitado é temático à atividade fim da
            referida Secretaria, faz-se necessário a realização {!! strip_tags($processo->objeto) !!} trazendo dessa forma a prestação de
            serviços públicos à população.
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Devidamente instruído, o processo fora remetido a Assessoria
            Jurídica, para emissão de parecer acerca da legalidade do procedimento,
            objetivando a contratação direta de empresa para a execução do serviço ora
            solicitado
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Desta feita, como previsto na norma superior, a realização do certame
            é a regra, contudo, a própria lei de licitações prevê situações em que é mais
            vantajoso para a Administração, a formalização da contratação direta, ou seja, sem
            que haja a necessidade do procedimento licitatório.
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Assim, conforme previsão do Artigo 75, I, da Lei 14.133/2021 (Nova
            Lei de Licitações), (Vide Decreto nº 12.343, de 2024) Vigência, trouxe em seu texto a
            possibilidade de realizar dispensa de licitações para contratação que envolva
            valores inferiores a R$ 100.000,00 (cem mil reais), no caso de obras e serviços de
            engenharia ou de serviços de manutenção de veículos automotores;
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Com efeito, conforme previsto na norma retrocitada, os critérios se
            aplicam no caso em tela, uma vez que, consoante disposto no Artigo 75, inciso I, da
            Nova Lei de Licitações e Contratos (Lei nº 14.133/2021) (Vide Decreto nº 12.343, de
            2024), é autorizado e está em harmonia com a lei a contratação direta de obras e
            serviços de engenharia ou de serviços de manutenção de veículos automotores,
            cujo valor seja de até R$ 100.000,00 (cem mil reais), valor este reajustado
            anualmente pelo decreto 12.343, de 2024, para o exercício 2025 no valor de R$ R$
            125.451,15 (cento e vinte e cinco mil quatrocentos e cinquenta e um reais e quinze
            centavos).
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Todavia, faz-se necessário transcrever o artigo alhures, que assim dispõe:
        </p>

        <table style="width: 100%; border-collapse: collapse; page-break-inside: auto;">
            <tr style="page-break-after: auto;">
                <td style="width: 40%;"></td>
                <!-- Conteúdo da primeira célula -->
                </td>
                <td style="width: 50%; text-align: justify; vertical-align: top;">
                    Art. 75. É dispensável a licitação: I - para
                    contratação que envolva valores inferiores a
                    R$ 100.000,00 (cem mil reais), no caso de
                    obras e serviços de engenharia ou de serviços
                    de manutenção de veículos automotores; (Vide
                    Decreto nº 12.343, de 2024) Vigência
                    <br><br>
                </td>
            </tr>
        </table>

        <p style="text-indent: 30px; text-align: justify;">
            Assim, é preponderante caminhar, doravante, na linha da
            possibilidade de contratação direta dos serviços, desde que, o valor dispendido no
            exercício financeiro em curso, para custear a despesa, não seja superior a
            cinquenta mil reais
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Nessa vereda, e seguindo a recomendação contida na nova lei de
            licitações, no sentido de que os pareceres jurídicos devam ser redigidos em
            linguagem simples e compreensível e de forma clara e objetiva, com apreciação de
            todos os elementos indispensáveis à contratação e com exposição dos
            pressupostos de fato e de direito levados em consideração na análise jurídica,
            entendo ser perfeitamente possível a contratação direta dos serviços, através de
            dispensa de licitação, com fundamento na Nova Lei de Licitações, desde que
            observados os requisitos fixados no artigo 72, da Lei nº 14.133/21 a saber:
        </p>

       <table style="width: 100%; border-collapse: collapse; page-break-inside: auto;">
            <tr style="page-break-after: auto;">
                <td style="width: 40%;"></td>
                <!-- Conteúdo da primeira célula -->
                </td>
                <td style="width: 50%; text-align: justify; vertical-align: top;">
                    “Art. 72. O processo de contratação direta, que
                    compreende os casos de inexigibilidade e de
                    dispensa de licitação, deverá ser instruído com
                    os seguintes documentos: 
                    <br><br>
                <td>
            </tr>
            <tr>
                <td></td>
                <td>
                    I - Documento de formalização de demanda e,
                    se for o caso, estudo técnico preliminar, análise
                    de riscos, Projeto Básico, projeto básico ou
                    projeto executivo; <br>
                    II - Estimativa de despesa, que deverá ser
                    calculada na forma estabelecida no art. 23
                    desta Lei;
                    <br><br>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    III - parecer jurídico e pareceres técnicos, se for
                    o caso, que demonstrem o atendimento dos
                    requisitos exigidos”. 
                    <br><br> 
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                   IV - Demonstração da compatibilidade da
                    previsão de recursos orçamentários com o
                    compromisso a ser assumido; <br>
                    V - Comprovação de que o contratado
                    preenche os requisitos de habilitação e
                    qualificação mínima necessária;
                    <br><br>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    VI - Razão da escolha do contratado;
                    <br><br>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    VII - justificativa de preço;
                    <br><br>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    VIII - autorização da autoridade competente
                    <br><br>
                </td>
            </tr>
        </table>

        <p style="text-indent: 30px; text-align: justify;">
            Inclusive cumpre recomendar também que, o ato que autorizar a
            contratação direta ou o extrato decorrente do contrato deverá ser divulgado e
            mantido à disposição do público em sítio eletrônico oficial, bem como ser divulgado
            no Diário Oficial dos Municípios por força do disposto no artigo 176, inciso I, da
            nova Lei de Licitações
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Por conseguinte, pode-se afirmar que, dentro das regras dos valores
            estabelecidos pela legislação vigente, não há qualquer óbice quanto à pretensão.
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Destaca-se, ainda, que nos autos constam os documentos de
            formalização de demanda e Projeto Básico, contendo os elementos necessários e
            suficientes, com nível de precisão adequado, para caracterizar o objeto requisitado.
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Consta, ainda, estimativa da despesa, mediante pesquisa direta com
            3 (três) fornecedores, através de solicitação formal de cotação e justificativa pela
            não utilização de pesquisa de preço em bancos de dados públicos.
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Nota-se, ainda, que o valor a ser contratado está dentro do limite
            previsto na Nova Lei, e a realização de procedimento licitatório específico oneraria
            ainda mais os cofres públicos, haja vista que demandaria a utilização de pessoas,
            tempo e material para sua conclusão.
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Considerando que os serviços a serem realizadas estão estimadas em 
             R$ {{ $processo->finalizacao->valor_total }}. é forçoso concluir pela
            possibilidade legal de contratação direta, através de dispensa de licitação, uma vez
            que, o caso em questão, se amolda perfeitamente nos valores previstos no Artigo
            75, inciso I, da Lei nº 14.133/2021
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Da análise do dispositivo acima, pode-se chegar a uma conclusão
            fundamental no sentido de que, ao estabelecer a licitação como regra, o legislador
            buscou garantir que a licitação alcançasse suas finalidades essenciais, quais sejam,
            igualdade de tratamento entre os diversos interessados em contratar com a
            administração pública, somada à possibilidade de escolher dentre as ofertas
            apresentadas, aquela que for mais vantajosa ao interesse público.
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Na linha de raciocínio aqui sufragada, constata-se que, para haver
            respaldo legal, a contratação direta deve se basear em justificativas. A justificativa
            de Dispensa de Licitação para a contratação dos referidos serviços se funda no
            inciso I, do artigo 75, da Lei 14.133/2021.
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Como já citado acima, o intuito da dispensa de licitação é dar
            celeridade às contratações indispensáveis para restabelecer a normalidade. Além
            disso, a contratação direta não significa burlar aos princípios administrativos, pois a
            Lei exige que o contrato somente seja celebrado, após procedimento simplificado
            de concorrência, suficiente para justificar a escolha do contratado, de modo a
            garantir uma disputa entre potenciais fornecedores. 
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Entretanto, conforme previsto no artigo 75, § 3º, da Nova Lei, as
            contratações diretas, pelo valor, serão preferencialmente precedidas de divulgação
            de aviso em sítio eletrônico oficial, pelo prazo mínimo de 3 (três) dias úteis, com a
            especificação do objeto pretendido e com a manifestação de interesse da
            Administração em obter propostas adicionais de eventuais interessados, devendo
            ser selecionada a proposta mais vantajosa.
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Assim, para obter preços mais vantajosos dos serviços requisitados,
            faz-se necessário que a Administração dê publicidade à intenção de realizar
            contratação com a divulgação de aviso em sítio eletrônico oficial, pelo prazo
            mínimo de 3 (três) dias úteis.
        </p>
        <h4>DA PUBLICIDADE DOS ATOS NO PCNP</h4>
        <p style="text-indent: 30px; text-align: justify;">
            Diante da sanção da Lei de Licitações de nº 14.133/2021, uma questão
            jurídica de grande relevância veio à tona, e que pode produzir importantes
            impactos na Administração Pública brasileira, que é: a aplicação da Lei nº
            14.133/2021 (nova lei de licitações) depende da criação do Portal Nacional de
            Contratações Públicas?
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            É cediço que o Portal Nacional de Contratações Públicas (PNCP) é sítio
            eletrônico oficial destinado à: I – divulgação centralizada e obrigatória dos atos
            exigidos por esta Lei; II – realização facultativa das contratações pelos órgãos e
            entidades dos Poderes Executivo, Legislativo e Judiciário de todos os entes
            federativos, conforme disposto no artigo 174, da Nova Lei.
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Percebemos que a Nova Lei se trata de norma geral, aplicável, por
            disposição expressa normativa, para todos os entes federados.
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Vale ressaltar que conforme disposto no §1º, do artigo 174, o PNCP
            será gerido pelo Comitê Gestor da Rede Nacional de Contratações Públicas, que
            conta com a participação de representantes de todos os entes da Federação.
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Desse modo, podemos concluir que, com a sanção da Lei nº
            14.133/2021, o veículo oficial de divulgação dos atos relativos às licitações e
            contratações públicas passa a ser o Portal Nacional de Contratações Públicas.
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Para reforçar esse entendimento, transcrevo aqui, dentre outras
            referências, dois dispositivos da citada norma versando sobre a publicidade dos
            atos licitatórios e contratuais no PNCP. Primeira está contida no artigo 54, que
            assim dispõe:
        </p>

        <table style="width: 100%; border-collapse: collapse; page-break-inside: auto;">
            <tr style="page-break-after: auto;">
                <td style="width: 40%;"></td>
                <!-- Conteúdo da primeira célula -->
                </td>
                <td style="width: 50%; text-align: justify; vertical-align: top;">
                    “Art. 54. A publicidade do edital de licitação
                    será realizada mediante divulgação e
                    manutenção do inteiro teor do ato
                    convocatório e de seus anexos no Portal
                    Nacional de Contratações Públicas (PNCP)”
                </td>
            </tr>
        </table>

        <p style="text-indent: 30px;">
            Já a segunda, está no artigo 94. Vejamos:
        </p>

        <table style="width: 100%; border-collapse: collapse; page-break-inside: auto;">
            <tr style="page-break-after: auto;">
                <td style="width: 40%;"></td>
                <!-- Conteúdo da primeira célula -->
                </td>
                <td style="width: 50%; text-align: justify; vertical-align: top;">
                    “Art. 94. A divulgação no Portal Nacional de
                    Contratações Públicas (PNCP) é condição
                    indispensável para a eficácia do contrato e de
                    seus aditamentos e deverá ocorrer nos
                    seguintes prazos, contados da data de sua
                    assinatura: I – 20 (vinte) dias úteis, no caso de
                    licitação; II – 10 (dez) dias úteis, no caso de
                    contratação direta. § 1º Os contratos
                    celebrados em caso de urgência terão eficácia
                    a partir de sua assinatura e deverão ser
                    publicados nos prazos previstos nos incisos I e
                    II do caput deste artigo, sob pena de nulidade.
                    Referidas normas podem induzir a 2
                    conclusões distintas, ambas, claro,
                    defensáveis, afinal, interpretação implica a
                    busca do melhor significado, dentre os vários
                    possíveis, de um determinado texto
                    normativo”.
                </td>
            </tr>
        </table>

        <p style="text-indent: 30px; text-align: justify;">
            Diante disso, se fizermos uma interpretação literal das normas pode,
            com efeito, levar à conclusão hermenêutica no sentido de que a Nova Lei só poderá
            ser aplicada após a criação do Portal Nacional de Contratações Públicas, haja vista
            que a publicidade dos editais de licitação deve ser feita no Portal, e a publicação do
            extrato do contrato no Portal é condição de sua eficácia.
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Porém, no nosso entendimento, essa não parece ser a melhor
            interpretação, pois, conforme previsto no artigo 194, a Nova Lei de Licitações entra
            em vigor na data de sua publicação, o que ocorreu no dia 1º de abril de 2021.
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Ademais, a eficácia de uma norma somente pode ser limitada ou
            contida mediante disposição expressa – ou, como defendem alguns, no mínimo
            implícita, o que não foi previsto na Lei.
        </p>
        <p style="text-indent: 30px; text-align: justify;">                
            Por fim, entendo que não parece atender o interesse público vincular
            a eficácia de uma lei à implementação de um banco de dados, a menos que o objeto
            da lei fosse unicamente a criação do referido Banco de Dados, ou que a sua
            aplicação dependesse materialmente dele – o que não é o caso.
        </p>
        <p style="text-indent: 30px; text-align: justify;">                
            Tem-se, assim, que a Lei nº 14.133/2021 é válida, vigente e eficaz, à
            exceção de eventuais normas que dependam de regulamentação, o que irá
            demandar indicação expressa. Então, se a Lei está vigente, portanto, ela pode ser
            aplicada. 
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Ademais, a própria Lei estabelece que “até o decurso do prazo de que
            trata o inciso II, do caput do artigo 193, a Administração poderá optar por licitar ou
            contratar diretamente de acordo com esta Lei ou de acordo com as Leis citadas no
            referido inciso, e a opção escolhida deverá ser indicada expressamente no edital ou
            no aviso ou instrumento de contratação direta, vedada a aplicação combinada
            desta Lei com as citadas no referido inciso” (art. 191)
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Veja que o legislador, em momento algum, vinculou a vigência da Lei
            à criação do Portal Nacional de Contratações Públicas, o que pode levar a outra
            conclusão no que tange à aplicabilidade imediata da Lei nº 14.133/2021.
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            E esta outra conclusão decorre de uma interpretação sistemática ou
            sistêmica das normas contidas na Nova Lei de Licitações. 
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Partindo-se da premissa de que a Lei tem vigência, e tem, como visto,
            e de que não se pode admitir eficácia contida ou limitada de nenhuma de suas
            normas sem expressa previsão também legal – ainda que implícita -, é possível
            deduzir conclusão no sentido da possibilidade de aplicação imediata do regime
            jurídico da Lei nº 14.133/2021. 
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            O primeiro argumento em favor da eficácia imediata da Lei nova tem
            relação com a função do Portal Nacional de Contratações Públicas. Trata-se de um
            banco de dados que conterá informações relevantes e indispensáveis sobre
            licitações e contratações públicas
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Será, também como visto, o veículo oficial de publicidade dos atos
            relativos às licitações e contratos da Administração Pública – à exceção das
            empresas estatais.
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Ora, esta função pode ser suprida, sem qualquer prejuízo de
            publicidade, pelo sistema de publicidade oficial dos atos administrativos já
            utilizados pelo Município, normalmente, a publicação em Diário Oficial, jornal de
            grande circulação, Portal da Transparência e endereço eletrônico oficial do
            Município. A publicidade dos atos relativos a licitações e contratos pode e deve
            ocorrer também por meio dos sítios eletrônicos oficiais – para conferir eficiência às
            publicações.
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Diante disso, entendo que o relevante e de interesse público é que
            ocorra efetivamente a publicação dos instrumentos convocatórios e dos extratos
            dos contratos, cumprindo dessa forma o princípio constitucional da publicidade.
            Nem se diga que esta sistemática ensejará prejuízos ou riscos de publicidade, pois é
            a sistemática de que se vale a Administração Pública com fundamento na Lei
            revoganda de nº 8.666/1993. 
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Nesse entendimento, diante da a interpretação sistemática das
            normas que exigem a publicação no Portal Nacional de Contratações Públicas,
            chego à conclusão de que: enquanto não for criado referido portal, a publicidade
            dos atos e contratos se dará por intermédio dos veículos oficiais de publicação e
            sítios eletrônicos dos entes e órgãos da Administração Pública; e a publicação no
            Portal somente será condição para eficácia dos contratos após a sua efetiva criação.
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Outrossim, podemos ainda balizar o nosso entendimento para
            aplicação imediata da Lei trazendo à tona o argumento lógico-jurídico, ou seja, não
            há sentido jurídico em vincular a vigência e a eficácia de uma Lei à criação de um
            banco de dados informatizado, que se presta a uma finalidade – conferir
            publicidade aos atos – que pode ser atingida por outros meios jurídicos legítimos e
            válidos. 
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Por hipótese, imagine-se que, transcorridos os 2 anos de que trata o
            artigo 193, inciso II, da Nova Lei tenhamos a revogação da Lei nº 8.666/1993, mas
            ainda não tenhamos um Portal Nacional de Contratações Públicas, neste caso,
            lamentavelmente, não poderemos mais realizar licitações ou contratações
            públicas, pois não haverá Lei vigente ou eficaz, para, nos estreitos limites da
            legalidade administrativa, amparar a Administração Pública, porque não foi criado
            um banco de dados informatizado
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Assim, concluímos que o a Lei de Licitações está plenamente válida e
            eficaz, podendo ser utilizada no caso contrato. 
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Antes de finalizar, compete ressaltar que, o parecer aqui exarado não
            contempla as hipóteses de fracionamento da despesa, cabendo ao gestor a adoção
            das medidas administrativas necessárias para evitar o fracionamento da despesa
            através de contratações formalizadas por dispensa de licitação, pois tal conduta
            além de ilegal caracterizará afronta as normas e princípios que norteiam a licitação
        </p>
        
        <h4>CONCLUSÃO</h4>

        <p style="text-indent: 30px; text-align: justify;">
            Antes de concluir, é importante esclarecer que, apoiado nos sábios
            ensinamentos do doutrinador HELY LOPES MEIRELLES, todas as considerações aqui
            expostas, trata-se de uma opinião técnica, de caráter meramente opinativo, não
            vinculando a Administração ou aos particulares à sua motivação ou conclusões,
            salvo se aprovado por ato subsequente. 
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            No caso de Dispensa de Licitação, a legislação não impõe regras
            objetivas quanto à quantidade de empresas chamadas a apresentarem propostas e
            a forma de seleção da contratada, mas determina que essa escolha seja justificada
            (artigo 26, parágrafo único, da Lei 14.133/2021). Acórdão 2186/2019 TCU Plenário.
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Por conseguinte, consoante sedimentado na jurisprudência do
            Tribunal de Contas da União e no Artigo 72, incisos VI e VII, o processo de Dispensa
            deverá ser instruído com elementos que demonstrem a razão da escolha do
            fornecedor ou executante e a justificativa do preço, não impondo de forma objetiva
            as regras quanto à quantidade e a forma de seleção do contratado, ou seja, deve
            ser justificado no processo a escolha do fornecedor. 
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Por fim, recomendo a Comissão Permanente de Licitação que sempre
            análise toda a documentação necessária para verificação da regularidade fiscal e
            trabalhista. Assim, observadas as prescrições suscitadas acima, vislumbro de plano
            a existência de autorização legal para contratação direta dos serviços.
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Sendo assim, a celebração do contrato não afronta os princípios
            reguladores da Administração Pública, e neste caso é absolutamente possível a
            contratação na forma prevista no artigo 75, inciso I, da Lei nº 14.133/2021. Dessa
            forma, observadas as prescrições exaradas nesse parecer, opino favoravelmente
            pela possibilidade de contratação direta dos serviços.
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Este é o parecer jurídico, o qual submeto à apreciação e quaisquer
            considerações das autoridades competentes.
        </p>

        {{-- Bloco de data e assinatura --}}
        <div class="footer-signature">
            {{ $processo->prefeitura->cidade }},
            {{ \Carbon\Carbon::parse($dataSelecionada)->translatedFormat('d \d\e F \d\e Y') }}
        </div>

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
    </div>
    {{-- QUEBRA DE PÁGINA --}}
    <div class="page-break"></div>
    <div>
        <h4>DESPACHO</h4>
        <p>
            Ao(À) Ilmo(a). Sr(a).<br>
            <span>{{ $processo->prefeitura->autoridade_competente }}</span>
            <br>
            Prefeito Municipal
        </p>

        <p style="text-align: justify;">
            Assunto: Encaminhamento Parecer Jurídico acerca de Processo de Dispensa de Licitação
        </p>
        <p style="text-indent: 30px; text-align: justify; ">
            Senhor(a) Prefeito,
        </p>
        <p style="text-indent: 30px; text-align: justify; ">
            Encaminho ao Exm. Senhor(a) o Parecer Jurídico acerca do Processo
            de Dispensa de Licitação nº{{ $processo->numero_procedimento }}, 
            objeto {!! strip_tags($processo->objeto) !!}, para a devida contiuidade do mesmo.
        </p>

        {{-- Bloco de data e assinatura --}}
        <div class="footer-signature">
            {{ $processo->prefeitura->cidade }},
            {{ \Carbon\Carbon::parse($dataSelecionada)->translatedFormat('d \d\e F \d\e Y') }}
        </div>

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
    </div>
</body>

</html>
