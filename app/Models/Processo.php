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
        'contTotalPagePhase1',
        'contTotalPagePhase2',
        'contTotalPagePhase3',
        'status',
        'data_cancelamento', // Adicionado
        'motivo_cancelamento', // Adicionado
        'data_adiamento', // Adicionado
        'justificativa_adiamento', // Adicionado
        'processo_original_id', // Adicionado para republicação
        'planejamento_status',
        'planejamento_data_abertura',
        'planejamento_fim_recurso',
        'planejamento_ordem',
        'finalizacao_iniciada_por_id',
        'finalizacao_iniciada_em',
    ];

    protected $casts = [
        'modalidade' => ModalidadeEnum::class,
        'tipo_procedimento' => TipoProcedimentoEnum::class,
        'tipo_contratacao' => TipoContratacaoEnum::class,
        'status' => ProcessoStatusEnum::class,
        'data_cancelamento' => 'date',
        'data_adiamento' => 'date',
        'planejamento_data_abertura' => 'datetime',
        'planejamento_fim_recurso' => 'datetime',
        'finalizacao_iniciada_em' => 'datetime',
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

    public function homologacoes(): HasMany
    {
        return $this->hasMany(Homologacao::class)->orderBy('numero_sequencial');
    }

    public function lotesPendentesHomologacao()
    {
        return Lote::query()
            ->whereIn('vencedor_id', $this->vencedores()->select('id'))
            ->whereNull('homologacao_id');
    }

    public function temLotesPendentesHomologacao(): bool
    {
        return $this->lotesPendentesHomologacao()->exists();
    }

    public function contrato()
    {
        return $this->hasOne(Contrato::class);
    }

    public function contratos()
    {
        return $this->hasMany(Contrato::class)->orderBy('numero_sequencial');
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

    public function pesquisaPrecoItens(): HasMany
    {
        return $this->hasMany(PesquisaPrecoItem::class)->orderBy('id');
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

    public function cancelamentos()
    {
        return $this->hasMany(Documento::class)
            ->where('tipo_documento', 'like', '%cancelamento%');
    }

    public function etp()
    {
        return $this->hasOne(Etp::class, 'processo_id');
    }

    public function notas()
    {
        return $this->hasMany(ProcessoNota::class)->with('user')->latest();
    }

    public function finalizacaoIniciador()
    {
        return $this->belongsTo(User::class, 'finalizacao_iniciada_por_id');
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
            Lote::class,
            Vencedor::class,  // Model intermediário
            'processo_id',    // FK em vencedores
            'vencedor_id',    // FK em lotes
            'id',             // PK em processos
            'id'              // PK em vencedores
        );
    }

    public function calcularStatusAutomatico(): ProcessoStatusEnum
    {
        // Se já estiver em status final, não mexe
        if (in_array($this->status, [
            ProcessoStatusEnum::FINALIZADO,
            ProcessoStatusEnum::CANCELADO,
            ProcessoStatusEnum::ADIADO,
            ProcessoStatusEnum::REPUBLICADO,
        ])) {
            return $this->status;
        }

        // Qualquer processo que não seja final/cancelado/adiado/republicado
        // é considerado "Em andamento"
        return ProcessoStatusEnum::EM_ANDAMENTO;
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

    public function aguardandoRespostaRecurso(): bool
    {
        return $this->planejamento_status === 'em_recurso'
            && $this->planejamento_fim_recurso !== null
            && $this->planejamento_fim_recurso->isPast();
    }

    public static function calcularFimRecursoSessao(\Carbon\Carbon $inicio, int $diasUteis = 3): \Carbon\Carbon
    {
        $data = $inicio->copy();
        $contagem = 0;
        while ($contagem < $diasUteis) {
            $data->addDay();
            if ($data->isWeekday()) {
                $contagem++;
            }
        }
        return $data;
    }

    protected static function booted()
    {
        static::updating(function ($processo) {
            if ($processo->isDirty('planejamento_status')) {
                $processo->planejamento_ordem = 0;
            }
        });
    }
}
