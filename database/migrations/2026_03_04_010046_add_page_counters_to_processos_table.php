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
        Schema::table('processos', function (Blueprint $table) {
            $table->integer('contTotalPagePhase1')->nullable()->after('contTotalPage');
            $table->integer('contTotalPagePhase2')->nullable()->after('contTotalPagePhase1');
            $table->integer('contTotalPagePhase3')->nullable()->after('contTotalPagePhase2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('processos', function (Blueprint $table) {
            $table->dropColumn(['contTotalPagePhase1', 'contTotalPagePhase2', 'contTotalPagePhase3']);
        });
    }
};
