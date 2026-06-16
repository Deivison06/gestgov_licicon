<?php

namespace App\Notifications\Assinatura;

use App\Models\SolicitacaoAssinatura;
use Illuminate\Notifications\Notification;

/**
 * Notificação para o assinante quando uma nova solicitação é criada para ele.
 * Canal: database (sininho). E-mail não é usado no MVP (decisão 5).
 */
class NovaSolicitacaoAssinatura extends Notification
{
    public function __construct(public SolicitacaoAssinatura $solicitacao) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $this->solicitacao->loadMissing(['versao', 'solicitadoPor']);

        return [
            'tipo'            => 'nova_solicitacao',
            'icone'           => 'fas fa-pen-fancy',
            'cor'             => 'amber',
            'mensagem'        => 'Novo documento aguardando sua assinatura',
            'solicitacao_id'  => $this->solicitacao->id,
            'documento'       => class_basename($this->solicitacao->versao->documentavel_type ?? ''),
            'versao'          => optional($this->solicitacao->versao)->versao,
            'solicitado_por'  => optional($this->solicitacao->solicitadoPor)->name,
            'expires_at'      => optional($this->solicitacao->expires_at)->toIso8601String(),
            'url'             => route('minhas-assinaturas.show', $this->solicitacao->id),
        ];
    }
}
