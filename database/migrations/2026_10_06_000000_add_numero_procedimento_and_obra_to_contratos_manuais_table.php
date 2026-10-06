<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contratos_manuais', function (Blueprint $table) {
            $table->string('numero_procedimento')->nullable()->after('numero_processo');
        });

        DB::statement("ALTER TABLE contratos_manuais MODIFY tipo_contrato ENUM('Compras', 'Serviço', 'Obra') DEFAULT 'Compras'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contratos_manuais', function (Blueprint $table) {
            $table->dropColumn('numero_procedimento');
        });

        DB::statement("ALTER TABLE contratos_manuais MODIFY tipo_contrato ENUM('Compras', 'Serviço') DEFAULT 'Compras'");
    }
};
