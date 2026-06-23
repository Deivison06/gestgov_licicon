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
    ->map(fn($item) => $item === 'outro' ? ($detalhe->prazo_vigencia_outro ?? '________________.') : ($opcoes_vigencia[$item] ?? ucfirst(str_replace('_', ' ', $item))))
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

                <!-- Prazo virgencia -->
                <div class="section">
                    <table>
                        <tr>
                            <td class="icon">
                                <img src="{{ public_path('icons/alerta.png') }}" width="40">
                            </td>
                            <td class="content">
                                <div style=" font-weight: bold; margin-bottom: 3px;">PRAZO DE VIGÊNCIA DA CONTRATAÇÃO</div>
                                <div style="">
                                    O prazo de vigência da contratação é de {{ $vigencia_formatada }} contados do(a) assinatura do
                                    Contrato, na forma do artigo 105 da Lei n° 14.133, de 2021
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
        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 2. JUSTIFICATIVA
        </p>
        <p style="text-align: justify;">
            {!! strip_tags($detalhe->justificativa) !!}
        </p>

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 3. ENQUADRAMENTO LEGAL
        </p>
        <p style="text-align: justify;">
           3.1. O presente Termo de Referência tem como base legal a Lei Federal nº 14.133/2021,
            especificamente o artigo 74, inciso V, que admite a inexigibilidade de licitação nas
            hipóteses de aquisição ou locação de imóvel cujas características de instalações e de
            localização tornem necessária sua escolha, desde que devidamente justificada pela
            Administração.
            <br>
            3.2. O procedimento observado obedece ao disposto no artigo 72, incisos I a VIII, bem
            como ao Decreto Municipal que regulamenta as contratações públicas, assegurando a
            realização das etapas essenciais, a motivação administrativa e a observância dos
            princípios constitucionais.
            <br>
            3.3. Nas palavras do ilustre professor Ronny Charles: “Quando a lei prevê hipóteses de
            contratação direta (dispensa e inexigibilidade) é porque admite que nem sempre a
            realização do certame levará à melhor forma de contratação pela Administração ou que,
            pelo menos, a sujeição do negócio ao procedimento formal e burocrático previsto pelo
            estatuto não serve eficaz ao atendimento do interesse público naquela hipótese
            específica.”
            <br>
            3.4. Nesse mesmo sentido, o nobre doutrinador Adilson Abreu Dallari destaca que: “Nem
            sempre, é verdade, a licitação leva uma contratação mais vantajosa. Não pode ocorrer,
            em virtude da realização do procedimento licitatório, é o sacrifício de outros valores e
            princípios consagrados pela ordem jurídica, especialmente o princípio da eficiência.”
            <br>
            3.5. No presente caso, a inexigibilidade de licitação mostra-se juridicamente adequada e
            necessária, uma vez que a escolha do imóvel decorre de requisitos específicos de
            localização, estrutura, acesso, dimensões e funcionalidade, devidamente comprovados
            por estudo técnico e declaração de indisponibilidade de imóveis próprios. Ainda assim, o
            procedimento deve observar todas as premissas fundamentais de uma contratação
            pública, como motivação, transparência, isonomia e avaliação da vantajosidade.
            <br>
            3.6. A contratação, via inexigibilidade de licitação, decorre da inviabilidade de
            competição, dada a singularidade do imóvel e as características que o tornam o único
            apto a atender plenamente à necessidade pública. A adoção da contratação direta, além
            de juridicamente respaldada, promove maior celeridade, reduz custos administrativos e
            viabiliza a instalação imediata do serviço, atendendo de forma eficiente ao interesse
            público.
            <br>
            3.7. Ressalta-se que a impossibilidade de adoção de critérios objetivos decorre da
            natureza singular do bem a ser contratado, haja vista que imóveis não são bens
            padronizados e suas características são únicas, impossibilitando o estabelecimento de
            parâmetros uniformes para competição em certame licitatório. Assim, a escolha
            fundamentada do imóvel demonstra-se a medida mais adequada, eficiente e compatível
            com o interesse público.
        </p>

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 4. APRESENTAÇÃO DA PROPOSTA DE PREÇOS
        </p>
        <p style="text-align: justify;">
            4.1. A proposta de preço deverá conter os seguintes elementos:
            <br>
            <span style="margin-left: 20px;">
                a) A proposta deve ser apresentada em papel timbrado (se Pessoa Jurídica) ou assinada
                formalmente (se Pessoa Física), datada e sem rasuras.
                <br>
                b) Discriminação do valor do aluguel mensal.<br>
                c) Valor total para a vigência do contrato.<br>
                d) No preço ofertado estarão incluídos todos os tributos, encargos sociais, trabalhistas,
                previdenciários e fiscais que recaiam sobre a propriedade.<br>
                e) Prazo de validade da proposta não poderá ser inferior a 60 (sessenta) dias.<br>
                f) A proposta que omitir o prazo de validade será considerada como válida pelo
                período de 60 (sessenta) dias.
            </span>
        </p>

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 5. DA ESPECIFICAÇÃO DO IMÓVEL
        </p>
        <p style="text-align: justify;">
            {!! strip_tags($processo->detalhe->especificacao_servicos_imovel) !!}
        </p>

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 6. DO REGIME DE EXECUÇÃO
        </p>
        <p style="text-align: justify;">
            6.1. A execução do objeto será realizada de forma indireta. <br>
            6.2. A execução do contrato terá início imediato após a assinatura do instrumento
            contratual, formalizando-se através dos seguintes atos:
            <br>
            <span style="margin-left: 20px;">
                a) Vistoria Inicial: Será realizada uma vistoria conjunta entre o fiscal do contrato nomeado
                pela Administração e o LOCADOR (ou seu representante legal), para aferição das
                condições do imóvel. <br>
                b) Termo de Recebimento Provisório/Entrega das Chaves: Após a vistoria, e estando o
                imóvel em conformidade com as exigências deste Termo de Referência, será lavrado o
                Termo de Recebimento Provisório e efetuada a entrega das chaves, momento em que o
                imóvel passa à posse direta da Administração.
                <br>
            </span>
            6.3. Considera-se executado o objeto mensalmente mediante a disponibilização contínua
            e ininterrupta do imóvel ao Órgão Locatário, em condições plenas de uso, habitabilidade
            e segurança, conforme as especificações descritas neste Termo.
        </p>

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 7. DA DESCRIÇÃO DA SOLUÇÃO
        </p>
        <p style="text-align: justify;">
            7.1. A solução identificada como a mais vantajosa e adequada para a Administração,
            consiste na {!! strip_tags($processo->objeto) !!} <br>
            7.2. A opção pela locação, em detrimento da aquisição ou construção de imóvel próprio,
            fundamenta-se na inexistência de imóveis públicos vagos adequados e o alto custo inicial
            para aquisição de um imóvel novo no mercado. <br>
            7.3. A solução de locação recai especificamente sobre este imóvel pois, conforme
            demonstrado, é o único que atende cumulativamente aos requisitos de localização
            estratégica, infraestrutura física com layout compatível com as necessidades do órgão,
            dispensando reformas estruturais onerosas e demoradas, além de atender aos requisitos
            de instalações elétricas, lógicas e hidráulicas demandadas para o funcionamento. <br>
            7.4. A solução abrange não apenas a disponibilização do espaço físico, mas a
            manutenção da habitabilidade do imóvel pelo LOCADOR durante toda a vigência
            contratual, garantindo a sustentabilidade da operação administrativa sem interrupções.
        </p>

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 8. DA VIGÊNCIA
        </p>
        <p style="text-align: justify;">
            8.1. O período de vigência do instrumento contratual será de {{ $vigencia_formatada }},
            contados da data de sua assinatura, podendo este ser rescindido ou ter seu prazo
            prorrogado na forma da Lei.
        </p>

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 9. REQUISITOS DA CONTRATAÇÃO
        </p>
        <p style="text-align: justify;">
            9.1. Deverão serem apresentados os seguintes documentos:
            <br>
            <span style="margin-left: 20px;">
                a) Se Pessoa Física:
                <br>
                <span style="margin-left: 20px;">
                    o Cédula de Identidade (RG) e CPF ou CNH; <br>
                    o Comprovante de residência;<br>
                    o Certidão negativa de débitos federais; <br>
                    o Certidão negativa de débitos estaduais; <br>
                    o Certidão negativa de débitos municipais;<br>
                </span>
                b) Se Pessoa Jurídica:
                <br>
                <span style="margin-left: 20px;">
                    o Contrato Social ou Estatuto em vigor; <br>
                    o Documentos dos sócios/administradores;<br>
                    o Prova de inscrição no CNPJ;<br>
                    o Certidão negativa de débitos federais;<br>
                    o Certidão negativa de débitos estaduais;<br>
                    o Certidão negativa de débitos municipais;<br>
                    o Certidão de Regularidade do FGTS (CRF);<br>
                    o Certidão Negativa de Débitos Trabalhistas (CNDT);<br>
                    o Certidão de Falência e Concordata;<br>
                </span>
                c) Comprovação da Propriedade:
                <br>
                <span style="margin-left: 20px;">
                    o Certidão de Inteiro Teor da Matrícula do Imóvel: Emitida pelo Cartório de Registro
                    de Imóveis, atualizada, comprovando a propriedade livre e desembaraçada de
                    ônus que impeçam a locação. <br>
                    o Habite-se: Comprovação de regularidade da construção perante a prefeitura, se
                    aplicável.
                </span>
            </span>
        </p>

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 10. MODELO DE GESTÃO DO CONTRATO
        </p>
        <p style="text-align: justify;">
            10.1. A fiscalização da contratação, decorrente desta inexigibilidade de licitação, será
            acompanhada e fiscalizada por servidor da Administração, especialmente designados,
            nos termos do artigo 117 da Lei Federal 14.133/2021.
            <br>
            10.2. A contratante deverá indiciar um responsável legal, através de documento
            encaminhado para o e-mail da prefeitura Municipal ou protocolado pessoalmente no
            setor de licitações e contratos deste município, indicando os respectivos contatos (e-mail,
            celular e Whatsapp), com poderes para representá-lo perante essa municipalidade na
            execução do contrato decorrente da inexigibilidade de licitação objeto deste termo de
            referência.
        </p>

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 11. DO RECEBIMENTO DO OBJETO E DOS CRITÉRIOS PARA MEDIÇÃO E PAGAMENTO
        </p>
        <p style="text-align: justify;">
            11.1. O recebimento do objeto do contrato, decorrente da referida inexigibilidade de
            licitação, se dará:
            <br>
            <span style="margin-left: 20px;">
                a) Recebimento Provisório: Ocorrerá no momento da entrega das chaves pelo
                LOCADOR, mediante a assinatura de Termo de Recebimento Provisório. O recebimento
                provisório será precedido, obrigatoriamente, por uma Vistoria Inicial detalhada, realizada
                pelo responsável designado juntamente com o Locador, onde será lavrado o Laudo de
                Vistoria. <br>
                b) Recebimento Definitivo: Ocorrerá em até 10 (dez) dias úteis após o recebimento
                provisório, mediante a emissão de Termo de Recebimento Definitivo pelo responsável
                designado, atestando que o imóvel atende a todas as especificações deste Termo de
                Referência e encontra-se em plenas condições de uso e funcionamento. Caso sejam
                constatadas irregularidades ou divergências com o Laudo de Avaliação ou com as
                especificações deste TR durante a vistoria, o recebimento será suspenso até que o
                LOCADOR providencie as adequações necessárias.
            </span>
            <br>
            11.2. A medição dos serviços será realizada mensalmente, considerando-se executado o
            objeto pela disponibilização do imóvel à Administração durante o período de 30 (trinta)
            dias. <br>
            11.2.1. A medição será formalizada mediante atesto na Nota Fiscal ou Recibo pelo
            responsável designado, confirmando que o imóvel esteve disponível e em condições de
            uso durante o período faturado. <br>
            11.3. O pagamento será realizado no prazo máximo de até 30 (trinta) dias, contados a
            partir do recebimento da Nota Fiscal/Fatura ou Recibo devidamente atestado, através de
            ordem bancária, para crédito em banco, agência e conta corrente indicados pelo
            contratado, respeitada a ordem cronológica. <br>
            11.3.1. Para a efetivação do pagamento, o LOCADOR deverá apresentar Nota Fiscal (se
            Pessoa Jurídica) ou Recibo (se Pessoa Física), discriminando o período de locação, e
            comprovação de manutenção das condições de habilitação exigidas na contratação.
        </p>

       <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 12. DOTAÇÃO ORÇAMENTÁRIA
        </p>
        <p style="text-align: justify;">
            12.1. Os custos com a presente contratação correrão por conta da seguinte dotação orçamentária:
        </p>
        <table style="border-collapse: collapse; width: 100%; border: 1px solid black;">
            <tr>
                <!-- Coluna da esquerda -->
                <td style="vertical-align: top; padding: 10px;">
                    {!! str_replace('<p>', '<p style="text-indent:30px; text-align: justify;">', $detalhe->dotacao_orcamentaria) !!}
                </td>
            </tr>
        </table>

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 13. FORMA E CRITÉRIO DE SELEÇÃO DO FORNECEDOR/PRESTADOR
        </p>
        <p style="text-align: justify;">
            13.1. A presente contratação fundamenta-se no Art. 74, inciso V, da Lei nº 14.133/2021,
            que autoriza a inexigibilidade de licitação para a aquisição ou locação de imóvel cujas
            características de instalações e de localização tornem necessária sua escolha. <br>
            13.2. A escolha do imóvel especificado pauta-se nos seguintes critérios técnicos e
            logísticos:
            <span style="margin-left: 20px;">
                a) O imóvel situa-se em área que garante o fácil acesso ao público-alvo. <br>
                b) O imóvel possui divisões internas, acessibilidade e instalações elétricas/hidráulicas
                que comportam a estrutura administrativa sem a necessidade de reformas estruturais
                vultosas. <br>
                c) Restou demonstrada a inexistência de imóveis públicos vagos e disponíveis na mesma
                região que pudessem atender à finalidade pretendida, conforme certificado nos autos.
            </span>
        </p>

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 14. DA RAZÃO E ESCOLHA DO CONTRATADO
        </p>
        <p style="text-align: justify;">
            14.1. {!! strip_tags($processo->detalhe->razao_escolha_contratado) !!}
        </p>

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
            16.1. O(A) CONTRATADO(A) obriga-se a: <br>
            16.1.1. Entregar o imóvel em perfeitas condições de uso; <br>
            16.1.2. Arcar com impostos, taxas e tributos incidentes sobre a propriedade (IPTU, taxas
            municipais etc.); <br>
            16.1.3. Manter a titularidade e regularidade jurídica do imóvel durante toda a vigência
            contratual; <br>
            16.1.4. Garantir a posse pacífica do imóvel à Administração.
        </p>
        {{ $processo->detalhe->obrigacoes_contratado_extras }}

        <p style="display: flex; align-items: center; font-weight: bold; ">
            <img src="{{ public_path('icons/grafico.png') }}" width="20" style="margin-right: 10px;"> 17. OBRIGAÇÕES DA CONTRATANTE 
        </p>
        <p style="text-align: justify;">
            17.1. A CONTRATANTE obriga-se a: <br>
            17.1.1. Utilizar o imóvel exclusivamente para a finalidade contratada; <br>
            17.1.2. Efetuar o pagamento dos aluguéis no prazo ajustado; <br>
            17.1.3. Realizar pequenas adaptações internas que não comprometam a estrutura do
            imóvel, se necessárias à atividade.
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
            ocorrência da anualidade;<br>
            19.2. Nos reajustes subsequentes ao primeiro, o interregno mínimo de um ano será
            contado a partir dos efeitos financeiros do último reajuste;<br>
            19.3. No caso de atraso ou não divulgação do índice de reajustamento, o
            CONTRATANTE pagará à CONTRATADA a importância calculada pela última variação
            conhecida, liquidando a diferença correspondente, tão logo seja divulgado o índice
            definitivo. Fica a CONTRATADA obrigada a apresentar memória de cálculo referente ao
            reajustamento de preços do valor remanescente, sempre que este ocorrer;<br>
            19.4. Nas aferições finais, o índice utilizado para reajuste será, obrigatoriamente, o
            definitivo;<br>
            19.5. Caso o índice estabelecido para reajustamento venha a ser extinto ou de qualquer
            forma não possa mais ser utilizado, será adotado, em substituição, o que vier a ser
            determinado pela legislação então em vigor;<br>
            19.6. Na ausência dê previsão legal quanto ao índice substituto, as partes elegerão novo
            índice oficial, para reajustamento do preço do valor remanescente, por meio de termo
            aditivo;<br>
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
            <p style="line-height: 1.6">Em conformidade com a legislação aplicável, encaminhamos o Presente Estudo
                Despacho para a Autoridade Competente para a Autorização de Abertura de Procedimento de
                Licitação.
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
                        <span style="font-weight: 700;">Assinado digitalmente por</span><br>
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
