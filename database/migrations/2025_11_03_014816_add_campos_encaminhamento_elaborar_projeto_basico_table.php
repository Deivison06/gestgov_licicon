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
        Schema::table('processo_detalhes', function (Blueprint $table) {
            $table->string('encaminhamento_elaborar_projeto_basico')->nullable()->after('encaminhamento_elaborar_editais');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('processo_detalhes', function (Blueprint $table) {
            $table->dropColumn('encaminhamento_elaborar_projeto_basico');
        });
    }
};
