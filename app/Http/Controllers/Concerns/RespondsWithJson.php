<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * Padroniza as respostas JSON dos controllers e o tratamento de erro.
 *
 * Mantém EXATAMENTE o mesmo shape já usado no projeto:
 *   sucesso → {"success": true, ...payload}
 *   falha   → {"success": false, "message": "...", ...extra}
 *
 * Objetivo: eliminar a repetição de `response()->json([...])` (201 ocorrências)
 * e do par `try/catch + Log::error + json 500` (123/84 ocorrências), sem alterar
 * o corpo nem o status code das respostas.
 */
trait RespondsWithJson
{
    /**
     * Resposta de sucesso. O payload é mesclado a `success: true` preservando
     * as chaves existentes (ex.: 'data', 'message', 'vencedores', 'token'...).
     */
    protected function jsonOk(array $payload = [], int $status = 200): JsonResponse
    {
        return response()->json(['success' => true] + $payload, $status);
    }

    /**
     * Resposta de falha. Mantém `{success:false, message}` + chaves extras opcionais.
     */
    protected function jsonFail(string $message, int $status = 500, array $extra = []): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message] + $extra, $status);
    }

    /**
     * Executa $fn (que deve retornar uma JsonResponse, normalmente via jsonOk).
     * Em caso de exceção, registra o log com o $contextoLog e devolve um jsonFail
     * 500 com a mensagem "{$mensagemErro}: {motivo}" — mesmo formato já praticado.
     *
     * Use apenas onde o fluxo de erro original era "logar + responder 500".
     * Onde há tratamento específico (ex.: \DomainException → 422), mantenha o
     * try/catch explícito usando jsonOk/jsonFail.
     */
    protected function tryJson(
        callable $fn,
        string $contextoLog,
        string $mensagemErro,
        array $logExtra = []
    ): JsonResponse {
        try {
            return $fn();
        } catch (\Throwable $e) {
            Log::error($contextoLog, array_merge(['erro' => $e->getMessage()], $logExtra));

            return $this->jsonFail($mensagemErro . ': ' . $e->getMessage());
        }
    }
}
