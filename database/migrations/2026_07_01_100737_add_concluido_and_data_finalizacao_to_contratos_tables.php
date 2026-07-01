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
        Schema::table('contratos', function (Blueprint $table) {
            $table->boolean('concluido')->default(false);
            $table->date('data_finalizacao')->nullable();
        });

        Schema::table('contratos_manuais', function (Blueprint $table) {
            $table->boolean('concluido')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            $table->dropColumn(['concluido', 'data_finalizacao']);
        });

        Schema::table('contratos_manuais', function (Blueprint $table) {
            $table->dropColumn(['concluido']);
        });
    }
};
