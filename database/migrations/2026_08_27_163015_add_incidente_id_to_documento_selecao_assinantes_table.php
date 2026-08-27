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
        Schema::table('documento_selecao_assinantes', function (Blueprint $table) {
            $table->unsignedBigInteger('incidente_id')->nullable()->after('tipo_documento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documento_selecao_assinantes', function (Blueprint $table) {
            $table->dropColumn('incidente_id');
        });
    }
};
