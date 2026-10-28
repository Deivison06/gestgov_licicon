<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Até aqui `unidades.nome` fazia dois papéis: nome do órgão (Secretaria/Órgão
 * Contratante do contrato) e cargo de quem assina (linha embaixo da assinatura).
 * Renomear a unidade para corrigir um papel quebrava o outro. A coluna `cargo`
 * separa o segundo papel.
 *
 * Preenchimento conservador — só onde dá para afirmar o cargo sem mudar o que os
 * documentos já mostram:
 *   - nome que já é um cargo ("Prefeito Municipal", "Pregoeiro"...): cargo = nome;
 *   - unidade-órgão cujo responsável é o próprio prefeito (ex.: a antiga "Prefeito
 *     Municipal" renomeada para "Secretaria Municipal"): cargo = "Prefeito Municipal";
 *   - demais unidades-órgão: cargo fica vazio e os documentos continuam usando o nome.
 */
return new class extends Migration
{
    private const PALAVRAS_ORGAO = [
        'secretaria', 'controladoria', 'comissão', 'comissao', 'departamento', 'setor',
        'procuradoria', 'assessoria', 'gabinete', 'fundo', 'coordenadoria', 'diretoria',
        'divisão', 'divisao', 'fundação', 'fundacao', 'instituto', 'câmara', 'camara',
    ];

    public function up(): void
    {
        Schema::table('unidades', function (Blueprint $table) {
            $table->string('cargo')->nullable()->after('servidor_responsavel');
        });

        $prefeitos = DB::table('prefeituras')->pluck('autoridade_competente', 'id')
            ->map(fn ($nome) => $this->normalizar($nome));

        DB::table('unidades')->orderBy('id')->each(function ($unidade) use ($prefeitos) {
            $nome = trim(preg_replace('/\s+/u', ' ', (string) $unidade->nome));
            $primeiraPalavra = mb_strtolower(strtok($nome, ' ') ?: '');

            $cargo = null;
            if ($nome !== '' && ! in_array($primeiraPalavra, self::PALAVRAS_ORGAO, true)) {
                $cargo = $nome;
            } elseif ($this->normalizar($unidade->servidor_responsavel) !== ''
                && $this->normalizar($unidade->servidor_responsavel) === ($prefeitos[$unidade->prefeitura_id] ?? null)) {
                $cargo = 'Prefeito Municipal';
            }

            if ($cargo !== null) {
                DB::table('unidades')->where('id', $unidade->id)->update(['cargo' => $cargo]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('unidades', function (Blueprint $table) {
            $table->dropColumn('cargo');
        });
    }

    private function normalizar(?string $texto): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', (string) $texto)));
    }
};
