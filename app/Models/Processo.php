<?php

namespace App\Models;

use App\Enums\ModalidadeEnum;
use App\Enums\ProcessoStatusEnum;
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
        'contTotalPage', // CORREÇÃO: Removi o ponto extra
        'status',
        'data_cancelamento', // Adicionado
        'motivo_cancelamento', // Adicionado
        'data_adiamento', // Adicionado
        'justificativa_adiamento', // Adicionado
        'processo_original_id' // Adicionado para republicação
    ];

    protected $casts = [
        'modalidade' => ModalidadeEnum::class,
        'tipo_procedimento' => TipoProcedimentoEnum::class,
        'tipo_contratacao' => TipoContratacaoEnum::class,
        'status' => ProcessoStatusEnum::class,
        'data_cancelamento' => 'date',
        'data_adiamento' => 'date',
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

    // Processo original (para republicação)
    public function processoOriginal()
    {
        return $this->belongsTo(Processo::class, 'processo_original_id');
    }

    // Processos republicados
    public function processosRepublicados()
    {
        return $this->hasMany(Processo::class, 'processo_original_id');
    }

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

    /**
     * Obter todas as republicações deste processo
     */
    public function republicacoes()
    {
        return $this->hasMany(Documento::class)
            ->where('tipo_documento', 'like', '%republicacao%')
            ->orWhere('tipo_documento', 'like', '%adiado%')
            ->orderBy('gerado_em', 'desc');
    }

    /**
     * Obter documentos de cancelamento
     */
    public function cancelamentos()
    {
        return $this->hasMany(Documento::class)
            ->where('tipo_documento', 'like', '%cancelamento%');
    }

    /**
     * Verificar se o processo está cancelado
     */
    public function getEstaCanceladoAttribute()
    {
        return $this->status === ProcessoStatusEnum::CANCELADO ||
            $this->cancelamentos()->exists();
    }

    /**
     * Verificar se o processo foi adiado
     */
    public function getFoiAdiadoAttribute()
    {
        return $this->republicacoes()
            ->where('tipo_documento', 'like', '%adiado%')
            ->exists();
    }

    /**
     * Obter última republicação
     */
    public function ultimaRepublicacao()
    {
        return $this->republicacoes()->latest()->first();
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

    // Adicione este accessor para garantir que o status seja sempre uma instância do enum
    public function getStatusAttribute($value)
    {
        if ($value instanceof ProcessoStatusEnum) {
            return $value;
        }

        return ProcessoStatusEnum::tryFrom($value) ?? ProcessoStatusEnum::RASCUNHO;
    }

    public function setStatusAttribute($value)
    {
        if ($value instanceof ProcessoStatusEnum) {
            $this->attributes['status'] = $value->value;
        } else {
            $this->attributes['status'] = $value;
        }
    }

    public function calcularStatusAutomatico(): ProcessoStatusEnum
    {
        // Se já estiver em status final, não mexe
        if (in_array($this->status, [
            ProcessoStatusEnum::FINALIZADO,
            ProcessoStatusEnum::CANCELADO,
            ProcessoStatusEnum::ADIADO,
        ])) {
            return $this->status;
        }

        if ($this->contrato()->exists()) {
            return ProcessoStatusEnum::EM_CONTRATO;
        }

        if ($this->finalizacao()->exists()) {
            return ProcessoStatusEnum::EM_FINALIZACAO;
        }

        if ($this->detalhe()->exists()) {
            return ProcessoStatusEnum::EM_INICIO;
        }

        return ProcessoStatusEnum::RASCUNHO;
    }

    public function atualizarStatusAutomatico(): bool
    {
        $novoStatus = $this->calcularStatusAutomatico();

        if ($this->status !== $novoStatus) {
            $this->update([
                'status' => $novoStatus,
            ]);
            return true;
        }

        return false;
    }

}
