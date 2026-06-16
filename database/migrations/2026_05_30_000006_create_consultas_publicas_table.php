<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registra cada consulta na página pública /autenticar/{codigo}.
 * Útil para auditoria, detecção de abuso e analytics (quantas vezes um documento foi consultado).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultas_publicas', function (Blueprint $table) {
            $table->id();

            $table->char('codigo_verificador', 20);

            // Null quando o código não bate em nenhuma assinatura (tentativa inválida).
            $table->foreignId('documento_versao_id')
                ->nullable()
                ->constrained('documento_versoes')
                ->nullOnDelete();

            $table->string('ip', 45);
            $table->text('user_agent');

            $table->boolean('sucesso')->default(false);

            $table->timestamp('consultado_em')->useCurrent();

            $table->index('codigo_verificador', 'consultas_publicas_codigo_idx');
            $table->index(['codigo_verificador', 'consultado_em'], 'consultas_publicas_codigo_data_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultas_publicas');
    }
};
