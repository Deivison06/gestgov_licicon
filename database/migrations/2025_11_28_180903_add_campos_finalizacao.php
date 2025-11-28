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
        Schema::table('finalizacaos', function (Blueprint $table) {

            // Dados do órgão responsável
            $table->string('orgao_responsavel')->nullable();
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
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('finalizacaos', function (Blueprint $table) {

            // Remover campos caso seja necessário fazer rollback
            $table->dropColumn([
                'orgao_responsavel',
                'cnpj',
                'endereco',
                'responsavel',
                'cpf_responsavel',
                'razao_social',
                'cnpj_empresa_vencedora',
                'endereco_empresa_vencedora',
                'representante_legal_empresa',
                'cpf_representante',
            ]);
        });
    }
};
