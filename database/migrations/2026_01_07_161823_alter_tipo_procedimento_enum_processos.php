<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Sintaxe específica de MySQL. Pula em outros drivers (ex.: sqlite usado em testes).
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("
            ALTER TABLE processos
            MODIFY tipo_procedimento
            ENUM('1','2','3')
            COMMENT '1-SERVIÇOS, 2-COMPRAS, 3-OBRA'
            NULL
        ");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("
            ALTER TABLE processos
            MODIFY tipo_procedimento
            ENUM('1','2')
            COMMENT '1-SERVIÇOS, 2-COMPRAS'
            NULL
        ");
    }
};
