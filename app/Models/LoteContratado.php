<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoteContratado extends Model
{
    protected $fillable = [
        'contrato_id',
        'lote_id',
        'vencedor_id',
        'processo_id',
        'quantidade_contratada',
        'quantidade_disponivel_pos_contrato',
        'valor_unitario',
        'valor_total',
        'status',
        'observacao',
    ];

    protected $casts = [
        'quantidade_contratada' => 'decimal:2',
        'quantidade_disponivel_pos_contrato' => 'decimal:2',
        'valor_unitario' => 'decimal:2',
        'valor_total' => 'decimal:2',
    ];

    /**
     * Relacionamentos
     */
    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class);
    }

    public function vencedor(): BelongsTo
    {
        return $this->belongsTo(Vencedor::class);
    }

    public function processo(): BelongsTo
    {
        return $this->belongsTo(Processo::class);
    }

    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class);
    }

    public function estoque(): BelongsTo
    {
        return $this->belongsTo(EstoqueLote::class, 'lote_id', 'lote_id')
            ->where('processo_id', $this->processo_id);
    }

    /**
     * Escopos
     */
    public function scopeContratadosConfirmados($query)
    {
        return $query->where('status', 'CONTRATADO');
    }

    public function scopeDoProcesso($query, $processoId)
    {
        return $query->where('processo_id', $processoId);
    }

    public function scopeDoLote($query, $loteId)
    {
        return $query->where('lote_id', $loteId);
    }

    public function scopeDoVencedor($query, $vencedorId)
    {
        return $query->where('vencedor_id', $vencedorId);
    }

    public function scopePendentes($query)
    {
        return $query->where('status', 'PENDENTE');
    }

    public function scopeAtivos($query)
    {
        return $query->whereIn('status', ['PENDENTE', 'CONTRATADO']);
    }

    /**
     * Métodos de verificação
     */
    public function podeEditar(): bool
    {
        return $this->status === 'PENDENTE';
    }

    public function podeCancelar(): bool
    {
        return in_array($this->status, ['PENDENTE', 'CONTRATADO']);
    }

    public function podeConfirmar(): bool
    {
        return $this->status === 'PENDENTE';
    }

    /**
     * Métodos de negócio
     */
    public function confirmar(): bool
    {
        if (!$this->podeConfirmar()) {
            return false;
        }

        $this->status = 'CONTRATADO';
        return $this->save();
    }

    public function cancelar(): bool
    {
        if (!$this->podeCancelar()) {
            return false;
        }

        // Liberar estoque antes de cancelar
        $estoque = EstoqueLote::where('lote_id', $this->lote_id)
            ->where('processo_id', $this->processo_id)
            ->first();

        if ($estoque) {
            $estoque->liberarQuantidade($this->quantidade_contratada);
        }

        $this->status = 'CANCELADO';
        return $this->save();
    }

    /**
     * Acessores
     */
    public function getValorTotalFormatadoAttribute(): string
    {
        return 'R$ ' . number_format($this->valor_total, 2, ',', '.');
    }

    public function getQuantidadeContratadaFormatadaAttribute(): string
    {
        return number_format($this->quantidade_contratada, 2, ',', '.');
    }

    public function getQuantidadeDisponivelPosContratoFormatadaAttribute(): string
    {
        return number_format($this->quantidade_disponivel_pos_contrato, 2, ',', '.');
    }

    public function getValorUnitarioFormatadoAttribute(): string
    {
        return 'R$ ' . number_format($this->valor_unitario, 2, ',', '.');
    }

    /**
     * Verificar se há conflito com outras contratações
     */
    public function verificarConflito(): bool
    {
        // Verificar se a quantidade disponível pós-contrato é negativa
        return $this->quantidade_disponivel_pos_contrato < 0;
    }
}
