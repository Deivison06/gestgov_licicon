<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SolicitacaoAssinatura extends Model
{
    use HasFactory;

    protected $table = 'solicitacoes_assinatura';

    // Estados possíveis — usados pelo state machine no AssinaturaService.
    public const STATUS_PENDENTE   = 'pendente';
    public const STATUS_ASSINADA   = 'assinada';
    public const STATUS_RECUSADA   = 'recusada';
    public const STATUS_CANCELADA  = 'cancelada';
    public const STATUS_EXPIRADA   = 'expirada';

    public const STATUSES = [
        self::STATUS_PENDENTE,
        self::STATUS_ASSINADA,
        self::STATUS_RECUSADA,
        self::STATUS_CANCELADA,
        self::STATUS_EXPIRADA,
    ];

    public const STATUSES_FINALIZADOS = [
        self::STATUS_ASSINADA,
        self::STATUS_RECUSADA,
        self::STATUS_CANCELADA,
        self::STATUS_EXPIRADA,
    ];

    protected $fillable = [
        'documento_versao_id',
        'assinante_user_id',
        'solicitado_por_user_id',
        'status',
        'ordem',
        'obrigatoria',
        'solicitado_em',
        'expires_at',
        'processada_em',
        'token_acesso',
        'motivo_recusa',
    ];

    protected $casts = [
        'ordem'         => 'integer',
        'obrigatoria'   => 'boolean',
        'solicitado_em' => 'datetime',
        'expires_at'    => 'datetime',
        'processada_em' => 'datetime',
    ];

    public function versao(): BelongsTo
    {
        return $this->belongsTo(DocumentoVersao::class, 'documento_versao_id');
    }

    public function assinante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assinante_user_id');
    }

    public function solicitadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitado_por_user_id');
    }

    public function assinatura(): HasOne
    {
        return $this->hasOne(AssinaturaDigital::class, 'solicitacao_assinatura_id');
    }

    /**
     * Aplica uma transição de status validada pela máquina de estados
     * (StatusSolicitacao). Lança \DomainException se a transição for inválida.
     * `$extra` carrega campos que mudam junto (ex.: motivo_recusa, processada_em).
     */
    public function transicionarPara(\App\Assinatura\Domain\Enums\StatusSolicitacao $novo, array $extra = []): void
    {
        $atual = \App\Assinatura\Domain\Enums\StatusSolicitacao::from($this->status);

        if (!$atual->podeTransicionarPara($novo)) {
            throw new \DomainException(
                "Transição de status inválida: {$atual->value} → {$novo->value}."
            );
        }

        $this->update(array_merge(['status' => $novo->value], $extra));
    }

    public function scopePendentes($query)
    {
        return $query->where('status', self::STATUS_PENDENTE);
    }

    public function scopeAtivas($query)
    {
        return $query->whereNotIn('status', self::STATUSES_FINALIZADOS);
    }

    public function estaExpirada(): bool
    {
        return $this->status === self::STATUS_EXPIRADA
            || ($this->expires_at && $this->expires_at->isPast() && $this->status === self::STATUS_PENDENTE);
    }

    public function podeSerAssinada(): bool
    {
        return $this->status === self::STATUS_PENDENTE && !$this->estaExpirada();
    }
}
