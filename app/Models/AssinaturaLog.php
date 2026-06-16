<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only audit log. Sem updated_at — uma vez gravado, nunca editado.
 */
class AssinaturaLog extends Model
{
    use HasFactory;

    protected $table = 'assinatura_logs';

    // Pega só created_at — não há updated_at na tabela.
    public const UPDATED_AT = null;
    public $timestamps = true;

    public const ACAO_CRIADA       = 'criada';
    public const ACAO_NOTIFICADA   = 'notificada';
    public const ACAO_VISUALIZADA  = 'visualizada';
    public const ACAO_ASSINADA     = 'assinada';
    public const ACAO_RECUSADA     = 'recusada';
    public const ACAO_CANCELADA    = 'cancelada';
    public const ACAO_EXPIRADA     = 'expirada';
    public const ACAO_REGERADA     = 'regerada';

    public const ACOES = [
        self::ACAO_CRIADA,
        self::ACAO_NOTIFICADA,
        self::ACAO_VISUALIZADA,
        self::ACAO_ASSINADA,
        self::ACAO_RECUSADA,
        self::ACAO_CANCELADA,
        self::ACAO_EXPIRADA,
        self::ACAO_REGERADA,
    ];

    protected $fillable = [
        'acao',
        'solicitacao_assinatura_id',
        'documento_versao_id',
        'user_id',
        'ip',
        'user_agent',
        'metadados',
    ];

    protected $casts = [
        'metadados'  => 'array',
        'created_at' => 'datetime',
    ];

    public function solicitacao(): BelongsTo
    {
        return $this->belongsTo(SolicitacaoAssinatura::class, 'solicitacao_assinatura_id');
    }

    public function versao(): BelongsTo
    {
        return $this->belongsTo(DocumentoVersao::class, 'documento_versao_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
