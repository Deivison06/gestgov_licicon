<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>PARECER TÉCNICO - Processo {{ $processo->numero_processo ?? $processo->id }}</title>
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
{{-- ====================================================================== --}}
{{-- BLOCO 1: CAPA DO DOCUMENTO --}}
{{-- ====================================================================== --}}
<div id="cover-page">
    <img src="{{ public_path('icons/capa-documento.png') }}" alt="Martelo da Justiça" class="cover-image">
    <div class="cover-title">
        PARECER TÉCNICO
    </div>
</div>
@php
    // Verifica se a variável $assinantes existe e tem itens
    $hasSelectedAssinantes = isset($assinantes) && count($assinantes) > 0;
@endphp

{{-- QUEBRA DE PÁGINA --}}
<div class="page-break"></div>

<div>
    <h4 style="text-align: center;">
        ATA COMISSÃO DE LICITAÇÃO ANÁLISE LEGAL
    </h4>

    <h4>
        PROCESSO ADMINISTRATIVO: {{ $processo->numero_processo }} <br>
        INEXIGIBILIDADE DE LICITAÇÃO No {{ $processo->numero_procedimento }} – CPL <br>
        DA JUSTIFICATIVA DA INEXIGIBILIDADE:
    </h4>

    <p style="text-align: justify">
        Atendendo à solicitação, manifestamos análise sobre o Processo em
        epígrafe. A matéria é apreciada tomando como base as especificações da Lei
        de Licitações nº 14.133/2021, Acordão, Resoluções e Instruções Normativas
        dos órgãos de Controle Externo. As disposições deste parecer versam sobre
        o princípio da legalidade, razoabilidade, eficiência, e impessoalidade além
        de observações quanto o formalismo processual. Feitas as considerações
        iniciais, passo ao exame de estilo.
    </p>
    <h4>DA FORMALIZAÇÃO DA DEMANDA E AUTORIZAÇÃO DE ABERTURA DE
        PROCESSO:</h4>
    <p style="text-align: justify">
        Em relação ao documento de formalização de demanda e a autorização da
        autoridade competente para abertura de processo de contratação, verificase as devidas formalizações encartadas nos autos do processo em epígrafe.
    </p>
    <h4>DA COMPATIBILIDADE DE PREVISÃO DOS RECURSOS ORÇAMENTÁRIOS:</h4>
    <p style="text-align: justify">
        Foi demonstrado, através de consulta ao setor contábil, a previsão de
        recursos orçamentários para custear as despesas com o objeto desta
        inexigibilidade de licitação, bem como atestado a disponibilidade financeira.
    </p>
    <h4>DA RAZÃO DA ESCOLHA DO CONTRATADO: </h4>
    <p style="text-align: justify">
        A escolha do imóvel, fundamenta-se na inviabilidade de competição
        estabelecida pelo Art. 74, inciso V, da Lei nº 14.133/2021, em razão da
        necessidade precípua da Administração em instalar Órgão em região que
        garanta fácil acesso ao público e centralidade geográfica. Após prospecção
        no mercado imobiliário local, verificou-se que apenas este imóvel reúne as
        condições de metragem, acessibilidade e infraestrutura imediata
        necessárias para o pleno funcionamento das atividades administrativas,
        tornando a escolha essencial e a competição inviável.
    </p>
    <h4>DA JUSTIFICATIVA DOS PREÇOS: </h4>
    <p style="text-align: justify">
        Em conformidade com o Art. 72, inciso VI da Lei 14.133/2021, foi elaborado o
        Laudo de Avaliação, que atesta a compatibilidade do valor da
        locação/aquisição com os parâmetros praticados no mercado imobiliário
        para a região. O valor proposto mostra-se justo e vantajoso, considerando o
        estado de conservação e a localização estratégica do bem.
    </p>
    <h4>PARECER TÉCNICO DA COMISSÃO DE CONTRATAÇÃO:</h4>
    <p style="text-align: justify">
        Face ao atendimento de todos os pré-requisitos legais exigidos no artigo 72
        e seus incisos, entendemos que há presente o atendimento dos requisitos
        formais para a contratação. Sendo assim, entendemos que não há
        impedimento de ordem legal para o acolhimento da postulação da
        inexigibilidade.
    </p>
</div>

{{-- Bloco de data e assinatura --}}
<div class="footer-signature">
    {{ $processo->prefeitura->cidade }},
    {{ \Carbon\Carbon::parse($dataSelecionada)->translatedFormat('d \d\e F \d\e Y') }}
</div>

@if ($hasSelectedAssinantes && !empty($assinantes))
    <div style="margin-top: 40px; text-align: center;">
        @foreach ($assinantes as $assinante)
            <br>
            <div class="signature-block" style="display: inline-block; margin: 0 40px;">
                ___________________________________<br>
                <p style="line-height: 1.2;">
                    {{ $assinante['responsavel'] }} <br>
                    <span>{{ $assinante['unidade_nome'] }}</span>
                </p>
            </div>
        @endforeach
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

{{-- QUEBRA DE PÁGINA --}}
<div class="page-break"></div>

<div>
    <h4>DESPACHO</h4>

    <p>
        Ao(À) Exmo(a). Sr(a). <br>
        XXXXXXXXXXXXXXXXXXXXX <br>
        Procurador Geral do Município<br>
        {{ $processo->prefeitura->cidade }}
    </p>

    <p style="text-align: justify">Assunto: Emissão de Parecer Jurídicoo</p>

    <p style="text-align: justify; text-indent: 30px;">Prezado(a) Senhor(a),</p>

    <p style="text-align: justify; text-indent: 30px;">
        Solicitamos parecer jurídico referente à {!! strip_tags($processo->objeto) !!} através do
        Processo Administrativo nº {{ $processo->numero_processo }}, Modalidade: Inexigibilidade de
        Licitação nº {{ $processo->numero_procedimento }}, informamos que as despesas correrão por conta
        dos recursos do
        XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
        XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
        XXXXXXXXXXXXXXXXXXXXXXX.
    <p>
    <p style="text-align: justify; text-indent: 30px;">
        Anexamos a esta, propostas de preços apresentadas pelas
        empresas/profissionais, com a cotação para o objeto em questão,
        documentação da empresa/profissional de melhor proposta e minuta do
        contrato.
    <p>
    <p style="text-align: justify; text-indent: 30px;">
        Devido à complexidade Jurídica no sentido da contratação,
        indagamos esta Procuradoria para consulta sobre a legalidade da
        contratação com inexigibilidade de licitação, sendo o parecer favorável
        pedimos ainda análise da Minuta Contratual.
        <br>
        Segue em anexo todo Processo Administrativo contendo a Solicitação de
        despesa da Unidade requisitante, razão da escolha do contratado,
        documentação para habilitação jurídica, fiscais e trabalhistas, indicação
        de recursos orçamentários e minuta de contrato para a devida.
    <p>
    <p style="text-align: justify; text-indent: 30px;">
        Caso opine favoravelmente pela contratação favor encaminhar parecer
        jurídico favorável para que a autoridade superior autorize a contratação e
        proceda com a devida publicidade, nos termos do artigo 72, parágrafo
        único da Lei Federai 14.133/2021.
    <p>
    <p style="text-align: justify; text-indent: 30px;">
        Encaminhamos em anexo a minuta do Contrato para análise complementar
    </p>

    {{-- Bloco de data e assinatura --}}
    <div class="footer-signature">
        {{ $processo->prefeitura->cidade }},
        {{ \Carbon\Carbon::parse($dataSelecionada)->translatedFormat('d \d\e F \d\e Y') }}
    </div>

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

</body>

</html>
