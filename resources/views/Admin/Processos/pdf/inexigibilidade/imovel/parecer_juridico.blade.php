<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>PARECER JURÍDICO - Processo {{ $processo->numero_processo ?? $processo->id }}</title>
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
            PARECER JURÍDICO
        </div>
    </div>

    {{-- QUEBRA DE PÁGINA --}}
    <div class="page-break"></div>

   <div>
       <h4 style="text-align: center">
           PARECER JURÍDICO <br>
           PROCESSO ADMINISTRATIVO N° {{ $processo->numero_processo }} <br>
           INEXIGIBILIDADE N° {{ $processo->numero_procedimento }}
       </h4>

       <p>EMENTA: INEXIGIBILIDADE DE LICITAÇÃO - LEGALIDADE</p>

       <h4>I - DO RELATÓRIO</h4>
       <p style="text-align: justify">
            Trata-se de solicitação de Parecer Jurídico acerca da legalidade da
            contratação da Empresa/Pessoa Física conforme documentação anexa
            referente a {!! strip_tags($processo->objeto) !!} <br>
            Deve ser ressaltado que a análise da Procuradoria repercute estritamente
            sobre a apreciação jurídica da contratação, não havendo qualquer opinião
            sobre o mento administrativo.
            Esse é o resumo dos fatos, passamos a nos manifestar.
       </p>

       <h4>II - DA FUNDAMENTAÇÃO</h4>
       <p style="text-align: justify">
            Em razão da solicitação de parecer jurídico foram os autos encaminhados a
            esta Assessoria Jurídica, na forma do art. 53, da Lei nº 14.133/2021. Este
            Parecer, portanto, tem o escopo de assistir a autoridade assessorada no
            controle interno e prévio da legalidade dos atos administrativos praticados
            na fase preparatória do procedimento, especificamente na demonstração
            formal do atendimento dos requisitos exigidos, em consonância com art. 72,
            III, da Lei 14.133/2021 e o art. 6º, III. Assim, o controle prévio de legalidade se
            dá em função do exercício da competência da análise jurídica da futura
            contratação, não abrangendo, portanto, os demais aspectos envolvidos,
            como os de natureza técnica, mercadológica ou de conveniência e
            oportunidade, sobretudo por já constar nos autos a determinação para
            contratação por via de inexigibilidade, pela autoridade administrativa, antes
            mesmo da presente manifestação jurídica. De fato, presume-se que as
            especificações técnicas contidas no presente processo, inclusive quanto ao
            detalhamento do objeto da contratação, suas características, quantitativos,
            escolha do imóvel, requisitos para a contratação e a avaliação do valor da
            locação e o mercado imobiliário respectivo, tenham sido regularmente
            determinadas pelo setor competente do órgão, com base em parâmetros
            técnicos objetivos, para a melhor consecução do interesse público, haja vista
            tratar-se da discricionariedade do órgão assessorado, cujas decisões devem
            ser motivadas nos autos. É importante lembrar que a teoria dos motivos
            determinantes preconiza que os atos administrativos, quando motivados,
            ficam vinculados aos motivos expostos, para todos os efeitos jurídicos. Até
            mesmo sua validade dependerá da efetiva existência dos motivos
            apresentados. Recomenda-se, por isso, especial cautela quanto aos seus
            termos, que devem ser claros, precisos e corresponder à real demanda da
            Municipalidade, sendo inadmissíveis especificações que não agreguem
            valor ao resultado da contratação, ou superiores às necessidades do
            Município, ou, ainda, que estejam defasadas tecnológica e/ou
            metodologicamente. Por conseguinte, e por zelo, no desempenho da função
            de assessoramento, cumpre-nos alertar à autoridade administrativa sobre a
            importância da devida motivação de seus atos, na medida em que recairá
            sobre esta, a responsabilidade. Como suporte, o entendimento do TCU.
            Vejamos: 
            <br>
            Os atos administrativos discricionários dão margem de liberdade de ação
            para o gestor agir pela sua conveniência e oportunidade, devendo, porém,
            observar a lei, a finalidade pública, a moralidade administrativa, a
            razoabilidade e o interesse público. (Acórdão TCU nº 1234/2008-Plenário).
            De outro lado, cabe esclarecer que não é papel do órgão de assessoramento
            jurídico exercer a auditoria quanto à competência de cada agente público
            para a prática de atos administrativos, nem de atos já praticados.
            <br>
            Incumbe, isto sim, a cada um destes observar se os seus atos estão dento do
            seu espectro de competências. Além disso, deve-se salientar que
            determinadas observações são feitas sem o caráter vinculativo, mas em prol
            a segurança da própria autoridade assessorada a quem incumbe, dentro da
            margem de discricionariedade que lhe é conferida pela lei, avaliar e acatar,
            ou não tais ponderações.
            <br>
            Passamos a análise jurídica. Feita tal explanação, a princípio, cumpre
            esclarecer que o artigo 37, XXI, da Constituição Federal de 1988, estabelece
            para Administração Pública a obrigatoriedade de licitar. No entanto, com
            base nos princípios nela dispostos, no ordenamento jurídico pátrio admite
            exceções, permitindo, em casos extraordinários, a contratação direta por
            dispensa ou inexigibilidade de licitação.
            <br>
            Sobre a temática de Inexigibilidade de Licitação, a Lei n° 14.133/2021,
            disciplinou que é inexigível a licitação quando inviável a competição, em
            especial nos casos de locação de imóvel cujas características de instalação e
            de localização tornem necessárias sua escolha. Como se apura, nos
            contratos de locação de imóvel pelo Poder Público, a nova lei de licitações,
            disciplina as formas de seleção processo de licitação como regra, através do
            artigo 51 ou por inexigibilidade de licitação quando comprovada a
            singularidade do bem, por intermédio do artigo 74, V.
            <br>
            O artigo 74 da Lei Federal nº 14.133/2021, elenca de forma exemplificativa,
            hipóteses de inexigibilidade de licitação, ou seja, casos em que a licitação é
            inviável, afastando a licitação para atender o interesse público.
            <br>
            Nesse passo, torna-se possível a contratação direta em razão da inexigível
            licitação para locação de imóvel cujas características de instalações e de
            localização tornem necessária sua escolha, consoante redação do art. 74, V,
            da Lei Federal nº 14.133/2021, atendidos os requisitos do seu § 5º. Vejamos:
            <br>
            Art. 74. É inexigível a licitação quando inviável a competição, em especial nos
            casos de: (...) V - aquisição ou locação de imóvel cujas características de
            instalações e de localização tornem necessária sua escolha. (...) § 5º Nas
            contratações com fundamento no inciso V do caput deste artigo, devem ser
            observados os seguintes requisitos: 
            <br>
            I - avaliação prévia do bem, do seu
            estado de conservação, dos custos de adaptações, quando imprescindíveis
            às necessidades de utilização, e do prazo de amortização dos investimentos;
            <br>
            II - certificação da inexistência de imóveis públicos vagos e disponíveis que
            atendam ao objeto; III - justificativas que demonstrem a singularidade do
            imóvel a ser comprado ou locado pela Administração e que evidenciem
            vantagem para ela.
            <br>
            Pela análise do tanto disposto acima, identificam-se requisitos essenciais
            para que seja caracterizada a inviabilidade de competição, os quais devem
            ser destacados no presente procedimento de contratação fundada na
            inexigibilidade de licitação do art. 74, V, da Lei nº 14.133/2021.
            <br>
            O imóvel deve ser destinado a atender finalidades precípuas da
            Administração, cujos aspectos relacionados com os fatores “instalações” e
            “localização” devem ser efetivamente relevantes para sua escolha, mediante
            justificativas que demonstrem a sua singularidade, restando este como
            único imóvel capaz de satisfazer o interesse público.
            <br>
            Ademais, o preço deve ser compatível com o praticado pelo mercado, sendo
            necessário comprovar essa compatibilidade mediante prévia avaliação do
            bem, incluindo quanto ao seu estado de conservação, dos custos de
            adaptações, quando necessários, bem como, do prazo de amortização dos
            investimentos.
            <br>
            Neste sentido é o entendimento do administrativista Matheus Carvalho: (...)
            A contratação direta ocorrerá quando as características de determinado
            imóvel, incluindo a sua localização, forem essenciais para cumprir a
            finalidade da Administração Pública. (...) A escolha entre a compra e a
            locação precisa ser fundamentada com estudo técnico (art. 44).
            <br>
            A contratação direta deve seguir os requisitos previstos no §5º, sendo
            necessária a avaliação prévia do bem, a certificação de inexistência de
            imóveis públicos que atendam às necessidades da Administração e as
            justificativas acerca da singularidade do imóvel.” (Nova Lei de Licitações
            Comentada, 2ª edição, Salvador: JusPodivm, 2022, p.312). Destarte, via
            regra, a locação de imóveis pela Administração Pública por meio da
            contratação direta, em razão da inexigibilidade de licitação, é plenamente
            possível, desde que sejam observadas as determinações legais.
            <br>
            Assim, observa-se que o Órgão demandante, traz a justifica no item 3 do
            Termo de Referência, a necessidade da locação em razão da sua destinação
            nos seguintes termos: “A educação é um direito fundamental previsto no art.
            6º, da Constituição Federal, sendo dever de todos os entes da federação,
            notadamente os municípios, proporcioná-la.
            <br>
            Outrossim, o inciso I, do art. 208, da CF/88, preleciona que o dever do Estado
            para com a educação será efetivado através da garantia de “educação básica
            obrigatória e gratuita dos 4 (quatro) aos 17 (dezessete) anos de idade. Deste
            modo, torna-se essencial a locação do imóvel descrito no Item 1.2 do
            presente instrumento, face à necessidade descrita no objeto.
            <br>
            Neste sentido, considerando que o Município não possui espaços para a
            instalação do órgão pretendido, o contrato é indispensável ao atendimento
            desta necessidade. Em continuidade, de forma expressa, segue justificando
            que o preço é compatível com o valor de mercado, segundo avaliação prévia
            mediante laudos de avaliação, acompanhado de registro fotográfico,
            conforme documentos acostados.
            <br>
            Diante desse quadro, constata-se a necessidade da locação por parte da
            Prefeitura Municipal, vez que foram preenchidos os requisitos relacionados
            ao imóvel que pretende alugar, bem como a sua localização, como
            justificativa da escolha do imóvel objeto da contratação, entendido, pela
            Administração, como único imóvel que reúne as características capaz de
            atender ao interesse público.

       </p>

       <h4>III - CONCLUSÃO</h4>
       <p style="text-align: justify">
            Pelo exposto, opino pela possibilidade da contratação por Inexigibilidade de
            Licitação, desde que seja observada a recomendação elencada no corpo
            deste Parecer Jurídico, assim, atesto a regularidade da Inexigibilidade de
            Licitação e da minuta do contrato do presente processo administrativo.
       </p>

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
    {{-- QUEBRA DE PÁGINA --}}
    <div class="page-break"></div>
    <div>
        <h4>DESPACHO</h4>

        <p>
            Ao(À) Ilmo(a). Sr(a).<br>
            <span>{{ $processo->detalhe->encaminhamento_controle_interno }}</span>
        </p>

        <p style="text-align: justify">Assunto: Encaminhamento de Processo de Inexigibilidade de Licitação</p>

        <p style="text-align: justify; text-indent: 30px;">Senhor(a) Prefeito,</p>

        <p style="text-align: justify; text-indent: 30px;">
            Encaminho ao Exm. Senhor(a) o Processo de Inexigibilidade de
            Licitação nº {{ $processo->numero_procedimento }}, {!! strip_tags($processo->objeto) !!} para emissão de parecer do Controlador Interno acerca da contratação
        </p>

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

</body>

</html>
