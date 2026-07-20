<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE etps MODIFY COLUMN status ENUM('pendente', 'em_analise', 'aprovado', 'recusado', 'em_processo', 'concluido') NOT NULL DEFAULT 'pendente'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE etps MODIFY COLUMN status ENUM('pendente', 'em_analise', 'aprovado', 'recusado', 'em_processo') NOT NULL DEFAULT 'pendente'");
    }
};
