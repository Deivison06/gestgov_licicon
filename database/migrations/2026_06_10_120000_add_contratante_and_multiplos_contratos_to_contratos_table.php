<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Múltiplos contratos por processo (um por secretaria): remove a unicidade
        // (processo, homologação) e passa a identificar cada contrato pelo seu id +
        // numero_sequencial. Contratos legados são preservados.
        Schema::table('contratos', function (Blueprint $table) {
            $table->dropUnique('contratos_processo_homologacao_unique');
        });

        Schema::table('contratos', function (Blueprint $table) {
            // Numeração "Contrato 1, 2, 3..." por processo.
            $table->unsignedInteger('numero_sequencial')->nullable()->after('homologacao_id');

            // Snapshot dos dados do contratante daquele contrato específico
            // (orgao_responsavel, cargo_responsavel, cnpj, endereco, responsavel,
            // cpf_responsavel, razao_social). Sobrescreve os dados globais do processo
            // apenas para este contrato.
            $table->json('dados_contratante')->nullable()->after('subcontratacao');

            // Arquivo PDF próprio de cada contrato (desacoplado da tabela `documentos`,
            // que continua guardando o "último" contrato da homologação para a montagem
            // do processo completo).
            $table->string('caminho')->nullable()->after('dados_contratante');
            $table->date('data_documento')->nullable()->after('caminho');
            $table->timestamp('gerado_em')->nullable()->after('data_documento');
        });

        $this->backfill();
    }

    /**
     * Preenche numero_sequencial por processo e tenta herdar o caminho do PDF já
     * registrado em `documentos` (para que contratos legados apareçam com download
     * na nova listagem).
     */
    private function backfill(): void
    {
        $processoIds = DB::table('contratos')->distinct()->pluck('processo_id');

        foreach ($processoIds as $processoId) {
            $contratos = DB::table('contratos')
                ->where('processo_id', $processoId)
                ->orderBy('id')
                ->get(['id', 'homologacao_id']);

            $seq = 1;
            foreach ($contratos as $contrato) {
                $documento = DB::table('documentos')
                    ->where('processo_id', $processoId)
                    ->where('tipo_documento', 'contrato')
                    ->when(
                        $contrato->homologacao_id !== null,
                        fn ($q) => $q->where('homologacao_id', $contrato->homologacao_id),
                        fn ($q) => $q->whereNull('homologacao_id')
                    )
                    ->first(['caminho', 'data_selecionada', 'gerado_em']);

                DB::table('contratos')->where('id', $contrato->id)->update([
                    'numero_sequencial' => $seq,
                    'caminho' => $documento->caminho ?? null,
                    'data_documento' => $documento->data_selecionada ?? null,
                    'gerado_em' => $documento->gerado_em ?? null,
                ]);

                $seq++;
            }
        }
    }

    public function down(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            $table->dropColumn([
                'numero_sequencial',
                'dados_contratante',
                'caminho',
                'data_documento',
                'gerado_em',
            ]);
        });

        Schema::table('contratos', function (Blueprint $table) {
            $table->unique(['processo_id', 'homologacao_id'], 'contratos_processo_homologacao_unique');
        });
    }
};
