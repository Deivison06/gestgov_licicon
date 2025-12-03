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
        Schema::table('finalizacaos', function (Blueprint $table) {
            $table->string('numero_ata_registro_precos')->nullable();
            $table->string('cargo_controle_interno')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('finalizacaos', function (Blueprint $table) {
            $table->dropColumn('numero_ata_registro_precos', 'cargo_controle_interno');
        });
    }
};
