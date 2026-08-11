<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('atas_registro_preco', function (Blueprint $table) {
            // Marca a Ata como invalidada por desistência/abandono da empresa vencedora
            // sem apagar o registro nem o arquivo — a Ata original continua acessível.
            $table->timestamp('invalidada_em')->nullable()->after('gerado_em');
        });
    }

    public function down(): void
    {
        Schema::table('atas_registro_preco', function (Blueprint $table) {
            $table->dropColumn('invalidada_em');
        });
    }
};
