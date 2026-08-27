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
            $table->string('nome_solicitante')->nullable();
            $table->string('cargo_solicitante')->nullable();
            $table->string('nome_parecerista')->nullable();
            $table->string('oab_parecerista')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incidentes_contratuais', function (Blueprint $table) {
            $table->dropColumn(['nome_solicitante', 'cargo_solicitante', 'nome_parecerista', 'oab_parecerista']);
        });
    }
};
