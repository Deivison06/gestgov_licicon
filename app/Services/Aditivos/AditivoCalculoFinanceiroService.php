<?php

namespace App\Services\Aditivos;

use App\Models\Contrato;
use App\Models\ContratoManual;
use App\Models\IncidenteContratual;
use App\Models\LoteContratado;

/**
 * Cálculos financeiros exigidos pela atualização do módulo de Aditivos
 * (apontamentos do Tribunal de Contas): valor inicial atualizado do
 * contrato, percentuais acumulados de acréscimo/supressão (sempre sobre o
 * valor inicial atualizado — nunca compostos, igual à forma como a Lei nº
 * 14.133/2021 mede os limites do art. 125) e o número ordinal do aditivo.
 *
 * Somente leitura/exibição — não altera IncidenteContratualService::
 * aplicarEfeitosNoContrato nem reverterEfeitosNoContrato.
 */
class AditivoCalculoFinanceiroService
{
    /**
     * Valor inicial do contrato, antes de qualquer aditivo.
     *
     * - ContratoManual: valor_total_original (fixado uma única vez, nunca
     *   mutado — ver migration de backfill).
     * - Contrato (sistema): nunca é armazenado diretamente — é sempre
     *   derivado da soma dos LoteContratado vinculados (mesmo fallback
     *   contrato_id -> processo_id já usado em IncidenteContratualService).
     */
    public function valorInicialAtualizado(Contrato|ContratoManual $contrato): float
    {
        if ($contrato instanceof ContratoManual) {
            return (float) ($contrato->valor_total_original ?? $contrato->valor_total);
        }

        $lotes = LoteContratado::where('contrato_id', $contrato->id)->sum('valor_total');

        if ($lotes <= 0) {
            $lotes = LoteContratado::where('processo_id', $contrato->processo_id)->sum('valor_total');
        }

        return (float) $lotes;
    }

    /**
     * Aditivos anteriores do mesmo contrato (mesmo contratavel_id +
     * contratavel_type) que envolvem valor, ordenados por ordem de criação.
     */
    private function aditivosAnterioresDeValor(IncidenteContratual $atual, Contrato|ContratoManual $contrato)
    {
        return IncidenteContratual::where('contratavel_id', $contrato->id)
            ->where('contratavel_type', get_class($contrato))
            ->where('id', '<', $atual->id ?? 0)
            ->whereIn('tipo', ['valor', 'prazo_valor'])
            ->orderBy('id')
            ->get();
    }

    /**
     * Resumo financeiro usado na Solicitação, no Parecer Técnico e no Termo
     * Aditivo: valor inicial, valores deste aditivo e percentuais acumulados
     * (deste aditivo + todos os anteriores do mesmo contrato).
     *
     * @return array{
     *     valor_inicial_atualizado: float,
     *     valor_acrescido_neste_aditivo: float,
     *     valor_suprimido_neste_aditivo: float,
     *     valor_impacto_liquido_neste_aditivo: float,
     *     valor_final_contrato: float,
     *     percentual_acumulado_acrescimo: float,
     *     percentual_acumulado_supressao: float,
     * }
     */
    public function resumoFinanceiro(IncidenteContratual $atual, Contrato|ContratoManual $contrato): array
    {
        $valorBase = $this->valorInicialAtualizado($contrato);
        $anteriores = $this->aditivosAnterioresDeValor($atual, $contrato);

        $percentualAcrescimoAtual = (float) ($atual->percentual_acrescimo ?? 0);
        $percentualSupressaoAtual = (float) ($atual->percentual_supressao ?? 0);

        $percentualAcumuladoAcrescimo = $anteriores->sum(fn ($a) => (float) ($a->percentual_acrescimo ?? 0)) + $percentualAcrescimoAtual;
        $percentualAcumuladoSupressao = $anteriores->sum(fn ($a) => (float) ($a->percentual_supressao ?? 0)) + $percentualSupressaoAtual;

        $valorAcrescido = $valorBase * $percentualAcrescimoAtual / 100;
        $valorSuprimido = $valorBase * $percentualSupressaoAtual / 100;

        $valorAcrescidoAcumulado = $valorBase * $percentualAcumuladoAcrescimo / 100;
        $valorSuprimidoAcumulado = $valorBase * $percentualAcumuladoSupressao / 100;

        return [
            'valor_inicial_atualizado' => $valorBase,
            'valor_acrescido_neste_aditivo' => $valorAcrescido,
            'valor_suprimido_neste_aditivo' => $valorSuprimido,
            'valor_impacto_liquido_neste_aditivo' => $valorAcrescido - $valorSuprimido,
            'valor_final_contrato' => $valorBase + $valorAcrescidoAcumulado - $valorSuprimidoAcumulado,
            'percentual_acumulado_acrescimo' => $percentualAcumuladoAcrescimo,
            'percentual_acumulado_supressao' => $percentualAcumuladoSupressao,
        ];
    }

    /**
     * Número ordinal deste aditivo entre todos os aditivos (qualquer tipo)
     * já abertos para o mesmo contrato, contando por ordem de criação (id).
     * "1" = "Xº Termo Aditivo" vira "1º Termo Aditivo", etc.
     *
     * Limitação conhecida: a ordem é a de criação no sistema, não
     * necessariamente a ordem cronológica real de formalização — um aditivo
     * externo cadastrado tardiamente pode divergir.
     */
    public function numeroOrdinal(IncidenteContratual $atual, Contrato|ContratoManual $contrato): int
    {
        return IncidenteContratual::where('contratavel_id', $contrato->id)
            ->where('contratavel_type', get_class($contrato))
            ->where('id', '<=', $atual->id)
            ->count();
    }
}
