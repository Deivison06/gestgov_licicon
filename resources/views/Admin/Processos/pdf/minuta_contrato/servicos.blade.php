<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Minuta do Contrato - Processo {{ $processo->numero_processo ?? $processo->id }}</title>
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
            font-size: 10.5pt;
            font-family: 'Aptos', sans-serif;
            background-image: url('{{ public_path($prefeitura->timbre) }}');
            background-repeat: no-repeat;
            background-position: top left;
            background-size: cover;
            text-align: justify;
            text-justify: inter-word;
            line-height: 1.3;
        }

        .page-break {
            page-break-after: always;
        }

        #cover-page {
            height: 100vh;
            width: 100%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }

        .cover-image {
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

        .section {
            margin-bottom: 12px;
        }

        .clausula-titulo {
            margin: 18px 0 8px;
        }

        .clausula-titulo img {
            width: 22px;
            vertical-align: middle;
        }

        .clausula-titulo h4 {
            display: inline-block;
            margin: 0 0 0 8px;
            vertical-align: middle;
            font-size: 12pt;
        }

        table.itens {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        table.itens th,
        table.itens td {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 9.5pt;
        }

        table.dotacao {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        table.dotacao td {
            border: 1px solid #000;
            padding: 6px 8px;
        }

        .signature-block {
            margin-top: 60px;
            text-align: center;
        }
    </style>
</head>

<body>

    {{-- ====================================================================== --}}
    {{-- CAPA --}}
    {{-- ====================================================================== --}}
    <div id="cover-page">
        <img src="{{ public_path('icons/capa-documento.png') }}" alt="Minuta do Contrato" class="cover-image">
        <div class="cover-title">
            MINUTA DO CONTRATO
        </div>
    </div>

    <div class="page-break"></div>

    {{-- ====================================================================== --}}
    {{-- QUALIFICAÇÃO DAS PARTES --}}
    {{-- ====================================================================== --}}
    <h4 style="text-align: center;">
        CONTRATO ADMINISTRATIVO Nº ......../......, QUE FAZEM ENTRE SI A {{ strtoupper($prefeitura->nome) }} E
        ..............................................................
    </h4>

    <div class="section">
        <table>
            <tr>
                <td class="icon" style="width: 40px;">
                    <img src="{{ public_path('icons/mao.png') }}" width="35">
                </td>
                <td>
                    <div style="font-weight: bold; margin-bottom: 3px;">Contratante</div>
                    <div>
                        {{ $prefeitura->nome }}, com sede no(a) {{ $prefeitura->endereco }}, na cidade de
                        {{ $prefeitura->cidade }}, inscrito(a) no CNPJ sob o nº {{ $prefeitura->cnpj }}, neste ato
                        representado(a) pelo(a) {{ $prefeitura->autoridade_competente }}, inscrito no CPF sob n°
                        ......................... e portador da Cédula de Identidade n° .........................
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <table>
            <tr>
                <td class="icon" style="width: 40px;">
                    <img src="{{ public_path('icons/mao.png') }}" width="35">
                </td>
                <td>
                    <div style="font-weight: bold; margin-bottom: 3px;">Contratado</div>
                    <div>
                        ....................................................................., inscrito(a) no
                        CNPJ/MF sob o nº ........................., sediado(a) na
                        ....................................................................., neste ato
                        representado(a) por ....................................................................,
                        inscrito no CPF sob n° ......................... e portador da Cédula de Identidade n°
                        .........................
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <p>
        Processo Administrativo nº {{ $processo->numero_processo }} — {{ $processo->modalidade->getDisplayName() }}
        nº {{ $processo->numero_procedimento }}.
    </p>

    {{-- ====================================================================== --}}
    {{-- CLÁUSULA PRIMEIRA — DO OBJETO --}}
    {{-- ====================================================================== --}}
    <div class="clausula-titulo">
        <img src="{{ public_path('icons/descricao-necessidade.png') }}" alt="Objeto">
        <h4>1. CLÁUSULA PRIMEIRA – DO OBJETO</h4>
    </div>
    <p>
        1.1. O objeto do presente instrumento é a contratação de {!! strip_tags($processo->objeto) !!}, nas condições
        estabelecidas no Termo de Referência, conforme itens abaixo:
    </p>

    <table class="itens">
        <tr>
            <th>Item</th>
            <th>Especificação</th>
            <th>Unidade de Medida</th>
            <th>Quantidade</th>
            <th>Valor Unitário</th>
            <th>Valor Total</th>
        </tr>
        @forelse ($itens as $item)
        <tr>
            <td>{{ $item['numero'] ?? '' }}</td>
            <td>{{ $item['descricao'] ?? '' }}</td>
            <td>{{ $item['und'] ?? '' }}</td>
            <td>{{ $item['quantidade'] ?? '' }}</td>
            <td></td>
            <td></td>
        </tr>
        @empty
        <tr>
            <td colspan="6">.....................................................................</td>
        </tr>
        @endforelse
    </table>

    <p>
        1.2. Vinculam esta contratação, independentemente de transcrição:<br>
        1.2.1. O Termo de Referência;<br>
        1.2.2. O Edital da Licitação;<br>
        1.2.3. A Proposta do contratado;<br>
        1.2.4. Eventuais anexos dos documentos supracitados.
    </p>

    {{-- ====================================================================== --}}
    {{-- CLÁUSULA SEGUNDA — VIGÊNCIA E PRORROGAÇÃO --}}
    {{-- ====================================================================== --}}
    <div class="clausula-titulo">
        <img src="{{ public_path('icons/alerta.png') }}" alt="Vigência">
        <h4>2. CLÁUSULA SEGUNDA – VIGÊNCIA E PRORROGAÇÃO</h4>
    </div>
    <p>
        2.1. O prazo de vigência da contratação é de {{ $detalhe?->prazo_vigencia_texto }} contados
        do(a) assinatura do contrato, prorrogável na forma dos artigos 106 e 107 da Lei n° 14.133, de 2021.<br>
        2.1.1. A prorrogação de que trata este item é condicionada ao ateste, pela autoridade competente, de que as
        condições e os preços permanecem vantajosos para a Administração, permitida a negociação com o contratado.
    </p>

    {{-- ====================================================================== --}}
    {{-- CLÁUSULA TERCEIRA — MODELOS DE EXECUÇÃO E GESTÃO CONTRATUAIS --}}
    {{-- ====================================================================== --}}
    <div class="clausula-titulo">
        <img src="{{ public_path('icons/engrenagem.png') }}" alt="Execução e Gestão">
        <h4>3. CLÁUSULA TERCEIRA – MODELOS DE EXECUÇÃO E GESTÃO CONTRATUAIS</h4>
    </div>
    <p>
        3.1. O regime de execução contratual, os modelos de gestão e de execução, assim como os prazos e condições de
        conclusão, entrega, observação e recebimento do objeto constam no Termo de Referência, anexo a este Contrato.
    </p>

    {{-- ====================================================================== --}}
    {{-- CLÁUSULA QUARTA — SUBCONTRATAÇÃO --}}
    {{-- ====================================================================== --}}
    <div class="clausula-titulo">
        <img src="{{ public_path('icons/lista.png') }}" alt="Subcontratação">
        <h4>4. CLÁUSULA QUARTA – SUBCONTRATAÇÃO</h4>
    </div>
    <p>
        4.1. Não será admitida a subcontratação do objeto contratual.
    </p>

    {{-- ====================================================================== --}}
    {{-- CLÁUSULA QUINTA — PREÇO --}}
    {{-- ====================================================================== --}}
    <div class="clausula-titulo">
        <img src="{{ public_path('icons/dinheiro.png') }}" alt="Preço">
        <h4>5. CLÁUSULA QUINTA - PREÇO</h4>
    </div>
    <p>
        5.1. O valor total da contratação é de
        R$ {{ $valorEstimado > 0 ? number_format($valorEstimado, 2, ',', '.') : '..........' }}
        ({{ $valorEstimado > 0 ? '' : '.....' }}).<br>
        5.2. No valor acima estão incluídas todas as despesas ordinárias diretas e indiretas decorrentes da execução
        do objeto, inclusive tributos e/ou impostos, encargos sociais, trabalhistas, previdenciários, fiscais e
        comerciais incidentes, taxa de administração, frete, seguro e outros necessários ao cumprimento integral do
        objeto da contratação.<br>
        5.3. O valor acima é meramente estimativo, de forma que os pagamentos devidos ao contratado dependerão dos
        quantitativos efetivamente fornecidos.
    </p>

    {{-- ====================================================================== --}}
    {{-- CLÁUSULA SEXTA — PAGAMENTO --}}
    {{-- ====================================================================== --}}
    <div class="clausula-titulo">
        <img src="{{ public_path('icons/dinheiro.png') }}" alt="Pagamento">
        <h4>6. CLÁUSULA SEXTA - PAGAMENTO</h4>
    </div>
    <p>
        6.1. Recebida a Nota Fiscal ou documento de cobrança equivalente, correrá o prazo de trinta dias para fins de
        liquidação, na forma prevista no Termo de Referência.<br>
        6.2. O pagamento do(s) produto(s) será(ão) efetuado(s) pela CONTRATANTE, mediante a emissão da nota fiscal e
        recibo por parte da CONTRATADA com o visto do funcionário responsável pela fiscalização.
    </p>

    {{-- ====================================================================== --}}
    {{-- CLÁUSULA SÉTIMA — REAJUSTE --}}
    {{-- ====================================================================== --}}
    <div class="clausula-titulo">
        <img src="{{ public_path('icons/calc.png') }}" alt="Reajuste">
        <h4>7. CLÁUSULA SÉTIMA - REAJUSTE</h4>
    </div>
    <p>
        7.1. Os preços inicialmente contratados são fixos e irreajustáveis no prazo de um ano contado da data do
        orçamento estimado.<br>
        7.2. Após o interregno de um ano, e independentemente de pedido do contratado, os preços iniciais serão
        reajustados, mediante a aplicação, pelo contratante, do índice Inflacionário, exclusivamente para as
        obrigações iniciadas e concluídas após a ocorrência da anualidade.<br>
        7.3. Nos reajustes subsequentes ao primeiro, o interregno mínimo de um ano será contado a partir dos efeitos
        financeiros do último reajuste.<br>
        7.4. No caso de atraso ou não divulgação do(s) índice(s) de reajustamento, o contratante pagará ao contratado
        a importância calculada pela última variação conhecida, liquidando a diferença correspondente tão logo
        seja(m) divulgado(s) o(s) índice(s) definitivo(s).<br>
        7.5. Nas aferições finais, o(s) índice(s) utilizado(s) para reajuste será(ão), obrigatoriamente, o(s)
        definitivo(s).<br>
        7.6. Caso o(s) índice(s) estabelecido(s) para reajustamento venha(m) a ser extinto(s) ou de qualquer forma não
        possa(m) mais ser utilizado(s), será(ão) adotado(s), em substituição, o(s) que vier(em) a ser determinado(s)
        pela legislação então em vigor.<br>
        7.7. Na ausência de previsão legal quanto ao índice substituto, as partes elegerão novo índice oficial, para
        reajustamento do preço do valor remanescente, por meio de termo aditivo.<br>
        7.8. O reajuste será realizado por apostilamento.
    </p>

    {{-- ====================================================================== --}}
    {{-- CLÁUSULA OITAVA — OBRIGAÇÕES DO CONTRATANTE --}}
    {{-- ====================================================================== --}}
    <div class="clausula-titulo">
        <img src="{{ public_path('icons/descricao-necessidade.png') }}" alt="Obrigações do Contratante">
        <h4>8. CLÁUSULA OITAVA - OBRIGAÇÕES DO CONTRATANTE</h4>
    </div>
    <p>
        8.1. São obrigações do Contratante:<br>
        8.2. Exigir o cumprimento de todas as obrigações assumidas pelo Contratado, de acordo com o contrato e seus
        anexos;<br>
        8.3. Receber o objeto no prazo e condições estabelecidas no Termo de Referência;<br>
        8.4. Notificar o Contratado, por escrito, sobre vícios, defeitos ou incorreções verificadas no objeto
        fornecido, para que seja por ele substituído, reparado ou corrigido, no total ou em parte, às suas
        expensas;<br>
        8.5. Acompanhar e fiscalizar a execução do contrato e o cumprimento das obrigações pelo Contratado;<br>
        8.6. Comunicar a empresa para emissão de Nota Fiscal no que pertine à parcela incontroversa da execução do
        objeto, para efeito de liquidação e pagamento, quando houver controvérsia sobre a execução do objeto, quanto
        à dimensão, qualidade e quantidade, conforme o art. 143 da Lei nº 14.133, de 2021;<br>
        8.7. Efetuar o pagamento ao Contratado do valor correspondente ao fornecimento do objeto, no prazo, forma e
        condições estabelecidos no presente Contrato;<br>
        8.8. Aplicar ao Contratado as sanções previstas na lei e neste Contrato;<br>
        8.9. Cientificar o órgão de representação judicial competente para adoção das medidas cabíveis quando do
        descumprimento de obrigações pelo Contratado;<br>
        8.10. Explicitamente emitir decisão sobre todas as solicitações e reclamações relacionadas à execução do
        presente Contrato, ressalvados os requerimentos manifestamente impertinentes, meramente protelatórios ou de
        nenhum interesse para a boa execução do ajuste.<br>
        8.10.1. A Administração terá o prazo de 05 (cinco) dias, a contar da data do protocolo do requerimento para
        decidir, admitida a prorrogação motivada, por igual período.<br>
        8.11. Responder eventuais pedidos de reestabelecimento do equilíbrio econômico-financeiro feitos pelo
        contratado no prazo máximo de 30 (trinta) dias.<br>
        8.12. A Administração não responderá por quaisquer compromissos assumidos pelo Contratado com terceiros,
        ainda que vinculados à execução do contrato, bem como por qualquer dano causado a terceiros em decorrência de
        ato do Contratado, de seus empregados, prepostos ou subordinados.
    </p>

    <div class="page-break"></div>

    {{-- ====================================================================== --}}
    {{-- CLÁUSULA NONA — OBRIGAÇÕES DO CONTRATADO --}}
    {{-- ====================================================================== --}}
    <div class="clausula-titulo">
        <img src="{{ public_path('icons/descricao-necessidade.png') }}" alt="Obrigações do Contratado">
        <h4>9. CLÁUSULA NONA - OBRIGAÇÕES DO CONTRATADO</h4>
    </div>
    <p>
        9.1. O Contratado deve cumprir todas as obrigações constantes deste Contrato e em seus anexos, assumindo como
        exclusivamente seus os riscos e as despesas decorrentes da boa e perfeita execução do objeto, observando,
        ainda, as obrigações a seguir dispostas:<br>
        9.2. Responsabilizar-se pelos vícios e danos decorrentes do objeto, de acordo com o Código de Defesa do
        Consumidor (Lei nº 8.078, de 1990);<br>
        9.3. Comunicar ao contratante, no prazo máximo de 24 (vinte e quatro) horas que antecede a data da entrega, os
        motivos que impossibilitem o cumprimento do prazo previsto, com a devida comprovação;<br>
        9.4. Atender às determinações regulares emitidas pelo fiscal ou gestor do contrato ou autoridade superior
        (art. 137, II, da Lei n.º 14.133, de 2021) e prestar todo esclarecimento ou informação por eles
        solicitados;<br>
        9.5. Reparar, corrigir, remover, reconstruir ou substituir, às suas expensas, no total ou em parte, no prazo
        fixado pelo fiscal do contrato, os bens nos quais se verificarem vícios, defeitos ou incorreções resultantes
        da execução ou dos materiais empregados;<br>
        9.6. Responsabilizar-se pelos vícios e danos decorrentes da execução do objeto, bem como por todo e qualquer
        dano causado à Administração ou terceiros, não reduzindo essa responsabilidade a fiscalização ou o
        acompanhamento da execução contratual pelo contratante, que ficará autorizado a descontar dos pagamentos
        devidos ou da garantia, caso exigida, o valor correspondente aos danos sofridos;<br>
        9.7. Quando não for possível a verificação da regularidade no Sistema de Cadastro de Fornecedores – SICAF, o
        contratado deverá entregar ao setor responsável pela fiscalização do contrato, junto com a Nota Fiscal para
        fins de pagamento, os seguintes documentos: 1) prova de regularidade relativa à Seguridade Social; 2) certidão
        conjunta relativa aos tributos federais e à Dívida Ativa da União; 3) certidões que comprovem a regularidade
        perante a Fazenda Estadual ou Distrital do domicílio ou sede do contratado; 4) Certidão de Regularidade do
        FGTS – CRF; e 5) Certidão Negativa de Débitos Trabalhistas – CNDT;<br>
        9.8. Responsabilizar-se pelo cumprimento de todas as obrigações trabalhistas, previdenciárias, fiscais,
        comerciais e as demais previstas em legislação específica, cuja inadimplência não transfere a responsabilidade
        ao contratante e não poderá onerar o objeto do contrato;<br>
        9.9. Comunicar ao Fiscal do contrato, no prazo de 24 (vinte e quatro) horas, qualquer ocorrência anormal ou
        acidente que se verifique no local da execução do objeto contratual;<br>
        9.10. Paralisar, por determinação do contratante, qualquer atividade que não esteja sendo executada de acordo
        com a boa técnica ou que ponha em risco a segurança de pessoas ou bens de terceiros;<br>
        9.11. Manter durante toda a vigência do contrato, em compatibilidade com as obrigações assumidas, todas as
        condições exigidas para habilitação na licitação;<br>
        9.12. Cumprir, durante todo o período de execução do contrato, a reserva de cargos prevista em lei para pessoa
        com deficiência, para reabilitado da Previdência Social ou para aprendiz, bem como as reservas de cargos
        previstas na legislação (art. 116, da Lei n.º 14.133, de 2021);<br>
        9.13. Comprovar a reserva de cargos a que se refere a cláusula acima, no prazo fixado pelo fiscal do contrato,
        com a indicação dos empregados que preencheram as referidas vagas (art. 116, parágrafo único, da Lei n.º
        14.133, de 2021);<br>
        9.14. Guardar sigilo sobre todas as informações obtidas em decorrência do cumprimento do contrato;<br>
        9.15. Arcar com o ônus decorrente de eventual equívoco no dimensionamento dos quantitativos de sua proposta,
        inclusive quanto aos custos variáveis decorrentes de fatores futuros e incertos, devendo complementá-los, caso
        o previsto inicialmente em sua proposta não seja satisfatório para o atendimento do objeto da contratação,
        exceto quando ocorrer algum dos eventos arrolados no art. 124, II, d, da Lei nº 14.133, de 2021;<br>
        9.16. Cumprir, além dos postulados legais vigentes de âmbito federal, estadual ou municipal, as normas de
        segurança do contratante;<br>
        9.17. Alocar os empregados necessários, com habilitação e conhecimento adequados, ao perfeito cumprimento das
        cláusulas deste contrato, fornecendo os materiais, equipamentos, ferramentas e utensílios demandados, cuja
        quantidade, qualidade e tecnologia deverão atender às recomendações de boa técnica e a legislação de
        regência;<br>
        9.18. Orientar e treinar seus empregados sobre os deveres previstos na Lei nº 13.709, de 14 de agosto de 2018,
        adotando medidas eficazes para proteção de dados pessoais a que tenha acesso por força da execução deste
        contrato;<br>
        9.19. Conduzir os trabalhos com estrita observância às normas da legislação pertinente, cumprindo as
        determinações dos Poderes Públicos, mantendo sempre limpo o local de execução do objeto e nas melhores
        condições de segurança, higiene e disciplina;<br>
        9.20. Submeter previamente, por escrito, ao contratante, para análise e aprovação, quaisquer mudanças nos
        métodos executivos que fujam às especificações do memorial descritivo ou instrumento congênere;<br>
        9.21. Não permitir a utilização de qualquer trabalho do menor de dezesseis anos, exceto na condição de
        aprendiz para os maiores de quatorze anos, nem permitir a utilização do trabalho do menor de dezoito anos em
        trabalho noturno, perigoso ou insalubre;<br>
        9.22. As partes cooperarão entre si no cumprimento das obrigações referentes ao exercício dos direitos dos
        Titulares previstos na LGPD e nas Leis e Regulamentos de Proteção de Dados em vigor e também no atendimento de
        requisições e determinações do Poder Judiciário, Ministério Público, Órgãos de controle administrativo;<br>
        9.23. As partes responderão administrativa e judicialmente, em caso de causarem danos patrimoniais, morais,
        individual ou coletivo, aos titulares de dados pessoais, repassados em decorrência da execução contratual, por
        inobservância à LGPD;<br>
        9.24. Em atendimento ao disposto na Lei n. 13.709/2018 - Lei Geral de Proteção de Dados Pessoais (LGPD), a
        CONTRATANTE, para a execução do serviço objeto deste contrato, terá acesso a dados pessoais dos representantes
        da CONTRATADA, tais como: número do CPF e do RG, endereço eletrônico, e cópia do documento de
        identificação;<br>
        9.25. A critério do Encarregado de Dados da CONTRATANTE, a CONTRATADA poderá ser provocada a colaborar na
        elaboração do relatório de impacto à proteção de dados pessoais (RIPD), conforme a sensibilidade e o risco
        inerente aos serviços objeto deste contrato, no tocante a dados pessoais;<br>
        9.26. A CONTRATADA fica obrigada a comunicar ao CONTRATANTE, em até 24 (vinte e quatro) horas, qualquer
        incidente de acessos não autorizados aos dados pessoais, situações acidentais ou ilícitas de destruição,
        perda, alteração, comunicação ou qualquer forma de tratamento inadequado ou ilícito, bem como adotar as
        providências dispostas no art. 48 da LGPD;<br>
        9.27. Encerrada a vigência do contrato ou não havendo mais necessidade de utilização dos dados pessoais,
        sensíveis ou não, a CONTRATADA interromperá o tratamento e, em no máximo 30 (trinta) dias, sob instruções e na
        medida do determinado pela CONTRATANTE, eliminará completamente os Dados Pessoais e todas as cópias
        porventura existentes (em formato digital, físico ou outro qualquer), salvo quando necessite mantê-los para
        cumprimento de obrigação legal ou outra hipótese legal prevista na LGPD.
    </p>

    {{-- ====================================================================== --}}
    {{-- CLÁUSULA DÉCIMA — GARANTIA DE EXECUÇÃO --}}
    {{-- ====================================================================== --}}
    <div class="clausula-titulo">
        <img src="{{ public_path('icons/selo.png') }}" alt="Garantia de Execução">
        <h4>10. CLÁUSULA DÉCIMA – GARANTIA DE EXECUÇÃO</h4>
    </div>
    <p>
        10.1. Não haverá exigência de garantia contratual da execução.
    </p>

    {{-- ====================================================================== --}}
    {{-- CLÁUSULA DÉCIMA PRIMEIRA — INFRAÇÕES E SANÇÕES ADMINISTRATIVAS --}}
    {{-- ====================================================================== --}}
    <div class="clausula-titulo">
        <img src="{{ public_path('icons/alerta.png') }}" alt="Infrações e Sanções">
        <h4>11. CLÁUSULA DÉCIMA PRIMEIRA – INFRAÇÕES E SANÇÕES ADMINISTRATIVAS</h4>
    </div>
    <p>
        11.1. Comete infração administrativa, nos termos da Lei nº 14.133, de 2021, o contratado que:<br>
        a) der causa à inexecução parcial do contrato;<br>
        b) der causa à inexecução parcial do contrato que cause grave dano à Administração ou ao funcionamento dos
        serviços públicos ou ao interesse coletivo;<br>
        c) der causa à inexecução total do contrato;<br>
        d) ensejar o retardamento da execução ou da entrega do objeto da contratação sem motivo justificado;<br>
        e) apresentar documentação falsa ou prestar declaração falsa durante a execução do contrato;<br>
        f) praticar ato fraudulento na execução do contrato;<br>
        g) comportar-se de modo inidôneo ou cometer fraude de qualquer natureza;<br>
        h) praticar ato lesivo previsto no art. 5º da Lei nº 12.846, de 1º de agosto de 2013.<br>
        11.2. Serão aplicadas ao contratado que incorrer nas infrações acima descritas as seguintes sanções:<br>
        i. Advertência, quando o contratado der causa à inexecução parcial do contrato, sempre que não se justificar a
        imposição de penalidade mais grave (art. 156, §2º, da Lei nº 14.133, de 2021);<br>
        ii. Impedimento de licitar e contratar, quando praticadas as condutas descritas nas alíneas "b", "c" e "d" do
        subitem acima deste Contrato, sempre que não se justificar a imposição de penalidade mais grave (art. 156, §
        4º, da Lei nº 14.133, de 2021);<br>
        iii. Declaração de inidoneidade para licitar e contratar, quando praticadas as condutas descritas nas alíneas
        "e", "f", "g" e "h" do subitem acima deste Contrato, bem como nas alíneas "b", "c" e "d", que justifiquem a
        imposição de penalidade mais grave (art. 156, §5º, da Lei nº 14.133, de 2021).<br>
        iv. Multa:<br>
        1. moratória de 0,5% (zero vírgula cinco por cento) por dia de atraso injustificado sobre o valor da parcela
        inadimplida, até o limite de 60 (sessenta) dias;<br>
        2. moratória de 0,5% (zero vírgula cinco por cento) por dia de atraso injustificado sobre o valor total do
        contrato, até o máximo de 30% (trinta por cento), pela inobservância do prazo fixado para apresentação,
        suplementação ou reposição da garantia.<br>
        i. O atraso superior a 30 (trinta) dias autoriza a Administração a promover a extinção do contrato por
        descumprimento ou cumprimento irregular de suas cláusulas, conforme dispõe o inciso I do art. 137 da Lei n.
        14.133, de 2021.<br>
        11.3. A aplicação das sanções previstas neste Contrato não exclui, em hipótese alguma, a obrigação de
        reparação integral do dano causado ao Contratante (art. 156, §9º, da Lei nº 14.133, de 2021).<br>
        11.4. Todas as sanções previstas neste Contrato poderão ser aplicadas cumulativamente com a multa (art. 156,
        §7º, da Lei nº 14.133, de 2021).<br>
        11.4.1. Antes da aplicação da multa será facultada a defesa do interessado no prazo de 15 (quinze) dias úteis,
        contado da data de sua intimação (art. 157, da Lei nº 14.133, de 2021).<br>
        11.4.2. Se a multa aplicada e as indenizações cabíveis forem superiores ao valor do pagamento eventualmente
        devido pelo Contratante ao Contratado, além da perda desse valor, a diferença será descontada da garantia
        prestada ou será cobrada judicialmente (art. 156, §8º, da Lei nº 14.133, de 2021).<br>
        11.4.3. Previamente ao encaminhamento à cobrança judicial, a multa poderá ser recolhida administrativamente
        no prazo máximo de 30 (trinta) dias, a contar da data do recebimento da comunicação enviada pela autoridade
        competente.<br>
        11.5. A aplicação das sanções realizar-se-á em processo administrativo que assegure o contraditório e a ampla
        defesa ao Contratado, observando-se o procedimento previsto no caput e parágrafos do art. 158 da Lei nº
        14.133, de 2021, para as penalidades de impedimento de licitar e contratar e de declaração de inidoneidade
        para licitar ou contratar.<br>
        11.6. Na aplicação das sanções serão considerados (art. 156, §1º, da Lei nº 14.133, de 2021):<br>
        a) a natureza e a gravidade da infração cometida;<br>
        b) as peculiaridades do caso concreto;<br>
        c) as circunstâncias agravantes ou atenuantes;<br>
        d) os danos que dela provierem para o Contratante;<br>
        e) a implantação ou o aperfeiçoamento de programa de integridade, conforme normas e orientações dos órgãos de
        controle.<br>
        11.7. Os atos previstos como infrações administrativas na Lei nº 14.133, de 2021, ou em outras leis de
        licitações e contratos da Administração Pública que também sejam tipificados como atos lesivos na Lei nº
        12.846, de 2013, serão apurados e julgados conjuntamente, nos mesmos autos, observado o rito procedimental e
        autoridade competente definidos na referida Lei (art. 159).<br>
        11.8. A personalidade jurídica do Contratado poderá ser desconsiderada sempre que utilizada com abuso do
        direito para facilitar, encobrir ou dissimular a prática dos atos ilícitos previstos neste Contrato ou para
        provocar confusão patrimonial, e, nesse caso, todos os efeitos das sanções aplicadas à pessoa jurídica serão
        estendidos aos seus administradores e sócios com poderes de administração, à pessoa jurídica sucessora ou à
        empresa do mesmo ramo com relação de coligação ou controle, de fato ou de direito, com o Contratado,
        observados, em todos os casos, o contraditório, a ampla defesa e a obrigatoriedade de análise jurídica prévia
        (art. 160, da Lei nº 14.133, de 2021).<br>
        11.9. O Contratante deverá, no prazo máximo 15 (quinze) dias úteis, contado da data da aplicação da sanção,
        informar e manter atualizados os dados relativos às sanções por ela aplicadas, para fins de publicidade no
        Cadastro Nacional de Empresas Inidôneas e Suspensas (Ceis) e no Cadastro Nacional de Empresas Punidas
        (Cnep).<br>
        11.10. As sanções de impedimento de licitar e contratar e declaração de inidoneidade para licitar ou contratar
        são passíveis de reabilitação na forma do art. 163 da Lei nº 14.133/21.<br>
        11.11. Os débitos do contratado para com a Administração contratante, resultantes de multa administrativa
        e/ou indenizações, não inscritos em dívida ativa, poderão ser compensados, total ou parcialmente, com os
        créditos devidos pelo referido órgão decorrentes deste mesmo contrato ou de outros contratos administrativos
        que o contratado possua com o mesmo órgão ora contratante.
    </p>

    <div class="page-break"></div>

    {{-- ====================================================================== --}}
    {{-- CLÁUSULA DÉCIMA SEGUNDA — DA EXTINÇÃO CONTRATUAL --}}
    {{-- ====================================================================== --}}
    <div class="clausula-titulo">
        <img src="{{ public_path('icons/alerta.png') }}" alt="Extinção Contratual">
        <h4>12. CLÁUSULA DÉCIMA SEGUNDA – DA EXTINÇÃO CONTRATUAL</h4>
    </div>
    <p>
        12.1. O contrato se extingue quando cumpridas as obrigações de ambas as partes, ainda que isso ocorra antes do
        prazo estipulado para tanto.<br>
        12.2. Se as obrigações não forem cumpridas no prazo estipulado, a vigência ficará prorrogada até a conclusão
        do objeto, caso em que deverá a Administração providenciar a readequação do cronograma fixado para o
        contrato.<br>
        12.2.1. Quando a não conclusão do contrato referida no item anterior decorrer de culpa do contratado:<br>
        a) ficará ele constituído em mora, sendo-lhe aplicáveis as respectivas sanções administrativas; e<br>
        b) poderá a Administração optar pela extinção do contrato e, nesse caso, adotará as medidas admitidas em lei
        para a continuidade da execução contratual.<br>
        12.3. O contrato pode ser extinto antes de cumpridas as obrigações nele estipuladas, ou antes do prazo nele
        fixado, por algum dos motivos previstos no artigo 137 da Lei nº 14.133/21, bem como amigavelmente, assegurados
        o contraditório e a ampla defesa.<br>
        12.3.1. Nesta hipótese, aplicam-se também os artigos 138 e 139 da mesma Lei.<br>
        12.3.2. A alteração social ou a modificação da finalidade ou da estrutura da empresa não ensejará a rescisão
        se não restringir sua capacidade de concluir o contrato.<br>
        12.3.2.1. Se a operação implicar mudança da pessoa jurídica contratada, deverá ser formalizado termo aditivo
        para alteração subjetiva.<br>
        12.4. O termo de rescisão, sempre que possível, será precedido de:<br>
        12.4.1. Balanço dos eventos contratuais já cumpridos ou parcialmente cumpridos;<br>
        12.4.2. Relação dos pagamentos já efetuados e ainda devidos;<br>
        12.4.3. Indenizações e multas.<br>
        12.5. A extinção do contrato não configura óbice para o reconhecimento do desequilíbrio econômico-financeiro,
        hipótese em que será concedida indenização por meio de termo indenizatório (art. 131, caput, da Lei n.º
        14.133, de 2021).
    </p>

    {{-- ====================================================================== --}}
    {{-- CLÁUSULA DÉCIMA TERCEIRA — DOTAÇÃO ORÇAMENTÁRIA --}}
    {{-- ====================================================================== --}}
    <div class="clausula-titulo">
        <img src="{{ public_path('icons/grafico.png') }}" alt="Dotação Orçamentária">
        <h4>13. CLÁUSULA DÉCIMA TERCEIRA – DOTAÇÃO ORÇAMENTÁRIA</h4>
    </div>
    <p>
        13.1. As despesas decorrentes da presente contratação correrão à conta de recursos específicos consignados no
        Orçamento vigente deste exercício, na dotação abaixo discriminada:
    </p>

    <table class="dotacao">
        <tr>
            <td colspan="2">
                @if (!empty($detalhe?->dotacao_orcamentaria))
                {!! $detalhe->dotacao_orcamentaria !!}
                @else
                Gestão/Unidade: [...];<br>
                Fonte de Recursos: [...];<br>
                Programa de Trabalho: [...];<br>
                Elemento de Despesa: [...];<br>
                Plano Interno: [...];
                @endif
            </td>
        </tr>
    </table>

    <p>
        13.2. A dotação relativa aos exercícios financeiros subsequentes será indicada após aprovação da Lei
        Orçamentária respectiva e liberação dos créditos correspondentes, mediante apostilamento.
    </p>

    {{-- ====================================================================== --}}
    {{-- CLÁUSULA DÉCIMA QUARTA — DOS CASOS OMISSOS --}}
    {{-- ====================================================================== --}}
    <div class="clausula-titulo">
        <img src="{{ public_path('icons/lista.png') }}" alt="Casos Omissos">
        <h4>14. CLÁUSULA DÉCIMA QUARTA – DOS CASOS OMISSOS</h4>
    </div>
    <p>
        14.1. Os casos omissos serão decididos pelo contratante, segundo as disposições contidas na Lei nº 14.133, de
        2021, e demais normas federais aplicáveis e, subsidiariamente, segundo as disposições contidas na Lei nº
        8.078, de 1990 – Código de Defesa do Consumidor – e normas e princípios gerais dos contratos.
    </p>

    {{-- ====================================================================== --}}
    {{-- CLÁUSULA DÉCIMA QUINTA — ALTERAÇÕES --}}
    {{-- ====================================================================== --}}
    <div class="clausula-titulo">
        <img src="{{ public_path('icons/engrenagem.png') }}" alt="Alterações">
        <h4>15. CLÁUSULA DÉCIMA QUINTA – ALTERAÇÕES</h4>
    </div>
    <p>
        15.1. Eventuais alterações contratuais reger-se-ão pela disciplina dos arts. 124 e seguintes da Lei nº
        14.133, de 2021.<br>
        15.2. O contratado é obrigado a aceitar, nas mesmas condições contratuais, os acréscimos ou supressões que se
        fizerem necessários, até o limite de 25% (vinte e cinco por cento) do valor inicial atualizado do
        contrato.<br>
        15.3. Registros que não caracterizam alteração do contrato podem ser realizados por simples apostila,
        dispensada a celebração de termo aditivo, na forma do art. 136 da Lei nº 14.133, de 2021.
    </p>

    {{-- ====================================================================== --}}
    {{-- CLÁUSULA DÉCIMA SEXTA — PUBLICAÇÃO --}}
    {{-- ====================================================================== --}}
    <div class="clausula-titulo">
        <img src="{{ public_path('icons/descricao-necessidade.png') }}" alt="Publicação">
        <h4>16. CLÁUSULA DÉCIMA SEXTA – PUBLICAÇÃO</h4>
    </div>
    <p>
        16.1. Incumbirá ao contratante divulgar o presente instrumento no Portal Nacional de Contratações Públicas
        (PNCP), na forma prevista no art. 94 da Lei 14.133, de 2021, bem como no respectivo sítio oficial na Internet.
    </p>

    {{-- ====================================================================== --}}
    {{-- CLÁUSULA DÉCIMA SÉTIMA — ALOCAÇÃO DE RISCOS --}}
    {{-- ====================================================================== --}}
    <div class="clausula-titulo">
        <img src="{{ public_path('icons/alerta.png') }}" alt="Alocação de Riscos">
        <h4>17. CLÁUSULA DÉCIMA SÉTIMA – DA ALOCAÇÃO DE RISCOS</h4>
    </div>
    <p>
        17.1. São de responsabilidade das partes, sem prejuízo das demais obrigações constantes neste Contrato e no
        Termo de Referência, os riscos relacionados oriundos deste contrato, conforme tenha sido prevista matriz de
        riscos para a sua execução.<br>
        17.2. Caso as situações descritas na matriz de riscos venham a ocorrer, poderão ser adotadas as providências a
        seguir:<br>
        17.2.1. Utilização de seguros obrigatórios previamente definidos no contrato;<br>
        17.2.2. Restabelecimento da equação econômico-financeira do contrato nos casos em que o sinistro seja
        considerado na matriz de riscos como causa de desequilíbrio não suportada pela parte que pretenda o
        restabelecimento;<br>
        17.2.3. Resolução do contrato quando o sinistro majorar excessivamente ou impedir a continuidade da execução
        contratual.<br>
        17.3. As providências elencadas no item 17.2 somam-se àquelas decorrentes das peculiaridades da contratação.
    </p>

    {{-- ====================================================================== --}}
    {{-- CLÁUSULA DÉCIMA OITAVA — FORO --}}
    {{-- ====================================================================== --}}
    <div class="clausula-titulo">
        <img src="{{ public_path('icons/mao.png') }}" alt="Foro">
        <h4>18. CLÁUSULA DÉCIMA OITAVA – FORO</h4>
    </div>
    <p>
        18.1. Fica eleito o Foro da Comarca de {{ $detalhe?->comarca ?? '.....................' }}, para dirimir os
        litígios que decorrerem da execução deste Termo de Contrato que não puderem ser compostos pela conciliação,
        conforme art. 92, §1º, da Lei nº 14.133/21.
    </p>

    <p style="text-align: right; margin-top: 30px;">
        {{ $prefeitura->cidade }}, ..... de ..................... de {{ now()->year }}.
    </p>

    <div class="signature-block">
        ___________________________________<br>
        Representante legal do CONTRATANTE
    </div>

    <div class="signature-block">
        ___________________________________<br>
        Representante legal do CONTRATADO
    </div>

    <p style="margin-top: 40px;">
        TESTEMUNHAS:<br>
        1- ___________________________________<br>
        2- ___________________________________
    </p>

</body>

</html>
