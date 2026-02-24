<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('etps', function (Blueprint $table) {
            $table->string('modalidade')->after('objeto_licitacao');
            $table->string('dotacao_orcamentaria')->nullable()->after('modalidade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('etps', function (Blueprint $table) {
            $table->dropColumn(['modalidade', 'dotacao_orcamentaria']);
        });
    }
};
