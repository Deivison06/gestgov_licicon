<?php

namespace App\Models;

use App\Enums\ModalidadeEnum;
use App\Scopes\PrefeituraScope;
use Illuminate\Database\Eloquent\Model;

class ContratoManual extends Model
{
    protected $table = "contratos_manuais";
    protected $fillable = [
        'empresa_id',       // Vínculo com a empresa (Novo)
        'prefeitura_id',
        'unidade_id',    // Vínculo com o contratante
        'numero_processo',
        'numero_contrato',
        'modalidade',
        'tipo_contrato',    // 'Fornecimento' ou 'Serviço'
        'objeto',           // Nome unificado (antes era objeto_contrato)
        'valor_total',
        'data_assinatura',
        'data_inicio',
        'data_finalizacao',
        'arquivo_contrato',
        'situacao_manual'
    ];

    protected $casts = [
        'modalidade' => ModalidadeEnum::class,
        'data_assinatura' => 'date',
        'data_inicio' => 'date',
        'data_finalizacao' => 'date',
        'valor_total' => 'decimal:2',
    ];

    public function empresa()
    {
        return $this->belongsTo(EmpresaContrato::class, 'empresa_id');
    }

    public function secretaria()
    {
        return $this->belongsTo(Unidade::class, 'unidade_id');
    }

    public function prefeitura()
    {
        return $this->belongsTo(Prefeitura::class);
    }


    protected static function booted()
    {
        static::addGlobalScope(new PrefeituraScope());
    }

    // No modelo ContratoManual, adicione este método de acesso
    public function getSituacaoAttribute()
    {
        // Lógica para determinar a situação com base na data de finalização
        if (!$this->data_finalizacao) {
            return 'PENDENTE';
        }
        
        $hoje = now();
        $dataFinal = \Carbon\Carbon::parse($this->data_finalizacao);
        
        if ($dataFinal->greaterThan($hoje)) {
            return 'VIGENTE';
        } else {
            return 'VENCIDO';
        }
    }

}
