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
            1.1. O presente Termo de Referência tem como finalidade a{!! strip_tags($processo->objeto) !!}
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
            (Nova Lei de Licitações), especificamente seu artigo art. 74, inciso III, alínea “c”.
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
            competição para a contratação de serviço técnico especializado e de natureza
            predominantemente intelectual de empresa especializada com notória especialização à
            realização do processo licitatório, além de tornar mais célere e eficiente a contratação,
            que visa à consecução do interesse público.
            <br>
            3.7. Ainda, a modalidade de contratação é definida pela impossibilidade de adoção de
            critérios objetivos, a serem definidos num processo licitatório, posto que os serviços a
            serem prestados possuem natureza intelectual, sendo que a contratada possui traços
            próprios e únicos para a execução desse serviço.
        </p>

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 4. APRESENTAÇÃO DA PROPOSTA DE PREÇOS
        </p>
        <p style="text-align: justify;">
            4.1. A proposta de preço deverá conter os seguintes elementos:
            <br>
            <span style="margin-left: 20px;">
                a) Nome; Endereço; CNPJ; Inscrição Estadual/Municipal.
                <br>
                b) Deverá ser organizada por lote, descrevendo todos os preços por item de acordo
                com o objeto devendo a negociação ocorrer por menor preço por item, e ratificação por
                item embora a contratação possa ser por lote ou por itens do lote a fim de atender e
                otimizar o empenhamento das despesas em atendimento a necessidade pontual da
                contratante.
                <br>
                c) Prazo de validade da proposta não poderá ser inferior a 60 (sessenta) dias.
                <br>
                d) A proposta que omitir o prazo de validade será considerada como válida pelo
                período de 60 (sessenta) dias.
                <br>
                e) O valor a ser cotado deve levar em consideração o valor total da proposta, em
                moeda corrente nacional, algarismo e/ou por extenso, apurado à data de sua
                apresentação, sem inclusão de qualquer encargo financeiro que deve ser assumido pelo
                potencial contratado ou previsão inflacionária. Nos preços propostos deverão estar
                incluídos, além do lucro, todas as despesas e custos, como por exemplo: transportes,
                fretes, tributos de qualquer natureza e todas as despesas, diretas ou indiretas,
                relacionadas com o objeto da licitação.
                <br>
                f) As propostas deverão ser apresentadas contemplando os quantitativos fixados,
                conforme anexo I, não sendo permitidas ofertas com quantitativo inferior.
                <br>
                g) O licitante deverá demonstrar na sua proposta, quantidade, e demais informações
                a fim de viabilizar as requisições demandadas respeitadas a forma e condições
                estabelecida no Termo de Referência.
                <br>
                h) O preço cotado permanecerá fixo e irreajustável pelo período do contrato, exceto
                quando confirmado motivo justo para revisão ou atualização, na forma que determina a
                legislação.
            </span>
        </p>

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 5. DA ESPECIFICAÇÃO DOS SERVIÇOS
        </p>
        <p style="text-align: justify;">
            {{ $processo->detalhe->especificacao_servicos_imovel }}.
        </p>

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 6. DO REGIME DE EXECUÇÃO
        </p>
        <p style="text-align: justify;">
            6.1. O objeto contratado será realizado por execução indireta;
            <br>
            6.2. A execução do objeto seguirá a seguinte dinâmica, sendo de inteira responsabilidade
            da contratada a realização das atividades abaixo relacionadas:
            <br>
            <span style="margin-left: 20px;">
                a) Os serviços contratados, além da execução de trabalhos técnicos e profissionais
                específicos, compreendem, a disponibilização de serviços especializados na modalidade
                de assessoria e consultoria no Setor Público; visando o aprimoramento e o
                desenvolvimento operacional das ações governamentais no âmbito do Poder Executivo
                com vistas ao atingimento de metas de eficiência, eficácia e qualidade nas atividades
                institucionais do Órgão, bem como do atendimento das exigências e obrigações
                constantes da legislação governamental vigente;
                <br>
                b) Poderão ser realizados concomitantemente nas sedes administrativas da
                contratante e da contratada, por meio de disponibilização de mão de obra especializada
                por sócios da empresa ou de propostos quando se tratar de trabalhos específicos e por
                meio de visitas técnicas* semanais de profissionais, bem como no atendimento de
                consultas formuladas por telefone e por meio eletrônica quando se tratar de assessoria e
                consultoria técnica;
                <br>
                c) Quando se tratar de reuniões técnicas para capacitação e orientação de
                servidores ou audiências públicas, estas poderão ser realizadas fora do expediente
                normal de trabalho da Contratante, mediante o agendamento e comunicação prévia por
                parte da Contratante;
                <br>
                d) Em razão da necessidade e da excepcionalidade por parte da Contratante e por se
                tratar de disponibilização de mão de obra por pessoa jurídica, que compreende serviços
                técnicos profissionais especializados, não haverá limitação de tempo e horário na
                execução dos trabalhos, porém, a execução de serviços na sede da Contratante não
                obrigará os profissionais ou prepostos designados pela Contratada à obrigatoriedade de
                cumprimento de horários diários, descaracterizando a subordinação e o vínculo
                empregatício entre ambas as partes;
                <br>
                e) Deverão ser disponibilizados canais de comunicação por parte da Contratada,
                para o atendimento de consultas à distância, através de telefones fixo e móvel, fax, e-mail
                e outras formas de tecnologia disponíveis;
                <br>
                f) A contratação não envolve a disponibilização de quaisquer tipos de equipamentos
                ou aplicativos, necessários às atividades operacionais de ambas as partes;
                <br>
                g) Os trabalhos específicos desdobram-se nos itens a seguir discriminados.
                <br>
                6.3. A CONTRATADA deverá executar o serviço utilizando-se dos materiais e
                equipamentos necessários à perfeita execução dos serviços a serem prestados;
                <br>
                6.4. Não será necessária a utilização de uniforme pela contratada, no entanto os
                funcionários deverão estar identificados no local de prestação de serviço;
                <br>
                6.5. Os Serviços deverão ser executados no município, nas semanas em que o
                profissional estiver no município e sempre à distância quando não houver profissional in
                loco no município.
            </span>
        </p>

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 7. DA DESCRIÇÃO DA SOLUÇÃO
        </p>
        <p style="text-align: justify;">
            7.1. A descrição da solução como um todo, abrange a
            {!! strip_tags($processo->objeto) !!}
            <br>
            7.2. A contratação em tela visa dar continuidade aos serviços acessórios que dão
            sustentabilidade à otimização e adequação das atividades da administração pública, em
            suas atribuições finalísticas.
            <br>
            7.3. Os serviços deverão ser executados com zelo e destreza, e de acordo com as
            descrições, detalhamento e especificações contidas nesse Termo de Referência, não
            eximindo a empresa da responsabilidade de execução de outras atividades atinentes ao
            objeto, a qualquer tempo e a critério da Administração.
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
            alguns requisitos mínimos necessários, dentre eles os de qualidade e capacidade de
            execução pelo contratado, nos termos do artigo 72, da Lei Federal 14.133/2021.
            <br>
            9.2. Será exigido, conforme artigo 62 da Lei Federal 14.133/2021, documentos referentes
            a habilitação jurídica (premissa do artigo 66), habilitação técnica (rol do artigo 67),
            habilitação fiscal, social e trabalhista (artigo 68) habilitação econômico-financeira (rol do
            artigo 69), todos da mesma legislação (Lei Federal 14.133/2021).
            <br>
            9.3. Sendo assim, os documentos exigidos serão:
            <br>
            <span style="margin-left: 20px;">
                o Contrato social da empresa (todas as alterações ou última consolidação);
                <br>
                o Documento de Identificação dos sócios da empresa;
                <br>
                o Prova de inscrição no Cadastro Nacional da Pessoa Jurídica (CNPJ);
                <br>
                o Prova de inscrição no cadastro de contribuintes estadual e/ou municipal
                <br>
                o Regularidade perante a Fazenda Municipal;
                <br>
                o Regularidade perante a Fazenda Estadual;
                <br>
                o Regularidade perante a Fazenda Federal;
                <br>
                o Regularidade perante a Caixa Econômica Federal;
                <br>
                o Regularidade perante a Justiça do Trabalho;
                <br>
                o Atestado de capacidade técnica profissional e/ou operacional;
                <br>
                o Atestado de exclusividade, contrato de exclusividade, declaração do fabricante ou
                outro documento idôneo capaz de comprovar que o objeto é fornecido ou prestado por
                produtor, empresa ou representante comercial exclusivos (se for o caso).
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
            11.1. O recebimento do objeto do contrato, decorrente da referida inexigibilidade de
            licitação, se dará:
            <br>
            <span style="margin-left: 20px;">
                a) provisoriamente, pelo responsável por seu acompanhamento e fiscalização, mediante
                termo detalhado, quando verificado o cumprimento das exigências de caráter técnico;
                <br>
                b) definitivamente, por servidor ou comissão designada pela autoridade competente,
                mediante termo detalhado que comprove o atendimento das exigências contratuais;
            </span>
            11.2. O pagamento será realizado no prazo máximo de até 30 (trinta) dias, contados a
            partir do recebimento da Nota Fiscal ou Fatura, através de ordem bancária, para crédito
            em banco, agência e conta corrente indicados pelo contratado, respeitada a ordem
            cronológica.
            <br>
            11.3. A emissão da Nota Fiscal/Fatura deve ser precedida do recebimento definitivo do
            objeto, nos termos abaixo.
            <br>
            11.4. No prazo de até 5 dias corridos do adimplemento da parcela, a CONTRATADA
            deverá entregar toda a documentação comprobatória do cumprimento da obrigação
            contratual;
            <br>
            11.5. A contratante realizará inspeção minuciosa de todos os objetos
            executados/fornecidos, por meio de profissionais técnicos competentes, acompanhados
            dos profissionais encarregados pelo serviço, com a finalidade de verificar a adequação
            do objeto e constatar e relacionar os arremates, retoques e revisões finais que se fizerem
            necessários.
            <br>
            11.6. Para efeito de recebimento provisório, ao final de cada período de faturamento, o
            fiscal técnico do contrato irá apurar o resultado das avaliações da execução do objeto e,
            se for o caso, a análise do desempenho e qualidade dos mesmos em consonância com
            os indicadores previstos, que poderá resultar no redimensionamento de valores a serem
            pagos à contratada, registrando em relatório a ser encaminhado ao gestor do contrato.
            <br>
            11.7. A Contratada fica obrigada a reparar, corrigir, remover, reconstruir ou substituir, às
            suas expensas, no todo ou em parte, o objeto em que se verificarem vícios, defeitos ou
            incorreções resultantes da execução ou materiais empregados, cabendo à fiscalização
            não atestar a última e/ou única medição de serviços até que sejam sanadas todas as
            eventuais pendências que possam vir a ser apontadas no Recebimento Provisório.
            <br>
            11.8. No prazo de até 10 (dez) dias corridos a partir do recebimento provisório, o Gestor
            do Contrato deverá providenciar o recebimento definitivo, ato quo concretiza o ateste da
            execução/fornecimento, obedecendo as seguintes diretrizes:
            <br>
            11.9. Realizar a análise dos relatórios e de toda a documentação apresentada pela
            fiscalização e, caso haja irregularidades que impeçam a liquidação e o pagamento da
            despesa, indicar as cláusulas contratuais pertinentes, solicitando à CONTRATADA, por
            escrito, as respectivas correções;
            <br>
            11.10. O recebimento provisório ou definitivo do objeto não exclui a responsabilidade da
            Contratada pelos prejuízos resultantes da incorreta execução do contrato, ou, em
            qualquer época, das garantias concedidas e das responsabilidades assumidas em
            contrato e por força das disposições legais em vigor.
            <br>
            11.11. Os serviços/produtos poderão ser rejeitados, no todo ou em parte, quando em
            desacordo com as especificações constantes neste Termo de Referência e na proposta,
            devendo ser corrigidos/refeitos/substituídos no prazo fixado pelo fiscal do contrato, às
            custas da Contratada, sem prejuízo da aplicação de penalidades.
            <br>
            11.12. A Nota Fiscal ou Fatura deverá ser obrigatoriamente acompanhada da
            comprovação da regularidade fiscal, mediante consulta aos sítios eletrônicos oficiais ou à
            documentação mencionada no art. 68 da Lei Federal 14.133/2021.
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
            13.1. A seleção do prestador de serviço foi realizada com base nos requisitos previstos
            neste termo de referência, atrelado a proposta vantajosa apresentada pelo(a)
            contratado(a) {{ $processo->detalhe->razao_social }}, inscrito(a) sob o CPF/CNPJ de n°
            {{ $processo->detalhe->cnpj_empresa_vencedora }}, conforme documentos acostados aos autos do processo.
            <br>
            13.2. O contratado(a) é notória em sua área de especialização, tendo cumprido todos os
            requisitos de habilitação exigidos, especialmente a habilitação jurídica, regularidade
            fiscal e trabalhista, qualificação econômico-financeira e qualificação técnica.
        </p>

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 14. DA RAZÃO E ESCOLHA DO CONTRATADO
        </p>
        <p style="text-align: justify;">
            14.1. {{ $processo->detalhe->razao_escolha_contratado }}
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
            15.3. Sendo assim, declara-se que o preço praticado para a presente contratação é
            compatível com o mercado sendo considerado justo para esta Administração.
        </p>

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 16. OBRIGAÇÕES DO(A) CONTRATADO(A) 
        </p>
        <p style="text-align: justify;">
            Quando for contratação de assessorias ou consultorias técnicas e auditorias financeiras
            ou tributárias (Art. 74, inc. III, alínea “c”, da Lei 14.133/21), manter o seguinte texto:

            16.1. O(A) CONTRATADO(A) obriga-se a:
            <br>
            16.1.1. executar os serviços conforme especificações do Termo de Referência e de sua
            proposta, com os recursos necessários ao perfeito cumprimento das cláusulas
            contratuais;
            <br>
            16.1.2. reparar, corrigir, remover, reconstruir ou substituir, às suas expensas, no total ou
            em parte, os serviços efetuados em que se verificarem vícios, defeitos ou incorreções
            resultantes da execução ou dos materiais empregados, a critério da Administração;
            <br>
            16.1.3. arcar com a responsabilidade civil por todos e quaisquer danos materiais e morais
            causados pela ação ou omissão de seus empregados, trabalhadores, prepostos ou
            representantes, dolosa ou culposamente, à Prefeitura ou a terceiros;
            <br>
            16.1.4. utilizar empregados habilitados e com conhecimentos básicos dos serviços a
            serem executados, de conformidade com as normas e determinações em
            <br>
            16.1.6. apresentar à CONTRATANTE, quando for o caso, a relação nominal dos
            empregados que adentrarão o órgão para a execução do serviço, os quais devem estar
            devidamente identificados por meio de crachá;
            <br>
            16.1.7. responsabilizar-se por todas as obrigações trabalhistas, sociais, previdenciárias,
            tributárias e as demais previstas na legislação específica;
            <br>
            16.1.8. instruir seus empregados quanto à necessidade de acatar as orientações da
            Administração, inclusive quanto ao cumprimento das Normas Internas, quando for o
            caso;
            <br>
            16.1.9. relatar à Prefeitura toda e qualquer irregularidade verificada no decorrer da
            prestação dos serviços;
            <br>
            16.1.10. não permitir a utilização de qualquer trabalho do menor de dezesseis anos,
            exceto na condição de aprendiz para os maiores de quatorze anos; nem permitir a
            utilização do trabalho do menor de dezoito anos em trabalho noturno, perigoso ou
            insalubre;
            <br>
            16.1.11. manter durante toda a vigência do contrato, em compatibilidade com as
            obrigações assumidas, todas as condições de habilitação e qualificação exigidas na
            contratação;
            <br>
            16.1.12. não transferir a terceiros, por qualquer forma, nem mesmo parcialmente, as
            obrigações assumidas, nem subcontratar qualquer das prestações a que está obrigada,
            exceto nas condições se previamente autorizadas pela Administração;
            <br>
            16.1.13. Utilizar empregados habilitados e com conhecimentos básicos dos serviços a
            serem executados, em conformidade com as normas e determinações em vigor;
            <br>
            16.1.14. Vedar a utilização, na execução dos serviços, de empregado que seja familiar de
            agente público ocupante de cargo em comissão ou função de confiança no órgão
            Contratante.
            <br>
            16.1.15. Disponibilizar à Contratante os empregados devidamente uniformizados e
            identificados por meio de crachá, além de provê-los com os Equipamentos de Proteção
            Individual - EPI, quando for o caso;
            <br>
            16.1.16. Fornecer os uniformes a serem utilizados por seus empregados, conforme
            disposto neste Termo de Referência, sem repassar quaisquer custos a estes;
            <br>
            16.1.17. As empresas contratadas que sejam regidas pela Consolidação das Leis do
            Trabalho (CLT) deverão apresentar a seguinte documentação no primeiro mês de
            prestação dos serviços:
            <br>
            16.1.18. Substituir, no prazo de 02:00 (horas), em caso de eventual ausência, tais como
            faltas e licenças, o empregado posto a serviço da Contratante, devendo identificar
            previamente o respectivo substituto ao Fiscal do Contrato:
            <br>
            16.1.19. Responsabilizar-se pelo cumprimento das obrigações previstas em Acordo,
            Convenção, Dissídio Coletivo de Trabalho ou equivalentes das categorias abrangidas
            pelo contrato, por todas as obrigações trabalhistas, sociais, previdenciárias, tributárias e
            as demais previstas em legislação específica, cuja inadimplência não transfere a
            responsabilidade à Contratante;
            <br>
            16.1.19.1. Não serão incluídas nas planilhas de custos e formação de preços as
            disposições contidas em Acordos, Dissídios ou Convenções Coletivas que tratem de
            pagamento de participação dos trabalhadores nos lucros ou resultados da empresa
            contratada, de matéria não trabalhista, de obrigações e direitos que somente se aplicam
            aos contratos com a Administração Pública, ou que estabeleçam direitos não previstos
            em lei, tais como valores ou índices obrigatórios de encargos sociais ou previdenciários,
            bem como de preços para os insumos relacionados ao exercício da atividade.
            <br>
            16.1.20. Efetuar o pagamento dos salários dos empregados alocados na execução
            contratual mediante depósito na conta bancária de titularidade do trabalhador, em
            agência situada na localidade ou região metropolitana em que ocorre a prestação dos
            serviços, de modo a possibilitar a conferência do pagamento por parte da Contratante.
            Em caso de impossibilidade de cumprimento desta disposição, a contratada deverá
            apresentar justificativa, a fim de que a Administração analise sua plausibilidade e possa
            verificar a realização do pagamento:
            <br>
            16.2. Assegurar à CONTRATANTE:
            <br>
            16.2.1. O direito de propriedade intelectual dos produtos desenvolvidos, inclusive sobre
            as eventuais adequações e atualizações que vierem a ser realizadas, logo após o
            recebimento de cada parcela, de forma permanente, permitindo à Contratante distribuir,
            alterar e utilizar os mesmos sem limitações;
            <br>
            16.2.2. Os direitos autorais da solução, do projeto, de suas especificações técnicas, da
            documentação produzida e congêneres, e de todos os demais produtos gerados na
            execução do contrato, inclusive aqueles produzidos por terceiros subcontratados, ficando
            proibida a sua utilização sem que exista autorização expressa da Contratante, sob pena
            de multa, sem prejuízo das sanções civis e penais cabíveis;
            <br>
            16.3. Os serviços serão executados pela CONTRATADA na forma descrita no Termo de
            Referência;
            <br>
            16.4. Os termos indicados na proposta vinculam a referida contratação;
        </p>

        {!! $processo->detalhe->obrigacoes_contratado_extras !!}

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 17. OBRIGAÇÕES DA CONTRATANTE 
        </p>
        <p style="text-align: justify;">
            17.1. A CONTRATANTE obriga-se a:
            <br>
            17.1.1 Proporcionar todas as condições para que a CONTRATADA possa desempenhar
            seus serviços de acordo com as determinações do Contrato e do Termo de Referência;
            <br>
            17.1.2. Exigir o cumprimento de todas as obrigações assumidas pela CONTRATADA, de
            acordo com as cláusulas contratuais e os termos de sua proposta;
            <br>
            17.1.3. Exercer o acompanhamento e a fiscalização do objeto contratado, por servidor
            especialmente designado, anotando em registro próprio as falhas detectadas, indicando
            dia, mês e ano, bem como o nome dos empregados eventualmente envolvidos, e
            encaminhando os apontamentos à autoridade competente para as providências cabíveis;
            <br>
            17.1.4. Notificar a CONTRATADA por escrito da ocorrência de eventuais imperfeições no
            curso da execução dos serviços, fixando prazo para a sua correção;
            <br>
            17.1.5. Pagar à CONTRATADA o valor resultante do objeto contratado, na forma do
            contrato;
            <br>
            17.1.6. Efetuar as retenções tributárias devidas sobre o valor da Nota Fiscal/Fatura da
            contratada, no que couber, em conformidade com a legislação.
            <br>
            17.2. Não praticar atos de ingerência na administração da Contratada, tais como:
            <br>
            17.2.1. exercer o poder de mando sobre os empregados da Contratada, devendo
            reportar-se somente aos prepostos ou responsáveis por ela indicados, exceto quando o
            objeto da contratação previr o atendimento direto, tais como nos serviços de recepção e
            apoio ao usuário;
            <br>
            17.2.2. direcionar a contratação de pessoas para trabalhar nas empresas Contratadas;
            <br>
            17.2.3. promover ou aceitar o desvio de funções dos trabalhadores da Contratada,
            mediante a utilização destes em atividades distintas daquelas previstas no objeto da
            contratação e em relação à função específica para a qual o trabalhador foi contratado;
            <br>
            17.2.4. considerar os trabalhadores da Contratada como colaboradores eventuais do
            próprio órgão ou entidade responsável pela contratação, especial mente para efeito de
            concessão de diárias e passagens;
            <br>
            17.3. fiscalizar mensalmente, por amostragem, o cumprimento das obrigações
            trabalhistas, previdenciárias e para com o FGTS, especialmente:
            <br>
            17.3.1. A concessão de férias remuneradas e o pagamento do respectivo adicional, bem
            como de auxílio-transporte, auxílio-alimentação e auxílio-saúde, quando for devido;
            <br>
            17.3.2. O recolhimento das contribuições previdenciárias e do FGTS dos empregados
            que efetivamente participem da execução dos serviços contratados, a fim de verificar
            qualquer irregularidade:
            <br>
            17.3.3. O pagamento de obrigações trabalhistas e previdenciárias dos empregados
            dispensados até a data da extinção do contrato;
            <br>
            17.4. Analisar os termos de rescisão dos contratos de trabalho do pessoal empregado na
            execução do objeto contratado no prazo de 30 (trinta) dias, prorrogável por igual período,
            após a extinção ou rescisão do contrato;
            <br>
            17.5. Fornecer por escrito às informações necessárias para a execução do objeto
            contratado;
            <br>
            17.6. Realizar avaliações periódicas da qualidade do objeto contratado, após seu
            recebimento;
            <br>
            17.7. Cientificar o órgão de representação judicial do município para adoção das medidas
            cabíveis quando do descumprimento das obrigações pela Contratada;
            <br>
            17.8. Arquivar, entre outros documentos, projetos, "as built", especificações técnicas,
            orçamentos, termos de recebimento, contratos e aditamentos, relatórios de inspeções
            técnicas após o recebimento do serviço e notificações expedidas;
            <br>
            17.9. Assegurar que o ambiente de trabalho, inclusive seus equipamentos e instalações,
            apresentem condições adequadas ao cumprimento, pela contratada, das normas de
            segurança e saúde no trabalho, quando o serviço for executado em suas dependências,
            ou em local por ela designado;
            <br>
            17.10. Verificar, no ato do recebimento, se o objeto entregue corresponde exatamente à
            marca/modelo/serviço contratado.
        </p>
        {{ $processo->detalhe->obrigacoes_contratante_extras }}

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
            a apresentação das propostas;
            <br>
            19.1.1 Dentro do prazo de vigência do contrato e mediante solicitação da contratada, os
            preços contratados poderão sofrer reajuste após o interregno de um ano, aplicando-se o
            índice IGPM exclusiva mente para as obrigações iniciadas e concluídas após a
            ocorrência da anualidade;
            <br>
            19.2. Nos reajustes subsequentes ao primeiro, o interregno mínimo de um ano será
            contado a partir dos efeitos financeiros do último reajuste;
            <br>
            19.3. No caso de atraso ou não divulgação do índice de reajustamento, o
            CONTRATANTE pagará à CONTRATADA a importância calculada pela última variação
            conhecida, liquidando a diferença correspondente, tão logo seja divulgado o índice
            definitivo. Fica a CONTRATADA obrigada a apresentar memória de cálculo referente ao
            reajustamento de preços do valor remanescente, sempre que este ocorrer;
            <br>
            19.4. Nas aferições finais, o índice utilizado para reajuste será, obrigatoriamente, o
            definitivo;
            <br>
            19.5. Caso o índice estabelecido para reajustamento venha a ser extinto ou de qualquer
            forma não possa mais ser utilizado, será adotado, em substituição, o que vier a ser
            determinado pela legislação então em vigor;
            <br>
            19.6. Na ausência dê previsão legal quanto ao índice substituto, as partes elegerão novo
            índice oficial, para reajustamento do preço do valor remanescente, por meio de termo
            aditivo;
            <br>
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
