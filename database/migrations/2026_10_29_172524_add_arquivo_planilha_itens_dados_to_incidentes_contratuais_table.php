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
            // Conteúdo bruto da planilha importada (linhas/colunas tal como enviadas),
            // usado para Contratos Manuais/Externos — que não têm lotes cadastrados
            // para casar/calcular contra a planilha. Exibido tal qual no quadro de
            // itens do Termo Aditivo (ver IncidenteContratualService::importarPlanilhaBruta()).
            $table->json('arquivo_planilha_itens_dados')->nullable()->after('arquivo_planilha_itens_nome');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incidentes_contratuais', function (Blueprint $table) {
            $table->dropColumn('arquivo_planilha_itens_dados');
        });
    }
};
