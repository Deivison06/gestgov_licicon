<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>DECLARAÇÃO DE INDISPONIBILIDADE DE IMÓVEL PRÓPRIO - Processo {{ $processo->numero_processo ?? $processo->id }}</title>
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
        DECLARAÇÃO DE INDISPONIBILIDADE DE IMÓVEL PRÓPRIO
    </div>
</div>

{{-- QUEBRA DE PÁGINA --}}
<div class="page-break"></div>

{{-- ====================================================================== --}}
{{-- BLOCO 2: DECLARAÇÃO DE COMPATIBILIDADE --}}
{{-- ====================================================================== --}}

<div>
    <h4 style="text-align: center;">DECLARAÇÃO DE INDISPONIBILIDADE DE IMÓVEL PRÓPRIO</h4>

    <p>Assunto: {!! strip_tags($processo->objeto) !!}</p>

    <p style="text-indent: 30px;">
        A Administração Pública do Município de {{ $processo->prefeitura->cidade }}, por meio da
        {{ $processo->detalhe->unidade_setor }}, após consultas internas e análise da
        estrutura patrimonial municipal, DECLARA para os devidos fins que NÃO
        dispõe de imóvel público próprio, adequado e disponível, que atenda às
        necessidades operacionais e às especificações técnicas exigidas para a
        execução das atividades referentes ao objeto em epígrafe.
    </p>

    <p style="text-indent: 30px;">
        A presente declaração fundamenta-se nos levantamentos realizados
        pela Secretaria de Administração, os quais evidenciaram:
    </p>
    <ul>
        <li> Inexistência de imóvel público com a localização adequada ao
            atendimento das demandas da pasta;</li>
        <li> existência de imóvel municipal com dimensões, estrutura física,
            instalações elétricas, hidráulicas e de acessibilidade compatíveis com o
            funcionamento da unidade pretendida;</li>
        <li> Inexistência de imóvel próprio livre, desocupado e regularizado para
        uso imediato;</li>
        <li> Identificação de que eventual adaptação de imóveis existentes
        demandaria reformas estruturais que comprometeriam a
        economicidade, razoabilidade e continuidade do serviço público.</li>
    </ul>
    <p style="text-indent: 30px;">
        Diante disso, fica justificada a necessidade de locação de imóvel
        privado para atender ao interesse público, o que respalda o prosseguimento
        do processo de INEXIGIBILIDADE DE LICITAÇÃO, nos termos do art. 74, caput
        e inciso V da Lei nº 14.133/2021, considerando a inviabilidade de competição
        decorrente das características específicas do imóvel necessário e da
        disponibilidade limitada no mercado local.
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
