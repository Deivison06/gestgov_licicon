<?php

namespace App\Notifications\Assinatura;

use App\Models\SolicitacaoAssinatura;
use Illuminate\Notifications\Notification;

/**
 * Notifica o solicitante quando alguém recusa a assinatura.
 * A rodada inteira foi cancelada (decisão 4) — operador precisa gerar nova versão.
 */
class SolicitacaoRecusada extends Notification
{
    public function __construct(
        public SolicitacaoAssinatura $solicitacao,
        public string $motivo
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $this->solicitacao->loadMissing(['versao', 'assinante']);

        return [
            'tipo'           => 'solicitacao_recusada',
            'icone'          => 'fas fa-ban',
            'cor'            => 'red',
            'mensagem'       => 'Documento recusado — rodada cancelada',
            'solicitacao_id' => $this->solicitacao->id,
            'documento'      => class_basename($this->solicitacao->versao->documentavel_type ?? ''),
            'versao'         => optional($this->solicitacao->versao)->versao,
            'recusada_por'   => optional($this->solicitacao->assinante)->name,
            'motivo'         => $this->motivo,
            'url'            => null, // operador edita o documento original, não a solicitação
        ];
    }
}
