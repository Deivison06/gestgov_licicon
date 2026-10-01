<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>PARECER DO CONTROLE INTERNO{{ $processo->numero_processo ?? $processo->id }}</title>
    <style type="text/css">
        @page {
            margin: 0;
            size: A4;
        }

        body {
            margin: 0;
            padding: 4cm 2cm;
            font-size: 10pt;
            font-family: Arial, Helvetica, sans-serif;
            /* Adiciona o timbre como background */
            background-image: url('{{ public_path($prefeitura->timbre) }}');
            background-repeat: no-repeat;
            background-position: top left;
            background-size: cover;

            text-align: justify;
            text-justify: inter-word;
            line-height: normal;
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
            font-size: 16pt;
            font-weight: 900;
            border: 2px solid #000;
            display: inline-block;
            line-height: 0.9;
            padding: 8px 40px;
            font-family: Arial, Helvetica, sans-serif;
        }

        .footer-signature {
            margin-top: 24px;
            text-align: right;
        }

        .signature-block {
            margin-top: 24px;
            text-align: center;
            page-break-inside: avoid;
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

<div>
    <h4>
        PARECER CONTROLE INTERNO <br>
        PROCESSO ADM. Nº. {{ $processo->numero_processo}} <br>
        INEXIGIBILIDADE Nº. {{ $processo->numero_inexigibilidade }}<br>
        INTERESSADO: PREFEITO MUNICIPAL DE <span style="text-transform: uppercase;">{{ $processo->prefeitura->cidade }}</span>
    </h4>

    <p style="text-align: justify">
        Tratam os autos do processo de {!! strip_tags($processo->objeto) !!} mediante Inexigibilidade de licitação em favor da
        empresa/profissional {{ $processo->detalhe->razao_social }},
        inscrita no CNPJ/CPF sob o nº {{ $processo->detalhe->cnpj_empresa_vencedora }}, no valor de R$
        {{ number_format($processo->detalhe->valor_total, 2, ',', '.') }}. Ressalta-se que o Procedimento ocorreu
        dentro das formalidades legais, conforme detalhado no processo, baseado
        na Lei 14.133/21
    </p>

    <h4>DO CONTROLE INTERNO</h4>

    <p style="text-align: justify">
        A Constituição Federal de 1988, em seu art. 74, estabelece as finalidades do
        Controle Interno, dentre outras competências, realizar acompanhamento,
        levantamento, inspeção e auditoria nos sistemas administrativo, contábil,
        financeiro, patrimonial e operacional relativo às atividades administrativas,
        com vistas a verificar a legalidade e a legitimidade de atos de gestão pela
        execução orçamentária, financeira e patrimonial e avaliar seus resultados
        quanto a economicidade, eficiência e eficácia.
        <br>
        O controle interno é fundamental para se atingir resultados favoráveis em
        qualquer organização. Na gestão pública os mecanismos de controle
        existentes previnem o erro, a fraude e o desperdício, trazendo benefícios à
        população.
        <br>
        Tendo em vista que o processo de contratação em exame, implica em
        realização de despesa, demonstra-se a competência do Controle Interno
        para análise e manifestação.
    </p>

    <h4>DA INEXIGIBILIDADE DE LICITAÇÃO</h4>

    <p style="text-align: justify">
        Conforme o Art. 74 da Lei nº 14.133/21, poderá ser utilizado Inexigibilidade
        de Licitação nos casos previstos.
    </p>

    <h4>DA ANÁLISE PROCEDIMENTAL</h4>

    <p style="text-align: justify">
        Em exame, quanto aos atos procedimentais na fase interna e externa
        verificou-se que:<br><br>
        I - Documento de formalização de demanda e, se for o caso, estudo técnico
        preliminar, análise de riscos, termo de referência, projeto básico ou projeto
        executivo; 
        <br>
        II - Estimativa de despesa, que deverá ser calculada na forma estabelecida
        no art. 23 desta Lei;
        <br>
        III - Parecer jurídico e pareceres técnicos, se for o caso, que demonstrem o
        atendimento dos requisitos exigidos;
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
        VIII - autorização da autoridade competente.
    </p>

    <h4>DOS FATOS</h4>

    <p style="text-align: justify">
        O Controle Interno, em suas considerações, faz saber que, após exames
        detalhados dos atos procedimentais pela Comissão de Licitação, conclui-se,
        que nenhuma irregularidade foi levantada, opinando nesta oportunidade
        apenas para que sejam realizadas as publicações dos extratos dos contratos,
        para que o procedimento realizado fique de acordo com a legislação
        vigente, sendo então dado prosseguimento as demais etapas
        subsequentes, evidenciando a presença efetiva de publicidade de todos os
        atos realizados.
    </p>

    <h4>CONCLUSÃO</h4>

    <p style="text-align: justify">
        A Comissão de Licitação atendeu os requisitos das leis nas atividades
        realizadas, nota-se, que o procedimento licitatório cumpriu seu objetivo,
        tendo alcançado seu êxito na contratação destacando-se na oportunidade a
        necessidade de publicação dos extratos para finalização do processo.
        <br><br>
        É o parecer,
    </p>

    <h4>DESPACHO</h4>

    <p>
        Ao(À) Ilmo(a). Sr(a).<br>
        <span>{{ $processo->prefeitura->autoridade_competente }}</span>
        <br>
        Prefeito Municipal
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
            $primeiroAssinante = $assinantes[0]; // Pega o primeiro item
        @endphp

        <div style="text-align: center;">
            <div class="signature-block" style="display: inline-block; margin-left: 40px; margin-right: 40px;">
                ___________________________________<br>
                <p style="line-height: 1.2;">
                    {{ $primeiroAssinante['responsavel'] }} <br>
                    <span>{{ $primeiroAssinante['unidade_nome'] }}</span>
                </p>
            </div>
        </div>
    @else
        {{-- Bloco Padrão (Fallback) --}}
        <div class="signature-block">
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
