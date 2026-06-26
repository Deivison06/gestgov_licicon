<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Base para os services de domínio (Processo, Finalização, Contrato, Ata).
 *
 * Concentra utilitários transversais — execução transacional com log e relança
 * a exceção preservando o tratamento de erro já feito nos controllers — para que
 * os services concretos foquem na regra de negócio.
 */
abstract class AbstractService
{
    /**
     * Executa $fn dentro de uma transação de banco. Em caso de erro, o
     * DB::transaction já faz o rollback; aqui apenas registramos o contexto
     * (quando informado) e **relançamos** — mantendo o fluxo de erro original
     * de quem chama (controller/listener).
     *
     * @template T
     * @param  callable():T  $fn
     * @return T
     */
    protected function transacao(callable $fn, string $contextoLog = '')
    {
        try {
            return DB::transaction($fn);
        } catch (Throwable $e) {
            if ($contextoLog !== '') {
                Log::error($contextoLog, ['erro' => $e->getMessage()]);
            }

            throw $e;
        }
    }
}
