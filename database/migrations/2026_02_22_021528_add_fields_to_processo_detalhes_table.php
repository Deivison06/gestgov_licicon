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
        Schema::table('processo_detalhes', function (Blueprint $table) {
            $table->string('razao_social')->nullable();
            $table->string('cnpj_empresa_vencedora')->nullable();
            $table->text('endereco_empresa_vencedora')->nullable();
            $table->string('representante_legal_empresa')->nullable();
            $table->string('cpf_representante')->nullable();
            $table->decimal('valor_total', 15, 2)->nullable();
            $table->text('especificacao_servicos_imovel')->nullable();
            $table->text('razao_escolha_contratado')->nullable();
            $table->text('obrigacoes_contratado_extras')->nullable();
            $table->text('obrigacoes_contratante_extras')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('processo_detalhes', function (Blueprint $table) {
            $table->dropColumn([
                'razao_social',
                'cnpj_empresa_vencedora',
                'endereco_empresa_vencedora',
                'representante_legal_empresa',
                'cpf_representante',
                'valor_total',
                'especificacao_servicos_imovel',
                'razao_escolha_contratado',
                'obrigacoes_contratado_extras',
                'obrigacoes_contratante_extras'
            ]);
        });
    }
};
