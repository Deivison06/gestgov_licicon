<?php

namespace App\Services;

use App\Models\Contrato;
use App\Models\IncidenteContratual;
use App\Models\IncidenteContratualItem;
use App\Models\LoteContratado;
use Illuminate\Support\Facades\DB;

class IncidenteContratualService
{
    public function atualizarAditivo(IncidenteContratual $incidente, Contrato $contrato, array $dados)
    {
        return DB::transaction(function () use ($incidente, $contrato, $dados) {
            $incidente->update([
                'meses_prorrogacao' => $dados['meses_prorrogacao'] ?? $incidente->meses_prorrogacao,
                'percentual_valor' => $dados['percentual_valor'] ?? $incidente->percentual_valor,
                'justificativa' => $dados['justificativa'] ?? $incidente->justificativa,
                'arquivo_solicitacao_path' => $dados['arquivo_solicitacao_path'] ?? $incidente->arquivo_solicitacao_path,
                'arquivo_orcamento_obra_path' => $dados['arquivo_orcamento_obra_path'] ?? $incidente->arquivo_orcamento_obra_path,
            ]);

            // Clear previous items to avoid duplication if user changes the percentage
            $incidente->itens()->delete();

            // If it's a value incident for Compras e Serviços, calculate items
            if (in_array($dados['tipo'], ['valor', 'prazo_valor']) && $dados['categoria'] === 'compras_servicos') {
                $percentual = $dados['percentual_valor'] / 100;

                $lotesContratados = LoteContratado::where('contrato_id', $contrato->id)->get();
                if ($lotesContratados->isEmpty()) {
                    $lotesContratados = LoteContratado::where('processo_id', $contrato->processo_id)->get();
                }

                foreach ($lotesContratados as $loteContratado) {
                    $qtdOriginal = $loteContratado->quantidade_contratada;
                    // Mantém precisão decimal se for menor que 1, ou arredonda se desejar, 
                    // usaremos o valor direto para não zerar (ex: 1 * 0.2 = 0.2)
                    $qtdAditivada = $qtdOriginal * $percentual;

                    if ($qtdAditivada > 0) {
                        $valorUnitario = $loteContratado->valor_unitario;
                        $valorTotalAditivado = $qtdAditivada * $valorUnitario;

                        IncidenteContratualItem::create([
                            'incidente_contratual_id' => $incidente->id,
                            'lote_contratado_id' => $loteContratado->id,
                            'quantidade_aditivada' => $qtdAditivada,
                            'valor_unitario' => $valorUnitario,
                            'valor_total_aditivado' => $valorTotalAditivado,
                        ]);
                    }
                }
            }

            return $incidente;
        });
    }
}
