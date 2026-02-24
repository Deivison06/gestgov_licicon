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

            $table->string('orgao_responsavel')->nullable()->after('id');
            $table->string('cnpj', 18)->nullable()->after('orgao_responsavel');

            $table->string('endereco')->nullable()->after('cnpj');
            $table->string('responsavel')->nullable()->after('endereco');
            $table->string('cpf_responsavel', 14)->nullable()->after('responsavel');

            $table->string('endereco_imovel')->nullable()->after('cpf_responsavel');

            $table->date('prazo_inicio_prestacao_servico')->nullable()->after('endereco_imovel');
            $table->date('prazo_final_prestacao_servico')->nullable()->after('prazo_inicio_prestacao_servico');

            $table->decimal('valor_mensal', 15, 2)->nullable()->after('prazo_final_prestacao_servico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('processo_detalhes', function (Blueprint $table) {

            $table->dropColumn([
                'orgao_responsavel',
                'cnpj',
                'endereco',
                'responsavel',
                'cpf_responsavel',
                'endereco_imovel',
                'prazo_inicio_prestacao_servico',
                'prazo_final_prestacao_servico',
            ]);
        });
    }
};
