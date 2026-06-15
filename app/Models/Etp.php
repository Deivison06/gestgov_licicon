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
        'motivo_recusa',
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

    /**
     * Retorna todos os itens do ETP, seja de forma direta ou através dos lotes.
     * @return \Illuminate\Support\Collection
     */
    public function getAllItensAttribute()
    {
        if ($this->tipo_contratacao === 'lote') {
            return $this->lotes->flatMap->itens;
        }
        return $this->itens;
    }

    /**
     * Transforma os itens ou lotes do ETP para o formato array esperado pelos DocumentServices
     * Formato: ['numero' => x, 'descricao' => y, 'und' => z, 'quantidade' => w]
     */
    public function transformarItensParaFormatoPdf(): array
    {
        $itensFormatados = [];
        $num = 1;

        if ($this->tipo_contratacao === 'lote') {
            foreach ($this->lotes as $lote) {
                foreach ($lote->itens as $item) {
                    $itensFormatados[] = [
                        'numero'     => $num++,
                        'descricao'  => $item->descricao_item,
                        'und'        => $item->pivot->unidade,
                        'quantidade' => (float) $item->pivot->quantidade,
                        'lote'       => $lote->nome,
                    ];
                }
            }
        } else {
            foreach ($this->itens as $item) {
                $itensFormatados[] = [
                    'numero'     => $num++,
                    'descricao'  => $item->descricao_item,
                    'und'        => $item->pivot->unidade,
                    'quantidade' => (float) $item->pivot->quantidade,
                    'lote'       => null,
                ];
            }
        }

        return $itensFormatados;
    }

}
