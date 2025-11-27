<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('lotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vencedor_id')->constrained('vencedores')->onDelete('cascade');
            $table->string('lote')->nullable(); // Apenas para tipo LOTE
            $table->string('status')->nullable();
            $table->string('item');
            $table->text('descricao');
            $table->string('unidade');
            $table->string('marca')->nullable();
            $table->string('modelo')->nullable();
            $table->decimal('quantidade', 15, 2);
            $table->decimal('vl_unit', 15, 2);
            $table->decimal('vl_total', 15, 2);
            $table->integer('ordem')->default(0); // Ordem de exibição
            $table->timestamps();

            // Index para melhor performance
            $table->index('vencedor_id');
            $table->index('lote');
            $table->index('item');
        });
    }

    public function down()
    {
        Schema::dropIfExists('lotes');
    }
};
