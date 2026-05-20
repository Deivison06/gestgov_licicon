<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Etp extends Model
{
    protected $table = 'etps';

    protected $fillable = [
        'prefeitura_id',
        'secretaria_id',
        'servidor_responsavel',
        'objeto_licitacao',
        'justificativa_necessidade',
        'modalidade',
        'dotacao_orcamentaria',
        'tipo_contratacao',
        'nome_lote',
        'prazo_entrega',
        'cotacao_path',
        'status',
        'processo_id',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONAMENTOS
    |--------------------------------------------------------------------------
    */

    public function prefeitura()
    {
        return $this->belongsTo(Prefeitura::class);
    }

    public function secretaria()
    {
        return $this->belongsTo(Unidade::class, 'secretaria_id');
    }

    public function itens()
    {
        return $this->belongsToMany(
            EtpItem::class,
            'etp_etp_item',
            'etp_id',
            'etp_item_id'
        )
        ->withPivot(['id', 'unidade', 'quantidade'])
        ->orderByPivot('id', 'asc')
        ->withTimestamps();
    }


    public function processo()
    {
        return $this->belongsTo(Processo::class);
    }
    public function lotes()
    {
        return $this->hasMany(EtpLote::class);
    }

}
