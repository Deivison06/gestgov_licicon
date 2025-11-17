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

        .footer-signature {
            margin-top: 60px;
            text-align: right;
        }

        .signature-block {
            margin-top: 60px;
            text-align: center;
        }

        /* ---------------------------------- */
        /* ESTILOS - CONTEÚDO PRINCIPAL */
        /* ---------------------------------- */
        .capa-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }
    </style>
</head>

<body>
    @include('Admin.Processos.pdf.capa_edital')

    {{-- QUEBRA DE PÁGINA --}}
    <div class="page-break"></div>

    <div>
        <div>
            <table style="border-collapse: collapse; width: 100%; border: 1px solid black; margin-top: 20px;">
                <thead>
                    <tr>
                        <td colspan="2" style="border: 1px solid black; text-align: center; font-weight: bold; padding: 5px; background-color:#e8e8e8;">
                            CRITÉRIOS ESPECÍFICOS DA CONTRATAÇÃO
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border: 1px solid black; padding: 5px; font-weight: bold; width: 50%;">
                            CRITÉRIO DE JULGAMENTO
                        </td>
                        <td style="border: 1px solid black; padding: 5px;">
                            MENOR PREÇO POR LOTE
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid black; padding: 5px; font-weight: bold;">
                            FORMA DE ADJUDICAÇÃO
                        </td>
                        <td style="border: 1px solid black; padding: 5px;">
                            POR LOTE
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid black; padding: 5px; font-weight: bold;">
                            MODO DE DISPUTA
                        </td>
                        <td style="border: 1px solid black; padding: 5px;">
                            ABERTO
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid black; padding: 5px; font-weight: bold;">
                            INTERVALO ENTRE OS LANCES
                        </td>
                        <td style="border: 1px solid black; padding: 5px;">
                            {{$detalhe->intervalo_lances }}
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid black; padding: 5px; font-weight: bold;">
                            REGIME DE EXECUÇÃO
                        </td>
                        <td style="border: 1px solid black; padding: 5px;">
                            EMPREITADA POR PREÇO GLOBAL
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid black; padding: 5px; font-weight: bold;">
                            EXIGÊNCIA DE GARANTIA DE PROPOSTA
                        </td>
                        <td style="border: 1px solid black; padding: 5px;">
                            {{ strtoupper($detalhe->exigencia_garantia_proposta ?? 'NÃO INFORMADO') }}
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid black; padding: 5px; font-weight: bold;">
                            EXIGÊNCIA DE GARANTIA DE CONTRATO
                        </td>
                        <td style="border: 1px solid black; padding: 5px;">
                            {{ strtoupper($detalhe->exigencia_garantia_contrato ?? 'NÃO INFORMADO') }}
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid black; padding: 5px; font-weight: bold;">
                            PERMITE PARTICIPAÇÃO DE CONSÓRCIO
                        </td>
                        <td style="border: 1px solid black; padding: 5px;">
                            NÃO
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid black; padding: 5px; font-weight: bold;">
                            HAVERÁ INVERSÃO A FASE DE HABILITAÇÃO?
                        </td>
                        <td style="border: 1px solid black; padding: 5px;">
                            {{ strtoupper($detalhe->inversao_fase ?? 'NÃO INFORMADO') }}
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid black; padding: 5px; font-weight: bold;">
                            PRAZO DE VALIDADE DA PROPOSTA
                        </td>
                        <td style="border: 1px solid black; padding: 5px;">
                            90 (noventa) DIAS
                        </td>
                    </tr>
                </tbody>
            </table>

            <table style="border-collapse: collapse; width: 100%;  border: 1px solid black; margin-top: 20px;">
                <thead>
                    <tr>
                        <td colspan="2" style="border: 1px solid black; text-align: center; font-weight: bold; padding: 5px; background-color:#e8e8e8;">
                            DOS BENEFÍCIOS ÀS MICROEMPRESAS E EMPRESAS DE PEQUENO PORTE
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border: 1px solid black; padding: 5px; font-weight: bold; width: 50%;">
                            Itens destinados a participação exclusivamente para MEI/ME/EPP, cujo valor seja de até R$ 80.000,00 (oitenta mil reais)?<br>
                            <span style="font-weight: normal;">(Art. 48, I, Lei Complementar nº 123/2006)</span>
                        </td>
                        <td style="border: 1px solid black; padding: 5px;">
                            {{ strtoupper($detalhe->participacao_exclusiva_mei_epp ?? 'NÃO INFORMADO') }}
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid black; padding: 5px; font-weight: bold;">
                            Itens com reserva de cotas destinados a participação exclusivamente para MEI/ME/EPP?<br>
                            <span style="font-weight: normal;">(Art. 48, III, Lei Complementar nº 123/06)</span>
                        </td>
                        <td style="border: 1px solid black; padding: 5px;">
                            {{ strtoupper($detalhe->reserva_cotas_mei_epp ?? 'NÃO INFORMADO') }}
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid black; padding: 5px; font-weight: bold;">
                            Prioridade de contratação para MEI/ME/EPP sediadas local ou regionalmente, até o limite de 10% (dez por cento) do melhor preço válido?<br>
                            <span style="font-weight: normal;">(Art. 48, §3º, Lei Complementar nº 123/06)</span>
                        </td>
                        <td style="border: 1px solid black; padding: 5px;">
                            {{ strtoupper($detalhe->prioridade_contratacao_mei_epp ?? 'NÃO INFORMADO') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- QUEBRA DE PÁGINA --}}
        <div class="page-break"></div>
        <div>
            <p style="text-align: justify;">
                A {{ $processo->prefeitura->nome }}, sediado(a) {{ $processo->prefeitura->endereco }}, inscrita no CNPJ: {{ $processo->prefeitura->cnpj }}, torna público por meio do(a) seu
                Pregoeiro, realizará licitação, na modalidade PREGÃO, na forma ELETRÔNICA, nos termos da Lei nº 14.133, de 2021,
                Decretos Municipais e demais legislação aplicável e, ainda, de acordo com as condições estabelecidas neste Edital.
            </p>
            <p style="text-align: justify;">
                O início da Sessão de disputa de preços será realizado no dia {{ $detalhe->data_hora_fase_edital->translatedFormat('d \d\e F \d\e Y') }}, às {{ $detalhe->data_hora_fase_edital->format('H:i') }}hs, por meio de sessão virtual, com
                inserção e comunicação via plataforma digital já especificada neste instrumento de convocação.
            </p>
            <p style="text-align: justify;">
                O Pregão Eletrônico será realizado em sessão pública, por meio da INTERNET, mediante condições de segurança -
                criptografia e autenticação - em todas as suas fases. Os trabalhos serão conduzidos por servidor da PREFEITURA
                MUNICIPAL, denominado Pregoeiro.
            </p>
            <p style="text-align: justify;">
                Também fica registrado neste instrumento de convocação, que as empresas licitantes terão até o dia {{ $detalhe->data_hora_limite_edital->translatedFormat('d \d\e F \d\e Y') }}, às {{ $detalhe->data_hora_limite_edital->format('H:i') }}min, para finalizar o envio de suas propostas com as devidas exigências do edital e documentos de habilitação
                pertinentes à futura disputa.
            </p>

            <p style="display: flex; align-items: center; font-weight: bold; ">
                <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 1. DO OBJETO
            </p>

            <p style="text-align: justify;">
                1.1 O objeto da presente licitação é a {!! strip_tags($processo->objeto) !!} conforme condições,
                quantidades e exigências estabelecidas neste Edital e seus anexos.
            </p>
            <p style="text-align: justify;">
                1.2. A licitação será dividida em grupos, formados por um ou mais itens, conforme tabela constante
                do Termo de Referência, facultando-se ao licitante a participação em quantos grupos forem de seu
                interesse, devendo oferecer proposta para todos os itens que os compõem o lote, conforme
                justificativa abaixo:
            </p>
            <p style="text-align: justify;">
                A presente justificativa tem por objetivo demonstrar a vantajosidade da contratação do objeto em
                LOTES, ao invés da aquisição ou contratação individualizada por itens, conforme os princípios e
                diretrizes estabelecidos pela Lei nº 14.133/2021, especialmente no art. 5º (princípios da eficiência
                e planejamento) e no art. 40, §1º, que dispõe:
            </p>
            <p style="text-align: justify;">
                “A administração pública poderá dividir o objeto da contratação em lotes, sempre que técnica e
                economicamente viável, visando à ampliação da competitividade e ao desenvolvimento do
                mercado local, regional ou nacional, conforme o caso.”
            </p>
            <p style="text-indent: 30px;">A contratação por lotes permite:</p>
            <ul>
                <li>
                    Melhor organização e gestão contratual, ao reduzir o número de fornecedores e simplificar
                    o acompanhamento das entregas ou da prestação dos serviços;
                </li>
                <li>
                    Centralização de responsabilidades, evitando múltiplos prazos, locais de entrega e agentes
                    executores;
                </li>
                <li>
                    Facilidade logística, pois os lotes são organizados por natureza ou destinação dos itens (ex:
                    lotes por tipo de material, setor usuário ou região de entrega);
                </li>
                <li>
                    Adoção de cronogramas otimizados, com menos risco de atrasos por fragmentação
                    excessiva de contratos.
                </li>
            </ul>
            <p style="text-indent: 30px; font-weight: bold;">VANTAGENS ECONÔMICAS</p>
            <ul>
                <li>
                    Redução de custos operacionais, tanto para a Administração quanto para os fornecedores
                    (ex: transporte, emissão de notas, gestão de pedidos);
                </li>
                <li>
                    Estimulação da competitividade saudável, uma vez que empresas de médio porte podem
                    participar de lotes especializados, e empresas menores de lotes regionais ou setoriais.
                </li>
            </ul>
            <p style="text-indent: 30px; font-weight: bold;">VANTAGENS NA FISCALIZAÇÃO E CONTROLE </p>
            <ul>
                <li>
                    Facilidade de fiscalização: menos contratos a serem monitorados e maior coerência entre
                    os itens de cada lote;
                </li>
                <li>
                    Redução de inconsistências entre entregas: evitando divergências de padrões ou prazos
                    quando múltiplas empresas atuam em paralelo em itens correlatos.
                </li>
            </ul>
            <p style="text-indent: 30px; text-align: justify;">
                A análise técnica e econômica da contratação indica que a divisão do objeto em lotes
                representa a solução mais vantajosa para a Administração Pública, ao permitir:
            </p>
            <ul>
                <li>
                    Racionalização da contratação e execução;
                </li>
                <li>
                    Maior eficiência administrativa e operacional;
                </li>
                <li>
                    Aderência ao planejamento de compras centralizadas;
                </li>
                <li>
                    Observância dos princípios da economicidade, eficiência e interesse público.
                </li>
            </ul>
            <p style="text-align: justify;">
                Assim, justifica-se plenamente a adoção da contratação por lotes, em detrimento da contratação
                por itens isolados
            </p>
            <p style="text-align: justify;">
                Por fim, a adoção deste modelo impacta diretamente no atendimento ao interesse público e na
                eficiência da contratação. A estrutura em lotes assegura que as necessidades imediatas da
                população sejam atendidas de maneira mais célere, visto que diferentes tipos de itens poderão
                estar disponíveis simultaneamente. Isso reduz o tempo de espera para o fornecimento, resultando
                em melhorias tangíveis na qualidade do fornecimento pretendido. Assim, a estratégia de licitação
                em lotes representa uma solução prática e eficiente para os desafios enfrentados pela Prefeitura,
                refletindo um compromisso com a transparência e a máxima utilidade dos recursos públicos.
            </p>

            <p style="text-align: justify;">
                1.3. Este certame licitatório obedecerá a seguinte ordem procedimental:
            </p>
            @if ($detalhe->inversao_fase === 'sim')
            <ol type="a" style="text-align: justify; ">
                <li style="margin-bottom: 6px;">
                    Fase de inserção do valor da proposta: Nesta fase, no período de divulgação do certame até o último minuto previsto para o fim do envio das propostas, prazo este improrrogável, os licitantes irão inserir o arquivo de ficha técnica exigido neste edital e os valores globais de sua proposta, os quais, em hipótese alguma, poderá ser superior ao valor global estimado pelo Edital, sob pena de desclassificação de sua proposta e consequente impossibilidade de disputar a fase de lances;
                </li>

                <li style="margin-bottom: 6px;">
                    Fase de lances: Nesta fase, os licitantes que cumprirem a exigências contidas na
                    alínea “a”, irão estabelecer lances sucessivos, obedecendo o critério de menor
                    preço global, dentro do tempo limite de 10 (dez) minutos estabelecidos pelo edital,
                    assim como, suas respectivas prorrogações de 2 (dois) minutos, os quais serão
                    sistematicamente controlados pelo Sistema Eletrônico do portal designado para a
                    realização da sessão;
                </li>

                <li style="margin-bottom: 6px;">
                    Fase de Habilitação: Nesta fase, o licitante classificado em primeiro lugar, obedecendo o critério de menor preço por lote, terá sua proposta inicial, documentos de habilitação e demais exigências contidas neste edital e no Termo de Referência, analisadas para efeito de classificação e prosseguimento para a fase seguinte. Também será analisado nesta fase, a respectiva exequibilidade da proposta informada na fase de lances, a qual deverá obedecer aos critérios legais previstos na Lei 14.133/2021 e no próprio edital;
                </li>

                <li style="margin-bottom: 6px;">
                    Fase de Recursos: As empresas licitantes que discordarem das decisões proferidas poderão registrar as razões de seu recurso em campo específico do sistema, vedada a manifestação via “chat”, dentro do prazo de 30 (trinta) minutos improrrogáveis, a contar da autorização do pregoeiro;
                </li>

                <li style="margin-bottom: 6px;">
                    Fase de Adjudicação: Nesta fase, o licitante que for declarado habilitado na fase de documentos de habilitação, terá o objeto adjudicado a seu favor, sendo posteriormente declarado vencedor do certame.
                </li>
            </ol>
            @else
            <ol type="a" style="text-align: justify; padding-left: 20px;">
                <li style="margin-bottom: 6px;">
                    Fase de inserção do valor da proposta: Nesta fase, no período de divulgação
                    do certame até o último minuto previsto para o fim do envio das propostas,
                    prazo este improrrogável, os licitantes irão inserir o arquivo de ficha técnica
                    exigido neste edital e os valores globais de sua proposta, os quais, em
                    hipótese alguma, poderá ser superior ao valor global estimado pelo Edital, sob
                    pena de desclassificação de sua proposta e consequente impossibilidade de
                    disputar a fase de lances;
                </li>

                <li style="margin-bottom: 6px;">
                    Fase de lances: Nesta fase, os licitantes que cumprirem a exigências
                    contidas na alínea “a”, irão estabelecer lances sucessivos, obedecendo o
                    critério de menor preço global, dentro do tempo limite de 10 (dez) minutos
                    estabelecidos pelo edital, assim como, suas respectivas prorrogações de 2
                    (dois) minutos, os quais serão sistematicamente controlados pelo Sistema
                    Eletrônico do Portal BNC;
                </li>

                <li style="margin-bottom: 6px;">
                    Fase de Habilitação: Nesta fase, o licitante que tiver sua proposta
                    classificada na fase anterior, terá seus documentos de habilitação
                    devidamente analisados, conforme as devidas exigências previstas neste
                    instrumento convocatório;
                </li>

                <li style="margin-bottom: 6px;">
                    Fase de Recurso: Nesta fase, as empresas licitantes que discordarem das
                    decisões proferidas neste certame, deverão inserir em campo especifico,
                    vedado a sua manifestação via “chat”, manifestarem as razões de seu recurso,
                    dentro do tempo limite de 30 (trinta) minutos, improrrogáveis, a ser autorizado
                    pelo pregoeiro(a);
                </li>

                <li style="margin-bottom: 6px;">
                    Fase de Adjudicação: Nesta fase, o licitante que for declarado habilitado na
                    fase de documentos de habilitação, terá o objeto adjudicado a seu favor,
                    sendo posteriormente declarado vencedor do certame.
                    <br><br>
                    1.4. Nenhum licitante passará para a fase seguinte, sem o devido cumprimento das
                    exigências contidas em cada fase, sob pena de desclassificação ou inabilitação.
                    <br><br>
                    1.5. Na fase de lances, cada empresa licitante poderá inserir quantos lances forem
                    necessários, ficando resguardado apenas os critérios de inexequibilidade de
                    proposta, que serão devidamente verificados na fase de habilitação.
                    <br><br>
                    1.6. Na fase recursal, após o inicial da contagem do tempo de 30 (trinta) minutos,
                    será aberto campo específico para que as manifestações dos licitantes sejam
                    devidamente registradas e reconhecidas pelo Sistema do portal designado para a
                    realização da sessão, não sendo aceitas, em nenhuma hipótese, manifestações
                    recursais inseridas dentro do campo de “chat”.
                </li>
            </ol>
            @endif
        </div>
        <div>
            <p style="display: flex; align-items: center; font-weight: bold; ">
                <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 2. DAS CONDIÇÕES DE PARTICIPAÇÃO
            </p>
            <p style="text-align: justify;">
                2.1 Poderão participar do processo os interessados que atenderem a todas as exigências contidas neste edital e anexos,
                para os fins do objeto pleiteado e estejam devidamente cadastrados e credenciados no Portal {{ $detalhe->portal }}, que atuará
                como órgão provedor do Sistema Eletrônico.
            </p>
            <p style="text-align: justify;">
                2.2. O licitante responsabiliza-se exclusiva e formalmente pelas transações efetuadas em seu nome, assume como firmes e
                verdadeiras suas propostas e seus lances, inclusive os atos praticados diretamente ou por seu representante, excluída a
                responsabilidade do provedor do sistema ou do órgão ou entidade promotora da licitação por eventuais danos decorrentes
                de uso indevido das credenciais de acesso, ainda que por terceiros.
            </p>
            <p style="text-align: justify;">
                2.3. É de responsabilidade do cadastrado conferir a exatidão dos seus dados cadastrais nos Sistemas relacionados no item
                anterior e mantê-los atualizados junto aos órgãos responsáveis pela informação, devendo proceder, imediatamente, à
                correção ou à alteração dos registros tão logo identifique incorreção ou aqueles se tornem desatualizados.
            </p>
            <p style="text-align: justify;">
                2.4. A não observância do disposto no item anterior poderá ensejar desclassificação no momento da habilitação.
            </p>
            <p style="text-align: justify;">
                2.5. A obtenção do benefício a que se refere o item anterior fica limitada às microempresas e às empresas de pequeno porte
                que, no ano-calendário de realização da licitação, ainda não tenham celebrado contratos com a Administração Pública cujos
                valores somados extrapolem a receita bruta máxima admitida para fins de enquadramento como empresa de pequeno porte.
            </p>
            <p style="text-align: justify;">
                2.6. Será concedido tratamento favorecido para as microempresas e empresas de pequeno porte, para as sociedades
                cooperativas mencionadas no artigo 16 da Lei nº 14.133, de 2021, para o agricultor familiar, o produtor rural pessoa física e
                para o microempreendedor individual - MEI, nos limites previstos da Lei Complementar nº 123, de 2006.
            </p>

            @if($detalhe->participacao_exclusiva_mei_epp === 'sim')
            <p style="text-align: justify;">
                2.7. Para os itens {{ $detalhe->numero_items }} a participação é exclusiva a microempresas e empresas de pequeno porte, nos termos do art.
                48 da Lei Complementar nº 123, de 14 de dezembro de 2006
            </p>
            @endif
            <p style="text-align: justify;">
                2.8. Não poderão disputar esta licitação:
            </p>
            <ol type="a" style="text-align: justify;">
                <li style="margin-bottom: 6px;">
                    aquele que não atenda às condições deste Edital e seu(s) anexo(s);
                </li>
                <li style="margin-bottom: 6px;">
                    autor do anteprojeto, do projeto básico ou do projeto executivo, pessoa física ou jurídica, quando a licitação versar
                    sobre serviços ou fornecimento de bens a ele relacionados;
                </li>
                <li style="margin-bottom: 6px;">
                    empresa, isoladamente ou em consórcio, responsável pela elaboração do projeto básico ou do projeto executivo,
                    ou empresa da qual o autor do projeto seja dirigente, gerente, controlador, acionista ou detentor de mais de 5% (cinco por
                    cento) do capital com direito a voto, responsável técnico ou subcontratado, quando a licitação versar sobre serviços ou
                    fornecimento de bens a ela necessários;
                </li>
                <li style="margin-bottom: 6px;">
                    pessoa física ou jurídica que se encontre, ao tempo da licitação, impossibilitada de participar da licitação em
                    decorrência de sanção que lhe foi imposta;
                </li>
                <li style="margin-bottom: 6px;">
                    aquele que mantenha vínculo de natureza técnica, comercial, econômica, financeira, trabalhista ou civil com
                    dirigente do órgão ou entidade contratante ou com agente público que desempenhe função na licitação ou atue na
                    fiscalização ou na gestão do contrato, ou que deles seja cônjuge, companheiro ou parente em linha reta, colateral ou por
                    afinidade, até o terceiro grau;
                </li>
                <li style="margin-bottom: 6px;">
                    empresas controladoras, controladas ou coligadas, nos termos da Lei nº 6.404, de 15 de dezembro de 1976,
                    concorrendo entre si;
                </li>
                <li style="margin-bottom: 6px;">
                    pessoa física ou jurídica que, nos 5 (cinco) anos anteriores à divulgação do edital, tenha sido condenada
                    judicialmente, com trânsito em julgado, por exploração de trabalho infantil, por submissão de trabalhadores a condições
                    análogas às de escravo ou por contratação de adolescentes nos casos vedados pela legislação trabalhista;
                </li>
                <li style="margin-bottom: 6px;">
                    agente público do órgão ou entidade licitante;
                </li>
                <li style="margin-bottom: 6px;">
                    pessoas jurídicas reunidas em consórcio;
                </li>
                <li style="margin-bottom: 6px;">
                    Organizações da Sociedade Civil de Interesse Público (OSCIP), atuando nessa condição.
                </li>
            </ol>
            <p style="text-align: justify;">
                2.9. Não poderá participar, direta ou indiretamente, da licitação ou da execução do contrato agente público do órgão ou
                entidade contratante, devendo ser observadas as situações que possam configurar conflito de interesses no exercício ou
                após o exercício do cargo ou emprego, nos termos da legislação que disciplina a matéria, conforme § 1º do art. 9º da Lei n.º
                14.133, de 2021
            </p>
            <p style="text-align: justify;">
                O tratamento diferenciado conferido às empresas de pequeno porte, às microempresas e às cooperativas de que tratam a
                Lei Complementar 123, de 14 de dezembro de 2006 e a Lei 11.488, de 15 de junho de 2007, deverá seguir o procedimento
                descrito a seguir:
            </p>
            <ol type="a" style="text-align: justify;">
                <li style="margin-bottom: 6px;">
                    Os licitantes deverão indicar no sistema eletrônico de licitações, antes do encaminhamento da proposta eletrônica
                    de preços, a sua condição de microempresa, empresa de pequeno porte ou cooperativa.
                </li>
                <li style="margin-bottom: 6px;">
                    O licitante que não informar sua condição antes do envio das propostas perderá o direito ao tratamento diferenciado.
                </li>
                <li style="margin-bottom: 6px;">
                    Ao final da sessão pública de disputa de lances, o sistema eletrônico detectará automaticamente as situações de
                    empate a que se referem os §§ 1o e 2o do art. 44 da Lei Complementar 123/2006, de 14 de dezembro de 2006.
                </li>
                <li style="margin-bottom: 6px;">
                    Considera-se empate aquelas situações em que as propostas apresentadas pelas microempresas, empresas de
                    pequeno porte e cooperativas sejam iguais ou até 5% (cinco por cento) superiores à proposta mais bem classificada, quando
                    esta for proposta de licitante não enquadrado como microempresa, empresa de pequeno porte ou cooperativa.
                </li>
                <li style="margin-bottom: 6px;">
                    Não ocorre empate quando a detentora da proposta mais bem classificada possuir a condição de microempresa,
                    empresa de pequeno porte ou cooperativa. Nesse caso, o pregoeiro convocará a arrematante a apresentar os documentos
                    de habilitação.
                </li>
                <li style="margin-bottom: 6px;">
                    Caso ocorra a situação de empate descrita, o pregoeiro convocará o representante da empresa de pequeno porte,
                    da microempresa ou da cooperativa mais bem classificada, imediatamente e por meio do sistema eletrônico, a ofertar lance
                    inferior ao menor lance registrado para o item no prazo de cinco minutos.
                </li>
                <li style="margin-bottom: 6px;">
                    Caso a licitante convocada não apresente lance inferior ao menor valor registrado no prazo acima indicado, as
                    demais microempresas, empresas de pequeno porte ou cooperativas que porventura possuam lances ou propostas, deverão
                    ser convocadas, na ordem de classificação, a ofertar lances inferiores à menor proposta.
                </li>
                <li style="margin-bottom: 6px;">
                    A microempresa, empresa de pequeno porte ou cooperativa que primeiro apresentar lance inferior ao menor lance
                    ofertado na sessão de disputa será considerada arrematante pelo pregoeiro, que encerrará a disputa do item na sala virtual,
                    e que deverá apresentar a documentação de habilitação e da proposta de preços.
                </li>
                <li style="margin-bottom: 6px;">
                    O não oferecimento de lances no prazo específico destinado a cada licitante produz a preclusão do direito de
                    apresentá-los. Os lances apresentados em momento inadequado, antes do início do prazo específico ou após o seu término
                    serão considerados inválidos.
                </li>
                <li style="margin-bottom: 6px;">
                    Caso a proposta inicialmente mais bem classificada, de licitante não enquadrado como microempresa, empresa de
                    pequeno porte ou cooperativa, seja desclassificada pelo pregoeiro, por desatendimento ao edital, essa proposta não é mais
                    considerada como parâmetro para o efeito do empate de que trata esta cláusula.
                </li>
                <li style="margin-bottom: 6px;">
                    Para o efeito do empate, no caso da desclassificação de que trata o item anterior, a melhor proposta passa a ser a
                    da próxima licitante não enquadrada como microempresa, empresa de pequeno porte ou cooperativa.
                </li>
                <li style="margin-bottom: 6px;">
                    No caso de o sistema eletrônico não convocar automaticamente a microempresa, empresa de pequeno porte ou
                    cooperativa, o pregoeiro o fará através do “chat de mensagens”.
                </li>
                <li style="margin-bottom: 6px;">
                    A partir da convocação, a microempresa, empresa de pequeno porte ou cooperativa, terá, caso o pregoeiro ache
                    necessário, até 24 (vinte e quatro) horas para oferecer proposta inferior à então mais bem classificada, através do “chat de
                    mensagens”, sob pena de preclusão de seu direito.
                </li>
                <li style="margin-bottom: 6px;">
                    Caso a microempresa, empresa de pequeno porte ou cooperativa exercite o seu direito de apresentar proposta
                    inferior a mais bem classificada, terá, a partir da apresentação desta no “chat de mensagens”, oportunidade para
                    encaminhar a documentação de habilitação e proposta de preços,
                </li>
                <li style="margin-bottom: 6px;">
                    O julgamento da habilitação das microempresas, empresas de pequeno porte e cooperativas obedecerá aos
                    critérios gerais definidos neste edital, observadas as particularidades de cada pessoa jurídica.
                </li>
                </li>
                Havendo alguma restrição na comprovação da regularidade fiscal, será assegurado às microempresas, empresas
                de pequeno porte e cooperativas um prazo adicional de 05 (cinco) dias úteis para a regularização da documentação,
                contados a partir da notificação da irregularidade pelo pregoeiro. O prazo de 05 (cinco) dias úteis poderá ser prorrogado por
                igual período se houver manifestação expressa do interessado antes do término do prazo inicial.
                </li>
            </ol>
        </div>
        <div>
            <p style="display: flex; align-items: center; font-weight: bold; ">
                <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 3. DO CREDENCIAMENTO DO LICITANTE
            </p>
            <p style="text-align: justify;">
                3.1. Para participar do pregão, o licitante deverá se credenciar no Portal de Licitações através do site “{{ $detalhe->portal }}”.
            </p>
            <p style="text-align: justify;">
                3.2. O credenciamento dar-se-á pela atribuição de chave de identificação e de senha, pessoal e intransferível, para acesso
                ao sistema eletrônico.
            </p>
            <p style="text-align: justify;">
                3.3. O uso da senha de acesso ao sistema eletrônico é de exclusiva responsabilidade da licitante, incluindo qualquer
                transação efetuada diretamente, ou por seu representante, na inserção de dados ou arquivos, não cabendo ao provedor do
                sistema ou ao Município, responsabilidade por eventuais equívocos ou danos decorrentes de uso indevido da senha, ainda
                que por terceiros.
            </p>
            <p style="text-align: justify;">
                3.4. O credenciamento junto ao provedor do sistema implica a responsabilidade legal da licitante ou seu representante legal
                e a presunção de sua capacidade técnica para realização das transações inerentes ao pregão eletrônico.
            </p>
            <p style="display: flex; align-items: center; font-weight: bold; ">
                <img src="{{ public_path('icons/dinheiro.png') }}" width="20" style="margin-right: 10px;"> 4. DO ENVIO DAS PROPOSTAS
            </p>
            <p style="text-align: justify;">
                4.1. Após a divulgação do edital no endereço eletrônico, as licitantes deverão, até a data e hora marcadas para recebimento
                das propostas, informar os valores de sua proposta, de acordo com a forma de adjudicação adotada pelo edital, por meio do
                sistema eletrônico, quando, então, encerrar-se-á, automaticamente, a fase de recebimento de propostas.
            </p>
            <p style="text-align: justify;">
                4.2. O licitante deverá enviar sua proposta mediante o preenchimento, no sistema eletrônico, dos seguintes campos:
            </p>
            <ol type="a" style="text-align: justify;">
                <li style="margin-bottom: 6px;">
                    valor do item;
                </li>
                <li style="margin-bottom: 6px;">
                    Marca;
                </li>
                <li style="margin-bottom: 6px;">
                    Fabricante;
                </li>
                <li style="margin-bottom: 6px;">
                    Descrição do objeto, contendo as informações similares à especificação do Termo de Referência;
                </li>
            </ol>
            <p style="text-align: justify;">
                4.3. Todas as especificações do objeto contidas na proposta vinculam o licitante.
            </p>
            <p style="text-align: justify;">
                4.4. Nos valores propostos estarão inclusos todos os custos operacionais, encargos previdenciários, trabalhistas,
                tributários, comerciais e quaisquer outros que incidam direta ou indiretamente na execução do objeto.
            </p>
            <p style="text-align: justify;">
                4.5. A proposta inicial também deverá apresentar sua validade, que deverá ser de no mínimo 90 (noventa) dias, a contar da
                data da sessão de abertura desta licitação, a qual torna-se necessária para efeitos de assinatura contratual, atualização de
                garantias iniciais firmadas em sessão, além de verificação das condições reais das empresas em face de benefícios gerados
                pela Lei Complementar 123, nos casos especiais de dilação de prazos.
            </p>
            <p style="text-align: justify;">
                4.6. Até a abertura da sessão, as licitantes poderão retirar ou substituir a proposta anteriormente apresentada.
            </p>
            <p style="text-align: justify;">
                4.7. Ao encaminhar a proposta de preços na forma prevista pelo sistema eletrônico, a licitante deverá preencher as informações no campo “CADASTRO PROPOSTA” e anexar FICHA TÉCNICA em arquivo PDF no campo apropriado do sistema da Bolsa Nacional de Compras - BNC, sendo vedada a identificação do licitante por qualquer meio.
            </p>
            <p style="text-align: justify;">
                4.8. Na ficha técnica de preços não deve conter identificação do licitante como: nome, razão social ou timbre do proponente, endereço, telefone, fax e endereço de correio eletrônico, nome do representante, carteira de identidade e cargo na empresa ou qualquer outra forma que possa identificar a proposta.
            </p>
            <p style="text-align: justify;">
                4.9. Ao encaminhar a proposta de preços na forma prevista pelo sistema eletrônico,
                a licitante deverá preencher as informações no campo “CADASTRO PROPOSTA” e
                anexar FICHA TÉCNICA em arquivo PDF no campo apropriado do sistema do portal
                designado para a realização da sessão, sendo vedada a identificação do licitante
                por qualquer meio.
            </p>
            <p style="text-align: justify;">
                4.10. Na ficha técnica de preços não deve conter identificação do licitante como:
                nome, razão social ou timbre do proponente, endereço, telefone, fax e endereço de
                correio eletrônico, nome do representante, carteira de identidade e cargo na
                empresa ou qualquer outra forma que possa identificar a proposta.
            </p>
            <p style="text-align: justify;">
                4.11. Deve conter o detalhamento dos serviços ofertados, indicando, prazo de
                garantia ou validade, e prazo de entrega.
            </p>
            <p style="text-align: justify;">
                4.12. Preço unitário do item, cotando-se cada produto discriminado no item, em
                moeda corrente nacional, em algarismo com até 02 (duas) casas decimais após a
                vírgula e por extenso. O preço total deverá ser indicado em algarismos e por
                extenso. Nos preços propostos deverão estar incluídos, além do lucro, todas as
                despesas e custos, como por exemplo: transportes (fretes), montagem e
                instalação, tributos de qualquer natureza e todas as despesas, diretas ou indiretas,
                relacionadas com o perfeito fornecimento do objeto desta licitação.
            </p>
        </div>
        <div>
            <p style="display: flex; align-items: center; font-weight: bold; ">
                <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 5. DA ABERTURA DA SESSÃO, CLASSIFICAÇÃO DAS PROPOSTAS E
                FORMULAÇÃO DE LANCES
            </p>
            <p style="text-align: justify;">
                5.1. Encerrado o prazo de recebimento das propostas o pregoeiro, via sistema eletrônico, dará início à Sessão Pública, na
                data e horário previstos neste Edital, verificando a validade dos valores iniciais de propostas conforme exigências do edital.
            </p>
            <p style="text-align: justify;">
                5.2. Será desclassificada a proposta ou os valores inseridos no sistema que:
            </p>
            <ol type="a" style="text-align: justify;">
                <li style="margin-bottom: 6px;">
                    Deixar de atender alguma exigência deste edital;
                </li>
                <li style="margin-bottom: 6px;">
                    Oferecer vantagem não prevista neste edital ou ainda preço e/ou vantagem baseada em propostas das demais
                    licitantes.
                </li>
                <li style="margin-bottom: 6px;">
                    A proposta ou o lance vencedor, que apresentar preço final ou unitário superior ao preço máximo fixado no Termo de
                    Referência (Acórdão nº 1455/2018 -TCU - Plenário), desconto menor do que o mínimo exigido ou que apresentar preço
                    manifestamente inexequível, onde neste último, será obrigatória e exigida pelo Pregoeiro a apresentação de prova de
                    composição de custos juntos com Notas Fiscais de produtos anteriormente comercializados, que comprove de forma
                    inequívoca, que o preço apresentado em sessão, após a quebra de preços, apresente margem de lucratividade ideal para o
                    cumprimento do contrato.
                </li>
                <li style="margin-bottom: 6px;">
                    Apresentar quantitativo dos itens, diferente do que foi estipulado no Termo de Referência.
                </li>
            </ol>
            <p style="text-align: justify;">
                5.3. Iniciada a etapa competitiva, os licitantes deverão encaminhar lances exclusivamente por meio de sistema eletrônico,
                sendo imediatamente informados do seu recebimento e do valor consignado no registro.
            </p>
            <p style="text-align: justify;">
                5.4. O lance deverá ser ofertado pelo valor Total.
            </p>
            <p style="text-align: justify;">
                5.5. O intervalo mínimo de diferença de valores ou percentuais entre os lances, que
                incidirá tanto em relação aos lances intermediários quanto em relação à proposta
                que cobrir a melhor oferta deverá ser de R$ 10,00 (dez reais) do valor total do LOTE.
            </p>
            <p style="text-align: justify;">
                5.6. O modo de disputa adotado para o envio de lances no pregão eletrônico será o “aberto”, os licitantes apresentarão
                lances públicos e sucessivos, com prorrogações.
            </p>
            <p style="text-align: justify;">
                5.7. Após o término dos prazos estabelecidos nos subitens anteriores, o sistema ordenará e divulgará os lances segundo a
                ordem crescente de valores.
            </p>
            <p style="text-align: justify;">
                5.8. No caso de desconexão com o Pregoeiro, no decorrer da etapa competitiva do Pregão, o sistema eletrônico poderá
                permanecer acessível aos licitantes para a recepção dos lances.
            </p>
            <p style="text-align: justify;">
                5.9. Quando a desconexão do sistema eletrônico para o pregoeiro persistir por tempo superior a dez minutos, a sessão
                pública será suspensa e reiniciada somente após decorridas vinte e quatro horas da comunicação do fato pelo Pregoeiro aos
                participantes, no sítio eletrônico utilizado para divulgação.
            </p>
            <p style="text-align: justify;">
                5.10. Caso o licitante não apresente lances, concorrerá com o valor de sua proposta.
            </p>
            <p style="text-align: justify;">
                5.11. Em relação a itens não exclusivos para participação de microempresas e empresas de pequeno porte, uma vez
                encerrada a etapa de lances, será efetivada a verificação automática, junto à Receita Federal, do porte da entidade
                empresarial. O sistema identificará em coluna própria as microempresas e empresas de pequeno porte participantes,
                procedendo à comparação com os valores da primeira colocada, se esta for empresa de maior porte, assim como das
                demais classificadas, para o fim de aplicar-se o disposto nos arts. 44 e 45 da Lei Complementar nº 123, de 2006,
                regulamentada pelo Decreto nº 8.538, de 2015.
            </p>
            <p style="text-align: justify;">
                5.12. Nessas condições, as propostas de microempresas e empresas de pequeno porte que se encontrarem na faixa de até
                5% (cinco por cento) acima da melhor proposta ou melhor lance serão consideradas empatadas com a primeira colocada.
            </p>
            <p style="text-align: justify;">
                5.13. A mais bem classificada nos termos do subitem anterior terá o direito de encaminhar uma última oferta para
                desempate, obrigatoriamente em valor inferior ao da primeira colocada, no prazo de 5 (cinco) minutos controlados pelo
                sistema, contados após a comunicação automática para tanto.
            </p>
            <p style="text-align: justify;">
                5.14. Caso a microempresa ou a empresa de pequeno porte melhor classificada desista ou não se manifeste no prazo
                estabelecido, serão convocadas as demais licitantes microempresa e empresa de pequeno porte que se encontrem naquele
                intervalo de 5% (cinco por cento), na ordem de classificação, para o exercício do mesmo direito, no prazo estabelecido no
                subitem anterior.
            </p>
            <p style="text-align: justify;">
                5.15. No caso de equivalência dos valores apresentados pelas microempresas e empresas de pequeno porte que se
                encontrem nos intervalos estabelecidos nos subitens anteriores, será realizado sorteio entre elas para que se identifique
                aquela que primeiro poderá apresentar melhor oferta.
            </p>
            <p style="text-align: justify;">
                5.16. Encerrada a etapa de envio de lances da sessão pública, na hipótese da proposta do primeiro colocado permanecer
                acima do preço máximo ou inferior ao desconto definido para a contratação, o pregoeiro poderá negociar condições mais
                vantajosas, após definido o resultado do julgamento.
            </p>
            <p style="text-align: justify;">
                5.17. A negociação poderá ser feita com os demais licitantes, segundo a ordem de classificação inicialmente estabelecida,
                quando o primeiro colocado, mesmo após a negociação, for desclassificado em razão de sua proposta permanecer acima
                do preço máximo definido pela Administração.
            </p>
            <p style="text-align: justify;">
                5.18. A negociação será realizada por meio do sistema, podendo ser acompanhada pelos demais licitantes.
            </p>
            <p style="text-align: justify;">
                5.19 O resultado da negociação será divulgado a todos os licitantes e anexado aos autos do processo licitatório
            </p>
            <p style="text-align: justify;">
                5.20. O pregoeiro solicitará ao licitante mais bem classificado que, no prazo de 2 (duas) horas, envie a proposta adequada ao
                último lance ofertado após a negociação realizada, acompanhada, se for o caso, dos documentos complementares, quando
                necessários à confirmação daqueles exigidos neste Edital e já apresentados.
            </p>
            <p style="text-align: justify;">
                5.21. É facultado ao pregoeiro prorrogar o prazo estabelecido, a partir de solicitação fundamentada feita no chat pelo
                licitante, antes de findo o prazo.
            </p>
            <p style="text-align: justify;">
                5.22. Na hipótese da proposta de menor valor desatender às exigências da proposta de preços o Pregoeiro examinará a
                proposta subsequente, verificando a sua aceitabilidade e procedendo na ordem de classificação, segundo o critério do
                Menor Preço e assim sucessivamente até a apuração de uma proposta que atenda ao edital.
            </p>
            <p style="text-align: justify;">
                5.23. Todas as propostas após a fase de negociação apresentarem valores inferiores a 50% (cinquenta por cento) do valor
                iniciar orçado pela administração, a administração irá reconhecer situação de presunção de inexequibilidade, abrindo
                diligências para apuração da veracidade e viabilidade do preço ofertado.
            </p>
            <p style="text-align: justify;">
                5.24. Caso o Termo de Referência exija a apresentação de amostra, o licitante classificado em primeiro lugar deverá
                apresentá-la, conforme disciplinado no Termo de Referência, sob pena de não aceitação da proposta.
            </p>
            <p style="text-align: justify;">
                5.25. Por meio de mensagem no sistema, será divulgado o local e horário de realização do procedimento para a avaliação
                das amostras, cuja presença será facultada a todos os interessados, incluindo os demais licitantes.
            </p>
            <p style="text-align: justify;">
                5.26. Os resultados das avaliações serão divulgados por meio de mensagem no sistema.
            </p>
            <p style="text-align: justify;">
                5.27. No caso de não haver entrega da amostra ou ocorrer atraso na entrega, sem justificativa aceita pelo Pregoeiro, ou
                havendo entrega de amostra fora das especificações previstas neste Edital, a proposta do licitante será recusada.
            </p>
            <p style="text-align: justify;">
                5.28. Se a(s) amostra(s) apresentada(s) pelo primeiro classificado não for(em) aceita(s), o Pregoeiro analisará a
                aceitabilidade da proposta ou lance ofertado pelo segundo classificado. Seguir-se-á com a verificação da(s) amostra(s) e,
                assim, sucessivamente, até a verificação de uma que atenda às especificações constantes no Termo de Referência.
            </p>
            <p style="text-align: justify;">
                5.29. O pregoeiro solicitará ao licitante mais bem classificado que, no prazo de 2
                (duas) horas, envie a proposta adequada ao último lance ofertado após a
                negociação realizada, acompanhada, se for o caso, dos documentos
                complementares, quando necessários à confirmação daqueles exigidos neste
                Edital e já apresentados, com as seguintes informações:
                <ul type="a">
                    <li>valor do item;</li>
                    <li>Descrição do objeto, contendo as informações similares à especificação
                        do Termo de Referência;</li>
                    <li>
                        Prazo de Garantia;
                    </li>
                    <li>Validade do Produto ou Prazo de Garantia; </li>
                    <li>
                        Na proposta de preços readequado, a empresa deverá apresentar, o
                        percentual de despesas diretas e indiretas de cada item a ser fornecido, assim
                        como, também, o percentual de imposto incidente.
                    </li>
                </ul>
            </p>
            <p style="text-align: justify;">
                5.30. Será desclassificada a proposta vencedora que:
                <ul type="a">
                    <li>contiver vícios insanáveis;</li>
                    <li>não obedecer às especificações técnicas contidas no Termo de Referência; </li>
                    <li>
                        apresentar preços inexequíveis ou permanecerem acima do preço máximo
                        definido para a contratação;
                    </li>
                    <li>não tiverem sua exequibilidade demonstrada, quando exigido pela
                        Administração; </li>
                    <li>
                        apresentar desconformidade com quaisquer outras exigências deste Edital
                        ou seus anexos, desde que insanável.
                    </li>
                </ul>
            </p>
            <p style="text-align: justify;">
                5.31. Na elaboração da proposta adequada ao seu lance final, os licitantes deverão
                obrigatoriamente apresentar proposta readequada com percentual de desconto
                fixo e linear sobre os preços de todos os ITENS/LOTES. A exigência busca evitar o a
                manipulação e jogo de planilhas, além disso, busca garantir a isonomia entre os
                licitantes.
            </p>
            <p style="text-align: justify;">
                5.32. A licitante deverá, dentro do prazo estipulado para envio da Proposta
                Readequada, realizar a redefinição dos valores unitários junto a plataforma onde
                será realizada a licitação, seguindo o exigido no ITEM 5.31, sob pena de inabilitação.
            </p>
        </div>
        <div>
            <p style="display: flex; align-items: center; font-weight: bold; ">
                <img src="{{ public_path('icons/check.png') }}" width="20" style="margin-right: 10px;"> 6. DA FASE DE HABILITAÇÃO
            </p>
            <p style="text-align: justify;">
                6.1. Iniciando a fase de habilitação o Pregoeiro irá convocar o licitante classificado em primeiro lugar para apresentação em
                um prazo de 02 (duas) horas os seguintes documentos:
            </p>
            <p style="text-align: justify;">
                6.2. Habilitação Jurídica:
            </p>

            <ol type="a" style="text-align: justify;">
                <li style="margin-bottom: 6px;">
                    Documentos de identificação de todos os sócios;
                </li>
                <li style="margin-bottom: 6px;">
                    Registro Comercial, no caso de empresa individual, ou;
                </li>
                <li style="margin-bottom: 6px;">
                    Ato Constitutivo, Estatuto ou Contrato Social em vigor, devidamente registrado, em se tratando de sociedades comerciais,
                    e, no caso de sociedade por ações, acompanhado de documentos de eleição de seus administradores, ou;
                </li>
                <li style="margin-bottom: 6px;">
                    Inscrição do Ato Constitutivo, no caso de sociedades civis, acompanhada de prova de diretoria em exercício, ou;
                </li>
                <li style="margin-bottom: 6px;">
                    Decreto de Autorização, em se tratando de empresa ou sociedade estrangeira em funcionamento no País, e Ato de Registro
                    ou Autorização para funcionamento expedido pelo órgão competente, quando a atividade assim o exigir.
                </li>
            </ol>

            <p style="text-align: justify;">
                6.3. Será obrigatório, sob pena de inabilitação, que o licitante tenha em seus atos constitutivos/objeto social, as atividades
                compatíveis com o objeto deste Edital;
            </p>
            <p style="text-align: justify;">
                6.4. Os licitantes deverão apresentar declaração de que suas propostas econômicas compreendem a integralidade dos
                custos para atendimento dos direitos trabalhistas assegurados na Constituição Federal, nas leis trabalhistas, nas normas
                infralegais, nas convenções coletivas de trabalho e nos termos de ajustamento de conduta vigentes na data de entrega das
                propostas.
            </p>
            <p style="text-align: justify; font-weight: bold;">
                6.5. Regularidade Fiscal e Trabalhista:
            </p>
            <ol type="a" style="text-align: justify;">
                <li style="margin-bottom: 6px;">
                    Prova de inscrição da empresa no Cadastro Nacional de Pessoa Jurídica (CNPJ), através de Comprovante de
                    Situação Cadastral emitida pela Receita Federal, com data de emissão não superior a 180 (cento e oitenta) dias;
                </li>
                <li style="margin-bottom: 6px;">
                    Prova de regularidade com a Fazenda Federal, através de Certidão emitida pela Secretaria da Receita Federal
                    conjuntamente com a Procuradora Geral da Fazenda Nacional, com data de emissão não superior a 180 (cento e oitenta)
                    dias quando não constar expressamente no corpo da Certidão o seu prazo de validade;
                </li>
                <li style="margin-bottom: 6px;">
                    Prova de regularidade para com a Fazenda Estadual, emitida pela Secretaria de Estado da Fazenda, com data de
                    emissão não superior a 90 (noventa) dias, quando não constar expressamente no corpo da mesma o seu prazo de validade;
                </li>
                <li style="margin-bottom: 6px;">
                    Prova de regularidade para com a Fazenda Municipal, emitida pelo Município sede da empresa licitante, com data
                    de emissão não superior a 90 (noventa) dias, quando não constar expressamente no corpo da mesma o seu prazo de
                    validade;
                </li>
                <li style="margin-bottom: 6px;">
                    Prova de regularidade relativa ao Fundo de Garantia por Tempo de Serviço - FGTS (CRF), demonstrando a situação
                    regular no cumprimento dos encargos instituídos por Lei;
                </li>
                <li style="margin-bottom: 6px;">
                    Prova de inexistência de débitos inadimplidos perante a Justiça do Trabalho, mediante a apresentação de Certidão Negativa
                    de Débitos Trabalhistas (CNDT), emitida pelo TST - Tribunal Superior do Trabalho, com data de emissão não superior a 180
                    (cento e oitenta) dias, quando não constar expressamente no corpo da Certidão o seu prazo de validade. (Lei 12.440/2011).
                </li>
                {!! preg_replace('/<\/?ul[^>]*>/', '', $detalhe->regularidade_fisica) !!}
            </ol>
            <p style="text-align: justify; font-weight: bold;">
                6.6. Qualificação Econômico-financeira:
            </p>
            <ol type="a" style="text-align: justify;">
                <li style="margin-bottom: 6px;">
                    Certidão negativa de pedidos de falência ou concordata, expedida pelo distribuidor da sede da empresa, com data
                    de emissão não superior a 60 (sessenta) dias quando não constar expressamente no corpo da Certidão o seu prazo de
                    validade.
                </li>
                <li style="margin-bottom: 6px;">
                    Balanço patrimonial e demonstrações contábeis dos dois últimos exercícios sociais exigíveis na forma da lei, que
                    comprove a boa e regular situação financeira da empresa, vedada a sua substituição por balancetes ou balanços provisórios,
                    podendo ser atualizados por índices oficiais quando encerrado há mais de 3 (três) meses da data de apresentação da
                    proposta. Admite-se a apresentação de balanço de abertura, para as empresas com menos de 01(um) exercício financeiro.
                </li>
                <li style="margin-bottom: 6px;">
                    As empresas com menos de 02 (dois) anos de exercício financeiro poderão apresentar o balanço do último exercício
                    financeiro.
                </li>
                <li style="margin-bottom: 6px;">
                    Declaração de Capacidade financeira, obrigatoriamente em papel timbrado da empresa, apresentando as
                    demonstrações contábeis do último exercício social, devidamente assinada pelo Representante Legal da Empresa e pelo
                    Contador responsável;
                </li>
                {!! preg_replace('/<\/?ul[^>]*>/', '', $detalhe->qualificacao_economica) !!}
            </ol>
            <p style="text-align: justify; font-weight: bold;">
                6.7 Qualificação Técnica:
            </p>
            <ol type="a" style="text-align: justify;">
                <li style="margin-bottom: 6px;">
                    Apresentar comprovante de que a licitante forneceu, sem restrição, serviços
                    semelhantes ao objeto do presente Edital, através da apresentação de 01 (um) ou mais Atestados
                    de Capacidade Técnica, fornecido por pessoa jurídica de direito público ou privado, devidamente
                    datado e assinado por responsável da área, com nome legível.
                </li>
                <li style="margin-bottom: 6px;">
                    Para fins da comprovação de que trata este subitem, os atestados deverão dizer respeito a contratos executados
                    com as seguintes características mínimas:
                </li>
                {!! preg_replace('/<\/?ul[^>]*>/', '', $detalhe->exigencias_tecnicas) !!}
            </ol>
            <p style="text-align: justify;">
                6.8. O Pregoeiro fará a análise dos documentos de habilitação do licitante, será aberto o prazo para manifestação da intenção
                de interposição de recurso. O não cumprimento do envio dos documentos de habilitação dentro dos prazos estabelecidos,
                acarretará a desclassificação e/ou inabilitação da licitante, bem como as sanções previstas neste Edital, podendo o
                Pregoeiro convocar a empresa que apresentou a proposta ou o lance subsequente.
            </p>
            <p style="text-align: justify;">
                6.9. Os documentos eletrônicos produzidos com a utilização de processo de certificação disponibilizada pela ICP-Brasil, nos
                termos da Medida Provisória nº 2200-2, de 24 de agosto de 2001, serão recebidos e presumir-se-ão verdadeiros em relação
                aos signatários, dispensando-se o envio de documentos originais e cópias autenticadas em papel.
            </p>
            <p style="text-align: justify;">
                6.10. O Pregoeiro reserva-se o direito de solicitar da licitante, em qualquer tempo, no curso da licitação, quaisquer
                esclarecimentos sobre documentos já entregues, fixando-lhe prazo para atendimento.
            </p>
        </div>
        <div>
            <p style="display: flex; align-items: center; font-weight: bold; ">
                <img src="{{ public_path('icons/check.png') }}" width="20" style="margin-right: 10px;"> 7. - DOS RECURSOS
            </p>
            <p style="text-align: justify;">
                7.1. Caberá recurso nos casos previstos no art. 165 da Lei 14.133/21, devendo o licitante manifestar motivadamente sua
                intenção de interpor recurso, através de formulário próprio do Portal de Licitações, explicitando sucintamente suas razões,
                após o término da sessão de lances.
            </p>
            <p style="text-align: justify;">
                7.2. A Intenção motivada de recorrer é aquela que identifica, objetivamente, os fatos e o direito que o licitante pretende que
                sejam revistos pelo Pregoeiro.
            </p>
            <p style="text-align: justify;">
                7.3. A licitante, que manifestar a intenção de recurso e o mesmo ter sido aceito pelo Pregoeiro, disporá do prazo de 03 (três)
                dias para apresentação das razões do recurso, por meio de formulário específico do sistema, que será disponibilizado a
                todos os participantes, ficando os demais desde logo intimados para apresentar as contrarrazões em igual número de dias.
            </p>
            <p style="text-align: justify;">
                7.4. Na hipótese de concessão de prazo para a regularização fiscal de microempresa ou empresa de pequeno porte que
                tenha apresentado melhor proposta, a etapa recursal será aberta por ocasião da retomada da sessão pública do pregão.
            </p>
            <p style="text-align: justify;">
                7.5. A falta de manifestação imediata e motivada do licitante importará a decadência do direito de recurso e adjudicação do
                objeto pelo(a) Pregoeiro(a) ao vencedor.
            </p>
            <p style="text-align: justify;">
                7.6. O recurso contra decisão do(a) Pregoeiro(a) não terá efeito suspensivo.
            </p>
            <p style="text-align: justify;">
                7.7 O acolhimento do recurso importará a invalidação apenas dos atos insuscetíveis de aproveitamento.
            </p>
            <p style="text-align: justify;">
                7.8. Não serão conhecidos os recursos interpostos após os respectivos prazos legais, bem como os encaminhados por fax,
                correios ou entregues pessoalmente.
            </p>
            <p style="text-align: justify;">
                7.9. A intenção de recorrer deverá ser manifestada imediatamente, sob pena de preclusão;
            </p>
            <p style="text-align: justify;">
                7.10. o prazo para apresentação das razões recursais será iniciado na data de intimação ou de lavratura da ata de habilitação
                ou inabilitação;
            </p>
        </div>
        <div>
            <p style="display: flex; align-items: center; font-weight: bold; ">
                <img src="{{ public_path('icons/check.png') }}" width="20" style="margin-right: 10px;"> 8. - DAS INFRAÇÕES ADMINISTRATIVAS E SANÇÕES
            </p>
            <p style="text-align: justify;">
                8.1. Ao fornecedor que, convocado dentro do prazo de validade da sua proposta, não celebrar o contrato, deixar de entregar
                ou apresentar documentação falsa exigida para o certame, não mantiver a proposta, ensejar o retardamento da execução do
                objeto, falhar ou fraudar na execução do contrato, comportar-se de modo inidôneo ou cometer fraude fiscal, poderão ser
                aplicadas as seguintes sanções, garantidos o contraditório e a prévia defesa, de acordo com as seguintes disposições.
            </p>
            <ol>
                <li style="margin-bottom: 6px;">
                    advertência;
                </li>
                <li style="margin-bottom: 6px;">
                    multa, observados os seguintes limites máximos:
                    <ol type="a" style="text-align: justify;">
                        <li style="margin-bottom: 6px;">
                            multa de 0,3 % (três décimos por cento) por dia, até o trigésimo dia de atraso, sobre o valor do fornecimento ou serviço não
                            realizado;
                        </li>
                        <li style="margin-bottom: 6px;">
                            multa de 10 % (dez por cento) sobre o valor total ou parcial da obrigação não cumprida, com o consequente cancelamento
                            da nota de empenho ou documento equivalente;
                        </li>
                    </ol>
                </li>
                <li style="margin-bottom: 6px;">
                    suspensão temporária de participar em licitação e impedimento de contratar com a entidade sancionadora por prazo
                    não superior a 2 (dois) anos.
                </li>
            </ol>
            <p style="text-align: justify;">
                § 1º O valor da multa aplicada será descontado do valor da garantia prestada, retido dos pagamentos devidos pela
                Administração ou cobrado judicialmente, sendo corrigida monetariamente, de conformidade com a variação do IPCA, a partir
                do termo inicial, até a data do efetivo recolhimento.
            </p>
            <p style="text-align: justify;">
                § 2º A contagem do período de atraso na execução dos ajustes será realizada a partir do primeiro dia útil subsequente ao do
                encerramento do prazo estabelecido para o cumprimento da obrigação.
            </p>
        </div>
        <div>
            <p style="display: flex; align-items: center; font-weight: bold; ">
                <img src="{{ public_path('icons/check.png') }}" width="20" style="margin-right: 10px;"> 9. - DA IMPUGNAÇÃO AO EDITAL E DO PEDIDO DE ESCLARECIMENTO
            </p>
            <p style="text-align: justify;">
                9.1. Qualquer pessoa é parte legítima para impugnar este Edital por irregularidade na aplicação da Lei nº 14.133, de 2021,
                devendo protocolar o pedido até 3 (cinco) dias úteis antes da data da abertura do certame.
            </p>
            <p style="text-align: justify;">
                9.2. A resposta à impugnação ou ao pedido de esclarecimento será divulgado em sítio eletrônico oficial no prazo de até 3
                (três) dias úteis, limitado ao último dia útil anterior à data da abertura do certame.
            </p>
            <p style="text-align: justify;">
                9.3. A impugnação e o pedido de esclarecimento poderão ser realizados por forma eletrônica, em campo especifico da
                plataforma.
            </p>
            <p style="text-align: justify;">
                9.4. As impugnações e pedidos de esclarecimentos não suspendem os prazos previstos no certame.
            </p>
            <p style="text-align: justify;">
                9.5. Acolhida a impugnação, será definida e publicada nova data para a realização do certame.
            </p>
        </div>
        <div>
            @if ($detalhe->tipo_srp == 'sim')
            <p style="display: flex; align-items: center; font-weight: bold; ">
                <img src="{{ public_path('icons/check.png') }}" width="20" style="margin-right: 10px;"> 10. - DA ATA DE REGISTRO DE PREÇOS
            </p>
            <p style="text-align: justify;">
                10.1. Por se tratar de mero registro de preços, INEXISTE obrigatoriedade de contratação do objeto desta licitação pelo
                Município, tudo conforme legislação vigente.
            </p>
            <p style="text-align: justify;">
                10.2.O Município poderá ainda “dar carona” do referido certame a quem interessar, obedecendo aos percentuais legais e as
                formalidades de praxe.
            </p>
            <p style="text-align: justify;">
                10.3. Serão formalizadas tantas Atas de Registro de Preços quanto necessárias para o registro de todos os itens constantes
                no Termo de Referência, com a indicação do licitante vencedor, a descrição do(s) item(ns), as respectivas quantidades,
                preços registrados e demais condições.
            </p>
            <p style="text-align: justify;">
                10.4. Poderá utilizar-se da Ata de Registro de Preços os órgãos interessados, ou qualquer outro órgão/entidade da
                Administração Pública que não tenha participado do certame objeto deste Edital, mediante prévia consulta à CONTRATANTE
                desde que devidamente comprovada a vantagem, respeitado o limite contido na legislação.
            </p>
            <p style="text-align: justify;">
                10.5. Os órgãos e entidades que não participaram do Registro de Preços, quando desejarem fazer uso da Ata de Registro de
                Preços, deverão manifestar seu interesse junto à CONTRATANTE para que esta indique os possíveis fornecedores e
                respectivos preços a serem praticados, obedecida a ordem de classificação;
            </p>
            <p style="text-align: justify;">
                10.6. Será incluído na ata, sob a forma de anexo, o registro dos licitantes que aceitarem cotar os bens ou serviços com preços
                iguais aos do licitante vencedor na sequência da classificação do certame, excluído o percentual referente à margem de
                preferência, quando o objeto não atender aos requisitos previstos no inciso VII, art. 82 da Lei 14.133/2021
            </p>
            <p style="text-align: justify;">
                10.7. Caberá ao fornecedor beneficiário da Ata de Registro de Preços, observadas as condições nela estabelecidas, optar
                pela aceitação ou não do fornecimento decorrente de adesão. Os Não Participantes da licitação poderão aderir a ATA, desde
                que devidamente autorizados pelo Chefe do Executivo Municipal.
            </p>
            <p style="text-align: justify;">
                10.4 Caberá aos fornecedores beneficiários da Ata de Registro de Preços, observadas as condições nela estabelecidas, optar
                pela aceitação ou não do fornecimento aos órgãos não participantes que solicitem adesão à Ata de Registro de Preços acima
                do quantitativo previsto, desde que este fornecimento não prejudique as obrigações anteriormente assumidas;
            </p>
            <p style="text-align: justify;">
                10.5 As solicitações de adesão, concessão de anuência pelo fornecedor e autorização do órgão gerenciador serão realizadas
                por meio de formalização de processo administrativo com as documentações necessárias, cuja responsabilidade é do órgão
                gerenciador.
            </p>
            <p style="text-align: justify;">
                10.6. O quantitativo decorrente das adesões à Ata de Registro de Preços não poderá exceder, na totalidade, ao dobro do
                quantitativo de cada item registrado na Ata de Registro de Preços para o órgão gerenciador e órgão participantes,
                independentemente do número de órgãos não participantes que aderirem.
            </p>
            <p style="text-align: justify;">
                10.7. Após a aceitação à adesão da Ata de Registro de Preços pelo órgão gerenciador, o Órgão denominado Carona deverá
                observar as seguintes instruções:
            </p>
            <ol type="a" style="text-align: justify;">
                <li style="margin-bottom: 6px;">
                    O Órgão Carona somente poderá adquirir os itens registrados nas mesmas condições comerciais e financeiras
                    estabelecidas no Pregão, dentro da vigência da Ata, não podendo ultrapassar 50% do registrado na mesma;
                </li>
                <li style="margin-bottom: 6px;">
                    Qualquer ato que o Órgão Carona, cometer de abuso às condições comerciais e financeiras expressas nesse
                    Processo Licitatório – Registro de Preços, responderá exclusivamente por si e assumirá inteira responsabilidade,
                    não envolvendo assim, o Órgão gerenciador do registro;
                </li>
                <li>
                    O Órgão Carona fará o contato com o vencedor do certame, conforme Termo de Adjudicação;
                </li>
            </ol>
            <p style="text-align: justify;">
                10.8. A previsão de aquisição ou contratação pelo Órgão Carona deverá ser de até
                90 (noventa) dias após a autorização, observando o prazo de vigência da ata.
            </p>
            @endif
            <p style="font-weight: bold;">
                DAS DISPOSIÇÕES GERAIS
            </p>
            <p style="text-align: justify;">
                A presente licitação não importa necessariamente em contratação, podendo a
                Administração, revogá-la, no todo ou em parte, por razões de interesse público,
                derivado de fato superveniente comprovado ou anulá-la por ilegalidade, de ofício
                ou por provocação mediante ato escrito e fundamentado disponibilizado no
                sistema para conhecimento dos participantes
            </p>
            <p style="text-align: justify;">
                É facultado ao Pregoeiro ou à Autoridade Superior, em qualquer fase da licitação, promover diligências com vistas a
                esclarecer ou a complementar a instrução do processo.
            </p>
            <p style="text-align: justify;">
                Havendo qualquer fato superveniente que impeça a realização do certame na data marcada, a sessão será transferida para
                dia e horário definidos pelo pregoeiro, comunicando devidamente aos licitantes do pregão eletrônico.
            </p>
            <p style="text-align: justify;">
                Integram este Edital, para todos os fins e efeitos, os seguintes anexos:
                <br>
                ANEXO I – Termo de Referência
                <br>
                ANEXO II – Minuta do Contrato
                <br>
                @if ($detalhe->tipo_srp == 'sim')
                ANEXO III – Ata de Registro de Preços
                @endif
            </p>
        </div>

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
    </div>
</body>

</html>
