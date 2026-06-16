<?php

namespace App\Assinatura\Domain\Rodada;

use App\Models\DocumentoVersao;
use App\Models\SolicitacaoAssinatura;
use Illuminate\Support\Collection;

/**
 * Política do modo de uma rodada de assinaturas (paralelo x sequencial).
 *
 * Centraliza as três decisões que antes estavam espalhadas entre
 * SolicitacaoService (quem notificar ao criar) e AssinaturaService
 * (quem é o próximo; pode assinar agora?).
 */
interface ModoRodadaStrategy
{
    /**
     * Quais solicitações devem ser notificadas no momento da criação da rodada.
     */
    public function alvosNotificacaoInicial(Collection $solicitacoes): Collection;

    /**
     * Próxima solicitação a ser notificada após `$atual` ser assinada
     * (null quando não há cadeia a avançar — caso paralelo).
     */
    public function proximoAposAssinar(SolicitacaoAssinatura $atual): ?SolicitacaoAssinatura;

    /**
     * Garante que `$solicitacao` pode ser assinada AGORA dado o modo.
     * Lança \DomainException quando a ordem ainda não chegou.
     */
    public function validarPodeAssinarAgora(SolicitacaoAssinatura $solicitacao, DocumentoVersao $versao): void;
}
