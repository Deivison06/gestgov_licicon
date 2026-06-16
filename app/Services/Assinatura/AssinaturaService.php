<?php

namespace App\Services\Assinatura;

use App\Assinatura\Domain\Enums\StatusSolicitacao;
use App\Assinatura\Domain\Events\AssinaturaRecusada;
use App\Assinatura\Domain\Events\AssinaturaRegistrada;
use App\Assinatura\Domain\Events\RodadaConcluida;
use App\Assinatura\Domain\Rodada\RodadaStrategyFactory;
use App\Models\AssinaturaDigital;
use App\Models\AssinaturaLog;
use App\Models\DocumentoVersao;
use App\Models\SolicitacaoAssinatura;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Núcleo do ato de assinar / recusar.
 *
 * Responsabilidades:
 *  - Validar pré-condições (status, expiração, user, integridade do PDF)
 *  - Lock pessimista para evitar race na cadeia de hash
 *  - Construir a cadeia (hash_cadeia_anterior → hash_proprio)
 *  - Gerar código verificador único
 *  - Snapshot dos dados PII do assinante (nome, cargo, matrícula, portaria)
 *  - Marcar a versão para consolidação quando for a última assinatura
 *  - Cancelar rodada inteira em caso de recusa (decisão 4)
 *
 * A consolidação visual do PDF (estampagem + QR) acontece na Fase 5.
 */
class AssinaturaService
{
    public function __construct(
        private readonly SolicitacaoService $solicitacaoService,
        private readonly RodadaStrategyFactory $strategyFactory
    ) {}

    /**
     * Registra a assinatura de uma solicitação.
     *
     * @param SolicitacaoAssinatura $solicitacao
     * @param User                  $assinante     Quem está assinando (auth()->user())
     * @param string                $ip
     * @param string                $userAgent
     * @return AssinaturaDigital
     */
    public function assinar(
        SolicitacaoAssinatura $solicitacao,
        User $assinante,
        string $ip,
        string $userAgent
    ): AssinaturaDigital {
        return DB::transaction(function () use ($solicitacao, $assinante, $ip, $userAgent) {
            // 1) Lock pessimista — evita 2 assinaturas concorrentes corromperem a cadeia
            $versao = DocumentoVersao::lockForUpdate()->findOrFail($solicitacao->documento_versao_id);
            $solicitacao = SolicitacaoAssinatura::lockForUpdate()->findOrFail($solicitacao->id);

            // 2) Validações de pré-condição
            $this->validarPreCondicoes($solicitacao, $assinante, $versao);

            // 3) Integridade do PDF (best-effort — só checa se o arquivo existe localmente)
            if ($versao->caminho_pdf && file_exists($versao->caminho_pdf)) {
                $hashAtual = hash_file('sha256', $versao->caminho_pdf);
                if ($hashAtual !== $versao->hash_sha256) {
                    throw new \DomainException(
                        'PDF foi alterado desde a criação da versão — assinatura abortada por segurança.'
                    );
                }
            }

            // 4) Construir cadeia
            $hashCadeiaAnterior = $this->ultimoHashDaCadeia($versao->id);
            $assinadoEm = now();
            $hashProprio = $this->calcularHashProprio(
                $versao->hash_sha256,
                $hashCadeiaAnterior,
                $assinante->id,
                $assinadoEm->format('Y-m-d H:i:s.u')
            );

            // 5) Registrar assinatura
            $assinatura = AssinaturaDigital::create([
                'solicitacao_assinatura_id' => $solicitacao->id,
                'documento_versao_id'       => $versao->id,
                'assinante_user_id'         => $assinante->id,
                'hash_documento_no_momento' => $versao->hash_sha256,
                'hash_cadeia_anterior'      => $hashCadeiaAnterior,
                'hash_proprio'              => $hashProprio,
                'codigo_verificador'        => $this->gerarCodigoVerificadorUnico(),
                'ip'                        => $ip,
                'user_agent'                => $userAgent,
                'assinado_em'               => $assinadoEm,
                'metadados'                 => $this->snapshotAssinante($assinante),
            ]);

            // 6) Mover solicitação para `assinada` (via máquina de estados)
            $solicitacao->transicionarPara(StatusSolicitacao::Assinada, [
                'processada_em' => $assinadoEm,
            ]);

            // 7) Log de auditoria
            AssinaturaLog::create([
                'acao'                      => AssinaturaLog::ACAO_ASSINADA,
                'solicitacao_assinatura_id' => $solicitacao->id,
                'documento_versao_id'       => $versao->id,
                'user_id'                   => $assinante->id,
                'ip'                        => $ip,
                'user_agent'                => $userAgent,
                'metadados'                 => [
                    'codigo_verificador' => $assinatura->codigo_verificador,
                    'ordem'              => $solicitacao->ordem,
                ],
            ]);

            // 8) Detectar fim da rodada — marca para consolidação (estampa o PDF)
            $rodadaConcluida = $this->rodadaConcluida($versao);
            if ($rodadaConcluida) {
                $versao->update(['assinaturas_consolidadas_em' => $assinadoEm]);
            }

            // 9) Efeitos colaterais saem via eventos (pós-commit → listeners):
            //    - AssinaturaRegistrada → notifica o próximo (modo sequencial)
            //    - RodadaConcluida      → consolida o PDF + notifica o operador
            DB::afterCommit(function () use ($assinatura, $solicitacao, $versao, $rodadaConcluida) {
                event(new AssinaturaRegistrada($assinatura, $solicitacao, $versao));
                if ($rodadaConcluida) {
                    event(new RodadaConcluida($versao));
                }
            });

            return $assinatura;
        });
    }

