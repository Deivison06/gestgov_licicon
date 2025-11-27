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
        Schema::create('finalizacaos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('processo_id')->constrained('processos')->onDelete('cascade');
            $table->string('anexo_atos_sessao')->nullable();
            $table->string('anexo_proposta')->nullable();
            $table->string('anexo_proposta_readequada')->nullable();
            $table->string('anexo_habilitacao')->nullable();
            $table->string('anexo_recurso_contratacoes')->nullable();
            $table->string('anexo_planilha')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finalizacaos');
    }
};
