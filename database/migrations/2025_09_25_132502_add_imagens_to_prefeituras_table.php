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
        // Idempotente — pula colunas que já existem (caso a create já as inclua).
        // Necessário para que `RefreshDatabase` em testes não quebre.
        Schema::table('prefeituras', function (Blueprint $table) {
            if (!Schema::hasColumn('prefeituras', 'capa')) {
                $table->string('capa')->nullable()->after('autoridade_competente');
            }
            if (!Schema::hasColumn('prefeituras', 'timbre')) {
                $table->string('timbre')->nullable()->after('capa');
            }
        });
    }

    public function down(): void
    {
        Schema::table('prefeituras', function (Blueprint $table) {
            $colunas = [];
            if (Schema::hasColumn('prefeituras', 'capa'))   $colunas[] = 'capa';
            if (Schema::hasColumn('prefeituras', 'timbre')) $colunas[] = 'timbre';
            if (!empty($colunas)) {
                $table->dropColumn($colunas);
            }
        });
    }

};
