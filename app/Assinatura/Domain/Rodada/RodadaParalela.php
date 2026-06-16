<?php

namespace App\Assinatura\Domain\Rodada;

use App\Models\DocumentoVersao;
use App\Models\SolicitacaoAssinatura;
use Illuminate\Support\Collection;

/**
 * Rodada paralela (ordem = 0): todos os assinantes podem assinar a qualquer
 * momento e são notificados de uma vez na criação.
 */
final class RodadaParalela implements ModoRodadaStrategy
{
    public function alvosNotificacaoInicial(Collection $solicitacoes): Collection
    {
        return $solicitacoes;
    }

    public function proximoAposAssinar(SolicitacaoAssinatura $atual): ?SolicitacaoAssinatura
    {
        return null;
    }

    public function validarPodeAssinarAgora(SolicitacaoAssinatura $solicitacao, DocumentoVersao $versao): void
    {
        // Sem restrição de ordem no modo paralelo.
    }
}
