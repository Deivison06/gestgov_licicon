<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('processos', function (Blueprint $table) {
            $table->foreignId('finalizacao_iniciada_por_id')
                ->nullable()
                ->after('planejamento_fim_recurso')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('finalizacao_iniciada_em')
                ->nullable()
                ->after('finalizacao_iniciada_por_id');
        });
    }

    public function down(): void
    {
        Schema::table('processos', function (Blueprint $table) {
            $table->dropForeign(['finalizacao_iniciada_por_id']);
            $table->dropColumn(['finalizacao_iniciada_por_id', 'finalizacao_iniciada_em']);
        });
    }
};
