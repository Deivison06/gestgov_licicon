<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Prefeitura;
use App\Models\PcaItem;

class Pca extends Model
{
    use HasFactory;

    protected $table = 'pcas';

    protected $fillable = [
        'prefeitura_id',
        'numero_pca',
        'exercicio',
        'equipe_elaboracao',
        'periodo_elaboracao_inicio',
        'periodo_elaboracao_fim',
        'status',
    ];

    protected $casts = [
        'equipe_elaboracao' => 'array',
        'periodo_elaboracao_inicio' => 'date',
        'periodo_elaboracao_fim' => 'date',
    ];

    public function prefeitura()
    {
        return $this->belongsTo(Prefeitura::class);
    }

    public function itens()
    {
        return $this->hasMany(PcaItem::class);
    }
}
