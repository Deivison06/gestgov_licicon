<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Avança processos de aguardando_sessao → em_andamento na virada do dia da sessão.
Schedule::command('planejamento:avancar-status')->dailyAt('00:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/planejamento-avancar.log'));

// Sincronização incremental do PNCP: executa todo dia às 3h da manhã.
// Para a sincronização completa (3 meses), execute manualmente:
//   php artisan pncp:sincronizar --meses=3
// Para UF específica: php artisan pncp:sincronizar --meses=3 --uf=SP
Schedule::command('pncp:sincronizar --incremental')->dailyAt('03:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/pncp-sync.log'));
