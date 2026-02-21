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
            CONTRATO DE LOCAÇÃO IMÓVEL N° {{ $campos['numero_contrato'] }}
        </h4>

        <p style="text-align: justify">
            O Prefeitura Municipal de XXXXXXX, com sede no(a) XXXXXXXXXXXXXXXXXXX, inscrito(a) no CNPJ sob o nº XXXXXXXXXX, neste ato representado(a) pelo(a) XXXXXXXXXXXXXXXXXXX, inscrito no CPF nº XXXXXXXX, doravante designado simplesmente LOCATÁRIO, e do outro lado da avença, XXXXXXXXXXXXXX, inscrito no CPF nº XXXXXXXX, doravante denominada LOCADOR, celebram o presente CONTRATO DE LOCAÇÃO de Imóvel, mediante as seguintes cláusulas e condições que se seguem: 
        </p>
        
        <h6>
             FUNDAMENTO DO CONTRATO:
        </h6>
        <p style="text-align: justify">
            Este contrato decorre do Processo Inexigibilidade XXXXX/202X sendo autorizado pelo fundamentado em inexigibilidade de licitação, na forma do disposto no Artigo 74, V, da Lei Federal n° 14.133, de 1º de abril de 2021 e na Lei Federal n. 8.245/1991 e suas alterações posteriores, mediante as seguintes cláusulas e condições: 
        </p>
        <h6>
             1. CLÁUSULA PRIMEIRA – DO OBJETO.
        </h6>
        <p style="text-align: justify">
            1.1 Constitui objeto do presente contrato a XXXXXXXXXXXXXXXXXXXXX, de propriedade de XXXXXXXXXXXXXX.
        </p>
        <h6>
             2. CLÁUSULA SEGUNDA – DOS DEVERES E RESPONSABILIDADES DO LOCADOR. 
        </h6>
        <p style="text-align: justify">
            2.1. O LOCADOR obriga-se a:
            2.1.1 Entregar o imóvel em perfeitas condições de uso para os fins a que se destina; <br>
            2.1.2 Fornecer declaração atestando que não pesa sobre o imóvel qualquer impedimento de ordem jurídica capaz de colocar em risco a locação, ou, caso exista algum impedimento, prestar os esclarecimentos cabíveis, inclusive com a juntada da documentação pertinente, para fins de avaliação por parte da Administração;
            2.1.3 Garantir, durante o tempo da locação, o uso pacífico do imóvel;<br>
            2.1.4 Manter, durante a locação, a forma e o destino do imóvel;<br>
            2.1.5 Responder pelos vícios ou defeitos anteriores à locação;<br>
            2.1.6 Realizar, junto com o LOCATÁRIO, a vistoria do imóvel por ocasião da entrega das chaves, para fins de verificação minuciosa do seu estado, fazendo constar no Termo de Vistoria, parte integrante deste contrato, os eventuais defeitos existentes;<br>
            2.1.7 Responder pelos danos ao patrimônio do LOCATÁRIO decorrentes de seus atos, bem como de vícios e defeitos anteriores à locação, como desabamentos decorrentes de vícios redibitórios, incêndios provenientes de vícios pré-existentes na instalação elétrica etc;<br>
            2.1.8 Responder pelos débitos de qualquer natureza anteriores à locação;<br>
            2.1.9 Responder pelas obrigações tributárias incidentes sobre o imóvel, como impostos e taxas.<br>
            2.1.10 Responder pelas contribuições de melhoria incidentes sobre o imóvel, ante o disposto no art. 8º, §3º, do Decreto-Lei n. 195/67;<br>
            2.1.11. Fornecer ao LOCATÁRIO recibo discriminando as importâncias pagas, vedada a quitação genérica;<br>
            2.1.12. Pagar as taxas de administração imobiliária e de intermediações, se existirem;<br>
            2.1.13. Pagar as despesas extraordinárias de condomínio, se houver, entendidas como aquelas que não se refiram aos gastos rotineiros de manutenção do edifício, como, por exemplo:
            <br>
            <span style="margin-left: 20px;">
                a. obras de reformas ou acréscimos que interessem à estrutura integral do imóvel; <br>
                b . pintura das fachadas, empenas, poços de aeração e iluminação, bem como das esquadrias externas; <br>
                c. obras destinadas a repor as condições de habitabilidade do edifício;<br>
                d . indenizações trabalhistas e previdenciárias pela dispensa de empregados, ocorridas em data anterior ao início da locação; <br>
                e . instalação de equipamento de segurança e de incêndio, de telefonia, de intercomunicação, de esporte e de lazer; <br>
                f. despesas de decoração e paisagismo nas partes de uso comum; <br>
                g . constituição de fundo de reserva, e reposição deste, quando utilizado para cobertura de despesas extraordinárias;<br>
            </span>
            2.1.14. Entregar, em perfeito estado de funcionamento, os sistemas existentes (ar-condicionado, combate a incêndio, hidráulico, elétrica e outros porventura existentes);<br>
            2.1.15. Manter, durante a vigência do contrato, todas as condições de habilitação e qualificação exigidas para a contratação;<br>
            2.1.16. Notificar o LOCATÁRIO, com antecedência mínima de 90 (noventa) dias do término da vigência do contrato, quando não houver interesse em prorrogar a locação; <br>
            2.1.17. Exibir ao LOCATÁRIO, quando solicitado, os comprovantes relativos às parcelas que estejam sendo exigidas;<br>
            2.1.18. Pagar o prêmio de seguro complementar contrafogo; <br>
            2.1.19. Providenciar a atualização do Auto de Vistoria do Corpo de Bombeiros, se for o caso;<br>
            2.1.20. Informar ao LOCATÁRIO quaisquer alterações na titularidade do imóvel, inclusive com a apresentação da documentação correspondente.
        </p>
        <h6>
             3. CLÁUSULA TERCEIRA – DOS DEVERES E RESPONSABILIDADES DO LOCATÁRIO.
        </h6>
        <p style="text-align: justify">
            3.1 o LOCATÁRIO obriga-se a: <br>
            3.1.1 Pagar o aluguel e os encargos da locação exigíveis, no prazo estipulado neste contrato;<br>
            3.1.2 Servir-se do imóvel para o uso convencionado, compatível com a natureza deste e com o fim a que se destina, devendo conservá-lo como se seu fosse;<br>
            3.1.3 Realizar, junto com o LOCADOR a vistoria do imóvel, por ocasião da entrega das chaves, para fins de verificação minuciosa do estado do imóvel, fazendo constar no Termo de Vistoria fornecido pelo LOCADOR os eventuais defeitos existentes;<br>
            3.1.4 Manter o imóvel locado em condições de limpeza, de segurança e de utilização;<br>
            3.1.5 Restituir o imóvel, finda a locação, nas condições em que o recebeu, conforme documento de descrição minuciosa elaborado quando da vistoria para entrega, salvo os desgastes e deteriorações decorrentes do uso normal. Alternativamente, poderá repassar ao Locador, desde que aceito por este, a importância correspondente ao orçamento elaborado pelo setor técnico da Administração, para fazer face aos reparos e reformas ali especificadas;<br>
            3.1.6 Comunicar ao LOCADOR qualquer dano ou defeito cuja reparação a este incumba, bem como as eventuais turbações de terceiros;<br>
            3.1.7 Consentir com a realização de reparos urgentes, a cargo do LOCADOR, assegurando-se o direito ao abatimento proporcional do aluguel, caso os reparos durem mais de dez dias, nos termos do artigo 26 da Lei n° 8.245, de 1991;<br>
            3.1.8 Realizar o imediato reparo dos danos verificados no imóvel, ou nas suas instalações, provocados por seus agentes, funcionários ou visitantes autorizados;<br>
            3.1.9 Não modificar a forma externa ou interna do imóvel, sem o consentimento prévio e por escrito do LOCADOR;<br>
            3.1.10. Comunicar ao LOCADOR o surgimento de qualquer dano ou defeito cuja reparação a este incumba, bem como as eventuais turbações de terceiros;<br>
            3.1.11. Entregar imediatamente ao LOCADOR os documentos de cobrança de tributos e encargos condominiais, cujo pagamento não seja de seu encargo, bem como qualquer intimação, multa ou exigência de autoridade pública, ainda que direcionada ao LOCATÁRIO;<br>
            3.1.12. Pagar as despesas ordinárias de condomínio, se existentes, entendidas como aquelas necessárias à sua administração, como, por exemplo:<br>
            <span style="margin-left: 20px;">
                a. salários, encargos trabalhistas, contribuições previdenciárias e sociais dos empregados do condomínio; <br>
                b. consumo de água e esgoto, gás, luz e força das áreas de uso comum; <br>
                c. limpeza, conservação e pintura das instalações e dependências de uso comum;<br>
                d. manutenção e conservação das instalações e equipamentos hidráulicos, elétricos, mecânicos e de segurança, de uso comum;<br>
                e. manutenção e conservação das instalações e equipamentos de uso comum destinados à prática de esportes e lazer; <br>
                f. manutenção e conservação de elevadores, porteiro eletrônico e antenas coletivas; <br>
                g. pequenos reparos nas dependências e instalações elétricas e hidráulicas de uso comum; <br>
                h. rateios de saldo devedor, salvo se referentes a período anterior ao início da locação; <br>
                i. reposição do fundo de reserva, total ou parcialmente utilizado no custeio ou complementação de despesas ordinárias, salvo se referentes a período anterior ao início da locação.<br>
            </span>
            3.1.13. Pagar as despesas de telefone, energia elétrica, gás (se houver), água e esgoto;<br>
            3.1.14. Permitir a vistoria do imóvel pelo LOCADOR ou por seus mandatários, mediante prévia combinação de dia e hora, bem como admitir que seja visitado e examinado por terceiros, na hipótese de sua alienação, quando não possuir interesse no exercício do direito de preferência de aquisição (artigo 27 da Lei nº 8.245, de 1991);<br>
            3.1.15. Cumprir integralmente a convenção de condomínio e os regulamentos internos, se existentes.<br>
        </p>
        <h6>
             4. CLÁUSULA QUARTA – DAS BENFEITORIAS E CONSERVAÇÃO
        </h6>
        <p style="text-align: justify">
            4.1 O LOCATÁRIO poderá realizar todas as obras, modificações ou benfeitorias sem prévia autorização ou conhecimento do LOCADOR, sempre que a utilização do imóvel estiver comprometida ou na iminência de qualquer dano que comprometa a continuação do presente contrato;<br>
            4.1.1 As benfeitorias necessárias que forem executadas nessas situações serão posteriormente indenizadas pelo LOCADOR; <br>
            4.2 As benfeitorias úteis, desde que autorizadas, serão indenizáveis e permitem o exercício do direito de retenção;<br>
            4.2.1 Na impossibilidade da obtenção da prévia anuência do LOCADOR, é facultado ao LOCATÁRIO a realização da benfeitoria útil sempre que assim determinar o interesse público devidamente motivado;<br>
            4.2.2 As benfeitorias úteis não autorizadas pelo LOCADOR poderão ser levantadas pelo LOCATÁRIO, desde que sua retirada não afete a estrutura e a substância do imóvel.<br>
            4.3 As benfeitorias voluptuárias serão indenizáveis caso haja prévia concordância do LOCADOR;<br>
            4.3.1 Caso não haja concordância da indenização, poderão ser levantadas pelo LOCATÁRIO, finda a locação, desde que sua retirada não afete a estrutura e a substância do imóvel.<br>
            4.4 O valor de toda e qualquer indenização poderá ser abatido dos aluguéis, até integral ressarcimento, no limite estabelecido pelas partes, mediante termo aditivo.<br>
            4.5 Caso as modificações ou adaptações feitas pelo LOCATÁRIO venham a causar algum dano ao imóvel durante o período de locação, este dano deve ser sanado às expensas do LOCATÁRIO.<br>
            4.6. Finda a locação, será o imóvel devolvido ao LOCADOR, nas condições em que foi recebido pelo LOCATÁRIO, conforme documento de descrição minuciosa elaborado quando da vistoria para entrega, salvo os desgastes e deteriorações decorrentes do uso normal.<br>
        </p>

        <h6>
             5. CLÁUSULA QUINTA – DO PRAZO, PRORROGAÇÃO E RESTITUIÇÃO.
        </h6>
        <p style="text-align: justify">
            <br>5.1 O prazo do presente Contrato será de XXXXXXXXX, nos termos do art. 3° da Lei n. 8.245/91 a contar da data de sua assinatura.
            <br>5.2 Os efeitos financeiros da contratação só terão início a partir da data da entrega das chaves, que deverá ser precedida da assinatura do Termo de Vistoria do imóvel por ambas as partes.
            <br>5.3 O prazo de vigência poderá ser prorrogado, enquanto houver necessidade pública, por consenso entre as partes e mediante Termo Aditivo.
            <br>5.4 A prorrogação do prazo de vigência dependerá da comprovação pelo LOCATÁRIO de que o imóvel satisfaz os interesses estatais, da compatibilidade do valor de mercado e da anuência expressa do LOCADOR, mediante assinatura do termo aditivo.
            <br>5.5 Caso não tenha interesse na prorrogação, o LOCADOR deverá enviar comunicação escrita ao LOCATÁRIO, com antecedência mínima de 90 (noventa) dias (sugestão) da data do término da vigência do contrato, sob pena de aplicação das sanções cabíveis por descumprimento de dever contratual.
            <br>5.6. Sob nenhuma hipótese, o contrato terá duração por tempo indeterminado, haja vista o disposto 92, VII da Lei n. 14.133/2021.
        </p>
        <h6>
              6. CLÁUSULA SEXTA – DO PREÇO E DA FORMA DE PAGAMENTO.
        </h6>
        <p style="text-align: justify">
            <br>6.1 O MUNICÍPIO pagará ao LOCADOR o aluguel mensal no valor de R$ XXXXXXXXXXXXXX, perfazendo o valor global de R$ XXXXXXXXXXXXX.
            <br>6.2 O pagamento será efetuado, até o 10° (décimo) dia útil subsequente ao do vencimento, por meio de Ordem de Pagamento.
            <br>6.2.2 Sobre os valores das faturas não quitadas na data de seus respectivos vencimentos, incidirá juros de 0,5% (meio por cento) a.m., desde que solicitado pelo LOCADOR mediante comunicação escrita à Administração, constituindo-se por este ato a mora.
            <br>6.3 As despesas ordinárias do condomínio, bem como os encargos locatícios incidentes sobre o imóvel (água e esgoto, energia elétrica etc.), cujo pagamento tenha sido atribuído contratualmente ao LOCATÁRIO, serão suportadas proporcionalmente, em regime de rateio, a partir da data da efetiva ocupação do imóvel.
            <br>6.4 O acertamento desta proporção se dará na primeira parcela vencível da despesa, pagando LOCADOR e LOCATÁRIO suas respectivas partes da parcela. Caso o LOCATÁRIO a pague na integralidade, a parte de responsabilidade do LOCADOR será abatida no valor do aluguel do mês subsequente. A mesma proporção também será observada no encerramento do contrato, promovendo-se o acertamento preferencialmente no pagamento do último aluguel.
            <br>6.5 Quando do pagamento ao LOCADOR, será efetuada eventual retenção tributária prevista na legislação aplicável.
            <br>6.6 O LOCATÁRIO não se responsabilizará por qualquer despesa que venha a ser efetuada pelo LOCADOR, que porventura não tenha sido acordada no contrato.
        <h6>
            7. CLÁUSULA SÉTIMA – DO REAJUSTE.
        </h6>
        <p style="text-align: justify">
            <br>7.1 Será admitido o reajuste do preço do aluguel da locação com prazo de vigência igual ou superior a doze meses, mediante a aplicação do Índice de Preços para o Consumidor Amplo – IPCA, medido mensalmente pelo Instituto Brasileiro de Geografia e Estatística – IBGE, desde que seja observado o interregno mínimo de 1 (um) ano, contado da data da assinatura do contrato, para o primeiro reajuste, ou da data do último reajuste, para os subsequentes.
            <br>7.2 Se a variação do indexador adotado implicar em reajuste desproporcional ao preço médio de mercado para a presente locação, o LOCADOR aceitará negociar a adoção de preço compatível ao mercado de locação do município em que se situa o imóvel.
            <br>7.3 Caso o LOCADOR não solicite o reajuste até a data da prorrogação contratual, na pactuação do termo aditivo, ocorrerá a preclusão do direito, e nova solicitação só poderá ser pleiteada após o decurso de novo interregno mínimo de 1 (um) ano, contado na forma prevista neste contrato.
            <br>7.4 O reajuste será formalizado no mesmo instrumento de prorrogação da vigência do contrato, ou por apostilamento, caso realizado em outra ocasião.
        </p>
        <h6>
             8. CLÁUSULA OITAVA – DA DOTAÇÃO ORÇAMENTÁRIA.
        </h6>
        <p style="text-align: justify">
            8. 1 A presente despesa correrá à conta da Dotação Orçamentária: XXXXXXXXXXXXXXXXXXXXXX.
        </p>
        <h6>
             9. CLÁUSULA NONA – DA FISCALIZAÇÃO.
        </h6>
        <p style="text-align: justify">
            <br>9.1 A fiscalização do presente contrato será exercida por um representante da Administração, a ser nomeado mediante Portaria, ao qual competirá dirimir as dúvidas que surgirem no curso da execução do contrato e de tudo dará ciência à Administração.
            <br>9.1.1 A fiscalização de que trata esta cláusula não exclui nem reduz a responsabilidade do LOCADOR, inclusive perante terceiros, por qualquer irregularidade, ainda que resultante de imperfeições técnicas, vícios redibitórios, ou emprego de material inadequado ou de qualidade inferior e, na ocorrência desta, não implica em corresponsabilidade do LOCATÁRIO ou de seus agentes e prepostos.
            <br>9.1.2. O fiscal do contrato anotará em registro próprio todas as ocorrências relacionadas com a execução do contrato, indicando dia, mês e ano, bem como o nome das pessoas eventualmente envolvidas, determinando o que for necessário à regularização das faltas ou defeitos observados e encaminhando os apontamentos à autoridade competente para as providências cabíveis.
            <br>9.1.3. A gestão e fiscalização do contrato seguirão as disposições da Lei n. 14.133/21 e os atos normativos regulamentares correspondentes.
            <br>9.1.4. As decisões e providências que ultrapassarem a competência do fiscal do contrato deverão ser solicitadas a seus superiores em tempo hábil, para a adoção das medidas convenientes.
            <br>9.1.5. O LOCADOR poderá indicar um representante para representá-lo na execução do contrato.
        </p>
        <h6>
             10. CLÁUSULA DÉCIMA – DAS INFRAÇÕES E DAS SANÇÕES ADMINISTRATIVAS.
        </h6>
        <p style="text-align: justify">
            <br>10.1. A inexecução total ou parcial do contrato, ou o descumprimento de qualquer dos deveres elencados no contrato, sujeitará o LOCADOR, garantidos o contraditório e a ampla defesa, sem prejuízo da responsabilidade civil e criminal e nos moldes da Lei 14.133/2021 e do Decreto Municipal n. 110/2023, ou outro que venha a substituí-lo, às penalidades de: 	a. Advertência em razão do descumprimento, de pequena relevância, de obrigação legal ou infração à lei, quando não se justificar a aplicação de sanção mais grave ou inexecução parcial de obrigação contratual principal ou acessória de pequena relevância, quando não se justificar a aplicação de sanção mais grave;
            <br>b. Multa: 
            <br>b.1. Moratória de 0,1% por dia de atraso injustificado, sobre o valor mensal da contratação; 
            <br>b.2. Compensatória: entre 0,5% (cinco décimos por cento) até 30% (trinta por cento) sobre o valor total do contrato, no caso de inexecução parcial ou total do objeto; 
            <br>b.2.2. considera-se inexecução total do contrato o atraso superior a 30 (trinta) dias no cumprimento do prazo estabelecido no contrato ou entre as partes; 
            <br>b.2.3. A multa poderá ser descontada de pagamento eventualmente devido pela contratante decorrente de outros contratos firmados com a administração pública municipal. 
            <br>b.2.4. A aplicação de multa moratória não impedirá que a administração a converta em compensatória e promova a extinção unilateral do contrato cumulada de outras sanções previstas na Lei federal nº 14.133, de 2021.
            <br>c. Impedimento de licitar e contratar, pelo prazo de até três anos, a ser aplicada quando não se justificar a imposição de outra mais grave, àquele que:
            <br>I . Der causa à inexecução parcial do contrato, que supere a gravidade daquela prevista no inciso I do art. 155 da Lei federal nº 14.133/21, ou que cause grave dano à administração, ao funcionamento dos serviços públicos ou ao interesse coletivo; 
            <br>II. Der causa à inexecução total do contrato;
            <br>III. Não manter a proposta, salvo em decorrência de fato superveniente devidamente justificado;
            <br>IV. Ensejar o retardamento da execução ou da entrega do objeto da contratação sem motivo justificado.

        <br>d. Declaração de inidoneidade para licitar ou contratar com a Administração Pública, no caso de:
            <br>I . o LOCADOR apresentar declaração ou documentação falsa para a celebração do contrato ou em sua execução; 
            <br>II. o LOCADOR fraudar a ou praticar ato fraudulento na execução do contrato;
            <br>III. Comportar-se de modo inidôneo ou cometer fraude de qualquer natureza;
            <br>IV. Praticar atos ilícitos com vistas a frustrar os objetivos do contrato;
            <br>V. Praticar ato lesivo previsto no art. 5º da Lei Federal n. 12.846/2013

        <br>10.1.1.A penalidade de multa pode ser aplicada cumulativamente com as demais sanções. 
        <br>10.1.2. Na aplicação das sanções serão considerados:
        <br>I- a natureza e a gravidade da infração cometida; 
        <br>II- as peculiaridades do caso concreto; 
        <br>III- as circunstâncias agravantes ou atenuantes; 
        <br>IV- os danos que dela provierem para a Administração Pública; 

        <br>10.2 A aplicação de qualquer das penalidades previstas realizar-se-á em processo administrativo que assegurará o contraditório e a ampla defesa observando-se o procedimento previsto na Lei nº 14.133/2021, Decreto n. 110/2023, ou outro que vier a substituí-lo.
        <br>10.3. As multas devidas e/ou prejuízos causados ao LOCATÁRIO serão deduzidos dos valores a serem pagos, ou recolhidos em favor do Município, ou ainda, quando for o caso, serão inscritos na Dívida Ativa do Município e cobrados judicialmente.
        <br>10.4. As sanções aqui previstas são independentes entre si, podendo ser aplicadas isoladas ou, no caso das multas, cumulativamente, sem prejuízo de outras medidas cabíveis.
        </p>
        <h6>
             11. CLÁUSULA DÉCIMA PRIMEIRA – DA VINCULAÇÃO
        </h6>
        <p style="text-align: justify">
            
        </p>
        <h6>
             10. CLÁUSULA DÉCIMA – DAS INFRAÇÕES E DAS SANÇÕES ADMINISTRATIVAS.
        </h6>
        <p style="text-align: justify">
            11.1. Consideram-se integrantes do presente instrumento contratual, o ato que autorizou a contratação direta, a respectiva proposta e o termo de referência, independentemente de transcrição.
        </p>
        <h6>
             12. CLÁUSULA DÉCIMA SEGUNDA – DA ALTERAÇÃO DO CONTRATO
        </h6>
        <p style="text-align: justify">
            <br>12.1. Este contrato poderá ser alterado, mediante Termo Aditivo, para melhor adequação ao atendimento da finalidade de interesse público a que se destina e para os casos previstos neste instrumento, sendo assegurada ao LOCADOR a manutenção do equilíbrio econômico-financeiro do ajuste.
            <br>12.2. Caso, por razões de interesse público devidamente justificadas, o LOCATÁRIO decida devolver o imóvel e rescindir o contrato, antes do término do seu prazo de vigência, ficará dispensada do pagamento de qualquer multa, desde que notifique o LOCADOR, por escrito, com antecedência mínima de 30 (trinta) dias; 
            <br>12.2.1. Nesta hipótese, caso não notifique tempestivamente o LOCADOR, e desde que este não tenha incorrido em culpa, o LOCATÁRIO ficará sujeito ao pagamento de multa equivalente a 02 aluguéis, segundo proporção prevista no art. 4º da Lei 8.245, de 1991 e no art. 413 do Código Civil, considerando-se o prazo restante para o término da vigência do contrato;
            <br>12.3. Se, durante a locação, a coisa locada se deteriorar, sem culpa do LOCATÁRIO e o imóvel ainda servir para o fim a que se disponha, a este caberá pedir redução proporcional do valor da locação;
            <br>12.4. Durante o prazo estipulado para a duração do contrato, não poderá o LOCADOR reaver o imóvel locado (art. 4º da Lei Federal n. 8.245/1991)
        </p>
        <h6>
             13. CLÁUSULA DÉCIMA TERCEIRA – DA EXTINÇÃO CONTRATUAL.
        </h6>
        <p style="text-align: justify">
            <br>13.1 O LOCATÁRIO, no seu lídimo interesse, poderá extinguir este contrato, sem qualquer ônus, em caso de descumprimento total ou parcial de qualquer cláusula contratual ou obrigação imposta ao LOCADOR, sem prejuízo da aplicação das penalidades cabíveis. 
            <br>13.1.1 A extinção por descumprimento das cláusulas e obrigações contratuais acarretará a execução dos valores das multas e indenizações devidos ao LOCATÁRIO, bem como a retenção dos créditos decorrentes do contrato, até o limite dos prejuízos causados, além das sanções previstas neste instrumento.
            <br>13.2 Também constitui motivo para a extinção do contrato a ocorrência de qualquer das hipóteses enumeradas no artigo 137 da Lei nº 14.133, de 2021, que sejam aplicáveis a esta relação locatícia.
            <br>13.3 Nos casos em que reste impossibilitada a ocupação do imóvel, tais como incêndio, desmoronamento, desapropriação, caso fortuito ou força maior etc., o LOCATÁRIO poderá considerar o contrato rescindido imediatamente, ficando dispensada de qualquer prévia notificação, ou multa, desde que, nesta hipótese, não tenha concorrido para a situação.
            <br>13.4 O procedimento formal de extinção contratual terá início mediante notificação escrita, entregue diretamente ao LOCADOR, por via postal, com aviso de recebimento, ou endereço eletrônico.
            <br>13.5 Os casos da rescisão contratual serão formalmente motivados nos autos, assegurado o contraditório e a ampla defesa, e precedidos de autorização escrita e fundamentada da autoridade competente.
            <br>13.6 O termo de rescisão deverá indicar, conforme o caso:
            <br>13.6.1. Balanço dos eventos contratuais já cumpridos ou parcialmente cumpridos;
            <br>13.6.2. Relação dos pagamentos já efetuados e ainda devidos;
            <br>13.6.3 Indenizações e multas.
        </p>
        <h6>
             14. CLÁUSULA DÉCIMA QUARTA – DA PUBLICAÇÃO.
        </h6>
        <p style="text-align: justify">
            14.1 - Caberá ao LOCATÁRIO providenciar, por sua conta, a publicação resumida do Contrato no Portal Nacional de Contratações Públicas (PNCP), que é condição indispensável para a sua eficácia, conforme preceitua o art. 94 da Lei 14.133/2021.
        </p>
        <h6>
             15. CLÁUSULA DÉCIMA SEXTA – DAS DISPOSIÇÕES GERAIS.
        </h6>
        <p style="text-align: justify">
            <br>15.1 - Cadastrar o Contrato e respectivos aditivos no sistema do Tribunal de Contas do Estado do Piauí, em até 05 (cinco) dias úteis a contar da publicação oficial, com respectivo upload do arquivo correspondente, não se responsabilizando o MUNICÍPIO, se aqueles órgãos, por qualquer motivo, denegar-lhe aprovação.
            <br>15.2 Os casos omissos ou situações não explicitadas nas cláusulas deste contrato serão decididos pelo LOCATÁRIO, segundo as disposições contidas na Lei nº 8.245, de 1991, e na Lei nº 14.133, de 2021, subsidiariamente, bem como nos demais atos normativos correlatos, que fazem parte integrante deste contrato, independentemente de suas transcrições.
            <br>15.3 Este contrato continuará em vigor em qualquer hipótese de alienação do imóvel locado, na forma do artigo 8º da Lei nº 8.245, de 1991, ficando desde já autorizada a averbação deste instrumento na matrícula do imóvel junto ao Oficial de Registro de Imóveis competente.
            <br>15.4 O locador deverá utilizar das ferramentas digitais (Aplicativos, Sistemas Web, Sites, Portais) disponibilizados pelo Poder Executivo Municipal para lançamento das informações referente ao objeto da contratação, com a finalidade de acompanhamento, fiscalização e gestão das Obras e Contratos por parte do Poder Executivo Municipal. 
        </p>
        <h6>
             16. CLÁUSULA DÉCIMA OITAVA – DO FORO.
        </h6>
        <p style="text-align: justify">
            16.1 Fica eleito o foro do Município de XXXXXXXXX, com renúncia expressa a qualquer outro, por mais privilegiado que seja ou venha se tornar, para dirimir quaisquer questões que possam advir do presente Contrato.
            <br>
            E assim, por estarem assim justas e acordadas, após lido e achado conforme, as partes assinam o presente instrumento, em 03 (três) vias de igual teor e forma para um só efeito legal, na presença das testemunhas abaixo nominadas. 
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
                        {{ $processo->finalizacao->razao_social }}
                    </td>
                </tr>

                <!-- CNPJ -->
                <tr>
                    <td style="padding:6px; font-weight:bold;">
                        CNPJ (CONTRATADO):
                    </td>
                    <td style="padding:6px;">
                        {{ $processo->finalizacao->cnpj_empresa_vencedora }}
                    </td>
                </tr>

                <!-- VALOR -->
                <tr>
                    <td style="padding:6px; font-weight:bold;">
                        VALOR:
                    </td>
                    <td style="padding:6px;">
                        {{ $processo->finalizacao->valor_total }}
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
                        {{ $processo->finalizacao->representante_legal_empresa }}
                    </td>
                </tr>

                <!-- DATA DA ASSINATURA -->
                <tr>
                    <td style="padding:6px; font-weight:bold;">
                        DATA DA ASSINATURA:
                    </td>
                    <td style="padding:6px;">
                        {{ $dataAssinaturaFormatada }}
                    </td>
                </tr>

            </table>
        </div>

</body>

</html>
