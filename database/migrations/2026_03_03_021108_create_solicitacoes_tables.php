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
        Schema::create('solicitacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('prefeitura_id')->constrained()->onDelete('cascade');
            
            $table->enum('tipo', ['correcao', 'reclamacao', 'outros']);
            $table->string('assunto');
            $table->enum('status', ['aberta', 'aguardando_resposta', 'recebida', 'finalizada'])->default('aberta');
            $table->timestamps();
        });

        Schema::create('solicitacao_mensagens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitacao_id')->constrained('solicitacoes')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('mensagem');
            $table->string('anexo_path')->nullable();
            $table->timestamp('lida_em')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitacao_mensagens');
        Schema::dropIfExists('solicitacoes');
    }
};
