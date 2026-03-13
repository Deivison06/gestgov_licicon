<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>PCA - {{ $pca->numero_pca ?? $pca->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .logo {
            max-width: 100px;
            margin-bottom: 10px;
        }
        h1, h2, h3 {
            color: #000;
            margin: 5px 0;
        }
        .section {
            margin-bottom: 15px;
        }
        .section-title {
            background-color: #f4f4f4;
            padding: 5px;
            font-weight: bold;
            border: 1px solid #ddd;
        }
        .info-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .info-grid th, .info-grid td {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: left;
        }
        .info-grid th {
            background-color: #f9f9f9;
            width: 25%;
        }
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .table-data th, .table-data td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: left;
            font-size: 10px;
        }
        .table-data th {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .page-break { page-break-after: always; }
        .signatures {
            margin-top: 40px;
            width: 100%;
        }
        .signature-box {
            width: 45%;
            display: inline-block;
            text-align: center;
            margin-bottom: 30px;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 40px;
            width: 80%;
            margin-left: 10%;
        }
        .currency { white-space: nowrap; }
    </style>
</head>
<body>

    <div class="header">
        @if($pca->prefeitura && $pca->prefeitura->logo)
            <img src="{{ public_path('storage/' . $pca->prefeitura->logo) }}" alt="Logo" class="logo">
        @endif
        <h2>{{ $pca->prefeitura->nome ?? 'Prefeitura' }}</h2>
        <h3>PLANO DE CONTRATAÇÃO ANUAL</h3>
        <h4>Exercício: {{ $pca->exercicio }}</h4>
    </div>

    <div class="section">
        <table class="info-grid">
            <tr>
                <th>Número do PCA:</th>
                <td>{{ $pca->numero_pca ?? $pca->id }}</td>
                <th>Data de Emissão:</th>
                <td>{{ date('d/m/Y') }}</td>
            </tr>
            <tr>
                <th>Período de Elaboração:</th>
                <td colspan="3">
                    @if($pca->periodo_elaboracao_inicio && $pca->periodo_elaboracao_fim)
                        {{ \Carbon\Carbon::parse($pca->periodo_elaboracao_inicio)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($pca->periodo_elaboracao_fim)->format('d/m/Y') }}
                    @else
                        Não informado
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- EQUIPE DE ELABORAÇÃO -->
    <div class="section">
        <div class="section-title">EQUIPE DE ELABORAÇÃO</div>
        @if(!empty($pca->equipe_elaboracao) && is_array($pca->equipe_elaboracao))
            <ul>
                @foreach($pca->equipe_elaboracao as $membro)
                    <li>
                        <strong>{{ $membro['responsavel'] ?? 'N/I' }}</strong> - 
                        Unidade: {{ \App\Models\Unidade::find($membro['unidade_id'])->nome ?? 'N/I' }} 
                        @if(!empty($membro['numero_portaria']))
                            (Portaria: {{ $membro['numero_portaria'] }}
                            @if(!empty($membro['data_portaria']))
                                de {{ \Carbon\Carbon::parse($membro['data_portaria'])->format('d/m/Y') }}
                            @endif)
                        @endif
                    </li>
                @endforeach
            </ul>
        @else
            <p>Nenhuma equipe de elaboração informada.</p>
        @endif
    </div>

    <!-- DETALHAMENTO DO PLANO -->
    <div class="section">
        <div class="section-title">DETALHAMENTO DO PLANO</div>
        <table class="table-data">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="15%">Secretaria/Unidade</th>
                    <th width="12%">Modalidade</th>
                    <th width="25%">Descrição (Classe/Grupo)</th>
                    <th width="12%">Valor Estimado</th>
                    <th width="8%">Prioridade</th>
                    <th width="10%">Início Providências</th>
                    <th width="10%">Conclusão Desejada</th>
                    <th width="3%">Prorrog.</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pca->itens as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item->unidade->nome ?? 'N/I' }}</td>
                        <td>{{ $item->modalidade ?? 'N/I' }}</td>
                        <td>{{ $item->descricao_classe_grupo }}</td>
                        <td class="currency text-right">R$ {{ number_format($item->valor_estimado, 2, ',', '.') }}</td>
                        <td class="text-center">{{ ucfirst($item->grau_prioridade) }}</td>
                        <td class="text-center">{{ $item->data_inicio_providencias ? \Carbon\Carbon::parse($item->data_inicio_providencias)->format('d/m/Y') : '-' }}</td>
                        <td class="text-center">{{ $item->data_desejada_conclusao ? \Carbon\Carbon::parse($item->data_desejada_conclusao)->format('d/m/Y') : '-' }}</td>
                        <td class="text-center">{{ $item->prorrogacao_contrato ? 'Sim' : 'Não' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">Nenhum item adicionado ao plano.</td>
                    </tr>
                @endforelse
            </tbody>
            @if($pca->itens->count() > 0)
            <tfoot>
                <tr>
                    <th colspan="4" class="text-right">VALOR TOTAL ESTIMADO DO PLANO:</th>
                    <th class="currency text-right">R$ {{ number_format($pca->itens->sum('valor_estimado'), 2, ',', '.') }}</th>
                    <th colspan="4"></th>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    <div class="page-break"></div>

    <!-- EQUIPE DE ELABORAÇÃO -->
    <div class="section">
        <div class="section-title">ASSINATURAS DA EQUIPE DE ELABORAÇÃO</div>
        <div class="signatures">
            @if(!empty($pca->equipe_elaboracao) && is_array($pca->equipe_elaboracao))
                @foreach($pca->equipe_elaboracao as $membro)
                    <div class="signature-box">
                        <div class="signature-line"></div>
                        <strong>{{ $membro['responsavel'] ?? 'N/I' }}</strong><br>
                        {{ \App\Models\Unidade::find($membro['unidade_id'])->nome ?? 'N/I' }}
                        @if(!empty($membro['numero_portaria']))
                            <br>Portaria nº {{ $membro['numero_portaria'] }}
                        @endif
                    </div>
                @endforeach
            @else
                <p>Nenhuma equipe de elaboração informada para assinatura.</p>
            @endif
        </div>
    </div>

</body>
</html>
