<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Persiste a seleção de assinantes que o operador definiu para um documento,
 * ANTES de a rodada ser disparada. Permite gerar PDF múltiplas vezes (para
 * testes/ajustes) sem perder a seleção. O disparo da rodada de assinatura
 * é feito separadamente via botão "Solicitar Assinatura".
 *
 * Chave de unicidade composta: (processo, tipo_documento, homologacao, vencedor)
 * — cobre o caso da Ata por vencedor (`ata_registro_precos_v{id}`) e dos
 * documentos por-homologação.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documento_selecao_assinantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('processo_id')->constrained('processos')->cascadeOnDelete();
            $table->string('tipo_documento', 80);
            $table->foreignId('homologacao_id')->nullable()->constrained('homologacoes')->nullOnDelete();
            $table->foreignId('vencedor_id')->nullable()->constrained('vencedores')->nullOnDelete();

            // 'paralelo' | 'sequencial'
            $table->string('modo', 20)->default('paralelo');
            $table->unsignedSmallInteger('prazo_dias')->default(7);

            // JSON com [{user_id, responsavel, unidade_nome, numero_portaria, data_portaria, ordem}]
            $table->json('assinantes');

            $table->foreignId('atualizado_por_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(
                ['processo_id', 'tipo_documento', 'homologacao_id', 'vencedor_id'],
                'doc_sel_assinantes_unico'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documento_selecao_assinantes');
    }
};
