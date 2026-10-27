<?php

namespace App\Helpers;

/**
 * Formata nomes de pessoas para os documentos: "CÂNDIDO DOS SANTOS RODRIGUES"
 * vira "Cândido dos Santos Rodrigues" (preposições/artigos ficam em minúsculo).
 */
class NomeProprio
{
    private static $minusculas = ['da', 'das', 'de', 'do', 'dos', 'e', 'di', 'du'];

    public static function formatar(?string $nome): string
    {
        $nome = trim(preg_replace('/\s+/u', ' ', (string) $nome));

        if ($nome === '') {
            return '';
        }

        $palavras = explode(' ', mb_convert_case($nome, MB_CASE_TITLE, 'UTF-8'));

        foreach ($palavras as $i => $palavra) {
            if ($i > 0 && in_array(mb_strtolower($palavra), self::$minusculas, true)) {
                $palavras[$i] = mb_strtolower($palavra);
            }
        }

        return implode(' ', $palavras);
    }
}
