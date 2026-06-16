<?php

namespace App\Providers;

use App\Assinatura\Application\Listeners\ConsolidarDocumentoAssinado;
use App\Assinatura\Application\Listeners\NotificarProximoAssinante;
use App\Assinatura\Application\Listeners\NotificarSolicitanteRecusa;
use App\Assinatura\Domain\Events\AssinaturaRecusada;
use App\Assinatura\Domain\Events\AssinaturaRegistrada;
use App\Assinatura\Domain\Events\RodadaConcluida;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Wiring do módulo de assinatura: liga os eventos de domínio aos seus listeners.
 * Mantém os efeitos colaterais (notificações, consolidação) fora do núcleo do
 * AssinaturaService.
 */
class AssinaturaServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(AssinaturaRegistrada::class, NotificarProximoAssinante::class);
        Event::listen(RodadaConcluida::class, ConsolidarDocumentoAssinado::class);
        Event::listen(AssinaturaRecusada::class, NotificarSolicitanteRecusa::class);
    }
}
