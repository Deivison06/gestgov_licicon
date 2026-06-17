<?php

namespace App\Assinatura\Application\Actions;

use App\Assinatura\Application\Queries\StatusDocumentoAssinatura;
use App\Models\Processo;
use App\Services\Assinatura\SolicitacaoService;

/**
 * Cancela a rodada de assinatura ATIVA de um documento: todas as solicitações
 * pendentes/assinadas da versão ativa voltam para `cancelada` (via SolicitacaoService).
 * Após cancelar, o documento volta a poder solicitar uma nova rodada.
 */
class CancelarRodadaAssinatura
{
    public function __construct(
        private readonly StatusDocumentoAssinatura $consulta,
        private readonly SolicitacaoService $solicitacaoService
    ) {}

    public function executar(
        Processo $processo,
        string $tipoDocumento,
        ?int $homologacaoId,
        ?int $vencedorId,
        int $userId,
        ?string $motivo = null
    ): int {
        $documento = $this->consulta->localizarDocumentoGerado($processo, $tipoDocumento, $homologacaoId);

        if (!$documento) {
            throw new \DomainException('Documento não encontrado para cancelar a rodada.');
        }

        $versao = $this->consulta->versaoAtiva($documento);

        if (!$versao) {
            throw new \DomainException('Não há rodada de assinatura ativa para cancelar.');
        }

        return $this->solicitacaoService->cancelarRodada(
            $versao,
            $userId,
            $motivo ?: 'Rodada cancelada pelo operador.'
        );
    }
}
