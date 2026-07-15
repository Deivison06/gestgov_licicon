<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Assinantes (servidores da prefeitura) do relatório de fiscalização — impressos
 * para assinatura física (sem assinatura eletrônica). Armazenados como JSON:
 * [{ "nome": "...", "cargo": "...", "unidade": "..." }, ...]
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fiscalizacoes', function (Blueprint $table) {
            $table->json('assinantes')->nullable()->after('relatorio_fotografico');
        });
    }

    public function down(): void
    {
        Schema::table('fiscalizacoes', function (Blueprint $table) {
            $table->dropColumn('assinantes');
        });
    }
};
