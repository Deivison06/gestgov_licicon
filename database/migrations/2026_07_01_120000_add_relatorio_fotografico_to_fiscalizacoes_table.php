<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fiscalizacoes', function (Blueprint $table) {
            $table->string('relatorio_fotografico')->nullable()->after('metodologia_fiscalizacao');
        });
    }

    public function down(): void
    {
        Schema::table('fiscalizacoes', function (Blueprint $table) {
            $table->dropColumn('relatorio_fotografico');
        });
    }
};
