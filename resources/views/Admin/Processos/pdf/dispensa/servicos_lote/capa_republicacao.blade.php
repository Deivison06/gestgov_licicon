<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Avisos - Processo {{ $processo->numero_processo ?? $processo->id }}</title>
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
            text-align: center;
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

    </style>
</head>

<body>
<div>
    <h4 style="text-align: center;">
        AVISO DE REPUBLICAÇÃO DE EDITAL
    </h4>
    <p style="text-align: justify; text-indent: 30px">
        A Prefeitura Municipal de
        <span style="font-weight: bold">
            {{ $processo->prefeitura->cidade }}
        </span>, por meio de
        <span style="font-weight: bold">seu Agente de Contratação</span>,
        torna público, para conhecimento dos interessados, a
        <span style="font-weight: bold">
            REPUBLICAÇÃO do Edital referente ao
            @if ($processo->modalidade !== \App\Enums\ModalidadeEnum::DISPENSA)
                {{ $processo->modalidade->getDisplayName() }}
            @else
                DISPENSA DE LICITAÇÃO
            @endif
            nº {{ $processo->numero_procedimento }},
        </span>
        cujo objeto é {!! strip_tags($processo->objeto) !!}.
        Em virtude das alterações promovidas, fica reaberto o prazo para apresentação
        das propostas/documentos, nos termos do edital republicado.
        @php
    $dataFormatada = \Carbon\Carbon::parse($dataSelecionada)->translatedFormat('d \d\e F \d\e Y');
