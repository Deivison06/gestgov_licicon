<?php

namespace App\Assinatura\Application\Listeners;

use App\Assinatura\Domain\Events\AssinaturaRegistrada;
use App\Assinatura\Domain\Rodada\RodadaStrategyFactory;
use App\Models\User;
use App\Notifications\Assinatura\NovaSolicitacaoAssinatura;
use Illuminate\Support\Facades\Log;

/**
 * Modo sequencial: ao registrar uma assinatura, notifica o próximo da fila.
 * No modo paralelo a estratégia devolve null e nada acontece.
 * Best-effort: falha de notificação não afeta a assinatura já registrada.
 */
class NotificarProximoAssinante
{
    public function __construct(
        private readonly RodadaStrategyFactory $strategyFactory
    ) {}

    public function handle(AssinaturaRegistrada $event): void
    {
        $proximo = $this->strategyFactory
            ->paraSolicitacao($event->solicitacao)
            ->proximoAposAssinar($event->solicitacao);

        if (!$proximo) {
            return;
        }

        try {
            $user = User::find($proximo->assinante_user_id);
            if ($user) {
                $user->notify(new NovaSolicitacaoAssinatura($proximo));
            }
        } catch (\Throwable $e) {
            Log::warning('Falha ao notificar próximo assinante', [
                'solicitacao_id' => $proximo->id,
                'erro'           => $e->getMessage(),
            ]);
        }
    }
}
