<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prefeitura extends Model
{
    use HasFactory;

    protected $table = 'prefeituras';

    protected $fillable = [
        'nome',
        'cnpj',
        'endereco',
        'cidade',
        'cor',
        'telefone',
        'email',
        'autoridade_competente',
        'capa',
        'timbre',
        'capa_edital'
    ];

    public function unidades(): HasMany
    {
        return $this->hasMany(Unidade::class);
    }

    public function processos(): HasMany
    {
        return $this->hasMany(Processo::class);
    }
}
