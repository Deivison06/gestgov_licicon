<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Etp;
use App\Models\Solicitacao;

class VerificarPendencias extends Command
{
    /**
     * O nome e a assinatura do comando.
     *
     * @var string
     */
    protected $signature = 'app:verificar-pendencias';

    /**
     * A descrição do comando.
     *
     * @var string
     */
    protected $description = 'Verifica ETPs e Solicitações pendentes para análise dos diretores';

    /**
     * Executa o comando.
     */
    public function handle()
    {
        $this->info('--- Verificando Pendências do Sistema ---');

        $diretores = \App\Models\User::role(['diretor_licicon', 'gerente_licicon'])->get();

        // 1. Verificar ETPs
        $etpsPendentes = Etp::where('status', 'em_analise')->with('prefeitura')->get();
        $countEtps = $etpsPendentes->count();

        if ($countEtps > 0) {
            $this->warn("\n[!] Existem {$countEtps} ETP(s) aguardando análise:");
            
            // Notificar no Banco
            foreach ($diretores as $diretor) {
                // Remove notificações antigas do mesmo tipo para não duplicar no front
                $diretor->notifications()->where('data->tipo', 'etp')->delete();
                
                $diretor->notify(new \App\Notifications\PendenciaIdentificada([
                    'titulo' => 'ETPs Pendentes',
                    'mensagem' => "Existem {$countEtps} ETP(s) aguardando sua análise.",
                    'tipo' => 'etp',
                    'link' => route('admin.etps_recebidos.index'),
                    'count' => $countEtps
                ]));
            }

            $etpData = $etpsPendentes->map(fn($e) => [
                'ID' => $e->id,
                'Prefeitura' => $e->prefeitura->nome ?? 'N/A',
                'Objeto' => substr($e->objeto_licitacao, 0, 50) . '...',
                'Criado em' => $e->created_at->format('d/m/Y H:i')
            ])->toArray();

            $this->table(['ID', 'Prefeitura', 'Objeto', 'Criado em'], $etpData);
        }

        // 2. Verificar Solicitações
        $solicitacoesPendentes = Solicitacao::whereIn('status', ['aberta', 'aguardando_resposta'])->with('prefeitura')->get();
        $countSols = $solicitacoesPendentes->count();

        if ($countSols > 0) {
            $this->warn("\n[!] Existem {$countSols} Solicitação(ões) pendente(s):");
            
            foreach ($diretores as $diretor) {
                // Remove notificações antigas do mesmo tipo
                $diretor->notifications()->where('data->tipo', 'solicitacao')->delete();

                $diretor->notify(new \App\Notifications\PendenciaIdentificada([
                    'titulo' => 'Solicitações Internas',
                    'mensagem' => "Existem {$countSols} solicitação(ões) aguardando resposta.",
                    'tipo' => 'solicitacao',
                    'link' => route('admin.solicitacoes.index'),
                    'count' => $countSols
                ]));
            }

            $solData = $solicitacoesPendentes->map(fn($s) => [
                'ID' => $s->id,
                'Prefeitura' => $s->prefeitura->nome ?? 'N/A',
                'Assunto' => $s->assunto,
                'Status' => $s->status,
                'Criado em' => $s->created_at->format('d/m/Y H:i')
            ])->toArray();

            $this->table(['ID', 'Prefeitura', 'Assunto', 'Status', 'Criado em'], $solData);
        }

        $this->info("\n--- Notificações enviadas para os diretores no Frontend ---");
        
        return 0;
    }
}
