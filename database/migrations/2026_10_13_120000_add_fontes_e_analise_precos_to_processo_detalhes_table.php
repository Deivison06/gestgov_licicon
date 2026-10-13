<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('processo_detalhes', function (Blueprint $table) {
            $table->text('fontes_consultadas')->nullable()->after('tipo_relatorio_analise_mercado');
            $table->text('analise_precos_coletados')->nullable()->after('fontes_consultadas');
        });
    }

    public function down(): void
    {
        Schema::table('processo_detalhes', function (Blueprint $table) {
            $table->dropColumn(['fontes_consultadas', 'analise_precos_coletados']);
        });
    }
};
