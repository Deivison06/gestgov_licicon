<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Alteração do enum só faz sentido em MySQL.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE etps MODIFY COLUMN tipo_contratacao ENUM('item', 'lote', 'servicos', 'compras', 'obras') DEFAULT NULL");
        }

        // Adicionar o campo justificativa_necessidade (cross-driver)
        Schema::table('etps', function (Blueprint $table) {
            $table->text('justificativa_necessidade')->nullable()->after('objeto_licitacao');
        });
    }

    public function down(): void
    {
        Schema::table('etps', function (Blueprint $table) {
            $table->dropColumn('justificativa_necessidade');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE etps MODIFY COLUMN tipo_contratacao ENUM('item', 'lote', 'servicos', 'compras') DEFAULT NULL");
        }
    }
};