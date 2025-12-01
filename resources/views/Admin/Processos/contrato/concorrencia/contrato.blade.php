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

    {{-- ====================================================================== --}}
    {{-- BLOCO 1: CAPA DO DOCUMENTO --}}
    {{-- ====================================================================== --}}
    <div class="container">
        <div class="conteudo-all">
            <div style="margin: 30px 0 0;">
                <div class="title">CONTRATO</div>
            </div>
            <div class="conteudo">
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
                                    Prefeitura Municipal de {{ $processo->finalizacao->orgao_responsavel }}, com sede no(a) {{ $processo->prefeitura->endereco }}, na
                                    cidade de {{ $processo->prefeitura->cidade }}, inscrito(a) no CNPJ sob o nº {{ $processo->finalizacao->cnpj }},
                                    neste ato representado(a) pelo(a) {{ $processo->finalizacao->responsavel }}, inscrito no CPF sob n°
                                    {{ $processo->finalizacao->cpf_responsavel }}.
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Alinhamento com Planejamento Anual -->
                <div class="section">
                    <table>
                        <tr>
                            <td class="icon">
                                <img src="{{ public_path('icons/Imagem2.png') }}" width="40">
                            </td>
                            <td class="content">
                                <div style=" font-weight: bold; margin-bottom: 3px;">Contratado</div>
                                <div>
                                    {{ $processo->finalizacao->razao_social }}, inscrito(a) no CNPJ/MF sob o nº {{ $processo->finalizacao->cnpj_empresa_vencedora }}, sediado(a)
                                    na {{ $processo->finalizacao->endereco_empresa_vencedora }}, neste ato representado(a) por {{ $processo->finalizacao->representante_legal_empresa }}, inscrito no
                                    CPF sob n° {{ $processo->finalizacao->cpf_representante }}.
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Equipe de Planejamento -->
                <div class="section">
                    <table>
                        <tr>
                            <td class="icon">
                                <img src="{{ public_path('icons/Imagem3.png') }}" width="40">
                            </td>
                            <td class="content">
                                @php
                                    $vigencia = is_array($processo->detalhe->prazo_vigencia ?? null)
                                        ? $processo->detalhe->prazo_vigencia
                                        : ['12_meses'];

                                    $outro_vigencia = $processo->detalhe->prazo_vigencia_outro ?? '________________.';

                                    $objeto_continuado = strtolower($processo->detalhe->objeto_continuado ?? 'nao');

                                    // Texto para preencher automaticamente
                                    if (in_array('exercicio_financeiro', $vigencia)) {
                                        $textoVigencia = "até 31/12 do exercício financeiro da contratação";
                                    } elseif (in_array('12_meses', $vigencia)) {
                                        $textoVigencia = "12 meses";
                                    } elseif (in_array('outro', $vigencia)) {
                                        $textoVigencia = $outro_vigencia;
                                    } else {
                                        $textoVigencia = "________________";
                                    }
                                @endphp

                                <div style="font-weight: bold; margin-bottom: 3px;">Prazo de Vigência</div>

                                <div>
                                    O prazo de vigência da contratação é de
                                    <span style="font-weight:bold; text-decoration:underline;">
                                        {{ $textoVigencia }}
                                    </span>,
                                    contados da ordem de Serviços, prorrogável na forma dos artigos 106 e 107 da Lei n° 14.133, de 2021.
                                </div>
                            </td>

                        </tr>
                    </table>
                </div>

                <!-- Problema Resumido -->
                <div class="section">
                    <table>
                        <tr>
                            <td class="icon">
                                <img src="{{ public_path('icons/Imagem4.png') }}" width="40">
                            </td>
                            <td class="content">
                                <div style=" font-weight: bold; margin-bottom: 3px;">Valor Total</div>
                                <div style="">
                                    {{ $processo->finalizacao->valor_total }}
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
        <h4 style="text-align: center;">
            CONTRATO Nº {{ $campos['numero_contrato'] }} <br>
            PROCESSO ADMINISTRATIVO Nº {{ $processo->numero_processo }} <br>
            CONCORRÊNCIA Nº {{ $processo->numero_procedimento }}
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
                            Prefeitura Municipal de {{ $processo->finalizacao->orgao_responsavel }}, com sede no(a) {{ $processo->prefeitura->endereco }}, na cidade de {{ $processo->prefeitura->cidade }} inscrito(a) no CNPJ
                            sob o nº {{ $processo->finalizacao->cnpj }}, neste ato representado(a) pelo(a) {{ $processo->finalizacao->responsavel }} inscrito no CPF sob n° {{ $processo->finalizacao->cpf_responsavel }}.
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
                            {{ $processo->finalizacao->razao_social }}, inscrito(a) no CNPJ/MF sob o nº {{ $processo->finalizacao->cnpj_empresa_vencedora }}, sediado(a) na {{ $processo->finalizacao->endereco }} neste
                            ato representado(a) por {{ $processo->finalizacao->representante_legal_empresa }}, inscrito no CPF sob n° {{ $processo->finalizacao->cpf_representante }}.
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                CLÁUSULA PRIMEIRA – DO OBJETO</h4>
        </div>

        <p style="text-align: justify;">
            1.1 Contratação de empresa para {!! strip_tags($processo->objeto) !!}
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                CLÁUSULA SEGUNDA – REGIME DE EXECUÇÃO E GARANTIA </h4>
        </div>

        <p style="text-align: justify;">
            2.1 Os serviços serão executados em Regime de Empreitada por Preço Global, de acordo
            com as especificações constantes no Projeto Básico.
            <br>
            2.2. Será exigida garantia contratual nos termos do art. 96 a 102 da lei 14.133. A garantia
            será de 5% (cinco por cento) do valor inicial do contrato, autorizada a majoração desse
            percentual para até 10% (dez por cento), desde que justificada mediante análise da
            complexidade técnica e dos riscos envolvidos.
            <br>
            2.3. A garantia prestada pelo contratado será liberada ou restituída após a fiel execução do
            contrato ou após a sua extinção por culpa exclusiva da Administração e, quando em
            dinheiro, atualizada monetariamente.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                CLÁUSULA TERCEIRA – DAS ESPECIFICAÇÕES E FISCALIZAÇÃO</h4>
        </div>

        <p style="text-align: justify;">
            3.1 Está a CONTRATADA obrigada, às suas expensas, a colocar e manter no local da obra,
            placa discriminando o objeto e número deste contrato, com o respectivo valor.
            <br>
            3.2 Cabe ao CONTRATANTE, a seu critério, por intermédio da Fiscalização designada pela
            PREFEITURA, exercer ampla, irrestrita e permanente fiscalização de todas as fases da
            execução da obra e do comportamento do pessoal da CONTRATADA, sem prejuízo da
            obrigação desta de fiscalizar seus responsáveis técnicos, empregados, prepostos ou
            subordinados.
            <br>
            3.3 A CONTRATADA declara aceitar, integralmente, todos os métodos e processos de
            inspeção, verificação e controle a serem adotados pelo CONTRATANTE.
            <br>
            3.4 A existência e a atuação da fiscalização do CONTRATANTE em nada restringem a
            responsabilidade única, integral e exclusiva da CONTRATADA no que concerne ao objeto
            contratado e as consequências e implicações, próximas ou remotas.
            <br>
            3.5 A obra deste contrato será fiscalizada e recebida de acordo com o disposto nos Artigos
            117, 118, 119 da Lei n° 14.133/2021.
            <br>
            3.6 Caberá à fiscalização do CONTRATANTE, formada por um ou mais representantes da
            Administração, designada pela autoridade competente, o seguinte:
            <br>
            3.6.1 Acompanhar e fiscalizar os trabalhos desde o início, até a aceitação definitiva da
            obra, verificando sua perfeita execução na conformidade das especificações e normas
            fixadas pela licitação;
            <br>
            3.6.2 Promover, com a presença da CONTRATADA, as medições e avaliações, decidir as
            questões técnicas surgidas na execução do objeto ora contratado, bem como certificar a
            veracidade das faturas decorrentes das medições, para efeito de seu pagamento;
            <br>
            3.6.3 Transmitir por escrito, por intermédio do Diário de Ocorrências, as instruções
            relativas às Ordens de Serviço, projetos aprovados, alterações de prazos, cronogramas e
            demais determinações dirigidas à Prefeitura Municipal de {{ $processo->prefeitura->cidade }}, precedidas sempre da
            anuência desta;
            <br>
            3.6.4 Comunicar à PREFEITURA as ocorrências que possam levar à aplicação de
            penalidades à CONTRATADA, verificadas no cumprimento das obrigações contratuais;
            <br>
            3.6.5 Solicitar a substituição de qualquer empregado da CONTRATADA que se encontre
            lotado no canteiro das obras prejudicando o bom andamento dos serviços;
            <br>
            3.6.6 Esclarecer as dúvidas que lhe forem apresentadas pela CONTRATADA, bem como
            acompanhar e fiscalizar a execução qualitativa das obras e determinar a correção das
            imperfeições verificadas;
            <br>
            3.6.7 Atestar a veracidade dos registros efetuados pela CONTRATADA no Diário de
            Ocorrências, principalmente os relativos às condições meteorológicas prejudiciais ao
            andamento das obras.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                CLÁUSULA QUARTA – DAS ADEQUAÇÕES TÉCNICAS</h4>
        </div>

        <p style="text-align: justify;">
            4.1 As eventuais modificações técnicas do projeto ou das especificações não poderão
            alterar o objeto da contratação, podendo ser realizadas somente quando comprovado que
            objetiva alcançar melhor adequação técnica, segundo os fins que se destinam.<br>
            4.2 As alterações de especificações técnicas que se revelam necessárias ao longo da
            execução contratual deverão ser consignadas em registro de ocorrência de obras, em ato
            precedido de justificava técnica, em documento assinado pelo engenheiro responsável
            pela fiscalização da obra e aprovado pela autoridade competente, desde que isto não
            represente em aumento ou supressão dos quantitativos licitados com alteração do valor
            inicial do contrato.<br>
            4.3 Quaisquer modificações que impliquem em aumento ou supressões de quantitativos
            nos termos do artigo 125 da Lei nº 14.133/2021 deverão ser registradas por intermédio de
            termo aditivo.<br>
            4.4 As alterações de especificações obrigatoriamente deverão ser discriminadas em
            planilhas que deverão ser juntadas aos autos do processo autorizativo da contratação.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                CLÁUSULA QUINTA – DO PREÇO:</h4>
        </div>

        <p style="text-align: justify;">
            5.1 O preço global deste contrato é de {{ $processo->finalizacao->valor_total }}, referente ao valor total da obra
            prevista no presente contrato. <br>
            5.2 A CONTRATADA fica obrigada a aceitar nas mesmas condições contratuais os
            acréscimos ou supressões que se fizerem nas obras, decorrentes de modificações de
            quantitativos, projetos ou especificações, até o limite de 25% (vinte e cinco por cento) do
            valor inicial atualizado do contrato, sendo que em qualquer caso, a alteração contratual
            será objeto de exame pela Assessoria Jurídica do Município.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                CLÁUSULA SEXTA – CONDIÇÕES DE PAGAMENTO:</h4>
        </div>

        <p style="text-align: justify;">
            6.1 A comissão de fiscalização da CONTRATANTE promoverá até o último dia útil do mês
            e/ou quinzena corrente, a medição dos serviços executados, e encaminhará a
            CONTRATADA para que esta emita Nota Fiscal relativa a medição apresentada,
            oportunidade em que deverá juntar as guias de recolhimento dos encargos sociais e
            trabalhistas referente ao mês imediatamente anterior. No corpo da Nota Fiscal deverá
            constar, obrigatoriamente as seguintes referências: <br>
            6.1.1 O objeto da prestação dos serviços; <br>
            6.1.2 O número do processo que deu origem à contratação; <br>
            6.1.3 Número da conta e agência do beneficiário. O pagamento será efetuado até o 10º
            (décimo) dia útil do mês e/ou quinzena subsequente ao da prestação dos serviços, no valor
            correspondente aos serviços realizados no período de referência, mediante apresentação
            de Nota Fiscal emitida no valor da medição e devidamente atestada pela comissão de
            fiscalização e pelo representante da contratada. 6.2 Por ocasião do pagamento, a
            CONTRATANTE efetuará as retenções tributárias exigidas pela legislação vigente. <br>
            6.3 A CONTRATADA, para fins de pagamento, deverá juntar aos autos a respectiva Guia de
            Recolhimento do Fundo de Garantia do Tempo de Serviços – GFIP (Lei nº 9.528/97); Guia
            de Recolhimento da Previdência Social – GRPS (Lei nº 8.212/91 alterada pela Lei nº
            9.032/95 e Resolução nº 657/98-INSS); cópia do documento de arrecadação da Receita
            Federal – DARF (IN SRF nº 81/96); cópia do comprovante de pagamento do salário dos
            empregados, relativo ao mês imediatamente anterior a apresentação da segunda fatura
            em diante, (art. 31, § 4º da Lei nº 8.212/91, alterada pela Lei nº 9.032/95).
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                CLÁUSULA SÉTIMA - DO REAJUSTE:</h4>
        </div>

        <p style="text-align: justify;">
            7.1 O valor do presente contrato é irreajustável nos termos da legislação vigente,
            considerando o prazo contratual, salvo acordo entre as partes, depois de comprovado o
            desequilíbrio econômico-financeiro na relação contratual, por intermédio de informações
            oficiais, tendo por base as disposições do Art. 136 da Lei nº 14.133/2021.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                CLÁUSULA OITAVA – DO PRAZO DE VIGÊNCIA</h4>
        </div>

        <p style="text-align: justify;">
            8.1 O prazo para execução dos serviços do objeto no presente Contrato será de {{ $processo->detalhe->prazo_entrega}},
            contados a partir da assinatura do presente e emissão da ordem de execução dos serviços. <br>
            8.2 A CONTRATADA deverá comparecer à Sede da Prefeitura Municipal de {{ $processo->prefeitura->cidade }}, no
            prazo de até 10 (dez) dias corridos, para assinatura e recebimento da Ordem de Serviço,
            contados a partir da assinatura do contrato, sob pena de aplicação da multa.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                CLÁUSULA NONA – DA ENTREGA E DO RECEBIMENTO: </h4>
        </div>

        <p style="text-align: justify;">
            9.1 A entrega e recebimento da obra se darão da seguinte forma: <br>
            9.1.1 Provisoriamente, pelo responsável por seu acompanhamento e fiscalização
            (PREFEITURA), mediante termo circunstanciado, assinado pelas partes em até 15 (quinze)
            dias da comunicação escrita da CONTRATADA; <br>
            9.1.2 Definitivamente, por servidor ou comissão designada pela autoridade competente,
            mediante termo circunstanciado, assinado pelas partes, após o decurso do prazo de
            observação, ou vistoria que comprove a adequação do objeto aos termos contratuais.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                CLÁUSULA DÉCIMA – DOS RECURSOS</h4>
        </div>

        <p style="text-align: justify;">
            10.1 Os recursos destinados à cobertura das despesas referentes ao objeto licitado no
            exercício de 2023, são provenientes da seguinte dotação orçamentária:
        </p>
        <table style="border-collapse: collapse; width: 100%; border: 1px solid black;">
            <tr>
                <!-- Coluna da esquerda -->
                <td style="vertical-align: top; padding: 10px;">
                    {!! str_replace('<p>', '<p style="text-indent:30px; text-align: justify;">', $processo->detalhe->dotacao_orcamentaria) !!}
                </td>
            </tr>
        </table>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                CLÁUSULA DÉCIMA PRIMEIRA – DAS OBRIGAÇÕES DA CONTRATADA:</h4>
        </div>
        <p style="text-align: justify;">
            11.1 Compete à CONTRATADA:<br>

            11.1.1 Fazer no prazo previsto entre a assinatura do contrato e o início da obra minucioso
            exame das especificações e projetos, de modo a poder em tempo hábil e por escrito
            apresentar à Fiscalização todas as divergências e dúvidas porventura encontradas, para o
            devido esclarecimento e aprovação;<br>

            11.1.2 Responsabilizar-se por todos os ônus e obrigações concernentes à legislação fiscal,
            social, tributária e trabalhista de seus empregados, bem como por todas as despesas
            decorrentes de eventuais trabalhos noturnos, inclusive iluminação e ainda por todos os
            danos e prejuízos que, a qualquer título, causar a terceiros em virtude da execução dos
            serviços a seu cargo, respondendo por si e por seus sucessores;<br>

            11.1.3 Reparar, corrigir, remover, reconstruir ou substituir às suas expensas no total ou em
            parte o objeto do contrato em que se verificarem vícios, defeitos ou incorreções,
            resultantes da execução ou da má qualidade e aplicação dos materiais empregados;<br>

            11.1.4 Adquirir e manter permanentemente no escritório da obra, um livro de ocorrência,
            para registro obrigatório de todas e quaisquer ocorrências que merecerem destaque;<br>

            11.1.5 Manter permanentemente no canteiro de Obras, engenheiro residente com plenos
            poderes de decisão na área técnica;<br>

            11.1.6 Executar as suas expensas todas as sondagens e escavações exploratórias que se
            fizerem necessárias e indispensáveis à elaboração do projeto executivo e da obra;<br>

            11.1.7 Promover e responder por todos os fornecimentos de água e energia elétrica
            necessárias à execução da obra, inclusive as instalações provisórias destinadas ao
            atendimento das necessidades; 11.1.8 Responsabilizar-se por quaisquer ações
            decorrentes de pleitos referentes a direitos, patentes e royalties, face à utilização de
            técnicas, materiais, equipamentos, processos ou modelos na execução da obra
            contratada;<br>

            11.1.9 Conduzir a execução da obra pactuada em estrita conformidade com o projeto
            executivo aprovado pelo CONTRATANTE, guardadas as normas técnicas pertinentes à
            natureza e à finalidade do empreendimento;<br>

            11.1.10 Assumir toda a responsabilidade civil sobre a execução da obra objeto desta
            licitação;<br>

            11.1.11 Contratar todos os seguros exigidos pela legislação brasileira, inclusive os
            pertinentes a danos a terceiros, acidente de trabalho, danos materiais a propriedades
            alheias e o relativo a veículos e equipamentos;<br>

            11.1.12 Adquirir e manter no local de execução da obra, todos os equipamentos
            destinados a atendimento a situação de emergência, incluindo as de proteção contra
            incêndio e acidentes de trabalho;<br>

            11.1.13 Comunicar à Administração, por escrito e no prazo de 48 (quarenta e oito) horas,
            quaisquer alterações ou acontecimentos por motivo superveniente que impeçam, mesmo
            temporariamente, a CONTRATADA de cumprir seus deveres e responsabilidades relativas
            à execução do contrato, total ou parcialmente;<br>

            11.1.14 Permitir e facilitar a inspeção pela Fiscalização, prestando informações e
            esclarecimentos quando solicitados, sobre quaisquer procedimentos atinentes à
            execução da obra;<br>

            11.1.15 Garantir durante a execução a proteção e a conservação dos serviços executados,
            até o seu recebimento definitivo;<br>

            11.1.16 Manter a guarda das Obras, até o seu final e definitivo recebimento pela Prefeitura
            Municipal de {{ $processo->prefeitura->cidade }}.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                CLÁUSULA DÉCIMA SEGUNDA – DAS OBRIGAÇÕES DO CONTRATANTE: </h4>
        </div>

        <p style="text-align: justify;">
            12.1 São obrigações do CONTRATANTE zelar pelo fiel cumprimento das obrigações
            pactuadas, pela prestação de todas as informações indispensáveis a regular execução das
            obras, pelo pagamento oportuno das parcelas devidas, custeando a publicação do extrato
            deste instrumento no MURAL DA PREFEITURA, DIÁRIO OFICIAL DA UNIÃO, JORNAL DE
            GRANDE CIRCULAÇÃO, DIÁRIO OFICIAL DOS MUNICÍPIOS, LICITAÇÕES WEB – TCE/PI E
            PORTAL DA TRANSPARÊNCIA.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                CLÁUSULA DÉCIMA TERCEIRA – DAS PENALIDADES:</h4>
        </div>

        <p style="text-align: justify;">
            13.1 O contratado será responsabilizado administrativamente pelas infrações constantes
            do Art. 155 e seus incisos, da Lei n° 14.133/2021, com as seguintes sanções: <br>
            13.1.1 Advertência;<br>
            13.1.2 Multa;<br>
            13.1.3 Impedimento de licitar;<br>
            13.1.4 Declaração de inidoneidade para licitar ou contratar.<br>
            13.2 Na aplicação das sanções serão considerados:<br>
            13.2.1 A natureza e a gravidade da infração cometida;<br>
            13.2.2 As peculiaridades do caso concreto;<br>
            13.2.3 As circunstâncias agravantes ou atenuantes;<br>
            13.2.4 Os danos que dela provierem para a Administração Pública;<br>
            13.2.5 A implantação ou o aperfeiçoamento de programa de integridade, conforme normas
            e orientações dos órgãos de controle.<br>
            13.3 Na aplicação de sanções previstas, serão observados os prazos e demais
            especificações expressas na Lei n° 14.133/2021 e legislação aplicável.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                CLÁUSULA DÉCIMA QUARTA – DOS ENCARGOS:</h4>
        </div>

        <p style="text-align: justify;">
            14.1 A CONTRATADA é responsável pelos encargos trabalhistas, previdenciários, fiscais e
            comerciais, resultantes da execução deste contrato.
        </p>
        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                CLÁUSULA DÉCIMA QUINTA – DA SUBCONTRATAÇÃO:</h4>
        </div>

        <p style="text-align: justify;">
            15.1 É expressamente vedado à CONTRATADA transferir a terceiros as obrigações
            assumidas neste contrato, sem expressa anuência do Município de {{ $processo->prefeitura->cidade }}.
        </p>
        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                CLÁUSULA DÉCIMA SEXTA – DA RESCISÃO:</h4>
        </div>

        <p style="text-align: justify;">
            16.1 São motivos ensejadores da rescisão contratual, sem prejuízo dos demais motivos
            previstos em lei e neste instrumento:<br>
            16.1.1 O descumprimento de cláusulas contratuais ou das especificações que norteiam a
            execução do objeto do contrato;<br>
            16.1.2 O desatendimento às determinações necessárias à execução contratual;<br>
            16.1.3 A prática reiterada, de atos considerados como faltosos, os quais devem ser
            devidamente anotados, nos termos do § 1º do art. 140 da Lei nº 14.133/2021;<br>
            16.1.4 A dissolução da sociedade, a modificação da modalidade ou da estrutura da
            empresa desde que isso venha a inviabilizar a execução contratual;<br>
            16.1.5 Razões de interesse público, devidamente justificados;<br>
            16.1.6 A subcontratação parcial ou total, cessão ou transferência da execução do objeto
            do contrato; 16.1.7 A rescisão contratual poderá ser determinada:<br>
            a) Por ato unilateral, nos casos elencados no art. 138, inciso I e 139, da Lei nº
            14.133/2021;<br>
            b) Por acordo das partes, desde que seja conveniente, segundo os objetivos da
            Administração, com fulcro no art. 138, inciso II da Lei 14.133/2021.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                CLÁUSULA DÉCIMA SÉTIMA – DAS PRERROGATIVAS</h4>
        </div>

        <p style="text-align: justify;">
            18.1 São prerrogativas do CONTRATANTE:<br>
            17.1.1 empreender unilateralmente, modificações nos termos do contrato, desde que
            objetive atender ao interesse público, ressalvados os direitos da CONTRATADA;<br>
            17.1.2 rescindir unilateralmente o contrato, desde que comprovada a inexecução parcial,
            total ou na ocorrência dos fatos elencados no art. 137, 138 e 139 da Lei nº 14.133/2021;<br>
            17.1.3 rescindir o contrato amigavelmente por acordo entre as partes, desde que
            conveniente aos interesses da Administração;<br>
            17.1.4 a rescisão contratual, deverá ser precedida de autorização escrita e fundamentada
            da autoridade superior.
        </p>

        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                CLÁUSULA DÉCIMA OITAVA – DOS CASOS OMISSOS:</h4>
        </div>

        <p style="text-align: justify;">
            18.1 O presente contrato será regido pela Lei nº 14.133/2021. Caso haja dúvidas
            decorrentes de fatos não contemplados no presente contrato, estas serão dirimidas
            segundo os princípios jurídicos, aplicáveis a situação fática existente, preservando-se o
            direito da CONTRATADA, sem prejuízo da prevalência do interesse público.
        </p>
        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                CLÁUSULA DÉCIMA NONA – DA PUBLICAÇÃO:</h4>
        </div>

        <p style="text-align: justify;">
            Após as assinaturas deste contrato, o CONTRATANTE providenciará a publicação do
            resumo no Diário Oficial dos Municípios.
        </p>
        <div style="margin-bottom: 20px;">
            <img src="{{ public_path('icons/descricao-necessidade.png') }}" width="30px"
            alt="DESCRIÇÃO DA NECESSIDADE">
            <h4 style="display: inline-block; margin: 0 0 0 10px; vertical-align: middle;">
                CLÁUSULA VIGÉSIMA SEGUNDA – DO FORO:</h4>
        </div>

        <p style="text-align: justify;">
            As partes elegem o Foro da Comarca de {{ $processo->contrato->comarca }}, para dirimir dúvidas e controvérsias
            oriundas do presente Termo. <br><br>
            Para firmeza e como prova do acordado, é lavrado o presente contrato, que depois de lido
            e achado conforme, é assinado pelas partes e duas testemunhas, que também o assinam,
            dele sendo extraídas as cópias que se fizerem necessárias para seu fiel cumprimento,
            todas de igual teor e forma.
        </p>
    </div>
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
                    Prefeito Municipal <br>
                    {{ $processo->prefeitura->autoridade_competente }}
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
                    PREFEITURA MUNICIPAL DE {{ $processo->prefeitura->cidade }}
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
                    Será regida pelas normas fixadas na Concorrência Eletrônica nº {{ $processo->numero_procedimento }},
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
                    {{ $processo->prefeitura->autoridade_competente }}
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
