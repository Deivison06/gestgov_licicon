<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Avança processos de aguardando_sessao → em_andamento quando a data da sessão chegou.
// Roda a cada hora para que o status seja atualizado no mesmo dia da sessão.
Schedule::command('planejamento:avancar-status')->hourly()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/planejamento-avancar.log'));

// Sincronização incremental do PNCP — desativada temporariamente.
// Para reativar, descomentar o bloco abaixo.
// Schedule::command('pncp:sincronizar --incremental')->dailyAt('03:00')
//     ->withoutOverlapping()
//     ->runInBackground()
//     ->appendOutputTo(storage_path('logs/pncp-sync.log'));
