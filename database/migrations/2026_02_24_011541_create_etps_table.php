<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etps', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('prefeitura_id');
            $table->unsignedBigInteger('secretaria_id');

            // ✅ Agora é string
            $table->string('servidor_responsavel');

            $table->text('objeto_licitacao');

            $table->enum('tipo_contratacao', ['item', 'lote']);

            $table->string('nome_lote')->nullable();
            $table->string('prazo_entrega')->nullable();

            $table->string('cotacao_path')->nullable();

            $table->enum('status', [
                'pendente',
                'em_analise',
                'aprovado',
                'recusado',
                'em_processo'
            ])->default('pendente');

            $table->unsignedBigInteger('processo_id')->nullable();

            $table->timestamps();

            // 🔗 Foreign Keys
            $table->foreign('prefeitura_id')
                ->references('id')
                ->on('prefeituras')
                ->onDelete('cascade');

            $table->foreign('secretaria_id')
                ->references('id')
                ->on('unidades')
                ->onDelete('cascade');

            $table->foreign('processo_id')
                ->references('id')
                ->on('processos')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('etps');
    }
};
