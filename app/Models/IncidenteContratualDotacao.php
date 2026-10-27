<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Snapshot estruturado da dotação orçamentária informada no ato "Informação
 * de Disponibilidade Orçamentária" de um IncidenteContratual (aditivo).
 * Congelado no momento em que o documento é gerado — não é recalculado se o
 * cadastro de dotação do Contrato/ContratoManual mudar depois.
 */
class IncidenteContratualDotacao extends Model
{
    protected $table = 'incidente_contratual_dotacoes';

    protected $fillable = [
        'incidente_contratual_id',
        'dotacao_orcamentaria',
        'unidade_orcamentaria',
        'classificacao_funcional_programatica',
        'elemento_despesa',
        'fonte_recurso',
        'valor',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
    ];

    /**
     * Texto único da dotação. Registros legados (4 campos separados) são
     * concatenados para continuar aparecendo.
     */
    public function textoDotacao(): string
    {
        if (filled($this->dotacao_orcamentaria)) {
            return $this->dotacao_orcamentaria;
        }

        return collect([
            $this->unidade_orcamentaria,
            $this->classificacao_funcional_programatica,
            $this->elemento_despesa,
            $this->fonte_recurso,
        ])->filter()->implode(' / ');
    }

    public function incidenteContratual(): BelongsTo
    {
        return $this->belongsTo(IncidenteContratual::class, 'incidente_contratual_id');
    }
}
