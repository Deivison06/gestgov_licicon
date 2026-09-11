<?php

namespace App\Services\Assinatura;

use App\Models\AssinaturaDigital;
use App\Models\DocumentoVersao;
use Illuminate\Support\Str;

/**
 * Geração do código verificador público (20 chars: 10 numéricos + 10 alfanuméricos).
 *
 * Compartilhado entre AssinaturaDigital (código por assinatura) e DocumentoVersao
 * (código único do documento, do nascimento à assinatura) — ambos vivem no mesmo
 * espaço de códigos consultável em /autenticar/{codigo}, então a checagem de
 * unicidade precisa cobrir as duas tabelas.
 */
class CodigoVerificadorService
{
    /**
     * Tenta até 5x em caso de colisão (extremamente improvável).
     */
    public function gerarUnico(): string
    {
        for ($i = 0; $i < 5; $i++) {
            $numerico = str_pad((string) random_int(1, 9_999_999_999), 10, '0', STR_PAD_LEFT);
            $alfa     = substr(strtoupper(Str::random(20)), 0, 10);
            $codigo   = $numerico . $alfa;

            $existe = AssinaturaDigital::where('codigo_verificador', $codigo)->exists()
                || DocumentoVersao::where('codigo_verificador', $codigo)->exists();

            if (!$existe) {
                return $codigo;
            }
        }
        throw new \RuntimeException('Não foi possível gerar um código verificador único após 5 tentativas.');
    }
}
