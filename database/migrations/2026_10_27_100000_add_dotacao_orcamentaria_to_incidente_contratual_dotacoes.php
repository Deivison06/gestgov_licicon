<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A tela de dotação do aditivo passou a ter um único campo "Dotação
 * Orçamentária" (texto livre) no lugar dos 4 campos separados. Os 4 campos
 * antigos continuam na tabela só para registros legados.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incidente_contratual_dotacoes', function (Blueprint $table) {
            $table->string('dotacao_orcamentaria', 500)->nullable()->after('incidente_contratual_id');
        });
    }

    public function down(): void
    {
        Schema::table('incidente_contratual_dotacoes', function (Blueprint $table) {
            $table->dropColumn('dotacao_orcamentaria');
        });
    }
};
