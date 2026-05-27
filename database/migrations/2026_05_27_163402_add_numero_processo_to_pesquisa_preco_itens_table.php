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
        Schema::table('pesquisa_preco_itens', function (Blueprint $table) {
            $table->string('numero_processo')->nullable()->after('sequencial_compra');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesquisa_preco_itens', function (Blueprint $table) {
            $table->dropColumn('numero_processo');
        });
    }
};
