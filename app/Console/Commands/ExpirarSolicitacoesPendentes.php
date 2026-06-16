<?php

namespace App\Console\Commands;

use App\Models\AssinaturaLog;
use App\Models\SolicitacaoAssinatura;
use App\Models\User;
use App\Notifications\Assinatura\SolicitacaoExpirando;
use Illuminate\Console\Command;

/**
 * Job diário:
 *   1) Marca solicitações com expires_at no passado e status=pendente como `expirada`
 *   2) Envia lembrete (24h antes) para solicitações que vão expirar
 */
class ExpirarSolicitacoesPendentes extends Command
{
    protected $signature = 'assinaturas:expirar-pendentes
                            {--apenas-lembrete : Só dispara lembretes (não expira nada)}
                            {--apenas-expirar  : Só expira (não dispara lembretes)}';

    protected $description = 'Expira solicitações pendentes vencidas e envia lembretes 24h antes';

    public function handle(): int
    {
        $expiradas = 0;
        $lembretes = 0;

        if (!$this->option('apenas-lembrete')) {
            $expiradas = $this->expirarVencidas();
        }

        if (!$this->option('apenas-expirar')) {
            $lembretes = $this->enviarLembretes();
        }

        $this->info("Expiradas: {$expiradas} | Lembretes enviados: {$lembretes}");
        return self::SUCCESS;
    }

    private function expirarVencidas(): int
    {
        $vencidas = SolicitacaoAssinatura::query()
            ->where('status', SolicitacaoAssinatura::STATUS_PENDENTE)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($vencidas as $sol) {
            $sol->transicionarPara(\App\Assinatura\Domain\Enums\StatusSolicitacao::Expirada, [
                'processada_em' => now(),
            ]);

            AssinaturaLog::create([
                'acao'                      => AssinaturaLog::ACAO_EXPIRADA,
                'solicitacao_assinatura_id' => $sol->id,
                'documento_versao_id'       => $sol->documento_versao_id,
                'metadados'                 => ['expires_at_original' => $sol->expires_at?->toIso8601String()],
            ]);
        }

        return $vencidas->count();
    }

    /**
     * Envia lembrete para solicitações que expiram nas próximas 24h.
     * Idempotente: usa metadado em assinatura_logs para evitar duplicar.
     */
    private function enviarLembretes(): int
    {
        $expirandoEm24h = SolicitacaoAssinatura::query()
            ->where('status', SolicitacaoAssinatura::STATUS_PENDENTE)
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDay()])
            ->get();

        $enviados = 0;

        foreach ($expirandoEm24h as $sol) {
            // Idempotência: já enviou lembrete pra essa solicitação?
            $jaEnviado = AssinaturaLog::query()
                ->where('solicitacao_assinatura_id', $sol->id)
                ->where('acao', AssinaturaLog::ACAO_NOTIFICADA)
                ->whereJsonContains('metadados->tipo', 'lembrete_expirando')
                ->exists();

            if ($jaEnviado) {
                continue;
            }

            $user = User::find($sol->assinante_user_id);
            if (!$user) continue;

            try {
                $user->notify(new SolicitacaoExpirando($sol));
                AssinaturaLog::create([
                    'acao'                      => AssinaturaLog::ACAO_NOTIFICADA,
                    'solicitacao_assinatura_id' => $sol->id,
                    'documento_versao_id'       => $sol->documento_versao_id,
                    'metadados'                 => ['tipo' => 'lembrete_expirando'],
                ]);
                $enviados++;
            } catch (\Throwable $e) {
                $this->warn("Falha ao notificar solicitação {$sol->id}: {$e->getMessage()}");
            }
        }

        return $enviados;
    }
}
