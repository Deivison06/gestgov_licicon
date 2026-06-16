<?php

namespace App\Assinatura\Domain\Enums;

/**
 * Estados de uma SolicitacaoAssinatura + transições permitidas.
 *
 * Centraliza a máquina de estados que antes estava implícita em vários
 * `update(['status' => ...])` espalhados (AssinaturaService, SolicitacaoService,
 * command de expiração). Os valores espelham exatamente as colunas string
 * existentes — nenhuma migração necessária.
 */
enum StatusSolicitacao: string
{
    case Pendente  = 'pendente';
    case Assinada  = 'assinada';
    case Recusada  = 'recusada';
    case Cancelada = 'cancelada';
    case Expirada  = 'expirada';

    /**
     * Transições válidas a partir deste estado.
     * - Pendente pode ir para qualquer desfecho.
     * - Assinada ainda pode ser Cancelada (uma recusa cancela a rodada inteira,
     *   inclusive solicitações já assinadas — comportamento de cancelarRodada).
     * - Estados finais de recusa/cancelamento/expiração não transicionam mais.
     */
    public function transicoesPermitidas(): array
    {
        return match ($this) {
            self::Pendente => [self::Assinada, self::Recusada, self::Cancelada, self::Expirada],
            self::Assinada => [self::Cancelada],
            default        => [],
        };
    }

    public function podeTransicionarPara(self $novo): bool
    {
        return in_array($novo, $this->transicoesPermitidas(), true);
    }

    /** Estado terminal (qualquer um que não seja Pendente). */
    public function finalizado(): bool
    {
        return $this !== self::Pendente;
    }
}
