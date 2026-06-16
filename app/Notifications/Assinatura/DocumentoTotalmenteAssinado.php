<?php

namespace App\Notifications\Assinatura;

use App\Models\DocumentoVersao;
use Illuminate\Notifications\Notification;

/**
 * Notifica o operador que gerou o documento quando todas as assinaturas foram coletadas.
 * É a deixa para consolidar o PDF (estampagem + QR) — Fase 5.
 */
class DocumentoTotalmenteAssinado extends Notification
{
    public function __construct(public DocumentoVersao $versao) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'tipo'                => 'documento_totalmente_assinado',
            'icone'               => 'fas fa-check-circle',
            'cor'                 => 'emerald',
            'mensagem'            => 'Documento totalmente assinado — pronto para baixar',
            'versao_id'           => $this->versao->id,
            'documento'           => class_basename($this->versao->documentavel_type),
            'versao'              => $this->versao->versao,
            'total_assinaturas'   => $this->versao->assinaturas()->count(),
            'url'                 => null, // até a Fase 5 (consolidação + QR), não há URL final
        ];
    }
}
