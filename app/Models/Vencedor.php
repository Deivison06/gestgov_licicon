<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vencedor extends Model
{
    use HasFactory;

    protected $table = 'vencedores';

    protected $fillable = [
        'processo_id',
        'razao_social',
        'cnpj',
        'representante',
        'cpf',
        'endereco', // <-- ADICIONE AQUI
        'ordem'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'ordem' => 'integer',
    ];

    /**
     * Get the processo that owns the vencedor.
     */
    public function processo(): BelongsTo
    {
        return $this->belongsTo(Processo::class);
    }

    /**
     * Get the lotes for the vencedor.
     */
    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class)->orderBy('ordem');
    }

    /**
     * Scope a query to only include vencedores from a specific processo.
     */
    public function scopeDoProcesso($query, $processoId)
    {
        return $query->where('processo_id', $processoId);
    }

    /**
     * Scope a query to order vencedores by ordem.
     */
    public function scopeOrdenados($query)
    {
        return $query->orderBy('ordem');
    }

    /**
     * Format CNPJ for display
     */
    public function getCnpjFormatadoAttribute(): string
    {
        $cnpj = $this->cnpj;
        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpj);
    }

    /**
     * Format CPF for display
     */
    public function getCpfFormatadoAttribute(): string
    {
        $cpf = $this->cpf;
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
    }

    /**
     * Calculate total value from all lotes
     */
    public function getValorTotalAttribute(): float
    {
        return $this->lotes->sum('vl_total');
    }

    /**
     * Get total quantity from all lotes
     */
    public function getQuantidadeTotalAttribute(): float
    {
        return $this->lotes->sum('quantidade');
    }
}
