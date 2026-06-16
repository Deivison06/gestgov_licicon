<?php

namespace App\Assinatura\Domain\Rodada;

use App\Models\SolicitacaoAssinatura;
use Illuminate\Support\Collection;

/**
 * Resolve a estratégia de modo a partir do estado das solicitações.
 *
 * Convenção (preservada do código original):
 *   - ordem = 0  → rodada paralela
 *   - ordem > 0  → rodada sequencial
 */
class RodadaStrategyFactory
{
    public function paraSolicitacao(SolicitacaoAssinatura $solicitacao): ModoRodadaStrategy
    {
        return ($solicitacao->ordem ?? 0) > 0
            ? new RodadaSequencial()
            : new RodadaParalela();
    }

    public function paraColecao(Collection $solicitacoes): ModoRodadaStrategy
    {
        $ehSequencial = $solicitacoes->contains(fn ($s) => ($s->ordem ?? 0) > 0);

        return $ehSequencial
            ? new RodadaSequencial()
            : new RodadaParalela();
    }
}
