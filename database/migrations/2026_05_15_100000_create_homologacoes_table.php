<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homologacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('processo_id')->constrained('processos')->onDelete('cascade');
            $table->unsignedInteger('numero_sequencial');
            $table->string('status', 30)->default('EM_EDICAO');
            $table->date('data_homologacao')->nullable();
            $table->text('observacao')->nullable();

            // Anexos PDF que ficam por homologação
            $table->string('anexo_recurso_contratacoes')->nullable();
            $table->string('anexo_publicacoes')->nullable();

            // Dados do órgão responsável (herdam da homologação anterior)
            $table->string('orgao_responsavel')->nullable();
            $table->string('cargo_responsavel')->nullable();
            $table->string('cnpj')->nullable();
            $table->string('endereco')->nullable();
            $table->string('responsavel')->nullable();
            $table->string('cpf_responsavel')->nullable();

            // Dados da empresa vencedora
            $table->string('razao_social')->nullable();
            $table->string('cnpj_empresa_vencedora')->nullable();
            $table->string('endereco_empresa_vencedora')->nullable();
            $table->string('representante_legal_empresa')->nullable();
            $table->string('cpf_representante')->nullable();
            $table->string('valor_total')->nullable();

            // Específicos
            $table->string('numero_ata_registro_precos')->nullable();
            $table->string('cargo_controle_interno')->nullable();
            $table->boolean('merenda_escolar')->default(false);
            $table->boolean('veiculos')->default(false);
            $table->string('valor_melhor_proposta')->nullable();
            $table->string('empresas_participantes')->nullable();

            $table->timestamps();

            $table->unique(['processo_id', 'numero_sequencial']);
            $table->index('processo_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homologacoes');
    }
};
