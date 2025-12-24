<?php

namespace App\Models;

use App\Enums\ModalidadeEnum;
use App\Enums\TipoContratacaoEnum;
use App\Enums\TipoProcedimentoEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Processo extends Model
{
    use HasFactory;

    protected $table = 'processos';

    protected $fillable = [
        'prefeitura_id',
        'modalidade',
        'numero_processo',
        'numero_procedimento',
        'objeto',
        'tipo_procedimento',
        'tipo_contratacao',
        'unidade_numeracao',
        'responsavel_numeracao',
        'portaria_numeracao',
        'user_id',
        'contTotalPage'
    ];

    protected $casts = [
        'modalidade' => ModalidadeEnum::class,
        'tipo_procedimento' => TipoProcedimentoEnum::class,
        'tipo_contratacao' => TipoContratacaoEnum::class,
    ];

    // Relacionamento com Prefeitura
    public function prefeitura()
    {
        return $this->belongsTo(Prefeitura::class);
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function detalhe()
    {
        return $this->hasOne(ProcessoDetalhe::class);
    }

    public function finalizacao()
    {
        return $this->hasOne(Finalizacao::class);
    }

    public function contrato()
    {
        return $this->hasOne(Contrato::class);
    }

    // No modelo Processo, adicione:
    public function lotesContratados()
    {
        return $this->hasMany(LoteContratado::class);
    }

    public function documentos()
    {
        return $this->hasMany(Documento::class);
    }

    public function getTipoContratacaoNomeAttribute(): string
    {
        return $this->tipo_contratacao?->getDisplayName() ?? '—';
    }

    public function getTipoProcedimentoNomeAttribute(): string
    {
        return $this->tipo_procedimento?->getDisplayName() ?? '—';
    }

     /**
     * Get the vencedores for the processo.
     */
    public function vencedores(): HasMany
    {
        return $this->hasMany(Vencedor::class)->orderBy('ordem');
    }

    public function reservas()
    {
        return $this->hasMany(Reserva::class)->orderBy('ordem');
    }

    /**
     * Get all lotes from all vencedores of this processo
     */
    public function getAllLotesAttribute()
    {
        return $this->vencedores->flatMap(function ($vencedor) {
            return $vencedor->lotes;
        });
    }

    /**
     * Get total value from all vencedores
     */
    public function getValorTotalVencedoresAttribute(): float
    {
        return $this->vencedores->sum(function ($vencedor) {
            return $vencedor->valor_total;
        });
    }

    // Accessor para garantir que campos nullable retornem null quando vazios
    public function setResponsavelNumeracaoAttribute($value)
    {
        $this->attributes['responsavel_numeracao'] = $value ?: null;
    }

    public function setPortariaNumeracaoAttribute($value)
    {
        $this->attributes['portaria_numeracao'] = $value ?: null;
    }

    public function setUnidadeNumeracaoAttribute($value)
    {
        $this->attributes['unidade_numeracao'] = $value ?: null;
    }


public function lotes(): HasManyThrough
{
    return $this->hasManyThrough(
        Lote::class,      // Model final
        Vencedor::class,  // Model intermediário
        'processo_id',    // FK em vencedores
        'vencedor_id',    // FK em lotes
        'id',             // PK em processos
        'id'              // PK em vencedores
    );
}
}
