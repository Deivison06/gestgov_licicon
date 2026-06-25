<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>PARECER DO CONTROLE INTERNO - Processo {{ $processo->numero_processo ?? $processo->id }}</title>
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
            PARECER DO CONTROLE INTERNO
        </div>
    </div>

    {{-- QUEBRA DE PÁGINA --}}
    <div class="page-break"></div>

    {{-- ====================================================================== --}}
    {{-- BLOCO 2: TERMO DE RECEBIMENTO --}}
    {{-- ====================================================================== --}}
    <div>
        <p style="text-indent: 50px; font-weight: bold;">
            PARECER DO CONTROLE INTERNO  <br>
            PROCESSO ADMINISTRATIVO {{ $processo->numero_processo }} <br>
            CONCORRÊNCIA Nº {{ $processo->numero_procedimento }}
        </p>
        <p>
            {!! strip_tags($processo->objeto) !!}
        </p>
        <h4>I. RELATÓRIO:</h4>
        <p style="text-indent: 30px; text-align: justify;">
            Versa o presente parecer acerca de pedido originário da {{ $processo->detalhe->secretaria }},
            que solicitou a contratação de {!! strip_tags($processo->objeto) !!} para atendimento do objeto acima especificado.
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Após o pedido feito pela {{ $processo->detalhe->secretaria }}, foi
            solicitado ao setor de engenharia o Projeto Básico, conforme documentos acostados aos autos.
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Instruem ainda o presente processo:
        </p>
        <ul style="list-style: none;">
            <li>✓ DFD;</li>
            <li>✓ Projeto Básico;</li>
            <li>✓ Declaração de Adequação Orçamentária e Financeira; </li>
            <li>✓ Autorização do Ordenador de Despesas; </li>
            <li>✓ Termo de Autuação do Processo Licitatório</li>
            <li>✓ Documentos de habilitação</li>
            <li>✓ Minuta do Contrato Administrativo; </li>
            <li>✓ Parecer jurídico;</li>
            <li>E o Relatório.</li>
        </ul>

        <h4>II. FUNDAMENTOS</h4>
        <p style="text-indent: 30px; text-align: justify;">
            No cumprimento das atribuições estabelecidas nos Art. 31, 70 e 74 da
            Constituição Federal, e demais normas que regulam as atribuições do Sistema
            de Controle Interno, referentes ao exercício do controle prévio e concomitante
            dos atos de gestão e, visando orientar o Administrador Público, expedimos, a
            seguir, nossas considerações.
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Primeiramente, ressalta-se que no caso em apreço há justificativa para
            realização da despesa, bem como, há dotação orçamentária suficiente para
            cobrir o pagamento pretendido, o que se verifica pelo espelho da dotação
            orçamentária constante dos autos.
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Verificou-se que o processo licitatório foi realizado com observância a
            todas as formalidades e atos necessários durante a fase interna, bem como de
            acordo com as disposições legais vigentes, em especial a Lei nº 14.133/21 (Nova
            Lei de Licitações e Contratos Administrativos).
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            No caso dos autos, observa-se que foi realizada a sessão e, ato seguido,
            foi contatado o vencedor do certame.
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Desta forma, observa-se que, o valor final está compatível com os preços
            praticados no mercado para a aquisição solicitada, conforme cotação de
            preços juntada aos autos.
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Noutro tocante, Marçal Justen Filho afirma: “Qualquer contratação que
            importe dispêndio de recursos públicos depende de previsão de recursos
            orçamentários. Assim se impõe em decorrência do princípio constitucional de
            que todas as despesas deverão estar previstas no orçamento (art. 167, incs. I e
            II da CF), somente podendo ser assumidos compromissos e deveres com
            fundamento na existência de receita prevista”.
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Neste particular, incumbe resguardar que o espelho da dotação
            orçamentária apontado pela Secretaria Municipal de Finanças supre os custos
            com as despesas específicas.
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Ao analisar os autos, verifica-se que foi realizado, pela Procuradoria
            Municipal, análise de controle prévio da legalidade dos atos praticados no
            procedimento de contratação direta, atendendo prescrição contida no art. 53,
            §4º da Lei nº 14.133/21. Ainda, observa-se que deve ser designado
            representante(s) da Administração Pública para exercer o acompanhamento e
            fiscalização da execução do contrato, nos moldes do art. 117 da Lei nº
            14.133/21.
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Por fim, ressalta-se que foram devidamente cumpridos os requisitos legais
            para publicidade dos atos do procedimento licitatório, bem como os licitantes
            vencedores apresentaram documentos capazes de comprovar o cumprimento
            dos requisitos de habilitação, nos termos da Lei nº 14.133/21. Desta forma,
            encontram-se satisfeitas as exigências legais para operação da contratação
            em tela.
        </p>

        <h4>IV. CONCLUSÃO</h4>

        <p style="text-indent: 30px; text-align: justify;">
            Nesta análise foram enfocados apenas aspectos legais com base nos
            elementos fornecidos no processo, não sendo considerados os critérios que
            levaram a Administração a tal procedimento.
        </p>
        <p style="text-indent: 30px; text-align: justify;">
            Dessa forma, realizada a análise do processo administrativo trazido à
            baila, restando comprovado não haver vícios que possam acarretar nulidade
            no procedimento, esta Controladoria Interna, em atenção aos princípios que
            regem a Administração Pública, opina pela REGULARIDADE do presente
            procedimento, estando APTO a gerar despesas para a municipalidade.
        </p>
        <p style="text-indent: 30px;">
            É o parecer, SMJ.
        </p>
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
