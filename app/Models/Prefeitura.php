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
     * Nome do órgão contratante para os documentos. Vira o nome da própria
     * Prefeitura quando a unidade é a do prefeito — cadastrada como "Prefeito
     * Municipal", renomeada para o genérico "Secretaria Municipal" (sem dizer
     * qual secretaria) ou com cargo de prefeito.
     */
    public function nomeOrgaoContratante(?string $nomeUnidade, ?string $cargo = null): string
    {
        $nome = trim(preg_replace('/\s+/u', ' ', (string) $nomeUnidade));

        if ($nome === ''
            || preg_match('/\bprefeit(o|a|ura)\b/iu', $nome)
            || preg_match('/^secretaria municipal$/iu', $nome)
            || preg_match('/\bprefeit(o|a)\b/iu', (string) $cargo)) {
            return $this->nome;
        }

        return $nome;
    }

    /**
     * Representante no preâmbulo do Termo Aditivo: "pelo Prefeito Municipal,
     * senhor(a) Fulano". Usa o cargo gravado na seleção; sem ele (seleções
     * antigas), o nome da unidade faz as vezes de cargo e Secretarias viram
     * "Secretário(a) ...". Sem assinante selecionado, usa o Prefeito.
     */
    public function representanteContratante(?array $assinante): string
    {
        $nome = ($assinante['responsavel'] ?? null) ?: ($this->autoridade_competente ?? 'Prefeito');
        // Cargo gravado na seleção (coluna unidades.cargo) tem prioridade; seleções
        // antigas, sem cargo, derivam do nome da unidade como antes.
        $cargo = trim((string) ($assinante['cargo'] ?? ''));
        if ($cargo === '') {
            $cargo = trim($assinante['unidade_nome'] ?? '') ?: 'Prefeito Municipal';
            $cargo = preg_replace('/^secretaria\b/iu', 'Secretário(a)', $cargo);
        }
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
