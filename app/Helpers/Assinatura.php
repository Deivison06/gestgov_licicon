<?php

namespace App\Helpers;

/**
 * Leitura dos assinantes gravados em documento_selecao_assinantes.assinantes.
 */
class Assinatura
{
    /**
     * Texto da linha embaixo do nome de quem assina. Usa o `cargo` gravado na
     * seleção; seleções antigas (sem cargo) continuam mostrando `unidade_nome`,
     * exatamente como antes.
     */
    public static function cargo(mixed $assinante, string $padrao = ''): string
    {
        if (is_object($assinante)) {
            $assinante = (array) $assinante;
        }
        if (! is_array($assinante) || $assinante === []) {
            return $padrao;
        }

        $cargo = trim((string) ($assinante['cargo'] ?? ''));

        return $cargo !== '' ? $cargo : (string) ($assinante['unidade_nome'] ?? $padrao);
    }
}
