<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Backfill de contratos_manuais.valor_total_original.
 *
 * valor_total_base de cada IncidenteContratual é, por definição, "o valor do
 * contrato ANTES do efeito daquele aditivo" (snapshot já tirado em
 * IncidenteContratualController::store). Logo, o aditivo MAIS ANTIGO de cada
 * ContratoManual guarda exatamente o valor pristino do contrato. Contratos
 * sem nenhum aditivo mantêm valor_total atual = original (nunca foi mutado).
 *
 * Risco conhecido (ver plano): se valor_total foi editado manualmente fora do
 * fluxo de aditivo em algum momento, ou um aditivo antigo foi excluído sem
 * reverter corretamente, o valor reconstruído aqui pode ficar incorreto —
 * auditar manualmente contratos manuais com aditivos antigos antes de confiar
 * cegamente neste backfill em produção.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('contratos_manuais')->orderBy('id')->chunkById(200, function ($contratos) {
            foreach ($contratos as $contrato) {
                $primeiroAditivo = DB::table('incidentes_contratuais')
                    ->where('contratavel_type', \App\Models\ContratoManual::class)
                    ->where('contratavel_id', $contrato->id)
                    ->orderBy('id')
                    ->first();

                $valorOriginal = $primeiroAditivo?->valor_total_base ?? $contrato->valor_total;

                DB::table('contratos_manuais')
                    ->where('id', $contrato->id)
                    ->update(['valor_total_original' => $valorOriginal]);
            }
        });
    }

    public function down(): void
    {
        DB::table('contratos_manuais')->update(['valor_total_original' => null]);
    }
};
