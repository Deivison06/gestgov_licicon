<?php

namespace App\Services\Assinatura;

use App\Models\AssinaturaLog;
use App\Models\DocumentoVersao;
use App\Models\SolicitacaoAssinatura;
use App\Models\User;
use App\Notifications\Assinatura\NovaSolicitacaoAssinatura;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Cria, lista e movimenta solicitações de assinatura.
 * A geração de assinatura propriamente dita (criptografia, validação)
 * fica em AssinaturaService (Fase 4).
 */
class SolicitacaoService
{
    public function __construct(
        private readonly \App\Assinatura\Domain\Rodada\RodadaStrategyFactory $strategyFactory
    ) {}

    /**
     * Cria uma rodada de solicitações para uma versão de documento.
     *
     * @param DocumentoVersao $versao
     * @param array $assinantes Lista de [
     *     'user_id'     => int,
     *     'ordem'       => int (0 = paralelo, 1..n sequencial)  [opcional, default 0]
     *     'obrigatoria' => bool [opcional, default true]
     * ]
     * @param int $solicitadoPorUserId User que disparou
     * @param Carbon|null $expiresAt Prazo default (7 dias se null)
     * @return Collection<int, SolicitacaoAssinatura>
     */
    public function criarRodada(
        DocumentoVersao $versao,
        array $assinantes,
        int $solicitadoPorUserId,
        ?Carbon $expiresAt = null
    ): Collection {
        if (empty($assinantes)) {
            throw new \InvalidArgumentException('Lista de assinantes vazia.');
        }

        if ($versao->assinaturas()->exists()) {
            throw new \DomainException(
                'Esta versão já tem assinaturas registradas — crie uma nova versão antes.'
            );
        }

        $expiresAt = $expiresAt ?? now()->addDays(7);

        return DB::transaction(function () use ($versao, $assinantes, $solicitadoPorUserId, $expiresAt) {
            $criadas = collect();
            $solicitadoEm = now();

            foreach ($assinantes as $config) {
                $userId = $config['user_id'] ?? null;
                if (!$userId) {
                    throw new \InvalidArgumentException('Cada item precisa de user_id.');
                }

                $this->validarAssinante($userId);

                $solicitacao = SolicitacaoAssinatura::create([
                    'documento_versao_id'    => $versao->id,
                    'assinante_user_id'      => $userId,
                    'solicitado_por_user_id' => $solicitadoPorUserId,
                    'status'                 => SolicitacaoAssinatura::STATUS_PENDENTE,
                    'ordem'                  => $config['ordem'] ?? 0,
                    'obrigatoria'            => $config['obrigatoria'] ?? true,
                    'solicitado_em'          => $solicitadoEm,
                    'expires_at'             => $expiresAt,
                    'token_acesso'           => Str::random(64),
                ]);

                AssinaturaLog::create([
                    'acao'                      => AssinaturaLog::ACAO_CRIADA,
                    'solicitacao_assinatura_id' => $solicitacao->id,
                    'documento_versao_id'       => $versao->id,
                    'user_id'                   => $solicitadoPorUserId,
                    'metadados'                 => [
                        'assinante_user_id' => $userId,
                        'ordem'             => $solicitacao->ordem,
                    ],
                ]);

                $criadas->push($solicitacao);
            }

            // Dispara notificações DEPOIS do commit — evita inconsistência se a transação reverter.
            // Em modo sequencial, só o primeiro recebe agora; os outros serão notificados
            // pelo AssinaturaService quando chegar a vez deles.
            DB::afterCommit(function () use ($criadas) {
                $this->notificarRodadaCriada($criadas);
            });

            return $criadas;
        });
    }

    /**
     * Envia NovaSolicitacaoAssinatura aos assinantes apropriados:
     * - Paralelo (ordem=0): todos
     * - Sequencial: apenas o de menor ordem
     */
    private function notificarRodadaCriada(Collection $solicitacoes): void
    {
        $alvo = $this->strategyFactory
            ->paraColecao($solicitacoes)
            ->alvosNotificacaoInicial($solicitacoes);

        foreach ($alvo as $sol) {
            try {
                $user = User::find($sol->assinante_user_id);
                if ($user) {
                    $user->notify(new NovaSolicitacaoAssinatura($sol));
                }
            } catch (\Throwable $e) {
                Log::warning('Falha ao notificar nova solicitação', [
                    'solicitacao_id' => $sol->id,
                    'erro'           => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Cancela toda a rodada da versão (recusa de qualquer um cancela a rodada inteira —
     * decisão 4 do plano).
     *
     * @param DocumentoVersao $versao
     * @param int $userId Quem cancelou
     * @param string|null $motivo
     * @return int Solicitações afetadas
     */
    public function cancelarRodada(DocumentoVersao $versao, int $userId, ?string $motivo = null): int
    {
        return DB::transaction(function () use ($versao, $userId, $motivo) {
            $afetadas = SolicitacaoAssinatura::query()
                ->where('documento_versao_id', $versao->id)
                ->whereIn('status', [
                    SolicitacaoAssinatura::STATUS_PENDENTE,
                    SolicitacaoAssinatura::STATUS_ASSINADA,
                ])
                ->lockForUpdate()
                ->get();

            foreach ($afetadas as $sol) {
                $sol->transicionarPara(\App\Assinatura\Domain\Enums\StatusSolicitacao::Cancelada, [
                    'processada_em' => now(),
                ]);

                AssinaturaLog::create([
                    'acao'                      => AssinaturaLog::ACAO_CANCELADA,
                    'solicitacao_assinatura_id' => $sol->id,
                    'documento_versao_id'       => $versao->id,
                    'user_id'                   => $userId,
                    'metadados'                 => ['motivo' => $motivo],
                ]);
            }

            return $afetadas->count();
        });
    }

    /**
     * Garante que o user existe, está com is_assinante=true e tem a role.
     */
    private function validarAssinante(int $userId): void
    {
        $user = User::find($userId);

        if (!$user) {
            throw new \InvalidArgumentException("User #{$userId} não existe.");
        }

        if (!$user->is_assinante) {
            throw new \DomainException("User #{$userId} ({$user->name}) não está marcado como assinante.");
        }

        if (!$user->hasRole('assinante')) {
            throw new \DomainException("User #{$userId} ({$user->name}) não tem a role `assinante`.");
        }
    }
}
