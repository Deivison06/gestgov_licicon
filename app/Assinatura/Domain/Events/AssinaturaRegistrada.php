<?php

namespace App\Assinatura\Domain\Events;

use App\Models\AssinaturaDigital;
use App\Models\DocumentoVersao;
use App\Models\SolicitacaoAssinatura;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Disparado após uma assinatura ser registrada com sucesso (já após o commit).
 * Efeitos colaterais (ex.: notificar o próximo assinante) ficam em listeners.
 */
class AssinaturaRegistrada
{
    use Dispatchable;

    public function __construct(
        public readonly AssinaturaDigital $assinatura,
        public readonly SolicitacaoAssinatura $solicitacao,
        public readonly DocumentoVersao $versao
    ) {}
}
