<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('app:verificar-pendencias')
                 ->everyFiveMinutes();

        // Expira solicitações pendentes vencidas e envia lembretes 24h antes
        $schedule->command('assinaturas:expirar-pendentes')
                 ->dailyAt('06:00')
                 ->withoutOverlapping();
    }
}
