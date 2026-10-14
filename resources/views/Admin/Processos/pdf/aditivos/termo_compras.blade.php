<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termo Aditivo</title>
    <style>
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
            margin: 0; padding: 4cm 2cm;
            font-size: 12pt; font-family: 'Aptos', sans-serif;
            background-image: url('{{ public_path($prefeitura->timbre ?? '') }}');
            background-repeat: no-repeat; background-position: top left; background-size: cover;
            text-align: justify; text-justify: inter-word; line-height: 1.5;
        }

        .title {
            text-align: center; font-weight: bold; font-size: 14pt; margin-bottom: 30px;
            font-family: 'AptosExtraBold', sans-serif; text-transform: uppercase;
        }

        .clause-title { font-weight: bold; margin-top: 25px; margin-bottom: 10px; }
        .clause-content { text-indent: 40px; }
        
        .signature-section { margin-top: 60px; width: 100%; text-align: center; }
        .signature-box { display: inline-block; width: 45%; vertical-align: top; margin-bottom: 40px; }
        .witness-box { text-align: left; margin-top: 20px; }
    </style>
</head>
<body>
    @php
        \Carbon\Carbon::setLocale('pt_BR');
        
        $strTipo = '';
        if ($incidente->tipo === 'prazo') $strTipo = 'PRORROGAÇÃO DE PRAZO';
        elseif ($incidente->tipo === 'valor') $strTipo = 'ACRÉSCIMO DE VALOR';
        else $strTipo = 'ACRÉSCIMO DE VALOR E PRORROGAÇÃO DE PRAZO';

        // Calcular o valor original do contrato. Contrato Manual/Externo tem o valor
        // direto no cadastro; Contrato do Sistema usa os lotes contratados.
        if ($contrato instanceof \App\Models\ContratoManual) {
            $valorOriginalContrato = (float) $contrato->valor_total;
        } else {
            $valorOriginalContrato = \App\Models\LoteContratado::where('processo_id', $contrato->processo_id)
                ->when($contrato->vencedor_id, function($query) use ($contrato) {
                    return $query->where('vencedor_id', $contrato->vencedor_id);
                })->sum('valor_total');
        }

        $valorStr = '';
        if (in_array($incidente->tipo, ['valor', 'prazo_valor'])) {
            $valorAcrescentado = $valorOriginalContrato * ($incidente->percentual_valor / 100);
            $novoValorGlobal = $valorOriginalContrato + $valorAcrescentado;

            $objetoProcesso = mb_strtoupper(trim(html_entity_decode(strip_tags($objetoContrato ?? ''))));
            $valorStr = 'AUMENTAR o valor previsto no contrato referente a "' . $objetoProcesso . '", em ' . number_format($incidente->percentual_valor, 2, ',', '.') . '% conforme solicitação aprovada pela Autoridade Competente, acrescentando ao valor do contrato a quantia de R$ ' . number_format($valorAcrescentado, 2, ',', '.') . ', passando a vigorar o valor global do contrato em R$ ' . number_format($novoValorGlobal, 2, ',', '.');
        }

        $prazoStr = '';
        if (in_array($incidente->tipo, ['prazo', 'prazo_valor'])) {
            $prazoStr = 'prorrogar o prazo de vigência do instrumento contratual em referência, firmado entre as partes pactuantes, por mais ' . $incidente->meses_prorrogacao . ' meses, compreendendo o período necessário à conclusão dos trabalhos';
        }

        // Resumo da alteração e fundamentação legal, usados no Extrato do Termo Aditivo.
        $resumoAlteracaoExtrato = '';
        if ($incidente->tipo === 'valor') {
            $resumoAlteracaoExtrato = 'Acréscimo de ' . number_format($incidente->percentual_valor, 2, ',', '.') . '% no valor do contrato, passando o valor global a R$ ' . number_format($novoValorGlobal ?? 0, 2, ',', '.') . '.';
        } elseif ($incidente->tipo === 'prazo') {
            $resumoAlteracaoExtrato = 'Prorrogação do prazo de vigência contratual por mais ' . $incidente->meses_prorrogacao . ' meses.';
        } else {
            $resumoAlteracaoExtrato = 'Acréscimo de ' . number_format($incidente->percentual_valor, 2, ',', '.') . '% no valor do contrato (novo valor global: R$ ' . number_format($novoValorGlobal ?? 0, 2, ',', '.') . ') e prorrogação do prazo de vigência por mais ' . $incidente->meses_prorrogacao . ' meses.';
        }

        $fundamentacaoLegalExtrato = match($incidente->tipo) {
            'prazo' => 'Art. 107 da Lei nº 14.133/2021.',
            'valor' => 'Art. 124, inciso I, alínea “a”, da Lei nº 14.133/2021.',
            default => 'Arts. 107 e 124, inciso I, alínea “a”, da Lei nº 14.133/2021.',
        };

        $assinaturaContratanteExtrato = (isset($documentoSelecao) && is_array($documentoSelecao->assinantes) && count($documentoSelecao->assinantes) > 0)
            ? mb_strtoupper($documentoSelecao->assinantes[0]['responsavel'])
            : mb_strtoupper($prefeitura->autoridade_competente ?? 'Prefeito');
    @endphp

    <div class="title">
        TERMO ADITIVO DE {{ $strTipo }} AO CONTRATO Nº {{ $contrato->numero_contrato ?? 'S/N' }}
    </div>

    <div class="clause-content">
        Pelo presente instrumento, de um lado o Município de {{ $prefeitura->cidade ?? 'Cidade' }}, pessoa jurídica de direito público interno, CNPJ {{ $prefeitura->cnpj ?? 'CNPJ' }}, localizada a {{ $prefeitura->endereco ?? 'Endereço' }}, neste ato representada pelo seu titular, <strong>{{ mb_strtoupper($prefeitura->autoridade_competente ?? 'Prefeito') }}</strong>, e do outro lado a empresa <strong>{{ $contrato->dados_contratante['razao_social'] ?? 'CONTRATADA' }}</strong>, CNPJ Nº {{ $contrato->dados_contratante['cnpj'] ?? 'CNPJ' }}, estabelecida à sede na {{ $contrato->dados_contratante['endereco'] ?? 'Endereço da Empresa' }}, neste ato devidamente representada por {{ $contrato->dados_contratante['representante'] ?? 'Representante Legal' }}, denominados <strong>CONTRATANTE</strong> e <strong>CONTRATADO</strong>, têm justo e acordado entre si o presente <strong>TERMO ADITIVO DE {{ $strTipo }}</strong> ao contrato de nº {{ $contrato->numero_contrato ?? 'S/N' }}, mediante as cláusulas e condições seguintes:
    </div>

    <div class="clause-title">CLÁUSULA PRIMEIRA – DO OBJETO</div>
    <div class="clause-content">
        O presente Termo Aditivo tem por objeto 
        @if($valorStr && $prazoStr)
            {{ $valorStr }} e {{ $prazoStr }}
        @elseif($valorStr)
            {{ $valorStr }}
        @elseif($prazoStr)
            {{ ucfirst($prazoStr) }}
        @endif
        , nos termos da Lei Federal nº 14.133/21 e suas alterações
        @if(isset($incidente->categoria) && $incidente->categoria === 'compras_servicos' && $incidente->itens && $incidente->itens->count() > 0)
            , conforme planilha abaixo contendo o quantitativo:
            <table style="width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 10pt; text-indent: 0; text-align: left; page-break-inside: auto;">
        @else
            .
        @endif
        
        @if(isset($incidente->categoria) && $incidente->categoria === 'compras_servicos' && $incidente->itens && $incidente->itens->count() > 0)
                <thead style="display: table-header-group;">
                    <tr style="background-color: #f3f4f6; page-break-inside: avoid;">
                        <th style="border: 1px solid #000; padding: 5px; text-align: center;">Item</th>
                        <th style="border: 1px solid #000; padding: 5px;">Descrição</th>
                        <th style="border: 1px solid #000; padding: 5px; text-align: center;">Qtd. Aditivada</th>
                        <th style="border: 1px solid #000; padding: 5px; text-align: right;">V. Unitário</th>
                        <th style="border: 1px solid #000; padding: 5px; text-align: right;">V. Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($incidente->itens as $item)
                        <tr style="page-break-inside: avoid;">
                            <td style="border: 1px solid #000; padding: 5px; text-align: center;">{{ $item->loteContratado->lote->item ?? '-' }}</td>
                            <td style="border: 1px solid #000; padding: 5px;">{{ $item->loteContratado->lote->descricao ?? 'Item sem descrição' }}</td>
                            <td style="border: 1px solid #000; padding: 5px; text-align: center;">{{ fmod($item->quantidade_aditivada, 1) !== 0.0 ? number_format($item->quantidade_aditivada, 2, ',', '.') : number_format($item->quantidade_aditivada, 0, ',', '.') }}</td>
                            <td style="border: 1px solid #000; padding: 5px; text-align: right;">R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                            <td style="border: 1px solid #000; padding: 5px; text-align: right;">R$ {{ number_format($item->valor_total_aditivado, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="clause-title">CLÁUSULA SEGUNDA – DA RATIFICAÇÃO</div>
    <div class="clause-content">
        Ficam ratificadas as demais cláusulas e condições do Contrato de nº {{ $contrato->numero_contrato ?? 'S/N' }}, desde que não contrariem o que ficou convencionado no presente <strong>Termo Aditivo</strong>.
    </div>

    <div class="clause-title">CLÁUSULA TERCEIRA – DA PUBLICAÇÃO</div>
    <div class="clause-content">
        O Município de {{ $prefeitura->cidade ?? 'Cidade' }} providenciará, sem ônus para a CONTRATADA, a publicação do extrato do presente aditamento no Diário Oficial ou Portal Nacional de Contratações Públicas, no prazo legal exigido.
    </div>

    <div class="clause-content" style="margin-top: 30px;">
        E, por estarem assim justos e acordados, firmam o presente <strong>Termo Aditivo</strong> ao Contrato, em benefício das partes, para que produza seus jurídicos e legais efeitos, na presença de 02 (duas) testemunhas igualmente subscritas.
    </div>

    <div style="margin-top: 40px; text-align: left; margin-left: 40px;">
        {{ $prefeitura->cidade ?? 'Cidade' }}, {{ \Carbon\Carbon::parse($data_selecionada ?? now())->translatedFormat('d \d\e F \d\e Y') }}.
    </div>

    @if(isset($documentoSelecao) && is_array($documentoSelecao->assinantes) && count($documentoSelecao->assinantes) > 0)
        <div class="signature-section" style="margin-top: 60px; width: 100%; text-align: center;">
        @foreach($documentoSelecao->assinantes as $assinante)
            <div class="signature-box" style="display: inline-block; width: 45%; vertical-align: top; margin-bottom: 40px;">
                ___________________________________<br>
                <strong>{{ mb_strtoupper($assinante['responsavel']) }}</strong><br>
                {{ $assinante['unidade_nome'] }}
                @if(!empty($assinante['portaria']))
                <br>Portaria: {{ $assinante['portaria'] }}
                @endif
            </div>
        @endforeach
        </div>
    @else
        <div class="signature-section">
            <div class="signature-box">
                ___________________________________<br>
                <strong>{{ mb_strtoupper($prefeitura->autoridade_competente ?? 'Prefeito') }}</strong><br>
                <strong>CONTRATANTE</strong>
            </div>
            <div class="signature-box">
                ___________________________________<br>
                <strong>{{ $contrato->dados_contratante['razao_social'] ?? 'CONTRATADA' }}</strong><br>
                <strong>CONTRATADA</strong>
            </div>
        </div>
    @endif

    <div class="witness-box">
        <p>Testemunhas:</p>
        <p>1. _____________________________________________ CPF:</p>
        <p>2. _____________________________________________ CPF:</p>
    </div>

    <div class="page-break" style="page-break-after: always;"></div>

    <div>
        <table style="width:100%; border-collapse:collapse; font-size:10px; margin-bottom:20px;" border="1">
            <tr>
                <td colspan="2" style="padding:8px; text-align:center; font-weight:bold;">
                    EXTRATO DO TERMO ADITIVO AO CONTRATO Nº {{ $contrato->numero_contrato ?? 'S/N' }}
                </td>
            </tr>
            <tr>
                <td style="padding:6px; width:30%; font-weight:bold;">OBJETO:</td>
                <td style="padding:6px;">{{ $objetoProcesso ?? mb_strtoupper(trim(html_entity_decode(strip_tags($objetoContrato ?? '')))) }}</td>
            </tr>
            <tr>
                <td style="padding:6px; font-weight:bold;">CONTRATANTE:</td>
                <td style="padding:6px;">{{ $contrato->dados_contratante['orgao_responsavel'] ?? ($prefeitura->nome ?? 'Município') }}</td>
            </tr>
            <tr>
                <td style="padding:6px; font-weight:bold;">CONTRATADO:</td>
                <td style="padding:6px;">{{ $contrato->dados_contratante['razao_social'] ?? 'CONTRATADA' }}</td>
            </tr>
            <tr>
                <td style="padding:6px; font-weight:bold;">CNPJ (CONTRATADO):</td>
                <td style="padding:6px;">{{ $contrato->dados_contratante['cnpj'] ?? 'CNPJ' }}</td>
            </tr>
            <tr>
                <td style="padding:6px; font-weight:bold;">ALTERAÇÃO:</td>
                <td style="padding:6px;">{{ $resumoAlteracaoExtrato }}</td>
            </tr>
            <tr>
                <td style="padding:6px; font-weight:bold;">FUNDAMENTAÇÃO LEGAL:</td>
                <td style="padding:6px; text-align:justify;">{{ $fundamentacaoLegalExtrato }}</td>
            </tr>
            <tr>
                <td style="padding:6px; font-weight:bold;">ASSINATURA (CONTRATANTE):</td>
                <td style="padding:6px;">{{ $assinaturaContratanteExtrato }}</td>
            </tr>
            <tr>
                <td style="padding:6px; font-weight:bold;">ASSINATURA (CONTRATADO):</td>
                <td style="padding:6px;">{{ $contrato->dados_contratante['representante'] ?? 'Representante Legal' }}</td>
            </tr>
            <tr>
                <td style="padding:6px; font-weight:bold;">DATA DA ASSINATURA:</td>
                <td style="padding:6px;">{{ \Carbon\Carbon::parse($data_selecionada ?? now())->translatedFormat('d \d\e F \d\e Y') }}</td>
            </tr>
        </table>
    </div>

</body>
</html>