    /**
     * Recusa uma solicitação. Em seguida, cancela toda a rodada (decisão 4).
     */
    public function recusar(
        SolicitacaoAssinatura $solicitacao,
        User $assinante,
        string $motivo,
        string $ip,
        string $userAgent
    ): void {
        if (trim($motivo) === '') {
            throw new \InvalidArgumentException('O motivo da recusa é obrigatório.');
        }

        DB::transaction(function () use ($solicitacao, $assinante, $motivo, $ip, $userAgent) {
            $solicitacao = SolicitacaoAssinatura::lockForUpdate()->findOrFail($solicitacao->id);
            $versao      = DocumentoVersao::findOrFail($solicitacao->documento_versao_id);

            // Validações
            if ($solicitacao->assinante_user_id !== $assinante->id) {
                throw new \DomainException('Esta solicitação não é desta pessoa.');
            }

            if ($solicitacao->status !== SolicitacaoAssinatura::STATUS_PENDENTE) {
                throw new \DomainException("Solicitação não pode ser recusada — status atual: {$solicitacao->status}.");
            }

            // Marca a do recusante (via máquina de estados)
            $solicitacao->transicionarPara(StatusSolicitacao::Recusada, [
                'motivo_recusa' => $motivo,
                'processada_em' => now(),
            ]);

            AssinaturaLog::create([
                'acao'                      => AssinaturaLog::ACAO_RECUSADA,
                'solicitacao_assinatura_id' => $solicitacao->id,
                'documento_versao_id'       => $versao->id,
                'user_id'                   => $assinante->id,
                'ip'                        => $ip,
                'user_agent'                => $userAgent,
                'metadados'                 => ['motivo' => $motivo],
            ]);

            // Decisão 4: recusa cancela rodada inteira
            $this->solicitacaoService->cancelarRodada(
                $versao,
                $assinante->id,
                "Recusada pelo assinante: {$motivo}"
            );

            // Aviso ao solicitante sai via evento (pós-commit → listener).
            DB::afterCommit(function () use ($solicitacao, $motivo) {
                event(new AssinaturaRecusada($solicitacao, $motivo));
            });
        });
    }

