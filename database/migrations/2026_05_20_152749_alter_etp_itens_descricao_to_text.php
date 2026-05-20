<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('etp_itens', function (Blueprint $table) {
            $table->text('descricao_item')->change();
        });
    }

    public function down(): void
    {
        Schema::table('etp_itens', function (Blueprint $table) {
            $table->string('descricao_item')->change();
        });
    }
};
