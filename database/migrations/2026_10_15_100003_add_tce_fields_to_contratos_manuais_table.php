<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contratos_manuais', function (Blueprint $table) {
            $table->boolean('continuo')->nullable()->after('tipo_contrato');
            $table->string('fonte_recurso')->nullable();
            $table->string('dotacao_unidade_orcamentaria')->nullable();
            $table->string('dotacao_classificacao_funcional_programatica')->nullable();
            $table->string('dotacao_elemento_despesa')->nullable();

            // Base estável para o % acumulado de acréscimo/supressão exigido pelo
            // TCU. valor_total é mutado de forma absoluta a cada aditivo (ver
            // IncidenteContratualService::aplicarEfeitosNoContrato), então o
            // "valor original" se perde — esta coluna é fixada uma única vez
            // (ver migration de backfill) e nunca mais mutada.
            $table->decimal('valor_total_original', 15, 2)->nullable()->after('valor_total');
        });
    }

    public function down(): void
    {
        Schema::table('contratos_manuais', function (Blueprint $table) {
            $table->dropColumn([
                'continuo',
                'fonte_recurso',
                'dotacao_unidade_orcamentaria',
                'dotacao_classificacao_funcional_programatica',
                'dotacao_elemento_despesa',
                'valor_total_original',
            ]);
        });
    }
};
