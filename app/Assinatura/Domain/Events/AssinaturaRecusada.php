<?php

namespace App\Assinatura\Domain\Events;

use App\Models\SolicitacaoAssinatura;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Disparado após uma solicitação ser recusada e a rodada cancelada (após commit).
 * O aviso ao solicitante fica em listener.
 */
class AssinaturaRecusada
{
    use Dispatchable;

    public function __construct(
        public readonly SolicitacaoAssinatura $solicitacao,
        public readonly string $motivo
    ) {}
}
