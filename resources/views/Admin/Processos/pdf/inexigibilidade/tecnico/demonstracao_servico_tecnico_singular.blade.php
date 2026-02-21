<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>DEMONSTRAÇÃO SERVIÇOS TÉCNICOS DE NATUREZA SINGULAR - Processo {{ $processo->numero_processo ?? $processo->id }}</title>
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
    @php
    // Verifica se a variável $assinantes existe e tem itens
    $hasSelectedAssinantes = isset($assinantes) && count($assinantes) > 0;

    // Define o primeiro assinante, se existir
    $primeiroAssinante = $hasSelectedAssinantes ? $assinantes[0] : null;

    // Extrai o nome do município removendo "Prefeitura Municipal de" ou "Prefeitura de"
    $municipio =  $processo->prefeitura->cidade;

    // Define a data formatada em português
    $dataFormatada = \Carbon\Carbon::parse($dataSelecionada)
    ->locale('pt_BR')
    ->translatedFormat('d \d\e F \d\e Y');
    @endphp
    {{-- ====================================================================== --}}
    {{-- BLOCO 1: CAPA DO DOCUMENTO --}}
    {{-- ====================================================================== --}}
    <div id="cover-page">
        <img src="{{ public_path('icons/capa-documento.png') }}" alt="Martelo da Justiça" class="cover-image">
        <div class="cover-title">
            DEMONSTRAÇÃO SERVIÇOS <br>
            TÉCNICOS DE NATUREZA <br>
            SINGULAR
        </div>
    </div>

    {{-- QUEBRA DE PÁGINA --}}
    <div class="page-break"></div>

    <div>
        <h4 style="text-align: center;">
            CERTIDÃO DE SINGULARIDADE E NOTÓRIA ESPECIALIZAÇÃO
        </h4>
        <p style="text-align: justify; text-indent: 30px;">
            CERTIFICO para devidos fins que o(a) empresa/pessoa física
            XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX, inscrito no CNPJ/CPF sob o n°
            XXXXXXXXXXXXXXXX, com endereço na XXXXXXXXXXXXXXXXXXXXXXXXXXXX, possui
            SINGULAR E NOTÓRIA ESPECIALIZAÇÃO na {!! strip_tags($processo->objeto) !!}, como se
            demonstra por meio das razões de ordem técnica a seguir articuladas;
        </p>
        <p style="text-align: justify; text-indent: 30px;">
            Preliminarmente, antes de se adentrar na análise dos atributos técnicos do prestador de
            serviço sob exame, se faz mister, para que a presente manifestação esteja robustamente
            fundamentada, que possamos tecer algumas considerações acerca dos contornos conceituais do
            que se pode entender por serviços técnicos de natureza singular e profissionais de notória
            especialização.
        </p>
        <p style="text-align: justify; text-indent: 30px;">
            Para que possamos cumprir nosso desiderato, indispensável o embasamento da doutrina
            especializada, que nos fornece a base para nossa reflexão. Nesse passo, passando a análise dos
            termos conceituais do que vem a ser serviço técnico singular, trazemos a colocação os
            ensinamentos de MARÇAL JUSTENFILHO que preleciona:
        </p>
        <p style="text-align: justify; text-indent: 30px;">
            “É problemático definir “natureza singular” especialmente porque toda hipótese de inviabilidade de
            competição pode ser reportada, em última análise, a um objeto singular. Mas a explicita referência
            contida no inciso II não pode ser ignorada e a expressão vocabular exige interpretação especifica a
            propósito dos serviços técnicos profissionais especializados.”
        </p>
        <p style="text-align: justify; text-indent: 30px;">
            [...] a “natureza singular” do serviço deve ser entendida como uma característica especial de
            algumas contratações de serviços técnicos profissionais especializados”. [...] a “natureza singular”
            do serviço deve ser entendida como uma característica especial de algumas contratações de
            serviços técnicos profissionais especializados.
        </p>
        <p style="text-align: justify; text-indent: 30px;">
            Com base na lição valiosa do especialista, podemos, em um esforço considerável, resumir
            a análise do que vem a ser “serviço de natureza singular”, como aquele que por sua natureza
            complexa, somente podem ser prestados por profissionais que possam atingir os resultados
            almejados, ou seja, profissionais com perfil diferenciado dado os serviços a ser prestado.
            Noutro giro, quanto à notória especialização, o próprio § 1º, do artigo 25, da Lei de
            Licitações, que traz as diretrizes da definição do que seja notória especialização. Aduz o dispositivo
            em questão:
        </p>
        <p style="text-align: justify; text-indent: 30px;">
            “Considera-se de notória especialização o profissional ou empresa cujo conceito no campo de sua
            especialidade, decorrente de desempenho anterior, estudos, experiências, publicações,
            organização, aparelhamento, equipe técnica, ou de outros requisitos relacionados com suas
            atividades, permita inferir que o seu trabalho é essencial e indiscutivelmente o mais adequado à
            plena satisfação do objeto do contrato”.
        </p>
        <p style="text-align: justify; text-indent: 30px;">
            O dispositivo em comento traz em seu bojo as balizas do que podemos compreender por
            notória especialização, basicamente afirmando da necessidade de qualificação técnica
            aprofundada.
        </p>
        <p style="text-align: justify; text-indent: 30px;">
            Não se pode olvidar outro elemento intrinsicamente ligado ao nosso objeto de análise, o
            caráter confiança, ou confiabilidade, pois dado a subjetividade dos conceitos ora enfrentados.
            Nesse sentido a lição de Celso Antônio Bandeira de Mello ao afirmar:
        </p>
        <p style="text-align: justify; text-indent: 30px;">
            “É natural, pois, que em situação deste gênero, a eleição do eventual contratado – a ser
            obrigatoriamente escolhido entre os sujeitos de reconhecida competência na matéria – recaia em
            profissional ou empresa cujos desempenhos despertem no contratante a convicção de que, para o
            caso, são presumivelmente, mas indicado do que os de outros, despertando-lhes a confiança de
            que produzirá a atividade mais adequada para o caso. Há, pois, nisto, também um componente
            subjetivo ineliminável por parte de quem contrata”. (In Curso de direito administrativo, 12ª ed.
            Malheiros, SP. 2000, p. 478).
        </p>
        <p style="text-align: justify; text-indent: 30px;">
            Pois bem, estabelecidas essas premissas podemos passar a avaliação dos predicados da
            pessoa física/jurídica sob exame.
        </p>
        <p style="text-align: justify; text-indent: 30px;">
            O profissional/empresa em questão realiza suas atividades de prestação de serviço, de
            caráter especializado, prestando inestimável colaboração ao público, como atestam as
            declarações que instruíram o procedimento que culminou com a expedição da presente Certidão.
            Ao se analisar o Currículo Resumido do profissional/contratos apresentados pela empresa, objeto
            de análise, constata-se, que sem dúvidas, é um exemplo de profissional/empresa, possuindo
            irretocável mister na área pública, comprovada experiência na área do objeto pretendido, pois a
            profissional/empresa realiza constantes atualizações, por meio de cursos e seminários, permitindo
            permanente aperfeiçoamento e adequação profissional para o domínio de atendimento à
            Administração, tecnologias, normatizações e programas governamentais que impõe profundo e
            especifico conhecimento para desenvolta atuação. Ante o exposto, considerando os fundamentos
            ao norte alinhados certificados que a empresa/pessoa física
            XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX, inscrito no CNPJ/CPF sob o n°
            XXXXXXXXXXXXXXXX, com endereço na XXXXXXXXXXXXXXXXXXXXXXXXXXXX, possui
            SINGULAR E NOTÓRIA ESPECIALIZAÇÃO na XXXXXXXXXX (OBJETO), com vasta experiencia
            de atuação no setor público, possuindo confiança absoluta desta Gestão Municipal.
        </p>
    </div>

    {{-- Bloco de data e assinatura --}}
    <div class="footer-signature">
        {{ $municipio }}, {{ $dataFormatada }}
    </div>

    @if ($hasSelectedAssinantes)
    {{-- Renderiza apenas o primeiro assinante --}}
    <div style="margin-top:40px; text-align:center;">
        <div class="signature-block" style="display:inline-block; margin:0 40px;">
            ___________________________________<br>
            <p style="font-size:10pt; line-height:1.2; margin:0;">
                {{ $primeiroAssinante['responsavel'] }}<br>
                <span style="color:#4b5563;">{{ $primeiroAssinante['unidade_nome'] }}</span>
            </p>
        </div>
    </div>
    @else
    {{-- Fallback (sem assinantes selecionados) --}}
    <div class="signature-block" style="margin-top:40px; text-align:center;">
        ___________________________________<br>
        <p style="font-size:10pt; line-height:1.2; margin:0;">
            {{ $processo->prefeitura->autoridade_competente ?? '____________________' }}<br>
            <span style="color:red;">[Cargo/Título Padrão - A ser ajustado]</span>
        </p>
    </div>
    @endif
    {{-- QUEBRA DE PÁGINA --}}
    <div class="page-break"></div>

    <div>
        <hr>
        <h4 style="text-align: center;">TERMO DE JUNTADA</h4>
        <hr>

        Aos XXX (xxxxxx) dias do mês de XXXX de 202X, procedi a juntada aos autos
        do processo administrativo XXX/202X, as propostas de preço referente a
        XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
        XXXXXXXXXXXXXXXXXXX (OBJETO), e a documentação das empresas. Com
        este fim e para constar, eu, XXXXXXXXXXXXXXXXXX, lavrei o presente termo
        que vai por mim assinado.

    </div>

</body>

</html>
