<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>TERMO DE REFERÊNCIA - Processo {{ $processo->numero_processo ?? $processo->id }}</title>
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
    @php
    // Mapeamento das opções para texto legível
    $opcoes_vigencia = [
    '12_meses' => '12 meses',
    '24_meses' => '24 meses',
    '36_meses' => '36 meses',
    'exercicio_financeiro' => 'Exercício financeiro da contratação (até 31/12)',
    'outro' => 'Outro',
    ];

    // Captura o campo (pode ser string ou array)
    $vigencia = $detalhe->prazo_vigencia ?? ['12_meses'];

    // Garante que é array
    $vigencia = is_array($vigencia) ? $vigencia : [$vigencia];

    // Substitui os códigos pelos textos legíveis
    $vigencia_formatada = collect($vigencia)
    ->map(fn($item) => $opcoes_vigencia[$item] ?? ucfirst(str_replace('_', ' ', $item)))
    ->implode(', ');

    $outro_vigencia = $detalhe->prazo_vigencia_outro ?? '________________.';
    $objeto_continuado = strtolower($detalhe->objeto_continuado ?? 'nao');
    @endphp
    {{-- ====================================================================== --}}
    {{-- BLOCO 1: CAPA DO DOCUMENTO --}}
    {{-- ====================================================================== --}}
    <div id="cover-page">
        <img src="{{ public_path('icons/capa-documento.png') }}" alt="Martelo da Justiça" class="cover-image">
        <div class="cover-title">
            TERMO DE REFERÊNCIA
        </div>
    </div>
    {{-- QUEBRA DE PÁGINA --}}
    <div class="page-break"></div>
    {{-- ====================================================================== --}}
    {{-- BLOCO 2: ANEXO I TERMO DE REFERÊNCIA --}}
    {{-- ====================================================================== --}}
    <div class="container">
        <div class="conteudo-all">
            <div style="margin: 30px 0 0;">
                <div class="title">TERMO DE REFERÊNCIA</div>
            </div>
            <div class="conteudo">
                <!-- Objeto -->
                <div class="section">
                    <table>
                        <tr>
                            <td class="icon">
                                <img src="{{ public_path('icons/grafico.png') }}" width="40">
                            </td>
                            <td class="content">
                                <div style=" font-weight: bold; margin-bottom: 3px;">OBJETO</div>
                                <div style="">{!! strip_tags($processo->objeto) !!}</div>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Equipe de Planejamento -->
                <div class="section">
                    <table>
                        <tr>
                            <td class="icon">
                                <img src="{{ public_path('icons/alerta.png') }}" width="40">
                            </td>
                            <td class="content">
                                <div style=" font-weight: bold; margin-bottom: 3px;">PRAZO DE VIGÊNCIA DA CONTRATAÇÃO</div>
                                <div style="">
                                    O prazo de vigência da contratação é de {{ $vigencia_formatada }} contados da assinatura do contrato, na forma do artigo 105 da Lei n° 14.133, de 2021
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
        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 1. OBJETO
        </p>

        <p style="text-align: justify;">
            1.1. O presente Termo de Referência tem como finalidade a {!! strip_tags($processo->objeto) !!}
        </p>
        <p style="text-align: justify;">
            1.2. A contratação seguira conforme quantitativo abaixo:
        </p>
        <table border="1" cellspacing="0" cellpadding="4" style="border-collapse: collapse; width: 100%; text-align: center; font-size: 8pt;">
            <thead>
                <tr>
                    <th style="width: 6%;">ITEM</th>
                    <th style="width: 30%;">ESPECIFICAÇÃO</th>
                    <th style="width: 8%;">UNIDADE</th>
                    <th style="width: 20%;">QUANTIDADE ESTIMADA</th>
                    <th style="width: 18%;">VALOR UNITÁRIO</th>
                    <th style="width: 18%;">VALOR TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @php
                $itens = is_array($detalhe->itens_especificaca_quantitativos_xml)
                    ? $detalhe->itens_especificaca_quantitativos_xml
                    : json_decode($detalhe->itens_especificaca_quantitativos_xml, true);

                $parseBr = fn($v) => (float) str_replace(',', '.', str_replace(['.', 'R$', ' '], '', $v ?? ''));
                $fmtBr = fn($v) => $v > 0 ? 'R$ ' . number_format($v, 2, ',', '.') : '—';
                
                $itensAgrupados = collect($itens)->groupBy(fn($i) => $i['lote'] ?? 'Sem Lote');
                @endphp

                @if ($itens && count($itens) > 0)
                    @foreach ($itensAgrupados as $loteNome => $itensDoLote)
                        @php $sumLote = 0; @endphp
                        @if($loteNome !== 'Sem Lote')
                            <tr style="background-color: #e9e9e9;">
                                <td colspan="6" style="text-align: left; font-weight: bold; padding-left: 10px;">{{ $loteNome }}</td>
                            </tr>
                        @endif

                        @foreach ($itensDoLote as $index => $item)
                            @php
                                $qtd = (float) str_replace(',', '.', str_replace('.', '', $item['quantidade'] ?? '0'));
                                $vUnit = $parseBr($item['valor_unitario'] ?? '0');
                                $vTotal = $qtd * $vUnit;
                                $sumLote += $vTotal;
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td style="text-align: left;">{{ $item['especificacoes'] ?? '' }}</td>
                                <td>{{ $item['unidade'] ?? '' }}</td>
                                <td>{{ number_format($qtd, 2, ',', '.') }}</td>
                                <td>{{ $fmtBr($vUnit) }}</td>
                                <td>{{ $fmtBr($vTotal) }}</td>
                            </tr>
                        @endforeach
                        
                        <tr>
                            <td colspan="5" style="text-align:right; font-weight:bold; background-color:#f0f0f0; border-top:1px solid #999;">TOTAL DO LOTE</td>
                            <td style="font-weight:bold; background-color:#f0f0f0; border-top:1px solid #999;">{{ $fmtBr($sumLote) }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="6">Nenhum item encontrado</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
    <div>
        <p style="display: flex; align-items: center; font-weight: bold;">
            <img src="{{ public_path('icons/engrenagem.png') }}" width="20" style="margin-right: 10px;">2. JUSTIFICATIVA
        </p>

        <p style="text-align: justify; color: red;">
            2.1 {{ $processo->detalhe->justificativa }}
        </p>
    </div>
    <div>
        <p style="display: flex; align-items: center; font-weight: bold;">
            <img src="{{ public_path('icons/check.png') }}" width="20" style="margin-right: 10px;">3. ENQUADRAMENTO LEGAL
        </p>

        <p style="text-align: justify;">
            3.1. O presente Termo de Referência tem como base legal a Lei Federal nº 14.133/2021
            (Nova Lei de Licitações e Contratos Administrativos), especialmente o artigo 74, inciso I,
            que dispõe sobre a inexigibilidade de licitação nas hipóteses em que a competição é
            inviável em razão da exclusividade do fornecedor, seja produtor, empresa ou
            representante comercial.
        </p>
        <p style="text-align: justify;">
            3.2. O procedimento observado também obedece ao disposto no artigo 72, incisos I a VIII
            da Lei 14.133/2021, bem como ao Decreto Municipal que regulamenta a matéria,
            garantindo o atendimento às fases preparatórias, à formalização da contratação e à
            observância dos princípios da Administração Pública.
        </p>
        <p style="text-align: justify;">
            3.3. Nas palavras do ilustre professor Ronny Charles: “Quando a lei prevê hipóteses de
            contratação direta (dispensa e inexigibilidade) é porque admite que nem sempre a
            realização do certame levará à melhor forma de contratação pela Administração ou que,
            pelo menos, a sujeição do negócio ao procedimento formal e burocrático previsto pelo
            estatuto não serve eficaz ao atendimento do interesse público naquela hipótese
            específica.”
        </p>
        <p style="text-align: justify;">
            3.4. Nesse mesmo sentido, o nobre doutrinador Adilson Abreu Dallari destaca que: “Nem
            sempre, é verdade, a licitação leva uma contratação mais vantajosa. Não pode ocorrer,
            em virtude da realização do procedimento licitatório, é o sacrifício de outros valores e
            princípios consagrados pela ordem jurídica, especialmente o princípio da eficiência.”
        </p>
        <p style="text-align: justify;">
            3.5. No presente caso, a inexigibilidade de licitação revela-se não apenas possível, mas
            juridicamente adequada e necessária, uma vez que a exclusividade do fornecedor torna
            inviável a realização de competição. Não obstante, o processo deve observar
            integralmente os elementos estruturais de uma contratação pública, como demonstração
            da necessidade, justificativa de preço, análise técnica e respeito aos princípios da
            impessoalidade, moralidade e publicidade.
        </p>
        <p style="text-align: justify;">
            3.6. A contratação direta por inexigibilidade, quando se trata de empresa ou
            representante comercial exclusivo, amolda-se à previsão legal do art. 74, I, pois a
            exclusividade impede a obtenção de propostas concorrentes. Desse modo, a contratação
            direta promove maior celeridade, assegura eficiência administrativa e garante a
            aquisição de bens/serviços compatíveis com o padrão exigido, atendendo plenamente ao
            interesse público.
        </p>
        <p style="text-align: justify;">
            3.7. Ressalta-se que a inviabilidade de competição decorre da unicidade da
            representação comercial ou da exclusividade de fornecimento, cenário em que não há
            pluralidade de fornecedores habilitados para competir em igualdade. Assim, a eleição da
            contratada não decorre de discricionariedade arbitrária, mas de fato jurídico comprovado
            documentalmente, que torna impraticável a realização de processo licitatório.
        </p>
    </div>
    <div>
        <p style="display: flex; align-items: center; font-weight: bold;">
            <img src="{{ public_path('icons/check.png') }}" width="20" style="margin-right: 10px;">4. APRESENTAÇÃO DA PROPOSTA DE PREÇOS
        </p>

        <p style="text-align: justify;">
            4.1. A proposta de preço deverá conter os seguintes elementos:
            <span style="margin-left: 20px;">
                a) Nome; Endereço; CNPJ; Inscrição Estadual/Municipal. <br>
                b) Deverá ser organizada por lote, descrevendo todos os preços por item de acordo
                com o objeto devendo a negociação ocorrer por menor preço por item, e ratificação por
                item embora a contratação possa ser por lote ou por itens do lote a fim de atender e
                otimizar o empenhamento das despesas em atendimento a necessidade pontual da
                contratante.<br>
                c) Prazo de validade da proposta não poderá ser inferior a 60 (sessenta) dias.<br>
                d) A proposta que omitir o prazo de validade será considerada como válida pelo
                período de 60 (sessenta) dias.<br>
                e) O valor a ser cotado deve levar em consideração o valor total da proposta, em
                moeda corrente nacional, algarismo e/ou por extenso, apurado à data de sua
                apresentação, sem inclusão de qualquer encargo financeiro que deve ser assumido pelo
                potencial contratado ou previsão inflacionária. Nos preços propostos deverão estar
                incluídos, além do lucro, todas as despesas e custos, como por exemplo: transportes,
                fretes, tributos de qualquer natureza e todas as despesas, diretas ou indiretas,
                relacionadas com o objeto da licitação.<br>
                f) As propostas deverão ser apresentadas contemplando os quantitativos fixados,
                conforme anexo I, não sendo permitidas ofertas com quantitativo inferior.<br>
                g) O licitante deverá demonstrar na sua proposta, quantidade, e demais informações
                a fim de viabilizar as requisições demandadas respeitadas a forma e condições
                estabelecida no Termo de Referência.<br>
                h) O preço cotado permanecerá fixo e irreajustável pelo período do contrato, exceto
                quando confirmado motivo justo para revisão ou atualização, na forma que determina a
                legislação.
            </span>
        </p>
        
    </div>
    <div>
        <p style="display: flex; align-items: center; font-weight: bold;">
            <img src="{{ public_path('icons/mao.png') }}" width="20" style="margin-right: 10px;">5. DA ESPECIFICAÇÃO DO IMÓVEL
        </p>

        <p style="text-align: justify; color: red;">
            {!! strip_tags($processo->detalhe->especificacao_servicos_imovel) !!}
        </p>
        
    </div>
    <div>
        <p style="display: flex; align-items: center; font-weight: bold;">
            <img src="{{ public_path('icons/bolsa.png') }}" width="20" style="margin-right: 10px;">6. DO REGIME DE EXECUÇÃO
        </p>

        <p style="text-align: justify;">
            6.1. O objeto contratado será realizado por execução indireta;<br>
            6.2. O objeto deverá ser entregue no prazo máximo de 02 dias úteis contados a partir do
            recebimento da Ordem de Fornecimento ou assinatura do contrato.<br>
            6.3. A entrega ocorrerá na sede da Prefeitura Municipal, nos horários de 07:30 h à 13:00h.<br>
            6.4. Correrão por conta da Contratada todas as despesas decorrentes de transporte,
            frete, seguros, impostos e encargos trabalhistas necessários à perfeita execução do
            objeto.<br>
            6.5. O recebimento do objeto seguirá rigorosa conferência técnica para garantir a
            conformidade com as especificações que justificaram a inexigibilidade:<br>
            <span style="margin-left: 20px;">
                a) Recebimento Provisório: Realizado no ato da entrega, por servidor ou comissão
                designada, para efeito de posterior verificação da conformidade do material com a
                especificação exigida.<br>
                b) Recebimento Definitivo: Ocorrerá em até 5 dias úteis após o recebimento provisório,
                mediante termo detalhado que comprove a adequação do objeto aos termos da proposta
                e às notas fiscais, conforme o Art. 140 da Lei nº 14.133/2021.
            </span>
            <br>
            6.6. A execução será acompanhada e fiscalizada pelo gestor/fiscal do contrato
            designado pela Administração, que anotará em registro próprio todas as ocorrências,
            determinando o que for necessário à regularização das faltas ou defeitos observados.
        </p>
    </div>
    <div>
        <p style="display: flex; align-items: center; font-weight: bold;">
            <img src="{{ public_path('icons/selo.png') }}" width="20" style="margin-right: 10px;">7. DA DESCRIÇÃO DA SOLUÇÃO
        </p>

        <p style="text-align: justify;">
           7.1. A descrição da solução como um todo, abrange a {!! strip_tags($processo->objeto) !!}
           <br>
           7.2. A contratação em tela visa dar continuidade aos serviços acessórios que dão
           sustentabilidade à otimização e adequação das atividades da administração pública, em
           suas atribuições finalísticas.<br>
           7.3. Os serviços deverão ser executados com zelo e destreza, e de acordo com as
           descrições, detalhamento e especificações contidas nesse Termo de Referência, não
           eximindo a empresa da responsabilidade de execução de outras atividades atinentes ao
           objeto, a qualquer tempo e a critério da Administração.
        </p>
    </div>
    <div>
        <p style="display: flex; align-items: center; font-weight: bold;">
            <img src="{{ public_path('icons/calc.png') }}" width="20" style="margin-right: 10px;">8. DA VIGÊNCIA
        </p>

        <p style="text-align: justify; color: red;">
            8.1. O período de vigência do instrumento contratual será de {{ $vigencia_formatada }},
            contados da data de sua assinatura, podendo este ser rescindido ou ter seu prazo
            prorrogado na forma da Lei.
        </p>
    </div>
    <div>
        <p style="display: flex; align-items: center; font-weight: bold;">
            <img src="{{ public_path('icons/calc.png') }}" width="20" style="margin-right: 10px;">9. REQUISITOS DA CONTRATAÇÃO
        </p>

        <p style="text-align: justify;">
            9.1. Para que o objeto da contratação seja atendido, é necessário o atendimento de
            alguns requisitos mínimos necessários, dentre eles os de qualidade e capacidade de
            execução pelo contratado, nos termos do artigo 72, da Lei Federal 14.133/2021. <br>
            9.2. Será exigido, conforme artigo 62 da Lei Federal 14.133/2021, documentos referentes
            a habilitação jurídica (premissa do artigo 66), habilitação técnica (rol do artigo 67),
            habilitação fiscal, social e trabalhista (artigo 68) habilitação econômico-financeira (rol do
            artigo 69), todos da mesma legislação (Lei Federal 14.133/2021).<br>
            9.3. Sendo assim, os documentos exigidos serão:<br>
            <span style="margin-left: 20px;">
                o Contrato social da empresa (todas as alterações ou última consolidação);<br>
                o Documento de Identificação dos sócios da empresa;<br>
                o Prova de inscrição no Cadastro Nacional da Pessoa Jurídica (CNPJ);<br>
                o Prova de inscrição no cadastro de contribuintes estadual e/ou municipal<br>
                o Regularidade perante a Fazenda Municipal;<br>
                o Regularidade perante a Fazenda Estadual;<br>
                o Regularidade perante a Fazenda Federal;<br>
                o Regularidade perante a Caixa Econômica Federal;<br>
                o Regularidade perante a Justiça do Trabalho;<br>
                o Atestado de capacidade técnica profissional e/ou operacional;<br>
                o Atestado de exclusividade, contrato de exclusividade, declaração do fabricante ou
                outro documento idôneo capaz de comprovar que o objeto é fornecido ou prestado por
                produtor, empresa ou representante comercial exclusivos (se for o caso).
            </span>
        </p>
    </div>
    <div>
        <p style="display: flex; align-items: center; font-weight: bold;">
            <img src="{{ public_path('icons/calc.png') }}" width="20" style="margin-right: 10px;">10. MODELO DE GESTÃO DO CONTRATO
        </p>

        <p style="text-align: justify;">
            10.1. A fiscalização da contratação, decorrente desta inexigibilidade de licitação, será
            acompanhada e fiscalizada por servidor da Administração, especialmente designados,
            nos termos do artigo 117 da Lei Federal 14.133/2021.<br>
            10.2. A contratante deverá indiciar um responsável legal, através de documento
            encaminhado para o e-mail da prefeitura Municipal ou protocolado pessoalmente no
            setor de licitações e contratos deste município, indicando os respectivos contatos (e-mail,
            celular e Whatsapp), com poderes para representá-lo perante essa municipalidade na
            execução do contrato decorrente da inexigibilidade de licitação objeto deste termo de
            referência.
        </p>
    </div>
    <div>
        <p style="display: flex; align-items: center; font-weight: bold;">
            <img src="{{ public_path('icons/calc.png') }}" width="20" style="margin-right: 10px;">11. DO RECEBIMENTO DO OBJETO E DOS CRITÉRIOS PARA MEDIÇÃO E PAGAMENTO
        </p>

        <p style="text-align: justify;">
            11.1. O recebimento do objeto do contrato, decorrente da referida inexigibilidade de
            licitação, se dará: <br>
            <span style="margin-left: 20px;">    
                a) provisoriamente, pelo responsável por seu acompanhamento e fiscalização, mediante
                termo detalhado, quando verificado o cumprimento das exigências de caráter técnico;
                <br>
                b) definitivamente, por servidor ou comissão designada pela autoridade competente,
                mediante termo detalhado que comprove o atendimento das exigências contratuais;
            </span>
            <br>
            11.2. O pagamento será realizado no prazo máximo de até 30 (trinta) dias, contados a
            partir do recebimento da Nota Fiscal ou Fatura, através de ordem bancária, para crédito
            em banco, agência e conta corrente indicados pelo contratado, respeitada a ordem
            cronológica.
            <br>
            11.3. A emissão da Nota Fiscal/Fatura deve ser precedida do recebimento definitivo do
            objeto, nos termos abaixo.
            <br>
            11.4. No prazo de até 5 dias corridos do adimplemento da parcela, a CONTRATADA
            deverá entregar toda a documentação comprobatória do cumprimento da obrigação
            contratual;
            <br>
            11.5. A contratante realizará inspeção minuciosa de todos os objetos
            executados/fornecidos, por meio de profissionais técnicos competentes, acompanhados
            dos profissionais encarregados pelo serviço, com a finalidade de verificar a adequação
            do objeto e constatar e relacionar os arremates, retoques e revisões finais que se fizerem
            necessários.
            <br>
            11.6. Para efeito de recebimento provisório, ao final de cada período de faturamento, o
            fiscal técnico do contrato irá apurar o resultado das avaliações da execução do objeto e,
            se for o caso, a análise do desempenho e qualidade dos mesmos em consonância com
            os indicadores previstos, que poderá resultar no redimensionamento de valores a serem
            pagos à contratada, registrando em relatório a ser encaminhado ao gestor do contrato.
            <br>
            11.7. A Contratada fica obrigada a reparar, corrigir, remover, reconstruir ou substituir, às
            suas expensas, no todo ou em parte, o objeto em que se verificarem vícios, defeitos ou
            incorreções resultantes da execução ou materiais empregados, cabendo à fiscalização
            não atestar a última e/ou única medição de serviços até que sejam sanadas todas as
            eventuais pendências que possam vir a ser apontadas no Recebimento Provisório.
            <br>
            11.8. No prazo de até 10 (dez) dias corridos a partir do recebimento provisório, o Gestor
            do Contrato deverá providenciar o recebimento definitivo, ato quo concretiza o ateste da
            execução/fornecimento, obedecendo as seguintes diretrizes:
            <br>
            11.9. Realizar a análise dos relatórios e de toda a documentação apresentada pela
            fiscalização e, caso haja irregularidades que impeçam a liquidação e o pagamento da
            despesa, indicar as cláusulas contratuais pertinentes, solicitando à CONTRATADA, por
            escrito, as respectivas correções;
            <br>
            11.10. O recebimento provisório ou definitivo do objeto não exclui a responsabilidade da
            Contratada pelos prejuízos resultantes da incorreta execução do contrato, ou, em
            qualquer época, das garantias concedidas e das responsabilidades assumidas em
            contrato e por força das disposições legais em vigor.
            <br>
            11.11. Os serviços/produtos poderão ser rejeitados, no todo ou em parte, quando em
            desacordo com as especificações constantes neste Termo de Referência e na proposta,
            devendo ser corrigidos/refeitos/substituídos no prazo fixado pelo fiscal do contrato, às
            custas da Contratada, sem prejuízo da aplicação de penalidades.
            <br>
            11.12. A Nota Fiscal ou Fatura deverá ser obrigatoriamente acompanhada da
            comprovação da regularidade fiscal, mediante consulta aos sítios eletrônicos oficiais ou à
            documentação mencionada no art. 68 da Lei Federal 14.133/2021.
        </p>
    </div>
    <div>
        <p style="display: flex; align-items: center; font-weight: bold;">
            <img src="{{ public_path('icons/calc.png') }}" width="20" style="margin-right: 10px;">12. DOTAÇÃO ORÇAMENTÁRIA
        </p>

        <p style="text-align: justify; color: red;">
            12.1. Os custos com a presente contratação correrão por conta da seguinte dotação
            orçamentária:
        </p>
        <table style="border-collapse: collapse; width: 100%; border: 1px solid black;">
            <tr>
                <!-- Coluna da esquerda -->
                <td style="vertical-align: top; padding: 10px;">
                    {!! str_replace('<p>', '<p style="text-indent:30px; text-align: justify;">', $detalhe->dotacao_orcamentaria) !!}
                </td>
            </tr>
        </table>
    </div>
    <div>
        <p style="display: flex; align-items: center; font-weight: bold;">
            <img src="{{ public_path('icons/calc.png') }}" width="20" style="margin-right: 10px;">13. FORMA E CRITÉRIO DE SELEÇÃO DO FORNECEDOR/PRESTADOR
        </p>

        <p style="text-align: justify; color: red;">
            13.1. A presente contratação será realizada mediante Inexigibilidade de Licitação, com
            fundamento no Art. 74, inciso I, da Lei Federal nº 14.133/2021, tendo em vista a
            inviabilidade de competição decorrente da exclusividade do fornecedor/prestador para o
            objeto descrito neste Termo de Referência. 
            <br>
            13.2. O critério de seleção pauta-se na singularidade e exclusividade do objeto. A
            escolha do contratado(a) {{ $processo->detalhe->razao_social }}, inscrito(a) sob o CPF/CNPJ de n°
            {{ $processo->detalhe->cnpj_empresa_vencedora }} justifica-se pelos seguintes fatores:
            <br>
            <span style="margin-left: 20px;">
                a) Exclusividade Técnica/Comercial: A referida empresa é a única detentora do direito de
                comercialização/fabricação na região.
                <br>
                b) Imprescindibilidade: O objeto a ser contratado é o único que atende tecnicamente às
                necessidades específicas da Administração, não havendo alternativas equivalentes no
                mercado que possam ser objeto de disputa licitatória.
            </span>
            <br>
            13.3. Para fins de conformidade legal, a exclusividade foi comprovada por meio de
            Atestado de Exclusividade, Cartas de exclusividade do fabricante, registros de patente ou
            contratos de representação comercial exclusiva.
            <br>
            13.4. Ainda que não haja competição, a seleção do fornecedor está condicionada à
            comprovação da conformidade do preço com os parâmetros de mercado. A seleção será
            validada mediante:
            <br>
            <span style="margin-left: 20px;">
                a) Apresentação de Notas Fiscais ou Contratos firmados pela empresa com outros
                órgãos da Administração Pública ou entidades privadas, demonstrando que os preços
                ofertados são equivalentes aos praticados usualmente.
                b) Análise técnica que comprove que o valor está dentro da estimativa de custos
                elaborada pelo setor competente desta Administração.
            </span>
        </p>
    </div>
    <div>
        <p style="display: flex; align-items: center; font-weight: bold;">
            <img src="{{ public_path('icons/calc.png') }}" width="20" style="margin-right: 10px;">14. DA RAZÃO E ESCOLHA DO CONTRATADO
        </p>

        <p style="text-align: justify; color: red;">
            14.1. {!! strip_tags($processo->detalhe->razao_escolha_contratado) !!}
        </p>
    </div>
    <div>
        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 15. DA JUSTIFICATIVA DOS PREÇOS:
        </p>
        <p style="text-align: justify;">
            15.1. No que diz respeito a JUSTIFICATIVA DE PREÇOS, em atendimento ao que
            preconiza o artigo 72, da Lei 14.133/2021 para elaboração do custo, deverá ser
            apresentado valores praticados nos mercados, através de contratações com objetos
            similares. <br>
            15.2. O contratado(a) apresentou notas fiscais e extratos de contratos de outros entes
            públicos, onde notadamente é similar ao valor proposto. <br>
            15.3. Sendo assim, declara-se que o preço praticado para a presente contratação é
            compatível com o mercado sendo considerado justo para esta Administração.
        </p>

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 16. OBRIGAÇÕES DO(A) CONTRATADO(A)
        </p>
        <p style="text-align: justify;">
           16.1. O(A) CONTRATADO(A) vincula-se à proposta apresentada e obriga-se a executar
            o objeto de acordo com as especificações contidas neste Termo de Referência, além das
            seguintes obrigações:
            <br>
            16.1.1. Manter, durante toda a execução contratual, as condições de exclusividade de
            fabricação, comercialização ou prestação de serviços que ensejaram a inexigibilidade,
            comunicando imediatamente à Administração qualquer alteração nessa condição.
            <br>
            16.1.2. Não subcontratar o objeto principal do ajuste, sob pena de desnaturalização da
            inexigibilidade.<br>
            16.1.3. Entregar os produtos ou prestar os serviços rigorosamente dentro dos padrões de
            qualidade e especificações técnicas detalhadas em sua proposta e no Termo de
            Referência.<br>
            16.1.4. Substituir ou reparar, às suas expensas, no prazo de 48 horas, o objeto que
            apresentar vícios, defeitos ou incorreções, ou que for identificado como em desacordo
            com as normas técnicas vigentes.<br>
            16.1.5. Assegurar a garantia técnica do fabricante/provedor, conforme os prazos e
            condições estabelecidos na legislação e na proposta comercial.<br>
            16.1.6. Manter, durante toda a vigência do contrato, a regularidade com o FGTS, o INSS,
            as Fazendas Federal, Estadual e Municipal, bem como a regularidade perante a Justiça
            do Trabalho.<br>
            16.1.7. Assumir total responsabilidade por quaisquer danos causados diretamente à
            Administração ou a terceiros, decorrentes de sua culpa ou dolo na execução do objeto.<br>
            16.1.8. Arcar com todos os encargos trabalhistas, previdenciários, fiscais, comerciais e
            de transporte (frete, carga e descarga) decorrentes da execução do contrato.
        </p>
        {{ $processo->detalhe->obrigacoes_contratado_extras }}

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 17. OBRIGAÇÕES DA CONTRATANTE
        </p>
        <p style="text-align: justify;">
            17.1. A CONTRATANTE obriga-se a: <br>
            17.1.1 Proporcionar todas as condições para que a CONTRATADA possa desempenhar
            seus serviços de acordo com as determinações do Contrato e do Termo de Referência;
            <br>
            17.1.2. Exigir o cumprimento de todas as obrigações assumidas pela CONTRATADA, de
            acordo com as cláusulas contratuais e os termos de sua proposta;
            <br>
            17.1.3. Exercer o acompanhamento e a fiscalização do objeto contratado, por servidor
            especialmente designado, anotando em registro próprio as falhas detectadas, indicando
            dia, mês e ano, bem como o nome dos empregados eventualmente envolvidos, e
            encaminhando os apontamentos à autoridade competente para as providências cabíveis;
            <br>
            17.1.4. Notificar a CONTRATADA por escrito da ocorrência de eventuais imperfeições no
            curso da execução dos serviços, fixando prazo para a sua correção;
            <br>
            17.1.5. Pagar à CONTRATADA o valor resultante do objeto contratado, na forma do
            contrato;
            <br>
            17.1.6. Efetuar as retenções tributárias devidas sobre o valor da Nota Fiscal/Fatura da
            contratada, no que couber, em conformidade com a legislação.
            <br>
            17.2. Não praticar atos de ingerência na administração da Contratada, tais como:
            <br>
            17.2.1. exercer o poder de mando sobre os empregados da Contratada, devendo
            reportar-se somente aos prepostos ou responsáveis por ela indicados, exceto quando o
            objeto da contratação previr o atendimento direto, tais como nos serviços de recepção e
            apoio ao usuário;
            <br>
            17.2.2. direcionar a contratação de pessoas para trabalhar nas empresas Contratadas;
            <br>
            17.2.3. promover ou aceitar o desvio de funções dos trabalhadores da Contratada,
            mediante a utilização destes em atividades distintas daquelas previstas no objeto da
            contratação e em relação à função específica para a qual o trabalhador foi contratado; e
            <br>
            17.2.4. considerar os trabalhadores da Contratada como colaboradores eventuais do
            próprio órgão ou entidade responsável pela contratação, especial mente para efeito de
            concessão de diárias e passagens;
            <br>
            17.3. fiscalizar mensalmente, por amostragem, o cumprimento das obrigações
            trabalhistas, previdenciárias e para com o FGTS, especialmente:
            <br>
            17.3.1. A concessão de férias remuneradas e o pagamento do respectivo adicional, bem
            como de auxílio-transporte, auxílio-alimentação e auxílio-saúde, quando for devido;
            <br>
            17.3.2. O recolhimento das contribuições previdenciárias e do FGTS dos empregados
            que efetivamente participem da execução dos serviços contratados, a fim de verificar
            qualquer irregularidade:
            <br>
            17.3.3. O pagamento de obrigações trabalhistas e previdenciárias dos empregados
            dispensados até a data da extinção do contrato;
            <br>
            17.4. Analisar os termos de rescisão dos contratos de trabalho do pessoal empregado na
            execução do objeto contratado no prazo de 30 (trinta) dias, prorrogável por igual período,
            após a extinção ou rescisão do contrato;
            <br>
            17.5. Fornecer por escrito às informações necessárias para a execução do objeto
            contratado;
            <br>
            17.6. Realizar avaliações periódicas da qualidade do objeto contratado, após seu
            recebimento;
            <br>
            17.7. Cientificar o órgão de representação judicial do município para adoção das medidas
            cabíveis quando do descumprimento das obrigações pela Contratada;
            <br>
            17.8. Arquivar, entre outros documentos, projetos, "as built", especificações técnicas,
            orçamentos, termos de recebimento, contratos e aditamentos, relatórios de inspeções
            técnicas após o recebimento do serviço e notificações expedidas;
            <br>
            17.9. Assegurar que o ambiente de trabalho, inclusive seus equipamentos e instalações,
            apresentem condições adequadas ao cumprimento, pela contratada, das normas de
            segurança e saúde no trabalho, quando o serviço for executado em suas dependências,
            ou em local por ela designado;
            <br>
            17.10. Verificar, no ato do recebimento, se o objeto entregue corresponde exatamente à
            marca/modelo/serviço contratado.
        </p>
        {{ $processo->detalhe->obrigacoes_contratante_extras }}

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 18. SUBCONTRATAÇÃO
        </p>
        <p style="text-align: justify;">
            18.1. Não será admitida a subcontratação total do objeto licitatório;
        </p>

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 19. REAJUSTE
        </p>
        <p style="text-align: justify;">
            19.1 Os preços são fixos e irreajustáveis no prazo de um ano contado da data limite para
            a apresentação das propostas; <br>
            19.1.1 Dentro do prazo de vigência do contrato e mediante solicitação da contratada, os
            preços contratados poderão sofrer reajuste após o interregno de um ano, aplicando-se o
            índice IGPM exclusiva mente para as obrigações iniciadas e concluídas após a
            ocorrência da anualidade;
            <br>
            19.2. Nos reajustes subsequentes ao primeiro, o interregno mínimo de um ano será
            contado a partir dos efeitos financeiros do último reajuste;
            <br>
            19.3. No caso de atraso ou não divulgação do índice de reajustamento, o
            CONTRATANTE pagará à CONTRATADA a importância calculada pela última variação
            conhecida, liquidando a diferença correspondente, tão logo seja divulgado o índice
            definitivo. Fica a CONTRATADA obrigada a apresentar memória de cálculo referente ao
            reajustamento de preços do valor remanescente, sempre que este ocorrer;
            <br>
            19.4. Nas aferições finais, o índice utilizado para reajuste será, obrigatoriamente, o
            definitivo;
            <br>
            19.5. Caso o índice estabelecido para reajustamento venha a ser extinto ou de qualquer
            forma não possa mais ser utilizado, será adotado, em substituição, o que vier a ser
            determinado pela legislação então em vigor;
            <br>
            19.6. Na ausência dê previsão legal quanto ao índice substituto, as partes elegerão novo
            índice oficial, para reajustamento do preço do valor remanescente, por meio de termo
            aditivo;
            <br>
            19.7. O reajuste será realizado por apostilamento;
        </p>

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 20. DAS SANÇÕES:
        </p>
        <p style="text-align: justify;">
            20.1. Pela inexecução total ou parcial do objeto deste contrato, a Administração pode
            aplicar à CONTRATADA, sanções previstas em lei, sempre respeitando com contraditório
            e ampla defesa.
        </p>
        <p
            style="text-align: center; font-size:12px; font-weight: 700; border: 1px solid black; padding: 10px; background:#dadada; margin-top:20px;">
            ENCAMINHAMENTO PARA ÓRGÃO DEMANDANTE
        </p>
        <div style="border: 1px solid black; padding: 10px;">
            <p style="line-height: 1.6">Despacho para a Autoridade Competente para a Autorização de Abertura de Procedimento de Licitação.
            </p>

            {{-- Bloco de data e assinatura --}}
            <div class="footer-signature">
                {{ $processo->prefeitura->cidade }},
                {{ \Carbon\Carbon::parse($dataSelecionada)->translatedFormat('d \d\e F \d\e Y') }}
            </div>
            <hr>

            @php
                // Verifica se a variável $assinantes existe e tem itens
                $hasSelectedAssinantes = isset($assinantes) && count($assinantes) > 0;
            @endphp

            @if ($hasSelectedAssinantes)
                {{-- Renderiza APENAS O PRIMEIRO assinante da lista --}}
                @php
                    $primeiroAssinante = $assinantes[0]; // Pega o primeiro item
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
                        <span style="color: red;">[Cargo/Título Padrão - A ser ajustado]</span>
                    </p>
                </div>
            @endif
        </div>
    </div>
</body>

</html>