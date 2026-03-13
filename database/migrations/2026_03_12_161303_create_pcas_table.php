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
        Schema::create('pcas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prefeitura_id')->constrained('prefeituras')->onDelete('cascade');
            $table->string('numero_pca')->nullable();
            $table->string('exercicio');
            $table->json('equipe_elaboracao')->nullable();
            $table->date('periodo_elaboracao_inicio')->nullable();
            $table->date('periodo_elaboracao_fim')->nullable();
            $table->enum('status', ['pendente', 'em_analise', 'aprovado', 'recusado'])->default('pendente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pcas');
    }
};
