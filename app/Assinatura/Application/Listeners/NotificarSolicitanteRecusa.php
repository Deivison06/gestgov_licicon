<?php

namespace App\Assinatura\Application\Listeners;

use App\Assinatura\Domain\Events\AssinaturaRecusada;
use App\Models\User;
use App\Notifications\Assinatura\SolicitacaoRecusada;
use Illuminate\Support\Facades\Log;

/**
 * Avisa quem solicitou a assinatura de que ela foi recusada (e a rodada cancelada).
 * Best-effort.
 */
class NotificarSolicitanteRecusa
{
    public function handle(AssinaturaRecusada $event): void
    {
        $solicitanteId = $event->solicitacao->solicitado_por_user_id;
        if (!$solicitanteId) {
            return;
        }

        try {
            $solicitante = User::find($solicitanteId);
            if ($solicitante) {
                $solicitante->notify(new SolicitacaoRecusada($event->solicitacao, $event->motivo));
            }
        } catch (\Throwable $e) {
            Log::warning('Falha ao notificar solicitante da recusa', [
                'solicitacao_id' => $event->solicitacao->id,
                'erro'           => $e->getMessage(),
            ]);
        }
    }
}
