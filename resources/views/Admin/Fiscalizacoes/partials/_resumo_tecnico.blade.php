{{--
    Resumo técnico da fiscalização — compartilhado entre relatorio.blade.php e
    relatorio_tecnico.blade.php, para os dois PDFs não divergirem no futuro.

    Depende de variáveis já calculadas pelos templates que o incluem:
    $fiscalizacao, $info (dados do contrato) e $labels (labels dinâmicas por
    tipo de contrato — 'execucao', 'qualidade', 'obs_servidor').
--}}
@php
    $ocorrenciaEstruturada = in_array($tipo, ['compras', 'servicos'], true);
@endphp

{{-- Checklist de Verificação (Compras e Serviços) --}}
@if($ocorrenciaEstruturada)
    <div class="sec-container">
        <div class="sec-heading">CHECKLIST DE VERIFICAÇÃO PRELIMINAR</div>
        <table class="checklist-table">
            <tbody>
                @foreach(collect(\App\Models\Fiscalizacao::CHECKLIST_ITENS)->chunk(2) as $par)
                    <tr>
                        @foreach($par as $chave => $rotulo)
                            @php $marcado = (bool) data_get($fiscalizacao->checklist_fiscalizacao, $chave); @endphp
                            <td class="checklist-cell">
                                <span class="checkbox-box {{ $marcado ? 'checked' : '' }}">
                                    {!! $marcado ? 'x' : '&nbsp;' !!}
                                </span>
                                <span class="chk-label {{ $marcado ? 'chk-active' : 'chk-inactive' }}">{{ $rotulo }}</span>
                            </td>
                        @endforeach
                        @if($par->count() < 2)
                            <td class="checklist-cell"></td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

{{-- Metodologia Aplicada (exclusivo de Obras) --}}
@if($tipo === 'obras' && $fiscalizacao->metodologia_fiscalizacao)
    <div class="sec-container">
        <div class="sec-heading">METODOLOGIA DE FISCALIZAÇÃO APLICADA</div>
        <div class="text-box">
            {{ $fiscalizacao->metodologia_fiscalizacao }}
        </div>
    </div>
@endif

{{-- Execução do Objeto / No Período --}}
<div class="sec-container">
    <div class="sec-heading">{{ $ocorrenciaEstruturada ? '1. ' : '' }}{{ mb_strtoupper($labels['execucao']) }}</div>
    <div class="text-box">
        {{ $fiscalizacao->execucao_objeto ?? 'Não informado.' }}
    </div>
</div>

{{-- Ocorrências e Irregularidades --}}
<div class="sec-container">
    <div class="sec-heading">{{ $ocorrenciaEstruturada ? '2. ' : '' }}{{ $ocorrenciaEstruturada ? 'OCORRÊNCIAS REGISTRADAS' : 'IRREGULARIDADES OBSERVADAS' }}</div>
    @if($ocorrenciaEstruturada)
        <div class="status-badge-box {{ $fiscalizacao->houve_ocorrencia ? 'badge-alert' : 'badge-ok' }}">
            <strong>Situação no Período:</strong> 
            {{ $fiscalizacao->houve_ocorrencia ? 'Houve registro de ocorrência(s)/anomalia(s)' : 'Não houve registro de ocorrências' }}
        </div>
        @if($fiscalizacao->houve_ocorrencia)
            <div class="text-box mt-2">
                <strong>Descrição da(s) Ocorrência(s):</strong><br>
                {{ $fiscalizacao->irregularidade_observada ?? 'Não informado.' }}
            </div>
            @if($fiscalizacao->providencias_adotadas)
                <div class="text-box mt-2">
                    <strong>Providências Imediatas Adotadas:</strong><br>
                    {{ $fiscalizacao->providencias_adotadas }}
                </div>
            @endif
        @endif
    @else
        <div class="text-box">
            {{ $fiscalizacao->irregularidade_observada ?? 'Nenhuma irregularidade observada durante a vistoria.' }}
        </div>
    @endif
</div>

{{-- Qualidade das Entregas / Serviços --}}
<div class="sec-container">
    <div class="sec-heading">{{ $ocorrenciaEstruturada ? '3. ' : '' }}{{ mb_strtoupper($labels['qualidade']) }}</div>
    <div class="text-box">
        {{ $fiscalizacao->qualidade_entregas ?? 'Não informado.' }}
    </div>
</div>

{{-- Pontualidade e Cumprimento dos Prazos --}}
<div class="sec-container">
    <div class="sec-heading">{{ $ocorrenciaEstruturada ? '4. ' : '' }}PONTUALIDADE E CUMPRIMENTO DOS PRAZOS</div>
    <div class="text-box">
        {{ $fiscalizacao->pontualidade_prazos ?? 'Não informado.' }}
    </div>
</div>

{{-- Regularidade Fiscal e Trabalhista --}}
<div class="sec-container">
    <div class="sec-heading">
        {{ $ocorrenciaEstruturada ? '5. ' : '' }}COMPROVAÇÃO DE REGULARIDADE FISCAL E TRABALHISTA
    </div>
    <div class="text-box">
        {{ $fiscalizacao->regularidade_fiscal_trabalhista ?? 'Não informado.' }}
    </div>
</div>

{{-- Comunicação e Atendimento (exclusivo de Obras) --}}
@if($tipo === 'obras' && $fiscalizacao->comunicacao_atendimento)
    <div class="sec-container">
        <div class="sec-heading">COMUNICAÇÃO E ATENDIMENTO DA CONTRATADA</div>
        <div class="text-box">
            {{ $fiscalizacao->comunicacao_atendimento }}
        </div>
    </div>
@endif

{{-- Observações do Servidor / Fiscal --}}
<div class="sec-container">
    <div class="sec-heading">{{ $ocorrenciaEstruturada ? '6. ' : '' }}{{ mb_strtoupper($labels['obs_servidor']) }}</div>
    <div class="text-box">
        {{ $fiscalizacao->observacoes_servidor ?? 'Sem observações adicionais.' }}
    </div>
</div>

{{-- Recomendações Gestor e Empresa (para compras / serviços se estruturado) --}}
@if($ocorrenciaEstruturada)
    <div class="sec-container">
        <div class="sec-heading">7. RECOMENDAÇÕES A SEREM ADOTADAS PELO GESTOR</div>
        <div class="text-box">
            {{ $fiscalizacao->recomendacoes_gestor ?? 'Sem recomendações específicas para o gestor até o momento.' }}
        </div>
    </div>

    <div class="sec-container">
        <div class="sec-heading">8. RECOMENDAÇÕES A SEREM ADOTADAS PELA EMPRESA CONTRATADA</div>
        <div class="text-box">
            {{ $fiscalizacao->recomendacoes_empresa ?? 'Manter a regularidade na prestação dos serviços/entrega dos produtos conforme edital e contrato.' }}
        </div>
    </div>
@endif