@endphp
{{ $processo->prefeitura->cidade }}, {{ $dataFormatada }}.
    </p>

    <h6 style="text-align: center">{{ $detalhe->agente_contratacao ?? $detalhe->pregoeiro ?? '____________________' }}</h6>

    {{-- QUEBRA DE PÁGINA --}}
    <div class="page-break"></div>

    @include('Admin.Processos.pdf.capa_edital')

    {{-- QUEBRA DE PÁGINA --}}
    <div class="page-break"></div>

    <div>
        <div>
            <p style="text-align: justify;">
                A {{ $processo->prefeitura->nome }}, inscrita no CNPJ: {{ $processo->prefeitura->cnpj }}, torna público realizará
                Dispensa de Licitação, com critério de julgamento menor preço na hipótese do {{ optional($detalhe ?? null)->is_oriundo_fracassado ? 'Art. 75, Inciso III, alínea "a", da Lei Federal nº 14.133/2021' : 'art. 75, inciso II, nos termos da Lei n.º 14.133, de 1º de abril de 2021' }}.
                As empresas interessadas a enviarem suas propostas de preços e todos os documentos
                de habilitação para o objeto constante do Termo de Referência e conforme modelo de
                proposta até o dia {{ $detalhe->data_hora_limite_edital->translatedFormat('d \d\e F \d\e Y') }}, às {{ $detalhe->data_hora_limite_edital->format('H:i') }} horas, para o e-mail:
                {{ $processo->prefeitura->email }}, ou entregar pessoalmente na Sala de
                Licitação do município de {{ $processo->prefeitura->cidade }}, na {{ $processo->prefeitura->endereco }}
            </p>

            <p style="display: flex; align-items: center; font-weight: bold; ">
                <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 1 - OBJETO DA CONTRATAÇÃO DIRETA
            </p>

            <p style="text-align: justify;">
                1.1. O objeto da presente dispensa é a escolha da proposta mais vantajosa para a
                contratação, por dispensa de licitação, de {!! strip_tags($processo->objeto) !!} conforme
                condições, quantidades e exigências estabelecidas neste Aviso de Contratação Direta e
                seus anexos.
            </p>
            <p style="text-align: justify;">
                1.2. A contratação ocorrerá conforme tabela abaixo.
            </p>
            <table border="1" cellspacing="0" cellpadding="4" style="border-collapse: collapse; width: 100%; text-align: center; font-size: 8pt;">
                <thead>
                <tr>
                    <th style="width: 6%;">ITEM</th>
                    <th style="width: 30%;">DESCRIÇÃO</th>
                    <th style="width: 8%;">UNID</th>
                    <th style="width: 20%;">QUANTIDADE</th>
                    <th style="width: 18%;">VALOR UNITÁRIO</th>
                    <th style="width: 18%;">VALOR TOTAL</th>
                </tr>
                </thead>
                <tbody>
                @php
                    $itens = is_array($detalhe->itens_especificaca_quantitativos_xml)
                    ? $detalhe->itens_especificaca_quantitativos_xml
                    : json_decode($detalhe->itens_especificaca_quantitativos_xml, true);
                    $itensAgrupados = collect($itens)->groupBy(fn($i) => $i['lote'] ?? 'Sem Lote');
                @endphp
                @if ($itens && count($itens) > 0)
                    @foreach ($itensAgrupados as $loteNome => $itensDoLote)
                        @if ($loteNome !== 'Sem Lote')
                            <tr style="background-color: #e9e9e9;">
                                <td colspan="6" style="text-align: left; font-weight: bold; padding-left: 10px;">{{ $loteNome }}</td>
                            </tr>
                        @endif
                        @foreach ($itensDoLote as $item)
                            <tr>
                                <td>{{ $item['item'] ?? '' }}</td>
                                <td style="text-align: left;">{{ $item['especificacoes'] ?? '' }}</td>
                                <td>{{ $item['unidade'] ?? '' }}</td>
                                <td>{{ $item['quantidade'] ?? '' }}</td>
                                <td>{{ $item['valor_unitario'] ?? '' }}</td>
                                <td>{{ $item['valor_total'] ?? '' }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                @else
                    <tr>
                        <td colspan="6">Nenhum item encontrado</td>
                    </tr>
                @endif
                </tbody>
            </table>
            <p style="text-align: justify;">
                1.3. O critério de julgamento adotado será o menor preço observadas as exigências
                contidas neste Aviso de Contratação Direta e seus Anexos quanto às especificações do
                objeto.
            </p>
        </div>
        <div>
            <p style="display: flex; align-items: center; font-weight: bold; ">
                <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 2 - PARTICIPAÇÃO NA DISPENSA
            </p>
            <p style="text-align: justify;">
                Não poderão disputar esta licitação:
            </p>
            <p style="text-align: justify;">
                a) aquele que não atenda às condições deste Edital e seu(s) anexo(s);<br><br>

                b) autor do anteprojeto, do projeto básico ou do projeto executivo, pessoa física ou
                jurídica, quando a licitação versar sobre serviços ou fornecimento de bens a ele
                relacionados; <br><br>

                c) empresa, isoladamente ou em consórcio, responsável pela elaboração do projeto
                básico ou do projeto executivo, ou empresa da qual o autor do projeto seja dirigente,
                gerente, controlador, acionista ou detentor de mais de 5% (cinco por cento) do capital
                com direito a voto, responsável técnico ou subcontratado, quando a licitação versar
                sobre serviços ou fornecimento de bens a ela necessários;<br><br>

                d) pessoa física ou jurídica que se encontre, ao tempo da licitação, impossibilitada de
                participar da licitação em decorrência de sanção que lhe foi imposta;<br><br>

                e) aquele que mantenha vínculo de natureza técnica, comercial, econômica,
                financeira, trabalhista ou civil com dirigente do órgão ou entidade contratante ou com
                agente público que desempenhe função na licitação ou atue na fiscalização ou na
                gestão do contrato, ou que deles seja cônjuge, companheiro ou parente em linha reta,
                colateral ou por afinidade, até o terceiro grau;<br><br>

                f) empresas controladoras, controladas ou coligadas, nos termos da Lei nº 6.404, de
                15 de dezembro de 1976, concorrendo entre si;<br><br>

                g) pessoa física ou jurídica que, nos 5 (cinco) anos anteriores à divulgação do edital,
                tenha sido condenada judicialmente, com trânsito em julgado, por exploração de
                trabalho infantil, por submissão de trabalhadores a condições análogas às de escravo
                ou por contratação de adolescentes nos casos vedados pela legislação trabalhista;<br><br>

                h) agente público do órgão ou entidade licitante;<br><br>

                i) pessoas jurídicas reunidas em consórcio;<br><br>

                j) Organizações da Sociedade Civil de Interesse Público - OSCIP, atuando nessa
                condição;<br><br>

                2.2. DISPENSA DE LICITAÇÃO EXCLUSIVA PARA MEs e EPPs: Atendendo o disposto na LC
                123/06, Art. 49, inciso IV, a licitação for dispensável ou inexigível, a compra deve ser feita
                preferencialmente de Microempresas e Empresas de Pequeno Porte.
            </p>
        </div>
        <div>
            <p style="display: flex; align-items: center; font-weight: bold; ">
                <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 3 - DA PROPOSTA
            </p>
            <p style="text-align: justify;">
                3.1. O ingresso do fornecedor na disputa da dispensa ocorrerá com o envio de sua
                proposta de preços para o objeto constante do Termo de Referência e conforme modelo
                de proposta até o dia {{ $detalhe->data_hora_limite_edital->translatedFormat('d \d\e F \d\e Y') }}, às {{ $detalhe->data_hora_limite_edital->format('H:i') }} horas para o e-mail:
                {{ $processo->prefeitura->email }}, ou entregar pessoalmente na Sala de
                Licitação do município de {{ $processo->prefeitura->cidade }}, na {{ $processo->prefeitura->endereco }}
                <br><br>
                3.2. Os interessados, após a divulgação do Aviso de Contratação Direta, encaminharão a
                proposta com a descrição do objeto ofertado, a marca do produto, quando for o caso, e
                o preço ou o desconto, até a data e o horário estabelecidos para abertura do
                procedimento.
                <br><br>
                3.3. Todas as especificações do objeto contidas na proposta, em especial o preço ou o
                desconto ofertado, vinculam a Contratada.<br><br>

                3.4. Nos valores propostos estarão inclusos todos os custos operacionais, encargos
                previdenciários, trabalhistas, tributários, comerciais e quaisquer outros que incidam
                direta ou indiretamente na execução do objeto;<br><br>

                3.5. A proposta deverá conter declaração de que compreende a integralidade dos custos
                para atendimento dos direitos trabalhistas assegurados na Constituição Federal, nas
                leis trabalhistas, nas normas infralegais, nas convenções coletivas de trabalho e nos
                termos de ajustamento de conduta vigentes na data de entrega das propostas.<br><br>

                3.6. Os preços ofertados, tanto na proposta inicial, quanto na etapa de lances, serão de
                exclusiva responsabilidade do fornecedor, não lhe assistindo o direito de pleitear
                qualquer alteração, sob alegação de erro, omissão ou qualquer outro pretexto.<br><br>

                3.7. Se o regime tributário da empresa implicar o recolhimento de tributos em
                percentuais variáveis, a cotação adequada será aquela correspondente à média dos
                efetivos recolhimentos da empresa nos últimos doze meses.<br><br>

                3.8. Independentemente do percentual do tributo que constar da planilha, no
                pagamento serão retidos na fonte os percentuais estabelecidos pela legislação vigente.<br><br>

                3.9. A apresentação das propostas implica obrigatoriedade do cumprimento das
                disposições nelas contidas, em conformidade com o que dispõe o Termo de Referência,
                Projeto Básico e Projeto Executivo, assumindo o proponente o compromisso de executar
                os serviços nos seus termos, bem como de fornecer os materiais, equipamentos,
                ferramentas e utensílios necessários, em quantidades e qualidades adequadas à
                perfeita execução contratual, promovendo, quando requerido, sua substituição.<br><br>

                3.10 No envio da proposta, o fornecedor deverá, também, encaminhar às seguintes
                declarações:<br><br>

                a) que inexistem fatos impeditivos para sua habilitação no certame, ciente da
                obrigatoriedade de declarar ocorrências posteriores;<br><br>

                b) que está ciente e concorda com as condições contidas no Aviso de Contratação
                Direta e seus anexos;<br><br>

                c) que não emprega menor de 18 anos em trabalho noturno, perigoso ou insalubre
                e não emprega menor de 16 anos, salvo menor, a partir de 14 anos, na condição de
                aprendiz, nos termos do artigo 7°, XXXIII, da Constituição;
                3.11. O licitante organizado em cooperativa deverá declarar, ainda, em campo próprio
                do sistema eletrônico, que cumpre os requisitos estabelecidos no artigo 16 da Lei nº
                14.133, de 2021.<br><br>

                3.12. O fornecedor enquadrado como microempresa, empresa de pequeno porte ou
                sociedade cooperativa deverá declarar, a que cumpre os requisitos estabelecidos no
                artigo 3° da Lei Complementar nº 123, de 2006, estando apto a usufruir do tratamento
                favorecido estabelecido em seus arts. 42 a 49, observado o disposto nos §§ 1º ao 3º do
                art. 4º, da Lei n.º 14.133, de 2021.<br><br>

                3.13. O prazo de validade da proposta não será inferior a 60 (sessenta) dias, a contar da
                data de sua apresentação.<br><br>

                3.14. Será desclassificada a proposta vencedora que:<br><br>

                a) contiver vícios insanáveis;<br><br>

                b) não obedecer às especificações técnicas pormenorizadas neste aviso ou em seus
                anexos;<br><br>

                c) apresentar preços inexequíveis ou permanecerem acima do preço máximo
                definido para a contratação;<br><br>

                d) não tiver sua exequibilidade demonstrada, quando exigido pela Administração;
                e) apresentar desconformidade com quaisquer outras exigências deste aviso ou
                seus anexos, desde que insanável.<br><br>

                3.15. Quando o fornecedor não conseguir comprovar que possui ou possuirá recursos
                suficientes para executar a contento o objeto, será considerada inexequível a proposta
                de preços que:<br><br>

                a) for insuficiente para a cobertura dos custos da contratação, apresente preços
                global ou unitários simbólicos, irrisórios ou de valor zero, incompatíveis com os preços
                dos insumos e salários de mercado, acrescidos dos respectivos encargos, ainda que o
                ato convocatório da dispensa não tenha estabelecido limites mínimos, exceto quando se
                referirem a materiais e instalações de propriedade do próprio fornecedor, para os quais
                ele renuncie a parcela ou à totalidade da remuneração.<br><br>

                b) apresentar um ou mais valores da planilha de custo que sejam inferiores àqueles
                fixados em instrumentos de caráter normativo obrigatório, tais como leis, medidas
                provisórias e convenções coletivas de trabalho vigentes.<br><br>

                3.16 Serão consideradas inexequíveis as propostas cujos valores forem inferiores a 50%
                (cinquenta por cento) do valor orçado pela Administração.<br><br>

                3.17. Será exigida garantia adicional do licitante vencedor cuja proposta for inferior a
                85% (oitenta e cinco por cento) do valor orçado pela Administração, equivalente à
                diferença entre este último e o valor da proposta, sem prejuízo das demais garantias
                exigíveis de acordo a Lei.<br><br>

                3.18. Se houver indícios de inexequibilidade da proposta de preço, ou em caso da
                necessidade de esclarecimentos complementares, poderão ser efetuadas diligências,
                para que o fornecedor comprove a exequibilidade da proposta.
            </p>
            <p style="display: flex; align-items: center; font-weight: bold; ">
                <img src="{{ public_path('icons/dinheiro.png') }}" width="20" style="margin-right: 10px;"> 4 - HABILITAÇÃO
            </p>
            <p style="text-align: justify;">
                4.1 Os documentos a serem exigidos para fins de habilitação constam do ANEXO I –
                DOCUMENTAÇÃO EXIGIDA PARA HABILITAÇÃO deveram ser encaminhadas junto com a
                documentação de habilitação ou poderão ser solicitadas para o fornecedor que
                ofereceu melhor preço.
            </p>
        </div>
        <div>
            <p style="display: flex; align-items: center; font-weight: bold; ">
                <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 5 - CONTRATAÇÃO
            </p>
            <p style="text-align: justify;">
                5.1. Após a conclusão do processo, caso se conclua pela contratação, será firmado
                Termo de Contrato ou emitido instrumento equivalente.
                <br><br>
                5.2. O adjudicatário terá o prazo de 02 (dois) dias úteis, contados a partir da data de sua
                convocação, para assinar o Termo de Contrato ou aceitar instrumento equivalente,
                conforme o caso (Nota de Empenho/Carta Contrato/Autorização), sob pena de decair o
                direito à contratação, sem prejuízo das sanções previstas neste Aviso de Contratação
                Direta.
                <br><br>
                5.3. O prazo previsto no subitem anterior poderá ser prorrogado, por igual período, por
                solicitação justificada do adjudicatário e aceita pela Administração.
                <br><br>
                5.4. O prazo de vigência da contratação é o estabelecido no Termo de Referência.
                <br><br>
                5.5. Na assinatura do contrato ou do instrumento equivalente será exigida a
                comprovação das condições de habilitação e contratação consignadas neste aviso, que
                deverão ser mantidas pelo fornecedor durante a vigência do contrato.
            </p>
        </div>
        <div>
            <p style="display: flex; align-items: center; font-weight: bold; ">
                <img src="{{ public_path('icons/check.png') }}" width="20" style="margin-right: 10px;"> 6 - INFRAÇÕES E SANÇÕES ADMINISTRATIVAS
            </p>
            <p style="text-align: justify;">
                6.1. Ao fornecedor que, convocado dentro do prazo de validade da sua proposta, não
                celebrar o contrato, deixar de entregar ou apresentar documentação falsa exigida para
                o certame, não mantiver a proposta, ensejar o retardamento da execução do objeto,
                falhar ou fraudar na execução do contrato, comportar-se de modo inidôneo ou cometer
                fraude fiscal, poderão ser aplicadas as seguintes sanções, garantidos o contraditório e a
                prévia defesa, de acordo com as seguintes disposições.
                <br><br>
                a) advertência;<br><br>
                b) multa, observados os seguintes limites máximos:<br><br>
                i) multa de 0,3 % (três décimos por cento) por dia, até o trigésimo dia de atraso,
                sobre o valor do fornecimento ou serviço não realizado;<br><br>
                ii) multa de 10 % (dez por cento) sobre o valor total ou parcial da obrigação não
                cumprida, com o consequente cancelamento da nota de empenho ou documento
                equivalente;<br><br>
                iii) suspensão temporária de participar em licitação e impedimento de contratar
                com a entidade sancionadora por prazo não superior a 2 (dois) anos.<br><br>
                <br><br>
                6.2. O valor da multa aplicada será descontado do valor da garantia prestada, retido
                dos pagamentos devidos pela Administração ou cobrado judicialmente, sendo corrigida
                monetariamente, de conformidade com a variação do IPCA, a partir do termo inicial, até
                a data do efetivo recolhimento.
                <br><br>
                6.3. A contagem do período de atraso na execução dos ajustes será realizada a partir do
                primeiro dia útil subsequente ao do encerramento do prazo estabelecido para o
                cumprimento da obrigação.
            </p>
        </div>
        <div>
            <p style="display: flex; align-items: center; font-weight: bold; ">
                <img src="{{ public_path('icons/check.png') }}" width="20" style="margin-right: 10px;"> 7 - DAS DISPOSIÇÕES GERAIS
            </p>
            <p style="text-align: justify;">
                7.1. No caso de todos os fornecedores restarem desclassificados ou inabilitados
                (procedimento fracassado), a Administração poderá:
                <br><br>
                a) republicar o presente aviso com uma nova data;
                <br><br>
                b) valer-se, para a contratação, de proposta obtida na pesquisa de preços que
                serviu de base ao procedimento, se houver, privilegiando-se os menores preços, sempre
                que possível, e desde que atendidas às condições de habilitação exigidas.
                <br><br>
                7.2. No caso do subitem anterior, a contratação será operacionalizada fora deste
                procedimento.
                <br><br>
                a) fixar prazo para que possa haver adequação das propostas ou da
                documentação de habilitação, conforme o caso.
                <br><br>
                7.3. Caberá ao fornecedor acompanhar as operações, ficando responsável pelo ônus
                decorrente da perda do negócio diante da inobservância de quaisquer mensagens
                emitidas pela Administração ou de sua desconexão.
                <br><br>
                7.4. Não havendo expediente ou ocorrendo qualquer fato superveniente que impeça a
                realização do certame na data marcada, a sessão será automaticamente transferida
                para o primeiro dia útil subsequente, no mesmo horário anteriormente estabelecido,
                desde que não haja comunicação em contrário.
                <br><br>
                7.5. Os horários estabelecidos na divulgação deste procedimento e durante o envio de
                lances observarão o horário de Brasília-DF, inclusive para contagem de tempo e registro
                no Sistema e na documentação relativa ao procedimento.
                <br><br>
                7.6. No julgamento das propostas e da habilitação, a Administração poderá sanar erros
                ou falhas que não alterem a substância das propostas, dos documentos e sua validade
                jurídica, mediante despacho fundamentado, registrado em ata e acessível a todos,
                atribuindo-lhes validade e eficácia para fins de habilitação e classificação.
                <br><br>
                7.7. As normas disciplinadoras deste Aviso de Contratação Direta serão sempre
                interpretadas em favor da ampliação da disputa entre os interessados, desde que não
                comprometam o interesse da Administração, o princípio da isonomia, a finalidade e a
                segurança da contratação.
                <br><br>
                7.8. Os fornecedores assumem todos os custos de preparação e apresentação de suas
                propostas e a Administração não será, em nenhum caso, responsável por esses custos,
                independentemente da condução ou do resultado do processo de contratação.
                <br><br>
                7.9. Em caso de divergência entre disposições deste Aviso de Contratação Direta e de
                seus anexos ou demais peças que compõem o processo, prevalecerá as deste Aviso.
                <br><br>
                7.10. Integram este Aviso de Contratação Direta, para todos os fins e efeitos, os
                seguintes anexos:
            </p>
        </div>

        <div>
            <p style="text-align: justify;">
                ANEXO I – Documentação exigida para Habilitação
                <br>
                ANEXO II – Minuta do Contrato
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

</div>

</body>

</html>
