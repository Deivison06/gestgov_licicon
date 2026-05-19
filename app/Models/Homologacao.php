<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Homologacao extends Model
{
    protected $table = 'homologacoes';

    public const STATUS_EM_EDICAO = 'EM_EDICAO';
    public const STATUS_HOMOLOGADA = 'HOMOLOGADA';
    public const STATUS_CANCELADA = 'CANCELADA';

    protected $fillable = [
        'processo_id',
        'numero_sequencial',
        'status',
        'data_homologacao',
        'observacao',
        'anexo_recurso_contratacoes',
        'anexo_publicacoes',
        'orgao_responsavel',
        'cargo_responsavel',
        'cnpj',
        'endereco',
        'responsavel',
        'cpf_responsavel',
        'razao_social',
        'cnpj_empresa_vencedora',
        'endereco_empresa_vencedora',
        'representante_legal_empresa',
        'cpf_representante',
        'valor_total',
        'numero_ata_registro_precos',
        'cargo_controle_interno',
        'merenda_escolar',
        'veiculos',
        'valor_melhor_proposta',
        'empresas_participantes',
    ];

    protected $casts = [
        'data_homologacao' => 'date',
        'merenda_escolar' => 'boolean',
        'veiculos' => 'boolean',
        'numero_sequencial' => 'integer',
    ];

    public function processo(): BelongsTo
    {
        return $this->belongsTo(Processo::class);
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class);
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }

    public function contrato(): HasOne
    {
        return $this->hasOne(Contrato::class);
    }

    public function getValorTotalLotesAttribute(): float
    {
        return (float) $this->lotes->sum('vl_total');
    }
}
