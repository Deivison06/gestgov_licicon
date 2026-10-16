<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesquisa_preco_memoria_itens', function (Blueprint $table) {
            $table->string('lote')->nullable()->after('etp_item_id');
            $table->unsignedInteger('numero_item')->nullable()->after('lote');
        });
    }

    public function down(): void
    {
        Schema::table('pesquisa_preco_memoria_itens', function (Blueprint $table) {
            $table->dropColumn(['lote', 'numero_item']);
        });
    }
};
