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

    /**
     * Representante no preâmbulo do Termo Aditivo: "pelo Prefeito Municipal,
     * senhor(a) Fulano". A unidade não tem campo de cargo — o nome dela já é o
     * cargo na prática ("Prefeito Municipal", "Pregoeiro"), exceto Secretarias,
     * cadastradas pelo nome do órgão, que viram "Secretário(a) ...".
     * Sem assinante selecionado, usa o Prefeito.
     */
    public function representanteContratante(?array $assinante): string
    {
        $nome = ($assinante['responsavel'] ?? null) ?: ($this->autoridade_competente ?? 'Prefeito');
        $cargo = trim($assinante['unidade_nome'] ?? '') ?: 'Prefeito Municipal';

        $cargo = preg_replace('/^secretaria\b/iu', 'Secretário(a)', $cargo);
        $primeiraPalavra = mb_strtolower(strtok($cargo, ' ') ?: '');

        $artigo = match (true) {
            str_ends_with($primeiraPalavra, '(a)') => 'pelo(a)',
            str_ends_with($primeiraPalavra, 'a') => 'pela',
            default => 'pelo',
        };

        return "{$artigo} {$cargo}, senhor(a) ".\App\Helpers\NomeProprio::formatar($nome);
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
