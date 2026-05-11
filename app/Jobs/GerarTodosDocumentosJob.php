<?php

namespace App\Jobs;

use App\Models\Processo;
use App\Services\FinalizacaoPdfService;
use App\Services\ProcessoPdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GerarTodosDocumentosJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Número máximo de tentativas (sem retry — falha limpa)
     */
    public int $tries = 1;

    /**
     * Timeout em segundos (40 minutos para processos muito grandes)
     */
    public int $timeout = 2400;

    public function __construct(
        public readonly int    $processoId,
        public readonly string $fase,  // 'iniciar' ou 'finalizar'
        public readonly string $token
    ) {}

    public function handle(ProcessoPdfService $pdfIniciar, FinalizacaoPdfService $pdfFinalizar): void
    {
        Log::info('GerarTodosDocumentosJob iniciado', [
            'processo_id' => $this->processoId,
            'fase'        => $this->fase,
            'token'       => $this->token,
        ]);

        // Registra status "processando" no cache (TTL: 2 horas)
        Cache::put("doc_status_{$this->token}", ['status' => 'processando'], now()->addHours(2));

        try {
            $processo = Processo::findOrFail($this->processoId);

            // Gera o arquivo mesclado e carimbado de acordo com a fase
            $caminhoArquivoOrigem = $this->fase === 'finalizar'
                ? $pdfFinalizar->gerarCaminhoTodosDocumentos($processo)
                : $pdfIniciar->gerarCaminhoTodosDocumentos($processo);

            if (!file_exists($caminhoArquivoOrigem)) {
                throw new \Exception("Falha: O arquivo PDF não foi gerado ou salvo no disco.");
            }

            // Precisamos mover o arquivo para uma pasta persistente ou dar um nome final,
            // pois os services costumam gerar arquivos e deletar após o download.
            // Para garantirmos persistência, moveremos para uma subpasta específica de downloads em lote.
            $ext = pathinfo($caminhoArquivoOrigem, PATHINFO_EXTENSION) ?: 'pdf';
            $nomeFinal = "processo_{$processo->id}_{$this->fase}_todos_docs_" . now()->format('YmdHis') . ".{$ext}";
            $caminhoPersistente = storage_path("app/public/downloads_assincronos");

            if (!file_exists($caminhoPersistente)) {
                mkdir($caminhoPersistente, 0777, true);
            }

            $caminhoFinal = "{$caminhoPersistente}/{$nomeFinal}";
            
            // Move ou copia o arquivo (copy é mais seguro caso o service delete internamente)
            copy($caminhoArquivoOrigem, $caminhoFinal);
            
            // Tenta remover a origem, se o arquivo era temporário.
            if (str_contains($caminhoArquivoOrigem, sys_get_temp_dir()) || str_contains($caminhoArquivoOrigem, 'uploads/')) {
                 @unlink($caminhoArquivoOrigem);
            }

            // Sinalizamos sucesso — o arquivo foi gerado e agora está disponível no caminhoFinal.
            Cache::put("doc_status_{$this->token}", [
                'status'  => 'pronto',
                'fase'    => $this->fase,
                'processo_id' => $this->processoId,
                'caminho' => $caminhoFinal, // Armazenamos o caminho absoluto!
                'nome'    => $nomeFinal,
            ], now()->addHours(2));

            Log::info('GerarTodosDocumentosJob concluído com sucesso', [
                'processo_id' => $this->processoId,
                'token'       => $this->token,
            ]);
        } catch (\Throwable $e) {
            Log::error('GerarTodosDocumentosJob falhou', [
                'processo_id' => $this->processoId,
                'fase'        => $this->fase,
                'token'       => $this->token,
                'erro'        => $e->getMessage(),
                'trace'       => $e->getTraceAsString(),
            ]);

            Cache::put("doc_status_{$this->token}", [
                'status'  => 'erro',
                'message' => 'Erro ao gerar documentos: ' . $e->getMessage(),
            ], now()->addHour());

            // Relança para marcar o job como falho no dashboard de filas
            throw $e;
        }
    }
}
