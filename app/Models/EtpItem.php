<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EtpItem extends Model
{
    protected $table = 'etp_itens';

    protected $fillable = [
        'descricao_item',
        'unidade_medida',
    ];

    public function etps()
    {
        return $this->belongsToMany(
            Etp::class,
            'etp_etp_item',
            'etp_item_id',
            'etp_id'
        )
        ->withPivot(['unidade', 'quantidade'])
        ->withTimestamps();
    }

}
