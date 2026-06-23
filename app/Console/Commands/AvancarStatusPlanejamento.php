<?php

namespace App\Console\Commands;

use App\Models\Processo;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AvancarStatusPlanejamento extends Command
{
    protected $signature = 'planejamento:avancar-status';
    protected $description = 'Avança processos de aguardando_sessao para em_andamento quando a data de abertura chegou';

    public function handle(): void
    {
        // Backfill: processos com data_hora no detalhe mas sem planejamento_data_abertura
        $semData = Processo::whereNull('planejamento_data_abertura')
            ->whereHas('detalhe', fn($q) => $q->whereNotNull('data_hora'))
            ->with('detalhe')
            ->get();

        $backfilled = 0;
        foreach ($semData as $processo) {
            $updates = ['planejamento_data_abertura' => $processo->detalhe->data_hora];
            if ($processo->planejamento_status === 'em_elaboracao') {
                $updates['planejamento_status'] = 'aguardando_sessao';
            }
            $processo->update($updates);
            $backfilled++;
        }

        if ($backfilled > 0) {
            $this->info("$backfilled processo(s) com data_hora sincronizados.");
        }

        // Avança aguardando_sessao → em_andamento quando a data de abertura chegou
        $quantidade = Processo::where('planejamento_status', 'aguardando_sessao')
            ->whereNotNull('planejamento_data_abertura')
            ->whereDate('planejamento_data_abertura', '<=', Carbon::today())
            ->update(['planejamento_status' => 'em_andamento']);

        $this->info("$quantidade processo(s) avançado(s) para Em Andamento.");
    }
}
