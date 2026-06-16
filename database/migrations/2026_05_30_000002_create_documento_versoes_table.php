<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Snapshot imutável de um documento PDF.
 * Cada vez que o usuário (re)gera um documento, cria-se uma nova versão.
 * Versões antigas ficam consultáveis publicamente (auditoria).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documento_versoes', function (Blueprint $table) {
            $table->id();

            // Polimórfico: pode apontar para Documento, Contrato, AtaRegistroPreco,
            // ContratoManual, Etp — qualquer model que represente um doc do sistema.
            $table->string('documentavel_type');
            $table->unsignedBigInteger('documentavel_id');

            // Autoincremental por (documentavel_type, documentavel_id) — controlado em código,
            // não pelo MySQL, porque o auto_increment global não respeita o escopo.
            $table->unsignedInteger('versao');

            // PDF "rascunho" (antes das assinaturas).
            $table->string('caminho_pdf');
            $table->char('hash_sha256', 64);

            $table->foreignId('gerado_por_user_id')->constrained('users')->restrictOnDelete();
            $table->timestamp('gerado_em');

            // Preenchidos quando a última assinatura é registrada.
            $table->timestamp('assinaturas_consolidadas_em')->nullable();
            $table->string('caminho_pdf_assinado')->nullable();
            $table->char('hash_pdf_assinado', 64)->nullable();

            $table->timestamps();

            $table->unique(
                ['documentavel_type', 'documentavel_id', 'versao'],
                'documento_versoes_unique'
            );
            $table->index(['documentavel_type', 'documentavel_id'], 'documento_versoes_documentavel_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documento_versoes');
    }
};
