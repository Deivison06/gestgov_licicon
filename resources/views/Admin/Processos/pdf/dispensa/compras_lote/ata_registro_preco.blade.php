<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>EDITAL - Processo {{ $processo->numero_processo ?? $processo->id }}</title>
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
    <div>
        @if ($detalhe->tipo_srp == 'sim')
        <p style="font-weight: bold; text-align: center;">ATA DE REGISTRO DE PREÇOS</p>
        <p style="font-weight: bold;">
            ATA DE REGISTRO DE PREÇOS Nº_____/2025<br>
            PREGÃO ELETRÔNICO SRP Nº ___/2025<br>
            PROCESSO ADMINISTRATIVO Nº _______________– XXXXXXXXXX-PI<br>
            VALIDADE: 12 (DOZE) MESES.
        </p>
        <p style="text-align: justify;">
            Aos _____ dias do mês de __________ do ano de dois mil e __________, na Rua
            XXXXXXX, nº XXX- - XXXXXXXX – Telefax: (0xx89) XXXXXX, na sede -----------------------
            -------, a Secretaria Municipal de ________________, do Município de XXXXXXXXXX-PI,
            representado por ________, portador do R.G nº ______ e inscrito no CPF sob nº
            ________, nomeado pela Portaria nº ________e as empresas qualificadas abaixo,
            jurídicos e legais, considerando o julgamento do PREGÃO ELETRÕNICO Nº ______,
            para o <span style="font-weight: bold;">REGISTRO DE PREÇO</span>, nº___/____,
            <span style="font-weight: bold;"> PROCESSO ADMINISTRATIVO Nº
                XXXXX/2025, RESOLVE REGISTRAR </span> os preços da empresa e quantidade cotada,
            atendendo as condições do edital, sujeitando-se as partes às normas constantes
            nana Lei nº 14.133, de 1º de abril de 2021 e em conformidade com as disposições
            a seguir:
        </p>

        <p style="font-weight: bold;">1. DO OBJETO</p>
        <p style="text-align: justify;">
            1.1. XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
            XXXXXXXXXX.
        </p>
        <p style="text-align: justify;">
            1.2. A Administração ou Gerenciamento da presente ata caberá à Secretaria
            Municipal de XXXXXXXXXXX.
        </p>
        <p style="font-weight: bold;">2. DOS PREÇOS, ESPECIFICAÇÕES E QUANTITATIVOS</p>
        <p style="text-align: justify;">
            2.1. O preço registrado, as especificações do objeto, as quantidades mínimas e
            máximas de cada item, fornecedor(es) e as demais condições ofertadas na(s)
            proposta(s) são as que seguem:
        </p>
        <table border="1" cellspacing="0" cellpadding="6" style="border-collapse: collapse; width: 100%; font-size: 9pt; border-color: #444;">
            <tr style="background-color: #f2f2f2;">
                <td colspan="6" style="font-weight: bold; text-align: center;">INFORMAÇÕES DA EMPRESA</td>
            </tr>
            <tr>
                <td style="font-weight: bold; width: 30%; background-color: #fafafa;">RAZÃO SOCIAL</td>
                <td style="width: 70%;"></td>
            </tr>
            <tr>
                <td style="font-weight: bold; background-color: #fafafa;">CNPJ</td>
                <td></td>
            </tr>
            <tr>
                <td style="font-weight: bold; background-color: #fafafa;">ENDEREÇO</td>
                <td></td>
            </tr>
            <tr>
                <td style="font-weight: bold; background-color: #fafafa;">TELEFONE</td>
                <td></td>
            </tr>
            <tr>
                <td style="font-weight: bold; background-color: #fafafa;">E-MAIL</td>
                <td></td>
            </tr>
            <tr>
                <td style="font-weight: bold; background-color: #fafafa;">REPRESENTANTE LEGAL</td>
                <td></td>
            </tr>

            <tr style="background-color: #f2f2f2;">
                <td colspan="6" style="font-weight: bold; text-align: center;">ITENS REGISTRADOS</td>
            </tr>
            <tr style="background-color: #e9e9e9; text-align: center;">
                <th style="width: 8%;">ITEM</th>
                <th style="width: 32%;">DESCRIÇÃO</th>
                <th style="width: 15%;">UND.</th>
                <th style="width: 15%;">QUANT.</th>
                <th style="width: 15%;">VALOR UNT.</th>
                <th style="width: 15%;">VALOR T.</th>
            </tr>
            <tr>
                <td style="text-align: center;">1</td>
                <td></td>
                <td style="text-align: center;"></td>
                <td style="text-align: right;"></td>
                <td style="text-align: right;"></td>
                <td style="text-align: right;"></td>
            </tr>
            <!-- Outras linhas de itens aqui -->
        </table>
        <p style="text-align: justify;">
            2.2. A listagem do cadastro de reserva referente ao presente registro de preços
            consta como anexo a esta Ata.
        </p>
        <p style="font-weight: bold;">
            3. ÓRGÃO(S) GERENCIADOR
        </p>

        <p style="text-align: justify;">
            3.1. O órgão gerenciador será o ......(nome do órgão)....
        </p>
        <p style="font-weight: bold;">
            4. DA ADESÃO À ATA DE REGISTRO DE PREÇOS
        </p>
        <p style="text-align: justify;">
            4.1. Durante a vigência da ata, os órgãos e as entidades que não participaram do
            procedimento de IRP poderão aderir à ata de registro de preços na condição de não
            participantes, observados os seguintes requisitos:
        </p>
        <p style="text-align: justify; padding-left: 30px;">
            4.1.1. apresentação de justificativa da vantagem da adesão, inclusive em
            situações de provável desabastecimento ou descontinuidade de serviço
            público;
        </p>
        <p style="text-align: justify; padding-left: 30px;">
            4.1.2. demonstração de que os valores registrados estão compatíveis com
            os valores praticados pelo mercado na forma do art. 23 da Lei nº 14.133, de
            2021; e
        </p>
        <p style="text-align: justify; padding-left: 30px;">
            4.1.3. consulta e aceitação prévias do órgão ou da entidade gerenciadora e
            do fornecedor.
        </p>
        <p style="text-align: justify;">
            4.2. A autorização do órgão ou entidade gerenciadora apenas será realizada
            após a aceitação da adesão pelo fornecedor.
        </p>
        <p style="text-align: justify; padding-left: 30px;">
            4.2.1. O órgão ou entidade gerenciadora poderá rejeitar adesões caso elas
            possam acarretar prejuízo à execução de seus próprios contratos ou à sua
            capacidade de gerenciamento.
        </p>
        <p style="text-align: justify;">
            4.3. Após a autorização do órgão ou da entidade gerenciadora, o órgão ou
            entidade não participante deverá efetivar a aquisição ou a contratação solicitada
            em até noventa dias, observado o prazo de vigência da ata.
        </p>
        <p style="text-align: justify;">
            4.4. O prazo de que trata o subitem anterior, relativo à efetivação da
            contratação, poderá ser prorrogado excepcionalmente, mediante solicitação do
            órgão ou da entidade não participante aceita pelo órgão ou pela entidade
            gerenciadora, desde que respeitado o limite temporal de vigência da ata de registro
            de preços.
        </p>
        <p style="text-align: justify;">
            4.5. O órgão ou a entidade poderá aderir a item da ata de registro de preços da
            qual seja integrante, na qualidade de não participante, para aqueles itens para os
            quais não tenha quantitativo registrado, observados os requisitos do item 4.1.
        </p>
        <p style="text-align: justify;">
            4.6. As aquisições ou contratações adicionais não poderão exceder, por órgão
            ou entidade, a cinquenta por cento dos quantitativos dos itens do instrumento
            convocatório registrados na ata de registro de preços para o órgão ou a entidade
            gerenciadora e para os órgãos ou as entidades participantes.
        </p>
        <p style="text-align: justify;">
            4.7. O quantitativo decorrente das adesões não poderá exceder, na totalidade,
            ao dobro do quantitativo de cada item registrado na ata de registro de preços para
            o órgão ou a entidade gerenciadora e os órgãos ou as entidades participantes,
            independentemente do número de órgãos ou entidades não participantes que
            aderirem à ata de registro de preços
        </p>
        <p style="font-weight: bold;">
            5. VALIDADE, FORMALIZAÇÃO DA ATA DE REGISTRO DE PREÇOS E CADASTRO RESERVA
        </p>
        <p style="text-align: justify;">
            5.1. A validade da Ata de Registro de Preços será de 1 (um) ano, contado a partir
            do primeiro dia útil subsequente à data de divulgação no PNCP, podendo ser
            prorrogada por igual período, mediante a anuência do fornecedor, desde que
            comprovado o preço vantajoso.
        </p>
        <p style="text-align: justify;">
            5.1.1. No ato de prorrogação da vigência da ata de registro de preços, haverá a
            renovação dos quantitativos registrados, até o limite do quantitativo original.
        </p>
        <p style="text-align: justify;">
            5.1.2. O contrato decorrente da ata de registro de preços terá sua vigência
            estabelecida no próprio instrumento contratual e observará no momento da
            contratação e a cada exercício financeiro a disponibilidade de créditos
            orçamentários, bem como a previsão no plano plurianual, quando ultrapassar 1
            (um) exercício financeiro.
        </p>
        <p style="text-align: justify;">
            5.1.3. Na formalização do contrato ou do instrumento substituto deverá haver a
            indicação da disponibilidade dos créditos orçamentários respectivos.
        </p>
        <p style="text-align: justify;">
            5.2. A contratação com os fornecedores registrados na ata será formalizada pelo
            órgão ou pela entidade interessada por intermédio de instrumento contratual,
            emissão de nota de empenho de despesa, autorização de compra ou outro
            instrumento hábil, conforme o art. 95 da Lei nº 14.133, de 2021.
        </p>
        <p style="text-align: justify;">
            5.2.1. O instrumento contratual de que trata o item 5.2 deverá ser assinado no
            prazo de validade da ata de registro de preços.
        </p>
        <p style="text-align: justify;">
            5.3. Os contratos decorrentes do sistema de registro de preços poderão ser
            alterados, observado o art. 124 da Lei nº 14.133, de 2021.
        </p>
        <p style="text-align: justify;">
            5.4. Após a homologação da licitação ou da contratação direta, deverão ser
            observadas as seguintes condições para formalização da ata de registro de preços:
        </p>
        <p style="text-align: justify;">
            5.4.1. Serão registrados na ata os preços e os quantitativos do adjudicatário,
            devendo ser observada a possibilidade de o licitante oferecer ou não proposta em
            quantitativo inferior ao máximo previsto no edital e se obrigar nos limites dela;
        </p>
        <p style="text-align: justify;">
            5.4.2. Será incluído na ata, na forma de anexo, o registro dos licitantes ou dos
            fornecedores que:
        </p>
        <p style="text-align: justify;">
            5.4.2.1. Aceitarem cotar os bens, as obras ou os serviços com preços iguais
            aos do adjudicatário, observada a classificação da licitação; e
        </p>
        <p style="text-align: justify;">
            5.4.2.2. Mantiverem sua proposta original.
        </p>
        <p style="text-align: justify;">
            5.4.3. Será respeitada, nas contratações, a ordem de classificação dos licitantes
            ou dos fornecedores registrados na ata.
        </p>
        <p style="text-align: justify;">
            5.5. O registro a que se refere o item 5.4.2 tem por objetivo a formação de
            cadastro de reserva para o caso de impossibilidade de atendimento pelo signatário
            da ata.
        </p>
        <p style="text-align: justify;">
            5.6. Para fins da ordem de classificação, os licitantes ou fornecedores que
            aceitarem reduzir suas propostas para o preço do adjudicatário antecederão
            aqueles que mantiverem sua proposta original.
        </p>
        <p style="text-align: justify;">
            5.7. A habilitação dos licitantes que comporão o cadastro de reserva a que se
            refere o item 5.4.2.2 somente será efetuada quando houver necessidade de
            contratação dos licitantes remanescentes, nas seguintes hipóteses:
        </p>
        <p style="text-align: justify;">
            5.7.1. Quando o licitante vencedor não assinar a ata de registro de preços, no prazo
            e nas condições estabelecidos no edital; e
        </p>
        <p style="text-align: justify;">
            5.7.2. Quando houver o cancelamento do registro do licitante ou do registro de
            preços nas hipóteses previstas no item 8.
        </p>
        <p style="text-align: justify;">
            5.8. O preço registrado com indicação dos licitantes e fornecedores será
            divulgado no PNCP e ficará disponibilizado durante a vigência da ata de registro de
            preços.
        </p>
        <p style="text-align: justify;">
            5.9. Após a homologação da licitação ou da contratação direta, o licitante mais
            bem classificado ou o fornecedor, no caso da contratação direta, será convocado
            para assinar a ata de registro de preços, no prazo e nas condições estabelecidos
            no edital de licitação ou no aviso de contratação direta, sob pena de decair o direito,
            sem prejuízo das sanções previstas na Lei nº 14.133, de 2021.
        </p>
        <p style="text-align: justify;">
            5.9.1. O prazo de convocação poderá ser prorrogado 1 (uma) vez, por igual período,
            mediante solicitação do licitante ou fornecedor convocado, desde que
            apresentada dentro do prazo, devidamente justificada, e que a justificativa seja
            aceita pela Administração.
        </p>
        <p style="text-align: justify;">
            5.10. A ata de registro de preços será assinada por meio de assinatura digital e
            disponibilizada no Sistema de Registro de Preços.
        </p>
        <p style="text-align: justify;">
            5.11. Quando o convocado não assinar a ata de registro de preços no prazo e nas
            condições estabelecidos no edital ou no aviso de contratação, e observado o
            disposto no item 5.7, observando o item 5.7 e subitens, fica facultado à
            Administração convocar os licitantes remanescentes do cadastro de reserva, na
            ordem de classificação, para fazê-lo em igual prazo e nas condições propostas pelo
            primeiro classificado.
        </p>
        <p style="text-align: justify;">
            5.12. Na hipótese de nenhum dos licitantes que trata o item 5.4.2.1, aceitar a
            contratação nos termos do item anterior, a Administração, observados o valor
            estimado e sua eventual atualização nos termos do edital, poderá:
        </p>
        <p style="text-align: justify;">
            5.12.1. Convocar para negociação os demais licitantes ou fornecedores
            remanescentes cujos preços foram registrados sem redução, observada a ordem
            de classificação, com vistas à obtenção de preço melhor, mesmo que acima do
            preço do adjudicatário; ou
        </p>
        <p style="text-align: justify;">
            5.12.2. Adjudicar e firmar o contrato nas condições ofertadas pelos licitantes
            ou fornecedores remanescentes, atendida a ordem classificatória, quando
            frustrada a negociação de melhor condição.
        </p>
        <p style="text-align: justify;">
            5.13. A existência de preços registrados implicará compromisso de fornecimento
            nas condições estabelecidas, mas não obrigará a Administração a contratar,
            facultada a realização de licitação específica para a aquisição pretendida, desde
            que devidamente justificada, e assegurado ao beneficiário do registro preferência
            de fornecimento ou contratação em igualdade de condições.
        </p>

        <p style="font-weight: bold;">
            6. ALTERAÇÃO OU ATUALIZAÇÃO DOS PREÇOS REGISTRADOS
        </p>
        <p style="text-align: justify;">
            6.1. Os preços registrados poderão ser alterados ou atualizados em decorrência
            de eventual redução dos preços praticados no mercado ou de fato que eleve o
            custo dos bens, das obras ou dos serviços registrados, nas seguintes situações:
        </p>
        <p style="text-align: justify;">
            6.1.1. Em caso de força maior, caso fortuito ou fato do príncipe ou em decorrência
            de fatos imprevisíveis ou previsíveis de consequências incalculáveis, que
            inviabilizem a execução da ata tal como pactuada, nos termos da alínea “d” do
            inciso II do caput do art. 124 da Lei nº 14.133, de 2021;
        </p>
        <p style="text-align: justify;">
            6.1.2. Em caso de criação, alteração ou extinção de quaisquer tributos ou
            encargos legais ou a superveniência de disposições legais, com comprovada
            repercussão sobre os preços registrados;
        </p>
        <p style="text-align: justify;">
            6.1.3. Na hipótese de previsão no edital ou no aviso de contratação direta de
            cláusula de reajustamento ou repactuação sobre os preços registrados, nos
            termos da Lei nº 14.133, de 2021.
        </p>
        <p style="text-align: justify;">
            6.1.3.1. No caso do reajustamento, deverá ser respeitada a contagem da
            anualidade e o índice previstos para a contratação;
        </p>
        <p style="text-align: justify;">
            6.1.3.2. No caso da repactuação, poderá ser a pedido do interessado,
            conforme critérios definidos para a contratação.
        </p>
        <p style="font-weight: bold;">
            7. NEGOCIAÇÃO DE PREÇOS REGISTRADOS
        </p>
        <p style="text-align: justify;">
            7.1. Na hipótese de o preço registrado tornar-se superior ao preço praticado no
            mercado por motivo superveniente, o órgão ou entidade gerenciadora convocará o
            fornecedor para negociar a redução do preço registrado.
        </p>
        <p style="text-align: justify;">
            7.1.1. Caso não aceite reduzir seu preço aos valores praticados pelo mercado, o
            fornecedor será liberado do compromisso assumido quanto ao item registrado,
            sem aplicação de penalidades administrativas.
        </p>
        <p style="text-align: justify;">
            7.1.2. Na hipótese prevista no item anterior, o gerenciador convocará os
            fornecedores do cadastro de reserva, na ordem de classificação, para verificar se
            aceitam reduzir seus preços aos valores de mercado e não convocará os licitantes
            ou fornecedores que tiveram seu registro cancelado.
        </p>
        <p style="text-align: justify;">
            7.1.3. Se não obtiver êxito nas negociações, o órgão ou entidade gerenciadora
            procederá ao cancelamento da ata de registro de preços, adotando as medidas
            cabíveis para obtenção de contratação mais vantajosa.
        </p>
        <p style="text-align: justify;">
            7.1.4. Na hipótese de redução do preço registrado, o gerenciador comunicará aos
            órgãos e às entidades que tiverem firmado contratos decorrentes da ata de registro
            de preços para que avaliem a conveniência e a oportunidade de diligenciarem
            negociação com vistas à alteração contratual, observado o disposto no art. 124 da
            Lei nº 14.133, de 2021.
        </p>
        <p style="text-align: justify;">
            7.2. Na hipótese de o preço de mercado tornar-se superior ao preço registrado e
            o fornecedor não poder cumprir as obrigações estabelecidas na ata, será facultado
            ao fornecedor requerer ao gerenciador a alteração do preço registrado, mediante
            comprovação de fato superveniente que supostamente o impossibilite de cumprir
            o compromisso.
        </p>
        <p style="text-align: justify;">
            7.2.1. Neste caso, o fornecedor encaminhará, juntamente com o pedido de
            alteração, a documentação comprobatória ou a planilha de custos que demonstre
            a inviabilidade do preço registrado em relação às condições inicialmente
            pactuadas.
        </p>
        <p style="text-align: justify;">
            7.2.2. Não hipótese de não comprovação da existência de fato superveniente que
            inviabilize o preço registrado, o pedido será indeferido pelo órgão ou entidade
            gerenciadora e o fornecedor deverá cumprir as obrigações estabelecidas na ata,
            sob pena de cancelamento do seu registro, nos termos do item 8.1, sem prejuízo
            das sanções previstas na Lei nº 14.133, de 2021, e na legislação aplicável.
        </p>
        <p style="text-align: justify;">
            7.2.3. Na hipótese de cancelamento do registro do fornecedor, nos termos do item
            anterior, o gerenciador convocará os fornecedores do cadastro de reserva, na
            ordem de classificação, para verificar se aceitam manter seus preços registrados,
            observado o disposto no item 5.7.
        </p>
        <p style="text-align: justify;">
            7.2.4. Se não obtiver êxito nas negociações, o órgão ou entidade gerenciadora
            procederá ao cancelamento da ata de registro de preços, nos termos do item 8.4, e
            adotará as medidas cabíveis para a obtenção da contratação mais vantajosa.
        </p>
        <p style="text-align: justify;">
            7.2.5. Na hipótese de comprovação da majoração do preço de mercado que
            inviabilize o preço registrado, conforme previsto no item 7.2 e no item 7.2.1, o órgão
            ou entidade gerenciadora atualizará o preço registrado, de acordo com a realidade
            dos valores praticados pelo mercado.
        </p>
        <p style="text-align: justify;">
            7.2.6. O órgão ou entidade gerenciadora comunicará aos órgãos e às entidades
            que tiverem firmado contratos decorrentes da ata de registro de preços sobre a
            efetiva alteração do preço registrado, para que avaliem a necessidade de alteração
            contratual, observado o disposto no art. 124 da Lei nº 14.133, de 2021.
        </p>
        <p style="font-weight: bold;">
            8. CANCELAMENTO DO REGISTRO DO LICITANTE VENCEDOR E DOS PREÇOS REGISTRADOS
        </p>
        <p style="text-align: justify;">
            8.1. O registro do fornecedor será cancelado pelo gerenciador, quando o fornecedor:
        </p>
        <div style="padding-left: 30px;">
            <p style="text-align: justify;">
                8.1.1. Descumprir as condições da ata de registro de preços, sem motivo
                justificado;
            </p>
            <p style="text-align: justify;">
                8.1.2. Não retirar a nota de empenho, ou instrumento equivalente, no prazo
                estabelecido pela Administração sem justificativa razoável;
            </p>
            <p style="text-align: justify;">
                8.1.3. Não aceitar reduzir seu preço, na hipótese deste se tornar superior
                àqueles praticados no mercado;
            </p>
            <p style="text-align: justify;">
                8.1.4. Não aceitar manter seu preço registrado;
            </p>
            <p style="text-align: justify;">
                8.1.5. Sofrer sanção prevista nos incisos III ou IV do caput do art. 156 da Lei
                nº 14.133, de 2021.
            </p>
            <div style="padding-left: 30px;">
                <p style="text-align: justify;">
                    8.1.5.1. Na hipótese de aplicação de sanção prevista nos incisos III ou
                    IV do caput do art. 156 da Lei nº 14.133, de 2021, caso a penalidade
                    aplicada ao fornecedor não ultrapasse o prazo de vigência da ata de
                    registro de preços, poderá o órgão ou a entidade gerenciadora,
                    mediante decisão fundamentada, decidir pela manutenção do registro
                    de preços, vedadas contratações derivadas da ata enquanto
                    perdurarem os efeitos da sanção.
                </p>
            </div>
        </div>
        <p style="text-align: justify;">
            8.2. O cancelamento de registros nas hipóteses previstas no item 8.1 será
            formalizado por despacho do órgão ou da entidade gerenciadora, garantidos os
            princípios do contraditório e da ampla defesa.
        </p>
        <p style="text-align: justify;">
            8.3. Na hipótese de cancelamento do registro do fornecedor, o órgão ou a
            entidade gerenciadora poderá convocar os licitantes que compõem o cadastro de
            reserva, observada a ordem de classificação.
        </p>
        <p style="text-align: justify;">
            8.4. O cancelamento dos preços registrados poderá ser realizado pelo
            gerenciador, em determinada ata de registro de preços, total ou parcialmente, nas
            seguintes hipóteses, desde que devidamente comprovadas e justificadas:
        </p>
        <p style="text-align: justify;">
            8.4.1. Por razão de interesse público, devidamente justificadas;
        </p>
        <p style="text-align: justify;">
            8.4.2. A pedido do fornecedor, por fato superveniente, decorrente de de força
            maior, caso fortuito ou fato do príncipe ou em decorrência de fatos imprevisíveis ou
            previsíveis de consequências incalculáveis, que inviabilizem a execução
            obrigações previstas na ata, devidamente demonstrado; ou
        </p>
        <p style="text-align: justify;">
            8.4.3. Se não houver êxito nas negociações, nas hipóteses em que o preço de
            mercado tornar-se superior ou inferior ao preço registrado, nos termos do artigos
            26, § 3º e 27, § 4º, ambos do Decreto nº 25.627, de 2024.
        </p>
        <p style="font-weight: bold;">
            9. DAS PENALIDADES
        </p>
        <p style="text-align: justify;">
            9.1. O descumprimento da Ata de Registro de Preços ensejará aplicação das
            penalidades estabelecidas no edital.
        </p>
        <p style="text-align: justify;">
            9.1.1. As sanções também se aplicam aos integrantes do cadastro de reserva no
            registro de preços que, convocados, não honrarem o compromisso assumido
            injustificadamente após terem assinado a ata.
        </p>
        <p style="font-weight: bold;">
            10. DO CONTRATO
        </p>
        <p style="text-align: justify;">
            10.1. Durante o prazo de validade do registro, as empresas detentoras poderão
            ser convidadas a firmar contratações mediante autorização da Prefeitura Municipal
            de xxxxxxxxxx, observadas as condições fixadas neste instrumento, no Edital e as
            determinações contidas na legislação pertinente.
        </p>
        <p style="text-align: justify;">
            10.2. O contrato para os serviços poderá ser representado pela Ordem de
            Serviços, Nota de Empenho, ou instrumento equivalente, sendo a sua celebração
            formalizada pelo recebimento ou retirada pela detentora da Ata de Registro de
            Preços, podendo ainda a Administração quando julgar conveniente, especialmente
            quando diante da necessidade de garantir os direitos e obrigações futuros, firmar
            contrato individual que possa resguardar no que tange às necessidades impostas
            para àquele contrato as partes em ajuste.
        </p>
        <p style="font-weight: bold;">
            11. CONDIÇÕES GERAIS
        </p>
        <p style="text-align: justify;">
            11.1. As condições gerais de execução do objeto, tais como os prazos para
            entrega e recebimento, as obrigações da Administração e do fornecedor registrado,
            penalidades e demais condições do ajuste, encontram-se definidos no Termo de
            Referência, ANEXO AO EDITAL.
        </p>
        <p style="text-align: justify;">
            11.2. No caso de adjudicação por preço global de grupo de itens, só será admitida
            a contratação de parte de itens do grupo se houver prévia pesquisa de mercado e
            demonstração de sua vantagem para o órgão ou a entidade.
        </p>
        <p style="text-align: justify;">
            11.3. Cada objeto a ser contratado deverá autorizado pelo titular da Prefeitura
            Municipal de xxxxxxxxxxx, depois de requeridos por seus órgãos ou unidades;
            Para firmeza e validade do pactuado, a presente Ata foi lavrada em .... (....) vias de
            igual teor, que, depois de lida e achada em ordem, vai assinada pelas partes e
            encaminhada cópia aos demais órgãos participantes.
        </p>
        <p style="text-align: center">
            Local e data <br><br>
            Assinaturas
        </p>
        <p style="text-align: center">
            Representante legal do órgão gerenciador e representante(s) legal(is) do(s)
            fornecedor(s) registrado(s)
        </p>
        @endif
    </div>
</body>

</html>
