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
        Schema::table('processo_detalhes', function (Blueprint $table) {
            // Motivos do fracasso do certame anterior (múltipla escolha) —
            // usado na AUTORIZAÇÃO DE CONTRATAÇÃO (dispensa oriunda de fracassado).
            if (! Schema::hasColumn('processo_detalhes', 'motivos_fracasso')) {
                $table->json('motivos_fracasso')->nullable()->after('anexo_pdf_ata_sessao_fracassada');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('processo_detalhes', function (Blueprint $table) {
            if (Schema::hasColumn('processo_detalhes', 'motivos_fracasso')) {
                $table->dropColumn('motivos_fracasso');
            }
        });
    }
};
