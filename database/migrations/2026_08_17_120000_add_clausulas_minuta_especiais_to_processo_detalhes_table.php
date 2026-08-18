<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Campo usado apenas no modelo de Minuta do Contrato de COMPRAS: permite marcar
     * blocos de cláusula condicionais (merenda escolar / aquisição de veículos) que
     * devem ser incluídos no PDF gerado automaticamente.
     */
    public function up(): void
    {
        Schema::table('processo_detalhes', function (Blueprint $table) {
            $table->json('clausulas_minuta_especiais')->nullable()->after('anexo_pdf_minuta_contrato');
        });
    }

    public function down(): void
    {
        Schema::table('processo_detalhes', function (Blueprint $table) {
            $table->dropColumn('clausulas_minuta_especiais');
        });
    }
};
