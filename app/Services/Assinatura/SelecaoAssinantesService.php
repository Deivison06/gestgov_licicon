<?php

namespace App\Services\Assinatura;

use App\Assinatura\Application\Actions\CancelarRodadaAssinatura;
use App\Assinatura\Application\Actions\SalvarSelecaoAssinantes;
use App\Assinatura\Application\Actions\SolicitarAssinatura;
use App\Assinatura\Application\Queries\StatusDocumentoAssinatura;
use App\Models\Documento;
use App\Models\DocumentoSelecaoAssinantes;
use App\Models\DocumentoVersao;
use App\Models\Processo;
use Illuminate\Support\Collection;

/**
 * Fachada de compatibilidade do fluxo de seleção/solicitação de assinaturas.
 *
 * A lógica foi decomposta (Fase 6) em:
 *   - SalvarSelecaoAssinantes (Action)     → escrita da seleção
 *   - SolicitarAssinatura (Action)         → orquestração da rodada
 *   - StatusDocumentoAssinatura (Query)    → leituras / status / caminho do PDF
 *
 * Este service permanece como ponto único consumido por controllers e testes,
 * apenas delegando. Código novo deve injetar as Actions/Query diretamente.
 */
class SelecaoAssinantesService
{
    public function __construct(
        protected SalvarSelecaoAssinantes $salvarSelecao,
        protected SolicitarAssinatura $solicitarAssinatura,
        protected StatusDocumentoAssinatura $consulta,
        protected CancelarRodadaAssinatura $cancelarRodadaAssinatura
    ) {}

    public function salvar(
        Processo $processo,
        string $tipoDocumento,
        ?int $homologacaoId,
        ?int $vencedorId,
        array $dados,
        int $atualizadoPorUserId
    ): DocumentoSelecaoAssinantes {
        return $this->salvarSelecao->executar(
            $processo, $tipoDocumento, $homologacaoId, $vencedorId, $dados, $atualizadoPorUserId
        );
    }

    public function obter(
        Processo $processo,
        string $tipoDocumento,
        ?int $homologacaoId = null,
        ?int $vencedorId = null
    ): ?DocumentoSelecaoAssinantes {
        return $this->consulta->obterSelecao($processo, $tipoDocumento, $homologacaoId, $vencedorId);
    }

    public function solicitarAssinatura(
        Processo $processo,
        string $tipoDocumento,
        ?int $homologacaoId,
        ?int $vencedorId,
        int $solicitadoPorUserId
    ): array {
        return $this->solicitarAssinatura->executar(
            $processo, $tipoDocumento, $homologacaoId, $vencedorId, $solicitadoPorUserId
        );
    }

    public function rodadaAtiva(Documento $documento): Collection
    {
        return $this->consulta->rodadaAtiva($documento);
    }

    public function existeRodadaAtiva(Documento $documento): bool
    {
        return $this->consulta->existeRodadaAtiva($documento);
    }

    public function versaoConsolidada(Documento $documento): ?DocumentoVersao
    {
        return $this->consulta->versaoConsolidada($documento);
    }

    public function statusDocumento(
        Processo $processo,
        string $tipoDocumento,
        ?int $homologacaoId = null,
        ?int $vencedorId = null
    ): array {
        return $this->consulta->status($processo, $tipoDocumento, $homologacaoId, $vencedorId);
    }

    public function caminhoPdfAssinado(?Documento $documento): ?string
    {
        return $this->consulta->caminhoPdfAssinado($documento);
    }

    public function cancelarRodada(
        Processo $processo,
        string $tipoDocumento,
        ?int $homologacaoId,
        ?int $vencedorId,
        int $userId,
        ?string $motivo = null
    ): int {
        return $this->cancelarRodadaAssinatura->executar(
            $processo, $tipoDocumento, $homologacaoId, $vencedorId, $userId, $motivo
        );
    }
}
