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
        Schema::table('etps', function (Blueprint $blueprint) {
            $blueprint->text('motivo_recusa')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('etps', function (Blueprint $blueprint) {
            $blueprint->dropColumn('motivo_recusa');
        });
    }
};
