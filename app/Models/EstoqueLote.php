<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstoqueLote extends Model
{
    protected $fillable = [
        'lote_id',
        'processo_id',
        'quantidade_disponivel',
        'quantidade_utilizada'
    ];

    protected $casts = [
        'quantidade_disponivel' => 'decimal:2',
        'quantidade_utilizada' => 'decimal:2',
    ];

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class);
    }

    public function processo(): BelongsTo
    {
        return $this->belongsTo(Processo::class);
    }

    public function reservarQuantidade($quantidade): void
    {
        if ($this->quantidade_disponivel < $quantidade) {
            throw new \Exception("Quantidade insuficiente em estoque. Disponível: {$this->quantidade_disponivel}, Solicitado: {$quantidade}");
        }

        $this->quantidade_disponivel -= $quantidade;
        $this->quantidade_utilizada += $quantidade;
        $this->save();
    }

    public function liberarQuantidade($quantidade): void
    {
        if ($this->quantidade_utilizada < $quantidade) {
            throw new \Exception("Não é possível liberar mais do que foi utilizado. Utilizado: {$this->quantidade_utilizada}, Tentando liberar: {$quantidade}");
        }

        $this->quantidade_disponivel += $quantidade;
        $this->quantidade_utilizada -= $quantidade;
        $this->save();
    }

    public function getPercentualUtilizadoAttribute(): float
    {
        $total = $this->quantidade_disponivel + $this->quantidade_utilizada;
        return $total > 0 ? ($this->quantidade_utilizada / $total) * 100 : 0;
    }

    public function getQuantidadeDisponivelFormatadaAttribute(): string
    {
        return number_format($this->quantidade_disponivel, 2, ',', '.');
    }

    public function getQuantidadeUtilizadaFormatadaAttribute(): string
    {
        return number_format($this->quantidade_utilizada, 2, ',', '.');
    }
}
