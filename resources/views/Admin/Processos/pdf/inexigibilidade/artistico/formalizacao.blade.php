<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formalização e Autorização - Processo {{ $processo->numero_processo ?? $processo->id }}</title>
    <style>
        /* ---------------------------------- */
        /* ESTILOS GERAIS E QUEBRA DE PÁGINA */
        /* ---------------------------------- */
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
            font-size: 18pt;
            font-weight: 900;
            border: 2px solid #000;
            display: inline-block;
            line-height: 0.9;
            padding: 10px 50px;
            font-family: 'AptosExtraBold', sans-serif;
        }

        /* ---------------------------------- */
        /* ESTILOS - FORMALIZAÇÃO DE DEMANDA (PÁGINA 1) */
        /* ---------------------------------- */

        .title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .form-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            page-break-inside: avoid;
        }

        .form-table td,
        .form-table th,
        .inner-table td {
            border: 1px solid #000;
            padding: 0;
            vertical-align: top;
        }

        .section-header {
            background-color: #e7e7e7;
            text-align: center;
            padding: 15px;
            border: 1px solid #000;
        }

        .field-label {
            color: #000;
            padding: 5px 8px 0 8px;
            display: block;
        }

        .field-value {
            /* min-height: 25px; */
            padding: 0 8px 5px 8px;
            display: block;
            box-sizing: border-box;
            word-wrap: break-word;
        }

        .large-value-cell {
            min-height: 60px;
            padding: 0 8px 5px 8px;
            text-align: justify;
            word-wrap: break-word;
        }

        .checkbox-area {
            padding: 20px;
        }

        .checkbox-label {
            display: inline-block;
            margin-top: 10px;
            margin-right: 20px;
        }

        .checkbox-box {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1px solid #000;
            margin-right: 5px;
            vertical-align: middle;
            text-align: center;
            line-height: 10px;
            font-weight: bold;
        }

        .two-columns {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            page-break-inside: avoid;
        }

        .two-columns td {
            width: 50%;
            vertical-align: top;
            padding: 0;
            border: none;
        }

        .inner-table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-law {
            margin-top: 5px;
            border: 1px solid #000;
            text-align: left;
            padding: 8px;
            page-break-inside: avoid;
        }

        /* ---------------------------------- */
        /* ESTILOS - AUTORIZAÇÃO (PÁGINA 2) */
        /* ---------------------------------- */

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .section {
            margin-bottom: 20px;
        }

        .auth-table {
            /* Renomeado para evitar conflito com .form-table */
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .auth-table td {
            border: 1px solid #000;
            /* padding: 5px; */
            vertical-align: top;
        }

        .no-border {
            border: none;
        }

        .table-title {
            background-color: #ebebeb;
        }

        .footer-signature {
            margin-top: 60px;
            text-align: right;
        }

        .signature-block {
            margin-top: 60px;
            text-align: center;
        }

    </style>
</head>

<body>
    {{-- ====================================================================== --}}
    {{-- BLOCO 1: CAPA DO DOCUMENTO --}}
    {{-- ====================================================================== --}}
    <div id="cover-page">
        {{-- Substitua 'caminho/para/capa-documento.png' pelo caminho real da sua imagem no sistema --}}
        <img src="{{ public_path('icons/capa-documento.png') }}" alt="Martelo da Justiça" class="cover-image">

        <div class="cover-title">
            DOCUMENTO DE FORMALIZAÇÃO DE<br>DEMANDA
        </div>
    </div>
    {{-- QUEBRA DE PÁGINA AQUI --}}
    <div class="page-break"></div>

    {{-- ====================================================================== --}}
    {{-- BLOCO 2: FORMALIZAÇÃO DE DEMANDA --}}
    {{-- ====================================================================== --}}
    <div id="formalizacao-demanda">

        <div class="title">
            DOCUMENTO FORMALIZAÇÃO DE DEMANDA
        </div>

        <table class="form-table">
            <tr>
                <td colspan="1" class="section-header">1 – IDENTIFICAÇÃO DO ÓRGÃO REQUISITANTE</td>
            </tr>
            <tr>
                <td>
                    <span class="field-label">Secretaria:
                        {{ $detalhe->secretaria ?? 'SECRETARIA DE EDUCACAO' }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="field-label">Unidade/Setor/Departamento:
                        {{ $detalhe->unidade_setor ?? 'Unidade 1' }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="field-label">Servidor responsável pela demanda:
                        {{ $detalhe->servidor_responsavel ?? 'Deivison' }}</span>
                </td>
            </tr>
        </table>

        <table class="form-table">
            <tr>
                <td class="section-header">2 – IDENTIFICAÇÃO DO OBJETO</td>
            </tr>
            <tr>
                <td>
                    <span class="field-label">{!! strip_tags($processo->objeto) !!}</span>
                    {{-- <div class="large-value-cell">
                            {!! str_replace('<p>', '<p style="text-indent:30px; text-align: justify;">', $detalhe->demanda) !!}
                        </div> --}}
                </td>
            </tr>
            <tr>
                <td style="padding: 10px;">
                    <span style="display:block; text-align:center; font-weight:bold;">
                        Justificativa da necessidade da contratação:
                    </span>

                    {!! str_replace('<p>', '<p style="text-indent:30px; text-align: justify; ">', $detalhe->justificativa) !!}
                </td>
            </tr>
        </table>

        <table style="width: 100%; border-collapse: collapse; border: 1px solid #000 !important; margin-bottom: 15px; page-break-inside: avoid; font-family: Arial, sans-serif; font-size: 12px;">
            <tr>
                <td colspan="2" style="font-weight: bold; text-align: center; background-color: #f2f2f2;">
                    3 – OBSERVAÇÕES GERAIS
                </td>
            </tr>

            <tr>
                <td style="width: 100%;  padding: 5px 8px; vertical-align: top;">
                    Prazo de entrega/execução: {{ $detalhe->prazo_entrega ?? '' }}
                </td>
            </tr>
        </table>

        <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px;">
            <tr>
                <!-- Coluna esquerda -->
                <td style="width: 50%; border-right: 1px solid #000; padding: 20px; vertical-align: top;">
                    @php
                    $vinculativo = is_array($detalhe->instrumento_vinculativo ?? null)
                    ? $detalhe->instrumento_vinculativo
                    : ['contrato'];
                    $outro_vinculativo = $detalhe->instrumento_vinculativo_outro ?? '________________.';
                    @endphp

                    <div style="font-weight: bold; margin-bottom: 5px;">Instrumento Vinculativo</div>

                    <div style="display: block; margin-bottom: 3px;">
                        <span style="width: 14px; height: 14px; border: 1px solid #000; display: inline-block; text-align: center; line-height: 12px; font-weight: bold;">
                            {{ in_array('contrato', $vinculativo) ? 'X' : '' }}
                        </span>
                        Contrato
                    </div>
                    <div style="display: block; margin-bottom: 3px;">
                        <span style="width: 14px; height: 14px; border: 1px solid #000; display: inline-block; text-align: center; line-height: 12px; font-weight: bold;">
                            {{ in_array('ata_registro_precos', $vinculativo) ? 'X' : '' }}
                        </span>
                        Ata de Registro de Preços
                    </div>
                    <div style="display: block; margin-bottom: 3px;">
                        <span style="width: 14px; height: 14px; border: 1px solid #000; display: inline-block; text-align: center; line-height: 12px; font-weight: bold;">
                            {{ in_array('outro', $vinculativo) ? 'X' : '' }}
                        </span>
                        Outro: <span style="font-weight: normal; text-decoration: underline;">{{ $outro_vinculativo }}</span>
                    </div>
                </td>

                <!-- Coluna direita -->
                <td style="width: 50%; padding: 20px; vertical-align: top;">
                    @php
                    $vigencia = is_array($detalhe->prazo_vigencia ?? null)
                    ? $detalhe->prazo_vigencia
                    : ['12_meses'];
                    $outro_vigencia = $detalhe->prazo_vigencia_outro ?? '________________.';
                    $objeto_continuado = strtolower($detalhe->objeto_continuado ?? 'nao');
                    @endphp

                    <div style="font-weight: bold; margin-bottom: 5px;">Prazo de Vigência do Objeto</div>

                    <div style="display: block; margin-bottom: 3px;">
                        <span style="width: 14px; height: 14px; border: 1px solid #000; display: inline-block; text-align: center; line-height: 12px; font-weight: bold;">
                            {{ in_array('exercicio_financeiro', $vigencia) ? 'X' : '' }}
                        </span>
                        Exercício financeiro da contratação (até 31/12)
                    </div>
                    <div style="display: block; margin-bottom: 3px;">
                        <span style="width: 14px; height: 14px; border: 1px solid #000; display: inline-block; text-align: center; line-height: 12px; font-weight: bold;">
                            {{ in_array('12_meses', $vigencia) ? 'X' : '' }}
                        </span>
                        Vigência de 12 meses
                    </div>
                    <div style="display: block; margin-bottom: 10px;">
                        <span style="width: 14px; height: 14px; border: 1px solid #000; display: inline-block; text-align: center; line-height: 12px; font-weight: bold;">
                            {{ in_array('outro', $vigencia) ? 'X' : '' }}
                        </span>
                        Outro: <span style="font-weight: normal; text-decoration: underline;">{{ $outro_vigencia }}</span>
                    </div>

                    <div style="margin-top: 10px; padding-top: 5px;">
                        <div style="padding: 0; display: block; font-weight: bold; margin-bottom: 3px;">
                            Contratação
                            de objeto continuado:</div>
                        <div style="display: block; margin-bottom: 3px;">
                            <span style="width: 14px; height: 14px; border: 1px solid #000; display: inline-block; text-align: center; line-height: 12px; font-weight: bold;">
                                {{ $objeto_continuado == 'sim' ? 'X' : '' }}
                            </span>
                            Sim
                        </div>
                        <div style="display: block; margin-bottom: 3px;">
                            <span style="width: 14px; height: 14px; border: 1px solid #000; display: inline-block; text-align: center; line-height: 12px; font-weight: bold;">
                                {{ $objeto_continuado == 'nao' ? 'X' : '' }}
                            </span>
                            Não
                        </div>
                    </div>
                </td>
            </tr>
        </table>


        {{-- USANDO SOMENTE .footer-law para garantir borda, fundo e page-break-inside: avoid --}}
        <div class="footer-law" style="margin-bottom: 10px; padding-bottom: 20px;">
            <span>Regime licitatório adotado:</span><br>
            Lei 14.133/2021 e legislação correlata.
        </div>

        {{-- USANDO .footer-law para garantir borda, fundo e page-break-inside: avoid, e alinhamento central --}}
        <div class="footer-law" style="text-align: justify; margin-top: 5px;">
            Despacho a Solicitação à Autoridade Competente, para a devida autorização acerca da elaboração de
            Estudos Técnicos Preliminares.
        </div>

        @php
        \Carbon\Carbon::setLocale('pt_BR');
        @endphp

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

    {{-- QUEBRA DE PÁGINA AQUI --}}
    <div class="page-break"></div>

    {{-- ====================================================================== --}}
    {{-- BLOCO 3: AUTORIZAÇÃO PARA ESTUDO TÉCNICO --}}
    {{-- ====================================================================== --}}
    <div id="autorizacao-estudo">

        <div style=" text-align: center; font-weight:bold; margin-bottom:20px;">AUTORIZAÇÃO PARA ELABORAÇÃO DE
            ESTUDO
            TÉCNICO</div>

        <p style="text-indent: 30px; text-align: justify;">
            Fica <strong>AUTORIZADO</strong> a equipe de planejamento a dar início aos trabalhos de estudo e
            planejamento de modo a permitir a
            avaliação da viabilidade técnica e econômica da contratação, respeitando-se os critérios mínimos
            estabelecidos no § 1º
            do artigo 18 da Lei 14.133/2021, conforme quadro resumo abaixo:
        </p>

        <div class="section">
            <table class="auth-table">
                <tr>
                    <td class="table-title" style="text-align: center;">UNIDADE SOLICITANTE</td>
                </tr>
                <tr>
                    <td class="center">{{ $detalhe->unidade_setor }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="bold" style="margin-bottom:5px; text-align: center;">NECESSIDADE OBJETO DO ESTUDO:</div>

            {!! str_replace('<p>', '<p style="text-indent:30px; text-align: justify;">', $detalhe->descricao_necessidade_autorizacao) !!}

        </div>

        <div class="section">
            <table class="auth-table">
                <tr>
                    <td class="table-title" style="text-align: center;">EQUIPE DE PLANEJAMENTO</td>
                </tr>
                <tr>
                    <td class="center">
                        {{ $detalhe->responsavel_equipe_planejamento }}
                    </td>
                </tr>
            </table>
        </div>

        <div class="section">
            <table class="auth-table">
                <tr>
                    <td>
                        <p style="text-align: center;">Despacho a Solicitação, para a devida elaboração de Estudos
                            Técnicos Preliminares.</p>
                    </td>
                </tr>
            </table>
        </div>

        {{-- Bloco de data e assinatura --}}
        <div class="footer-signature">
            {{ $processo->prefeitura->cidade }},
            {{ \Carbon\Carbon::parse($dataSelecionada)->translatedFormat('d \d\e F \d\e Y') }}
        </div>

        <div class="signature-block">
            ___________________________________<br>
            {{ $processo->prefeitura->autoridade_competente }} <br>
            Prefeito Municipal
        </div>

    </div>
</body>

</html>
