<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Capa do Aditivo</title>
    <style>
        @page { margin: 0; size: A4; }

        body {
            margin: 0; padding: 6cm 2cm;
            font-size: 12pt; font-family: Arial, Helvetica, sans-serif;
            background-image: url('{{ public_path($prefeitura->timbre ?? '') }}');
            background-repeat: no-repeat; background-position: top left; background-size: cover;
            text-align: center; line-height: 1.3;
        }

        .title {
            font-weight: bold; font-size: 16pt; margin-top: 20px; margin-bottom: 40px;
            text-transform: uppercase;
        }

        table {
            width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 12pt;
        }

        td {
            border: 1px solid #000; padding: 8px 10px; text-align: left; vertical-align: middle;
        }

        .td-label {
            width: 30%; font-weight: bold; text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="title">
        {{ $vm->tituloDocumento() }}
    </div>

    @php
        // Usa a unidade selecionada em "Definir Assinantes" pra esse documento,
        // se houver; senão cai no órgão padrão do contrato (ou na prefeitura).
        $usaUnidadeSelecionada = isset($documentoSelecao) && is_array($documentoSelecao->assinantes) && count($documentoSelecao->assinantes) > 0 && ! empty($documentoSelecao->assinantes[0]['unidade_nome']);
        $orgaoSetorCapa = $usaUnidadeSelecionada
            ? $documentoSelecao->assinantes[0]['unidade_nome']
            : ($contrato->dados_contratante['orgao_responsavel'] ?? null);
        // Unidade do prefeito ("Prefeito Municipal", "Secretaria Municipal" genérica ou
        // cargo de prefeito) vira o nome da Prefeitura (ver Prefeitura::nomeOrgaoContratante).
        $orgaoSetorCapa = $prefeitura->nomeOrgaoContratante($orgaoSetorCapa, $usaUnidadeSelecionada ? ($documentoSelecao->assinantes[0]['cargo'] ?? null) : null);
    @endphp
    <table>
        <tr>
            <td class="td-label">ORGÃO/SETOR</td>
            <td>{{ mb_strtoupper($orgaoSetorCapa) }}</td>
        </tr>
        <tr>
            <td class="td-label">OBJETIVO</td>
            <td style="font-weight: bold; text-transform: uppercase;">{{ $vm->tituloTipoAlteracao() }} DO CONTRATO Nº {{ $contrato->numero_contrato ?? 'S/N' }}</td>
        </tr>
    </table>
</body>
</html>
