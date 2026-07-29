<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Autorização de Contratação - Fracassada</title>
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

        @page { margin: 0; size: A4; }
        body {
            margin: 0; padding: 4cm 2cm; font-size: 11pt; font-family: 'Aptos', sans-serif;
            background-image: url('{{ public_path($prefeitura->timbre) }}'); background-repeat: no-repeat;
            background-position: top left; background-size: cover; text-align: justify;
            text-justify: inter-word; line-height: 1.2;
        }
        .page-break { page-break-after: always; }
        .signature-block { margin-top: 60px; text-align: center; }
        p { margin-bottom: 15px; }
        h4, h3 { text-align: center; font-weight: bold; margin-bottom: 20px; font-family: 'AptosExtraBold', sans-serif;}
    </style>
</head>
<body>
    @php
        $dataFormatada = \Carbon\Carbon::parse($dataSelecionada)
            ->locale('pt_BR')
            ->translatedFormat('d \d\e F \d\e Y');
            
        $hasSelectedAssinantes = isset($assinantes) && count($assinantes) > 0;
        $primeiroAssinante = $hasSelectedAssinantes ? $assinantes[0] : null;
        
        $processoFracassado = $detalhe->processoFracassado;
    @endphp

    <p style="text-align: left;">
        <strong>ÓRGÃO/ENTIDADE:</strong> PREFEITURA MUNICIPAL DE {{ strtoupper($prefeitura->cidade) }}<br>
        <strong>PROCESSO ADMINISTRATIVO:</strong> {{ $processo->numero_processo }}<br>
        <strong>PROCESSO DE DISPENSA DE LICITAÇÃO:</strong> {{ $processo->numero_procedimento }}<br>
        <strong>MODALIDADE E Nº DO PROCESSO ADMINISTRATIVO DA LICITAÇÃO ANTERIOR:</strong> {{ $processoFracassado ? $processoFracassado->modalidade->getDisplayName() . ' nº ' . $processoFracassado->numero_procedimento : 'XXX' }}
    </p>
    
    <br>
    
    <h3>DESPACHO DE DECLARAÇÃO DE LICITAÇÃO FRACASSADA E AUTORIZAÇÃO DE CONTRATAÇÃO DIRETA POR DISPENSA</h3>
    <h4 style="font-size: 10pt;">(Art. 75, Inciso III, c/c Art. 72 da Lei Federal nº 14.133/2021)</h4>
    
    <p><strong>1. RELATÓRIO</strong></p>
    
    <p>1.1. Tratam os autos do Processo Administrativo nº {{ $processoFracassado ? $processoFracassado->numero_processo : 'XXX' }}, instaurado com o objetivo de promover a licitação, na modalidade {{ $processoFracassado ? $processoFracassado->modalidade->getDisplayName() : 'XXX' }}, para {!! strip_tags($processo->objeto) !!}, sob o regramento da Lei Federal nº 14.133/2021.</p>
    
    <p>1.2. Realizada a sessão pública do {{ $processoFracassado ? $processoFracassado->modalidade->getDisplayName() : 'XXX' }} nº {{ $processoFracassado ? $processoFracassado->numero_procedimento : 'XXX' }}, o(a) Pregoeiro(a)/Agente de Contratação, após a regular apreciação das propostas e/ou documentos de habilitação, atestou em Ata de Sessão e Relatório Final que a licitação restou FRACASSADA, tendo em vista que:</p>
    
    @php
        $motivos = is_array($detalhe->motivos_fracasso ?? null) ? $detalhe->motivos_fracasso : [];
        $opcoesFracasso = [
            'desclassificadas' => 'Todas as propostas apresentadas foram desclassificadas por desconformidade com o Edital;',
            'inabilitados' => 'Todos os licitantes habilitados/convocados foram inabilitados por descumprimento das exigências editalícias;',
            'ambos' => 'Ocorreu a desclassificação e inabilitação de todos os licitantes participantes da disputa.',
        ];
    @endphp
    @if(!empty($motivos))
    <p>
        @foreach($opcoesFracasso as $chave => $texto)
            @if(in_array($chave, $motivos, true))
                {{ $texto }}<br>
            @endif
        @endforeach
    </p>
    @endif
    
    <p>1.3. O relatório do certame foi devidamente instruído e submetido a esta Autoridade Competente, atestando o exaurimento da fase competitiva sem a obtenção de proposta apta à adjudicação, bem como a permanência da necessidade pública e do interesse da Administração na contratação do referido objeto.</p>
    
    <p><strong>2. FUNDAMENTAÇÃO E MOTIVAÇÃO</strong></p>
    
    <p>2.1. O insucesso da disputa licitatória não afastou a necessidade premente do órgão no provimento do objeto contratual.</p>
    
    <p>2.2. A Lei nº 14.133/2021 autoriza expressamente a contratação direta por Dispensa de Licitação quando a licitação anterior tiver restado fracassada há menos de 1 (um) ano, desde que mantidas integralmente as condições definidas no edital original e justificadas as razões do preço e da escolha do fornecedor, nos termos do seu Art. 75, inciso III:</p>
    
    <p style="margin-left: 40px; font-style: italic;">
        “Art. 75. É dispensável a licitação:<br>
        III - para contratação que mantenha todas as condições definidas em edital de licitação realizada há menos de 1 (um) ano, quando se verificar que naquela licitação:<br>
        a) não surgiram licitantes interessados ou não foram apresentadas propostas válidas;”
    </p>
    
    <p>2.3. Constata-se que o encerramento do certame anterior ocorreu dentro do prazo legal citado (há menos de 1 ano) e que o Estudo Técnico Preliminar (ETP) e o Termo de Referência originais permanecem adequados, vigentes e aptos para reger a contratação direta.</p>

    <p><strong>3. DA DEMONSTRAÇÃO DO PREJUÍZO À ADMINISTRAÇÃO E DA INVIABILIDADE DE NOVO CERTAME</strong></p>
    
    <p>A opção da Administração em prosseguir via Dispensa de Licitação, em detrimento da deflagração de uma nova licitação tradicional, fundamenta-se nos seguintes fatores imperativos:</p>
    
    <p><strong>a) Risco de Descontinuidade e Prejuízo ao Interesse Público</strong><br>
    - A realização de um novo processo licitatório (elaboração de novas fases, publicação de edital, prazos mínimos de apresentação de propostas, sessão pública, fases recursais e homologação) consumiria um período estimado entre 60 a 90 dias úteis.<br>
    - A aguarda por esse interregno temporal causará prejuízo direto e irreparável à Administração, resultando em paralisação de serviços essenciais.</p>
    
    <p><strong>b) Ineficiência e Custos Administrativos Repetitivos</strong><br>
    - A repetição integral do rito do processo licitatório fracassado acarretaria a mobilização desnecessária da equipe da Comissão de Licitação, assessoria jurídica e setores técnicos, gerando custos operacionais e administrativos que afrontam o Princípio da Eficiência (Art. 37, caput, CF/88) e o Princípio da Economia Processual (Art. 5º da Lei nº 14.133/2021).<br>
    - Não há garantia ou indicador de que a reabertura do certame nos mesmos moldes atrairia resultado diverso no curto prazo, onerando a máquina pública sem ganho efetivo de competitividade.</p>
    
    <p><strong>c) Celeridade e Efetividade da Contratação Direta</strong><br>
    - O procedimento de dispensa por licitação fracassada permite à Administração buscar no mercado fornecedores capacitados que aceitem executar o objeto nas exatas condições estipuladas no edital original, assegurando o atendimento tempestivo da necessidade pública.</p>
    
    <p><strong>4. DECISÃO E DETERMINAÇÕES</strong></p>
    
    <p>Diante do exposto, no uso das atribuições legais a mim conferidas, DECIDO:</p>
    
    <p>
        1. DECLARAR FORMALMENTE FRACASSADO o processo licitatório na modalidade {{ $processoFracassado ? $processoFracassado->modalidade->getDisplayName() : 'XXX' }} nº {{ $processoFracassado ? $processoFracassado->numero_procedimento : 'XXX' }}, Processo Administrativo nº {{ $processoFracassado ? $processoFracassado->numero_processo : 'XXX' }}.<br>
        2. CERTIFICAR E ATESTAR, para os devidos fins de instrução do Processo de Dispensa de Licitação nº {{ $processo->numero_procedimento }}, o fracasso da licitação anterior, servindo o presente despacho como documento comprobatório exigido pela legislação vigente.<br>
        3. AUTORIZAR O REAPROVEITAMENTO INTEGRAL dos Estudos Técnicos Preliminares (ETP) e do Termo de Referência do certame fracassado, ficando expressamente vedada qualquer alteração nas especificações do objeto, quantitativos, prazos, locais de entrega e condições de habilitação ou pagamento estabelecidas no edital originário.<br>
        4. DETERMINAR A INSTAURAÇÃO E INSTRUÇÃO DA DISPENSA DE LICITAÇÃO com fulcro no Art. 75, inciso III, da Lei nº 14.133/2021, devendo a unidade técnica adotar as seguintes providências:<br>
        &nbsp;&nbsp;&nbsp;&nbsp;1) Anexar cópia integral da Ata do Certame Fracassado e do presente Despacho aos autos da Dispensa;<br>
        &nbsp;&nbsp;&nbsp;&nbsp;2) Realizar a cotação/pesquisa de preços atualizada no mercado ou contatar fornecedores atuantes no ramo para verificação de aceitação das condições do edital original por preço igual ou inferior ao estimado no certame;<br>
        &nbsp;&nbsp;&nbsp;&nbsp;3) Proceder à verificação da documentação de habilitação jurídica, fiscal, trabalhista e qualificação técnica do fornecedor selecionado, em estrita conformidade com os critérios fixados no edital original;<br>
        &nbsp;&nbsp;&nbsp;&nbsp;4) Encaminhar os autos à Assessoria Jurídica para emissão do parecer opinativo nos termos do Art. 72 da Lei nº 14.133/2021.
    </p>
    
    <div style="text-align: center; margin-top: 40px;">
        {{ $prefeitura->cidade }} – PI, {{ $dataFormatada }}.
    </div>

    @if ($hasSelectedAssinantes)
        <div style="margin-top: 40px; text-align: center;">
            <div class="signature-block" style="display: inline-block;">
                ___________________________________<br>
                <p style="line-height: 1.2; margin: 0;">
                    {{ $primeiroAssinante['responsavel'] }}<br>
                    <span>{{ $primeiroAssinante['unidade_nome'] }}</span>
                </p>
            </div>
        </div>
    @else
        <div class="signature-block" style="margin-top: 40px;">
            ___________________________________<br>
            <p style="line-height: 1.2; margin: 0;">
                {{ $processo->prefeitura->autoridade_competente }}<br>
                <span>Prefeito(a) Municipal</span>
            </p>
        </div>
    @endif
    
    {{-- A Ata da Sessão do certame fracassado (anexo_pdf_ata_sessao_fracassada) é
         anexada automaticamente ao final pelo ProcessoPdfService (mapeamentoAnexos). --}}
</body>
</html>
