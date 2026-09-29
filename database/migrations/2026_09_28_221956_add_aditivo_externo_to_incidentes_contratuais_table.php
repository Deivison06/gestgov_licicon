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
        Schema::table('incidentes_contratuais', function (Blueprint $table) {
            if (!Schema::hasColumn('incidentes_contratuais', 'origem')) {
                // 'interno' (fluxo atual, gera documentos) ou 'externo' (feito fora do
                // sistema, registrado com anexo — sem geração de documentos).
                $table->string('origem')->default('interno')->after('categoria');
            }
            if (!Schema::hasColumn('incidentes_contratuais', 'data_aditivo')) {
                $table->date('data_aditivo')->nullable()->after('origem');
            }
            if (!Schema::hasColumn('incidentes_contratuais', 'arquivo_aditivo_externo_path')) {
                $table->string('arquivo_aditivo_externo_path')->nullable()->after('arquivo_orcamento_obra_path');
            }
            if (!Schema::hasColumn('incidentes_contratuais', 'data_finalizacao_base')) {
                // Snapshot do vencimento do contrato no momento em que este aditivo foi
                // criado — permite aplicar/reverter o efeito de prazo sem depender de
                // somar todos os aditivos toda vez (idempotente a re-salvamentos).
                $table->date('data_finalizacao_base')->nullable()->after('data_aditivo');
            }
            if (!Schema::hasColumn('incidentes_contratuais', 'valor_total_base')) {
                // Idem, para o valor_total (só existe coluna própria em ContratoManual —
                // Contrato do Sistema deriva valor dos lotes, ver incidente_contratual_itens).
                $table->decimal('valor_total_base', 15, 2)->nullable()->after('data_finalizacao_base');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incidentes_contratuais', function (Blueprint $table) {
            foreach (['origem', 'data_aditivo', 'arquivo_aditivo_externo_path', 'data_finalizacao_base', 'valor_total_base'] as $coluna) {
                if (Schema::hasColumn('incidentes_contratuais', $coluna)) {
                    $table->dropColumn($coluna);
                }
            }
        });
    }
};
