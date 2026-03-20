<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiscalizacoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prefeitura_id');
            $table->morphs('fiscalizavel'); // fiscalizavel_id + fiscalizavel_type + índice

            $table->string('tipo_contrato'); // compras, servicos, obras
            $table->date('data_fiscalizacao');
            $table->string('numero_fiscalizacao');

            // Campos comuns
            $table->text('pontualidade_prazos')->nullable();
            $table->text('regularidade_fiscal_trabalhista')->nullable();
            $table->text('comunicacao_atendimento')->nullable();
            $table->text('irregularidade_observada')->nullable();
            $table->text('recomendacoes_gestor')->nullable();
            $table->text('recomendacoes_empresa')->nullable();
            $table->tinyInteger('conclusao_fiscal'); // 1, 2 ou 3

            // Campos unificados (label varia por tipo na view)
            $table->text('execucao_objeto')->nullable();
            $table->text('qualidade_entregas')->nullable();
            $table->text('observacoes_servidor')->nullable();

            // Campo exclusivo de Serviços e Obras
            $table->text('metodologia_fiscalizacao')->nullable();

            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            // Índices
            $table->index('prefeitura_id');
            $table->foreign('prefeitura_id')->references('id')->on('prefeituras')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiscalizacoes');
    }
};
