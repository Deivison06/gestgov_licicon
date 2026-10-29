<?php

namespace App\Services;

use App\Models\Contrato;
use App\Models\ContratoManual;
use App\Models\IncidenteContratual;
use App\Models\IncidenteContratualItem;
use App\Models\LoteContratado;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

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
                'natureza_alteracao' => $dados['natureza_alteracao'] ?? $incidente->natureza_alteracao,
                'percentual_acrescimo' => array_key_exists('percentual_acrescimo', $dados) ? $dados['percentual_acrescimo'] : $incidente->percentual_acrescimo,
                'percentual_supressao' => array_key_exists('percentual_supressao', $dados) ? $dados['percentual_supressao'] : $incidente->percentual_supressao,
                'tem_valor_periodo_prorrogado' => array_key_exists('tem_valor_periodo_prorrogado', $dados) ? $dados['tem_valor_periodo_prorrogado'] : $incidente->tem_valor_periodo_prorrogado,
                'valor_periodo_prorrogado' => array_key_exists('valor_periodo_prorrogado', $dados) ? $dados['valor_periodo_prorrogado'] : $incidente->valor_periodo_prorrogado,
                'justificativa' => $dados['justificativa'] ?? $incidente->justificativa,
                'arquivo_solicitacao_path' => $dados['arquivo_solicitacao_path'] ?? $incidente->arquivo_solicitacao_path,
                'arquivo_orcamento_obra_path' => $dados['arquivo_orcamento_obra_path'] ?? $incidente->arquivo_orcamento_obra_path,
                'arquivo_aceite_documentacao_path' => $dados['arquivo_aceite_documentacao_path'] ?? $incidente->arquivo_aceite_documentacao_path,
                'nome_solicitante' => $dados['nome_solicitante'] ?? $incidente->nome_solicitante,
                'cargo_solicitante' => $dados['cargo_solicitante'] ?? $incidente->cargo_solicitante,
                'nome_parecerista' => $dados['nome_parecerista'] ?? $incidente->nome_parecerista,
                'cargo_parecerista' => $dados['cargo_parecerista'] ?? $incidente->cargo_parecerista,
                'oab_parecerista' => $dados['oab_parecerista'] ?? $incidente->oab_parecerista,
                // Campos do Parecer Técnico (Obras)
                'nome_engenheiro_parecer_tecnico' => $dados['nome_engenheiro_parecer_tecnico'] ?? $incidente->nome_engenheiro_parecer_tecnico,
                'cargo_engenheiro_parecer_tecnico' => $dados['cargo_engenheiro_parecer_tecnico'] ?? $incidente->cargo_engenheiro_parecer_tecnico,
                'registro_profissional_parecer_tecnico' => $dados['registro_profissional_parecer_tecnico'] ?? $incidente->registro_profissional_parecer_tecnico,
                'percentual_execucao_fisica' => array_key_exists('percentual_execucao_fisica', $dados) ? $dados['percentual_execucao_fisica'] : $incidente->percentual_execucao_fisica,
                'situacao_execucao_contratual' => $dados['situacao_execucao_contratual'] ?? $incidente->situacao_execucao_contratual,
                'fatores_atraso' => array_key_exists('fatores_atraso', $dados) ? $dados['fatores_atraso'] : $incidente->fatores_atraso,
                'descricao_fatores_atraso' => $dados['descricao_fatores_atraso'] ?? $incidente->descricao_fatores_atraso,
                'responsabilidade_atraso' => $dados['responsabilidade_atraso'] ?? $incidente->responsabilidade_atraso,
                'texto_vantajosidade_tecnica' => $dados['texto_vantajosidade_tecnica'] ?? $incidente->texto_vantajosidade_tecnica,
                'conclusao_tecnica' => $dados['conclusao_tecnica'] ?? $incidente->conclusao_tecnica,
                'limite_legal_aditivo_obras' => $dados['limite_legal_aditivo_obras'] ?? $incidente->limite_legal_aditivo_obras,
            ]);

            // Quando os itens vieram de uma planilha importada (ver
            // importarItensPlanilha()), eles não são recalculados/apagados aqui —
            // só o recálculo automático por percentual passa por este bloco.
            if (! $incidente->itens_origem_planilha) {
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
            }

            $this->aplicarEfeitosNoContrato($incidente, $contrato, $dados);

            return $incidente;
        });
    }

    /**
     * Importa os quantitativos aditivados de uma planilha (mesmo layout usado
     * para o Termo de Referência: coluna A = item, B = descrição, C = unidade,
     * D = quantidade aditivada — aceita negativo, para supressão). Casa cada
     * linha com o item do contrato pelo número do item (Lote::item). Se algum
     * item da planilha não existir no contrato, nada é alterado e a lista de
     * itens não encontrados é retornada para o usuário corrigir o arquivo.
     *
     * @return array{sucesso: bool, itens?: \Illuminate\Support\Collection, nao_encontrados?: array<int>}
     */
    public function importarItensPlanilha(IncidenteContratual $incidente, Contrato $contrato, UploadedFile $arquivo): array
    {
        $spreadsheet = IOFactory::load($arquivo->getRealPath());
        $linhas = $spreadsheet->getActiveSheet()->toArray();

        $lotesContratados = LoteContratado::where('contrato_id', $contrato->id)
            ->with('lote')
            ->get()
            ->keyBy(fn (LoteContratado $loteContratado) => trim((string) $loteContratado->lote?->item));

        $paraImportar = [];
        $naoEncontrados = [];

        foreach ($linhas as $index => $linha) {
            if ($index === 0) {
                continue; // cabeçalho
            }

            $itemNumero = trim((string) ($linha[0] ?? ''));
            $quantidade = trim((string) ($linha[3] ?? ''));

            if ($itemNumero === '' && $quantidade === '') {
                continue; // linha em branco
            }

            if (! is_numeric($quantidade) || (float) $quantidade == 0.0) {
                continue; // sem quantidade aditivada informada para este item
            }

            $loteContratado = $lotesContratados->get($itemNumero);

            if (! $loteContratado) {
                $naoEncontrados[] = $itemNumero;

                continue;
            }

            $paraImportar[] = [
                'lote_contratado_id' => $loteContratado->id,
                'quantidade_aditivada' => (float) $quantidade,
                'valor_unitario' => $loteContratado->valor_unitario,
            ];
        }

        if (! empty($naoEncontrados)) {
            return ['sucesso' => false, 'nao_encontrados' => $naoEncontrados];
        }

        return DB::transaction(function () use ($incidente, $arquivo, $paraImportar) {
            if ($incidente->arquivo_planilha_itens_path) {
                Storage::disk('public')->delete($incidente->arquivo_planilha_itens_path);
            }

            $incidente->itens()->delete();

            foreach ($paraImportar as $item) {
                IncidenteContratualItem::create([
                    'incidente_contratual_id' => $incidente->id,
                    'lote_contratado_id' => $item['lote_contratado_id'],
                    'quantidade_aditivada' => $item['quantidade_aditivada'],
                    'valor_unitario' => $item['valor_unitario'],
                    'valor_total_aditivado' => $item['quantidade_aditivada'] * $item['valor_unitario'],
                ]);
            }

            $incidente->update([
                'itens_origem_planilha' => true,
                'arquivo_planilha_itens_path' => $arquivo->store('incidentes/planilhas-itens', 'public'),
                'arquivo_planilha_itens_nome' => $arquivo->getClientOriginalName(),
            ]);

            return ['sucesso' => true, 'itens' => $incidente->itens()->with('loteContratado.lote')->get()];
        });
    }

    /**
     * Para Contratos Manuais/Externos: não há lotes cadastrados no sistema para
     * casar/calcular contra a planilha (o aditivo externo já é instruído fora
     * do sistema, e a lei só exige que a planilha de quantitativos acompanhe o
     * termo). Por isso aqui não se valida nem calcula nada — o conteúdo é
     * guardado tal como enviado e exibido no quadro de itens do Termo Aditivo.
     *
     * @return array{linhas: int}
     */
    public function importarPlanilhaBruta(IncidenteContratual $incidente, UploadedFile $arquivo): array
    {
        $spreadsheet = IOFactory::load($arquivo->getRealPath());
        $linhas = $spreadsheet->getActiveSheet()->toArray();

        return DB::transaction(function () use ($incidente, $arquivo, $linhas) {
            if ($incidente->arquivo_planilha_itens_path) {
                Storage::disk('public')->delete($incidente->arquivo_planilha_itens_path);
            }

            $incidente->update([
                'itens_origem_planilha' => true,
                'arquivo_planilha_itens_path' => $arquivo->store('incidentes/planilhas-itens', 'public'),
                'arquivo_planilha_itens_nome' => $arquivo->getClientOriginalName(),
                'arquivo_planilha_itens_dados' => $linhas,
            ]);

            return ['linhas' => max(count($linhas) - 1, 0)];
        });
    }

    /**
     * Desfaz a importação por planilha. Para Contrato do Sistema, volta o
     * aditivo ao modo "percentual" (o próximo salvamento da Solicitação do
     * Aditivo recalcula os itens a partir do percentual_valor salvo — ver
     * atualizarAditivo()); para Contrato Manual/Externo, só remove o anexo e
     * os dados brutos exibidos no Termo.
     */
    public function removerItensPlanilha(IncidenteContratual $incidente): void
    {
        DB::transaction(function () use ($incidente) {
            if ($incidente->arquivo_planilha_itens_path) {
                Storage::disk('public')->delete($incidente->arquivo_planilha_itens_path);
            }

            $incidente->itens()->delete();

            $incidente->update([
                'itens_origem_planilha' => false,
                'arquivo_planilha_itens_path' => null,
                'arquivo_planilha_itens_nome' => null,
                'arquivo_planilha_itens_dados' => null,
            ]);
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
                    if (! $lote || (float) $item['quantidade_aditivada'] <= 0) {
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
            && ! empty($dados['meses_prorrogacao'])) {
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
            } elseif ($incidente->origem !== 'externo' && ! empty($dados['percentual_valor'])) {
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
