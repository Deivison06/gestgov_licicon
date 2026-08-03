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
        Schema::table('etps', function (Blueprint $table) {
            $table->enum('organizacao_itens', ['item', 'lote'])->nullable()->after('tipo_contratacao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('etps', function (Blueprint $table) {
            $table->dropColumn('organizacao_itens');
        });
    }
};
