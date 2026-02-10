<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('processos', function (Blueprint $table) {
            $table->enum('status', [
                'RASCUNHO',
                'EM_INICIO',
                'EM_FINALIZACAO',
                'EM_CONTRATO',
                'FINALIZADO',
                'CANCELADO',
                'ADIADO',
                'REPUBLICADO'
            ])->default('RASCUNHO')->after('user_id');

            $table->date('data_cancelamento')->nullable()->after('status');
            $table->text('motivo_cancelamento')->nullable()->after('data_cancelamento');
            $table->date('data_adiamento')->nullable()->after('motivo_cancelamento');
            $table->text('justificativa_adiamento')->nullable()->after('data_adiamento');
            $table->foreignId('processo_original_id')->nullable()->constrained('processos')->after('justificativa_adiamento');
        });
    }

    public function down(): void
    {
        Schema::table('processos', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'data_cancelamento',
                'motivo_cancelamento',
                'data_adiamento',
                'justificativa_adiamento',
                'processo_original_id'
            ]);
        });
    }
};
