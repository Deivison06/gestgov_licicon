<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>ATA - Processo {{ $processo->numero_processo ?? $processo->id }}</title>
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
            ATA
        </div>
    </div>

    <div class="page-break"></div>

    {{-- ====================================================================== --}}
    {{-- BLOCO 2: CONTEÚDO DO DOCUMENTO --}}
    {{-- ====================================================================== --}}
    <div>
        <h4 style="text-align: center">ATA DE RECONHECIMENTO DA DISPENSA DE LICITAÇÃO</h4>

        <p style="text-align: justify;">
            Assunto: reconhecimento e solicitação de Ratificação de Dispensa de Licitação - (Artigo 78 da Lei nº 14.133/2021).  
        </p>

        <p>
            Processo Administrativo nº {{ $processo->numero_processo }} <br>
            Dispensa de Licitação nº {{ $processo->numero_procedimento }} <br>
            REFERENTE: <br>
            {{ $processo->finalizacao->valor_total }}.
        </p>
        <p style="text-align: justify;">
            BASE LEGAL: Art. 75, inciso I, da Lei nº 14.133 de 01 de ABRIL de 2021
        </p>
        @php
             use Carbon\Carbon;

            $data = Carbon::parse($dataSelecionada);

            $formatter = new \NumberFormatter('pt_BR', \NumberFormatter::SPELLOUT);

            $dia = $data->day;
            $mes = mb_strtoupper($data->translatedFormat('F')); // mês por extenso
            $anoExtenso = $formatter->format($data->year);
        @endphp
        <p style="text-align: justify; text-indent: 30px;">
            Aos {{ $dia }} dias do mês de {{ $mes }} de {{ $anoExtenso }}, reuniu-se a
            Comissão de Licitação, para deliberar sobre a {!! strip_tags($processo->objeto) !!}, foi enviado a esta
            comissão de Contratação propostas de preços: {{ $processo->finalizacao->empresas_participantes }} após análise e verificação dos preços propostos, a
            comissão julgou e decidiu em favor da empresa {{ $processo->finalizacao->razao_social }}, 
            CNPJ: {{ $processo->finalizacao->cnpj_empresa_vencedora }}, respaldado no Art. 75, inciso I, da Lei nº
            14.133 de 01 de ABRIL de 2021 e demais documentos objeto do Processo.
        </p>

        <h4 style="text-align: center">JUSTIFICATIVA DA CONTRATAÇÃO</h4>

        <p style="text-align: justify; text-indent: 30px;">
           A contratação encontra-se respaldado no Art. 75, inciso I, da Lei nº 14.133 de 01 de
            ABRIL de 2021, que viabiliza a contratação em comento, diante da realidade, a própria Lei de
            Licitação se preocupou prevendo a contratação nos casos que se caracterizam como dispensa.
        </p>
        <table style="width: 100%; border-collapse: collapse; page-break-inside: auto;">
            <tr style="page-break-after: auto;">
                <td style="width: 40%;"></td>
                <!-- Conteúdo da primeira célula -->
                </td>
                <td style="width: 50%; text-align: center; vertical-align: top; ">
                    ART. 75, INCISO II, DA LEI Nº 14.133/21 DE 01 DE ABRIL DE 2021.
                </td>
            </tr>

            <tr style="page-break-after: auto;">
                <td style="width: 40%;"></td>
                <!-- Conteúdo da primeira célula -->
                </td>
                <td style="width: 50%; text-align: justify; vertical-align: top; ">
                    “Art. 75. É dispensável a licitação:
                    <br><br>
            </tr>
            <tr style="page-break-after: auto;">
                <td style="width: 40%;"></td>
                <!-- Conteúdo da primeira célula -->
                </td>
                <td style="width: 50%; text-align: justify; vertical-align: top; ">
                    I - para contratação que envolva valores
                    inferiores a R$ 125.451,15 vide O Decreto nº
                    12.343/2024, de 30 de dezembro de 2021, no
                    caso de outros serviços e compras;
                </td>
            </tr>
        </table>

        <p style="text-align: justify; text-indent: 30px;">
            A dispensa de Licitação se dá pela grade necessidade {!! strip_tags($processo->objeto) !!}
        </p>
        <p style="text-align: justify; text-indent: 30px;">
            A contratação atende as normas legais, onde a contratação da empresa dar-se-á devido a
            mesma ter apresentado menor preço dentre aquelas que apresentaram propostas para o
            fornecimento dos produtos.
        </p>
        <p style="text-align: justify; text-indent: 30px;">
            Nota-se que o valor da futura contratação está dentro do limite previsto em lei, com isto,
            objetiva-se atender aos princípios da legalidade, economicidade e celeridade, na realização da
            presente contratação.
        </p>
        <p style="text-align: justify; text-indent: 30px;">
            O legislador entendeu que, em função do pequeno valor financeiro envolvido, não se
            justificaria a realização de um procedimento licitatório pela Administração. Sobre o tema, o
            professor Marçal Justen Filho (2004, p. 236) assevera:
        </p>
        
        <table style="width: 100%; border-collapse: collapse; page-break-inside: auto;">
            <tr style="page-break-after: auto;">
                <td style="width: 40%;"></td>
                <!-- Conteúdo da primeira célula -->
                </td>
                <td style="width: 50%; text-align: justify; vertical-align: top; ">
                    “A pequena relevância econômica da contratação
                    não justifica gastos com uma licitação comum. A
                    distinção legislativa entre concorrência, tomada de
                    preços e convite se filia não só à dimensão
                    econômica do contrato. A lei determinou que as
                    formalidades prévias deverão ser proporcionais às
                    peculiaridades do interesse e da necessidade
                    pública. Por isso, tanto mais simples serão as
                    formalidades e mais rápido o procedimento licitatório
                    quanto menor for o valor a ser despendido pela
                    Administração Pública.”
                </td>
            </tr>
        </table>

        <h4>III – DA JUSTIFICATIVA DA DISPENSA</h4>
        @php
            $html = $processo->detalhe->justificativa;

            // Substitui <p> com estilo
            $html = preg_replace(
                '/<p([^>]*)>/i',
                '<p style="text-indent:30px; text-align: justify;">',
                $html
            );

            // Substitui <strong ...> por <span style="font-weight:bold;">
            $html = preg_replace(
                '/<strong[^>]*>/i',
                '<span style="font-weight: bold;">',
                $html
            );

            // Fecha corretamente
            $html = str_replace('</strong>', '</span>', $html);
        @endphp

        <div>
            {!! $html !!}
        </div>

        <h4>IV – DA RAZÃO DA ESCOLHA DO FORNECEDOR OU EXECUTANTE</h4>
        <p style="text-align: justify; text-indent: 30px;">
            O serviço será disponibilizado pela empresa supracitada é compatível e não
            apresenta diferença que venha a influenciar na escolha, ficando está vinculada apenas à
            verificação do critério do menor preço.
        </p>
        <h4>V – DA JUSTIFICATIVA DO PREÇO</h4>
        <p style="text-align: justify; text-indent: 30px;">
            O critério do menor preço deve presidir a escolha do adjudicatário direto como regra
            geral, e o meio de aferi-lo está em juntar aos autos do respectivo processo.
        </p>
        
        <h4>VI – DA ESCOLHA</h4>
        <p style="text-align: justify; text-indent: 30px;">
             A empresa escolhida neste processo para sacramentar a contratação de
            fornecimento dos produtos pretendidos, foi:
            {{ $processo->finalizacao->razao_social }}, 
            CNPJ: {{ $processo->finalizacao->cnpj_empresa_vencedora }} foi
            de R$ {{ $processo->finalizacao->valor_total }}.
        </p>
        <h4>VII – CONCLUSÃO</h4>
        <p style="text-align: justify; text-indent: 30px;">
            Por tudo isso, viemos RECONHECER o procedimento de Dispensa de
            Licitação, e de forma a cumprir o disposto no art. 75, da mesma lei, e tendo em vista
            o constante do presente processo, o qual foi submetido a exame da douta
            Procuradoria Municipal que emitiu parecer favorável, apresentaremos a presente
            para RATIFICAÇÃO para HOMOLOGAÇÃO do Excelentíssimo Prefeito Municipal, e
            posterior publicação no Diário Oficial.
        </p>
        <p style="text-align: center; text-indent: 30px;">
            À deliberação do Senhor Prefeito Municipal para homologação.
        </p>
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
    </div>

    {{-- QUEBRA DE PÁGINA --}}
    <div class="page-break"></div>
    <div>
        <h4>DESPACHO</h4>
        <p>
            Ao(À) Ilmo(a). Sr(a).<br>
            <span>{{ $processo->prefeitura->autoridade_competente }}</span>
            <br>
            Prefeito Municipal
        </p>

        <p style="text-align: justify;">
            Assunto: Emissão de Parecer Jurídico
        </p>
        <p style="text-indent: 30px; text-align: justify; ">
            Prezado(a) Senhor(a),
        </p>
        <p style="text-indent: 30px; text-align: justify; ">
            Solicitamos parecer jurídico referente à {!! strip_tags($processo->objeto) !!} 
            através do Processo Administrativo nº {{ $processo->numero_processo }}, Modalidade: Dispensa de
            Licitação nº {{ $processo->numero_procedimento }}, informamos que as despesas correrão por conta dos recursos do
            {!! strip_tags($processo->detalhe->dotacao_orcamentaria) !!}
        </p>
        <p style="text-indent: 30px; text-align: justify; ">
            Devido à complexidade Jurídica no sentido da contratação com base no Art. 75, inciso II,
            da Lei nº 14.133/21, indagamos esta Procuradoria para consulta sobre a legalidade da contratação
            com dispensa de licitação.
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
