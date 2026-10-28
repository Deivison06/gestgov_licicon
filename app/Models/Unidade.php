<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Unidade extends Model
{
    use HasFactory;

    protected $table = 'unidades';

    protected $fillable = [
        'prefeitura_id',
        'nome',
        'servidor_responsavel',
        'cargo',
        'numero_portaria',
        'data_portaria'
    ];

    public function prefeitura(): BelongsTo
    {
        return $this->belongsTo(Prefeitura::class);
    }
    public function contrato()
    {
        return $this->hasMany(ContratoManual::class);
    }

    /**
     * Cargo de quem assina por esta unidade. `nome` é o órgão (vai em "Secretaria /
     * Órgão Contratante"); `cargo` é o que sai embaixo da assinatura. Sem cargo
     * preenchido, cai no nome — o comportamento de antes desta coluna existir.
     */
    public function cargoParaAssinatura(): string
    {
        return filled($this->cargo) ? $this->cargo : (string) $this->nome;
    }

    /**
     * Texto da opção nos selects de assinante: "Cargo — Unidade". Quando o cargo
     * está vazio ou é o próprio nome, mostra só o nome, sem repetir.
     */
    public function rotuloAssinante(): string
    {
        $cargo = trim((string) $this->cargo);
        $nome = trim((string) $this->nome);

        return $cargo !== '' && mb_strtolower($cargo) !== mb_strtolower($nome) ? "{$cargo} — {$nome}" : $nome;
    }

    /**
     * Unidades que servem como órgão (Secretaria / Órgão Contratante). Esconde as
     * que são só um cargo (cargo igual ao nome: "Pregoeiro", "Agente de
     * Contratação"...), mas sempre mantém a já vinculada ($manterId), para
     * nenhum registro existente perder a opção selecionada.
     */
    public function scopeOrgaos($query, $manterId = null)
    {
        return $query->where(function ($q) use ($manterId) {
            $q->whereNull('cargo')
                ->orWhere('cargo', '')
                ->orWhereRaw('TRIM(cargo) <> TRIM(nome)');

            if ($manterId) {
                $q->orWhere('id', $manterId);
            }
        });
    }

    // Método para buscar o servidor responsável pelo nome da unidade
    public static function getServidorByNome($nomeUnidade)
    {
        return static::where('nome', $nomeUnidade)->value('servidor_responsavel');
    }
}
