<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lote extends Model
{
    use HasFactory;

    protected $table = 'lotes';

    protected $fillable = [
        'vencedor_id',
        'lote',
        'status',
        'item',
        'descricao',
        'unidade',
        'marca',
        'modelo',
        'quantidade',
        'vl_unit',
        'vl_total',
        'ordem'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'quantidade' => 'decimal:2',
        'vl_unit' => 'decimal:2',
        'vl_total' => 'decimal:2',
        'ordem' => 'integer',
    ];

    /**
     * Get the vencedor that owns the lote.
     */
    public function vencedor(): BelongsTo
    {
        return $this->belongsTo(Vencedor::class);
    }

    /**
     * Scope a query to only include lotes from a specific vencedor.
     */
    public function scopeDoVencedor($query, $vencedorId)
    {
        return $query->where('vencedor_id', $vencedorId);
    }

    /**
     * Scope a query to order lotes by ordem.
     */
    public function scopeOrdenados($query)
    {
        return $query->orderBy('ordem');
    }

    /**
     * Scope a query to only include lotes with specific lote number.
     */
    public function scopeDoLote($query, $lote)
    {
        return $query->where('lote', $lote);
    }

    /**
     * Calculate vl_total based on quantidade and vl_unit
     */
    public function calcularTotal(): void
    {
        $this->vl_total = $this->quantidade * $this->vl_unit;
    }

    /**
     * Boot method to automatically calculate vl_total
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($lote) {
            $lote->calcularTotal();
        });
    }

    /**
     * Get formatted value for display
     */
    public function getValorUnitarioFormatadoAttribute(): string
    {
        return 'R$ ' . number_format($this->vl_unit, 2, ',', '.');
    }

    /**
     * Get formatted total value for display
     */
    public function getValorTotalFormatadoAttribute(): string
    {
        return 'R$ ' . number_format($this->vl_total, 2, ',', '.');
    }

    /**
     * Get formatted quantity for display
     */
    public function getQuantidadeFormatadaAttribute(): string
    {
        return number_format($this->quantidade, 2, ',', '.');
    }
}
