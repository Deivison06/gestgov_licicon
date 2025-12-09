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
        Schema::create('estoque_lotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lote_id')->constrained('lotes')->onDelete('cascade');
            $table->foreignId('processo_id')->constrained('processos')->onDelete('cascade');
            $table->decimal('quantidade_disponivel', 15, 2);
            $table->decimal('quantidade_utilizada', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['lote_id', 'processo_id']);
            $table->index(['lote_id', 'quantidade_disponivel']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estoque_lotes');
    }
};
