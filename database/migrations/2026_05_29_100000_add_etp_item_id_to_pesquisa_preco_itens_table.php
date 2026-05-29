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
        Schema::table('pesquisa_preco_itens', function (Blueprint $table) {
            $table->unsignedBigInteger('etp_item_id')->nullable()->after('processo_id');
            $table->foreign('etp_item_id')->references('id')->on('etp_itens')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesquisa_preco_itens', function (Blueprint $table) {
            $table->dropForeign(['etp_item_id']);
            $table->dropColumn('etp_item_id');
        });
    }
};
