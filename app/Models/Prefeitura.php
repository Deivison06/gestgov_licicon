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

    /**
     * Nome do órgão contratante para os documentos. As prefeituras costumam
     * cadastrar a unidade "Prefeito Municipal" para o gabinete; quando ela é
     * a selecionada, o contratante é a própria Prefeitura, não o cargo.
     */
    public function nomeOrgaoContratante(?string $nomeUnidade): string
    {
        if (blank($nomeUnidade) || preg_match('/\bprefeit(o|ura)\b/iu', $nomeUnidade)) {
            return $this->nome;
        }

        return $nomeUnidade;
    }

    public function unidades(): HasMany
    {
        return $this->hasMany(Unidade::class);
    }

    public function processos(): HasMany
    {
        return $this->hasMany(Processo::class);
    }
}
