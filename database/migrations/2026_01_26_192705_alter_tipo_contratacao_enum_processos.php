<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        DB::statement("
            ALTER TABLE processos
            MODIFY tipo_contratacao
            ENUM('1','2','3','4','5','6')
            COMMENT '1-LOTE, 2-ITEM, 3-TECNICO, 4-ARTISTICO, 5-IMOVEL, 6-FORNECEDOR'
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
            MODIFY tipo_contratacao
            ENUM('1','2')
            COMMENT '1-LOTE, 2-ITEM'
            NULL
        ");
    }
};
