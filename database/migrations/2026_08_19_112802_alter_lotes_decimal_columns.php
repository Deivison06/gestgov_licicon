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
        Schema::table('lotes', function (Blueprint $table) {
            $table->decimal('quantidade', 15, 4)->change();
            $table->decimal('vl_unit', 15, 4)->change();
        });

        Schema::table('lote_contratados', function (Blueprint $table) {
            $table->decimal('quantidade_disponivel_pos_contrato', 15, 4)->nullable()->change();
            $table->decimal('quantidade_contratada', 15, 4)->change();
            $table->decimal('valor_unitario', 15, 4)->change();
        });

        Schema::table('estoque_lotes', function (Blueprint $table) {
            $table->decimal('quantidade_disponivel', 15, 4)->change();
            $table->decimal('quantidade_utilizada', 15, 4)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estoque_lotes', function (Blueprint $table) {
            $table->decimal('quantidade_utilizada', 15, 2)->change();
            $table->decimal('quantidade_disponivel', 15, 2)->change();
        });

        Schema::table('lote_contratados', function (Blueprint $table) {
            $table->decimal('valor_unitario', 15, 2)->change();
            $table->decimal('quantidade_contratada', 15, 2)->change();
            $table->decimal('quantidade_disponivel_pos_contrato', 15, 2)->nullable()->change();
        });

        Schema::table('lotes', function (Blueprint $table) {
            $table->decimal('vl_unit', 15, 2)->change();
            $table->decimal('quantidade', 15, 2)->change();
        });
    }
};
