<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "Ordem de serviço" para um assinante.
 * Uma SolicitacaoAssinatura é criada para cada (versao, assinante) — em rodada
 * paralela todas têm `ordem=0`; em sequencial, ordens 1..n.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitacoes_assinatura', function (Blueprint $table) {
            $table->id();

            $table->foreignId('documento_versao_id')->constrained('documento_versoes')->cascadeOnDelete();

            // Se o assinante for deletado fisicamente (raro), bloqueia.
            $table->foreignId('assinante_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('solicitado_por_user_id')->constrained('users')->restrictOnDelete();

            // pendente, assinada, recusada, cancelada, expirada
            $table->string('status', 20)->default('pendente');

            // 0 = paralelo; 1..n = ordem sequencial
            $table->unsignedSmallInteger('ordem')->default(0);

            $table->boolean('obrigatoria')->default(true);

            $table->timestamp('solicitado_em');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('processada_em')->nullable();

            // Reservado para fase futura (link via e-mail). MVP não consome.
            $table->char('token_acesso', 64)->unique();

            $table->text('motivo_recusa')->nullable();

            $table->timestamps();

            $table->index(['assinante_user_id', 'status'], 'solicitacoes_assinatura_assinante_status_idx');
            $table->index(['documento_versao_id', 'ordem'], 'solicitacoes_assinatura_versao_ordem_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitacoes_assinatura');
    }
};
