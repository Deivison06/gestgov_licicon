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
        Schema::create('incidente_contratual_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incidente_contratual_id')->constrained('incidentes_contratuais')->onDelete('cascade');
            $table->foreignId('lote_contratado_id')->constrained('lote_contratados')->onDelete('cascade');
            $table->decimal('quantidade_aditivada', 15, 4);
            $table->decimal('valor_unitario', 15, 4);
            $table->decimal('valor_total_aditivado', 15, 4);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidente_contratual_itens');
    }
};
