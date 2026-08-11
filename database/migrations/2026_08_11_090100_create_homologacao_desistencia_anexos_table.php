<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homologacao_desistencia_anexos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homologacao_desistencia_id')
                ->constrained('homologacao_desistencias', indexName: 'homolog_desist_anexos_desistencia_id_foreign')
                ->cascadeOnDelete();

            $table->string('caminho');
            $table->string('nome_original')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homologacao_desistencia_anexos');
    }
};
