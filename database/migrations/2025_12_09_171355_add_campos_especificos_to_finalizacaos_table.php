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
            $table->boolean('merenda_escolar')
                  ->default(false);
            $table->boolean('veiculos')
                  ->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('finalizacaos', function (Blueprint $table) {
            $table->dropColumn('merenda_escolar');
            $table->dropColumn('veiculos');
        });
    }
};
