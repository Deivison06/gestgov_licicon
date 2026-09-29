<?php

namespace App\Services;

use App\Models\Contrato;
use App\Models\ContratoManual;
use App\Models\IncidenteContratual;
use App\Models\IncidenteContratualItem;
use App\Models\LoteContratado;
use Illuminate\Support\Facades\DB;

class IncidenteContratualService
{
    public function atualizarAditivo(IncidenteContratual $incidente, Contrato|ContratoManual $contrato, array $dados)
    {
        return DB::transaction(function () use ($incidente, $contrato, $dados) {
            $incidente->update([
                'tipo' => $dados['tipo'],
                'categoria' => $dados['categoria'],
                'meses_prorrogacao' => $dados['meses_prorrogacao'] ?? $incidente->meses_prorrogacao,
                'percentual_valor' => $dados['percentual_valor'] ?? $incidente->percentual_valor,
                'justificativa' => $dados['justificativa'] ?? $incidente->justificativa,
                'arquivo_solicitacao_path' => $dados['arquivo_solicitacao_path'] ?? $incidente->arquivo_solicitacao_path,
                'arquivo_orcamento_obra_path' => $dados['arquivo_orcamento_obra_path'] ?? $incidente->arquivo_orcamento_obra_path,
                'nome_solicitante' => $dados['nome_solicitante'] ?? $incidente->nome_solicitante,
                'cargo_solicitante' => $dados['cargo_solicitante'] ?? $incidente->cargo_solicitante,
                'nome_parecerista' => $dados['nome_parecerista'] ?? $incidente->nome_parecerista,
                'oab_parecerista' => $dados['oab_parecerista'] ?? $incidente->oab_parecerista,
            ]);

            // Clear previous items to avoid duplication if user changes the percentage
            $incidente->itens()->delete();

            // Cálculo automático de itens aditivados (por percentual, a partir dos lotes
            // contratados) só existe para Contratos do Sistema — Contratos Manuais/Externos
            // não têm lotes formalizados no sistema (não vieram de um Processo licitatório).
            // Para eles, o aditivo segue apenas com o texto/percentual informado.
            if ($contrato instanceof Contrato
                && in_array($dados['tipo'], ['valor', 'prazo_valor'])
                && $dados['categoria'] === 'compras_servicos') {
                $percentual_valor = $dados['percentual_valor'] ?? $incidente->percentual_valor;
                $percentual = $percentual_valor / 100;

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

            $this->aplicarEfeitosNoContrato($incidente, $contrato, $dados);

            return $incidente;
        });
    }

    /**
     * Registra um aditivo feito fora do sistema: só os campos principais + anexo,
     * sem gerar solicitação/parecer/termo. Para Contrato do Sistema com itens de
     * valor, a quantidade aditivada é informada item a item (não por percentual —
     * um aditivo externo raramente é um percentual uniforme entre os lotes).
     */
    public function atualizarAditivoExterno(IncidenteContratual $incidente, Contrato|ContratoManual $contrato, array $dados)
    {
        return DB::transaction(function () use ($incidente, $contrato, $dados) {
            $incidente->update([
                'data_aditivo' => $dados['data_aditivo'],
                'justificativa' => $dados['justificativa'] ?? $incidente->justificativa,
                'meses_prorrogacao' => $dados['meses_prorrogacao'] ?? $incidente->meses_prorrogacao,
                'arquivo_aditivo_externo_path' => $dados['arquivo_aditivo_externo_path'] ?? $incidente->arquivo_aditivo_externo_path,
            ]);

            if (isset($dados['itens'])) {
                $incidente->itens()->delete();

                foreach ($dados['itens'] as $item) {
                    $lote = LoteContratado::find($item['lote_contratado_id']);
                    if (!$lote || (float) $item['quantidade_aditivada'] <= 0) {
                        continue;
                    }

                    IncidenteContratualItem::create([
                        'incidente_contratual_id' => $incidente->id,
                        'lote_contratado_id' => $lote->id,
                        'quantidade_aditivada' => $item['quantidade_aditivada'],
                        'valor_unitario' => $lote->valor_unitario,
                        'valor_total_aditivado' => $item['quantidade_aditivada'] * $lote->valor_unitario,
                    ]);
                }
            }

            $this->aplicarEfeitosNoContrato($incidente, $contrato, $dados);

            return $incidente;
        });
    }

    /**
     * Aplica o efeito real do aditivo no contrato (prazo e/ou valor), a partir do
     * snapshot tirado na criação deste aditivo (data_finalizacao_base/valor_total_base).
     * Sempre recalcula um valor ABSOLUTO a partir do snapshot — nunca incrementa —
     * então re-salvar o mesmo aditivo várias vezes (edição) não duplica o efeito.
     *
     * Limitação conhecida: editar um aditivo mais antigo depois de já existir um mais
     * novo no mesmo contrato não recalcula em cascata o(s) aditivo(s) seguinte(s).
     */
    private function aplicarEfeitosNoContrato(IncidenteContratual $incidente, Contrato|ContratoManual $contrato, array $dados): void
    {
        $mudou = false;

        if (in_array($incidente->tipo, ['prazo', 'prazo_valor'])
            && $incidente->data_finalizacao_base
            && !empty($dados['meses_prorrogacao'])) {
            $contrato->data_finalizacao = $incidente->data_finalizacao_base->copy()
                ->addMonths((int) $dados['meses_prorrogacao']);
            $mudou = true;
        }

        if (in_array($incidente->tipo, ['valor', 'prazo_valor'])
            && $contrato instanceof ContratoManual
            && $incidente->valor_total_base !== null) {
            if ($incidente->origem === 'externo' && isset($dados['valor_acrescido'])) {
                $contrato->valor_total = $incidente->valor_total_base + $dados['valor_acrescido'];
                $mudou = true;
            } elseif ($incidente->origem !== 'externo' && !empty($dados['percentual_valor'])) {
                $contrato->valor_total = $incidente->valor_total_base * (1 + $dados['percentual_valor'] / 100);
                $mudou = true;
            }
        }

        if ($mudou) {
            $contrato->save();
        }
    }

    /**
     * Desfaz o efeito deste aditivo no contrato, restaurando o snapshot tirado na
     * criação dele. Usado ao reverter/excluir o aditivo (ver limitação acima).
     */
    public function reverterEfeitosNoContrato(IncidenteContratual $incidente, Contrato|ContratoManual $contrato): void
    {
        $mudou = false;

        if ($incidente->data_finalizacao_base !== null) {
            $contrato->data_finalizacao = $incidente->data_finalizacao_base;
            $mudou = true;
        }

        if ($contrato instanceof ContratoManual && $incidente->valor_total_base !== null) {
            $contrato->valor_total = $incidente->valor_total_base;
            $mudou = true;
        }

        if ($mudou) {
            $contrato->save();
        }
    }
}
