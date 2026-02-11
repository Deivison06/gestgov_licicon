<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Primeiro, adicionar uma coluna temporária para backup
        Schema::table('processos', function (Blueprint $table) {
            $table->string('old_status')->nullable()->after('status');
        });

        // 2. Copiar os valores atuais para a coluna de backup
        DB::statement('UPDATE processos SET old_status = status');

        // 3. Modificar a coluna status com os novos valores
        DB::statement("
            ALTER TABLE processos
            MODIFY COLUMN status ENUM(
                'EM_ANDAMENTO',
                'FINALIZADO',
                'CANCELADO',
                'REPUBLICADO',
                'ADIADO'
            ) DEFAULT 'EM_ANDAMENTO'
        ");

        // 4. Mapear os valores antigos para os novos
        DB::statement("
            UPDATE processos
            SET status = CASE old_status
                WHEN 'RASCUNHO' THEN 'EM_ANDAMENTO'
                WHEN 'EM_INICIO' THEN 'EM_ANDAMENTO'
                WHEN 'EM_FINALIZACAO' THEN 'EM_ANDAMENTO'
                WHEN 'EM_CONTRATO' THEN 'EM_ANDAMENTO'
                WHEN 'FINALIZADO' THEN 'FINALIZADO'
                WHEN 'CANCELADO' THEN 'CANCELADO'
                WHEN 'ADIADO' THEN 'ADIADO'
                WHEN 'REPUBLICADO' THEN 'REPUBLICADO'
                ELSE 'EM_ANDAMENTO'
            END
        ");

        // 5. Remover a coluna temporária
        Schema::table('processos', function (Blueprint $table) {
            $table->dropColumn('old_status');
        });
    }

    public function down(): void
    {
        // 1. Adicionar coluna de backup para rollback
        Schema::table('processos', function (Blueprint $table) {
            $table->string('old_status')->nullable()->after('status');
        });

        // 2. Copiar valores atuais
        DB::statement('UPDATE processos SET old_status = status');

        // 3. Reverter para os valores originais
        DB::statement("
            ALTER TABLE processos
            MODIFY COLUMN status ENUM(
                'RASCUNHO',
                'EM_INICIO',
                'EM_FINALIZACAO',
                'EM_CONTRATO',
                'FINALIZADO',
                'CANCELADO',
                'ADIADO',
                'REPUBLICADO'
            ) DEFAULT 'RASCUNHO'
        ");

        // 4. Mapear de volta (aproximação)
        DB::statement("
            UPDATE processos
            SET status = CASE old_status
                WHEN 'EM_ANDAMENTO' THEN 'EM_INICIO'
                WHEN 'FINALIZADO' THEN 'FINALIZADO'
                WHEN 'CANCELADO' THEN 'CANCELADO'
                WHEN 'ADIADO' THEN 'ADIADO'
                WHEN 'REPUBLICADO' THEN 'REPUBLICADO'
                ELSE 'RASCUNHO'
            END
        ");

        // 5. Remover coluna temporária
        Schema::table('processos', function (Blueprint $table) {
            $table->dropColumn('old_status');
        });
    }
};
