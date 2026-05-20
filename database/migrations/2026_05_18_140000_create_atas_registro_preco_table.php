<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atas_registro_preco', function (Blueprint $table) {
            $table->id();
            $table->foreignId('processo_id')->constrained('processos')->cascadeOnDelete();
            $table->foreignId('homologacao_id')->constrained('homologacoes')->cascadeOnDelete();
            $table->foreignId('vencedor_id')->constrained('vencedores')->cascadeOnDelete();

            $table->string('numero_ata_registro_precos')->nullable();
            $table->string('cargo_controle_interno')->nullable();
            $table->date('data_selecionada')->nullable();
            $table->json('assinantes')->nullable();
            $table->string('caminho')->nullable();
            $table->timestamp('gerado_em')->nullable();

            $table->timestamps();

            // 1 Ata por vencedor dentro de cada homologação.
            $table->unique(['homologacao_id', 'vencedor_id'], 'atas_homologacao_vencedor_unique');
            $table->index('processo_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atas_registro_preco');
    }
};
