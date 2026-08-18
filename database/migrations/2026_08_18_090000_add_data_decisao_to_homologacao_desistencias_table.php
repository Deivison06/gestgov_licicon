<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('homologacao_desistencias', function (Blueprint $table) {
            // Data da decisão administrativa (data do Termo) — DIFERENTE da data de
            // solicitação de assinatura/convocação. Ex.: convocação em 15/07, prazo de
            // 3 dias úteis vencido, decisão registrada em 18/07 — não necessariamente
            // a data em que o PDF foi gerado no sistema.
            $table->date('data_decisao')->nullable()->after('data_solicitacao_assinatura');
        });
    }

    public function down(): void
    {
        Schema::table('homologacao_desistencias', function (Blueprint $table) {
            $table->dropColumn('data_decisao');
        });
    }
};
