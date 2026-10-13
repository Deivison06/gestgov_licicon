<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesquisaPrecoMemoriaItem extends Model
{
    protected $table = 'pesquisa_preco_memoria_itens';

    protected $fillable = [
        'processo_id',
        'etp_item_id',
        'ordem',
        'titulo',
        'valor_adotado',
        'fontes',
    ];

    protected $casts = [
        'ordem' => 'integer',
        'valor_adotado' => 'float',
        'fontes' => 'array',
    ];

    public function processo(): BelongsTo
    {
        return $this->belongsTo(Processo::class);
    }

    public function etpItem(): BelongsTo
    {
        return $this->belongsTo(EtpItem::class, 'etp_item_id');
    }
}
