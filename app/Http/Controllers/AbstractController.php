<?php

namespace App\Http\Controllers;

use App\Jobs\GerarTodosDocumentosJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Base para os controllers de domínio (Processo, Finalização, Contrato, Ata).
 *
 * Herda os helpers de resposta JSON (jsonOk/jsonFail/tryJson) do Controller e
 * concentra utilitários compartilhados desses fluxos. Hoje: o disparo de
 * geração/download de documentos em lote, que era duplicado quase idêntico em
 * ProcessoController (fase "iniciar") e FinalizacaoProcessoController (fase
 * "finalizar"). Será populado com mais utilitários conforme cada domínio for
 * refatorado.
 */
abstract class AbstractController extends Controller
{
    /**
     * Enfileira a geração de todos os documentos do processo e devolve o token
     * de acompanhamento. Mantém o mesmo contrato JSON já usado:
     * {success:true, token, message}.
     */
    protected function dispararDownloadEmLote(int $processoId, string $fase): JsonResponse
    {
        return $this->tryJson(function () use ($processoId, $fase) {
            $token = Str::uuid()->toString();

            Cache::put("doc_status_{$token}", ['status' => 'na_fila', 'fase' => $fase], now()->addHours(2));

            GerarTodosDocumentosJob::dispatch($processoId, $fase, $token);

            return $this->jsonOk([
                'token'   => $token,
                'message' => 'Processamento iniciado em segundo plano.',
            ]);
        }, "Erro ao colocar na fila todos os documentos ({$fase})", 'Erro ao iniciar download', ['processo_id' => $processoId]);
    }
}
