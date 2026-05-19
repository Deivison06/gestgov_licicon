<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // O nome do índice unique gerado automaticamente em create_contratos
        // segue o padrão {tabela}_{coluna}_unique.
        Schema::table('contratos', function (Blueprint $table) {
            $table->dropUnique('contratos_processo_id_unique');
        });

        Schema::table('contratos', function (Blueprint $table) {
            $table->foreignId('homologacao_id')
                ->nullable()
                ->after('processo_id')
                ->constrained('homologacoes')
                ->nullOnDelete();

            // 1 contrato por homologação dentro do mesmo processo;
            // contratos legados (homologacao_id NULL) coexistem porque o MySQL
            // trata NULL como distinto em índices unique.
            $table->unique(['processo_id', 'homologacao_id'], 'contratos_processo_homologacao_unique');
        });
    }

    public function down(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            $table->dropUnique('contratos_processo_homologacao_unique');
            $table->dropForeign(['homologacao_id']);
            $table->dropColumn('homologacao_id');
        });

        Schema::table('contratos', function (Blueprint $table) {
            $table->unique('processo_id', 'contratos_processo_id_unique');
        });
    }
};
