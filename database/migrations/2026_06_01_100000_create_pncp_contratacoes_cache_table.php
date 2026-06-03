<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pncp_contratacoes_cache', function (Blueprint $table) {
            $table->id();
            $table->string('cnpj', 20);
            $table->unsignedSmallInteger('ano_compra');
            $table->unsignedInteger('sequencial_compra');
            $table->unsignedTinyInteger('modalidade_codigo')->nullable();
            $table->string('modalidade_nome', 120)->nullable();
            $table->text('objeto')->nullable();
            $table->char('uf', 2)->nullable();
            $table->string('municipio', 120)->nullable();
            $table->string('orgao_nome', 250)->nullable();
            $table->unsignedTinyInteger('codigo_situacao_compra')->nullable();
            $table->string('situacao_nome', 120)->nullable();
            $table->decimal('valor_total_estimado', 15, 2)->nullable();
            $table->decimal('valor_total_homologado', 15, 2)->nullable();
            $table->date('data_publicacao_pncp')->nullable();
            $table->date('data_resultado_compra')->nullable();
            $table->timestamp('synced_at')->useCurrent();
            $table->timestamps();

            $table->unique(['cnpj', 'ano_compra', 'sequencial_compra'], 'uq_pncp_contratacao');
            $table->index('uf');
            $table->index('modalidade_codigo');
            $table->index('codigo_situacao_compra');
            $table->index('data_publicacao_pncp');
            $table->fullText('objeto', 'ft_pncp_objeto');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pncp_contratacoes_cache');
    }
};
