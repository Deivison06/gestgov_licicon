<?php

namespace App\Services\Assinatura;

use App\Assinatura\Infrastructure\Pdf\PaginaAssinaturasRenderer;
use App\Models\AssinaturaLog;
use App\Models\DocumentoVersao;
use Illuminate\Support\Facades\Log;

/**
 * Orquestra a consolidação visual do PDF assinado: valida pré-condições,
 * delega o desenho para PaginaAssinaturasRenderer, calcula o hash final e
 * persiste em DocumentoVersao.
 *
 * Disparada quando a última assinatura é registrada (evento RodadaConcluida →
 * listener ConsolidarDocumentoAssinado). Idempotente: se já consolidada, retorna
 * o caminho existente sem refazer.
 *
 * A renderização FPDI/TCPDF foi extraída para PaginaAssinaturasRenderer (Fase 7).
 */
class AssinaturaConsolidacaoService
{
    public function __construct(
        private readonly PaginaAssinaturasRenderer $renderer
    ) {}

    /**
     * @return string Caminho absoluto do PDF assinado
     */
    public function consolidar(DocumentoVersao $versao): string
    {
        // Idempotente: já consolidada?
        if ($versao->caminho_pdf_assinado && file_exists($versao->caminho_pdf_assinado)) {
            return $versao->caminho_pdf_assinado;
        }

        $versao->loadMissing(['assinaturas.assinante']);

        if ($versao->assinaturas->isEmpty()) {
            throw new \DomainException('Versão não tem assinaturas — nada para consolidar.');
        }

        if (!$versao->caminho_pdf || !file_exists($versao->caminho_pdf)) {
            throw new \RuntimeException("PDF rascunho não encontrado: {$versao->caminho_pdf}");
        }

        $caminhoSaida = $this->definirCaminhoSaida($versao->caminho_pdf);

        try {
            $this->renderer->gerar($versao, $caminhoSaida);
        } catch (\Throwable $e) {
            Log::error('Falha ao consolidar PDF de assinaturas', [
                'versao_id' => $versao->id,
                'erro'      => $e->getMessage(),
            ]);
            throw $e;
        }

        $hashFinal = hash_file('sha256', $caminhoSaida);

        $versao->update([
            'caminho_pdf_assinado'        => $caminhoSaida,
            'hash_pdf_assinado'           => $hashFinal,
            'assinaturas_consolidadas_em' => $versao->assinaturas_consolidadas_em ?? now(),
        ]);

        AssinaturaLog::create([
            'acao'                => AssinaturaLog::ACAO_REGERADA, // reusing — "PDF final estampado"
            'documento_versao_id' => $versao->id,
            'metadados'           => [
                'tipo'              => 'consolidacao',
                'total_assinaturas' => $versao->assinaturas->count(),
                'hash'              => substr($hashFinal, 0, 16),
            ],
        ]);

        return $caminhoSaida;
    }

    private function definirCaminhoSaida(string $caminhoOriginal): string
    {
        $dir   = dirname($caminhoOriginal);
        $nome  = pathinfo($caminhoOriginal, PATHINFO_FILENAME);
        return $dir . DIRECTORY_SEPARATOR . $nome . '_assinado.pdf';
    }
}
