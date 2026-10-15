<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            $table->string('tipo_objeto')->nullable()->after('fonte_recurso');
            $table->boolean('continuo')->nullable()->after('tipo_objeto');
            $table->string('dotacao_unidade_orcamentaria')->nullable();
            $table->string('dotacao_classificacao_funcional_programatica')->nullable();
            $table->string('dotacao_elemento_despesa')->nullable();
            // fonte_recurso (já existente) é reaproveitada como 4º campo da dotação.
        });
    }

    public function down(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            $table->dropColumn([
                'tipo_objeto',
                'continuo',
                'dotacao_unidade_orcamentaria',
                'dotacao_classificacao_funcional_programatica',
                'dotacao_elemento_despesa',
            ]);
        });
    }
};
