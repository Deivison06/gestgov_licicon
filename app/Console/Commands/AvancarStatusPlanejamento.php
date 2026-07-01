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
        // Avança aguardando_sessao → em_andamento quando a data da sessão pública chegou
        $quantidade = Processo::where('planejamento_status', 'aguardando_sessao')
            ->whereHas('detalhe', fn($q) => $q->whereNotNull('data_hora_fase_edital')
                ->whereDate('data_hora_fase_edital', '<=', Carbon::today()))
            ->update(['planejamento_status' => 'em_andamento']);

        $this->info("$quantidade processo(s) avançado(s) para Em Andamento.");
    }
}
