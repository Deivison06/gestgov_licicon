<?php

namespace App\Services\Assinatura;

use App\Models\AssinaturaDigital;
use App\Models\ConsultaPublica;
use App\Models\DocumentoVersao;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Consulta de autenticidade pública via código verificador.
 *
 * - Registra cada consulta (sucesso ou falha) em consultas_publicas
 * - Cache de leitura: documentos consolidados são imutáveis, podem ficar
 *   em cache por muito tempo. Invalidação só se a lógica do payload mudar.
 */
class ValidacaoPublicaService
{
    private const CACHE_PREFIX = 'validacao_publica:';
    private const CACHE_TTL_SUCESSO = 86400; // 1 dia
    private const CACHE_TTL_FALHA   = 60;    // 1 minuto (evita stress em código inválido)

    /**
     * @return array{
     *   status: 'autentico'|'autentico_nao_assinado'|'nao_encontrado',
     *   versao?: DocumentoVersao,
     *   assinatura_referenciada?: AssinaturaDigital,
     *   assinaturas?: Collection,
     *   documento_tipo?: string,
     *   gerado_em?: string,
     *   hash?: string,
     *   download_url?: ?string,
     * }
     */
    public function consultar(string $codigo, ?string $ip = null, ?string $userAgent = null): array
    {
        $codigo = strtoupper(trim($codigo));

        if ($codigo === '') {
            return ['status' => 'nao_encontrado'];
        }

        $resultado = Cache::remember(
            self::CACHE_PREFIX . $codigo,
            self::CACHE_TTL_FALHA,
            fn () => $this->buscarPorCodigo($codigo)
        );

        // Re-cache com TTL longo se foi sucesso. "autentico_nao_assinado" fica com TTL
        // curto (como falha): o status pode mudar a qualquer momento (assinatura chegando).
        if ($resultado['status'] === 'autentico') {
            Cache::put(self::CACHE_PREFIX . $codigo, $resultado, self::CACHE_TTL_SUCESSO);
        }

        // Loga consulta (mesmo se cached — auditoria precisa registrar todas)
        $this->registrarConsulta($codigo, $resultado, $ip, $userAgent);

        return $resultado;
    }

    /**
     * Retorna o caminho absoluto do PDF para download: o assinado, se já houver
     * assinatura(s) consolidada(s); senão o rascunho (com o selo de autenticação).
     */
    public function caminhoDownload(string $codigo): ?string
    {
        $resultado = $this->consultar($codigo);
        $versao = $resultado['versao'] ?? null;

        if (!$versao) {
            return null;
        }

        $caminho = $resultado['status'] === 'autentico'
            ? $versao->caminho_pdf_assinado
            : $versao->caminho_pdf;

        return ($caminho && file_exists($caminho)) ? $caminho : null;
    }

    // ====================================================================
    // Internos
    // ====================================================================

    private function buscarPorCodigo(string $codigo): array
    {
        // Busca primeiro pelo código da versão — selo de autenticação, carimbado já na
        // geração do documento (existe para toda versão gerada a partir desta feature).
        $versao = DocumentoVersao::query()
            ->where('codigo_verificador', $codigo)
            ->with(['assinaturas.assinante'])
            ->first();

        if ($versao) {
            return $this->resultadoParaVersao($versao);
        }

        // Compatibilidade permanente: códigos impressos em PDFs assinados antes desta
        // mudança são o código da própria assinatura, não da versão.
        $assinatura = AssinaturaDigital::query()
            ->where('codigo_verificador', $codigo)
            ->with(['versao.assinaturas.assinante'])
            ->first();

        if (!$assinatura) {
            return ['status' => 'nao_encontrado'];
        }

        return $this->resultadoParaVersao($assinatura->versao, $assinatura);
    }

    private function resultadoParaVersao(DocumentoVersao $versao, ?AssinaturaDigital $assinaturaReferenciada = null): array
    {
        $assinaturas = $versao->assinaturas->sortBy('assinado_em')->values();

        if ($assinaturas->isEmpty()) {
            return [
                'status'               => 'autentico_nao_assinado',
                'versao'               => $versao,
                'assinaturas'          => $assinaturas,
                'documento_tipo'       => class_basename($versao->documentavel_type),
                'versao_numero'        => $versao->versao,
                'gerado_em'            => $versao->gerado_em?->format('d/m/Y H:i'),
                'hash'                 => $versao->hash_sha256,
                'download_disponivel'  => $versao->caminho_pdf && file_exists($versao->caminho_pdf),
            ];
        }

        return [
            'status'                  => 'autentico',
            'versao'                  => $versao,
            'assinatura_referenciada' => $assinaturaReferenciada,
            'assinaturas'             => $assinaturas,
            'documento_tipo'          => class_basename($versao->documentavel_type),
            'versao_numero'           => $versao->versao,
            'gerado_em'               => $versao->gerado_em?->format('d/m/Y H:i'),
            'hash'                    => $versao->hash_pdf_assinado ?? $versao->hash_sha256,
            'download_disponivel'     => $versao->caminho_pdf_assinado
                && file_exists($versao->caminho_pdf_assinado),
        ];
    }

    private function registrarConsulta(string $codigo, array $resultado, ?string $ip, ?string $userAgent): void
    {
        try {
            ConsultaPublica::create([
                'codigo_verificador'  => $codigo,
                'documento_versao_id' => optional($resultado['versao'] ?? null)->id,
                'ip'                  => $ip ?? '0.0.0.0',
                'user_agent'          => substr((string) ($userAgent ?? ''), 0, 500),
                'sucesso'             => in_array($resultado['status'], ['autentico', 'autentico_nao_assinado'], true),
                'consultado_em'       => now(),
            ]);
        } catch (\Throwable $e) {
            // Log falha não bloqueia consulta
        }
    }
}
