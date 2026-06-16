<?php

namespace App\Services\Assinatura;

use App\Assinatura\Application\Support\AssinanteResolver;

/**
 * @deprecated A lógica foi extraída para App\Assinatura\Application\Support\AssinanteResolver
 *             (classe injetável e testável). Este trait permanece apenas como casca de
 *             compatibilidade para os consumidores que ainda o utilizam via `use`.
 *             Prefira injetar AssinanteResolver diretamente em código novo.
 */
trait ResolveLegacyAssinantesTrait
{
    protected function resolverAssinantesParaRodada(array $entradaRequest, string $modo = 'paralelo'): array
    {
        return (new AssinanteResolver())->resolverParaRodada($entradaRequest, $modo);
    }

    protected function extrairListaAssinantesDaRequest(array $requestData): array
    {
        return (new AssinanteResolver())->extrairDaRequest($requestData);
    }
}
