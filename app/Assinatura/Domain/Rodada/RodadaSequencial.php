<?php

namespace App\Assinatura\Domain\Rodada;

use App\Models\DocumentoVersao;
use App\Models\SolicitacaoAssinatura;
use Illuminate\Support\Collection;

/**
 * Rodada sequencial (ordem 1..n): notifica um por vez, na ordem; a ordem N só
 * pode assinar quando 1..N-1 já assinaram.
 */
final class RodadaSequencial implements ModoRodadaStrategy
{
    public function alvosNotificacaoInicial(Collection $solicitacoes): Collection
    {
        return $solicitacoes->sortBy('ordem')->take(1);
    }

    public function proximoAposAssinar(SolicitacaoAssinatura $atual): ?SolicitacaoAssinatura
    {
        return SolicitacaoAssinatura::query()
            ->where('documento_versao_id', $atual->documento_versao_id)
            ->where('ordem', '>', $atual->ordem)
            ->where('status', SolicitacaoAssinatura::STATUS_PENDENTE)
            ->orderBy('ordem')
            ->first();
    }

    public function validarPodeAssinarAgora(SolicitacaoAssinatura $solicitacao, DocumentoVersao $versao): void
    {
        if ($solicitacao->ordem <= 1) {
            return;
        }

        $anterioresPendentes = SolicitacaoAssinatura::query()
            ->where('documento_versao_id', $versao->id)
            ->where('ordem', '<', $solicitacao->ordem)
            ->where('status', '!=', SolicitacaoAssinatura::STATUS_ASSINADA)
            ->exists();

        if ($anterioresPendentes) {
            throw new \DomainException(
                "Esta é uma rodada sequencial — assinantes de ordem menor que {$solicitacao->ordem} ainda não concluíram."
            );
        }
    }
}
