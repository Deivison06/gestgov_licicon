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
        Schema::create('pca_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pca_id')->constrained('pcas')->onDelete('cascade');
            $table->foreignId('unidade_requisitante_id')->constrained('unidades')->onDelete('cascade');
            $table->string('modalidade')->nullable();
            $table->text('descricao_classe_grupo')->nullable();
            $table->decimal('valor_estimado', 15, 2)->nullable();
            $table->enum('grau_prioridade', ['alto', 'medio', 'baixo'])->nullable();
            $table->date('data_inicio_providencias')->nullable();
            $table->date('data_desejada_conclusao')->nullable();
            $table->boolean('prorrogacao_contrato')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pca_items');
    }
};
