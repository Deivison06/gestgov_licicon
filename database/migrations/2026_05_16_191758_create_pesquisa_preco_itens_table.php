<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesquisa_preco_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('processo_id')->constrained('processos')->cascadeOnDelete();

            // Identificação do item no PNCP
            $table->string('numero_item', 20)->nullable();
            $table->string('ano_compra', 4);
            $table->string('sequencial_compra');
            $table->string('orgao_cnpj', 18);
            $table->string('orgao_nome');
            $table->char('uf', 2)->nullable();
            $table->string('municipio')->nullable();
            $table->date('data_publicacao')->nullable();
            $table->string('modalidade')->nullable();

            // Dados do item
            $table->text('descricao');
            $table->decimal('quantidade', 12, 4)->nullable();
            $table->string('unidade_medida', 30)->nullable();
            $table->decimal('valor_unitario', 14, 4);
            $table->string('tipo_valor', 12)->default('estimado'); // 'homologado' | 'estimado'
            $table->decimal('valor_total', 14, 4)->nullable();     // quantidade * valor_unitario

            // Fornecedor (disponível apenas em itens homologados)
            $table->string('fornecedor_nome')->nullable();
            $table->string('fornecedor_cnpj', 18)->nullable();

            // Referência externa
            $table->string('link_pncp')->nullable();

            $table->timestamps();

            $table->index('processo_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesquisa_preco_itens');
    }
};
