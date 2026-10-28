    <div>
        <div>
            <table style="border-collapse: collapse; width: 100%; border: 1px solid black; margin-top: 20px;">
                <thead>
                    <tr>
                        <td colspan="2" style="border: 1px solid black; text-align: center; font-weight: bold; padding: 5px; background-color:#c8d3da;">
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
                            MENOR PREÇO
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid black; padding: 5px; font-weight: bold;">
                            FORMA DE ADJUDICAÇÃO
                        </td>
                        <td style="border: 1px solid black; padding: 5px;">
                            GLOBAL
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
                        <td colspan="2" style="border: 1px solid black; text-align: center; font-weight: bold; padding: 5px; background-color:#c8d3da;">
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
            <p style="text-align: center; font-weight: bold;">
                CONCORRÊNCIA Nº {{ $processo->numero_procedimento }} <br>
                PROCESSO ADMINISTRATIVO Nº {{ $processo->numero_processo }}
            </p>
            <p style="text-align: justify;">
                O MUNICÍPIO DE {{ $processo->prefeitura->cidade }}, TORNA PÚBLICO, PARA CONHECIMENTO
                DOS INTERESSADOS, QUE REALIZARÁ LICITAÇÃO NA MODALIDADE
                CONCORRÊNCIA, NA FORMA ELETRÔNICA, NOS TERMOS DA LEI Nº 14.133/2021,
                DA LEI COMPLEMENTAR Nº 123/2006, DECRETOS MUNICIPAIS, E DAS
                EXIGÊNCIAS ESTABELECIDAS NESTE EDITAL.
            </p>
            <p style="font-weight: bold;">1. DO OBJETO.</p>
            <p style="text-align: justify;">
                1.1. O objeto da presente licitação é a escolha da proposta mais vantajosa para
                {!! strip_tags($processo->objeto) !!} conforme condições, quantidades e exigências
                estabelecidas neste Edital e Projeto Básico.
            </p>
            <p style="text-align: justify;">
                1.2. O critério de julgamento adotado será o menor preço GLOBAL.
            </p>

            @if ($detalhe->inversao_fase == 'sim')
                <p style="text-align: justify;">
                    1.3. Na presente licitação, a fase de habilitação ANTECEDERÁ a fase de
                    apresentação de propostas e lances e de julgamento, conforme artigo 17, inciso V,
                    § 1° da Lei nº 14.133/2021.
                </p>
                <p style="text-align: justify;">
                    1.4. Nos termos da Lei nº 14.133/2021, na qual se realiza primeiramente o
                    julgamento das propostas para, somente após, proceder à análise da habilitação
                    da licitante mais bem classificada, constitui a regra geral para os processos
                    licitatórios (art. 17). No entanto, a própria legislação prevê a possibilidade de a fase
                    de habilitação anteceder a fase de apresentação de propostas e lances e de
                    julgamento, mediante justificativa técnica devidamente fundamentada e previsão
                    expressa no edital, conforme dispõe o art. 17, § 1º.
                </p>
                <p style="text-align: justify;">
                    1.4.1. Entre os principais fundamentos para essa escolha, destacam-se: <br>
                    <ul>
                        <li>
                            Maior segurança jurídica e técnica na seleção das propostas: Ao verificar
                            previamente a capacidade técnica e documental dos licitantes, a
                            Administração garante que apenas empresas efetivamente aptas disputem o
                            fornecimento do objeto, reduzindo riscos de desclassificações posteriores
                            que comprometeriam a efetividade do certame.
                        </li>
                        <li>
                            Histórico de processos com problemas na fase de habilitação: Em certames
                            anteriores, observou-se a recorrência de propostas vantajosas apresentadas
                            por empresas que, ao final, foram inabilitadas por não atenderem aos
                            requisitos técnicos ou legais. Esse cenário resultou em atrasos processuais,
                            necessidade de reavaliação de lances, e até mesmo anulação de etapas, o
                            que comprometeu a eficiência da contratação.
                        </li>
                        <li>
                            Prevenção à atuação de licitantes de fachada: Conforme alerta o jurista
                            Marçal Justen Filho, há risco da participação de empresas sem capacidade
                            real de execução, que se utilizam do certame para criar embaraços ou
                            participar de maneira simulada. A análise prévia da habilitação funciona
                            como um filtro eficaz contra tais práticas.
                        </li>
                    </ul>
                </p>
                <p style="text-align: justify;">
                    1.4.2. Ressalta-se que a presente justificativa atende aos requisitos legais exigidos
                    pela nova Lei de Licitações, garantindo a transparência, a isonomia entre os
                    licitantes e a adequação procedimental, sem prejuízo à competitividade do
                    certame.
                </p>
                <p style="text-align: justify;">
                    1.4.3. Dessa forma, a Administração Pública opta por adotar a inversão de fases
                    neste processo, seguindo, assim, o rito com análise prévia da habilitação, nos
                    termos da Lei nº 14.133/2021, com previsão expressa no edital.
                </p>
                <p style="text-align: justify;">
                    1.5. O procedimento observará a seguinte ordem:
                    <div style="margin-left: 20px">
                        I Recebimento das propostas e dos documentos de habilitação;
                        <br>
                        II Análise e julgamento da habilitação de todos os licitantes;
                        <br>
                        III Fase de lances;
                        <br>
                        IV Recebimento e julgamento das propostas apenas dos licitantes
                        habilitados;
                        <br>
                        V Fase de Recurso;
                        <br>
                        VI Classificação final e adjudicação.
                    </div>
                </p>

            @else
            <p style="text-align: justify;">
                1.3. O procedimento observará a seguinte ordem:
                <div style="margin-left: 20px">
                    I Recebimento e julgamento das propostas;
                    <br>
                    II Fase de lances;
                    <br>
                    III Recebimento dos documentos de habilitação e julgamento das propostas
                    apenas dos licitantes habilitados;
                    <br>
                    IV  Fase de Recurso;
                    <br>
                    V Classificação final e adjudicação.
                </div>
                </p>
            @endif

            <p style="font-weight: bold;">2. DOS RECURSOS ORÇAMENTÁRIOS</p>
            <p style="text-align: justify;">
                2.1.As despesas para atender a esta licitação estão programadas em dotação
                orçamentária própria, prevista no orçamento da União para o exercício de 2025, na
                classificação abaixo:
            </p>
            {!! $detalhe->dotacao_orcamentaria !!}

            <p style="font-weight: bold;">3. DO CREDENCIAMENTO</p>
            <p style="text-align: justify;">
                3.1. O Credenciamento será realizado no Compras BR que permite a participação
                dos interessados na modalidade LICITATÓRIA CONCORRÊNCIA, em sua FORMA
                ELETRÔNICA.
            </p>
            <p style="text-align: justify;">
                3.2. O cadastro deverá ser feito no Portal Compras BR, no sítio
                www.comprasbr.com.br;
            </p>
            <p style="text-align: justify;">
                3.3. O credenciamento junto ao provedor do sistema implica a responsabilidade do
                licitante ou de seu representante legal e a presunção de sua capacidade técnica
                para realização das transações inerentes a esta licitação.
            </p>
            <p style="text-align: justify;">
                3.4. O licitante responsabiliza-se exclusiva e formalmente pelas transações
                efetuadas em seu nome, assume como firmes e verdadeiras suas propostas e seus
                lances, inclusive os atos praticados diretamente ou por seu representante,
                excluída a responsabilidade do provedor do sistema ou do órgão ou entidade
                promotora da licitação por eventuais danos decorrentes de uso indevido das
                credenciais de acesso, ainda que por terceiros.
            </p>
            <p style="text-align: justify;">
                3.5. É de responsabilidade do cadastrado conferir a exatidão dos seus dados
                cadastrais e mantê-los atualizados junto aos órgãos responsáveis pela informação,
                devendo proceder, imediatamente, à correção ou à alteração dos registros tão logo
                identifique incorreção ou aqueles se tornem desatualizados.
            </p>
            <p style="text-align: justify;">
                3.5.1. A não observância do disposto no subitem anterior poderá ensejar a inabilitação do licitante.
            </p>

            <p style="font-weight: bold;">4. DA PARTICIPAÇÃO NA CONCORRÊNCIA.</p>
            <p style="text-align: justify;">
                4.1. Poderão participar desta Concorrência interessados cujo ramo de atividade
                seja compatível com o objeto desta licitação, e que estejam com Credenciamento
                regular no Compras BR.
            </p>
            <p style="text-align: justify;">
                4.2. Será concedido tratamento favorecido para as microempresas e empresas de
                pequeno porte, para as sociedades cooperativas mencionadas no artigo 34 da Lei
                nº 11.488/2007, para o microempreendedor individual - MEI, nos limites previstos
                da Lei Complementar nº 123/2006 e no artigo 4º da Lei nº 14.133/2021.
            </p>
            <p style="text-align: justify;">
                4.3. Não poderão participar desta licitação os interessados:
            </p>
            <p style="text-align: justify;">
                4.3.1. Proibidos de participar de licitações e celebrar contratos administrativos, na
                forma da legislação vigente;
            </p>
            <p style="text-align: justify;">
                4.3.2. Que não atendam às condições deste Edital e seu(s) anexo(s);
            </p>
            <p style="text-align: justify;">
                4.3.3. Estrangeiros que não tenham representação legal no Brasil com poderes
                expressos para receber citação e responder administrativa ou judicialmente;
            </p>
            <p style="text-align: justify;">
                4.3.4. Que se enquadrem nas vedações previstas nos artigos 9º e 14 da Lei nº
                14.133/2021;
            </p>
            <p style="text-align: justify;">
                4.3.5. Que estejam sob falência, concurso de credores ou em processo de dissolução ou liquidação, observado o item 8.6.2 quanto às empresas em recuperação judicial ou extrajudicial;
            </p>
            <p style="text-align: justify;">
                4.3.6. Organizações da Sociedade Civil de Interesse Público - OSCIP, atuando
                nessa condição (Acórdão nº 746/2014-TCU-Plenário).
            </p>
            <p style="text-align: justify;">
                4.4. A pessoa jurídica poderá participar da licitação em consórcio, observadas as
                regras do art. 15 da Lei nº 14.133/2021.
            </p>
            <p style="text-align: justify;">
                4.5. Como condição para participação na concorrência, a licitante assinalará “sim”
                ou “não” em campo próprio do sistema eletrônico, relativo às seguintes
                declarações:
            </p>
            <p style="text-align: justify;">
                4.5.1. Que cumpre os requisitos estabelecidos no artigo 3° da Lei Complementar
                nº 123/2006, estando apta a usufruir do tratamento favorecido estabelecido em
                seus arts. 42 a 49 e que não celebrou contratos com a Administração Pública cujos
                valores extrapolem a receita bruta máxima admitida para fins de enquadramento
                como empresa de pequeno porte;
            </p>
            <p style="text-align: justify;">
                4.5.1.1. Nos itens exclusivos para participação de microempresas e empresas de
                pequeno porte, a assinalação do campo “não” impedirá o prosseguimento no
                certame;
            </p>
            <p style="text-align: justify;">
                4.5.1.2. Nos itens em que a participação não for exclusiva para microempresas e
                empresas de pequeno porte, a assinalação do campo “não” apenas produzirá o
                efeito de o licitante não ter direito ao tratamento favorecido previsto na Lei
                Complementar nº 123/2006, mesmo que microempresa, empresa de pequeno
                porte.
            </p>
            <p style="text-align: justify;">
                4.5.2. Que está ciente e concorda com as condições contidas no Edital e seus
                anexos;
            </p>
            <p style="text-align: justify;">
                4.5.3. Que cumpre os requisitos para a habilitação definidos no Edital e que a
                proposta apresentada está em conformidade com as exigências editalícias;
            </p>
            <p style="text-align: justify;">
                4.5.4. Que inexistem fatos impeditivos para sua habilitação no certame, ciente da
                obrigatoriedade de declarar ocorrências posteriores;
            </p>
            <p style="text-align: justify;">
                4.5.5. Que cumpre com a reserva de cargos prevista em lei para pessoa com
                deficiência ou para reabilitado da Previdência Social e que atendam às regras de
                acessibilidade previstas na legislação, conforme disposto no art. 93 da Lei nº
                8.213/1991.
            </p>
            <p style="text-align: justify;">
                4.6. A declaração falsa relativa ao cumprimento de qualquer condição sujeitará o
                licitante às sanções previstas em lei e neste Edital.
            </p>
        </div>
        <div>
            <p style="font-weight: bold;">5. DO ENVIO DA PROPOSTA E DOS VALORES INICIAIS .</p>
            <p style="text-align: justify;">
                5.1. Os licitantes encaminharão, exclusivamente por meio do sistema eletrônico,
                proposta com a descrição do objeto ofertado e o preço, até a data e o horário
                estabelecidos para abertura da sessão pública, quando, então, encerrar-se-á
                automaticamente a etapa de envio dessa documentação.
            </p>
            <p style="text-align: justify;">
                5.2. O envio da proposta ocorrerá por meio de chave de acesso e senha.
            </p>
            <p style="text-align: justify;">
                5.3. As Microempresas e Empresas de Pequeno Porte deverão encaminhar a documentação de habilitação, ainda que haja alguma restrição de regularidade fiscal e trabalhista, nos termos do art. 43, caput da LC nº 123/2006.
            </p>
            <p style="text-align: justify;">
                5.4. Incumbirá ao licitante acompanhar as operações no sistema eletrônico
                durante a sessão pública da Concorrência, ficando responsável pelo ônus
                decorrente da perda de negócios, diante da inobservância de quaisquer
                mensagens emitidas pelo sistema ou de sua desconexão.
            </p>
            <p style="text-align: justify;">
                5.5. Até a abertura da sessão pública, os licitantes poderão retirar ou substituir a
                proposta e os documentos de habilitação anteriormente inseridos no sistema;
            </p>
            <p style="text-align: justify;">
                5.6. Não será estabelecida, nessa etapa do certame, ordem de classificação entre
                as propostas apresentadas, o que somente ocorrerá após a realização dos
                procedimentos de negociação e julgamento da proposta.
            </p>
            <p style="text-align: justify;">
                5.7. O licitante enviará sua proposta mediante o preenchimento, no sistema
                eletrônico, dos seguintes campos:
            </p>
            <p style="text-align: justify;">
                5.7.1. Valor unitário e total para cada item ou lote de itens, em moeda corrente
                nacional;
            </p>
            <p style="text-align: justify;">
                5.7.2. Descrição do objeto, conforme as especificações do Projeto Básico, acompanhada dos documentos da proposta inicial relacionados no item 7.4.
            </p>
            <p style="text-align: justify;">
                5.8. Nos valores propostos estarão inclusos todos os custos operacionais, encargos previdenciários, trabalhistas, tributários, comerciais e quaisquer outros que incidam direta ou indiretamente na execução da obra, demonstrados na planilha orçamentária, nas composições de preços unitários e nas composições do BDI e dos encargos sociais exigidas no item 7.4.
            </p>
            <p style="text-align: justify;">
                5.9. O prazo de validade da proposta não será inferior a 90 DIAS, a contar da data
                de sua apresentação.
            </p>
            <p style="text-align: justify;">
                5.10. Por força da legislação vigente, será desclassificada Proposta Inicial que
                possua timbre, carimbo, informações do licitante em anexos que possam
                acompanhar a Proposta Inicial ou qualquer elemento que possa identificar o
                licitante, sem prejuízo das sanções previstas no Edital.
            </p>

            <p style="font-weight: bold">6. DA ABERTURA DA SESSÃO, CLASSIFICAÇÃO DAS PROPOSTAS E FORMULAÇÃO DE LANCES. </p>
            <p style="text-align: justify;">
                6.1. A abertura da presente licitação dar-se-á em sessão pública, por meio de
                sistema eletrônico, na data, horário e local indicados neste Edital.
            </p>
            <p style="text-align: justify;">
                6.2. A Agente de Contratação verificará as propostas apresentadas,
                desclassificando, desde logo, aquelas que não estejam em conformidade com os
                requisitos estabelecidos neste Edital, contenham vícios insanáveis ou não
                apresentem as especificações técnicas exigidas conforme art. 59 da Lei nº
                14.133/2021.
            </p>
            <p style="text-align: justify;">
                6.2.1. Também será desclassificada a proposta que identifique o licitante.
            </p>
            <p style="text-align: justify;">
                6.2.2. A desclassificação será sempre fundamentada e registrada no sistema, com
                acompanhamento em tempo real por todos os participantes.
            </p>
            <p style="text-align: justify;">
                6.2.3. A não desclassificação da proposta não impede o seu julgamento definitivo
                em sentido contrário, levado a efeito na fase de aceitação.
            </p>
            <p style="text-align: justify;">
                6.3. O sistema ordenará automaticamente as propostas classificadas, sendo que
                somente estas participarão da fase de lances.
            </p>
            <p style="text-align: justify;">
                6.4. O sistema disponibilizará campo próprio para troca de mensagens entre o
                Agente de Contratação e os licitantes.
            </p>
            <p style="text-align: justify;">
                6.5. Iniciada a etapa competitiva, os licitantes deverão encaminhar lances
                exclusivamente por meio do sistema eletrônico, sendo imediatamente informados
                do seu recebimento e do valor consignado no registro.
            </p>
            <p style="text-align: justify;">
                6.5.1. O lance deverá ser ofertado de acordo com o tipo de licitação indicada no
                preambulo deste Edital.
            </p>
            <p style="text-align: justify;">
                6.6. Os licitantes poderão oferecer lances sucessivos, observando o horário fixado
                para abertura da sessão e as regras estabelecidas no Edital.
            </p>
            <p style="text-align: justify;">
                6.7. O licitante somente poderá oferecer lance de valor inferior ou percentual de
                desconto superior ao último por ele ofertado e registrado pelo sistema.
            </p>
            <p style="text-align: justify;">
                6.8. O intervalo mínimo de diferença de valores ou percentuais entre os lances, que
                incidirá tanto em relação aos lances intermediários quanto em relação à proposta
                que cobrir a melhor oferta deverá ser de R$ 100,00 (cem reais).
            </p>
            <p style="text-align: justify;">
                6.9. Será adotado para o envio de lances na licitação o modo de disputa aberto,
                em que os licitantes apresentarão lances públicos e sucessivos, com
                prorrogações.
            </p>
            <p style="text-align: justify;">
                6.10. A etapa de lances da sessão pública terá duração de dez minutos e, após
                isso, será prorrogada automaticamente pelo sistema quando houver lance
                ofertado nos últimos dois minutos do período de duração da sessão pública.
            </p>
            <p style="text-align: justify;">
                6.11. A prorrogação automática da etapa de lances, de que trata o item anterior,
                será de dois minutos e ocorrerá sucessivamente sempre que houver lances
                enviados nesse período de prorrogação, inclusive no caso de lances
                intermediários.
            </p>
            <p style="text-align: justify;">
                6.12. Não havendo novos lances na forma estabelecida nos itens anteriores, a
                sessão pública encerrar-se-á automaticamente.
            </p>
            <p style="text-align: justify;">
                6.13. Encerrada a fase competitiva sem que haja a prorrogação automática pelo
                sistema, poderá o Agente de Contratação, assessorado pela equipe de apoio,
                justificadamente, admitir o reinício da sessão pública de lances, em prol da
                consecução do melhor preço.
            </p>
            <p style="text-align: justify;">
                6.14. Em caso de falha no sistema, os lances em desacordo com os subitens
                anteriores deverão ser desconsiderados pelo Agente de Contratação.
            </p>
            <p style="text-align: justify;">
                6.15. Não serão aceitos dois ou mais lances de mesmo valor, prevalecendo
                aquele que for recebido e registrado primeiro.
            </p>
            <p style="text-align: justify;">
                6.16. Durante o transcurso da sessão pública, os licitantes serão informados, em
                tempo real, do valor do menor lance registrado, vedada a identificação do licitante.
            </p>
            <p style="text-align: justify;">
                6.17. No caso de desconexão com o Agente de Contratação, no decorrer da etapa
                competitiva da Concorrência, o sistema eletrônico poderá permanecer acessível
                aos licitantes para a recepção dos lances.
            </p>
            <p style="text-align: justify;">
                6.18. Quando a desconexão do sistema eletrônico para o Agente de Contratação
                persistir por tempo superior a dez minutos, a sessão pública será suspensa e terá
                reinício somente após comunicação expressa do Agente de Contratação aos
                participantes do certame, publicada no http://www.comprasbr.com.br, quando
                serão divulgadas data e hora para a sua reabertura. E será reiniciada somente após
                decorridas vinte e quatro horas da comunicação do fato pelo Agente de
                Contratação aos participantes, no sítio eletrônico utilizado para divulgação.
            </p>
            <p style="text-align: justify;">
                6.19. Caso o licitante não apresente lances, concorrerá com o valor de sua
                proposta.
            </p>
            <p style="text-align: justify;">
                6.20. Em relação a itens não exclusivos para participação de microempresas e
                empresas de pequeno porte, uma vez encerrada a etapa de lances, será efetivada
                a verificação automática, junto à Receita Federal, do porte da entidade
                empresarial. O sistema identificará em coluna própria as microempresas e
                empresas de pequeno porte participantes, procedendo à comparação com os
                valores da primeira colocada, se esta for empresa de maior porte, assim como das
                demais classificadas, para o fim de aplicar-se o disposto nos arts. 44 e 45 da LC nº
                123/2006, regulamentada pelo Decreto nº 8.538/2015.
            </p>
            <p style="text-align: justify;">
                6.21. Encerrada a etapa de lances, será aplicado o tratamento diferenciado às microempresas e empresas de pequeno porte, na forma do item 9.
            </p>
            <p style="text-align: justify;">
                6.22. A ordem de apresentação pelos licitantes é utilizada como um dos critérios
                de classificação, de maneira que só poderá haver empate entre propostas iguais
                (não seguidas de lances), ou entre lances finais da fase fechada do modo de
                disputa aberto e fechado.
            </p>
            <p style="text-align: justify;">
                6.23. Em caso de empate entre duas ou mais propostas, serão utilizados os
                seguintes critérios de desempate, nesta ordem:
            </p>
            <p style="text-align: justify;">
                6.23.1. disputa final, hipótese em que os licitantes empatados poderão
                apresentar nova proposta em ato contínuo à classificação;
            </p>
            <p style="text-align: justify;">
                6.23.2. avaliação do desempenho contratual prévio dos licitantes;
            </p>
            <p style="text-align: justify;">
                6.23.3. desenvolvimento pelo licitante de ações de equidade entre homens e
                mulheres no ambiente de trabalho, conforme regulamento;
            </p>
            <p style="text-align: justify;">
                6.23.4. desenvolvimento pelo licitante de programa de integridade, conforme
                orientações dos órgãos de controle;
            </p>
            <p style="text-align: justify;">
                6.24. Persistindo o empate, será assegurada preferência, sucessivamente, aos
                bens e serviços produzidos ou prestados por:
            </p>
            <p style="text-align: justify;">
                6.24.1. empresas estabelecidas no território do Estado ou do Distrito Federal do
                órgão ou entidade da Administração Pública estadual ou distrital licitante ou, no
                caso de licitação realizada por órgão ou entidade de Município, no território do
                Estado em que este se localize;
            </p>
            <p style="text-align: justify;">
                6.24.2. empresas brasileiras;
            </p>
            <p style="text-align: justify;">
                6.24.3. empresas que invistam em pesquisa e no desenvolvimento de tecnologia
                no País;
            </p>
            <p style="text-align: justify;">
                6.24.4. empresas que comprovem a prática de mitigação, nos termos da Lei nº
                12.187/2009.
            </p>
            <p style="text-align: justify;">
                6.25. Encerrada a etapa de envio de lances da sessão pública, o Agente de
                Contratação deverá encaminhar, pelo sistema eletrônico, contraproposta ao
                licitante que tenha apresentado o melhor preço, para que seja obtida melhor
                proposta, vedada a negociação em condições diferentes das previstas neste Edital.
            </p>
            <p style="text-align: justify;">
                6.25.1. A negociação será realizada por meio do sistema, podendo ser
                acompanhada pelos demais licitantes.
            </p>
            <p style="text-align: justify;">
                6.26. Após a negociação do preço, o Agente de Contratação iniciará a fase de
                Abertura de Vistas.
            </p>
            <p style="font-weight: bold;">7. DA FASE DE ABERTURA DE VISTAS.</p>
            <p style="text-align: justify;">
                7.1. Após finalizada a fase de lances, o Agente de Contratação analisará a proposta
                na respectiva fase de Abertura de Vistas, que uma vez atendida as condições de
                julgamento, divulgará o vencedor provisório do certame.
            </p>
            <p style="text-align: justify;">
                7.2. Com base no Art. 34 da Lei 14.133/2021, como condição de parâmetro
                mínimo de exigência para esta licitação, deverá ser anexada, a proposta comercial
                inicial na condição de “catálogo”, sem identificação, contendo os documentos relacionados no item 7.4, para fins de julgamento (art. 59 da Lei nº 14.133/2021).
            </p>
            <p style="text-align: justify;">
                7.3. A não apresentação da exigência acima, acarretará a desclassificação da
                proposta inicial apresentada, assim como os valores de lances efetivados na fase
                de lances iniciais, o que remeterá ao Agente de Contratação, a necessidade de
                chamar os licitantes remanescentes, na respectiva ordem de classificação na fase
                anterior.
            </p>
            <p style="text-align: justify;">
                7.4. Para efeito de classificação, a proposta inicial deverá obedecer aos seguintes
                regramentos:
            </p>
            <p style="text-align: justify;">
                a)
                O Termo de Proposta, deverá conter o valor global, incluindo BDI, encargos
                sociais, taxas, impostos e emolumentos para a execução das obras objeto desta
                licitação, e deverá constituir-se no primeiro documento da Proposta Financeira;
                <br>
                b)
                Cronograma Físico-Financeiro dos itens principais da planilha orçamentária
                constantes da descrição geral das obras, obedecendo as atividades e prazos, com
                os percentuais previstos mês a mês, observado o prazo de execução estabelecido
                neste Edital;
                <br>
                c)
                Planilha de serviços e quantidades, de preços unitários e totais em real (R$),
                na data da apresentação da PROPOSTA, com totais parciais e globais, com
                rigorosas especificações e quantitativos, incluindo suas respectivas composições
                dos preços unitários. E, ainda, observando que não poderão ser alterados os
                quantitativos previstos, como também, que os preços unitários propostos não
                poderão ser superiores aos preços unitários básicos integrante do Projeto Básico;
                <br>
                d) A Proposta de Preços deverá contemplar todos os itens de serviços e
                fornecimentos descritos na Planilha de Preços Básicos, inclusive o BDI, sob pena
                de desclassificação da proposta.
                <br>
                e) Composição analítica do BDI, com a discriminação de todas as parcelas, inclusive tributos e lucro, aplicada sobre os preços unitários propostos. As alíquotas dos tributos deverão corresponder ao regime de tributação a que o licitante está submetido, e a margem de lucro é de livre fixação pelo licitante, observada a exequibilidade da proposta.
                <br>
                f) Composição dos encargos Sociais, conforme tipo de desoneração
                especificada no Projeto Básico desta Licitação.
                <br>
                g) O prazo de validade das propostas será de 90 (noventa) dias contado a partir da
                data estabelecida para a entrega das mesmas, sujeita à revalidação por idêntico
                período.
                <br>
                h) É vedada a apresentação de preços unitários distintos para um mesmo serviço. As composições de preços unitários poderão adotar coeficientes de produtividade e de consumo próprios, desde que compatíveis com a execução do serviço e com a exequibilidade da proposta.
                <br>
                i) A diferença percentual entre o valor final proposto e o valor estimado pela Administração será aplicada de forma uniforme sobre os preços unitários de todos os itens da planilha orçamentária, para fins de elaboração da proposta readequada, devendo as composições de preços unitários e do BDI ser ajustadas a esses preços. Nos aditivos, os preços de serviços não previstos serão fixados pela aplicação dessa mesma relação sobre os preços referenciais vigentes na data do aditamento (art. 127), e a diferença percentual entre o valor global do contrato e o preço global de referência não poderá ser reduzida em favor do contratado (art. 128).
            </p>
            <p style="text-align: justify;">
                7.5. A proposta inicial que não apresentar as especificações e exigências
                anteriormente informadas, será automaticamente desclassificada, sendo
                convocado o vencedor subsequente da fase de lances.
            </p>
            <p style="text-align: justify;">
                7.6. Na abertura de vistas, o agente de contratação irá analisar as condições
                de exigência pertinentes ao objeto e as exigências nele ressaltadas.
            </p>
            <p style="text-align: justify;">
                7.7. Também será analisada na fase de Abertura de Vistas, a respectiva
                exequibilidade do valor ofertado na fase de lances, o qual deverá obedecer aos
                critérios de aceitabilidade e classificação previstos no Edital.
            </p>
            <p style="text-align: justify;">
                7.8. Preço unitário inferior aos limites dos itens 7.12.1 e 7.12.2 não acarretará, por si só, a desclassificação da proposta, devendo o licitante demonstrar sua exequibilidade na forma daqueles itens. Não demonstrada a exequibilidade, a proposta será desclassificada (art. 59, §3º).
            </p>
            <p style="text-align: justify;">
                7.9. Será desclassificada a proposta que contiver vício insanável; que não
                obedecer às especificações técnicas pormenorizadas no edital ou apresentarem
                desconformidade com exigências do ato convocatório.
            </p>
            <p style="text-align: justify;">
                7.10. Será desclassificada a proposta ou o lance vencedor, que apresentar preço
                final superior ao preço máximo fixado (Acórdão nº 1455/2018 -TCU - Plenário), ou
                que apresentar preço manifestamente inexequível.
            </p>
            <p style="text-align: justify;">
                7.10.1. Considera-se inexequível a proposta de preços ou menor lance que for
                insuficiente para a cobertura dos custos da contratação, apresente preços global
                ou unitários simbólicos, irrisórios ou de valor zero, incompatíveis com os preços
                dos insumos e salários de mercado, acrescidos dos respectivos encargos, ainda
                que o ato convocatório da licitação não tenha estabelecido limites mínimos, exceto
                quando se referirem a materiais e instalações de propriedade do próprio licitante,
                para os quais ele renuncie a parcela ou à totalidade da remuneração.
            </p>
            <p style="text-align: justify;">
                7.11. Qualquer interessado poderá requerer que se realizem diligências para
                aferir a exequibilidade e a legalidade das propostas, devendo apresentar as provas
                ou os indícios que fundamentam a suspeita;
            </p>
            <p style="text-align: justify;">
                7.12. A proposta cujo valor global seja inferior a 75% (setenta e cinco por cento) do valor orçado pela Administração terá presumida a sua inexequibilidade (art. 59, §4º), e o Agente de Contratação realizará diligência para que o licitante demonstre a exequibilidade, sob pena de desclassificação.
            </p>
            <p style="text-align: justify;">
                7.12.1. Caso a proposta apresentada contenha preço(s) unitário(s) com
                valor(es) inferior(es) a 75% do orçado no Projeto Básico, será obrigatória a
                apresentação de justificativa e COMPROVAÇÃO de exequibilidade para cada um
                do(s) itens e/ou serviço(s) em questão, devidamente acompanhada dos
                documentos que lhe dão suporte.
            </p>
            <p style="text-align: justify;">
                7.12.2. Caso a proposta apresentada contenha preço(s) unitário(s) dos itens
                relevantes designados no projeto básico (Curva A) com valor(es) inferior(es) a 85%
                do orçado no Projeto Básico, será obrigatória a apresentação de justificativa e
                COMPROVAÇÃO de exequibilidade para cada um do(s) serviço(s) em questão,
                devidamente acompanhada dos documentos que lhe dão suporte.
            </p>
            <p style="text-align: justify;">
                7.13. O Agente de Contratação poderá convocar o licitante para enviar
                documento digital complementar, por meio de funcionalidade disponível no
                sistema, no prazo de 02 (duas) horas, sob pena de não aceitação da proposta.
            </p>
            <p style="text-align: justify;">
                7.13.1. O prazo estabelecido poderá ser prorrogado pelo Agente de Contratação
                por solicitação escrita e justificada do licitante, formulada antes de findo o prazo, e
                formalmente aceita pelo Agente de Contratação.
            </p>
            <p style="text-align: justify;">
                7.14. Se a proposta ou lance vencedor for desclassificado, o Agente de
                Contratação examinará a proposta ou lance subsequente, e, assim
                sucessivamente, na ordem de classificação.
            </p>
            <p style="text-align: justify;">
                7.15. Havendo necessidade, o Agente de Contratação suspenderá a sessão,
                informando no “chat” a nova data e horário para a sua continuidade.
            </p>
            <p style="text-align: justify;">
                7.16. O Agente de Contratação solicitará ao licitante mais bem classificado
                que, no prazo de 02 (duas) horas, envie a proposta readequada ao último lance
                ofertado após a negociação realizada, acompanhada da garantia de proposta de
                1%, como condição de pré-habilitação nos termos do art. 58, da lei 14.133, ficando
                vedada o envio de documentação via e-mail.
            </p>
            @if($processo->detalhe->inversao_fase === "não")
                <p style="text-align: justify;">
                    7.17. Encerrada a análise quanto à aceitação da proposta, o Agente de
                    Contratação verificará a habilitação do licitante, observado o disposto neste Edital.
                </p>
            @endif
        </div>
        <div>
            <p style="font-weight: bold;">8. DA HABILITAÇÃO.</p>
            @if (($processo->detalhe->inversao_fase ?? $detalhe->inversao_fase) === 'sim')
                <p style="text-align: justify;">
                    8.1. Como condição prévia ao exame da documentação de habilitação, o Agente de Contratação verificará, em relação a todos os licitantes, o eventual descumprimento das condições de participação, especialmente quanto à existência de sanção que impeça a participação no certame ou a futura contratação, mediante consulta aos seguintes cadastros:
                </p>
            @else
                <p style="text-align: justify;">
                    8.1. COMO CONDIÇÃO PRÉVIA AO EXAME DA DOCUMENTAÇÃO DE HABILITAÇÃO
                    DO LICITANTE DETENTOR DA PROPOSTA CLASSIFICADA EM PRIMEIRO LUGAR, O
                    AGENTE DE CONTRATAÇÃO VERIFICARÁ O EVENTUAL DESCUMPRIMENTO DAS
                    CONDIÇÕES DE PARTICIPAÇÃO, ESPECIALMENTE QUANTO À EXISTÊNCIA DE
                    SANÇÃO QUE IMPEÇA A PARTICIPAÇÃO NO CERTAME OU A FUTURA
                    CONTRATAÇÃO, MEDIANTE A CONSULTA AOS DOCUMENTOS INSERIDOS NO
                    PORTAL DE COMPRAS PÚBLICAS, E AINDA NOS SEGUINTES CADASTROS:
                </p>
            @endif
            <p style="text-align: justify;">
                8.1.1. Cadastro Nacional de Empresas Inidôneas e Suspensas – CEIS e Cadastro Nacional de Empresas Punidas – CNEP (www.portaldatransparencia.gov.br);
            </p>
            <p style="text-align: justify;">
                8.1.2. Cadastro Nacional de Condenações Cíveis por Atos de Improbidade Administrativa e Inelegibilidade, mantido pelo Conselho Nacional de Justiça (www.cnj.jus.br/improbidade_adm/consultar_requerido.php);
            </p>
            <p style="text-align: justify;">
                8.1.3. Lista de Inidôneos, mantida pelo Tribunal de Contas da União – TCU
                https://contas.tcu.gov.br/ords/f?p=1660:3:0
            </p>
            <p style="text-align: justify;">
                8.1.4. A consulta aos cadastros será realizada em nome da empresa licitante e
                também de seu sócio majoritário, por força do artigo 12 da Lei n° 8.429/1992, que
                prevê, dentre as sanções impostas ao responsável pela prática de ato de
                improbidade administrativa, a proibição de contratar com o Poder Público,
                inclusive por intermédio de pessoa jurídica da qual seja sócio majoritário.
            </p>
            <p style="text-align: justify;">
                8.1.4.1. Havendo indícios de que o licitante busca burlar sanção aplicada a outra empresa, verificados por vínculos societários, linhas de fornecimento similares, entre outros, o Agente de Contratação diligenciará para apurar os fatos.
            </p>
            <p style="text-align: justify;">
                8.1.4.2. O licitante será convocado para manifestação previamente à sua inabilitação.
            </p>
            <p style="text-align: justify;">
                8.1.5. Constatada a existência de sanção, o Agente de Contratação reputará o
                licitante inabilitado, por falta de condição de participação.
            </p>
            @if (($processo->detalhe->inversao_fase ?? $detalhe->inversao_fase) === 'sim')
                <p style="text-align: justify;">
                    8.1.6. Como a habilitação antecede a fase de lances, somente os licitantes habilitados participarão da disputa, e o empate ficto (arts. 44 e 45 da LC nº 123/2006) será verificado ao final da fase de lances, entre os licitantes habilitados.
                </p>
            @else
                <p style="text-align: justify;">
                    8.1.6. No caso de inabilitação, haverá nova verificação, pelo sistema, da eventual
                    ocorrência do empate ficto, previsto nos arts. 44 e 45 da Lei Complementar nº 123/
                    2006, seguindo-se a disciplina antes estabelecida para aceitação da proposta
                    subsequente.
                </p>
            @endif
            @if($processo->detalhe->inversao_fase === "sim")
                <p style="text-align: justify;">
                    8.2. Os documentos necessários e suficiente para demonstrar a capacidade do licitante de realizar 
                    o objeto da licitação, serão exigidos para fins de habilitação, e deveram ser enviados, exclusivamente 
                    via plataforma, no momento do cadastro da proposta junto ao sistema, ficando vedado o envio de documentos via e-mail.
                </p>
            @else
                <p style="text-align: justify;">
                    8.2. Os documentos necessários e suficiente para demonstrar a capacidade do
                    licitante de realizar o objeto da licitação, serão exigidos para fins de habilitação,
                    apenas do licitante vencedor, nos termos de art. 62 a 70 da lei 14.133, e deveram
                    ser enviados em um prazo de 02 (duas) horas e deverão ser anexados
                    exclusivamente via plataforma, ficando vedado o envio de documentos via e-mail.
                </p>
            @endif
            
            <p style="text-align: justify;">
                8.3. Havendo a necessidade de envio de documentos de habilitação
                complementares, necessários à confirmação daqueles exigidos neste Edital e já
                apresentados, o licitante será convocado a encaminhá-los, em formato digital, via
                sistema, no prazo de 02 (duas) horas sob pena de inabilitação.
            </p>
            <p>8.4 <span style="font-weight: bold;">HABILITAÇÃO JURÍDICA:</span> </p>
            <p style="text-align: justify;">
                8.4.1. No caso de empresário individual: inscrição no Registro Público de
                Empresas Mercantis, a cargo da Junta Comercial da respectiva sede;
            </p>
            <p style="text-align: justify;">
                8.4.2. Em se tratando de microempreendedor individual – MEI: Certificado da
                Condição de Microempreendedor Individual - CCMEI, cuja aceitação ficará
                condicionada à verificação da autenticidade no sítio www.portaldoempreendedor.gov.br;
            </p>
            <p style="text-align: justify;">
                8.4.3. No caso de sociedade empresária ou empresa individual de
                responsabilidade limitada - EIRELI: ato constitutivo, estatuto ou contrato social em
                vigor, devidamente registrado na Junta Comercial da respectiva sede,
                acompanhado de documento comprobatório de seus administradores;
            </p>
            <p style="text-align: justify;">
                8.4.4. Inscrição no Registro Público de Empresas Mercantis onde opera, com
                averbação no Registro onde tem sede a matriz, no caso de ser o participante
                sucursal, filial ou agência;
            </p>
            <p style="text-align: justify;">
                8.4.5. No caso de sociedade simples: inscrição do ato constitutivo no Registro
                Civil das Pessoas Jurídicas do local de sua sede, acompanhada de prova da
                indicação dos seus administradores;
            </p>
            <p style="text-align: justify;">
                8.4.6. No caso de cooperativa: ata de fundação e estatuto social em vigor, com a
                ata da assembleia que o aprovou, devidamente arquivado na Junta Comercial ou
                inscrito no Registro Civil das Pessoas Jurídicas da respectiva sede, bem como o
                registro de que trata o art. 107 da Lei nº 5.764, de 1971;
            </p>
            <p style="text-align: justify;">
                8.4.7. No caso de empresa ou sociedade estrangeira em funcionamento no País:
                decreto de autorização;
            </p>
            <p style="text-align: justify;">
                8.4.8. Os documentos acima deverão estar acompanhados de todas as
                alterações ou da consolidação respectiva;
            </p>

            <p>8.5 <span style="font-weight: bold;">HABILITAÇÃO FISCAL, SOCIAL E TRABALHISTA:</span> </p>
            <p style="text-align: justify;">
                8.5.1. Prova de inscrição no Cadastro Nacional de Pessoas Jurídicas (CNPJ) ou
                no Cadastro de Pessoas Físicas (CPF), conforme o caso;
            </p>
            <p style="text-align: justify;">
                8.5.2. Prova de inscrição no cadastro de contribuintes estadual e/ou municipal,
                se houver relativo ao domicílio ou sede do licitante, pertinente ao seu ramo de
                atividade e compatível com o objeto contratual;
            </p>
            <p style="text-align: justify;">
                8.5.3. Prova de regularidade fiscal perante a Fazenda Nacional para pessoa jurídica, 
                mediante apresentação de certidão expedida conjuntamente pela Secretaria da Receita 
                Federal do Brasil (RFB) e pela Procuradoria-Geral da Fazenda Nacional (PGFN), referente a 
                todos os créditos tributários federais e à Dívida Ativa da União (DAU) por elas administrados, 
                inclusive aqueles relativos à Seguridade Social, nos termos da Portaria Conjunta nº 1.751, de 02/10/2014, 
                do Secretário da Receita Federal do Brasil e da Procuradora-Geral da Fazenda Nacional.
            </p>
            <p style="text-align: justify;">
                8.5.4. Prova de regularidade com o Fundo de Garantia do Tempo de Serviço
                (FGTS);
            </p>
            <p style="text-align: justify;">
                8.5.5. Prova de inexistência de débitos inadimplidos perante a justiça do
                trabalho, mediante a apresentação de certidão negativa ou positiva com efeito de
                negativa, nos termos do Título VII-A da Consolidação das Leis do Trabalho,
                aprovada pelo Decreto-Lei nº 5.452/1943;
            </p>
            <p style="text-align: justify;">
                8.5.6. Prova de regularidade junto à Fazenda Estadual, através da certidão negativa ou positiva com efeitos de negativa conjunta junto aos Tributos Estaduais, emitida pela Secretaria da Fazenda Estadual onde a empresa for sediada;
            </p>
            <p style="text-align: justify;">
                8.5.7. Prova de regularidade junto à Fazenda Municipal, através da certidão negativa ou positiva com efeitos de negativa junto aos Tributos Municipais, emitida pela Secretaria da Fazenda Municipal onde a empresa for sediada;
            </p>
            <p style="text-align: justify;">
                8.5.8. A microempresa ou empresa de pequeno porte deverá apresentar toda a documentação exigida para comprovação de regularidade fiscal e trabalhista, mesmo que esta apresente alguma restrição, sob pena de inabilitação (art. 43 da LC nº 123/2006). A restrição não impedirá sua participação na fase de lances nem sua declaração como vencedora, observado o item 8.12.
            </p>
            {!! preg_replace(['/<\/?ul[^>]*>/', '/\sstyle="[^"]*"/i', '/\sclass="[^"]*"/i'], '', $detalhe->regularidade_fisica) !!}
            <p>8.6. <span style="font-weight: bold;">HABILITAÇÃO ECONÔMICO-FINANCEIRA.</span> </p>
            <p style="text-align: justify;">
                8.6.1. Certidão Negativa de falência, de concordata, de recuperação judicial ou extrajudicial 
                (Lei nº 11.101/2005) pessoa jurídica, expedida pelo distribuidor da sede da empresa, datado dos 
                últimos 30 (trinta) dias, ou que esteja dentro do prazo de validade expresso na própria Certidão; 
            </p>
            <p style="text-align: justify;">
                8.6.2. No caso de certidão positiva de recuperação judicial ou extrajudicial, o
                licitante deverá apresentar a comprovação de que o respectivo plano de
                recuperação foi acolhido judicialmente, na forma do art. 58, da Lei n.º 11.101, de
                09 de fevereiro de 2005, sob pena de inabilitação, devendo, ainda, comprovar todos
                os demais requisitos de habilitação.
            </p>
            <p style="text-align: justify;">
                8.6.3. Balanço patrimonial, demonstração de resultado de exercício e demais demonstrações contábeis dos 2 (dois) últimos exercícios sociais, já exigíveis e apresentados na forma da lei, vedada a substituição por balancetes ou balanços provisórios (art. 69, I).
            </p>
            <p style="text-align: justify;">
                8.6.3.1. A empresa constituída há menos de 2 (dois) anos apresentará os documentos do último exercício (art. 69, §6º); a constituída no exercício social vigente apresentará o balanço de abertura ou o referente ao período de existência da sociedade.
            </p>
            <p style="text-align: justify;">
                8.6.3.2. É admissível o balanço intermediário, se decorrer de lei ou contrato
                social/estatuto social.
            </p>
            <p style="text-align: justify;">
                8.6.4. A comprovação da situação financeira da empresa será constatada
                mediante obtenção de índices de Liquidez Geral (LG), Solvência Geral (SG) e
                Liquidez Corrente (LC), superiores a 1 (hum) resultantes da aplicação das fórmulas:
            </p>
            <div style="margin-left: 200px;">
                <div style="font-family:'Times New Roman', serif; margin: 15px 0;">
                    <span style="display:inline-block; width:40px; text-align:right; font-weight:bold;">LG =</span>
                    <div style="display:inline-block; vertical-align:middle; text-align:center;">
                        <span style="display:block; padding:2px 10px;">Ativo Circulante + Realizável a Longo Prazo</span>
                        <span style="display:block; border-top:1px solid #000; padding:2px 10px;">Passivo Circulante + Passivo Não Circulante</span>
                    </div>
                </div>

                <div style="font-family:'Times New Roman', serif; margin: 15px 0;">
                    <span style="display:inline-block; width:40px; text-align:right; font-weight:bold;">SG =</span>
                    <div style="display:inline-block; vertical-align:middle; text-align:center;">
                        <span style="display:block; padding:2px 10px;">Ativo Total</span>
                        <span style="display:block; border-top:1px solid #000; padding:2px 10px;">Passivo Circulante + Passivo Não Circulante</span>
                    </div>
                </div>

                <div style="font-family:'Times New Roman', serif; margin: 15px 0;">
                    <span style="display:inline-block; width:40px; text-align:right; font-weight:bold;">LC =</span>
                    <div style="display:inline-block; vertical-align:middle; text-align:center;">
                        <span style="display:block; padding:2px 10px;">Ativo Circulante</span>
                        <span style="display:block; border-top:1px solid #000; padding:2px 10px;">Passivo Circulante</span>
                    </div>
                </div>
            </div>
            <p style="text-align: justify;">
                8.6.5. A empresa que apresentar resultado igual ou inferior a 1 (um) em qualquer dos índices do item 8.6.4 deverá comprovar patrimônio líquido mínimo de 10% (dez por cento) do valor estimado da contratação (art. 69, §4º).
            </p>
            <p style="text-align: justify;">
                8.6.6. As licitantes deverão ainda complementar a comprovação da
                qualificação econômico-financeira por meio de comprovação de patrimônio
                líquido de 10% (dez por cento) do valor estimado da contratação, por meio da
                apresentação do balanço patrimonial e demonstrações contáveis do último
                exercício social, apresentados na forma da lei, vedada a substituição por
                balancetes ou balanços provisórios, podendo ser atualizados por índices oficiais
                quando encerrados há mais de 3 (três) meses da data da apresentação da
                proposta.
            </p>
            <p style="text-align: justify;">
                8.6.7. Garantia da proposta: Conforme o estabelecido no artigo 58, § 1º da Lei
                Federal n.º 14.133/2021, no importe de 1% (um por cento) do valor estimado para
                a contratação, que deverá estar em nome do Município.
            </p>
            <p style="text-align: justify;">
                8.6.7.1. Os licitantes deverão apresentar comprovante da referida garantia da
                proposta sob uma das modalidades, nos termos do art. 96, da Lei nº 14.133/2021:
            </p>
            <p style="text-align: justify;">
                a) caução em dinheiro ou em títulos da dívida pública emitidos sob a forma
                escritural, mediante registro em sistema centralizado de liquidação e de custódia
                autorizado pelo Banco Central do Brasil, e avaliados por seus valores econômicos,
                conforme definido pelo Ministério da Economia;
            </p>
            <p style="text-align: justify;">
                b) seguro-garantia;
            </p>
            <p style="text-align: justify;">
                c) fiança bancária emitida por banco ou instituição financeira devidamente
                autorizada a operar no País pelo Banco Central do Brasil;
            </p>
            <p style="text-align: justify;">
                d) título de capitalização custeado por pagamento único, com resgate pelo
                valor total. (Incluído pela Lei nº 14.770, de 2023), acompanhado de anuência
                da Instituição Financeira.
            </p>
            <p style="text-align: justify;">
                8.6.7.2. Em caso de caução em dinheiro, o depósito deverá ser feito em conta
                própria fornecida pela Secretaria de Finanças do Município, sendo que garantia de
                proposta será devolvida aos licitantes no prazo de 10 (dez) dias úteis, contado da
                assinatura do contrato ou da data em que for declarada fracassada a licitação.
            </p>
            <p style="text-align: justify;">
                8.6.7.3. A garantia será analisada na fase de habilitação, que antecede a fase de lances, e sua ausência ou insuficiência implicará a inabilitação do licitante.
            </p>
            <p style="text-align: justify;">
                8.6.7.4. Implicará a execução do valor integral da garantia a recusa em assinar o contrato ou a não apresentação dos documentos para a contratação (art. 58, §3º).
            </p>
            <p style="text-align: justify;">
                8.6.8. Declaração de Capacidade financeira, obrigatoriamente em papel
                timbrado da empresa, apresentando as demonstrações contábeis do último
                exercício social, devidamente assinada pelo Representante Legal da Empresa e
                pelo Contador responsável.
            </p>
            @if (!empty($detalhe?->qualificacao_economica))
                <div style="text-align: justify;">
                    {!! preg_replace(['/<\/?ul[^>]*>/', '/\sstyle="[^"]*"/i', '/\sclass="[^"]*"/i'], '', $detalhe->qualificacao_economica) !!}
                </div>
            @endif
            <p>8.7. <span style="font-weight: bold;">QUALIFICAÇÃO TÉCNICA.</span> </p>
            <p style="text-align: justify;">
                8.7.1. Registro ou inscrição da empresa licitante no CREA (Conselho Regional
                de Engenharia e Agronomia) e/ou CAU (Conselho de Arquitetura e Urbanismo),
                conforme as áreas de atuação previstas no Projeto Básico, em plena validade.
            </p>
            <p style="text-align: justify;">
                8.7.2. Quanto à capacitação técnico-profissional: comprovação da empresa
                licitante de possuir em seu quadro, profissional (is) de nível superior ou outro(s)
                reconhecido(s) pelo CREA, CAU, ou CRT.
            </p>
            <p style="text-align: justify;">
                8.7.3. A comprovação do vínculo do(s) profissional(is), do quadro da licitante, será comprovada mediante a apresentação dos documentos a seguir:
            </p>
            <p style="text-align: justify;">
                8.7.3.1. Empregado: Cópia do livro de registro de empregado registrado na
                Delegacia Regional do Trabalho - DRT ou cópia da Carteira de Trabalho e
                Previdência Social – CTPS anotada ou ainda, contrato de prestação de serviços, na
                forma da legislação trabalhista;
            </p>
            <p style="text-align: justify;">
                8.7.3.2. Sócio: Contrato Social devidamente registrado no órgão competente;
            </p>
            <p style="text-align: justify;">
                8.7.3.3. Diretor: Cópia do Contrato Social, em se tratando de firma individual ou
                limitada ou cópia da ata de eleição devidamente publicada na imprensa, em se
                tratando de sociedade anônima;
            </p>
            <p style="text-align: justify;">
                8.7.3.4. Profissional autônomo: cópia do contrato de prestação de serviços assinado pelas partes, ou declaração de compromisso de vinculação futura, caso o licitante seja vencedor, acompanhada da anuência do profissional;
            </p>
            <p style="text-align: justify;">
                8.7.3.5. Responsável Técnico: Além da cópia da Certidão expedida pelo CREA,
                CAU ou CRT da sede ou filial da licitante onde consta o registro do profissional
                como responsável técnico, deverá comprovar o vínculo em uma das formas
                contidas do subitem retro.
            </p>
            <p style="text-align: justify;">
                8.7.4. Caso a licitante seja sociedade cooperativa, os responsáveis técnicos
                e/ou membros da equipe técnica devem ser cooperados, demonstrando-se tal
                condição através da apresentação das respectivas atas de inscrição, da
                comprovação da integralização das respectivas quotas-partes e de três registros
                de presença desses cooperados em assembleias gerais ou nas reuniões
                seccionais, bem como da comprovação de que estão domiciliados em localidade
                abrangida na definição do artigo 4°, inciso XI, da Lei n° 5.764, de 1971.
            </p>
            <p style="text-align: justify;">
                8.7.5. Os profissionais indicados pelo licitante para fins de comprovação da
                capacitação técnico-profissional deverão participar da obra ou serviço objeto
                desta licitação, admitindo-se a substituição por profissionais de experiência
                equivalente ou superior, desde que aprovado pela Contratante.
            </p>
            <p>
                {!! $detalhe->exigencias_tecnicas !!}
            </p>
        </div>
        <div>
            <p>8.8. <span style="font-weight: bold;">DOCUMENTAÇÃO COMPLEMENTAR</span> </p>
            <p style="text-align: justify;">
                8.8.1. Declaração em modelo próprio que não emprega menor de 18 anos em
                trabalho noturno, perigoso ou insalubre e não emprega menor de 16 anos, salvo
                menor, a partir de 14 anos, na condição de aprendiz, nos termos do artigo 7°, XXXIII,
                da Constituição Federal de 1988;
            </p>
            <p style="text-align: justify;">
                8.8.2. Declaração em modelo próprio que a proposta foi elaborada de forma
                independente;
            </p>
            <p style="text-align: justify;">
                8.8.3. Declaração em modelo próprio que não possui, em sua cadeia produtiva,
                empregados executando trabalho degradante ou forçado, observando o disposto
                nos incisos III e IV do art. 1º e no inciso III do art. 5º da Constituição Federal;
            </p>
            <p style="text-align: justify;">
                8.8.4. Declaração, assinada pelo responsável técnico do licitante, de que tem pleno conhecimento do local e das condições e peculiaridades da obra, sendo facultada a realização de vistoria prévia, mediante agendamento prévio com a Secretaria Municipal de Obras pelo telefone/e-mail, até o dia útil anterior à abertura da sessão (art. 63, §§2º e 3º).
            </p>
            @if (($processo->detalhe->inversao_fase ?? $detalhe->inversao_fase) === 'sim')
                <p style="text-align: justify;">
                    8.9. Concluída a análise dos documentos de habilitação de todos os licitantes, o Agente de Contratação registrará no sistema o resultado, com a motivação das inabilitações, e somente os licitantes habilitados participarão da fase de lances. A intenção de recorrer contra a habilitação ou a inabilitação será manifestada na fase recursal única, após o julgamento das propostas (art. 165, §1º).
                </p>
            @else
                <p style="text-align: justify;">
                    8.9. O Agente de Contratação fará a análise dos documentos de habilitação do
                    licitante vencedor momento que será franqueada vista aos interessados após a
                    análise será aberto o prazo para manifestação da intenção de interposição de
                    recurso.
                </p>
            @endif
            <p style="text-align: justify;">
                8.10. A existência de restrição relativamente à regularidade fiscal e trabalhista
                não impede que a licitante qualificada como microempresa ou empresa de
                pequeno porte seja declarada vencedora, uma vez que atenda a todas as demais
                exigências do edital.
            </p>
            @if (($processo->detalhe->inversao_fase ?? $detalhe->inversao_fase) === 'sim')
                <p style="text-align: justify;">
                    8.11. A declaração do vencedor ocorrerá após o julgamento da proposta, na forma do item 7.
                </p>
            @else
                <p style="text-align: justify;">
                    8.11. A declaração do vencedor acontecerá no momento imediatamente
                    posterior à fase de habilitação.
                </p>
            @endif
            <p style="text-align: justify;">
                8.12. Caso a proposta mais vantajosa seja ofertada por licitante qualificada
                como microempresa ou empresa de pequeno porte, e uma vez constatada a
                existência de alguma restrição no que tange à regularidade fiscal e trabalhista, a
                mesma será convocada para, no prazo de 5 (cinco) dias úteis, após a declaração
                do vencedor, comprovar a regularização. O prazo poderá ser prorrogado por igual
                período, a critério da administração pública, quando requerida pelo licitante,
                mediante apresentação de justificativa.
            </p>
            <p style="text-align: justify;">
                8.13. A não-regularização fiscal e trabalhista no prazo previsto no subitem
                anterior acarretará a inabilitação do licitante, sem prejuízo das sanções previstas
                neste Edital, sendo facultada a convocação dos licitantes remanescentes, na
                ordem de classificação. Se, na ordem de classificação, seguir-se outra
                microempresa, empresa de pequeno porte ou sociedade cooperativa com alguma
                restrição na documentação fiscal e trabalhista, será concedido o mesmo prazo
                para regularização.
            </p>
            <p style="text-align: justify;">
                8.14. Havendo necessidade de analisar minuciosamente os documentos
                exigidos, o Agente de Contratação suspenderá a sessão, informando no “chat” a
                nova data e horário para a continuidade da mesma.
            </p>
            <p style="text-align: justify;">
                8.15. Será inabilitado o licitante que não comprovar sua habilitação, seja por não
                apresentar quaisquer dos documentos exigidos, ou apresentá-los em desacordo
                com o estabelecido neste Edital.
            </p>
            @if (($processo->detalhe->inversao_fase ?? $detalhe->inversao_fase) !== 'sim')
                <p style="text-align: justify;">
                    8.16. Nos itens não exclusivos a microempresas e empresas de pequeno porte,
                    em havendo inabilitação, haverá nova verificação, pelo sistema, da eventual
                    ocorrência do empate ficto, previsto nos artigos 44 e 45 da LC nº 123/2006,
                    seguindo-se a disciplina antes estabelecida para aceitação da proposta
                    subsequente.
                </p>
                <p style="text-align: justify;">
                    8.17. Constatado o atendimento às exigências de habilitação fixadas no Edital, o
                    licitante será declarado vencedor.
                </p>
            @endif
            <p style="font-weight: bold;"> 9. DO TRATAMENTO DIFERENCIADO ÀS MICROEMPRESAS, EMPRESAS DE PEQUENOS PORTE </p>
            <p style="text-align: justify;">
                9.1. O tratamento diferenciado conferido às empresas de pequeno porte, às
                microempresas e às cooperativas de que tratam a Lei Complementar 123, de 14 de
                dezembro de 2006 e a Lei 11.488, de 15 de junho de 2007, deverá seguir o
                procedimento descrito a seguir:
            </p>
            <p style="text-align: justify;">
                9.2. Os licitantes deverão indicar no sistema eletrônico de licitações, antes do
                encaminhamento da proposta eletrônica de preços, a sua condição de
                microempresa, empresa de pequeno porte ou cooperativa.
            </p>
            <p style="text-align: justify;">
                9.3. O licitante que não informar sua condição antes do envio das propostas
                perderá o direito ao tratamento diferenciado.
            </p>
            <p style="text-align: justify;">
                9.4. Ao final da sessão pública de disputa de lances, o sistema
                eletrônico detectará automaticamente as situações de empate a que se referem
                os §§ 1o e 2o do art. 44 da Lei Complementar 123/2006, de 14 de dezembro de 2006.
            </p>
            <p style="text-align: justify;">
                9.5. Considera-se empate aquelas situações em que as propostas
                apresentadas pelas microempresas, empresas de pequeno porte e cooperativas
                sejam iguais ou até 5% (cinco por cento) superiores à proposta mais bem
                classificada, quando esta for proposta de licitante não enquadrado como
                microempresa, empresa de pequeno porte ou cooperativa.
            </p>
            <p style="text-align: justify;">
                9.6. Não ocorre empate quando a detentora da proposta mais bem classificada
                possuir a condição de microempresa, empresa de pequeno porte ou cooperativa.
                Nesse caso, o Agente de Contratação prosseguirá com o julgamento da proposta
                da arrematante, cujos documentos de habilitação já terão sido analisados na fase
                própria.
            </p>
            <p style="text-align: justify;">
                9.7. Caso ocorra a situação de empate descrita no item 9.5, o agente de
                contratação convocará o representante da empresa de pequeno porte, da
                microempresa ou da cooperativa mais bem classificada, imediatamente e por meio
                do sistema eletrônico, a ofertar lance inferior ao menor lance registrado para o item
                no prazo de cinco minutos.
            </p>
            <p style="text-align: justify;">
                9.8. Caso a licitante convocada não apresente lance inferior ao menor valor
                registrado no prazo acima indicado, as demais microempresas, empresas de
                pequeno porte ou cooperativas que porventura possuam lances ou propostas,
                deverão ser convocadas, na ordem de classificação, a ofertar lances inferiores à
                menor proposta.
            </p>
            <p style="text-align: justify;">
                9.9. A microempresa, empresa de pequeno porte ou cooperativa que primeiro
                apresentar lance inferior ao menor lance ofertado na sessão de disputa será
                considerada arrematante pelo Agente de Contratação, que encerrará a disputa do
                item na sala virtual, devendo a arrematante apresentar a proposta readequada, na
                forma do item 7.16.
            </p>
            <p style="text-align: justify;">
                9.10. O não oferecimento de lances no prazo específico destinado a cada
                licitante produz a preclusão do direito de apresentá-los. Os lances apresentados
                em momento inadequado, antes do início do prazo específico ou após o seu
                término serão considerados inválidos
            </p>
            <p style="text-align: justify;">
                9.11. Caso a proposta inicialmente mais bem classificada, de licitante não
                enquadrado como microempresa, empresa de pequeno porte ou cooperativa, seja
                desclassificada pelo agente de contratação, por desatendimento ao edital, essa
                proposta não é mais considerada como parâmetro para o efeito do empate de que
                trata esta cláusula.
            </p>
            <p style="text-align: justify;">
                9.12. Para o efeito do empate, no caso da desclassificação de que trata o item
                anterior, a melhor proposta passa a ser a da próxima licitante não enquadrada
                como microempresa, empresa de pequeno porte ou cooperativa.
            </p>
            <p style="text-align: justify;">
                9.13. No caso de o sistema eletrônico não convocar automaticamente a
                microempresa, empresa de pequeno porte ou cooperativa, o agente de contratação
                fará através do “chat de mensagens”.
            </p>
            <p style="text-align: justify;">
                9.14. O julgamento da habilitação das microempresas, empresas de pequeno
                porte e cooperativas obedecerá aos critérios gerais definidos neste edital,
                observadas as particularidades de cada pessoa jurídica.
            </p>
            <p style="font-weight: bold;">10. DOS RECURSOS. </p>
            <p style="text-align: justify;">
                10.1. Declarado o vencedor e decorrido, se for o caso, o prazo de regularização
                fiscal e trabalhista de microempresa ou empresa de pequeno porte, o Agente de
                Contratação abrirá o prazo de 30 (trinta) minutos para que qualquer licitante
                manifeste, imediata e motivadamente, em campo próprio do sistema, a intenção de
                recorrer contra os atos de habilitação ou inabilitação e de julgamento das
                propostas, em fase única (art. 165, §1º).
            </p>
            <p style="text-align: justify;">
                10.2. O recorrente terá o prazo de 3 (três) dias úteis para apresentar as razões
                pelo sistema eletrônico, contado da data de intimação ou de lavratura da ata de
                julgamento (art. 165, I, e §1º, I). Os demais licitantes poderão apresentar
                contrarrazões em igual prazo, contado da data de divulgação da interposição do
                recurso (art. 165, §4º), assegurada vista imediata dos elementos indispensáveis à
                defesa de seus interesses (art. 165, §5º).
            </p>
            <p style="text-align: justify;">
                10.3. O acolhimento do recurso invalida tão somente os atos insuscetíveis de
                aproveitamento.
            </p>
            <p style="text-align: justify;">
                10.4. Os autos do processo permanecerão com vista franqueada aos
                interessados, no endereço constante neste Edital.
            </p>
            <p style="font-weight: bold;">11. DA REABERTURA DA SESSÃO PÚBLICA.</p>
            <p style="text-align: justify;">
                11.1. A sessão pública poderá ser reaberta:
            </p>
            <p style="text-align: justify;">
                11.1.1. Nas hipóteses de provimento de recurso que leve à anulação de atos
                anteriores à realização da sessão pública precedente ou em que seja anulada a
                própria sessão pública, situação em que serão repetidos os atos anulados e os que
                dele dependam.
            </p>
            <p style="text-align: justify;">
                11.1.2. Quando houver erro na aceitação do preço melhor classificado ou
                quando o licitante declarado vencedor não assinar o contrato, não retirar o
                instrumento equivalente ou não comprovar a regularização fiscal e trabalhista, nos
                termos do art. 43, §1º da LC nº 123/2006. Nessas hipóteses, serão adotados os
                procedimentos imediatamente posteriores ao encerramento da etapa de lances.
            </p>
            <p style="text-align: justify;">
                11.2. Todos os licitantes remanescentes deverão ser convocados para
                acompanhar a sessão reaberta.
            </p>
            <p style="text-align: justify;">
                11.2.1. A convocação se dará por meio do sistema eletrônico (“chat”).
            </p>
            <p style="font-weight: bold;">12. DA ADJUDICAÇÃO E HOMOLOGAÇÃO.</p>
            <p style="text-align: justify;">
                12.1. Julgados os recursos, constatada a regularidade dos atos praticados, a
                Autoridade Superior adjudicará e homologará a licitação.
            </p>
            <p style="font-weight: bold;">13. DA GARANTIA DE EXECUÇÃO. </p>
            <p style="text-align: justify;">
                13.1. Não será exigida a prestação de garantia na presente contratação.
            </p>
            <p style="font-weight: bold;">14. DO TERMO DE CONTRATO OU INSTRUMENTO EQUIVALENTE</p>
            <p style="text-align: justify;">
                14.1. Após a homologação da licitação, em sendo realizada a contratação, será
                firmado Termo de Contrato ou emitido instrumento equivalente.
            </p>
            <p style="text-align: justify;">
                14.2. O adjudicatário terá o prazo de 02 dias úteis, contados a partir da data de sua
                convocação, para assinar o Termo de Contrato ou aceitar instrumento equivalente,
                conforme o caso (Nota de Empenho/Carta Contrato/Autorização), sob pena de
                decair do direito à contratação, sem prejuízo das sanções previstas neste Edital.
            </p>
            <p style="text-align: justify;">
                14.2.1. Alternativamente à convocação para comparecer perante o órgão ou
                entidade para a assinatura do Termo de Contrato ou aceite do instrumento
                equivalente, a Administração poderá encaminhá-lo para assinatura ou aceite da
                Adjudicatária, mediante correspondência postal com aviso de recebimento (AR) ou
                meio eletrônico, para que seja assinado ou aceito no prazo de 2 (dois) dias úteis, a contar da
                data de seu recebimento.
            </p>
            <p style="text-align: justify;">
                14.2.2. O prazo previsto no subitem anterior poderá ser prorrogado, por igual
                período, por solicitação justificada do adjudicatário e aceita pela Administração
            </p>
            <p style="text-align: justify;">
                14.3. O Aceite da Nota de Empenho ou do instrumento equivalente, emitida à
                empresa adjudicada, implica no reconhecimento de que:
            </p>
            <p style="text-align: justify;">
                14.3.1. Referida Nota está substituindo o contrato, aplicando-se à relação de
                negócios ali estabelecida as disposições da Lei nº 14.133/2021;
            </p>
            <p style="text-align: justify;">
                14.3.2. A contratada se vincula à sua proposta e às previsões contidas no edital e
                seus anexos;
            </p>
            <p style="text-align: justify;">
                14.3.3. A contratada reconhece que as hipóteses de rescisão são aquelas previstas
                no artigo 137 da Lei nº 14.133/2021 e reconhece os direitos da Administração
                previstos nos artigos 138 e 139 da mesma Lei.
            </p>
            <p style="text-align: justify;">
                14.4. O prazo de vigência da contratação é o estabelecido no Projeto Básico.
            </p>
            <p style="text-align: justify;">
                14.5. Previamente à contratação, a Administração consultará os cadastros
                relacionados no item 8.1 para verificar a manutenção das condições de
                participação e a inexistência de sanção que impeça a contratação.
            </p>
            <p style="text-align: justify;">
                14.6. Na assinatura do contrato, será exigida a comprovação das condições de
                habilitação consignadas neste Edital, as quais deverão ser mantidas pelo licitante
                durante a vigência do contrato.
            </p>
            <p style="text-align: justify;">
                14.6.1. Na hipótese de irregularidade, o contratado deverá regularizar a sua
                situação quanto às condições de habilitação no prazo de até 05 (cinco) dias úteis,
                sob pena de aplicação das penalidades previstas no edital e anexos.
            </p>
            <p style="text-align: justify;">
                14.7. Na hipótese de o vencedor não comprovar as condições de habilitação ou se
                recusar a assinar o contrato, a Administração, sem prejuízo das sanções cabíveis,
                poderá convocar os licitantes remanescentes, na ordem de classificação, para a
                celebração do contrato nas condições propostas pelo vencedor. Não havendo
                aceitação, poderá convocá-los para negociação, na ordem de classificação, com
                vistas a preço melhor, ainda que acima do preço do vencedor, ou adjudicar e
                celebrar o contrato nas condições por eles ofertadas, quando frustrada a
                negociação (art. 90, §§2º e 4º).
            </p>
        </div>
        <div>
            <p style="font-weight: bold;">15. DO REAJUSTAMENTO EM SENTIDO GERAL.</p>
            <p style="text-align: justify;">
                15.1. As regras acerca do reajustamento em sentido geral do valor contratual
                serão regidas pelas normas da lei 14.133/21.
            </p>

            <p style="font-weight: bold;">16. DO RECEBIMENTO DO OBJETO E DA FISCALIZAÇÃO.</p>
            <p style="text-align: justify;">
                16.1. Os critérios de recebimento e aceitação do objeto e de fiscalização estão
                previstos no contrato.
            </p>

            <p style="font-weight: bold;">17. DAS OBRIGAÇÕES DA CONTRATANTE E DA CONTRATADA.</p>
            <p style="text-align: justify;">
                17.1. As obrigações da Contratante e da Contratada são as estabelecidas na
                Minuta do Contrato.
            </p>

            <p style="font-weight: bold;">18. DO PAGAMENTO.</p>
            <p style="text-align: justify;">
                18.1. As regras acerca do pagamento são as estabelecidas na minuta do contrato,
                anexo a este Edital.
            </p>
            <p style="font-weight: bold;">19. DAS SANÇÕES ADMINISTRATIVAS.</p>
            <p style="text-align: justify;">
                19.1. Comete infração administrativa, nos termos da Lei nº 14.133/2021, o
                licitante/adjudicatário que:
            </p>
            <p style="text-align: justify;">
                19.1.1. Der causa à inexecução parcial ou total do contrato;
            </p>
            <p style="text-align: justify;">
                19.1.2. Deixar de entregar os documentos exigidos no certame;
            </p>
            <p style="text-align: justify;">
                19.1.3. Não mantiver a proposta, salvo em decorrência de fato superveniente
                devidamente justificado;
            </p>
            <p style="text-align: justify;">
                19.1.4. Não assinar o termo de contrato ou aceitar/retirar o instrumento
                equivalente, quando convocado dentro do prazo de validade da proposta;
            </p>
            <p style="text-align: justify;">
                19.1.5. Ensejar o retardamento da execução ou entrega do objeto da licitação
                sem motivo justificado;
            </p>
            <p style="text-align: justify;">
                19.1.6. Apresentar declaração ou documentação falsa;
            </p>
            <p style="text-align: justify;">
                19.1.7. Fraudar a licitação ou praticar ato fraudulento na execução do contrato;
            </p>
            <p style="text-align: justify;">
                19.1.8. Comportar-se de modo inidôneo ou cometer fraude de qualquer
                natureza;
            </p>
            <p style="text-align: justify;">
                19.1.9. Praticar atos ilícitos com vistas a frustrar os objetivos da licitação;
            </p>
            <p style="text-align: justify;">
                19.1.10. Praticar ato lesivo previsto no art. 5º da Lei nº 12.846/2013.
            </p>
            <p style="text-align: justify;">
                19.2. O licitante/adjudicatário que cometer qualquer das infrações discriminadas
                nos subitens anteriores ficará sujeito, sem prejuízo da responsabilidade civil e
                criminal, às seguintes sanções:<br><br>

                a) Advertência por escrito; <br>
                b) Multa; <br>
                c) Impedimento de licitar e contratar; <br>
                d) Declaração de inidoneidade para licitar ou contratar.<br>
            </p>
            <p style="text-align: justify;">
                19.3. A penalidade de multa pode ser aplicada cumulativamente com as demais
                sanções.
            </p>
            <p style="text-align: justify;">
                19.4. Do ato que aplicar a penalidade caberá recurso, no prazo de 15 (quinze) dias
                úteis, a contar da ciência da intimação, podendo a autoridade que tiver proferido o
                ato reconsiderar sua decisão ou, no prazo de 05 (cinco) dias encaminhá-lo
                devidamente informado para a apreciação e decisão superior, no prazo de 20 (vinte)
                dias úteis.
            </p>
            <p style="text-align: justify;">
                19.5. Serão publicadas na Imprensa Oficial, as sanções administrativas previstas
                no ITEM 19.2, c, d, deste edital, inclusive a reabilitação perante a Administração
                Pública.
            </p>
            <p style="text-align: justify;">
                19.6. DA FRAUDE E DA CORRUPÇÃO - Os licitantes e o contratado devem observar
                e fazer observar, por seus fornecedores e subcontratados, se admitida à
                subcontratação, o mais alto padrão de ética durante todo o processo de licitação,
                de contratação e de execução do objeto contratual.
            </p>
            <p style="text-align: justify;">
                19.6.1. PARA OS PROPÓSITOS DESTA CLÁUSULA, DEFINEM-SE AS SEGUINTES
                PRÁTICAS:<br><br>

                a) PRÁTICA CORRUPTA: Oferecer, dar, receber ou solicitar, direta ou indiretamente,
                qualquer vantagem com o objetivo de influenciar a ação de servidor público no
                processo de licitação ou na execução do contrato;<br>
                b) PRÁTICA FRAUDULENTA: A falsificação ou omissão dos fatos, com o objetivo de
                influenciar o processo de licitação ou de execução do contrato;<br>
                c) PRÁTICA CONCERTADA: Esquematizar ou estabelecer um acordo entre dois ou
                mais licitantes, com ou sem o conhecimento de representantes ou prepostos do
                órgão licitador, visando estabelecer preços em níveis artificiais e não-competitivos;<br>
                d) PRÁTICA COERCITIVA: Causar danos ou ameaçar causar dano, direta ou
                indiretamente, às pessoas ou sua propriedade, visando influenciar sua
                participação em um processo licitatório ou afetar a execução do contrato.<br>
                e) PRÁTICA OBSTRUTIVA: Destruir, falsificar, alterar ou ocultar provas em
                inspeções ou fazer declarações falsas aos representantes do organismo financeiro
                multilateral, com o objetivo de impedir materialmente a apuração de alegações de
                prática prevista acima; atos cuja intenção seja impedir materialmente o exercício
                do direito de o organismo financeiro multilateral promover inspeção.
            </p>
            <p style="font-weight: bold;"> 20. DA IMPUGNAÇÃO AO EDITAL E DO PEDIDO DE ESCLARECIMENTO.</p>
            <p style="text-align: justify;">
                20.1. Até 03 (três) dias úteis antes da data designada para a abertura da sessão
                pública, qualquer pessoa poderá impugnar este Edital e/ou apresentar pedido de
                esclarecimento.
            </p>
            <p style="text-align: justify;">
                20.2. A IMPUGNAÇÃO e/ou PEDIDO DE ESCLARECIMENTO DEVERÃO ser feitos
                EXCLUSIVAMENTE por FORMA ELETRÔNICA no sistema.
            </p>
            <p style="text-align: justify;">
                20.3. A resposta à impugnação ou ao pedido de esclarecimento será divulgada no
                Portal de Compras Públicas no prazo de até 3 (três) dias úteis, limitado ao último
                dia útil anterior à data da abertura do certame.
            </p>
            <p style="text-align: justify;">
                20.4. Acolhida a impugnação, será definida e publicada nova data para a
                realização do certame.
            </p>
            <p style="text-align: justify;">
                20.5. As impugnações e pedidos de esclarecimentos não suspendem os prazos
                previstos no certame, salvo quando se amoldarem ao art. 55 parágrafo 1º, da Lei nº
                14.133/2021.
            </p>
            <p style="text-align: justify;">
                20.5.1. A concessão de efeito suspensivo à impugnação é medida excepcional e
                deverá ser motivada pelo Agente de Contratação, nos autos do processo de
                licitação.
            </p>
            <p style="text-align: justify;">
                20.6. As respostas aos pedidos de esclarecimentos serão divulgadas pelo
                sistema e vincularão os participantes e a administração.
            </p>
            <p style="text-align: justify;">
                20.7. As respostas às impugnações e aos esclarecimentos solicitados, bem como
                outros avisos de ordem geral, serão cadastradas no sítio www.comprasbr.com.br,
                sendo de responsabilidade dos licitantes, seu acompanhamento.
            </p>
            <p style="text-align: justify;">
                20.8. A petição de impugnação apresentada por empresa deve ser firmada por
                sócio, pessoa designada para a administração da sociedade empresária, ou
                procurador, e vir acompanhada, conforme o caso, de estatuto ou contrato social e
                suas posteriores alterações, se houver, do ato de designação do administrador, ou
                de procuração pública ou particular (instrumento de mandato com poderes para
                impugnar o Edital).
            </p>

            <p style="font-weight: bold;"> 21. DAS DISPOSIÇÕES GERAIS.</p>

            <p style="text-align: justify;">
                21.1. Da sessão pública da Concorrência divulgar-se-á Ata no sistema eletrônico.
            </p>
            <p style="text-align: justify;">
                21.2. Não havendo expediente ou ocorrendo qualquer fato superveniente que
                impeça a realização do certame na data marcada, a sessão será automaticamente
                transferida para o primeiro dia útil subsequente, no mesmo horário anteriormente
                estabelecido, desde que não haja comunicação em contrário, pelo Agente de
                Contratação.
            </p>
            <p style="text-align: justify;">
                21.3. Todas as referências de tempo no Edital, no aviso e durante a sessão pública
                observarão o horário de Brasília – DF.
            </p>
            <p style="text-align: justify;">
                21.4. No julgamento das propostas e da habilitação, o Agente de Contratação
                poderá sanar erros ou falhas que não alterem a substância das propostas, dos
                documentos e sua validade jurídica, mediante despacho fundamentado, registrado
                em ata e acessível a todos, atribuindo-lhes validade e eficácia para fins de
                habilitação e classificação.
            </p>
            <p style="text-align: justify;">
                21.5. A homologação do resultado desta licitação não implicará direito à
                contratação.
            </p>
            <p style="text-align: justify;">
                21.6. As normas disciplinadoras da licitação serão sempre interpretadas em favor
                da ampliação da disputa entre os interessados, desde que não comprometam o
                interesse da Administração, o princípio da isonomia, a finalidade e a segurança da
                contratação.
            </p>
            <p style="text-align: justify;">
                21.7. Os licitantes assumem todos os custos de preparação e apresentação de
                suas propostas e a Administração não será, em nenhum caso, responsável por
                esses custos, independentemente da condução ou do resultado do processo
                licitatório.
            </p>
            <p style="text-align: justify;">
                21.8. Na contagem dos prazos estabelecidos neste Edital e seus Anexos, excluir
                se-á o dia do início e incluir-se-á o do vencimento. Só se iniciam e vencem os prazos
                em dias de expediente na Administração.
            </p>
            <p style="text-align: justify;">
                21.9. O desatendimento de exigências formais não essenciais não importará o
                afastamento do licitante, desde que seja possível o aproveitamento do ato,
                observados os princípios da isonomia e do interesse público.
            </p>
            <p style="text-align: justify;">
                21.10. O licitante é o responsável pela fidelidade e legitimidade das informações
                prestadas e dos documentos apresentados em qualquer fase da licitação.
            </p>
            <p style="text-align: justify;">
                21.10.1. A falsidade de qualquer documento apresentado ou a inverdade das
                informações nele contidas implicará a imediata desclassificação do proponente
                que o tiver apresentado, ou, caso tenha sido o vencedor, a rescisão do contrato ou
                do documento equivalente, sem prejuízo das demais sanções cabíveis.
            </p>
            <p style="text-align: justify;">
                21.11.  Em caso de divergência entre disposições deste Edital e de seus anexos ou
                demais peças que compõem o processo, prevalecerá as deste Edital.
            </p>
            <p style="text-align: justify;">
                21.12.  A Prefeitura Municipal, poderá revogar este Concorrência por razões de
                interesse público decorrente de fato superveniente que constitua óbice manifesto
                e incontornável, ou anulá-lo por ilegalidade, de ofício ou por provocação de
                terceiros, salvo quando for viável a convalidação do ato ou do procedimento
                viciado, desde que observados os princípios da ampla defesa e contraditório.
            </p>
            <p style="text-align: justify;">
                21.12.1. A anulação da Concorrência induz à do contrato.
            </p>
            <p style="text-align: justify;">
                21.12.2. A anulação da licitação por motivo de ilegalidade não gera obrigação de
                indenizar.
            </p>
            <p style="text-align: justify;">
                21.13.  É facultado à Autoridade Superior, em qualquer fase deste Concorrência,
                promover diligência destinada a esclarecer ou completar a instrução do processo,
                vedada a inclusão posterior de informação ou de documentos que deveriam ter
                sido apresentados para fins de classificação e habilitação.
            </p>
            <p style="text-align: justify;">
                21.14.  O Edital está disponibilizado, na íntegra, no endereço eletrônico
                www.comprasbr.com.br, e também poderão ser lidos e/ou obtidos no endereço
                {{ $processo->prefeitura->endereco }}, no horário de 07:30h às 13:00h, no mesmo endereço e período
                em que os autos do processo administrativo permanecerão com acesso e vista
                franqueada aos interessados.
            </p>
            <p style="text-align: justify;">
                21.15. Integram este Edital, para todos os fins e efeitos, os seguintes anexos:
            </p>
            <p style="text-align: justify;">
                ANEXO I – MINUTA DO CONTRATO
                <br>
                ANEXO II – PROJETO BÁSICO
            </p>
        </div>

        <div>
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

            <div style="text-align: center;">
                <div class="signature-block" style="display: inline-block; margin-left: 40px; margin-right: 40px;">
                    ___________________________________<br>
                    <p style="line-height: 1.2;">
                        {{ $primeiroAssinante['responsavel'] }} <br>
                        <span>{{ \App\Helpers\Assinatura::cargo($primeiroAssinante) }}</span>
                    </p>
                </div>
            </div>
            @else
            {{-- Bloco Padrão (Fallback) --}}
            <div class="signature-block">
                ___________________________________<br>
                <p style="line-height: 1.2;">
                    {{ $processo->prefeitura->autoridade_competente }} <br>
                    <span style="color: red;">[Pregoeira/Agente de Contratação]</span>
                </p>
            </div>
            @endif
        </div>
    </div>
