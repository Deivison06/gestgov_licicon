<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PendenciaIdentificada extends Notification
{
    use Queueable;

    private $dados;

    public function __construct($dados)
    {
        $this->dados = $dados;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'titulo' => $this->dados['titulo'],
            'mensagem' => $this->dados['mensagem'],
            'tipo' => $this->dados['tipo'],
            'link' => $this->dados['link'],
            'count' => $this->dados['count'] ?? 1
        ];
    }
}
