<?php

namespace App\Notifications\Assinatura;

use App\Models\SolicitacaoAssinatura;
use Illuminate\Notifications\Notification;

/**
 * Notifica o assinante que sua solicitação expira em menos de 24h.
 * Disparada pelo command `assinaturas:lembrete-expirando` (Schedule diário).
 */
class SolicitacaoExpirando extends Notification
{
    public function __construct(public SolicitacaoAssinatura $solicitacao) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $this->solicitacao->loadMissing('versao');

        $horasRestantes = (int) max(0, now()->diffInHours($this->solicitacao->expires_at, false));

        return [
            'tipo'              => 'solicitacao_expirando',
            'icone'             => 'fas fa-hourglass-half',
            'cor'               => 'red',
            'mensagem'          => "Solicitação expira em {$horasRestantes}h",
            'solicitacao_id'    => $this->solicitacao->id,
            'documento'         => class_basename($this->solicitacao->versao->documentavel_type ?? ''),
            'horas_restantes'   => $horasRestantes,
            'expires_at'        => $this->solicitacao->expires_at->toIso8601String(),
            'url'               => route('minhas-assinaturas.show', $this->solicitacao->id),
        ];
    }
}
