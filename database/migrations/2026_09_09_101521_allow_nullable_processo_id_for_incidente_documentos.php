<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Documentos de Aditivo (IncidenteContratual) de um Contrato Manual/Externo não têm
 * Processo associado — mas `documentos.processo_id` e
 * `documento_selecao_assinantes.processo_id` eram NOT NULL, o que impedia registrar
 * a data/assinantes/geração desses documentos para esse tipo de contrato.
 *
 * Aproveita para corrigir também a UNIQUE KEY de `documento_selecao_assinantes`, que
 * não incluía `incidente_id` — dois incidentes do mesmo processo/contrato gerando o
 * mesmo tipo_documento (ex.: "solicitacao_aditivo") colidiam nela.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('documentos', 'processo_id')
            && ! $this->columnIsNullable('documentos', 'processo_id')) {
            Schema::table('documentos', function (Blueprint $table) {
                $table->dropForeign('documentos_processo_id_foreign');
            });
            DB::statement('ALTER TABLE documentos MODIFY processo_id BIGINT UNSIGNED NULL');
            Schema::table('documentos', function (Blueprint $table) {
                $table->foreign('processo_id')->references('id')->on('processos')->onDelete('cascade');
            });
        }

        if (Schema::hasColumn('documento_selecao_assinantes', 'processo_id')
            && ! $this->columnIsNullable('documento_selecao_assinantes', 'processo_id')) {
            Schema::table('documento_selecao_assinantes', function (Blueprint $table) {
                // A FK usa 'doc_sel_assinantes_unico' como índice de suporte (processo_id
                // é a 1ª coluna) — precisa ser removida antes do índice, senão o MySQL
                // recusa o DROP INDEX (erro 1553).
                $table->dropForeign('documento_selecao_assinantes_processo_id_foreign');
                $table->dropUnique('doc_sel_assinantes_unico');
            });
            DB::statement('ALTER TABLE documento_selecao_assinantes MODIFY processo_id BIGINT UNSIGNED NULL');
            Schema::table('documento_selecao_assinantes', function (Blueprint $table) {
                $table->foreign('processo_id')->references('id')->on('processos')->onDelete('cascade');
                $table->unique(
                    ['processo_id', 'tipo_documento', 'homologacao_id', 'vencedor_id', 'incidente_id'],
                    'doc_sel_assinantes_unico'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::table('documento_selecao_assinantes', function (Blueprint $table) {
            $table->dropUnique('doc_sel_assinantes_unico');
        });
        Schema::table('documento_selecao_assinantes', function (Blueprint $table) {
            $table->unique(
                ['processo_id', 'tipo_documento', 'homologacao_id', 'vencedor_id'],
                'doc_sel_assinantes_unico'
            );
        });

        // Não reverte NOT NULL (poderia haver linhas com processo_id nulo já gravadas).
    }

    private function columnIsNullable(string $table, string $column): bool
    {
        $row = DB::selectOne(
            'SELECT IS_NULLABLE FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$table, $column]
        );

        return $row && $row->IS_NULLABLE === 'YES';
    }
};
