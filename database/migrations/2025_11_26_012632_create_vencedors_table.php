<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vencedores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('processo_id')->constrained('processos')->onDelete('cascade');
            $table->string('razao_social');
            $table->string('cnpj', 20);
            $table->string('representante');
            $table->string('cpf', 14);
            $table->integer('ordem')->default(0); // Ordem de exibição dos vencedores
            $table->timestamps();

            // Index para melhor performance
            $table->index('processo_id');
            $table->index('cnpj');
        });
    }

    public function down()
    {
        Schema::dropIfExists('vencedores');
    }
};
