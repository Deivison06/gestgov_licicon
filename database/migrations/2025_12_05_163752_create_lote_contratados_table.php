<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lote_contratados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contrato_id')->nullable()->constrained('contratos')->onDelete('cascade');
            $table->foreignId('lote_id')->constrained('lotes')->onDelete('cascade');
            $table->foreignId('vencedor_id')->constrained('vencedores')->onDelete('cascade');
            $table->foreignId('processo_id')->constrained('processos')->onDelete('cascade');
            $table->decimal('quantidade_disponivel_pos_contrato', 15, 2)->nullable();
            $table->decimal('quantidade_contratada', 15, 2);
            $table->decimal('valor_unitario', 15, 2);
            $table->decimal('valor_total', 15, 2);
            $table->string('status')->default('PENDENTE'); // PENDENTE, CONTRATADO, FINALIZADO
            $table->text('observacao')->nullable();
            $table->timestamps();

            $table->index(['lote_id', 'vencedor_id']);
            $table->index('status');
        });

        // Atualizar lote_contratados para focar apenas no contrato atual
        Schema::table('lote_contratados', function (Blueprint $table) {
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lote_contratados');
    }
};
