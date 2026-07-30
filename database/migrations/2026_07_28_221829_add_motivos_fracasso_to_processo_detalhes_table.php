<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('processo_detalhes', function (Blueprint $table) {
            // Campos da dispensa oriunda de certame fracassado — referenciados em
            // ProcessoDetalhe::$fillable, ProcessoRequest e ProcessoService, mas
            // nunca haviam sido migrados.
            if (! Schema::hasColumn('processo_detalhes', 'is_oriundo_fracassado')) {
                $table->boolean('is_oriundo_fracassado')->nullable()->after('portal');
            }
            if (! Schema::hasColumn('processo_detalhes', 'processo_fracassado_id')) {
                $table->foreignId('processo_fracassado_id')->nullable()->after('is_oriundo_fracassado')->constrained('processos')->nullOnDelete();
            }
            if (! Schema::hasColumn('processo_detalhes', 'motivo_fracasso')) {
                $table->string('motivo_fracasso')->nullable()->after('processo_fracassado_id');
            }
            if (! Schema::hasColumn('processo_detalhes', 'anexo_pdf_ata_sessao_fracassada')) {
                $table->string('anexo_pdf_ata_sessao_fracassada')->nullable()->after('motivo_fracasso');
            }

            // Motivos do fracasso do certame anterior (múltipla escolha) —
            // usado na AUTORIZAÇÃO DE CONTRATAÇÃO (dispensa oriunda de fracassado).
            if (! Schema::hasColumn('processo_detalhes', 'motivos_fracasso')) {
                $table->json('motivos_fracasso')->nullable()->after('anexo_pdf_ata_sessao_fracassada');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('processo_detalhes', function (Blueprint $table) {
            if (Schema::hasColumn('processo_detalhes', 'motivos_fracasso')) {
                $table->dropColumn('motivos_fracasso');
            }
            if (Schema::hasColumn('processo_detalhes', 'anexo_pdf_ata_sessao_fracassada')) {
                $table->dropColumn('anexo_pdf_ata_sessao_fracassada');
            }
            if (Schema::hasColumn('processo_detalhes', 'motivo_fracasso')) {
                $table->dropColumn('motivo_fracasso');
            }
            if (Schema::hasColumn('processo_detalhes', 'processo_fracassado_id')) {
                $table->dropConstrainedForeignId('processo_fracassado_id');
            }
            if (Schema::hasColumn('processo_detalhes', 'is_oriundo_fracassado')) {
                $table->dropColumn('is_oriundo_fracassado');
            }
        });
    }
};
