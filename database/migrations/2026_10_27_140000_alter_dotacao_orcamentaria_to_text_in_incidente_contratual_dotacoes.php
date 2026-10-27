<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Cliente pediu mais de 1000 caracteres no campo único de dotação (500 não
 * comportava dotações com várias fontes). SQL puro: doctrine/dbal não está instalado.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE incidente_contratual_dotacoes MODIFY dotacao_orcamentaria TEXT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE incidente_contratual_dotacoes MODIFY dotacao_orcamentaria VARCHAR(500) NULL');
    }
};
