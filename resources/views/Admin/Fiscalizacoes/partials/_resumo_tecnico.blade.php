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

<div class="resumo-item">
    <span class="resumo-label">Contrato e Contratada:</span>
    <span class="resumo-texto">{{ $info['numero_contrato'] }} — {{ $info['razao_social'] }} ({{ $info['cnpj'] }})</span>
</div>

{{-- Checklist de Verificação (Compras e Serviços) --}}
@if($ocorrenciaEstruturada)
    <div class="resumo-item">
        <span class="resumo-label">Checklist de Verificação:</span>
        <table class="checklist-table">
            @foreach(collect(\App\Models\Fiscalizacao::CHECKLIST_ITENS)->chunk(2) as $par)
                <tr>
                    @foreach($par as $chave => $rotulo)
                        @php $marcado = (bool) data_get($fiscalizacao->checklist_fiscalizacao, $chave); @endphp
                        <td class="checklist-cell">
                            <span class="checkbox-box {{ $marcado ? 'checked' : '' }}">{{ $marcado ? 'X' : '' }}</span>
                            <span class="{{ $marcado ? '' : 'checklist-pendente' }}">{{ $rotulo }}</span>
                        </td>
                    @endforeach
                    @if($par->count() < 2)
                        <td class="checklist-cell"></td>
                    @endif
                </tr>
            @endforeach
        </table>
    </div>
@endif

{{-- Metodologia Aplicada (exclusivo de Obras) --}}
@if($tipo === 'obras' && $fiscalizacao->metodologia_fiscalizacao)
    <div class="resumo-item">
        <span class="resumo-label">Metodologia Aplicada:</span>
        <span class="resumo-texto">{{ $fiscalizacao->metodologia_fiscalizacao }}</span>
    </div>
@endif

<div class="resumo-item">
    <span class="resumo-label">{{ $ocorrenciaEstruturada ? '1. ' : '' }}{{ $labels['execucao'] }}:</span>
    <span class="resumo-texto">{{ $fiscalizacao->execucao_objeto ?? 'Não informado.' }}</span>
</div>

{{-- Ocorrências --}}
<div class="resumo-item">
    <span class="resumo-label">{{ $ocorrenciaEstruturada ? '2. Ocorrências' : 'Irregularidades Observadas' }}:</span>
    @if($ocorrenciaEstruturada)
        @if($fiscalizacao->houve_ocorrencia)
            <span class="resumo-texto">☑ Houve ocorrências, conforme descrição abaixo:</span>
            <span class="resumo-texto">{{ $fiscalizacao->irregularidade_observada ?? 'Não informado.' }}</span>
            @if($fiscalizacao->providencias_adotadas)
                <span class="resumo-label" style="margin-top: 8px;">Providências adotadas:</span>
                <span class="resumo-texto">{{ $fiscalizacao->providencias_adotadas }}</span>
            @endif
        @else
            <span class="resumo-texto">☑ Não houve ocorrências.</span>
        @endif
    @else
        <span class="resumo-texto">{{ $fiscalizacao->irregularidade_observada ?? 'Nenhuma irregularidade observada durante o período.' }}</span>
    @endif
</div>

<div class="resumo-item">
    <span class="resumo-label">{{ $ocorrenciaEstruturada ? '3. ' : '' }}{{ $labels['qualidade'] }}:</span>
    <span class="resumo-texto">{{ $fiscalizacao->qualidade_entregas ?? 'Não informado.' }}</span>
</div>

<div class="resumo-item">
    <span class="resumo-label">{{ $ocorrenciaEstruturada ? '4. ' : '' }}Pontualidade / Cumprimento dos Prazos:</span>
    <span class="resumo-texto">{{ $fiscalizacao->pontualidade_prazos ?? 'Não informado.' }}</span>
</div>

<div class="resumo-item">
    <span class="resumo-label">
        {{ $ocorrenciaEstruturada ? '5. A Empresa tem apresentado comprovação de Regularidade Fiscal e Trabalhista?' : 'Regularidade Fiscal e Trabalhista' }}:
    </span>
    <span class="resumo-texto">{{ $fiscalizacao->regularidade_fiscal_trabalhista ?? 'Não informado.' }}</span>
</div>

{{-- Comunicação e Atendimento (exclusivo de Obras) --}}
@if($tipo === 'obras' && $fiscalizacao->comunicacao_atendimento)
    <div class="resumo-item">
        <span class="resumo-label">Comunicação e Atendimento:</span>
        <span class="resumo-texto">{{ $fiscalizacao->comunicacao_atendimento }}</span>
    </div>
@endif

<div class="resumo-item">
    <span class="resumo-label">{{ $ocorrenciaEstruturada ? '6. ' : '' }}{{ $labels['obs_servidor'] }}:</span>
    <span class="resumo-texto">{{ $fiscalizacao->observacoes_servidor ?? 'Sem observações adicionais.' }}</span>
</div>

@if($ocorrenciaEstruturada)
    <div class="resumo-item">
        <span class="resumo-label">7. Recomendações a serem adotadas pelo Gestor:</span>
        <span class="resumo-texto">{{ $fiscalizacao->recomendacoes_gestor ?? 'Sem recomendações específicas para o gestor até o momento.' }}</span>
    </div>

    <div class="resumo-item">
        <span class="resumo-label">8. Recomendações a serem adotadas pela Empresa:</span>
        <span class="resumo-texto">{{ $fiscalizacao->recomendacoes_empresa ?? 'Manter a regularidade na prestação dos serviços/entrega dos produtos conforme edital.' }}</span>
    </div>
@endif
