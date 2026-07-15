<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fotos do Relatório Fotográfico da fiscalização (múltiplas imagens por fiscalização).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiscalizacao_fotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiscalizacao_id')->constrained('fiscalizacoes')->cascadeOnDelete();
            $table->string('caminho');
            $table->string('legenda')->nullable();
            $table->unsignedInteger('ordem')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiscalizacao_fotos');
    }
};
