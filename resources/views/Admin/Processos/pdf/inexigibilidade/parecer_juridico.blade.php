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
           PROCESSO ADMINISTRATIVO N° XXXX/202X <br>
           INEXIGIBILIDADE N° XXX/202X
       </h4>

       <p>EMENTA: INEXIGIBILIDADE DE LICITAÇÃO - LEGALIDADE</p>

       <h4>I - DO RELATÓRIO</h4>
       <p style="text-align: justify">
           Trata-se de solicitação de Parecer Jurídico acerca da legalidade da
           contratação da Empresa/Pessoa Física conforme documentação anexa
           referente a XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX (OBJETO). <br>
           Deve ser ressaltado que a análise da Procuradoria repercute estritamente
           sobre a apreciação jurídica da contratação, não havendo qualquer opinião
           sobre o mento administrativo. <br>
           Esse é o resumo dos fatos, passamos a nos manifestar
       </p>

       <h4>II - DA FUNDAMENTAÇÃO</h4>
       <p style="text-align: justify">
           A regra geral em nosso ordenamento jurídico, atribuída pela Constituição
           Federal, e a exigência da celebração de contratos pela Administração
           Pública, procedida de licitação pública (CF, art. 37, XXI). <br>
           Existem, contudo, hipóteses em que a Licitação formal seria impossível ou
           frustraria a própria consecução do interesse público, uma vez que o
           procedimento licitatório normal conduziria ao sacrifício do interesse público
           e não asseguraria a contratação mais vantajosa. <br>
           Entre estas hipóteses repousam o art. 74, inciso III, da nova Lei de Licitação
           n" 14 133/2021 onde está previsto a contratação direta por inexigibilidade,
           em razão de serviços técnicos especializados de natureza
           predominantemente intelectual.
       </p>

       <p style="text-align: justify">
           Art. 74. É inexigível a licitação quando inviável a competição, em especial nos casos de; <br>
           (...).<br>
           III - contratação dos seguintes serviços técnicos especializados de natureza
           predominantemente intelectual com profissionais ou empresas de notona
           especialização, vedada a inexigibilidade para serviços de publicidade e divulgação:
           <br>
           c) Assessorias ou consultorias técnicas e auditorias financeiras ou tributárias;
           <br>
           e) patrocínio ou defesa de causas judiciais ou administrativas/<br>
           §3° Para fins do disposto no inciso III do caput deste artigo, considera-se de notória
           especialização o profissional ou a empresa cujo conceito no campo de sua
           especialidade, decorrente de desempenho anterior, estudos, experiência, publicações,
           organização, aparelhamento, equipe técnica ou outros requisitos relacionados com
           suas atividades, permita inferir que o seu trabalho é essencial e reconhecidamente
           adequado à plena satisfação do objeto do contrato. (...)•”.
           <br><br>
           Do exposto, observa-se que de acordo com o artigo supra, a prestação de
           serviços de assessoria técnica, pode vir a ser contratado pela Administração
           Pública, mediante inexigibilidade de licitação, acaso demonstrada a notória
           especialização do profissional ou do escritório.
           <br>
           Ressaltando ainda, que a referida Lei excluiu a expressão serviços "de
           caráter singular", presente no art. 25, inciso II, da Lei n° 8.666/93. Quanto ao
           Notoriamente especializado será, assim, o profissional ou empresa que,
           detendo especial qualificação, desfrute de certo conceito e se diferencie,
           exatamente por isso, daqueles do mesmo ramo ou segmento de atuação.
           <br>
           Para HELY LOPES MEIRELLES, a notória especialização "... é o reconhecimento público da
           alta capacidade profissional Notoriedade profissional é algo mais que habilitação
           profissional Esta é a autorização legal para o exercício da profissão; aquela e a
           proclamação da clientela e dos colegas sobre o indiscutível valor do profissional na sua
           especialidade. Notoriedade é, em última análise, para fins de dispensa de licitação, a
           fama consagradora do profissional no campo de sua especialidade.
           <br>
           Em tais circunstâncias, quando restar caracterizada a notória especialização
           do prestador, pessoa física ou empresa, a contratação não demandará a
           realização de prévio certame licitatório, inviabilizado pela impossibilidade de
           competição que diretamente resulta da alta capacitação e do nível de
           qualificação daquele a quem se pretende contrata.
           <br>
           É o que se verifica no caso dos autos, uma vez que a assessoria e consultoria
           no setor público, visa o aprimoramento e o desenvolvimento operacional
           das ações governamentais para o atingimento de metas de eficiência,
           eficácia e qualidade na Administração Municipal, é considerada de extrema
           importância, pois é correlacionada as necessidades da Administração
           Pública, pois todos os seus atos devem ser revestidos de legalidade, a
           interrupção da prestação de tais serviços pode atrasar todos os andamentos
           processuais e administrativos e podem afetar todas as demais áreas do
           órgão envolvido, como projetos de recebimento de verbas públicas,
           implementação de normatizações ou exigências de órgãos controladores, e
           não demandará da realização de prévio certame licitatório, inviabilizado
           pela impossibilidade de competição que diretamente resulta da alta
           capacitação e do nível e qualificação desta.
           <br>
           Diante dos requisitos estabelecidos em Lei para autorizar a contratação
           direta profissional especializado, entendemos ser possível a contratação
           tendo em vista haver comprovação nos autos de que a mesma e possuidora
           de especialização essencial e mais adequada a plena satisfação do objeto a
           ser contratada, vez que comprova a sua especialidade decorrente de
           desempenho anterior, publicações, organização, equipe técnica e outros
           requisitos relacionados com suas atividades.
           <br>
           Isto porque, a assessoria a ser contratada possui notório reconhecimento e
           patente currículo profissional, demonstrando ter exercido atividades
           similares com perfeição, inclusive com objetos idênticos. Neste sentido,
           vejamos Marçal Justen Filho:
           <br>
           Isso se traduz na existência de elementos objetivos ou formais, tais como a conclusão de
           cursos e a titulação no âmbito de pós-graduação, a participação em organismos
           voltados à atividade especializada, o desenvolvimento frutífero e exitoso de serviços
           semelhantes em outras oportunidades, a autoria de obras técnicas, o exercício de
           magistério superior, a premiação em concursos ou a obtenção de láureas, a
           organização de equipe técnica e assim por diante.
           <br>
           Não bastasse a condição de especialista do interessado, pretendido pelo
           Município, a contratação pelo Poder Público não poderia ser confiada a
           quaisquer profissionais. Aqui ingressa uma série de requisitos de índole
           subjetiva que interessa à Administração muito mais do que uma licitação
           ordinária poderia suportar.
           <br>
           Destaque-se, neste particular, o elemento confiança, qualificado
           juridicamente. Confiança (fidúcia) não se lícita, não pode ser objeto de
           cotejo, disputa ou comparação, mui o menos ser mensurada. Aliás, o
           Tribunal de Conta da União já se manifestou sobre o assunto, In verbis:
           <br>
           Notório especializado só tem lugar quando se trata de serviço inédito ou incomum.
           capaz de exigir na seleção do executor de confiança um grau de subjetividade,
           insusceptível de ser medido pelos critérios objetivos de qualificação inerentes ao
           processo de licitação. (Enunciado n° 39/TCU).
           <br>
           Diante dos requisitos exigidos pela lei para autorizar a contratação direta de
           um profissional especializado, entendemos ser possível à contratação,
           tendo em vista haver comprovação nos autos de que o mesmo seja
           possuidor de especialização indiscutivelmente essencial e mais adequada à
           plena satisfação do objeto a ser contratado, compatível com a necessidade
           administrativa.
           <br>
           Ademais, é importante ressaltar que que o Município não possui em seu
           quadro, servidores suficientes em condições de atender a demanda descrita
           no objeto a ser contratado.
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
           II - Estimativa de despesa, que deverá ser calculada na forma estabelecida no
           art. 23 desta Lei;
           <br>
           III - parecer jurídico e pareceres técnicos, se for o caso, que demonstrem o
           atendimento dos requisitos exigidos”.
           <br>
           IV - Demonstração da compatibilidade da previsão de recursos orçamentários
           com o compromisso a ser assumido;
           <br>
           V - Comprovação de que o contratado preenche os requisitos de habilitação e
           qualificação mínima necessária;
           <br>
           VI - Razão da escolha do contratado;
           <br>
           VII - justificativa de preço;
           <br>
           VIII - autorização da autoridade competente
           <br><br>
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
    {{-- QUEBRA DE PÁGINA --}}
    <div class="page-break"></div>
    <div>
        <h4>DESPACHO</h4>

        <p>
            Ao(À) Ilmo(a). Sr(a).<br>
            <span>xxxxxxxxxxxxxxxx</span>
            <br>
            Controlador do Município
        </p>

        <p style="text-align: justify">Assunto: Encaminhamento de Processo de Inexigibilidade de Licitação</p>

        <p style="text-align: justify; text-indent: 30px;">Senhor(a) Prefeito,</p>

        <p style="text-align: justify; text-indent: 30px;">
            Encaminho ao Exm. Senhor(a) o Processo de Inexigibilidade de
            Licitação nº XXX/202X, objeto
            XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
            XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
            XXXXXX, para emissão de parecer do Contrato Interno acerca da
            contrataçã
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
