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
            referente a {!! strip_tags($processo->objeto) !!}. <br>
            Deve ser ressaltado que a análise da Procuradoria repercute estritamente
            sobre a apreciação jurídica da contratação, não havendo qualquer opinião
            sobre o mento administrativo.
            Esse é o resumo dos fatos, passamos a nos manifestar.
       </p>

       <h4>II - DA FUNDAMENTAÇÃO</h4>
       <p style="text-align: justify">
            A regra geral em nosso ordenamento jurídico, atribuída pela Constituição
            Federal, e a exigência da celebração de contratos pela Administração
            Pública, procedida de licitação pública (CF, art. 37, XXI). 
            <br>
            Existem, contudo, hipóteses em que a Licitação formal seria impossível ou
            frustraria a própria consecução do interesse público, uma vez que o
            procedimento licitatório normal conduziria ao sacrifício do interesse público
            e não asseguraria a contratação mais vantajosa.
            <br>
            Entre estas hipóteses repousam o Art. 74, inc. I da Lei 14.133/21, onde está
            previsto a contratação direta por inexigibilidade, em razão da exclusividade
            para a prestação dos serviços/fornecimento dos bens objeto deste processo.
            Conforme leciona a doutrina pátria, a inviabilidade de competição ocorre
            quando o objeto é singular ou o fornecedor é único, tornando o certame
            licitatório um ato inócuo e contrário ao princípio da eficiência, visto que não
            haveria disputa entre diferentes agentes econômicos.
            <br>
            Em estrito cumprimento ao § 1º do Art. 74 da Lei 14.133/2021, a
            exclusividade foi devidamente comprovada mediante a apresentação de
            Atestado de Exclusividade, documento este que atesta ser a contratada a
            única representante/fabricante autorizada no território nacional/regional
            para o objeto em questão.
            <br>
            O processo seguiu rigorosamente os ritos do Art. 72 da Nova Lei de
            Licitações, contando com o Documento de Formalização da Demanda (DFD),
            Estudo Técnico Preliminar (ETP), Análise de Riscos e a devida demonstração
            de que o preço a ser pago é compatível com o praticado pela futura
            contratada em situações análogas, garantindo a proteção ao erário.
            Diante dos requisitos exigidos pela lei para autorizar a contratação direta,
            entendemos ser possível à contratação, tendo em vista haver comprovação
            nos autos de que a empresa/pessoa física é possuidora de Atestado de
            Exclusividade, adequado à plena satisfação do objeto a ser contratado,
            compatível com a necessidade administrativa.
            <br>
            Com efeito, para efetuar contratações através de Inexigibilidade de Licitação
            com fulcro no artigo supra, a Administração deve necessariamente observar
            requisitos acima descritos, bem como as exigências legais para a
            contratação, previstas no artigo 72, e incisos do mesmo dispositivo, que
            assim dispõem:
            <br>
            Art. 72. O processo de contratação direta, que compreende os casos de
            inexigibilidade e de dispensa de licitação, deverá ser instruído com os
            seguintes documentos:
            <br>
            I - Documento de formalização de demanda e, se for o caso, estudo técnico
            preliminar, análise de riscos, termo de referência, projeto básico ou projeto
            executivo;
            <br>
            II - Estimativa de despesa, que deverá ser calculada na forma estabelecida
            no art. 23 desta Lei;
            <br>
            III - parecer jurídico e pareceres técnicos, se for o caso, que demonstrem o
            atendimento dos requisitos exigidos”.
            <br>
            IV - Demonstração da compatibilidade da previsão de recursos
            orçamentários com o compromisso a ser assumido;
            <br>
            V - Comprovação de que o contratado preenche os requisitos de habilitação
            e qualificação mínima necessária;
            <br>
            VI - Razão da escolha do contratado;
            <br>
            VII - justificativa de preço;
            <br>
            VIII - autorização da autoridade competente
            No caso dos autos, verifica-se que os requisitos supra foram considerados,
            vez que se observa o seguinte: comprovação de que o contratado preenche
            os requisitos de habilitação bem como a razão da escolha do contratado,
            justificativa de preço e autorização da autoridade competente.
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
            Licitação nº {{ $processo->numero_procedimento }}, objeto
            {!! strip_tags($processo->objeto) !!}
            , para emissão de parecer do Controlador Interno acerca da contratação
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
