<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('processo_detalhes', function (Blueprint $table) {
            $table->string('tipo_relatorio_analise_mercado')->nullable()->default('tce')->after('painel_preco_tce');
            $table->json('fornecedor_local_precos')->nullable()->after('tipo_relatorio_analise_mercado');
        });
    }

    public function down(): void
    {
        Schema::table('processo_detalhes', function (Blueprint $table) {
            $table->dropColumn(['tipo_relatorio_analise_mercado', 'fornecedor_local_precos']);
        });
    }
};
