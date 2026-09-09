<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Torna IncidenteContratual (Aditivo) polimórfico, para poder pertencer tanto a um
 * Contrato do Sistema (App\Models\Contrato) quanto a um Contrato Manual/Externo
 * (App\Models\ContratoManual) — mesmo padrão morphTo já usado por Fiscalizacao/Ocorrencia
 * via 'fiscalizavel'. contrato_id (FK fixa para `contratos`) vira contratavel_id +
 * contratavel_type.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('incidentes_contratuais', 'contratavel_type')) {
            return; // já migrado (idempotente)
        }

        Schema::table('incidentes_contratuais', function (Blueprint $table) {
            $table->dropForeign('incidentes_contratuais_contrato_id_foreign');
        });

        // Rename via SQL puro — doctrine/dbal (exigido por ->renameColumn()/->change())
        // não está instalado neste projeto.
        DB::statement('ALTER TABLE incidentes_contratuais CHANGE contrato_id contratavel_id BIGINT UNSIGNED NOT NULL');

        Schema::table('incidentes_contratuais', function (Blueprint $table) {
            $table->string('contratavel_type')->nullable()->after('contratavel_id');
        });

        // Todos os registros pré-existentes são Contratos do Sistema.
        DB::table('incidentes_contratuais')
            ->whereNull('contratavel_type')
            ->update(['contratavel_type' => \App\Models\Contrato::class]);

        DB::statement('ALTER TABLE incidentes_contratuais MODIFY contratavel_type VARCHAR(255) NOT NULL');

        Schema::table('incidentes_contratuais', function (Blueprint $table) {
            $table->index(['contratavel_id', 'contratavel_type'], 'incidentes_contratuais_contratavel_index');
        });
    }

    public function down(): void
    {
        Schema::table('incidentes_contratuais', function (Blueprint $table) {
            $table->dropIndex('incidentes_contratuais_contratavel_index');
            $table->dropColumn('contratavel_type');
        });

        DB::statement('ALTER TABLE incidentes_contratuais CHANGE contratavel_id contrato_id BIGINT UNSIGNED NOT NULL');

        Schema::table('incidentes_contratuais', function (Blueprint $table) {
            $table->foreign('contrato_id')->references('id')->on('contratos')->onDelete('cascade');
        });
    }
};
