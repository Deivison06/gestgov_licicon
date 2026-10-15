<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termo Aditivo</title>
    <style>
        @include('Admin.Processos.pdf.aditivos.partials.estilo-base')
    </style>
</head>
<body>
    @php
        \Carbon\Carbon::setLocale('pt_BR');

        $blocos = $textoJuridico->clausulaObjetoTermoAditivo($vm);
        $resumo = $vm->resumoFinanceiro;

        $itensAcrescidos = $incidente->itens->filter(fn ($i) => $i->quantidade_aditivada > 0);
        $itensSuprimidos = $incidente->itens->filter(fn ($i) => $i->quantidade_aditivada < 0);

        $assinaturaContratanteExtrato = (isset($documentoSelecao) && is_array($documentoSelecao->assinantes) && count($documentoSelecao->assinantes) > 0)
            ? mb_strtoupper($documentoSelecao->assinantes[0]['responsavel'])
            : mb_strtoupper($prefeitura->autoridade_competente ?? 'Prefeito');

        $fundamentacaoLegalExtrato = collect([
            $vm->envolvePrazo() ? ($vm->artigoFundamentacaoPrazo().' da Lei nº 14.133/2021') : null,
            $vm->envolveValor() ? ('art. 124, inciso I, alínea "'.$vm->alineaArt124().'", da Lei nº 14.133/2021') : null,
        ])->filter()->implode('; ');
    @endphp

    <div class="title">{{ $vm->tituloDocumento() }}</div>

    <div class="clause-content">
        Pelo presente instrumento, de um lado o Município de {{ $prefeitura->cidade ?? 'Cidade' }}, pessoa jurídica de direito público interno, CNPJ {{ $prefeitura->cnpj ?? 'CNPJ' }}, localizada a {{ $prefeitura->endereco ?? 'Endereço' }}, neste ato representada pelo seu titular, <strong>{{ mb_strtoupper($prefeitura->autoridade_competente ?? 'Prefeito') }}</strong>, e do outro lado a empresa <strong>{{ $contrato->dados_contratante['razao_social'] ?? 'CONTRATADA' }}</strong>, CNPJ Nº {{ $contrato->dados_contratante['cnpj'] ?? 'CNPJ' }}, estabelecida à sede na {{ $contrato->dados_contratante['endereco'] ?? 'Endereço da Empresa' }}, neste ato devidamente representada por {{ $contrato->dados_contratante['representante'] ?? 'Representante Legal' }}, denominados <strong>CONTRATANTE</strong> e <strong>CONTRATADO</strong>, têm justo e acordado entre si o presente <strong>{{ $vm->numeroOrdinalFormatado() }} TERMO ADITIVO</strong> ao contrato de nº {{ $vm->numeroContrato }}, mediante as cláusulas e condições seguintes:
    </div>

    <div class="clause-title">CLÁUSULA PRIMEIRA – DO OBJETO</div>
    <div class="clause-content">
        O presente Termo Aditivo tem por objeto:
    </div>
    <ul class="list-content">
        @if(isset($blocos['prazo']))
            <li>{{ $blocos['prazo'] }};</li>
        @endif
        @if(isset($blocos['objeto_alteracao']))
            <li>{{ $blocos['objeto_alteracao'] }}.</li>
        @endif
    </ul>

    @if(isset($blocos['acrescimo']))
        <div class="clause-content">{{ $blocos['acrescimo'] }}</div>
    @endif
    @if(isset($blocos['supressao']))
        <div class="clause-content">{{ $blocos['supressao'] }}</div>
    @endif
    @if(isset($blocos['valor_global']))
        <div class="clause-content">{{ $blocos['valor_global'] }}</div>
    @endif

    @if($itensAcrescidos->isNotEmpty())
        <div class="clause-content" style="text-indent: 0;">Itens acrescidos, conforme quadro abaixo:</div>
        <table style="width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 15px; font-size: 10pt; text-align: left;">
            <thead>
                <tr style="background-color: #f3f4f6;">
                    <th style="border: 1px solid #000; padding: 5px; text-align: center;">Item</th>
                    <th style="border: 1px solid #000; padding: 5px;">Descrição</th>
                    <th style="border: 1px solid #000; padding: 5px; text-align: center;">Qtd. Acrescida</th>
                    <th style="border: 1px solid #000; padding: 5px; text-align: right;">V. Unitário</th>
                    <th style="border: 1px solid #000; padding: 5px; text-align: right;">V. Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($itensAcrescidos as $item)
                    <tr>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">{{ $item->loteContratado->lote->item ?? '-' }}</td>
                        <td style="border: 1px solid #000; padding: 5px;">{{ $item->loteContratado->lote->descricao ?? 'Item sem descrição' }}</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">{{ number_format(abs($item->quantidade_aditivada), 2, ',', '.') }}</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: right;">R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: right;">R$ {{ number_format(abs($item->valor_total_aditivado), 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($itensSuprimidos->isNotEmpty())
        <div class="clause-content" style="text-indent: 0;">Itens suprimidos, conforme quadro abaixo:</div>
        <table style="width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 15px; font-size: 10pt; text-align: left;">
            <thead>
                <tr style="background-color: #f3f4f6;">
                    <th style="border: 1px solid #000; padding: 5px; text-align: center;">Item</th>
                    <th style="border: 1px solid #000; padding: 5px;">Descrição</th>
                    <th style="border: 1px solid #000; padding: 5px; text-align: center;">Qtd. Suprimida</th>
                    <th style="border: 1px solid #000; padding: 5px; text-align: right;">V. Unitário</th>
                    <th style="border: 1px solid #000; padding: 5px; text-align: right;">V. Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($itensSuprimidos as $item)
                    <tr>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">{{ $item->loteContratado->lote->item ?? '-' }}</td>
                        <td style="border: 1px solid #000; padding: 5px;">{{ $item->loteContratado->lote->descricao ?? 'Item sem descrição' }}</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">{{ number_format(abs($item->quantidade_aditivada), 2, ',', '.') }}</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: right;">R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: right;">R$ {{ number_format(abs($item->valor_total_aditivado), 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if(isset($blocos['dotacao']))
        <div class="clause-content">{{ $blocos['dotacao'] }}</div>
    @endif

    <div class="clause-title">CLÁUSULA SEGUNDA – DA RATIFICAÇÃO</div>
    <div class="clause-content">
        Ficam ratificadas as demais cláusulas e condições do Contrato de nº {{ $vm->numeroContrato }}, desde que não contrariem o que ficou convencionado no presente <strong>Termo Aditivo</strong>.
    </div>

    <div class="clause-title">CLÁUSULA TERCEIRA – DA PUBLICAÇÃO</div>
    <div class="clause-content">
        O Município de {{ $prefeitura->cidade ?? 'Cidade' }} – {{ $prefeitura->uf ?? 'PI' }} providenciará, sem ônus para a CONTRATADA, a publicação do presente Termo Aditivo nos meios legais.
    </div>

    <div class="clause-content" style="margin-top: 30px;">
        E, por estarem assim justos e acordados, as partes firmam o presente <strong>Termo Aditivo</strong> ao Contrato nº {{ $vm->numeroContrato }}, para que produza seus jurídicos e legais efeitos.
    </div>

    <div style="margin-top: 40px; text-align: left; margin-left: 40px;">
        {{ $prefeitura->cidade ?? 'Cidade' }}, {{ \Carbon\Carbon::parse($data_selecionada ?? now())->translatedFormat('d \d\e F \d\e Y') }}.
    </div>

    @if(isset($documentoSelecao) && is_array($documentoSelecao->assinantes) && count($documentoSelecao->assinantes) > 0)
        <div class="signature-section">
        @foreach($documentoSelecao->assinantes as $assinante)
            <div class="signature-box">
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

    <div class="page-break"></div>

    <div>
        <table style="width:100%; border-collapse:collapse; font-size:10px; margin-bottom:20px;" border="1">
            <tr>
                <td colspan="2" style="padding:8px; text-align:center; font-weight:bold;">
                    EXTRATO DO {{ $vm->numeroOrdinalFormatado() }} TERMO ADITIVO AO CONTRATO Nº {{ $vm->numeroContrato }}
                </td>
            </tr>
            <tr>
                <td style="padding:6px; width:30%; font-weight:bold;">OBJETO:</td>
                <td style="padding:6px;">{{ $vm->objetoContrato }}</td>
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
                <td style="padding:6px;">{{ $vm->tituloTipoAlteracao() }}{{ $vm->envolveValor() ? ' — novo valor global: R$ '.number_format($resumo['valor_final_contrato'] ?? 0, 2, ',', '.') : '' }}{{ $vm->envolvePrazo() ? ' — prorrogação de '.$incidente->meses_prorrogacao.' meses' : '' }}.</td>
            </tr>
            <tr>
                <td style="padding:6px; font-weight:bold;">FUNDAMENTAÇÃO LEGAL:</td>
                <td style="padding:6px; text-align:justify;">{{ $fundamentacaoLegalExtrato }}.</td>
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
