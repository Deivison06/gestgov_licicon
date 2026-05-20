<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EtpLote extends Model
{
    protected $fillable = ['etp_id', 'nome'];

    public function etp()
    {
        return $this->belongsTo(Etp::class);
    }

    public function itens()
    {
        return $this->belongsToMany(
            EtpItem::class,
            'etp_lote_item',
            'etp_lote_id',
            'etp_item_id'
        )->withPivot(['id', 'unidade','quantidade'])
         ->orderByPivot('id', 'asc')
         ->withTimestamps();
    }
}
