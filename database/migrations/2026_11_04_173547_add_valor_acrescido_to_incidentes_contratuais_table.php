<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * O form de Aditivo Externo (atualizarExterno) sempre exigiu e usou
 * "valor_acrescido" pra calcular o novo valor_total do contrato, mas a
 * coluna nunca existiu — o valor digitado nunca era salvo no próprio
 * incidente, só aplicado uma vez no contrato. Resultado: reabrir um
 * aditivo externo de valor sempre mostrava o campo vazio.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incidentes_contratuais', function (Blueprint $table) {
            $table->decimal('valor_acrescido', 15, 2)->nullable()->after('percentual_valor');
        });
    }

    public function down(): void
    {
        Schema::table('incidentes_contratuais', function (Blueprint $table) {
            $table->dropColumn('valor_acrescido');
        });
    }
};
