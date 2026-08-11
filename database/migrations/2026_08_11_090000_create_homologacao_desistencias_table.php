<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homologacao_desistencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homologacao_id')->constrained('homologacoes')->cascadeOnDelete();
            $table->foreignId('vencedor_id')->constrained('vencedores')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->date('data_solicitacao_assinatura');
            $table->text('observacao')->nullable();

            // Cópia dos lotes (item, descrição, quantidade, valores) ANTES de zerar o
            // saldo, para auditoria — nada é apenas apagado.
            $table->json('quantidade_lotes_snapshot')->nullable();

            $table->string('caminho_pdf')->nullable();
            $table->timestamp('gerado_em')->nullable();

            $table->timestamps();

            // Uma desistência por vencedor dentro de cada homologação.
            $table->unique(['homologacao_id', 'vencedor_id'], 'homolog_desist_homologacao_vencedor_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homologacao_desistencias');
    }
};
