<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('finalizacaos', function (Blueprint $table) {
            $table->string('valor_melhor_proposta', 15, 2)
                ->nullable(); 

            $table->string('empresas_participantes')
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('finalizacaos', function (Blueprint $table) {
            $table->dropColumn([
                'valor_melhor_proposta',
                'empresas_participantes'
            ]);
        });
    }
};
