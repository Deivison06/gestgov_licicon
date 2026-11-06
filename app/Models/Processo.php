<?php

namespace App\Models;

use App\Enums\ModalidadeEnum;
use App\Enums\ProcessoStatusEnum;
use App\Enums\TipoContratacaoEnum;
use App\Enums\TipoProcedimentoEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'nome_resumido',
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
        'planejamento_data_abertura', // TODO: remover coluna (obsoleta desde 2026-07-01, substituída por detalhe.data_hora_fase_edital)
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
        'planejamento_data_abertura' => 'datetime', // TODO: remover (obsoleta, ver fillable)
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

    public function pesquisaPrecoMemoriaItens(): HasMany
    {
        return $this->hasMany(PesquisaPrecoMemoriaItem::class)->orderBy('ordem');
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
     * Valor total do processo — fonte canônica usada em toda a tela de Contratos
     * (listagem, detalhe, relatórios). Cadeia de fallback, da fonte mais granular/
     * confiável pra mais genérica: soma dos vencedores (Pregão/Concorrência) →
     * homologação → finalização → valor estimado no detalhe (Dispensa/
     * Inexigibilidade, contratação direta sem nenhuma das etapas acima).
     *
     * Todas essas colunas (exceto a soma de vencedores) são VARCHAR preenchidas
     * à mão, às vezes um número limpo ("126.474,33"), às vezes um texto composto
     * ("R$ 400.109,04 (Quatrocentos mil, cento e nove reais e quatro centavos)",
     * às vezes citando outros valores depois — parcela, percentual etc.). Por
     * isso cada uma passa por parseValorMonetario() antes de entrar na cadeia.
     */
    public function getValorTotalVencedoresAttribute(): float
    {
        $total = $this->vencedores->sum(function ($vencedor) {
            return $vencedor->valor_total;
        });

        if ($total > 0) {
            return $total;
        }

        $fontes = [
            $this->contrato?->homologacao?->valor_total,
            $this->finalizacao?->valor_total,
            $this->finalizacao?->valor_melhor_proposta,
            $this->detalhe?->valor_total,
            $this->detalhe?->valor_estimado,
        ];

        foreach ($fontes as $fonte) {
            $valor = self::parseValorMonetario($fonte);
            if ($valor > 0) {
                return $valor;
            }
        }

        return 0.0;
    }

    /**
     * Converte um valor monetário gravado como string livre (ou já numérico)
     * num float. Cobre os formatos reais encontrados nessas colunas: número
     * limpo em formato BR ("126.474,33"), texto composto com a parte em
     * extenso entre parênteses ("R$ 120.000,00 (cento e vinte mil reais),
     * a serem pagos em..." — pega só o primeiro valor, que é sempre o
     * total), milhar sem decimal ("8.500"), e o typo "15.000.00" (ponto no
     * lugar da vírgula decimal). Retorna 0.0 se não achar nada numérico
     * (ex.: contrato por percentual, "4,5% do valor do crédito").
     */
    public static function parseValorMonetario(mixed $valor): float
    {
        if (is_null($valor)) {
            return 0.0;
        }

        if (is_numeric($valor)) {
            return (float) $valor;
        }

        $str = (string) $valor;
        $str = preg_replace('/\(.*$/u', '', $str);
        $str = trim(preg_replace('/R\$|\s|\xc2\xa0/u', '', $str));

        if ($str === '' || ! preg_match('/\d/', $str)) {
            return 0.0;
        }

        if (str_contains($str, ',')) {
            // Formato brasileiro: ponto(s) = separador de milhar, última vírgula = decimal.
            $str = str_replace('.', '', $str);
            $str = str_replace(',', '.', $str);
        } elseif (substr_count($str, '.') >= 2) {
            // "15.000.00" (typo: usou ponto no lugar da vírgula decimal) — último ponto é o decimal.
            $pos = strrpos($str, '.');
            $str = str_replace('.', '', substr($str, 0, $pos)).'.'.substr($str, $pos + 1);
        } elseif (preg_match('/^\d{1,3}(\.\d{3})+$/', $str)) {
            // "8.500" sem vírgula nem decimais — ponto é separador de milhar.
            $str = str_replace('.', '', $str);
        }
        // Senão: já é um número simples sem separadores, ou tem formato ambíguo — usa como está.

        return is_numeric($str) ? (float) $str : 0.0;
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
            ->where(function ($query) {
                $query->where('tipo_documento', 'like', '%republicacao%')
                    ->orWhere('tipo_documento', 'like', '%adiado%');
            })
            ->orderBy('gerado_em', 'desc');
    }

    public function cancelamentos()
    {
        return $this->hasMany(Documento::class)
            ->where('tipo_documento', 'like', '%cancelamento%');
    }

    /**
     * Scope para filtrar processos pelo indicador "foi republicado"
     * (independente do status real de andamento). Usa a mesma condição
     * da relação `republicacoes()`.
     */
    public function scopeFoiRepublicado($query, bool $republicado = true)
    {
        if ($republicado) {
            return $query->where(function ($q) {
                $q->whereNotNull('processo_original_id')
                    ->orWhereHas('republicacoes');
            });
        }

        return $query->whereNull('processo_original_id')
            ->whereDoesntHave('republicacoes');
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
     * Verificar se este processo é fruto de uma republicação (duplicação de outro
     * processo) ou já teve o edital republicado no próprio registro. Indicador
     * independente do campo `status`.
     */
    public function getFoiRepublicadoAttribute(): bool
    {
        return $this->processo_original_id !== null
            || $this->republicacoes()->exists();
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

        // A FK etps.processo_id tem nullOnDelete(), então o banco já limpa o
        // vínculo sozinho — mas só a coluna processo_id, não o status. Sem
        // isso, o ETP fica preso em "em_processo" (sem processo nenhum de
        // verdade) e some da lista de ETPs disponíveis pra vincular, já que
        // vincularEtp() só aceita status 'aprovado'. Mesmo reset que
        // ProcessoController::desvincularEtp() já faz manualmente.
        static::deleting(function ($processo) {
            if ($processo->etp) {
                $processo->etp->update([
                    'processo_id' => null,
                    'status' => 'aprovado',
                ]);
            }
        });
    }
}
