<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Solicitação de Abertura - ETP {{ $etp->id }}</title>
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
            background-image: url('{{ public_path($etp->prefeitura->timbre ?? '') }}');
            background-repeat: no-repeat;
            background-position: top left;
            background-size: cover;
            text-align: justify;
            text-justify: inter-word;
            line-height: 1.5;
        }

        .center {
            text-align: center;
        }

        h3 {
            font-size: 14pt;
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
        }

        h4 {
            font-size: 12pt;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 20px;
            font-size: 10pt;
        }

        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>
<body>

@php
    $secao = 1;

    $temItens = 
        ($etp->tipo_contratacao === 'lote' && $etp->lotes && $etp->lotes->count() > 0) ||
        ($etp->itens && $etp->itens->count() > 0);
@endphp

<h3>SOLICITAÇÃO DE ABERTURA DE PROCEDIMENTO LICITATÓRIO</h3>

<div style="margin-top: 40px;">
    À<br>
    Coordenação / Setor de Licitações<br>
    {{ $etp->prefeitura->nome ?? 'Nome da Entidade' }}
</div>

<p>
    <strong>Assunto:</strong> Solicitação de abertura de procedimento licitatório
</p>

<p>
    Senhor(a) Coordenador(a),
</p>

<p style="text-indent: 40px;">
    Considerando a necessidade de atendimento às demandas administrativas deste(a) {{ $etp->secretaria->nome ?? 'órgão/secretaria' }}, bem como a importância de assegurar a continuidade, eficiência e regularidade dos serviços públicos prestados, solicito a abertura de procedimento licitatório, nos termos da Lei nº 14.133/2021, para a contratação descrita a seguir.
</p>

{{-- 1 --}}
<h4>{{ $secao++ }}. Objeto da Contratação</h4>
<p style="margin-bottom: 20px;">
    {!! nl2br(e($etp->objeto_licitacao)) !!}
</p>

{{-- 2 --}}
<h4>{{ $secao++ }}. Justificativa da Necessidade</h4>
<p style="margin-bottom: 20px;">
   {!! nl2br(e($etp->justificativa_necessidade)) !!}
</p>

{{-- 3 (se existir itens) --}}
@if($temItens)
    <h4>{{ $secao++ }}. Relação de Itens e Quantitativos</h4>

    @if($etp->tipo_contratacao === 'lote' && $etp->lotes && $etp->lotes->count() > 0)
        @foreach($etp->lotes as $lote)
            <p style="margin-top: 15px; font-weight: bold;">Lote: {{ $lote->nome }}</p>
            <table>
                <thead>
                    <tr>
                        <th width="10%">Item</th>
                        <th width="50%">Descrição</th>
                        <th width="20%">Unidade</th>
                        <th width="20%">Quantidade</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lote->itens as $index => $item)
                    <tr>
                        <td class="center">{{ $index + 1 }}</td>
                        <td>{{ $item->descricao_item }}</td>
                        <td class="center">{{ $item->pivot->unidade }}</td>
                        <td class="center">{{ $item->pivot->quantidade }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach

    @elseif($etp->itens && $etp->itens->count() > 0)
        <table>
            <thead>
                <tr>
                    <th width="10%">Item</th>
                    <th width="50%">Descrição</th>
                    <th width="20%">Unidade</th>
                    <th width="20%">Quantidade</th>
                </tr>
            </thead>
            <tbody>
                @foreach($etp->itens as $index => $item)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $item->descricao_item }}</td>
                    <td class="center">{{ $item->pivot->unidade }}</td>
                    <td class="center">{{ $item->pivot->quantidade }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endif

{{-- Próxima seção sempre continua corretamente --}}
<h4>{{ $secao++ }}. Dotação Orçamentária</h4>
<p style="margin-bottom: 30px;">
    {!! nl2br(e($etp->dotacao_orcamentaria)) !!}
</p>

<p style="text-indent: 40px; margin-bottom: 20px;">
    Diante do exposto, solicito a adoção das providências administrativas cabíveis para a abertura do procedimento licitatório, com a devida análise técnica e jurídica, observando-se a legislação vigente.
</p>

<p style="text-indent: 40px; margin-bottom: 50px;">
    Sem mais para o momento, coloco-me à disposição para quaisquer esclarecimentos adicionais.
</p>

<p style="margin-top: 50px; text-align: center;">
    Atenciosamente,<br><br><br>
    ___________________________________________________<br>
    <strong>{{ $etp->servidor_responsavel }}</strong><br>
    {{ $etp->secretaria->nome ?? 'Secretaria' }}
</p>

</body>
</html>
