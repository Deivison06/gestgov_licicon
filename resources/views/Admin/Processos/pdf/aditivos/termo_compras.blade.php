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

        $temAssinanteSelecionado = isset($documentoSelecao) && is_array($documentoSelecao->assinantes) && count($documentoSelecao->assinantes) > 0;

        $assinaturaContratanteExtrato = $temAssinanteSelecionado
            ? \App\Helpers\NomeProprio::formatar($documentoSelecao->assinantes[0]['responsavel'])
            : \App\Helpers\NomeProprio::formatar($prefeitura->autoridade_competente ?? 'Prefeito');

        // Órgão que assina o aditivo: a unidade/secretaria selecionada pra esse
        // documento, ou a prefeitura (titular = prefeito) se nada foi selecionado.
        // Unidade "Prefeito Municipal" vira o nome da Prefeitura (ver Prefeitura::nomeOrgaoContratante).
        $orgaoContratanteExtrato = $prefeitura
            ? $prefeitura->nomeOrgaoContratante($temAssinanteSelecionado && ! empty($documentoSelecao->assinantes[0]['unidade_nome'])
                ? $documentoSelecao->assinantes[0]['unidade_nome']
                : ($contrato->dados_contratante['orgao_responsavel'] ?? null))
            : 'Município';

        // "neste ato representado pelo Prefeito Municipal, senhor(a) Fulano" (ver Prefeitura::representanteContratante)
        $representanteContratanteTermo = $prefeitura
            ? $prefeitura->representanteContratante($temAssinanteSelecionado ? $documentoSelecao->assinantes[0] : null)
            : 'pelo Prefeito Municipal';

        $fundamentacaoLegalExtrato = collect([
            $vm->envolvePrazo() ? ($vm->artigoFundamentacaoPrazo().' da Lei nº 14.133/2021') : null,
            $vm->envolveValor() ? ('art. 124, inciso I, alínea "'.$vm->alineaArt124().'", da Lei nº 14.133/2021') : null,
        ])->filter()->implode('; ');
    @endphp

    <div class="title">{{ $vm->tituloDocumento() }}</div>

    <div class="clause-content">
        Pelo presente instrumento, de um lado o Município de {{ $prefeitura->cidade ?? 'Cidade' }}, pessoa jurídica de direito público interno, CNPJ {{ $prefeitura->cnpj ?? 'CNPJ' }}, localizada a {{ $prefeitura->endereco ?? 'Endereço' }}, neste ato representado {{ $representanteContratanteTermo }}, e do outro lado a empresa <strong>{{ $contrato->dados_contratante['razao_social'] ?? 'CONTRATADA' }}</strong>, CNPJ Nº {{ $contrato->dados_contratante['cnpj'] ?? 'CNPJ' }}, estabelecida à sede na {{ $contrato->dados_contratante['endereco'] ?? 'Endereço da Empresa' }}, neste ato devidamente representada por {{ $contrato->dados_contratante['representante'] ?? 'Representante Legal' }}, denominados <strong>CONTRATANTE</strong> e <strong>CONTRATADO</strong>, têm justo e acordado entre si o presente <strong>{{ $vm->numeroOrdinalFormatado() }} TERMO ADITIVO</strong> ao contrato de nº {{ $vm->numeroContrato }}, mediante as cláusulas e condições seguintes:
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
        <table class="tabela">
            <thead>
                <tr>
                    <th style="text-align: center;">Item</th>
                    <th>Descrição</th>
                    <th style="text-align: center;">Qtd. Acrescida</th>
                    <th style="text-align: right;">V. Unitário</th>
                    <th style="text-align: right;">V. Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($itensAcrescidos as $item)
                    <tr>
                        <td style="text-align: center;">{{ $item->loteContratado->lote->item ?? '-' }}</td>
                        <td>{{ $item->loteContratado->lote->descricao ?? 'Item sem descrição' }}</td>
                        <td style="text-align: center;">{{ number_format(abs($item->quantidade_aditivada), 2, ',', '.') }}</td>
                        <td style="text-align: right;">R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                        <td style="text-align: right;">R$ {{ number_format(abs($item->valor_total_aditivado), 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($itensSuprimidos->isNotEmpty())
        <div class="clause-content" style="text-indent: 0;">Itens suprimidos, conforme quadro abaixo:</div>
        <table class="tabela">
            <thead>
                <tr>
                    <th style="text-align: center;">Item</th>
                    <th>Descrição</th>
                    <th style="text-align: center;">Qtd. Suprimida</th>
                    <th style="text-align: right;">V. Unitário</th>
                    <th style="text-align: right;">V. Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($itensSuprimidos as $item)
                    <tr>
                        <td style="text-align: center;">{{ $item->loteContratado->lote->item ?? '-' }}</td>
                        <td>{{ $item->loteContratado->lote->descricao ?? 'Item sem descrição' }}</td>
                        <td style="text-align: center;">{{ number_format(abs($item->quantidade_aditivada), 2, ',', '.') }}</td>
                        <td style="text-align: right;">R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                        <td style="text-align: right;">R$ {{ number_format(abs($item->valor_total_aditivado), 2, ',', '.') }}</td>
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
        O Município de {{ $prefeitura->cidade ?? 'Cidade' }} providenciará, sem ônus para a CONTRATADA, a publicação do presente Termo Aditivo nos meios legais.
    </div>

    <div class="clause-content">
        E, por estarem assim justos e acordados, as partes firmam o presente <strong>Termo Aditivo</strong> ao Contrato nº {{ $vm->numeroContrato }}, para que produza seus jurídicos e legais efeitos.
    </div>

    {{-- Data + assinaturas juntas: não deixa a data sozinha no fim da página --}}
    <div class="bloco-final">
    @include('Admin.Processos.pdf.aditivos.partials.data-local')

    @if(isset($documentoSelecao) && is_array($documentoSelecao->assinantes) && count($documentoSelecao->assinantes) > 0)
        <div class="signature-section">
        @foreach($documentoSelecao->assinantes as $assinante)
            <div class="signature-box">
                ___________________________________<br>
                <strong>{{ \App\Helpers\NomeProprio::formatar($assinante['responsavel']) }}</strong><br>
                {{ $assinante['unidade_nome'] }}
                @if(!empty($assinante['portaria']))
                <br>Portaria: {{ $assinante['portaria'] }}
                @endif
            </div>
        @endforeach
            <div class="signature-box">
                ___________________________________<br>
                <strong>{{ $contrato->dados_contratante['razao_social'] ?? 'CONTRATADA' }}</strong><br>
                <strong>CONTRATADA</strong>
            </div>
        </div>
    @else
        <div class="signature-section">
            <div class="signature-box">
                ___________________________________<br>
                <strong>{{ \App\Helpers\NomeProprio::formatar($prefeitura->autoridade_competente ?? 'Prefeito') }}</strong><br>
                <strong>CONTRATANTE</strong>
            </div>
            <div class="signature-box">
                ___________________________________<br>
                <strong>{{ $contrato->dados_contratante['razao_social'] ?? 'CONTRATADA' }}</strong><br>
                <strong>CONTRATADA</strong>
            </div>
        </div>
    @endif
    </div>

    <div class="page-break"></div>

    <div>
        <table class="tabela">
            <tr>
                <td colspan="2" class="cabecalho">
                    EXTRATO DO {{ $vm->numeroOrdinalFormatado() }} TERMO ADITIVO AO CONTRATO Nº {{ $vm->numeroContrato }}
                </td>
            </tr>
            <tr>
                <td class="rotulo">OBJETO:</td>
                <td>{{ $vm->objetoContrato }}</td>
            </tr>
            <tr>
                <td class="rotulo">CONTRATANTE:</td>
                <td>{{ $orgaoContratanteExtrato }}</td>
            </tr>
            <tr>
                <td class="rotulo">CONTRATADO:</td>
                <td>{{ $contrato->dados_contratante['razao_social'] ?? 'CONTRATADA' }}</td>
            </tr>
            <tr>
                <td class="rotulo">CNPJ (CONTRATADO):</td>
                <td>{{ $contrato->dados_contratante['cnpj'] ?? 'CNPJ' }}</td>
            </tr>
            <tr>
                <td class="rotulo">ALTERAÇÃO:</td>
                <td>
                    @php
                        $mesesExtratoNum = (int) ($incidente->meses_prorrogacao ?? 0);
                        $mesesExtratoExt = strtoupper(\App\Helpers\ValorPorExtenso::numero($mesesExtratoNum));
                        $periodoExtratoTexto = '';
                        if ($vm->envolvePrazo() && $mesesExtratoNum > 0 && $incidente->data_finalizacao_base) {
                            $dataBaseExtrato = \Carbon\Carbon::parse($incidente->data_finalizacao_base);
                            $dataFimExtrato = $dataBaseExtrato->copy()->addMonths($mesesExtratoNum);
                            $periodoExtratoTexto = ', de ' . $dataBaseExtrato->translatedFormat('d/m/Y') . ' a ' . $dataFimExtrato->translatedFormat('d/m/Y');
                        }
                    @endphp
                    {{ $vm->tituloTipoAlteracao() }}{{ $vm->envolveValor() ? ' — novo valor global: R$ '.number_format($resumo['valor_final_contrato'] ?? 0, 2, ',', '.') : '' }}{{ $vm->envolvePrazo() && $mesesExtratoNum > 0 ? " — prorrogação de {$mesesExtratoNum} ({$mesesExtratoExt}) " . ($mesesExtratoNum === 1 ? 'mês' : 'meses') . $periodoExtratoTexto : '' }}.
                </td>
            </tr>
            <tr>
                <td class="rotulo">FUNDAMENTAÇÃO LEGAL:</td>
                <td>{{ $fundamentacaoLegalExtrato }}.</td>
            </tr>
            <tr>
                <td class="rotulo">ASSINATURA (CONTRATANTE):</td>
                <td>{{ $assinaturaContratanteExtrato }}</td>
            </tr>
            <tr>
                <td class="rotulo">ASSINATURA (CONTRATADO):</td>
                <td>{{ $contrato->dados_contratante['representante'] ?? 'Representante Legal' }}</td>
            </tr>
            <tr>
                <td class="rotulo">DATA DA ASSINATURA:</td>
                <td>{{ \Carbon\Carbon::parse($data_selecionada ?? now())->translatedFormat('d \d\e F \d\e Y') }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
