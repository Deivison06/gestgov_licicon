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
        Schema::table('fiscalizacoes', function (Blueprint $table) {
            // Checklist de verificação inicial (Compras/Serviços) — {chave: bool}
            $table->json('checklist_fiscalizacao')->nullable()->after('metodologia_fiscalizacao');
            // Seção "Ocorrências" do novo relatório (Compras/Serviços)
            $table->boolean('houve_ocorrencia')->nullable()->after('irregularidade_observada');
            $table->text('providencias_adotadas')->nullable()->after('houve_ocorrencia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fiscalizacoes', function (Blueprint $table) {
            $table->dropColumn(['checklist_fiscalizacao', 'houve_ocorrencia', 'providencias_adotadas']);
        });
    }
};
