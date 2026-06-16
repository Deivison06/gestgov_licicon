<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DocumentoVersao extends Model
{
    use HasFactory;

    protected $table = 'documento_versoes';

    protected $fillable = [
        'documentavel_type',
        'documentavel_id',
        'versao',
        'caminho_pdf',
        'hash_sha256',
        'gerado_por_user_id',
        'gerado_em',
        'assinaturas_consolidadas_em',
        'caminho_pdf_assinado',
        'hash_pdf_assinado',
    ];

    protected $casts = [
        'versao'                       => 'integer',
        'gerado_em'                    => 'datetime',
        'assinaturas_consolidadas_em'  => 'datetime',
    ];

    public function documentavel(): MorphTo
    {
        return $this->morphTo();
    }

    public function geradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gerado_por_user_id');
    }

    public function solicitacoes(): HasMany
    {
        return $this->hasMany(SolicitacaoAssinatura::class, 'documento_versao_id');
    }

    public function assinaturas(): HasMany
    {
        return $this->hasMany(AssinaturaDigital::class, 'documento_versao_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AssinaturaLog::class, 'documento_versao_id');
    }

    public function isConsolidada(): bool
    {
        return $this->assinaturas_consolidadas_em !== null;
    }

    public function estaEditavel(): bool
    {
        // Sem nenhuma assinatura registrada ainda.
        return !$this->assinaturas()->exists();
    }
}
