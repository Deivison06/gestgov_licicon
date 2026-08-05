{{--
    Minuta do PARECER JURÍDICO para Dispensa oriunda de certame FRACASSADO
    (Art. 75, inciso III, alínea "a", da Lei nº 14.133/2021).
    Incluído por parecer_juridico.blade.php quando $processo->detalhe->is_oriundo_fracassado.
    A data e o bloco de assinatura são renderizados pelo arquivo pai.
--}}
@php
    $procFrac = $processo->detalhe?->processoFracassado;
    $refFracassado = $procFrac
        ? $procFrac->modalidade->getDisplayName() . ' nº ' . $procFrac->numero_procedimento
        : 'certame anterior';
    $valorContratado = $vencedores->sum(fn ($v) => $v->lotes->sum('vl_total'));
@endphp

<p style="text-align: center; font-weight: bold;">
    PARECER JURÍDICO
</p>
<p>
    Interessado: {{ $processo->prefeitura->nome }} <br>
    Assunto: Contratação Direta por Dispensa de Licitação — Licitação Anterior Fracassada
    (Art. 75, III, "a", da Lei nº 14.133/2021)
</p>

<table style="width:100%; table-layout:fixed; border-collapse:collapse;">
    <tr>
        <td style="width:40%; padding:8px; vertical-align:top;"></td>
        <td style="width:60%; padding:8px; vertical-align:top; word-wrap:break-word; font-weight: bold;">
            EMENTA: DIREITO ADMINISTRATIVO. LICITAÇÕES E CONTRATOS. NOVA LEI DE LICITAÇÕES
            (LEI Nº 14.133/2021). DISPENSA DE LICITAÇÃO (ART. 75, INCISO III, ALÍNEA "A").
            LICITAÇÃO ANTERIOR FRACASSADA. TEMPORALIDADE INFERIOR A 1 (UM) ANO. MANUTENÇÃO
            INTEGRAL DAS CONDIÇÕES EDITALÍCIAS ORIGINAIS. JUSTIFICATIVA DE PREJUÍZO E
            INVIABILIDADE DE NOVO CERTAME. COMPROVAÇÃO DE PREÇO E HABILITAÇÃO DO FORNECEDOR.
            CUMPRIMENTO DOS REQUISITOS DO ART. 72 DA LEI Nº 14.133/2021. POSSIBILIDADE JURÍDICA.
            PARECER FAVORÁVEL À CONTRATAÇÃO.
        </td>
    </tr>
</table>

<h4>1. RELATÓRIO</h4>
<p style="text-indent: 30px; text-align: justify;">
    Vêm a esta Assessoria Jurídica, para emissão de parecer opinativo nos termos do Art. 72,
    da Lei Federal nº 14.133/2021, os autos do Processo Administrativo em epígrafe, que versa
    sobre a contratação direta por Dispensa de Licitação para {!! strip_tags($processo->objeto) !!}.
</p>
<p style="text-indent: 30px; text-align: justify;">
    Consta dos autos que a Administração Pública promoveu anteriormente o {{ $refFracassado }},
    cujo objeto restou FRACASSADO, consoante Ata de Sessão Pública e Despacho Homologatório da
    Autoridade Competente.
</p>
<p style="text-indent: 30px; text-align: justify;">
    Diante da permanência da necessidade pública e do risco de prejuízo/descontinuidade dos
    serviços administrativos, o Setor Requisitante instaurou o presente procedimento de Dispensa
    de Licitação fundamentado no Art. 75, inciso III, alínea "a", da Lei nº 14.133/2021, instruindo
    o feito com: Despacho da Autoridade Competente declarando formalmente o fracasso do certame
    anterior; Declaração atestando a estrita e integral manutenção das condições fixadas no edital
    original; Justificativa da inviabilidade e do prejuízo em realizar novo certame licitatório;
    Termo de Referência revalidado; Pesquisa de mercado e justificativa da adequação do preço
    contratado; Documentação de habilitação jurídica, fiscal, trabalhista e qualificação técnica do
    vencedor; Declaração de disponibilidade e reserva orçamentária; e Minuta do Contrato.
</p>
<p style="text-indent: 30px; text-align: justify;">
    É o relatório. Passo à fundamentação jurídica.
</p>

<h4>2. FUNDAMENTAÇÃO JURÍDICA</h4>

<p style="font-weight: bold; text-align: justify;">2.1. Da Hipótese de Dispensa por Certame Fracassado (Art. 75, III, "a")</p>
<p style="text-indent: 30px; text-align: justify;">
    A Constituição Federal estabelece, como regra geral, a obrigatoriedade de licitar (Art. 37, XXI).
    Contudo, a própria Carta Magna ressalva os casos especificados na legislação infraconstitucional.
</p>
<p style="text-indent: 30px; text-align: justify;">
    No âmbito da Nova Lei de Licitações (Lei nº 14.133/2021), o Art. 75, inciso III, alínea "a",
    prevê hipótese de dispensa de licitação para contratações cujo certame originário restou infrutífero:
</p>

<table style="width: 100%; border-collapse: collapse; page-break-inside: auto;">
    <tr>
        <td style="width: 40%;"></td>
        <td style="width: 50%; text-align: justify; vertical-align: top;">
            “Art. 75. É dispensável a licitação:
            <br><br>
            III - para contratação que mantenha todas as condições definidas em edital de licitação
            realizada há menos de 1 (um) ano, quando se verificar que naquela licitação:
            <br><br>
            a) não surgiram licitantes interessados ou não foram apresentadas propostas válidas;”
        </td>
    </tr>
