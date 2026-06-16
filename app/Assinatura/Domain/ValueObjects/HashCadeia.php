<?php

namespace App\Assinatura\Domain\ValueObjects;

/**
 * Fórmula ÚNICA da cadeia de hash das assinaturas (Lei 14.063/2020).
 *
 * Cada assinatura encadeia na anterior:
 *   hash_proprio = sha256(hash_documento + hash_cadeia_anterior + assinante_id + timestamp)
 *
 * Esta era a regra de domínio mais crítica do módulo e estava duplicada entre
 * AssinaturaService (geração) e AssinaturaDigital::cadeiaIntegra (verificação).
 * Centralizar aqui elimina o risco de as duas implementações divergirem.
 *
 * IMPORTANTE: alterar esta fórmula invalida a verificação de TODAS as assinaturas
 * já registradas. Só mude com um plano de re-verificação/migração.
 */
final class HashCadeia
{
    /**
     * @param string      $hashDocumento      Hash SHA-256 do PDF no momento da assinatura.
     * @param string|null $hashCadeiaAnterior  Hash próprio da assinatura anterior (null na primeira).
     * @param int         $assinanteId         ID do usuário que assinou.
     * @param string      $timestamp           Timestamp determinístico já formatado (Y-m-d H:i:s.u).
     */
    public static function calcular(
        string $hashDocumento,
        ?string $hashCadeiaAnterior,
        int $assinanteId,
        string $timestamp
    ): string {
        return hash(
            'sha256',
            $hashDocumento . ($hashCadeiaAnterior ?? '') . $assinanteId . $timestamp
        );
    }
}
