<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('incidentes_contratuais', function (Blueprint $table) {
            // Quando true, os itens aditivados vieram de uma planilha importada
            // (item a item) — atualizarAditivo() não deve mais recalcular os
            // itens pelo percentual uniforme enquanto este valor for true.
            $table->boolean('itens_origem_planilha')->default(false)->after('percentual_supressao');
            $table->string('arquivo_planilha_itens_path')->nullable()->after('itens_origem_planilha');
            $table->string('arquivo_planilha_itens_nome')->nullable()->after('arquivo_planilha_itens_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incidentes_contratuais', function (Blueprint $table) {
            $table->dropColumn([
                'itens_origem_planilha',
                'arquivo_planilha_itens_path',
                'arquivo_planilha_itens_nome',
            ]);
        });
    }
};
