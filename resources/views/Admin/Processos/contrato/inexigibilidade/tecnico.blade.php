<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Contrato - Processo {{ $processo->numero_processo ?? $processo->id }}</title>
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

        /* .conteudo {
            margin: 0 90px;
        } */

        .title {
            text-align: center;
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
    </style>
</head>

<body>

    <div>
        <h4 style="text-align: center;">
            CONTRATO Nº {{ $campos['numero_contrato'] }}
        </h4>

        <!-- Unidade Requisitante -->
        <div class="section">
            <table>
                <tr>
                    <td class="icon">
                        <img src="{{ public_path('icons/Imagem1.png') }}" width="40">
                    </td>
                    <td class="content">
                        <div style=" font-weight: bold; margin-bottom: 3px;">Contratante</div>
                        <div style="">
                            {{ $processo->finalizacao->orgao_responsavel }}, com sede no(a)
                            {{ $processo->prefeitura->endereco }}, na cidade de {{ $processo->prefeitura->cidade }}
                            inscrito(a) no CNPJ
                            sob o nº {{ $processo->finalizacao->cnpj }}, neste ato representado(a) pelo(a)
                            {{ $processo->finalizacao->responsavel }} inscrito no CPF sob n°
                            {{ $processo->finalizacao->cpf_responsavel }}.
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <div class="section">
            <table>
                <tr>
                    <td class="icon">
                        <img src="{{ public_path('icons/Imagem1.png') }}" width="40">
                    </td>
                    <td class="content">
                        <div style=" font-weight: bold; margin-bottom: 3px;">Contratado</div>
                        <div style="">
                            {{ $processo->detalhe->razao_social }}, inscrito(a) no CNPJ/MF sob o nº
                            {{ $processo->detalhe->cnpj_empresa_vencedora }}, sediado(a) na
                            {{ $processo->detalhe->endereco_empresa_vencedora }} neste
                            ato representado(a) por {{ $processo->detalhe->representante_legal_empresa }}, inscrito
                            no CPF sob n° {{ $processo->detalhe->cpf_representante }}.
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <p style="text-align: justify;">
            Tendo em vista o que consta no Processo administrativo n° {{ $processo->numero_processo }} e em
            observância às disposições da Lei n°
            14.133, de 2021 e na Lei n° 8.078, de 1990 - Código de Defesa do Consumidor, resolvem celebrar o presente
            Termo de Contrato, decorrente da Inexigibilidade de licitação n° {{ $processo->numero_processo }}, mediante as cláusulas e condições a seguir
            enunciadas.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
                 alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">1. CLÁUSULA PRIMEIRA – DO OBJETO E REGIME DE EXECUÇÃO</h4>
        </div>
        <p style="text-align: justify;">
            1.1. O objeto do presente Termo de contrato é a {!! strip_tags($processo->objeto) !!}<br>
            1.2. Todos os termos do Termo de Referência e da proposta da contratada integram o presente contrato em todas as
            suas condições.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
                 alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">2. CLÁUSULA SEGUNDA - DA EXECUÇÃO DO CONTRATO</h4>
        </div>
        <p style="text-align: justify;">
            2.1. Os serviços serão executados em conformidade com a proposta apresentada pela CONTRATADA, vez compõe, em
            todos os seus termos, o processo administrativo n° {{ $processo->numero_processo }} e inexigibilidade de licitação {{ $processo->numero_procedimento }}.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
                 alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">3. CLÁUSULA TERCEIRA - DO PRAZO</h4>
        </div>
        <p style="text-align: justify;">
            3.1- O prazo de vigência deste Termo de Contrato tem início na data de {{ \Carbon\Carbon::parse($processo->detalhe->prazo_inicio_prestacao_servico)->format('d/m/Y') }} e encerramento em {{ \Carbon\Carbon::parse($processo->detalhe->prazo_final_prestacao_servico)->format('d/m/Y') }}.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
                 alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">4. CLÁUSULA QUARTA - DO PREÇO E FORMA DE PAGAMENTO</h4>
        </div>
        <p style="text-align: justify;">
            4.1- O valor do presente Termo de Contrato é de R$ {{ number_format($processo->detalhe->valor_total, 2, ',', '.') }}; <br>
            4.2 - No valor acima estão incluídas todas as despesas ordinárias diretas e indiretas decorrentes da execução contratual,
            inclusive tributos e/ou impostos, encargos sociais, trabalhistas, previdenciários, fiscais e comerciais incidentes, taxa de
            administração, frete, seguro e outros necessários ao cumprimento integral do objeto da contratação;<br>
            4.3 - Os preços são fixos e irreajustáveis;<br>
            4.4 - O pagamento será efetuado em até 30 (trinta) dias da apresentação Fatura / Nota Fiscal, em 02 (duas) vias que
            deverá ser apresentada ao titular da Secretaria de Finanças para a devida aprovação.<br>
            4.5 - Não será efetuado qualquer pagamento a título de antecipação do valor contratado mesmo que a requerimento do
            interessado.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
                 alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">5. CLÁUSULA QUINTA - DA DOTAÇÃO ORÇAMENTÁRIA</h4>
        </div>
        <p style="text-align: justify;">
            5.1 - A Dotação orçamentária que correrá tal despesa é: {!! strip_tags($processo->detalhe->dotacao_orcamentaria) !!}, conforme disposto na Lei de meios
            vigente.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
                 alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">6. CLÁUSULA SEXTA - DAS ALTERAÇÕES</h4>
        </div>
        <p style="text-align: justify;">
            6.1 - Eventuais alterações contratuais reger-se-ão pela disciplina do art. 124 da Lei n° 14.133 de 2021; A CONTRATADA
            é obrigada a aceitar, nas mesmas condições contratuais, os acréscimos ou supressões que se fizerem necessários, até
            o limite de 25% (vinte e cinco por cento) do valor inicial atualizado do contrato; <br>
            6.3 - As supressões resultantes de acordo celebrado entre as partes contratantes poderão exceder o limite de 25% (vinte
            e cinco por cento) do valor inicial atualizado do contrato.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
                 alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">7. CLÁUSULA SÉTIMA - FISCALIZAÇÃO</h4>
        </div>
        <p style="text-align: justify;">
            7.1 - A fiscalização da execução do objeto será efetuada por Representante designado pela Secretaria solicitante
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
                 alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">8. CLÁUSULA OITAVA - OBRIGAÇÕES DO CONTRATANTE</h4>
        </div>
        <p style="text-align: justify;">
            8.1. A CONTRATADA obriga-se a:<br>
            8. 1.1. executar os serviços conforme especificações do Termo de Referência e de sua proposta, com os recursos
            necessários ao perfeito cumprimento das cláusulas contratuais;<br>
            8.1.2. reparar, corrigir, remover, reconstruir ou substituir, às suas expensas, no total ou em parte, os serviços
            efetuados em que se verificarem vícios, defeitos ou incorreções resultantes da execução ou dos materiais
            empregados, a critério da Administração;<br>
            8.1.3. arcar com a responsabilidade civil por todos e quaisquer danos materiais e morais causados pela ação ou
            omissão de seus empregados, trabalhadores, prepostos ou representantes, dolosa ou culposamente, à Prefeitura
            ou a terceiros;<br>
            8.1.4. utilizar empregados habilitados e com conhecimentos básicos dos serviços a serem executados, de
            conformidade com as normas e determinações em vigor;<br>
            8.1.6. apresentar à CONTRATANTE, quando for o caso, a relação nominal dos empregados que adentrarão o
            órgão para a execução do serviço, os quais devem estar devidamente identificados por meio de crachá;<br>
            8. 1.7. responsabilizar-se por todas as obrigações trabalhistas, sociais, previdenciárias, tributárias e as demais
            previstas na legislação específica;<br>
            8.1.8. instruir seus empregados quanto à necessidade de acatar as orientações da Administração, inclusive quanto
            ao cumprimento das Normas Internas, quando for o caso;<br>
            8.1.9. relatar à Prefeitura toda e qualquer irregularidade verificada no decorrer da prestação dos serviços;<br>
            8.1.10. não permitir a utilização de qualquer trabalho do menor de dezesseis anos, exceto na condição de aprendiz
            para os maiores de quatorze anos; nem permitir a utilização do trabalho do menor de dezoito anos em trabalho
            noturno, perigoso ou insalubre;<br>
            8.1.11. manter durante toda a vigência do contrato, em compatibilidade com as obrigações assumidas, todas as
            condições de habilitação e qualificação exigidas na contratação;<br>
            8.1.12. não transferir a terceiros, por qualquer forma, nem mesmo parcialmente, as obrigações assumidas, nem
            subcontratar qualquer das prestações a que está obrigada, exceto nas condições se previamente autorizadas pela
            Administração;<br>
            8.1.13. Utilizar empregados habilitados e com conhecimentos básicos dos serviços a serem executados, em
            conformidade com as normas e determinações em vigor;<br>
            8.1.14. Vedar a utilização, na execução dos serviços, de empregado que seja familiar de agente público ocupante
            de cargo em comissão ou função de confiança no órgão Contratante.<br>
            8.1.15. Disponibilizar à Contratante os empregados devidamente uniformizados e identificados por meio de crachá,
            além de provê-los com os Equipamentos de Proteção Individual - EPI, quando for o caso;<br>
            8.1.16. Fornecer os uniformes a serem utilizados por seus empregados, conforme disposto neste Termo de
            Referência, sem repassar quaisquer custos a estes;<br>
            8.1.17. As empresas contratadas que sejam regidas pela Consolidação das Leis do Trabalho (CLT) deverão
            apresentar a seguinte documentação no primeiro mês de prestação dos serviços:<br>
            8.1.18. Substituir, no prazo de 02:00 (horas), em caso de eventual ausência, tais como faltas e licenças, o
            empregado posto a serviço da Contratante, devendo identificar previamente o respectivo substituto ao Fiscal do
            Contrato:<br>
            8.1.19. Responsabilizar-se pelo cumprimento das obrigações previstas em Acordo, Convenção, Dissídio Coletivo
            de Trabalho ou equivalentes das categorias abrangidas pelo contrato, por todas as obrigações trabalhistas, sociais,
            previdenciárias, tributárias e as demais previstas em legislação específica, cuja inadimplência não transfere a
            responsabilidade à Contratante;<br>
            8.1.19.1. Não serão incluídas nas planilhas de custos e formação de preços as disposições contidas em Acordos,
            Dissídios ou Convenções Coletivas que tratem de pagamento de participação dos trabalhadores nos lucros ou
            resultados da empresa contratada, de matéria não trabalhista, de obrigações e direitos que somente se aplicam
            aos contratos com a Administração Pública, ou que estabeleçam direitos não previstos em lei, tais como valores ou
            índices obrigatórios de encargos sociais ou previdenciários, bem como de preços para os insumos relacionados ao
            exercício da atividade.<br>
            8.1.20. Efetuar o pagamento dos salários dos empregados alocados na execução contratual mediante depósito na
            conta bancária de titularidade do trabalhador, em agência situada na localidade ou região metropolitana em que
            ocorre a prestação dos serviços, de modo a possibilitar a conferência do pagamento por parte da Contratante. Em
            caso de impossibilidade de cumprimento desta disposição, a contratada deverá apresentar justificativa, a fim de
            que a Administração analise sua plausibilidade e possa verificar a realização do pagamento:<br>
            8.2. Assegurar à CONTRATANTE:<br>
            8.2.1. O direito de propriedade intelectual dos produtos desenvolvidos, inclusive sobre as eventuais adequações e
            atualizações que vierem a ser realizadas, logo após o recebimento de cada parcela, de forma permanente,
            permitindo à Contratante distribuir, alterar e utilizar os mesmos sem limitações;<br>
            8.2.2. Os direitos autorais da solução, do projeto, de suas especificações técnicas, da documentação produzida e
            congêneres, e de todos os demais produtos gerados na execução do contrato, inclusive aqueles produzidos por
            terceiros subcontratados, ficando proibida a sua utilização sem que exista autorização expressa da Contratante,
            sob pena de multa, sem prejuízo das sanções civis e penais cabíveis;<br>
            8.3. Os serviços serão executados pela CONTRATADA na forma descrita no Termo de Referência;<br>
            8.4. Os termos indicados na proposta vinculam a referida contratação;
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
                 alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">9. CLÁUSULA NONA - OBRIGAÇÕES DA CONTRATANTE</h4>
        </div>
        <p style="text-align: justify;">
            9.1. A CONTRATANTE obriga-se a:<br>
            9.1.1 Proporcionar todas as condições para que a CONTRATADA possa desempenhar seus serviços de acordo
            com as determinações do Contrato e do Termo de Referência;<br>
            9.1.2. Exigir o cumprimento de todas as obrigações assumidas pela CONTRATADA, de acordo com as cláusulas
            contratuais e os termos de sua proposta;<br>
            9.1.3. Exercer o acompanhamento e a fiscalização dos serviços, por servidor especialmente designado, anotando
            em registro próprio as falhas detectadas, indicando dia, mês e ano, bem como o nome dos empregados
            eventualmente envolvidos, e encaminhando os apontamentos à autoridade competente para as providências
            cabíveis;<br>
            9.1.4. Notificar a CONTRATADA por escrito da ocorrência de eventuais imperfeições no curso da execução dos
            serviços, fixando prazo para a sua correção;<br>
            9.1.5. Pagar à CONTRATADA o valor resultante da prestação do serviço, na forma do contrato;<br>
            9.1.6. Efetuar as retenções tributárias devidas sobre o valor da Nota Fiscal/Fatura da contratada, no que couber,
            em conformidade com a legislação.<br>
            9.2. Não praticar atos de ingerência na administração da Contratada, tais como:<br>
            9.2.1. exercer o poder de mando sobre os empregados da Contratada, devendo reportar-se somente aos
            prepostos ou responsáveis por ela indicados, exceto quando o objeto da contratação previr o atendimento direto,
            tais como nos serviços de recepção e apoio ao usuário;<br>
            9.2.2. direcionar a contratação de pessoas para trabalhar nas empresas Contratadas;<br>
            9.2.3. promover ou aceitar o desvio de funções dos trabalhadores da Contratada, mediante a utilização destes em
            atividades distintas daquelas previstas no objeto da contratação e em relação à função específica para a qual o
            trabalhador foi contratado; <br>
            9.2.4. considerar os trabalhadores da Contratada como colaboradores eventuais do próprio órgão ou entidade
            responsável pela contratação, especial mente para efeito de concessão de diárias e passagens;<br>
            9.3. fiscalizar mensalmente, por amostragem, o cumprimento das obrigações trabalhistas, previdenciárias e para
            com o FGTS, especialmente:<br>
            9.3.1. A concessão de férias remuneradas e o pagamento do respectivo adicional, bem como de auxílio-transporte,
            auxílio-alimentação e auxílio-saúde, quando for devido;<br>
            9.3.2. O recolhimento das contribuições previdenciárias e do FGTS dos empregados que efetivamente participem
            da execução dos serviços contratados, a fim de verificar qualquer irregularidade:<br>
            9.3.3. O pagamento de obrigações trabalhistas e previdenciárias dos empregados dispensados até a data da
            extinção do contrato;<br>
            9.4. Analisar os termos de rescisão dos contratos de trabalho do pessoal empregado na prestação dos serviços no
            prazo de 30 (trinta) dias, prorrogável por igual período, após a extinção ou rescisão do contrato;
            9.5. Fornecer por escrito às informações necessárias para o desenvolvimento dos serviços objeto do
            contrato;<br>
            9.6. Realizar avaliações periódicas da qualidade dos serviços, após seu recebimento;<br>
            9.7. Cientificar o órgão de representação judicial do município para adoção das medidas cabíveis quando do
            descumprimento das obrigações pela Contratada;<br>
            9.8. Arquivar, entre outros documentos, projetos, "as built", especificações técnicas, orçamentos, termos de
            recebimento, contratos e aditamentos, relatórios de inspeções técnicas após o recebimento do serviço e
            notificações expedidas;<br>
            9.9. Assegurar que o ambiente de trabalho, inclusive seus equipamentos e instalações, apresentem condições
            adequadas ao cumprimento, pela contratada, das normas de segurança e saúde no trabalho, quando o serviço for
            executado em suas dependências, ou em local por ela designado;
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
                 alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">10. CLÁUSULA DÉCIMA - DAS PENALIDADES E SANÇÕES ADMINISTRATIVAS</h4>
        </div>
        <p style="text-align: justify;">
            10.1. Pela inexecução total ou parcial do objeto do CONTRATO, o Município poderá aplicar a CONTRATADA multa
            de até 5% (cinco por cento) do valor do contrato, sem prejuízo das demais penalidades previstas na Lei 14.133/21,
            inclusive responsabilização civil e penal na forma da Legislação específica;<br>
            10.2. Além da multa prevista ficam estabelecidas as penas de advertência, rescisão de contrato, declaração de
            inidoneidade e suspensão do direito de licitar e contratar com o MUNICÍPIO, que serão aplicadas em função da
            natureza e gravidade da falta cometida, garantida a ampla defesa.<br>
            10.3. O MUNICÍPIO reterá dos créditos decorrentes deste Contrato valores suficientes ao pagamento das multas
            aplicadas.<br>
            10.4. Nenhum pagamento será efetuado à CONTRATADA sem a quitação das multas aplicadas em definitivo.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
                 alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">11. CLÁUSULA DÉCIMA PRIMEIRA - DA RESCISÃO</h4>
        </div>
        <p style="text-align: justify;">
            11.1 - O presente Termo de Contrato poderá ser rescindido nas hipóteses previstas no art. 137 da Lei n° 14.133, de
            2021, sem prejuízo das sanções aplicáveis.<br>
            11.2 - É admissível a fusão, cisão ou incorporação da contratada com/em outra pessoa jurídica, desde que sejam
            observados pela nova pessoa jurídica todos os requisitos de habilitação exigidos na licitação original; sejam
            mantidas as demais cláusulas e condições do contrato; não haja prejuízo à execução do objeto pactuado e haja a
            anuência expressa da Administração à continuidade do contrato;<br>
            11.3 - Os casos de rescisão contratual serão formalmente motivados, assegurando-se à CONTRATADA o direito à
            prévia e ampla defesa;
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
                 alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">12. CLÁUSULA DÉCIMA SEGUNDA - DOS CASOS OMISSOS</h4>
        </div>
        <p style="text-align: justify;">
            12.1. Os casos omissos serão decididos pela CONTRATANTE, segundo as disposições contidas na Lei n° 14.133,
            de 2021, e demais normas federais de licitações e contratos administrativos e, subsidiariamente, segundo as
            disposições contidas na Lei n° 8.078, de 1990 - Código de Defesa do Consumidor - e normas e princípios gerais
            dos contratos.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
                 alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">13. CLÁUSULA DÉCIMA TERCEIRA - DA FUNDAMENTAÇÃO LEGAL E PUBLICAÇÃO</h4>
        </div>
        <p style="text-align: justify;">
            13.1 - O presente Contrato tem embasamento legal no artigo 74, inciso III, “c” da 14.133, de 2021.<br>
            13.2 - É de responsabilidade da CONTRATANTE a publicação legal do instrumento.
        </p>
        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
                 alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">14. CLÁUSULA DÉCIMA QUARTA - DO FORO</h4>
        </div>
        <p style="text-align: justify;">
            14.1 - Fica eleito o foro da Comarca de {{ $processo->contrato->comarca }} como único e competente para dirimir quaisquer demandas do
            presente contrato, por mais privilegiado que outro possa ser.<br>
            14.2 - E por estarem justos e contratados firmam o presente em 02 (duas) vias de igual teor e forma para que
            produzam os efeitos legais.
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
                        Prefeitura do Município de {{ $processo->prefeitura->cidade }} <br>
                        {{ $primeiroAssinante['responsavel'] }} <br>
                        <span>{{ $primeiroAssinante['unidade_nome'] }}</span>
                    </p>
                </div>
            </div>
            <div style="margin-top: 40px; text-align: center;">
                <div class="signature-block" style="display: inline-block; margin: 0 40px;">
                    ___________________________________<br>
                    <p style="line-height: 1.2;">
                        {{ $processo->finalizacao->razao_social }} <br>
                        {{ $processo->finalizacao->representante_legal_empresa }} <br>
                        {{ $processo->finalizacao->cpf_representante }} <br>
                    </p>
                </div>
            </div>
        @else
            {{-- Bloco Padrão (Fallback) --}}
            <div class="signature-block" style="margin-top: 40px; text-align: center;">
                ___________________________________<br>
                <p style="line-height: 1.2;">
                    <span style="color: red;">[Cargo/Título Padrão - A ser ajustado]</span>
                </p>
            </div>
        @endif

        TESTEMUNHAS<br><br>

        ___________________________________<br><br><br>
        ___________________________________

        {{-- QUEBRA DE PÁGINA --}}
        <div class="page-break"></div>

        <div>
            <table style="width:100%; border-collapse:collapse; font-size:10px; margin-bottom:20px; " border="1">

                <!-- Cabeçalho -->
                <tr>
                    <td colspan="2" style="padding:8px; text-align:center; font-weight:bold;">
                        EXTRATO DO CONTRATO Nº {{ $campos['numero_extrato'] }}<br>
                        PROCESSO ADMINISTRATIVO Nº {{ $processo->numero_processo }}<br>
                        MODALIDADE: CONCORRÊNCIA ELETRÔNICA Nº {{ $processo->numero_procedimento }}
                    </td>
                </tr>

                <!-- OBJETO -->
                <tr>
                    <td style="padding:6px; width:30%; font-weight:bold;">
                        OBJETO:
                    </td>
                    <td style="padding:6px;">
                        {!! strip_tags($processo->objeto) !!}
                    </td>
                </tr>

                <!-- CONTRATANTE -->
                <tr>
                    <td style="padding:6px; font-weight:bold;">
                        CONTRATANTE:
                    </td>
                    <td style="padding:6px;">
                        <span>{{ $processo->prefeitura->nome }}</span>
                    </td>
                </tr>

                <!-- CONTRATADO -->
                <tr>
                    <td style="padding:6px; font-weight:bold;">
                        CONTRATADO:
                    </td>
                    <td style="padding:6px;">
                        {{ $processo->detalhe->razao_social }}
                    </td>
                </tr>

                <!-- CNPJ -->
                <tr>
                    <td style="padding:6px; font-weight:bold;">
                        CNPJ (CONTRATADO):
                    </td>
                    <td style="padding:6px;">
                        {{ $processo->detalhe->cnpj_empresa_vencedora }}
                    </td>
                </tr>

                <!-- VALOR -->
                <tr>
                    <td style="padding:6px; font-weight:bold;">
                        VALOR:
                    </td>
                    <td style="padding:6px;">
                        R$ {{ number_format($processo->detalhe->valor_total, 2, ',', '.') }}
                    </td>
                </tr>

                <!-- VIGÊNCIA -->
                @php
                    $vigencia = is_array($processo->detalhe->prazo_vigencia ?? null)
                        ? $processo->detalhe->prazo_vigencia
                        : ['12_meses'];

                    $outro_vigencia = $processo->detalhe->prazo_vigencia_outro ?? '________________.';

                    // Texto final da vigência
                    if (in_array('exercicio_financeiro', $vigencia)) {
                        $textoVigenciaTabela = "Até 31/12 do exercício financeiro da contratação";
                    } elseif (in_array('12_meses', $vigencia)) {
                        $textoVigenciaTabela = "12 meses";
                    } elseif (in_array('outro', $vigencia)) {
                        $textoVigenciaTabela = $outro_vigencia;
                    } else {
                        $textoVigenciaTabela = "________________";
                    }
                @endphp
                <tr>
                    <td style="padding:6px; font-weight:bold;">
                        VIGÊNCIA:
                    </td>
                    <td style="padding:6px;">
                        {{ $textoVigenciaTabela }}
                    </td>
                </tr>


                <!-- FONTE DOS RECURSOS -->
                <tr>
                    <td style="padding:6px; font-weight:bold;">
                        FONTE DOS RECURSOS:
                    </td>
                    <td style="padding:6px;">
                        {!! strip_tags($processo->detalhe->dotacao_orcamentaria) !!}
                    </td>
                </tr>

                <!-- FUNDAMENTAÇÃO LEGAL -->
                <tr>
                    <td style="padding:6px; font-weight:bold;">
                        FUNDAMENTAÇÃO LEGAL:
                    </td>
                    <td style="padding:6px; text-align:justify;">
                        Será regida pelas normas fixadas na Concorrência Eletrônica nº
                        {{ $processo->numero_procedimento }},
                        e pela Lei 14.133/21, de 1 de abril de 2021, e legislação posterior,
                        que o suplementam no que for omisso.
                    </td>
                </tr>

                <!-- ASSINATURA (CONTRATANTE) -->
                <tr>
                    <td style="padding:6px; font-weight:bold;">
                        ASSINATURA (CONTRATANTE):
                    </td>
                    <td style="padding:6px;">
                        {{ $primeiroAssinante['responsavel'] }}
                    </td>
                </tr>

                <!-- ASSINATURA (CONTRATADO) -->
                <tr>
                    <td style="padding:6px; font-weight:bold;">
                        ASSINATURA (CONTRATADO):
                    </td>
                    <td style="padding:6px;">
                        {{ $processo->detalhe->razao_social }}
                    </td>
                </tr>

                <!-- DATA DA ASSINATURA -->
                <tr>
                    <td style="padding:6px; font-weight:bold;">
                        DATA DA ASSINATURA:
                    </td>
                    <td style="padding:6px;">
                        {{ \Carbon\Carbon::parse($processo->contrato->data_assinatura_contrato)->translatedFormat('d \d\e F \d\e Y') }}
                    </td>
                </tr>

            </table>
        </div>

</body>

</html>
