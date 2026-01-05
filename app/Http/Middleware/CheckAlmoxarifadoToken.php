<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAlmoxarifadoToken
{
    public function handle(Request $request, Closure $next): Response
    {
        // pega o token do Header da requisição
        $tokenRecebido = $request->header('X-INTEGRATION-TOKEN');

        // pega o token real configurado no .env
        $tokenValido = env('ALMOXARIFADO_INTEGRATION_TOKEN');

        // verifica se o token não está vazio no .env
        if (!$tokenValido || $tokenRecebido !== $tokenValido) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acesso negado. Token de integração inválido ou ausente.'
            ], 403);
        }

        return $next($request);
    }
}
