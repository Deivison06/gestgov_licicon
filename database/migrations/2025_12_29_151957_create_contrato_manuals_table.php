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
        Schema::create('contratos_manuais', function (Blueprint $table) {
            $table->id();

            // Relacionamentos Principais
            $table->foreignId('empresa_id')->constrained('empresa_contratos')->onDelete('cascade');
            $table->foreignId('prefeitura_id')->constrained('prefeituras')->onDelete('cascade');
            $table->foreignId('unidade_id')->constrained('unidades')->onDelete('cascade');

            // Dados de Identificação (Vindos do antigo Processo)
            $table->string('numero_processo');
            $table->string('numero_contrato')->nullable();
            $table->string('modalidade')->nullable();

            // Definição do Fluxo (Novo)
            $table->enum('tipo_contrato', ['Compras', 'Serviço'])->default('Compras');

            $table->text('objeto');

            // Financeiro e Vigência (Vindos do antigo Contrato)
            $table->decimal('valor_total', 15, 2)->default(0);
            $table->date('data_assinatura')->nullable();
            $table->date('data_inicio')->nullable();
            $table->date('data_finalizacao')->nullable();

            // Arquivos e Controle
            $table->string('arquivo_contrato')->nullable();
            $table->string('situacao_manual')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contratos_manuais ');
    }
};
