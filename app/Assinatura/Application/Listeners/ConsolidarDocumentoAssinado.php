<?php

namespace App\Assinatura\Application\Listeners;

use App\Assinatura\Domain\Events\RodadaConcluida;
use App\Models\User;
use App\Notifications\Assinatura\DocumentoTotalmenteAssinado;
use App\Services\Assinatura\AssinaturaConsolidacaoService;
use Illuminate\Support\Facades\Log;

/**
 * Fim da rodada: consolida o PDF (estampa assinaturas + QR) e notifica o operador
 * que gerou o documento. Best-effort — falhas são logadas e não revertem nada
 * (a consolidação pode ser regerada por comando administrativo).
 *
 * Substitui o app(AssinaturaConsolidacaoService::class) (service locator) por
 * injeção via construtor.
 */
class ConsolidarDocumentoAssinado
{
    public function __construct(
        private readonly AssinaturaConsolidacaoService $consolidacao
    ) {}

    public function handle(RodadaConcluida $event): void
    {
        $versao = $event->versao;

        try {
            $this->consolidacao->consolidar($versao->refresh());
        } catch (\Throwable $e) {
            Log::warning('Falha ao consolidar PDF após última assinatura', [
                'versao_id' => $versao->id,
                'erro'      => $e->getMessage(),
            ]);
        }

        if (!$versao->gerado_por_user_id) {
            return;
        }

        try {
            $operador = User::find($versao->gerado_por_user_id);
            if ($operador) {
                $operador->notify(new DocumentoTotalmenteAssinado($versao->refresh()));
            }
        } catch (\Throwable $e) {
            Log::warning('Falha ao notificar operador do documento assinado', [
                'versao_id' => $versao->id,
                'erro'      => $e->getMessage(),
            ]);
        }
    }
}
