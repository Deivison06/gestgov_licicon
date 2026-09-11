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
        Schema::table('documento_versoes', function (Blueprint $table) {
            if (!Schema::hasColumn('documento_versoes', 'codigo_verificador')) {
                // Código público do documento (mesmo formato/espaço de AssinaturaDigital.codigo_verificador)
                // — gerado já no rascunho, reaproveitado pelo selo de assinatura quando o documento é assinado.
                $table->char('codigo_verificador', 20)->nullable()->unique()->after('hash_sha256');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documento_versoes', function (Blueprint $table) {
            if (Schema::hasColumn('documento_versoes', 'codigo_verificador')) {
                $table->dropUnique(['codigo_verificador']);
                $table->dropColumn('codigo_verificador');
            }
        });
    }
};
