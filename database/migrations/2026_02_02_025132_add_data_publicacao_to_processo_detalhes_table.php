<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('processo_detalhes', function (Blueprint $table) {
            $table->date('data_publicacao')->nullable()->after('processo_id');
        });
    }

    public function down(): void
    {
        Schema::table('processo_detalhes', function (Blueprint $table) {
            $table->dropColumn('data_publicacao');
        });
    }
};
