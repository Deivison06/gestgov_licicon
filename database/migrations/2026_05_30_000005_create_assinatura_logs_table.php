<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit trail completo: criada, notificada, visualizada, assinada, recusada,
 * cancelada, expirada, regerada. Append-only (sem updated_at).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assinatura_logs', function (Blueprint $table) {
            $table->id();

            $table->string('acao', 20);

            // Algumas ações podem não ter solicitação (ex.: regerada do documento).
            $table->foreignId('solicitacao_assinatura_id')
                ->nullable()
                ->constrained('solicitacoes_assinatura')
                ->nullOnDelete();

            $table->foreignId('documento_versao_id')
                ->nullable()
                ->constrained('documento_versoes')
                ->nullOnDelete();

            // User pode ser sistema (cron job) — daí nullable.
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->json('metadados')->nullable();

            // Append-only: só created_at, sem updated_at.
            $table->timestamp('created_at')->useCurrent();

            $table->index(['documento_versao_id', 'created_at'], 'assinatura_logs_versao_data_idx');
            $table->index(['acao', 'created_at'], 'assinatura_logs_acao_data_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assinatura_logs');
    }
};
