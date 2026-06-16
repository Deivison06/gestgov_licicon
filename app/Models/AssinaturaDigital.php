<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssinaturaDigital extends Model
{
    use HasFactory;

    protected $table = 'assinaturas_digitais';

    protected $fillable = [
        'solicitacao_assinatura_id',
        'documento_versao_id',
        'assinante_user_id',
        'hash_documento_no_momento',
        'hash_cadeia_anterior',
        'hash_proprio',
        'codigo_verificador',
        'ip',
        'user_agent',
        'assinado_em',
        'metadados',
    ];

    protected $casts = [
        'assinado_em' => 'datetime',
        'metadados'   => 'array',
    ];

    public function solicitacao(): BelongsTo
    {
        return $this->belongsTo(SolicitacaoAssinatura::class, 'solicitacao_assinatura_id');
    }

    public function versao(): BelongsTo
    {
        return $this->belongsTo(DocumentoVersao::class, 'documento_versao_id');
    }

    public function assinante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assinante_user_id');
    }

    /**
     * CRC humano: primeiros 8 chars do hash do PDF assinado (consistente com SEI).
     * Mostrado no rodapé do PDF para validação manual.
     */
    public function getCrcHumanoAttribute(): string
    {
        $base = $this->versao?->hash_pdf_assinado ?? $this->hash_documento_no_momento;
        return strtoupper(substr($base, 0, 8));
    }

    /**
     * Validade da cadeia: re-calcula `hash_proprio` e compara com o gravado.
     * Detecta adulteração no DB ou inserção fora de ordem.
     */
    public function cadeiaIntegra(): bool
    {
        // Usa a MESMA fórmula da geração (centralizada em HashCadeia) — evita divergência
        // entre o cálculo que grava e o que verifica.
        $recalculado = \App\Assinatura\Domain\ValueObjects\HashCadeia::calcular(
            $this->hash_documento_no_momento,
            $this->hash_cadeia_anterior,
            $this->assinante_user_id,
            $this->assinado_em->format('Y-m-d H:i:s.u')
        );

        // O timestamp usado na geração inclui microtime — pode haver divergência
        // de 1 microssegundo em edge cases. A comparação real fica no Service que
        // gera com timestamp determinístico armazenado. Este método é checagem
        // best-effort para detectar adulteração grosseira.
        return $recalculado === $this->hash_proprio;
    }
}
