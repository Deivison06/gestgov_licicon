<?php

namespace App\Assinatura\Domain\Events;

use App\Models\DocumentoVersao;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Disparado quando a última assinatura pendente de uma versão é registrada
 * (já após o commit). Aciona a consolidação visual do PDF e a divulgação.
 */
class RodadaConcluida
{
    use Dispatchable;

    public function __construct(
        public readonly DocumentoVersao $versao
    ) {}
}
