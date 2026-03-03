<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Primeiro, alterar o enum para adicionar 'obras'
        DB::statement("ALTER TABLE etps MODIFY COLUMN tipo_contratacao ENUM('item', 'lote', 'servicos', 'compras', 'obras') DEFAULT NULL");
        
        // Adicionar o campo justificativa_necessidade
        Schema::table('etps', function (Blueprint $table) {
            $table->text('justificativa_necessidade')->nullable()->after('objeto_licitacao');
        });
    }

    public function down(): void
    {
        // Remover o campo justificativa_necessidade
        Schema::table('etps', function (Blueprint $table) {
            $table->dropColumn('justificativa_necessidade');
        });
        
        // Reverter o enum para o estado anterior (remover 'obras')
        DB::statement("ALTER TABLE etps MODIFY COLUMN tipo_contratacao ENUM('item', 'lote', 'servicos', 'compras') DEFAULT NULL");
    }
};