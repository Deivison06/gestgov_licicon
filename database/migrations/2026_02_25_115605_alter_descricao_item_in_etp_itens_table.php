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
        Schema::table('etp_itens', function (Blueprint $table) {
            // Muda de VARCHAR(255) para TEXT (que suporta até 65.535 caracteres)
            $table->text('descricao_item')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('etp_itens', function (Blueprint $table) {
            // Reverte para VARCHAR(255)
            $table->string('descricao_item', 255)->change();
        });
    }
};
