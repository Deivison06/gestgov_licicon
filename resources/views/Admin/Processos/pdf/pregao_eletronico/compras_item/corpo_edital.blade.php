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
                            MENOR PREÇO POR ITEM
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid black; padding: 5px; font-weight: bold;">
                            FORMA DE ADJUDICAÇÃO
                        </td>
                        <td style="border: 1px solid black; padding: 5px;">
                            POR ITEM
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
            <p style="text-align: justify;">
                A {{ $processo->prefeitura->nome }}, sediado(a) {{ $processo->prefeitura->endereco }}, inscrita no CNPJ: {{ $processo->prefeitura->cnpj }}, torna público por meio do(a) seu
                Pregoeiro, realizará licitação, na modalidade PREGÃO, na forma ELETRÔNICA, nos termos da Lei nº 14.133, de 2021,
                Decretos Municipais e demais legislação aplicável e, ainda, de acordo com as condições estabelecidas neste Edital.
            </p>
            <p style="text-align: justify;">
                O início da Sessão de disputa de preços será realizado no dia {{ ($embutidoMinuta ?? false) ? 'XXXXXXXXXXXXXX' : $detalhe->data_hora_fase_edital->translatedFormat('d \d\e F \d\e Y') }}, às {{ ($embutidoMinuta ?? false) ? 'XX:XX' : $detalhe->data_hora_fase_edital->format('H:i') }}hs, por meio de sessão virtual, com
                inserção e comunicação via plataforma digital já especificada neste instrumento de convocação.
            </p>
            <p style="text-align: justify;">
                O Pregão Eletrônico será realizado em sessão pública, por meio da INTERNET, mediante condições de segurança -
                criptografia e autenticação - em todas as suas fases. Os trabalhos serão conduzidos por servidor da PREFEITURA
                MUNICIPAL, denominado Pregoeiro.
            </p>
            <p style="text-align: justify;">
                Também fica registrado neste instrumento de convocação, que as empresas licitantes terão até o dia {{ ($embutidoMinuta ?? false) ? 'XXXXXXXXXXXXXX' : $detalhe->data_hora_limite_edital->translatedFormat('d \d\e F \d\e Y') }}, às {{ ($embutidoMinuta ?? false) ? 'XX:XX' : $detalhe->data_hora_limite_edital->format('H:i') }}min, para finalizar o envio de suas propostas com as devidas exigências do edital e documentos de habilitação
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
                1.2. A licitação será dividida em itens, conforme tabela constante do Termo de Referência,
                facultando-se ao licitante a participação em quantos itens forem de seu interesse, conforme
                justificativa abaixo:
            </p>
            <ol type="a">
                <li style="margin-bottom: 6px; text-align: justify;">
                    O fracionamento do objeto da licitação em itens encontra amparo legal no art. 40, § 1º da
                    Lei nº 14.133/2021, que incentiva o parcelamento sempre que viável, desde que não
                    comprometa a execução do objeto. A medida visa permitir a ampla participação de
                    fornecedores, principalmente de pequeno porte, bem como alcançar melhor resultado
                    para a Administração.
                </li>
                <li style="margin-bottom: 6px; text-align: justify;">
                    O objeto da presente licitação abrange diversos produtos/serviços com características
                    distintas, que podem ser adquiridos, entregues ou executados de forma independente, sem
                    prejuízo à integridade da execução contratual.
                </li>
                <li style="margin-bottom: 6px; text-align: justify;">
                    A divisão por itens não compromete a obtenção de preços vantajosos, e ao contrário,
                    estimula a competitividade, ao permitir que microempresas, empresas locais e
                    fornecedores especializados possam concorrer apenas nos itens de sua capacidade
                    técnica e logística.
                </li>
                <li style="margin-bottom: 6px; text-align: justify;">
                    Com isso, evita-se a concentração do fornecimento em um único fornecedor, promovendo
                    maior eficiência, economicidade e mitigação de riscos contratuais.
                </li>
                <li style="margin-bottom: 6px; text-align: justify;">
                    A adoção do parcelamento por itens está alinhada ao planejamento da Administração Pública, favorecendo:
                    <ul style="margin-top: 5px; margin-bottom: 5px;">
                        <li style="margin-bottom: 5px; text-align: justify;">Atendimento adequado às necessidades específicas de cada unidade administrativa;</li>
                        <li style="margin-bottom: 5px; text-align: justify;">Diversificação de fornecedores e redução do risco de desabastecimento;</li>
                        <li style="margin-bottom: 5px; text-align: justify;">Fortalecimento da economia local/regional; </li>
                        <li style="margin-bottom: 5px; text-align: justify;">Observância ao princípio da isonomia, conforme art. 5º da Lei nº 14.133/2021.</li>
                    </ul>
                </li>
                <li style="text-align: justify;">
                    Além disso, o parcelamento da contratação em itens favorece uma competição saudável
                    entre fornecedores, o que pode resultar em custos mais baixos e condições mais
                    vantajosas para a Administração Pública. Ao permitir que empresas ofereçam suas
                    propostas por ITEM, a Prefeitura pode beneficiar-se da especialização dos
                    fornecedores, garantindo aquisições de melhor qualidade. Essa dinâmica
                    também contribui para minimizar riscos, uma vez que cada item pode ser ajustado
                    conforme a resposta do mercado e as demandas emergentes facilitando adaptações ao
                    longo do fornecimento.
                </li>
            </ol>

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
                    Fase de Habilitação: a fase de habilitação antecede a fase de lances. Nesta fase, o Pregoeiro analisará os documentos de habilitação de todos os licitantes que tiverem suas propostas iniciais em conformidade com o edital, para efeito de habilitação e prosseguimento à fase seguinte, sendo que somente os licitantes habilitados participarão da fase de lances;
                </li>

                <li style="margin-bottom: 6px;">
                    Fase Recursal: encerrada a fase de julgamento das propostas, o Pregoeiro abrirá o prazo de 30 (trinta) minutos para que qualquer licitante manifeste, de forma imediata e motivada, em campo próprio do sistema, vedada a manifestação via “chat”, a intenção de recorrer contra os atos de habilitação ou inabilitação e de julgamento. As razões do recurso serão apresentadas no prazo de 3 (três) dias úteis, na forma do item 7 deste Edital, em fase única;
                </li>

                <li style="margin-bottom: 6px;">
                    Fase de Adjudicação e Homologação: exauridos os recursos, o processo será encaminhado à autoridade superior para adjudicação do objeto e homologação da licitação (art. 71, IV, da Lei nº 14.133/2021).
                </li>
            </ol>
            @else
            <ol type="a" style="text-align: justify; padding-left: 20px;">
                <li style="margin-bottom: 6px;">
                    Fase de inserção da proposta: no período de divulgação do certame até a
                    data e hora limite para envio das propostas, prazo improrrogável, os
                    licitantes inserirão no sistema o valor de sua proposta, o arquivo de ficha
                    técnica e o comprovante da garantia de proposta, quando exigida. O valor
                    proposto não poderá ser superior ao valor estimado pelo Edital, sob pena de
                    desclassificação;
                </li>

                <li style="margin-bottom: 6px;">
                    Fase de lances: os licitantes cujas propostas forem aceitas na fase anterior
                    ofertarão lances sucessivos, pelo critério de menor preço, no tempo de 10
                    (dez) minutos, com as respectivas prorrogações de 2 (dois) minutos,
                    controladas pelo sistema eletrônico do portal designado para a realização da
                    sessão;
                </li>

                <li style="margin-bottom: 6px;">
                    Fase de julgamento: encerrados os lances, o Pregoeiro analisará a
                    aceitabilidade da proposta mais bem classificada, inclusive quanto à
                    exequibilidade, podendo negociar condições mais vantajosas (arts. 59 e 61
                    da Lei nº 14.133/2021);
                </li>

                <li style="margin-bottom: 6px;">
                    Fase de habilitação: o licitante cuja proposta for aceita será convocado a
                    apresentar os documentos de habilitação, na forma do item 6;
                </li>

                <li style="margin-bottom: 6px;">
                    Fase recursal: declarado o vencedor, o Pregoeiro abrirá o prazo de 30
                    (trinta) minutos para que qualquer licitante manifeste, imediata e
                    motivadamente, em campo próprio do sistema, vedada a manifestação via
                    “chat”, a intenção de recorrer. As razões serão apresentadas em 3 (três)
                    dias úteis, na forma do item 7;
                </li>

                <li style="margin-bottom: 6px;">
                    Fase de adjudicação e homologação: exauridos os recursos, o processo
                    será encaminhado à autoridade superior para adjudicação do objeto e
                    homologação da licitação (art. 71, IV).
                </li>
            </ol>
            @endif
            <p style="text-align: justify;">
                1.4. Nenhum licitante passará para a fase seguinte, sem o devido cumprimento das
                exigências contidas em cada fase, sob pena de desclassificação ou inabilitação.
            </p>
            <p style="text-align: justify;">
                1.5. Na fase de lances, cada empresa licitante poderá inserir quantos lances forem
                necessários, ficando resguardado apenas os critérios de inexequibilidade de
                proposta, que serão verificados na fase de julgamento das propostas.
            </p>
            <p style="text-align: justify;">
                1.6. Na fase recursal, após o inicial da contagem do tempo de 30 (trinta) minutos,
                será aberto campo específico para que as manifestações dos licitantes sejam
                devidamente registradas e reconhecidas pelo Sistema do portal designado para a
                realização da sessão, não sendo aceitas, em nenhuma hipótese, manifestações
                recursais inseridas dentro do campo de “chat”.
            </p>
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
                2.4. A não observância do disposto no item anterior poderá ensejar a inabilitação do licitante.
            </p>
            <p style="text-align: justify;">
                2.5. Será concedido tratamento favorecido para as microempresas e empresas de pequeno porte, para as sociedades
                cooperativas mencionadas no artigo 16 da Lei nº 14.133, de 2021, para o agricultor familiar, o produtor rural pessoa física e
                para o microempreendedor individual - MEI, nos limites previstos da Lei Complementar nº 123, de 2006.
            </p>
            <p style="text-align: justify;">
                2.6. A obtenção do tratamento favorecido a que se refere o item 2.5 fica limitada às microempresas e às empresas de
                pequeno porte que, no ano-calendário de realização da licitação, ainda não tenham celebrado contratos com a
                Administração Pública cujos valores somados extrapolem a receita bruta máxima admitida para fins de enquadramento
                como empresa de pequeno porte (art. 4º, §2º, da Lei nº 14.133/2021).
            </p>

            @if($detalhe->participacao_exclusiva_mei_epp === 'nao')
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
                    empresa de pequeno porte ou cooperativa.
                    @if ($detalhe->inversao_fase === 'sim')
                    Nesse caso, o Pregoeiro prosseguirá com o julgamento da proposta da arrematante, cujos documentos de habilitação já
                    terão sido analisados na fase própria.
                    @else
                    Nesse caso, o pregoeiro convocará a arrematante a apresentar os documentos de habilitação.
                    @endif
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
                    ofertado na sessão de disputa será considerada arrematante pelo pregoeiro, que encerrará a disputa do item na sala virtual.
                    @if ($detalhe->inversao_fase === 'sim')
                    Devendo a arrematante apresentar a proposta de preços readequada, nos termos do item 5.28.
                    @else
                    Devendo a arrematante apresentar a documentação de habilitação e da proposta de preços.
                    @endif
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
                    O julgamento da habilitação das microempresas, empresas de pequeno porte e cooperativas obedecerá aos
                    critérios gerais definidos neste edital, observadas as particularidades de cada pessoa jurídica.
                </li>
                <li style="margin-bottom: 6px;">
                    Havendo alguma restrição na comprovação da regularidade fiscal e trabalhista, será assegurado às
                    microempresas, empresas de pequeno porte e cooperativas o prazo de 5 (cinco) dias úteis, cujo termo inicial
                    corresponderá ao momento em que o proponente for declarado vencedor do certame, prorrogável por igual
                    período, a critério da Administração, mediante requerimento apresentado antes do término do prazo inicial,
                    para a regularização da documentação (art. 43, §1º, da LC nº 123/2006).
                    @if ($detalhe->inversao_fase === 'sim')
                    Considerando a inversão de fases adotada neste Edital, a microempresa, empresa de pequeno porte ou
                    cooperativa que apresentar restrição na regularidade fiscal e trabalhista não será inabilitada por esse motivo
                    na fase de habilitação, participando da fase de lances, e o prazo de regularização somente será aberto se
                    ela for declarada vencedora.
                    @endif
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
                4.7. Ao encaminhar a proposta de preços na forma prevista pelo sistema eletrônico, a licitante deverá preencher as informações no campo “CADASTRO PROPOSTA” e anexar FICHA TÉCNICA em arquivo PDF no campo apropriado do sistema da Bolsa Nacional de Compras - {{ $detalhe->portal }}, sendo vedada a identificação do licitante por qualquer meio.
            </p>
            <p style="text-align: justify;">
                4.8. Na ficha técnica de preços não deve conter identificação do licitante como: nome, razão social ou timbre do proponente, endereço, telefone, fax e endereço de correio eletrônico, nome do representante, carteira de identidade e cargo na empresa ou qualquer outra forma que possa identificar a proposta.
            </p>
            <p style="text-align: justify;">
                4.9. Deve conter o detalhamento dos produtos ofertados, indicando, marca,
                modelo, fabricante, prazo de garantia ou validade, prazo de entrega e
                acondicionamento.
            </p>
            <p style="text-align: justify;">
                4.10. Preço unitário do item, cotando-se cada produto discriminado no item, em
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
                que cobrir a melhor oferta deverá ser de R$ 10,00 (dez reais) do valor total do ITEM.
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
                5.20. É facultado ao pregoeiro prorrogar o prazo estabelecido, a partir de solicitação fundamentada feita no chat pelo
                licitante, antes de findo o prazo.
            </p>
            <p style="text-align: justify;">
                5.21. Na hipótese da proposta de menor valor desatender às exigências da proposta de preços o Pregoeiro examinará a
                proposta subsequente, verificando a sua aceitabilidade e procedendo na ordem de classificação, segundo o critério do
                Menor Preço e assim sucessivamente até a apuração de uma proposta que atenda ao edital.
            </p>
            <p style="text-align: justify;">
                5.22. Considera-se indício de inexequibilidade a proposta cujo valor final seja inferior a 50% (cinquenta por
                cento) do valor orçado pela Administração. Nessa hipótese, antes de qualquer decisão, o Pregoeiro realizará
                diligência para que o licitante demonstre a exequibilidade de sua proposta, podendo apresentar planilha de
                composição de custos, notas fiscais, contratos ou outros documentos que comprovem que o seu custo não
                ultrapassa o valor ofertado (art. 59, §2º, da Lei nº 14.133/2021).
            </p>
            <p style="text-align: justify;">
                5.23. Caso o Termo de Referência exija a apresentação de amostra, o licitante classificado em primeiro lugar deverá
                apresentá-la, conforme disciplinado no Termo de Referência, sob pena de não aceitação da proposta.
            </p>
            <p style="text-align: justify;">
                5.24. Por meio de mensagem no sistema, será divulgado o local e horário de realização do procedimento para a avaliação
                das amostras, cuja presença será facultada a todos os interessados, incluindo os demais licitantes.
            </p>
            <p style="text-align: justify;">
                5.25. Os resultados das avaliações serão divulgados por meio de mensagem no sistema.
            </p>
            <p style="text-align: justify;">
                5.26. No caso de não haver entrega da amostra ou ocorrer atraso na entrega, sem justificativa aceita pelo Pregoeiro, ou
                havendo entrega de amostra fora das especificações previstas neste Edital, a proposta do licitante será recusada.
            </p>
            <p style="text-align: justify;">
                5.27. Se a(s) amostra(s) apresentada(s) pelo primeiro classificado não for(em) aceita(s), o Pregoeiro analisará a
                aceitabilidade da proposta ou lance ofertado pelo segundo classificado. Seguir-se-á com a verificação da(s) amostra(s) e,
                assim, sucessivamente, até a verificação de uma que atenda às especificações constantes no Termo de Referência.
            </p>
            <p style="text-align: justify;">
                5.28. O pregoeiro solicitará ao licitante mais bem classificado que, no prazo de 2
                (duas) horas, envie a proposta adequada ao último lance ofertado após a
                negociação realizada, acompanhada, se for o caso, dos documentos
                complementares, quando necessários à confirmação daqueles exigidos neste
                Edital e já apresentados, com as seguintes informações:
                <ul type="a">
                    <li>valor do item;  </li>
                    <li>Marca;  </li>
                    <li>
                        Descrição do objeto, contendo as informações similares à especificação
                        do Termo de Referência;
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
                5.29. Será desclassificada a proposta vencedora que:
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
                5.30. Na elaboração da proposta adequada ao seu lance final, os licitantes deverão
                obrigatoriamente apresentar proposta readequada com percentual de desconto
                fixo e linear sobre os preços de todos os ITENS/LOTES. A exigência busca evitar o a
                manipulação e jogo de planilhas, além disso, busca garantir a isonomia entre os
                licitantes.
            </p>
            <p style="text-align: justify;">
                5.31. A licitante deverá, dentro do prazo estipulado para envio da Proposta
                Readequada, realizar a redefinição dos valores unitários junto a plataforma onde
                será realizada a licitação, seguindo o exigido no ITEM 5.30, sob pena de desclassificação da proposta.
            </p>
            <p style="text-align: justify;">
                5.32. A diferença entre o valor final proposto e o valor estimado pela Administração será
                considerada como coeficiente percentual de desconto, a ser aplicado de forma uniforme sobre
                todos os itens constantes no Termo de Referência e na Proposta Readequada apresentada pela licitante.
                Tal coeficiente permanecerá vigente durante toda a execução contratual, aplicando-se também aos
                acréscimos quantitativos dos itens registrados, nos limites do art. 125 da Lei nº 14.133/2021.
            </p>
        </div>
        <div>
            <p style="display: flex; align-items: center; font-weight: bold; ">
                <img src="{{ public_path('icons/check.png') }}" width="20" style="margin-right: 10px;"> 6. DA FASE DE HABILITAÇÃO
            </p>
            <p style="text-align: justify;">
                @if (isset($detalhe) && $detalhe->inversao_fase === 'sim')
                    6.1. Por se tratar de um certame com Inversão de Fases, todos os licitantes devem anexar os seguintes documentos no momento do cadastro de suas propostas:
                @else
                    6.1. Iniciando a fase de habilitação o Pregoeiro irá convocar o licitante classificado em primeiro lugar para apresentação em
                    um prazo de 02 (duas) horas os seguintes documentos:
                @endif
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
                    Balanço patrimonial, demonstração de resultado de exercício e demais demonstrações contábeis dos 2 (dois)
                    últimos exercícios sociais, já exigíveis na forma da lei, vedada a substituição por balancetes ou balanços
                    provisórios (art. 69, I, da Lei nº 14.133/2021). Admite-se a apresentação de balanço de abertura para as
                    empresas com menos de 1 (um) exercício financeiro.
                </li>
                <li style="margin-bottom: 6px;">
                    A boa situação financeira será aferida pelos índices de Liquidez Geral (LG), Solvência Geral (SG) e Liquidez
                    Corrente (LC), que deverão ser superiores a 1 (um), calculados da seguinte forma: LG = (Ativo Circulante +
                    Realizável a Longo Prazo) ÷ (Passivo Circulante + Passivo Não Circulante); SG = Ativo Total ÷ (Passivo
                    Circulante + Passivo Não Circulante); LC = Ativo Circulante ÷ Passivo Circulante.
                </li>
                <li style="margin-bottom: 6px;">
                    Declaração de Capacidade financeira, obrigatoriamente em papel timbrado da empresa, apresentando as
                    demonstrações contábeis do último exercício social, devidamente assinada pelo Representante Legal da Empresa e pelo
                    Contador responsável;
                </li>
                {!! preg_replace('/<\/?ul[^>]*>/', '', $detalhe->qualificacao_economica) !!}
            </ol>
            @if ($detalhe->exige_atestado == 'sim')
                <p style="text-align: justify; font-weight: bold;">
                    6.7 Qualificação Técnica:
                </p>
                <ol type="a" style="text-align: justify;">
                    <li style="margin-bottom: 6px;">
                        Apresentar comprovante de que a licitante forneceu, sem restrição, produtos semelhantes ao objeto do presente
                        Edital, através da apresentação de 01 (um) ou mais Atestados de Capacidade Técnica, fornecido por pessoa jurídica de
                        direito público ou privado, devidamente datado e assinado por responsável da área, com nome legível.
                    </li>
                    {!! preg_replace('/<\/?ul[^>]*>/', '', $detalhe->exigencias_tecnicas) !!}
                </ol>
            @endif

            <p style="text-align: justify;">
                @if ($detalhe->exige_atestado == 'sim')6.8. @else 6.7. @endif
                @if ($detalhe->inversao_fase === 'sim')
                    Concluída a análise, o Pregoeiro registrará no sistema o resultado da habilitação, com a motivação das
                    inabilitações. Somente os licitantes habilitados participarão da fase de lances. A intenção de recorrer
                    contra a habilitação ou inabilitação será manifestada na fase recursal única, após o julgamento das
                    propostas (art. 165, §1º, da Lei nº 14.133/2021). Acolhido recurso contra inabilitação, serão invalidados
                    os atos insuscetíveis de aproveitamento, repetindo-se a fase de lances, se necessário. O licitante que
                    não enviar os documentos de habilitação até a data e hora limite para envio das propostas será
                    inabilitado, sem prejuízo das sanções previstas neste Edital.
                @else
                    O Pregoeiro fará a análise dos documentos de habilitação do licitante, será aberto o prazo para
                    manifestação da intenção de interposição de recurso. O não cumprimento do envio dos documentos de
                    habilitação dentro dos prazos estabelecidos, acarretará a desclassificação e/ou inabilitação da
                    licitante, bem como as sanções previstas neste Edital, podendo o Pregoeiro convocar a empresa que
                    apresentou a proposta ou o lance subsequente.
                @endif
            </p>
            <p style="text-align: justify;">
                @if ($detalhe->exige_atestado == 'sim')6.9. @else 6.8. @endif Os documentos eletrônicos produzidos com a utilização de processo de certificação disponibilizada pela ICP-Brasil, nos
                termos da Medida Provisória nº 2200-2, de 24 de agosto de 2001, serão recebidos e presumir-se-ão verdadeiros em relação
                aos signatários, dispensando-se o envio de documentos originais e cópias autenticadas em papel.
            </p>
            <p style="text-align: justify;">
                @if ($detalhe->exige_atestado == 'sim')6.10. @else 6.9. @endif O Pregoeiro reserva-se o direito de solicitar da licitante, em qualquer tempo, no curso da licitação, quaisquer
                esclarecimentos sobre documentos já entregues, fixando-lhe prazo para atendimento.
            </p>
            <p style="text-align: justify;">
                @if ($detalhe->exige_atestado == 'sim')6.11. @else 6.10. @endif O licitante deverá apresentar, juntamente com os documentos de habilitação, as seguintes declarações:
            </p>
            <ol type="a" style="text-align: justify;">
                <li style="margin-bottom: 6px;">de que atende aos requisitos de habilitação (art. 63, I, da Lei nº 14.133/2021);</li>
                <li style="margin-bottom: 6px;">de que cumpre as exigências de reserva de cargos para pessoa com deficiência e para reabilitado da Previdência Social (art. 63, IV, da Lei nº 14.133/2021);</li>
                <li style="margin-bottom: 6px;">de que não emprega menor de 18 (dezoito) anos em trabalho noturno, perigoso ou insalubre e não emprega menor de 16 (dezesseis) anos, salvo na condição de aprendiz, a partir de 14 (quatorze) anos (art. 68, VI, da Lei nº 14.133/2021, c/c art. 7º, XXXIII, da Constituição Federal);</li>
                <li style="margin-bottom: 6px;">de inexistência de fato impeditivo à participação na licitação (art. 14 da Lei nº 14.133/2021);</li>
                <li style="margin-bottom: 6px;">de enquadramento como microempresa ou empresa de pequeno porte, quando for o caso.</li>
            </ol>
        </div>
        <div>
            <p style="display: flex; align-items: center; font-weight: bold; ">
                <img src="{{ public_path('icons/check.png') }}" width="20" style="margin-right: 10px;"> 7. - DOS RECURSOS
            </p>
            <p style="text-align: justify;">
                @if ($detalhe->inversao_fase === 'sim')
                    7.1. Caberá recurso nos casos previstos no art. 165 da Lei nº 14.133/2021, devendo o licitante manifestar,
                    imediata e motivadamente, sua intenção de recorrer, em campo próprio do sistema, no prazo de 30 (trinta)
                    minutos aberto pelo Pregoeiro após o encerramento da fase de julgamento das propostas, em fase única
                    que abrangerá os atos de habilitação ou inabilitação e de julgamento.
                @else
                    7.1. Caberá recurso nos casos previstos no art. 165 da Lei nº 14.133/2021, devendo o licitante manifestar,
                    imediata e motivadamente, sua intenção de recorrer, em campo próprio do sistema, no prazo de 30 (trinta)
                    minutos aberto pelo Pregoeiro após a declaração do vencedor, encerrada a fase de habilitação.
                @endif
            </p>
            <p style="text-align: justify;">
                7.2. A Intenção motivada de recorrer é aquela que identifica, objetivamente, os fatos e o direito que o licitante pretende que
                sejam revistos pelo Pregoeiro.
            </p>
            <p style="text-align: justify;">
                7.3. O licitante que tiver manifestado a intenção de recorrer disporá do prazo de 3 (três) dias úteis para apresentar
                as razões do recurso, por meio de formulário específico do sistema. Os demais licitantes poderão apresentar
                contrarrazões em igual prazo, contado da data de divulgação da interposição do recurso (art. 165, I, e §4º,
                da Lei nº 14.133/2021).
            </p>
            <p style="text-align: justify;">
                7.4. Na hipótese de concessão de prazo para a regularização fiscal de microempresa ou empresa de pequeno porte que
                tenha apresentado melhor proposta, a etapa recursal será aberta por ocasião da retomada da sessão pública do pregão.
            </p>
            <p style="text-align: justify;">
                7.5. A falta de manifestação imediata do licitante importará a preclusão do direito de recorrer, e o processo será
                encaminhado à autoridade superior para adjudicação do objeto e homologação da licitação (art. 71, IV, da
                Lei nº 14.133/2021).
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
                @if ($detalhe->inversao_fase === 'sim')
                    7.10. O prazo para apresentação das razões recursais será iniciado na data de intimação ou de lavratura
                    da ata de julgamento, tendo em vista a adoção da inversão de fases (art. 165, §1º, I, parte final, da Lei
                    nº 14.133/2021).
                @else
                    7.10. O prazo para apresentação das razões recursais será iniciado na data de intimação ou de lavratura
                    da ata de habilitação ou inabilitação.
                @endif
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
                devendo protocolar o pedido até 3 (três) dias úteis antes da data da abertura do certame.
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
                10.6. Será incluído na ata, sob a forma de anexo, o registro dos licitantes que aceitarem cotar os bens ou serviços com
                preços iguais aos do licitante vencedor, na sequência da classificação do certame, assegurada a preferência de
                contratação de acordo com a ordem de classificação (art. 82, VII, da Lei nº 14.133/2021).
            </p>
            <p style="text-align: justify;">
                10.7. Caberá ao fornecedor beneficiário da Ata de Registro de Preços, observadas as condições nela estabelecidas, optar
                pela aceitação ou não do fornecimento decorrente de adesão. Os órgãos e entidades não participantes poderão
                aderir à Ata, desde que atendidos os requisitos do art. 86, §2º, da Lei nº 14.133/2021: justificativa da vantagem
                da adesão, demonstração de que os valores registrados são compatíveis com os de mercado e consulta e
                aceitação prévias do órgão gerenciador e do fornecedor.
            </p>
            <p style="text-align: justify;">
                10.8. Caberá aos fornecedores beneficiários da Ata de Registro de Preços, observadas as condições nela estabelecidas, optar
                pela aceitação ou não do fornecimento aos órgãos não participantes que solicitem adesão à Ata de Registro de Preços acima
                do quantitativo previsto, desde que este fornecimento não prejudique as obrigações anteriormente assumidas;
            </p>
            <p style="text-align: justify;">
                10.9. As solicitações de adesão, concessão de anuência pelo fornecedor e autorização do órgão gerenciador serão realizadas
                por meio de formalização de processo administrativo com as documentações necessárias, cuja responsabilidade é do órgão
                gerenciador.
            </p>
            <p style="text-align: justify;">
                10.10. O quantitativo decorrente das adesões à Ata de Registro de Preços não poderá exceder, na totalidade, ao dobro do
                quantitativo de cada item registrado na Ata de Registro de Preços para o órgão gerenciador e órgão participantes,
                independentemente do número de órgãos não participantes que aderirem.
            </p>
            <p style="text-align: justify;">
                10.11. Após a aceitação à adesão da Ata de Registro de Preços pelo órgão gerenciador, o Órgão denominado Carona deverá
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
                10.12. A previsão de aquisição ou contratação pelo Órgão Carona deverá ser de até
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

        <div style="text-align: center;">
            <div class="signature-block" style="display: inline-block; margin-left: 40px; margin-right: 40px;">
                ___________________________________<br>
                <p style="line-height: 1.2;">
                    {{ $primeiroAssinante['responsavel'] }} <br>
                    <span>{{ $primeiroAssinante['unidade_nome'] }}</span>
                </p>
            </div>
        </div>
        @else
        {{-- Bloco Padrão (Fallback) --}}
        <div class="signature-block">
            ___________________________________<br>
            <p style="line-height: 1.2;">
                {{ $processo->prefeitura->autoridade_competente }} <br>
                {!! $cargoFallback ?? 'Prefeito Municipal' !!}
            </p>
        </div>
        @endif
    </div>
