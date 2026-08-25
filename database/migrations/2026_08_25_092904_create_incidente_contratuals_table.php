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
        Schema::create('incidentes_contratuais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contrato_id')->constrained('contratos')->onDelete('cascade');
            $table->string('tipo');
            $table->string('categoria');
            $table->integer('meses_prorrogacao')->nullable();
            $table->decimal('percentual_valor', 10, 2)->nullable();
            $table->text('justificativa')->nullable();
            $table->string('status')->default('ativo');
            $table->string('arquivo_solicitacao_path')->nullable();
            $table->string('arquivo_orcamento_obra_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidentes_contratuais');
    }
};
