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
    ->map(fn($item) => $item === 'outro' ? ($detalhe->prazo_vigencia_outro ?? '________________.') : ($opcoes_vigencia[$item] ?? ucfirst(str_replace('_', ' ', $item))))
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
                <div class="title">TERMO DE REFERÊNCIA</div>
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

                <!-- Prazo virgencia -->
                <div class="section">
                    <table>
                        <tr>
                            <td class="icon">
                                <img src="{{ public_path('icons/alerta.png') }}" width="40">
                            </td>
                            <td class="content">
                                <div style=" font-weight: bold; margin-bottom: 3px;">PRAZO DE VIGÊNCIA DA CONTRATAÇÃO</div>
                                <div style="">
                                    O prazo de vigência da contratação é de {{ $vigencia_formatada }} contados do(a) assinatura do
                                    Contrato, na forma do artigo 105 da Lei n° 14.133, de 2021
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
            1.1. O presente Termo de Referência tem como finalidade a {!! strip_tags($processo->objeto) !!}
        </p>
        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 2. JUSTIFICATIVA
        </p>
        <p style="text-align: justify;">
            {!! strip_tags($detalhe->justificativa) !!}
        </p>

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 3. ENQUADRAMENTO LEGAL
        </p>
        <p style="text-align: justify;">
            3.1. O presente termo de referência tem como base legai a Lei Federal 14.133/2021
            (Nova Lei de Licitações), especificamente seu artigo art. 74, inciso II.
            <br>
            3.2. O procedimento observado obedece ao disposto no artigo 72, incisos I a VIII, bem
            como o Decreto Municipal.
            <br>
            3.3. Nas palavras do ilustre professor Ronny Charles: “Quando a lei prevê hipóteses de
            contratação direta (dispensa e inexigibilidade) é porque admite que nem sempre a
            realização do certame levará à melhor forma de contratação pela Administração ou que,
            pelo menos, a sujeição do negócio ao procedimento formal e burocrático previsto pelo
            estatuto não serve eficaz ao atendimento do interesse público naquela hipótese
            específica.” 
            <br>
            3.4. Nesse mesmo sentido, o nobre doutrinador Adilson Abreu Dallari destaca que: “Nem
            sempre, é verdade, a licitação leva uma contratação mais vantajosa. Não pode ocorrer,
            em virtude da realização do procedimento licitatório, é o sacrifício de outros valores e
            princípios consagrados pela ordem jurídica, especialmente o princípio da eficiência.”
            <br>
            3.5. No presente caso, a inexigibilidade de licitação torna-se mais viável ao procedimento
            licitatório, porém deve ser pormenorizada em um procedimento formal, não sendo
            afastado nenhuma das premissas básicas de um procedimento licitatório, como a busca
            pelo melhor atendimento à finalidade pública e respeito a princípios basilares como a
            impessoalidade, moralidade, publicidade dentre outros;
            <br>
            3.6. A contratação, via inexigibilidade de licitação, em razão da inviabilidade de
            competição para a contratação de profissional do setor artístico, diretamente ou por meio
            de empresário exclusivo, desde que consagrado pela crítica especializada ou pela
            opinião pública, à realização do processo licitatório, além de tornar mais célere e eficiente
            a contratação, que visa à consecução do interesse público.
            <br>
            3.7. Ainda, a modalidade de contratação é definida por tratar-se de serviço artístico com
            natureza singular, o qual não se sujeita a critérios objetivos de julgamento ou competição.
            A adoção de procedimento licitatório, além de impraticável, comprometeria os objetivos
            públicos propostos.
        </p>

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 4. APRESENTAÇÃO DA PROPOSTA DE PREÇOS
        </p>
        <p style="text-align: justify;">
            4.1. Para fins de instrução do processo de contratação direta por inexigibilidade, a
            proposta de preço deverá ser apresentada pelo empresário exclusivo ou representante
            legal do artista, contendo obrigatoriamente os seguintes elementos:
            <br>
            <span style="margin-left: 20px;">
                a) Nome; Endereço; CNPJ; Inscrição Estadual/Municipal.
                <br>
                b) Valor global da apresentação artística a ser realizada, em moeda corrente
                nacional, expresso em algarismos e por extenso, considerando o custo total para
                execução do serviço, incluindo todas as despesas envolvidas, tais como cachê artístico,
                deslocamentos, transporte de equipamentos e equipe técnica, hospedagem e
                alimentação, se for o caso, impostos, tributos e encargos, montagem e desmontagem, se
                incluídas no escopo, e demais custos diretos ou indiretos relacionados à execução da
                apresentação.
                <br>
                c) Prazo de validade da proposta não poderá ser inferior a 60 (sessenta) dias.
                <br>
                d) A proposta que omitir o prazo de validade será considerada como válida pelo
                período de 60 (sessenta) dias.
                <br>
                e) O preço apresentado deverá estar compatível com os valores praticados no
                mercado artístico para serviços de mesma natureza e porte.
                <br>
                f) A proposta deverá conter a assinatura do responsável legal e, quando aplicável,
                estar acompanhada da documentação comprobatória de representação exclusiva do
                artista, com validade nacional.
                <br>
                g) O preço cotado permanecerá fixo e irreajustável pelo período do contrato, exceto
                quando confirmado motivo justo para revisão ou atualização, na forma que determina a
                legislação.
            </span>
        </p>

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 5. DA ESPECIFICAÇÃO DOS SERVIÇOS
        </p>
        <p style="text-align: justify;">
            {!! strip_tags($processo->detalhe->especificacao_servicos_imovel) !!}
        </p>

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 6. DO REGIME DE EXECUÇÃO
        </p>
        <p style="text-align: justify;">
            6.1. O serviço contratado será realizado por execução indireta;
            <br>
            6.2. A execução do objeto seguirá a seguinte dinâmica, sendo de inteira responsabilidade
            da contratada a realização das atividades abaixo relacionadas:
            <br>
            <span style="margin-left: 20px;">
                a) Os serviços contratados, além da execução de trabalhos técnicos e profissionais
                específicos, compreendem, a disponibilização de serviços especializados de natureza
                artística; visando o desenvolvimento cultural com vistas ao atingimento de metas de
                eficiência, eficácia e qualidade nas atividades institucionais do Órgão, bem como do
                atendimento das exigências e obrigações constantes da legislação governamental
                vigente;
                <br>
                b) Deverão ser disponibilizados canais de comunicação por parte da Contratada
                através de telefones fixo e móvel, fax, e-mail e outras formas de tecnologia disponíveis;
                <br>
                c) A contratação não envolve a disponibilização de quaisquer tipos de equipamentos
                ou aplicativos, necessários às atividades operacionais de ambas as partes;
                <br>
                d) Os trabalhos específicos desdobram-se nos itens a seguir discriminados.
                <br>
            </span>
            6.3. A CONTRATADA deverá executar o serviço utilizando-se dos materiais e
            equipamentos necessários à perfeita execução dos serviços a serem prestados;
            <br>
            6.4. Não será necessária a utilização de uniforme pela contratada, no entanto os
            funcionários deverão estar identificados no local de prestação de serviço;
        </p>

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 7. DA DESCRIÇÃO DA SOLUÇÃO
        </p>
        <p style="text-align: justify;">
            7.1. A presente solução contempla a contratação direta, por meio de inexigibilidade de
            licitação, para {!! strip_tags($processo->objeto) !!}
            <br>
            7.2. A contratação visa atender à demanda cultural da Administração Pública Municipal,
            promovendo o acesso da população a atividades artísticas de relevância e assegurando
            a qualidade e a atratividade do evento, com o objetivo de fomentar a cultura, o lazer, a
            economia local e a valorização das tradições populares, alinhando-se às diretrizes de
            interesse público.
            <br>
            7.3. A apresentação deverá ser executada com responsabilidade e profissionalismo,
            conforme especificações técnicas estabelecidas no Termo de Referência e no rider
            técnico apresentado pela contratada. A contratada deverá observar integralmente as
            condições pactuadas, sendo responsável por todos os aspectos operacionais
            diretamente vinculados à execução da apresentação, bem como por sua equipe técnica,
            instrumentos, logística e demais elementos necessários.
        </p>

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 8. DA VIGÊNCIA
        </p>
        <p style="text-align: justify;">
            8.1. O período de vigência do instrumento contratual será de {{ $vigencia_formatada }},
            contados da data de sua assinatura, podendo este ser rescindido ou ter seu prazo
            prorrogado na forma da Lei.
        </p>

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 9. REQUISITOS DA CONTRATAÇÃO
        </p>
        <p style="text-align: justify;">
            9.1. Para que o objeto da contratação seja atendido, é necessário o atendimento de
            alguns requisitos mínimos necessários, dentre eles os de qualidade e de regularidade da
            representação, nos termos do artigo 72, da Lei Federal 14.133/2021, observando-se a
            natureza singular do serviço e a inviabilidade de competição.
            <br>
            9.2. Por se tratar de contratação por inexigibilidade de licitação, fundamentada no artigo
            74, inciso II, da Lei nº 14.133/2021, a documentação será compatível com a modalidade
            de contratação e com a natureza do contratado (pessoa física ou jurídica), devendo ser
            apresentados os seguintes documentos:
            <br>
            a) Quando se tratar de contratação por meio de empresário exclusivo:
            <br>
            <span style="margin-left: 20px;">
                o Contrato social da empresa (todas as alterações ou última consolidação);<br>
                o Documento de Identificação dos sócios da empresa;<br>
                o Prova de inscrição no Cadastro Nacional da Pessoa Jurídica (CNPJ);<br>
                o Prova de inscrição no cadastro de contribuintes estadual e/ou municipal<br>
                o Regularidade perante a Fazenda Municipal;<br>
                o Regularidade perante a Fazenda Estadual;<br>
                o Regularidade perante a Fazenda Federal;<br>
                o Regularidade perante a Caixa Econômica Federal;<br>
                o Regularidade perante a Justiça do Trabalho;<br>
                o Contrato de exclusividade firmado entre o artista e o empresário, com validade
                nacional;<br>
                o Prova de consagração do artista pela crítica especializada ou pela opinião
                pública (matérias, registros de shows, mídias, redes sociais, premiações, etc.);
                <br>
            </span>
            b) Quando a contratação for realizada diretamente com o artista (pessoa física):
                <br>
            <span style="margin-left: 20px;">
                o Cópia do documento de identificação e CPF do contratado;<br>
                o Comprovante de endereço;<br>
                o Comprovação de consagração pública ou crítica especializada;<br>
                o Declaração de exclusividade ou autodeclaração de que o serviço será prestado
                de forma pessoal e direta;<br>
                o Declaração de que não possui impedimentos legais para contratar com o poder
                público;<br>
                o Certidão negativa de débitos federais;<br>
                o Certidão negativa da Justiça do Trabalho, se aplicável.<br>
            </span>
        </p>

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 10. MODELO DE GESTÃO DO CONTRATO
        </p>
        <p style="text-align: justify;">
            10.1. A fiscalização da contratação, decorrente desta inexigibilidade de licitação, será
            acompanhada e fiscalizada por servidor da Administração, especialmente designados,
            nos termos do artigo 117 da Lei Federal 14.133/2021.
            <br>
            10.2. A contratante deverá indiciar um responsável legal, através de documento
            encaminhado para o e-mail da prefeitura Municipal ou protocolado pessoalmente no
            setor de licitações e contratos deste município, indicando os respectivos contatos (e-mail,
            celular e Whatsapp), com poderes para representá-lo perante essa municipalidade na
            execução do contrato decorrente da inexigibilidade de licitação objeto deste termo de
            referência.
        </p>

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 11. DO RECEBIMENTO DO OBJETO E DOS CRITÉRIOS PARA MEDIÇÃO E PAGAMENTO
        </p>
        <p style="text-align: justify;">
            11.1. O objeto será recebido observando-se os seguintes procedimentos:
            <br>
            <span style="margin-left: 20px;">
                a) Recebimento Provisório (No dia do evento): Dar-se-á imediatamente após o término da
                apresentação, mediante atesto in loco pelo Fiscal do Contrato ou Comissão de
                Fiscalização, confirmando que: 
                <br>
                <span style="margin-left: 20px;">
                    o O artista/grupo contratado compareceu com a formação acordada;<br>
                    o O tempo de duração da apresentação cumpriu o estipulado no contrato;<br>
                    o O repertório ou conteúdo da apresentação foi compatível com o objeto
                    contratado. <br>
                    <br>
                </span>
                b) Recebimento Definitivo (Documental): Ocorrerá no prazo de até 10 dias úteis após a
                apresentação, mediante a análise e aprovação, pelo Fiscal do Contrato, do Relatório de
                Execução elaborado pela própria fiscalização, contendo as evidências da realização do
                evento.
            </span>
            11.1.1. O Recebimento Definitivo ficará condicionado à entrega de comprovação visual
            da realização do evento (fotos datadas e/ou vídeos da apresentação) que demonstrem
            inequivocamente a presença do artista no local e data estipulados.
            <br>
            11.2. A medição será realizada por evento/apresentação realizada, vedado o pagamento
            por horas ou frações não estipuladas.
            <br>
            11.3. O pagamento será efetuado conforme o estipulado em contrato, mediante a
            apresentação de Nota Fiscal/Fatura, discriminando o serviço artístico prestado, data e
            local.
        </p>

       <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 12. DOTAÇÃO ORÇAMENTÁRIA
        </p>
        <p style="text-align: justify;">
            12.1. Os custos com a presente contratação correrão por conta da seguinte dotação
            orçamentária:
        </p>
         <table style="border-collapse: collapse; width: 100%; border: 1px solid black;">
            <tr>
                <!-- Coluna da esquerda -->
                <td style="vertical-align: top; padding: 10px;">
                    {!! str_replace('<p>', '<p style="text-indent:30px; text-align: justify;">', $detalhe->dotacao_orcamentaria) !!}
                </td>
            </tr>
        </table>

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 13. FORMA E CRITÉRIO DE SELEÇÃO DO FORNECEDOR/PRESTADOR
        </p>
        <p style="text-align: justify;">
            13.1. A seleção do fornecedor dar-se-á por meio de Inexigibilidade de Licitação, com
            fundamento no art. 74, inciso II, da Lei Federal nº 14.133/2021, dada a inviabilidade de
            competição para a contratação de profissional do setor artístico, diretamente ou através
            de empresário exclusivo.
            <br>
            13.2. A seleção deste fornecedor específico justifica-se pela aderência e pertinência do
            estilo artístico ao conceito do evento, visando atender às expectativas da comunidade
            local e aos objetivos culturais da Administração, conforme detalhado no Estudo Técnico
            Preliminar (ETP).
        </p>

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 14. DA RAZÃO E ESCOLHA DO CONTRATADO
        </p>
        <p style="text-align: justify;">
            14.1. {!! strip_tags($processo->detalhe->razao_escolha_contratado) !!}
        </p>

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 15. DA JUSTIFICATIVA DOS PREÇOS:
        </p>
        <p style="text-align: justify;">
            15.1. No que diz respeito a JUSTIFICATIVA DE PREÇOS, em atendimento ao que
            preconiza o artigo 72, da Lei 14.133/2021 para elaboração do custo, deverá ser
            apresentado valores praticados nos mercados, através de contratações com objetos
            similares.
            <br>
            15.2. O contratado(a) apresentou notas fiscais e extratos de contratos de outros entes
            públicos, onde notadamente é similar ao valor proposto.
            <br>
            15.3. Sendo assim, declara-se que o preço praticado para a presente contratação é
            compatível com o mercado sendo considerado justo para esta Administração.
        </p>

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 16. OBRIGAÇÕES DO(A) CONTRATADO(A) 
        </p>
        <p style="text-align: justify;">
            16.1. O(A) CONTRATADO(A) obriga-se a: <br>
            16.1.1. Executar a apresentação artística conforme condições, prazos e especificações
            constantes neste Termo de Referência, no contrato e em sua proposta;<br>
            16.1.2. Comparecer ao local do evento com antecedência mínima acordada, observando
            a pontualidade e os requisitos logísticos estabelecidos pela Administração Pública;<br>
            16.1.3. Garantir a realização da apresentação com os artistas originalmente contratados,
            salvo motivo de força maior, devidamente justificado e aceito pela Administração;<br>
            16.1.4. Fornecer, quando solicitado, informações técnicas necessárias à realização da
            apresentação, tais como: ficha técnica, riders de som, mapa de palco, lista de
            necessidades e outras condições indispensáveis;<br>
            16.1.5. Arcar com quaisquer encargos trabalhistas, previdenciários, fiscais, securitários e
            outros decorrentes de sua atividade e da equipe envolvida na execução da apresentação,
            isentando a Administração Pública de qualquer responsabilidade solidária ou subsidiária<br>
            16.1.6. Responder por danos causados à Administração ou a terceiros em decorrência
            de ações ou omissões dolosas ou culposas no cumprimento do contrato;<br>
            16.1.7. Cumprir todas as normas de segurança, saúde e meio ambiente relativas ao
            evento;<br>
            16.1.8. Não subcontratar, total ou parcialmente, o objeto da contratação sem autorização
            prévia e expressa da Administração;<br>
            16.1.9. Manter, durante toda a execução contratual, as condições de habilitação exigidas,
            especialmente quanto à exclusividade e à consagração do artista ou grupo artístico.<br>
            16.2. O(A) CONTRATADO(A) cede à Administração, para fins institucionais e sem fins
            lucrativos, o direito de uso da imagem, som e nome artístico da atração, apenas para fins
            de divulgação do evento, respeitada a legislação aplicável sobre direitos autorais e de
            imagem.<br>
            16.3. Os direitos autorais da solução, do projeto, de suas especificações técnicas, da
            documentação produzida e congêneres, e de todos os demais produtos gerados na
            execução do contrato, inclusive aqueles produzidos por terceiros subcontratados, ficando
            proibida a sua utilização sem que exista autorização expressa da Contratante, sob pena
            de multa, sem prejuízo das sanções civis e penais cabíveis;<br>
            16.4. O não cumprimento das obrigações assumidas poderá implicar na aplicação das
            penalidades previstas na Lei nº 14.133/2021 e no contrato, sem prejuízo da rescisão
            contratual.<br>
            16.5. Os serviços serão executados pela CONTRATADA na forma descrita no Termo de
            Referência;<br>
            16.6. Os termos indicados na proposta vinculam a referida contratação;
        </p>
        {!! $processo->detalhe->obrigacoes_contratado_extras !!}

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 17. OBRIGAÇÕES DA CONTRATANTE
        </p>
        <p style="text-align: justify;">
            17.1. A CONTRATANTE obriga-se a:<br>
            17.1.1 Disponibilizar as condições técnicas, operacionais e logísticas necessárias para a
            plena execução da apresentação artística, conforme as especificações estabelecidas
            neste Termo de Referência e no contrato;<br>
            17.1.2. Prestar os esclarecimentos necessários à CONTRATADA quanto ao local, data e
            condições do evento, com a antecedência mínima necessária para viabilizar a
            preparação técnica da apresentação;<br>
            17.1.3. Garantir o acesso dos artistas e equipe técnica ao local da apresentação,
            inclusive em horários compatíveis com montagem, passagem de som e outras
            necessidades previstas pela CONTRATADA;<br>
            17.1.4. Fornecer as estruturas previamente pactuadas, como palco, som, iluminação,
            camarim, segurança, energia elétrica, pontos de acesso e demais itens técnicos
            acordados;<br>
            17.1.5. Proceder ao pagamento dos valores contratados, conforme estabelecido no
            contrato e nas condições da proposta aprovada, desde que cumpridas as exigências de
            documentação fiscal e contratual;<br>
            17.1.6. Designar servidor responsável pelo acompanhamento da execução contratual e,
            se necessário, por atestar a realização da apresentação para fins de liberação do
            pagamento.<br>
            17.2. Informar à CONTRATADA sobre eventuais alterações no cronograma ou na
            estrutura do evento com antecedência razoável, desde que possível, buscando evitar
            prejuízos à execução do objeto contratado.<br>
            17.3. Não intervir na gestão artística, técnica ou organizacional da apresentação,
            respeitando a autonomia criativa do artista e sua equipe, conforme o objeto contratado.<br>
            17.4. Adotar as medidas cabíveis, inclusive judiciais, em caso de inadimplemento
            injustificado por parte da CONTRATADA, nos termos da legislação vigente.
        </p>
        {!! $processo->detalhe->obrigacoes_contratante_extras !!}

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 18. SUBCONTRATAÇÃO
        </p>
        <p style="text-align: justify;">
            18.1. Não será admitida a subcontratação total do objeto licitatório;
        </p>

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 19. REAJUSTE
        </p>
        <p style="text-align: justify;">
            19.1 Os preços são fixos e irreajustáveis no prazo de um ano contado da data limite para
            a apresentação das propostas;<br>
            19.1.1 Dentro do prazo de vigência do contrato e mediante solicitação da contratada, os
            preços contratados poderão sofrer reajuste após o interregno de um ano, aplicando-se o
            índice IGPM exclusiva mente para as obrigações iniciadas e concluídas após a
            ocorrência da anualidade;<br>
            19.2. Nos reajustes subsequentes ao primeiro, o interregno mínimo de um ano será
            contado a partir dos efeitos financeiros do último reajuste;<br>
            19.3. No caso de atraso ou não divulgação do índice de reajustamento, o
            CONTRATANTE pagará à CONTRATADA a importância calculada pela última variação
            conhecida, liquidando a diferença correspondente, tão logo seja divulgado o índice
            definitivo. Fica a CONTRATADA obrigada a apresentar memória de cálculo referente ao
            reajustamento de preços do valor remanescente, sempre que este ocorrer;
            19.4. Nas aferições finais, o índice utilizado para reajuste será, obrigatoriamente, o
            definitivo;<br>
            19.5. Caso o índice estabelecido para reajustamento venha a ser extinto ou de qualquer
            forma não possa mais ser utilizado, será adotado, em substituição, o que vier a ser
            determinado pela legislação então em vigor;<br>
            19.6. Na ausência dê previsão legal quanto ao índice substituto, as partes elegerão novo
            índice oficial, para reajustamento do preço do valor remanescente, por meio de termo
            aditivo;<br>
            19.7. O reajuste será realizado por apostilamento;
        </p>

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 20. DAS SANÇÕES:
        </p>
        <p style="text-align: justify;">
            20.1. Pela inexecução total ou parcial do objeto deste contrato, a Administração pode
            aplicar à CONTRATADA, sanções previstas em lei, sempre respeitando com contraditório
            e ampla defesa.
        </p>

        <p
            style="text-align: center; font-size:12px; font-weight: 700; border: 1px solid black; padding: 10px; background:#dadada; margin-top:20px;">
            ENCAMINHAMENTO PARA ÓRGÃO DEMANDANTE
        </p>
        <div style="border: 1px solid black; padding: 10px;">
            <p style="line-height: 1.6">Em conformidade com a legislação aplicável, encaminhamos o Presente Estudo
                Despacho para a Autoridade Competente para a Autorização de Abertura de Procedimento de
                Licitação.
            </p>

            {{-- Bloco de data e assinatura --}}
            <div class="footer-signature">
                {{ $processo->prefeitura->cidade }},
                {{ \Carbon\Carbon::parse($dataSelecionada)->translatedFormat('d \d\e F \d\e Y') }}
            </div>
            <hr>

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
    </div>

</body>

</html>
