<?php

namespace App\Models;

use App\Enums\SituacaoOcorrenciaEnum;
use App\Enums\StatusOcorrenciaEnum;
use App\Scopes\PrefeituraScope;
use Illuminate\Database\Eloquent\Model;

class Ocorrencia extends Model
{
    protected $table = 'ocorrencias';

    /**
     * Meios de comprovação do fato (checkbox de marcação do documento do
     * tribunal). Fonte única usada pelo formulário, pelo show e pelos PDFs.
     */
    public const TIPOS_COMPROVACAO = [
        'fotografias' => 'Fotografias',
        'videos' => 'Vídeos',
        'relatorio' => 'Relatório',
        'email' => 'E-mail',
        'mensagem' => 'Mensagem',
        'outros' => 'Outros',
    ];

    protected $fillable = [
        'prefeitura_id',
        'fiscalizavel_id',
        'fiscalizavel_type',
        'numero_ocorrencia',
        'data_ocorrencia',
        'local',
        'descricao_fato',
        'obrigacao_descumprida',
        'prazo_resposta',
        'tipo_comprovacao',
        'tipo_comprovacao_outro',
        'situacao',
        'status',
        'assinantes',
        'resposta_registrada_em',
        'correcao_descricao',
        'correcao_data',
        'correcao_elementos_comprobatorios',
        'notificacao_numero',
        'notificacao_expedida_em',
        'user_id',
    ];

    protected $casts = [
        'data_ocorrencia' => 'date',
        'resposta_registrada_em' => 'date',
        'correcao_data' => 'date',
        'notificacao_expedida_em' => 'date',
        'tipo_comprovacao' => 'array',
        'assinantes' => 'array',
        'status' => StatusOcorrenciaEnum::class,
        'situacao' => SituacaoOcorrenciaEnum::class,
    ];

    protected static function booted()
    {
        static::addGlobalScope(new PrefeituraScope);
    }

    // =========================================
    // Relacionamentos
    // =========================================

    /**
     * Contrato vinculado (polimórfico: Contrato ou ContratoManual)
     */
    public function fiscalizavel()
    {
        return $this->morphTo();
    }

    public function prefeitura()
    {
        return $this->belongsTo(Prefeitura::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function anexos()
    {
        return $this->hasMany(OcorrenciaAnexo::class);
    }

    public function anexosFato()
    {
        return $this->anexos()->where('categoria', 'fato');
    }

    public function anexosResposta()
    {
        return $this->anexos()->where('categoria', 'resposta');
    }

    public function anexosCorrecao()
    {
        return $this->anexos()->where('categoria', 'correcao');
    }

    // =========================================
    // Accessors
    // =========================================

    public function getStatusTextoAttribute(): string
    {
        return $this->status?->getDisplayName() ?? '—';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return $this->status?->getBadgeClass() ?? 'bg-gray-100 text-gray-800';
    }

    public function getSituacaoTextoAttribute(): string
    {
        return $this->situacao?->getDisplayName() ?? '—';
    }

    public function getSituacaoBadgeClassAttribute(): string
    {
        return $this->situacao?->getBadgeClass() ?? 'bg-gray-100 text-gray-800';
    }
}
