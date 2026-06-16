<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A "prova" da assinatura. Não pode ser deletada — é a evidência do ato.
 * Cadeia de hash:
 *   hash_proprio = sha256(hash_documento + hash_cadeia_anterior + assinante_id + microtime)
 * Detecta inserção/remoção de assinaturas fora de ordem.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assinaturas_digitais', function (Blueprint $table) {
            $table->id();

            // Uma assinatura por solicitação.
            $table->foreignId('solicitacao_assinatura_id')
                ->unique()
                ->constrained('solicitacoes_assinatura')
                ->restrictOnDelete();

            // Redundante para queries, mas evita JOIN em consultas frequentes.
            $table->foreignId('documento_versao_id')
                ->constrained('documento_versoes')
                ->restrictOnDelete();

            $table->foreignId('assinante_user_id')
                ->constrained('users')
                ->restrictOnDelete();

            // Snapshot do hash do PDF no momento da assinatura — se PDF mudar, quebra.
            $table->char('hash_documento_no_momento', 64);

            // Cadeia: hash da assinatura anterior nessa mesma versão (ou null se for a primeira).
            $table->char('hash_cadeia_anterior', 64)->nullable();

            // Hash desta própria assinatura — entra na cadeia da próxima.
            $table->char('hash_proprio', 64);

            // Código mostrado no PDF/QR/página pública (estilo SEI: 10-20 chars).
            $table->char('codigo_verificador', 20)->unique();

            $table->string('ip', 45); // suporta IPv6
            $table->text('user_agent');

            $table->timestamp('assinado_em');

            // Snapshot dos dados PII do assinante no momento (nome, cargo, matrícula, portaria).
            // Se o user for editado/desativado depois, a assinatura preserva o estado real.
            $table->json('metadados')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assinaturas_digitais');
    }
};