    // ====================================================================
    // Internos
    // ====================================================================

    private function validarPreCondicoes(
        SolicitacaoAssinatura $solicitacao,
        User $assinante,
        DocumentoVersao $versao
    ): void {
        if (!$assinante->is_assinante) {
            throw new \DomainException("Usuário {$assinante->name} não está marcado como assinante.");
        }

        if (!$assinante->hasRole('assinante')) {
            throw new \DomainException("Usuário {$assinante->name} não tem a role `assinante`.");
        }

        if ($solicitacao->assinante_user_id !== $assinante->id) {
            throw new \DomainException('Esta solicitação não pertence a este usuário.');
        }

        if ($solicitacao->status !== SolicitacaoAssinatura::STATUS_PENDENTE) {
            throw new \DomainException("Solicitação não pode ser assinada — status atual: {$solicitacao->status}.");
        }

        if ($solicitacao->expires_at && $solicitacao->expires_at->isPast()) {
            throw new \DomainException('Solicitação expirada — solicite uma prorrogação ou nova versão.');
        }

        // Regra de ordem (paralelo x sequencial) centralizada na strategy de modo.
        $this->strategyFactory
            ->paraSolicitacao($solicitacao)
            ->validarPodeAssinarAgora($solicitacao, $versao);
    }

    private function ultimoHashDaCadeia(int $versaoId): ?string
    {
        return AssinaturaDigital::query()
            ->where('documento_versao_id', $versaoId)
            ->orderByDesc('assinado_em')
            ->orderByDesc('id')
            ->value('hash_proprio');
    }

    private function calcularHashProprio(
        string $hashDoc,
        ?string $hashAnterior,
        int $assinanteId,
        string $timestampStr
    ): string {
        // Fórmula centralizada em HashCadeia (mesma usada por AssinaturaDigital::cadeiaIntegra).
        return \App\Assinatura\Domain\ValueObjects\HashCadeia::calcular(
            $hashDoc,
            $hashAnterior,
            $assinanteId,
            $timestampStr
        );
    }

    /**
     * Gera código verificador único de 20 chars (10 numéricos + 10 alfanuméricos).
     * Tenta até 5x em caso de colisão (extremamente improvável).
     */
    private function gerarCodigoVerificadorUnico(): string
    {
        for ($i = 0; $i < 5; $i++) {
            $numerico = str_pad((string) random_int(1, 9_999_999_999), 10, '0', STR_PAD_LEFT);
            $alfa     = substr(strtoupper(Str::random(20)), 0, 10);
            $codigo   = $numerico . $alfa;
            if (!AssinaturaDigital::where('codigo_verificador', $codigo)->exists()) {
                return $codigo;
            }
        }
        throw new \RuntimeException('Não foi possível gerar um código verificador único após 5 tentativas.');
    }

    /**
     * Snapshot dos dados PII do assinante no momento da assinatura.
     * Se o user for editado/desativado depois, a assinatura preserva o estado real.
     */
    private function snapshotAssinante(User $u): array
    {
        $u->loadMissing(['prefeitura', 'unidade']);
        return [
            'nome'            => $u->name,
            'email'           => $u->email,
            'cpf'             => $u->cpf,
            'numero_portaria' => $u->numero_portaria,
            'data_portaria'   => optional($u->data_portaria)->format('Y-m-d'),
            'prefeitura'      => optional($u->prefeitura)->nome ?? optional($u->prefeitura)->cidade,
            'unidade'         => optional($u->unidade)->nome,
        ];
    }

    private function rodadaConcluida(DocumentoVersao $versao): bool
    {
        $pendentes = SolicitacaoAssinatura::query()
            ->where('documento_versao_id', $versao->id)
            ->where('status', SolicitacaoAssinatura::STATUS_PENDENTE)
            ->exists();

        return !$pendentes;
    }
}
