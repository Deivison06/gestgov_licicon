<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EtpItem extends Model
{
    protected $table = 'etp_itens';

    protected $fillable = [
        'descricao_item',
    ];

    public function etps()
    {
        return $this->belongsToMany(Etp::class, 'etp_etp_item', 'etp_item_id', 'etp_id')->withTimestamps();
    }
}
