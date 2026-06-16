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
     *   status: 'autentico'|'nao_encontrado',
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

        // Re-cache com TTL longo se foi sucesso
        if ($resultado['status'] === 'autentico') {
            Cache::put(self::CACHE_PREFIX . $codigo, $resultado, self::CACHE_TTL_SUCESSO);
        }

        // Loga consulta (mesmo se cached — auditoria precisa registrar todas)
        $this->registrarConsulta($codigo, $resultado, $ip, $userAgent);

        return $resultado;
    }

    /**
     * Retorna o caminho absoluto do PDF assinado para download, ou null.
     */
    public function caminhoDownload(string $codigo): ?string
    {
        $resultado = $this->consultar($codigo);

        if ($resultado['status'] !== 'autentico') {
            return null;
        }

        $caminho = optional($resultado['versao'])->caminho_pdf_assinado;
        return ($caminho && file_exists($caminho)) ? $caminho : null;
    }

    // ====================================================================
    // Internos
    // ====================================================================

    private function buscarPorCodigo(string $codigo): array
    {
        $assinatura = AssinaturaDigital::query()
            ->where('codigo_verificador', $codigo)
            ->with(['versao.assinaturas.assinante'])
            ->first();

        if (!$assinatura) {
            return ['status' => 'nao_encontrado'];
        }

        $versao = $assinatura->versao;

        return [
            'status'                  => 'autentico',
            'versao'                  => $versao,
            'assinatura_referenciada' => $assinatura,
            'assinaturas'             => $versao->assinaturas->sortBy('assinado_em')->values(),
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
                'sucesso'             => $resultado['status'] === 'autentico',
                'consultado_em'       => now(),
            ]);
        } catch (\Throwable $e) {
            // Log falha não bloqueia consulta
        }
    }
}
