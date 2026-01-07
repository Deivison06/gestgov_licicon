<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
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
        DB::statement("
            ALTER TABLE processos 
            MODIFY tipo_procedimento 
            ENUM('1','2') 
            COMMENT '1-SERVIÇOS, 2-COMPRAS'
            NULL
        ");
    }
};