</table>

<p style="text-indent: 30px; text-align: justify;">
    Para a correta aplicação do dispositivo legal, a doutrina e a jurisprudência fixam quatro
    requisitos cumulativos: (1) ocorrência prévia de licitação fracassada; (2) temporalidade
    inferior a 1 (um) ano entre a realização da licitação e a contratação direta; (3) manutenção
    irrestrita das condições constantes do edital originário; e (4) justificativa do preço e da
    escolha do fornecedor.
</p>

<p style="font-weight: bold; text-align: justify;">2.2. Da Análise do Preenchimento dos Requisitos do Caso Concreto</p>
<p style="text-indent: 30px; text-align: justify;">
    <strong style="display:inline;">a) Do Fracasso Anterior e Temporalidade:</strong> Compulsa-se dos autos que o
    {{ $refFracassado }} teve sua sessão pública realizada e restou fracassado, constatando-se que
    não decorreu o prazo de 1 (um) ano exigido no dispositivo legal, estando plenamente preenchido
    o requisito temporal.
</p>
<p style="text-indent: 30px; text-align: justify;">
    <strong style="display:inline;">b) Da Manutenção das Condições Editalícias:</strong> A instrução conta com DECLARAÇÃO DE
    MANUTENÇÃO DAS CONDIÇÕES EDITALÍCIAS atestando que as especificações técnicas, quantitativos,
    prazos, critérios de habilitação e obrigações contratuais são rigorosamente idênticos aos
    previstos no instrumento convocatório anterior. Não houve qualquer flexibilização ou alteração
    capaz de descaracterizar o objeto ou beneficiar indevidamente a futura contratada.
</p>
<p style="text-indent: 30px; text-align: justify;">
    <strong style="display:inline;">c) Da Justificativa de Prejuízo e Inviabilidade de Novo Certame:</strong> A Autoridade
    Competente apresentou justificativa fundamentada demonstrando que a deflagração de um novo
    processo licitatório demandaria prazos incompatíveis com a urgência da demanda, gerando custos
    operacionais repetitivos e risco de descontinuidade do serviço público, restando atendido o
    Princípio da Eficiência (Art. 37, caput, CF/88).
</p>

<p style="font-weight: bold; text-align: justify;">2.3. Da Instrução Processual de Acordo com o Art. 72 da Lei nº 14.133/2021</p>
<p style="text-indent: 30px; text-align: justify;">
    O processo administrativo da contratação direta deve observar os requisitos formais estipulados
    no Art. 72 da Lei nº 14.133/2021, verificando-se o atendimento dos seguintes itens nos autos:
    I - ETP, TR ou Projeto Básico mantidos do edital original; II - Estimativa de despesa mediante
    pesquisa de mercado e planilha orçamentária atualizada; III - Parecer Jurídico (o presente
    documento opinativo); IV - Disponibilidade Orçamentária, com declaração da dotação e reserva;
    V - Comprovação de Habilitação, com certidões fiscais, trabalhistas, jurídicas e qualificações;
    VI - Razão da Escolha e do Preço, com justificativa técnica atestando compatibilidade com o
    mercado; e VII - Autorização da Autoridade, mediante Despacho da Autoridade Competente.
</p>

<p style="font-weight: bold; text-align: justify;">2.4. Da Razoabilidade do Preço Contratado</p>
<p style="text-indent: 30px; text-align: justify;">
    A empresa a ser contratada apresentou proposta dentro do limite estimado (R$
    {{ number_format($valorContratado, 2, ',', '.') }}) e em consonância com a pesquisa mercadológica
    atualizada encartada aos autos, afastando qualquer hipótese de sobrepreço ou superfaturamento.
</p>

<h4>3. CONCLUSÃO E RECOMENDAÇÕES</h4>
<p style="text-indent: 30px; text-align: justify;">
    Face ao exposto, com fundamento no Art. 75, inciso III, alínea "a", c/c o Art. 72 da Lei Federal
    nº 14.133/2021, esta Assessoria Jurídica opina pela POSSIBILIDADE JURÍDICA do prosseguimento da
    contratação direta por Dispensa de Licitação, devendo ser observadas as seguintes recomendações
    antes da assinatura do contrato:
</p>
<p style="text-indent: 30px; text-align: justify;">
    1. <strong style="display:inline;">Certidões Negativas:</strong> Verificar a manutenção da validade de todas as certidões de
    regularidade fiscal, social e trabalhista da empresa no ato da assinatura do contrato;
</p>
<p style="text-indent: 30px; text-align: justify;">
    2. <strong style="display:inline;">Ato de Autorização e Ratificação:</strong> Submeter os autos à Autoridade Competente para ato
    formal de autorização e adjudicação da dispensa, conforme exige o Art. 72, VIII, da NLLC;
</p>
<p style="text-indent: 30px; text-align: justify;">
    3. <strong style="display:inline;">Publicidade Obrigatória:</strong> Garantir a publicação do extrato do contrato e do ato de
    dispensa no Portal Nacional de Contratações Públicas (PNCP), no prazo máximo de 10 (dez) dias
    úteis contados da assinatura do contrato, como condição indispensável para sua eficácia.
</p>
<p style="text-indent: 30px; text-align: justify;">
    É o parecer, de caráter opinativo, que submeto à consideração superior.
</p>
