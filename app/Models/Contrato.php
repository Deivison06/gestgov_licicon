<?php

namespace App\Models;

use App\Models\Concerns\FiltravelPorVencimento;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contrato extends Model
{
    use FiltravelPorVencimento;

    protected $fillable = [
        'processo_id',
        'homologacao_id',
        'numero_sequencial',
        'numero_contrato',
        'data_assinatura_contrato',
        'numero_extrato',
        'comarca',
        'fonte_recurso',
        'subcontratacao',
        // Snapshot do contratante (sobrescreve os dados globais do processo só neste contrato)
        'dados_contratante',
        // Arquivo PDF próprio deste contrato
        'caminho',
        'data_documento',
        'gerado_em',
        'concluido',
        'data_finalizacao',
    ];

    protected $casts = [
        'data_assinatura_contrato' => 'datetime',
        'data_documento' => 'date',
        'gerado_em' => 'datetime',
        'dados_contratante' => 'array',
        'concluido' => 'boolean',
        'data_finalizacao' => 'date',
        // Sim/Não (1/0). Sem cast, "0" (string) quebrava a comparação estrita
        // `=== 0` da cláusula de subcontratação. null permanece null (=> "permitida").
        'subcontratacao' => 'integer',
    ];

    public function processo(): BelongsTo
    {
        return $this->belongsTo(Processo::class, 'processo_id');
    }

    public function homologacao(): BelongsTo
    {
        return $this->belongsTo(Homologacao::class);
    }

    public function fiscalizacoes()
    {
        return $this->morphMany(Fiscalizacao::class, 'fiscalizavel');
    }

    public function getSituacaoAttribute()
    {
        if ($this->concluido) {
            return 'CONCLUÍDO';
        }

        if (!$this->data_finalizacao) {
            // Attempt to calculate based on details if not explicitly set?
            // Since we just added the field, maybe some legacy logic? Or we just return PENDENTE
            return 'PENDENTE';
        }

        $hoje = now()->startOfDay();
        $dataFinal = \Carbon\Carbon::parse($this->data_finalizacao)->startOfDay();

        if ($dataFinal->greaterThanOrEqualTo($hoje)) {
            return 'VIGENTE';
        } else {
            return 'VENCIDO';
        }
    }
}
