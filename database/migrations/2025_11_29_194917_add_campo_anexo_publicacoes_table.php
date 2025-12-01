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
        Schema::table('finalizacaos', function (Blueprint $table) {
            $table->string('anexo_publicacoes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('finalizacaos', function (Blueprint $table) {
            $table->dropColumn('anexo_publicacoes');
        });
    }
};
