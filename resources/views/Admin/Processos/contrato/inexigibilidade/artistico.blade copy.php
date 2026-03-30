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
            CONTRATO Nº {{ $processo->contrato->numero_contrato}}
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
                            {{ $processo->prefeitura->nome }}, com sede no(a)
                            {{ $processo->prefeitura->endereco }}, na cidade de {{ $processo->prefeitura->cidade }}
                            inscrito(a) no CNPJ
                            sob o nº {{ $processo->prefeitura->cnpj }}, neste ato representado(a) pelo(a)
                            {{ $processo->detalhe->responsavel }} inscrito no CPF sob n°
                            {{ $processo->detalhe->cpf_responsavel }}.
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
                            {{ $processo->detalhe->razao_social}}, inscrito(a) no CNPJ/MF sob o nº
                            {{ $processo->detalhe->cnpj_empresa_vencedora}}, sediado(a) na
                            {{ $processo->detalhe->endereco_empresa_vencedora}} neste
                            ato representado(a) por {{ $processo->detalhe->representante_legal_empresa}}, inscrito
                            no CPF sob n° {{ $processo->detalhe->cpf_representante}}.
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <p style="text-align: justify;">
            Tendo em vista o que consta no Processo administrativo n° {{ $processo->numero_processo }} e em observância às disposições da Lei
            n° 14.133, de 2021 e na Lei n° 8.078, de 1990 - Código de Defesa do Consumidor, resolvem celebrar o
            presente Termo de Contrato, decorrente da Inexigibilidade de licitação n° {{ $processo->numero_procedimento }}, mediante as cláusulas e
            condições a seguir enunciadas.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
                 alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">1. CLÁUSULA PRIMEIRA – DO OBJETO E REGIME DE EXECUÇÃO</h4>
        </div>
        @php
            $inicio = \Carbon\Carbon::parse($processo->detalhe->prazo_inicio_prestacao_servico);
            $fim = \Carbon\Carbon::parse($processo->detalhe->prazo_final_prestacao_servico);

            $duracaoMinutos = $inicio->diffInMinutes($fim);
        @endphp
        <p style="text-align: justify;">
            1.1.{!! strip_tags($processo->objeto) !!} <br>
            1.2. Todos os termos do Termo de Referência e da proposta da contratada integram o presente contrato em
            todas as suas condições.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
                 alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">2. CLÁUSULA SEGUNDA - DA EXECUÇÃO DO CONTRATO</h4>
        </div>
        <p style="text-align: justify;">
            2.1. Os serviços serão executados em conformidade com a proposta apresentada pela CONTRATADA, vez
            compõe, em todos os seus termos, o processo administrativo n° {{ $processo->numero_processo }} e inexigibilidade de licitação
            {{ $processo->numero_procedimento }}.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
                 alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">3. CLÁUSULA TERCEIRA - DO PRAZO</h4>
        </div>
        <p style="text-align: justify;">
            3.1. O prazo de vigência deste Termo de Contrato tem início na data de {{ \Carbon\Carbon::parse($processo->detalhe->prazo_inicio_prestacao_servico)->format('d/m/Y') }} e encerramento em
            {{ \Carbon\Carbon::parse($processo->detalhe->prazo_final_prestacao_servico)->format('d/m/Y') }}, sendo que em caso de eventual necessidade de prorrogação, decorrente de acordo entre as
            partes, será formalizado o respectivo Aditivo contratual.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
                 alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">4. CLÁUSULA QUARTA - DO PREÇO E FORMA DE PAGAMENTO</h4>
        </div>
        <p style="text-align: justify;">
            4.1. O valor do presente Termo de Contrato é de R$  {{ $detalhe->valor_estimado }};
            4.2. No valor acima estão incluídas todas as despesas ordinárias diretas e indiretas decorrentes da execução
            contratual, inclusive tributos e/ou impostos, encargos sociais, trabalhistas, previdenciários, fiscais e comerciais
            incidentes, taxa de administração, frete, seguro e outros necessários ao cumprimento integral do objeto da
            contratação, ressalvado o que for de responsabilidade do Contratante conforme Cláusula 9ª;
            4.3. Os preços são fixos e irreajustáveis;
            4.4. O pagamento será efetuado mediante apresentação de Fatura / Nota Fiscal, em 02 (duas) vias que deverá
            ser apresentada ao titular da Secretaria de Finanças para a devida aprovação.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
                 alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">5. CLÁUSULA QUINTA - DA DOTAÇÃO ORÇAMENTÁRIA</h4>
        </div>
        <p style="text-align: justify;">
            5.1. A Dotação orçamentária que correrá tal despesa é: {!! strip_tags($processo->detalhe->dotacao_orcamentaria) !!}, conforme disposto na Lei de meios vigente.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
                 alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">6. CLÁUSULA SEXTA - DAS ALTERAÇÕES</h4>
        </div>
        <p style="text-align: justify;">
            6.1. Eventuais alterações contratuais reger-se-ão pela disciplina do art. 124 da Lei n° 14.133 de 2021; A
            CONTRATADA é obrigada a aceitar, nas mesmas condições contratuais, os acréscimos ou supressões que se
            fizerem necessários, até o limite de 25% (vinte e cinco por cento) do valor inicial atualizado do contrato;
            6.2. As supressões resultantes de acordo celebrado entre as partes contratantes poderão exceder o limite de
            25% (vinte e cinco por cento) do valor inicial atualizado do contrato.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
                 alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">7. CLÁUSULA SÉTIMA - FISCALIZAÇÃO</h4>
        </div>
        <p style="text-align: justify;">
            7.1. A fiscalização da execução do objeto será efetuada por Representante designado pela Secretaria solicitante.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
                 alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">8. CLÁUSULA OITAVA - OBRIGAÇÕES DO CONTRATANTE</h4>
        </div>
        <p style="text-align: justify;">
            8.1. A CONTRATADA obriga-se a:
            8. 1.1. Executar a apresentação artística conforme condições, prazos e especificações constantes neste
            Termo de Referência, no contrato e em sua proposta; <br>
            8.1.2. Comparecer ao local do evento com antecedência mínima acordada, observando a pontualidade e os
            requisitos logísticos estabelecidos pela Administração Pública;<br>
            8.1.3. Garantir a realização da apresentação com os artistas originalmente contratados, salvo motivo de força
            maior, devidamente justificado e aceito pela Administração;<br>
            8.1.4. Fornecer, quando solicitado, informações técnicas necessárias à realização da apresentação, tais
            como: ficha técnica, riders de som, mapa de palco, lista de necessidades e outras condições indispensáveis;<br>
            8.1.5. Arcar com quaisquer encargos trabalhistas, previdenciários, fiscais, securitários e outros decorrentes de
            sua atividade e da equipe envolvida na execução da apresentação, isentando a Administração Pública de
            qualquer responsabilidade solidária ou subsidiária;<br>
            8. 1.6. Responder por danos causados à Administração ou a terceiros em decorrência de ações ou omissões
            dolosas ou culposas no cumprimento do contrato;<br>
            8.1.7. Cumprir todas as normas de segurança, saúde e meio ambiente relativas ao evento;<br>
            8.1.8. Não subcontratar, total ou parcialmente, o objeto da contratação sem autorização prévia e expressa da
            Administração;<br>
            8.1.9. Manter, durante toda a execução contratual, as condições de habilitação exigidas, especialmente
            quanto à exclusividade e à consagração do artista ou grupo artístico;<br>
            8.2. A CONTRATADA cede à Administração, para fins institucionais e sem fins lucrativos, o direito de uso da
            imagem, som e nome artístico da atração, apenas para fins de divulgação do evento, respeitada a legislação
            aplicável sobre direitos autorais e de imagem;<br>
            8.3. Os direitos autorais da solução, do projeto, de suas especificações técnicas, da documentação produzida
            e congêneres, e de todos os demais produtos gerados na execução do contrato, inclusive aqueles produzidos
            por terceiros subcontratados, ficando proibida a sua utilização sem que exista autorização expressa da
            Contratante, sob pena de multa, sem prejuízo das sanções civis e penais cabíveis;<br>
            8.4. O não cumprimento das obrigações assumidas poderá implicar na aplicação das penalidades previstas na
            Lei nº 14.133/2021 e no contrato, sem prejuízo da rescisão contratual.<br>
            8.5. Os serviços serão executados pela CONTRATADA na forma descrita no Termo de Referência;<br>
            8.6. Os termos indicados na proposta vinculam a referida contratação;<br>
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
                 alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">9. CLÁUSULA NONA - OBRIGAÇÕES DA CONTRATANTE</h4>
        </div>
        <p style="text-align: justify;">
            9.1. A CONTRATANTE obriga-se a: 
            <br>9.1.1 Disponibilizar as condições técnicas, operacionais e logísticas necessárias para a plena execução da apresentação artística, conforme as especificações estabelecidas neste Termo de Referência e no contrato;
            <br>9.1.2. Prestar os esclarecimentos necessários à CONTRATADA quanto ao local, data e condições do evento, com a antecedência mínima necessária para viabilizar a preparação técnica da apresentação;
            <br>9.1.3. Garantir o acesso dos artistas e equipe técnica ao local da apresentação, inclusive em horários compatíveis com montagem, passagem de som e outras necessidades previstas pela CONTRATADA;
            <br>9.1.4. Fornecer as estruturas previamente pactuadas, como palco, som, iluminação, camarim, segurança, energia elétrica, pontos de acesso e demais itens técnicos acordados;
            <br>9.1.5. Proceder ao pagamento dos valores contratados, conforme estabelecido no contrato e nas condições da proposta aprovada, desde que cumpridas as exigências de documentação fiscal e contratual;
            <br>9.1.6. Designar servidor responsável pelo acompanhamento da execução contratual e, se necessário, por atestar a realização da apresentação para fins de liberação do pagamento.
            <br>9.2. Informar à CONTRATADA sobre eventuais alterações no cronograma ou na estrutura do evento com antecedência razoável, desde que possível, buscando evitar prejuízos à execução do objeto contratado.
            <br>9.3. fiscalizar mensalmente, por amostragem, o cumprimento das obrigações trabalhistas, previdenciárias e para com o FGTS, especialmente:
            <br>9.3.1. A concessão de férias remuneradas e o pagamento do respectivo adicional, bem como de auxílio-transporte, auxílio-alimentação e auxílio-saúde, quando for devido;
            <br>9.3.2. O recolhimento das contribuições previdenciárias e do FGTS dos empregados que efetivamente participem da execução dos serviços contratados, a fim de verificar qualquer irregularidade:
            <br>9.3.3. O pagamento de obrigações trabalhistas e previdenciárias dos empregados dispensados até a data da extinção do contrato;
            <br>9.4. Analisar os termos de rescisão dos contratos de trabalho do pessoal empregado na prestação dos serviços no prazo de 30 (trinta) dias, prorrogável por igual período, após a extinção ou rescisão do contrato; 
            <br>9.5. Fornecer por escrito às informações necessárias para o desenvolvimento dos serviços objeto do
            <br>contrato;
            <br>9.6. Realizar avaliações periódicas da qualidade dos serviços, após seu recebimento;
            <br>9.7. Cientificar o órgão de representação judicial do município para adoção das medidas cabíveis quando do descumprimento das obrigações pela Contratada;
            <br>9.8. Arquivar, entre outros documentos, projetos, "as built", especificações técnicas, orçamentos, termos de recebimento, contratos e aditamentos, relatórios de inspeções técnicas após o recebimento do serviço e notificações expedidas;
            <br>9.9. Assegurar que o ambiente de trabalho, inclusive seus equipamentos e instalações, apresentem condições adequadas ao cumprimento, pela contratada, das normas de segurança e saúde no trabalho, quando o serviço for executado em suas dependências, ou em local por ela designado;
            <br>9.10. Caberá exclusivamente à CONTRATANTE a organização e liberação da realização do espetáculo junto a todos os órgãos públicos e entidades de classe, bem como junto às autoridades locais, inclusive o pagamento de todos e quaisquer impostos, taxas e contribuições de qualquer espécie ou natureza devidos, por força de Lei, a todos e quaisquer órgãos Municipais, Estaduais ou Federais, inclusive do ECAD (Escritório Central de Arrecadação de Direitos Autorais ou órgão similar, com antecedência de 05 (cinco) dias da data prevista para a realização da apresentação artística a que se refere o presente instrumento, bem como a obtenção de todas as licenças e alvarás necessários, inclusive junto ao Juizado de Menores, aos Órgãos de Censura de Diversões Públicas, das instituições arrecadadoras de direitos autorias, associadas ou independentes e a todas as demais entidades que possam interferir na realização ou no resultado da apresentação musical, e qualquer outra obrigação devida, seja de natureza fiscal, previdenciária, de direitos autorais ou qualquer outra,  além de respeitar todas as normas de ordem pública para organização e realização do evento, em especial Polícia Militar e Corpo de Bombeiros bem como o pagamento de direitos autorais, se o caso;
            <br>9.11. Arcar com todas as despesas para a realização do evento, tais como, mas não limitadas a estas: palco, iluminação, sonorização, publicidade, segurança dos músicos, bem como do público presente, respeitando a orientação dos órgãos públicos, em especial Polícia Militar e Corpo de Bombeiros no tocante à razão número de seguranças x número de pessoas presentes, e espaço mínimo de segurança, entre o palco e o público, de 2 metros, isolado por disciplinadores ou equipamento equivalente que impeça o público de ficar muito próximo ao palco, sendo tal espaço reservado para seguranças do evento;
            <br>9.12. Informar com exatidão o estado do local onde o evento será realizado, respeitando a capacidade do mesmo, bem como as demais condições de segurança e saúde exigidas pelo Poder Público, todas as exigidas e que se fizerem necessárias, enviando fotografias ou vídeos;
            <br>9.13. Arcar com todo e qualquer prejuízo oriundo de demanda judicial, cuja causa seja o presente instrumento, seja de natureza indenizatória, trabalhista, tributária, previdenciária ou qualquer outra área do ramo do direito, isentando, em qualquer hipótese, a CONTRATADA de qualquer responsabilidade, garantindo-lhe o direito de regresso, bem como a devolução de toda e qualquer despesa havidas até a sua exclusão da lide ou término do processo, salvo se a causa for comprovadamente de responsabilidade da CONTRATADA, ou se tratar de caso fortuito ou força maior, nos termos da legislação civil;
            <br>9.14. Caso os equipamentos fornecidos pela CONTRATANTE, ou qualquer outro item da produção, tais como, mas não limitados a estes, sonorização, iluminação, palco, projeção, cenário, equipe de montagem e desmontagem ou qualquer outro item, estiver em desacordo com o disposto no presente instrumento ou em seus anexos, prejudicando, dessa forma, a apresentação, a CONTRATADA poderá, sem qualquer ônus para si, descumprir o disposto neste contrato, sem prejuízo de a CONTRATANTE honrar com o disposto na Cláusula 4ª deste pacto;
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
                 alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">10. CLÁUSULA DÉCIMA - DAS PENALIDADES E SANÇÕES ADMINISTRATIVAS</h4>
        </div>
        <p style="text-align: justify;">
            10.1. Pela inexecução total ou parcial do objeto do CONTRATO, o Município poderá aplicar a CONTRATADA multa de até 5% (cinco por cento) do valor do contrato, sem prejuízo das demais penalidades previstas na Lei 14.133/21, inclusive responsabilização civil e penal na forma da Legislação específica;
            <br>10.2. Além da multa prevista ficam estabelecidas as penas de advertência, rescisão de contrato, declaração de inidoneidade e suspensão do direito de licitar e contratar com o MUNICÍPIO, que serão aplicadas em função da natureza e gravidade da falta cometida, garantida a ampla defesa.
            <br>10.3. O MUNICÍPIO reterá dos créditos decorrentes deste Contrato valores suficientes ao pagamento das multas aplicadas.
            <br>10.4. Nenhum pagamento será efetuado à CONTRATADA sem a quitação das multas aplicadas em definitivo.
            <br>10.5. Não será considerada inadimplente a CONTRATADA, ficando isenta do pagamento de qualquer multa ou indenização à CONTRATANTE, nas seguintes hipóteses:
            <br>a)  Caso fortuito ou força maior, nos termos da legislação civil, aí compreendido eventos da natureza, tempestade com desmoronamento de barreira, falta de condição de pouso, black-out, ato de autoridade ou qualquer fato imprevisível e invencível capaz de impedir o comparecimento dos vocalistas, músicos, funcionários e equipamentos de propriedade da CONTRATADA.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
                 alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">11. CLÁUSULA DÉCIMA PRIMEIRA - DA RESCISÃO</h4>
        </div>
        <p style="text-align: justify;">
            11.1. O presente Termo de Contrato poderá ser rescindido nas hipóteses previstas no art. 137 da Lei n° 14.133, de 2021, sem prejuízo das sanções aplicáveis, bem como amigavelmente, assegurados o contraditório e a ampla defesa, mediante distrato assinado pelas partes e confirmado por duas testemunhas. Nessa hipótese, não haverá qualquer ônus para as partes, ficando isentas quanto ao pagamento de indenização por danos materiais e morais eventualmente experimentados.
            <br>11.2. É admissível a fusão, cisão ou incorporação da contratada com/em outra pessoa jurídica, desde que sejam observados pela nova pessoa jurídica todos os requisitos de habilitação exigidos na licitação original; sejam mantidas as demais cláusulas e condições do contrato; não haja prejuízo à execução do objeto pactuado e haja a anuência expressa da Administração à continuidade do contrato;
            <br>11.3. Os casos de rescisão contratual serão formalmente motivados, assegurando-se à CONTRATADA o direito à prévia e ampla defesa;
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
                 alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">12. CLÁUSULA DÉCIMA SEGUNDA - DOS CASOS OMISSOS</h4>
        </div>
        <p style="text-align: justify;">
            2.1. Os casos omissos serão decididos pela CONTRATANTE, segundo as disposições contidas na Lei n° 14.133, de 2021, e demais normas federais de licitações e contratos administrativos e, subsidiariamente, segundo as disposições contidas na Lei n° 8.078, de 1990 - Código de Defesa do Consumidor - e normas e princípios gerais dos contratos.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
                 alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">13. CLÁUSULA DÉCIMA TERCEIRA - DA FUNDAMENTAÇÃO LEGAL E PUBLICAÇÃO</h4>
        </div>
        <p style="text-align: justify;">
            13.1. O presente Contrato tem embasamento legal no artigo 74, inciso II da Lei 14.133, de 2021.
            <br>
            13.2. É de responsabilidade da CONTRATANTE a publicação legal do instrumento.
        </p>
        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
                 alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">14. CLÁUSULA DÉCIMA QUARTA - DO FORO</h4>
        </div>
        <p style="text-align: justify;">
            14.1. Declaram as partes expresso CONSENTIMENTO que serão coletados, tratados e compartilhados os dados necessários ao cumprimento do contrato, nos termos do Art. 7º, inc. V da LGPD, seja os dados necessários para cumprimento de obrigações legais, nos termos do Art. 7º, inc. II da LGPD, bem como os dados, se necessários para proteção ao crédito, conforme autorizado pelo Art. 7º, inc. V da LGPD, sendo que outros dados poderão ser coletados, mediante termo de consentimento específico.
            <br>
            14.2. As partes e as testemunhas envolvidas neste instrumento afirmam e declaram que o mesmo será assinado eletronicamente, com fundamento no Artigo 10, parágrafo 2º da MP 2200-2/2001, e do Artigo 6º do Decreto 10.278/2020, sendo as assinaturas consideradas válidas, vinculantes e executáveis, desde que firmadas pelos representantes legais das partes, conforme estabelecido no preâmbulo. Consigna-se, ainda, no presente instrumento, que a assinatura com Certificado Digital/eletrônica tem a mesma validade jurídica de um registro e autenticação feita em Cartório, seja mediante utilização de certificados e-CPF, e-CNPJ e/ou NF-e. Assim, as partes renunciam à possibilidade de exigir a troca, envio ou entrega das vias originais (não-eletrônicas) assinadas do instrumento, bem como renunciam ao direito de recusar ou contestar a validade das assinaturas eletrônicas, na medida máxima permitida pela legislação aplicável.
        </p>
        <p style="text-align: justify">
            15.1. Fica eleito o foro da Comarca de {{ $processo->contrato->comarca}} como único e competente para dirimir quaisquer demandas do presente contrato, por mais privilegiado que outro possa ser.
            <br>
            15.2. E por estarem justos e contratados firmam o presente em 02 (duas) vias de igual teor e forma para que produzam os efeitos legais.
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
                        {{ $processo->detalhe->razao_social }} <br>
                        {{ $processo->detalhe->representante_legal_empresa}} <br>
                        {{ $processo->detalhe->cnpj_empresa_vencedora}} <br>
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
                        EXTRATO DO CONTRATO Nº {{ $processo->contrato->numero_extrato }}<br>
                        PROCESSO ADMINISTRATIVO Nº {{ $processo->numero_processo }}<br>
                        MODALIDADE: INEXIGIBILIDADE Nº {{ $processo->numero_procedimento }}
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
                        R$ {{ $detalhe->valor_estimado }}
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
                        Será regida pelas normas fixadas na Inexigibilidade nº
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
                         {{ $processo->detalhe->representante_legal_empresa }}
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
