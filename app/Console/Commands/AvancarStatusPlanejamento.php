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
        $quantidade = Processo::where('planejamento_status', 'aguardando_sessao')
            ->whereNotNull('planejamento_data_abertura')
            ->whereDate('planejamento_data_abertura', '<=', Carbon::today())
            ->update(['planejamento_status' => 'em_andamento']);

        $this->info("$quantidade processo(s) avançado(s) para Em Andamento.");
    }
}
