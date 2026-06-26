<?php

namespace App\Services;

use App\Models\Processo;
use App\Models\Lote;
use App\Models\LoteContratado;
use App\Models\Documento;
use App\Models\EstoqueLote;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AtaContratacaoService extends AbstractService
{
    public function getLotesDisponiveis(Processo $processo, $vencedorId): array
    {
        Log::info('Buscando lotes disponíveis', [
            'processo_id' => $processo->id,
            'vencedor_id' => $vencedorId
        ]);

        $vencedor = \App\Models\Vencedor::where('id', $vencedorId)
            ->where('processo_id', $processo->id)
            ->first();

        if (!$vencedor) {
            throw new \Exception('Vencedor não encontrado neste processo.');
        }

        $lotes = Lote::where('vencedor_id', $vencedorId)
            ->with(['estoque' => function($query) use ($processo) {
                $query->where('processo_id', $processo->id);
            }])
            ->get()
            ->map(function($lote) use ($processo) {
                $quantidadeContratada = LoteContratado::where('lote_id', $lote->id)
                    ->where('processo_id', $processo->id)
                    ->whereIn('status', ['PENDENTE', 'CONTRATADO'])
                    ->sum('quantidade_contratada');

                $quantidadeDisponivel = max(0, (float) $lote->quantidade - (float) $quantidadeContratada);

                return [
                    'id' => $lote->id,
                    'item' => $lote->item,
                    'descricao' => $lote->descricao,
                    'quantidade_original' => (float) $lote->quantidade,
                    'quantidade_contratada' => (float) $quantidadeContratada,
                    'quantidade_disponivel' => $quantidadeDisponivel,
                    'vl_unit' => (float) $lote->vl_unit,
                    'unidade' => $lote->unidade,
                    'valor_total_disponivel' => $quantidadeDisponivel * (float) $lote->vl_unit
                ];
            })
            ->filter(function($lote) {
                return $lote['quantidade_disponivel'] > 0;
            })
            ->values();

        Log::info('Lotes disponíveis encontrados', [
            'processo_id' => $processo->id,
            'vencedor_id' => $vencedorId,
            'quantidade' => $lotes->count()
        ]);

        return [
            'lotes' => $lotes,
            'vencedor' => [
                'id' => $vencedor->id,
                'razao_social' => $vencedor->razao_social,
                'cnpj' => $vencedor->cnpj
            ]
        ];
    }

    public function criarContratacaoDireta(Processo $processo, array $data): array
    {
        $vencedorId = $data['vencedor_id'] ?? null;
        $loteId = $data['lote_id'] ?? null;
        $quantidade = (float) ($data['quantidade'] ?? 0);

        $lote = Lote::where('id', $loteId)
            ->where('vencedor_id', $vencedorId)
            ->firstOrFail();

        $quantidadeContratada = LoteContratado::where('lote_id', $loteId)
            ->where('processo_id', $processo->id)
            ->whereIn('status', ['PENDENTE', 'CONTRATADO'])
            ->sum('quantidade_contratada');

        $quantidadeDisponivel = max(0, (float) $lote->quantidade - (float) $quantidadeContratada);

        if ($quantidade > $quantidadeDisponivel) {
            throw new \Exception('Quantidade solicitada excede o disponível. Disponível: ' . number_format($quantidadeDisponivel, 2, ',', '.'));
        }

        $contratacao = LoteContratado::create([
            'processo_id' => $processo->id,
            'vencedor_id' => $vencedorId,
            'lote_id' => $loteId,
            'quantidade_contratada' => $quantidade,
            'valor_unitario' => (float) $lote->vl_unit,
            'valor_total' => (float) $lote->vl_unit * $quantidade,
            'status' => 'PENDENTE',
            'quantidade_disponivel_pos_contrato' => $quantidadeDisponivel - $quantidade
        ]);

        $this->atualizarEstoque($processo, $lote, $quantidade);

        Log::info('Contratação criada com sucesso', [
            'processo_id' => $processo->id,
            'lote_id' => $loteId,
            'quantidade' => $quantidade,
            'contratacao_id' => $contratacao->id
        ]);

        return [
            'contratacao' => [
                'id' => $contratacao->id,
                'item' => $lote->item,
                'quantidade' => $contratacao->quantidade_contratada,
                'valor_total' => $contratacao->valor_total
            ]
        ];
    }

    public function marcarComoContratado(Processo $processo, array $contratacaoIds): void
    {
        LoteContratado::whereIn('id', $contratacaoIds)
            ->where('processo_id', $processo->id)
            ->update(['status' => 'CONTRATADO']);

        Log::info('Contratações marcadas como CONTRATADO', [
            'processo_id' => $processo->id,
            'quantidade' => count($contratacaoIds)
        ]);
    }

    public function getContratacoesPendentes(Processo $processo)
    {
        return LoteContratado::where('processo_id', $processo->id)
            ->where('status', 'PENDENTE')
            ->with(['lote', 'vencedor'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getContratacoesPendentesNaoUsadas(Processo $processo)
    {
        // IDs de contratações já usadas em contratos
        $contratacoesUsadas = Documento::where('processo_id', $processo->id)
            ->where('tipo_documento', 'contrato')
            ->get()
            ->flatMap(function($doc) {
                $ids = $doc->contratacoes_selecionadas ?? [];
                return is_string($ids) ? json_decode($ids, true) : $ids;
            })
            ->unique()
            ->values()
            ->toArray();

        return LoteContratado::where('processo_id', $processo->id)
            ->where('status', 'PENDENTE')
            ->when(!empty($contratacoesUsadas), function($query) use ($contratacoesUsadas) {
                return $query->whereNotIn('id', $contratacoesUsadas);
            })
            ->with(['lote', 'vencedor'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getContratacoesAtualizadas(Processo $processo): array
    {
        $processo->load(['vencedores']);

        $contratacoes = LoteContratado::where('processo_id', $processo->id)
            ->where('status', 'PENDENTE')
            ->with(['lote', 'vencedor'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('vencedor_id');

        $html = view('admin.atas.partials.contratacoes_table', [
            'processo' => $processo,
            'contratacoes' => $contratacoes
        ])->render();

        return [
            'html' => $html,
            'totalItens' => LoteContratado::where('processo_id', $processo->id)
                ->where('status', 'PENDENTE')
                ->count(),
            'valorTotal' => LoteContratado::where('processo_id', $processo->id)
                ->where('status', 'PENDENTE')
                ->sum('valor_total')
        ];
    }

    public function getDadosParaEdicao(Processo $processo, LoteContratado $contratacao): array
    {
        if ($contratacao->processo_id != $processo->id) {
            throw new \Exception('Contratação não pertence a este processo.');
        }

        $contratacao->load(['lote', 'vencedor']);

        $estoque = EstoqueLote::where('lote_id', $contratacao->lote_id)
            ->where('processo_id', $processo->id)
            ->first();

        $quantidadeDisponivel = $estoque
            ? (float) $estoque->quantidade_disponivel + (float) $contratacao->quantidade_contratada
            : (float) $contratacao->lote->quantidade;

        return [
            'contratacao' => [
                'id' => $contratacao->id,
                'vencedor_id' => $contratacao->vencedor_id,
                'lote_id' => $contratacao->lote_id,
                'quantidade_contratada' => (float) $contratacao->quantidade_contratada,
                'valor_unitario' => (float) $contratacao->valor_unitario,
                'valor_total' => (float) $contratacao->valor_total,
                'observacao' => $contratacao->observacao,
                'status' => $contratacao->status,
                'item' => $contratacao->lote->item,
                'descricao' => $contratacao->lote->descricao,
                'vencedor' => $contratacao->vencedor->razao_social,
            ],
            'estoque' => [
                'quantidade_disponivel' => $quantidadeDisponivel,
                'quantidade_total_lote' => (float) $contratacao->lote->quantidade,
                'quantidade_utilizada' => $estoque ? (float) $estoque->quantidade_utilizada : 0,
            ],
            'max_quantidade' => $quantidadeDisponivel
        ];
    }

    public function atualizarContratacao(Processo $processo, LoteContratado $contratacao, array $data): array
    {
        DB::beginTransaction();

        if ($contratacao->status !== 'PENDENTE') {
            throw new \Exception('Apenas contratações com status PENDENTE podem ser editadas.');
        }

        $estoque = EstoqueLote::where('lote_id', $contratacao->lote_id)
            ->where('processo_id', $processo->id)
            ->first();

        if (!$estoque) {
            $lote = $contratacao->lote;
            $estoque = EstoqueLote::create([
                'lote_id' => $contratacao->lote_id,
                'processo_id' => $processo->id,
                'quantidade_disponivel' => $lote->quantidade,
                'quantidade_utilizada' => 0
            ]);
        }

        $novaQuantidade = (float) ($data['quantidade_contratada'] ?? 0);
        $quantidadeAtual = (float) $contratacao->quantidade_contratada;
        $diferenca = $novaQuantidade - $quantidadeAtual;

        if ($diferenca > 0) {
            $quantidadeDisponivelAtual = (float) $estoque->quantidade_disponivel;

            if ($diferenca > $quantidadeDisponivelAtual) {
                throw new \Exception(
                    "Quantidade solicitada excede a disponível. " .
                    "Disponível: " . number_format($quantidadeDisponivelAtual, 2, ',', '.') .
                    " | Necessário adicional: " . number_format($diferenca, 2, ',', '.')
                );
            }
        }

        if ($diferenca > 0) {
            $estoque->quantidade_disponivel -= $diferenca;
            $estoque->quantidade_utilizada += $diferenca;
        } elseif ($diferenca < 0) {
            $estoque->quantidade_disponivel += abs($diferenca);
            $estoque->quantidade_utilizada -= abs($diferenca);
        }
        $estoque->save();

        $disponivelApos = (float) $estoque->quantidade_disponivel;

        $contratacao->update([
            'quantidade_contratada' => $novaQuantidade,
            'quantidade_disponivel_pos_contrato' => $disponivelApos,
            'valor_total' => $novaQuantidade * (float) $contratacao->valor_unitario,
            'observacao' => $data['observacao'] ?? null,
        ]);

        DB::commit();

        Log::info('Contratação atualizada com sucesso', [
            'processo_id' => $processo->id,
            'contratacao_id' => $contratacao->id,
            'quantidade_anterior' => $quantidadeAtual,
            'quantidade_nova' => $novaQuantidade,
            'diferenca' => $diferenca
        ]);

        return [
            'contratacao' => $contratacao->fresh()->load(['lote', 'vencedor']),
            'estoque' => $estoque->fresh()
        ];
    }

    public function excluirContratacao(Processo $processo, LoteContratado $contratacao): array
    {
        DB::beginTransaction();

        if ($contratacao->status !== 'PENDENTE') {
            throw new \Exception('Apenas contratações com status PENDENTE podem ser excluídas.');
        }

        $estoque = EstoqueLote::where('lote_id', $contratacao->lote_id)
            ->where('processo_id', $processo->id)
            ->first();

        if ($estoque) {
            $estoque->quantidade_disponivel += (float) $contratacao->quantidade_contratada;
            $estoque->quantidade_utilizada -= (float) $contratacao->quantidade_contratada;

            if ($estoque->quantidade_utilizada < 0) {
                $estoque->quantidade_utilizada = 0;
            }

            $estoque->save();
        }

        $dadosContratacao = [
            'id' => $contratacao->id,
            'lote_id' => $contratacao->lote_id,
            'vencedor_id' => $contratacao->vencedor_id,
            'quantidade_contratada' => (float) $contratacao->quantidade_contratada,
            'valor_total' => (float) $contratacao->valor_total
        ];

        $contratacao->delete();

        DB::commit();

        Log::info('Contratação excluída com sucesso', [
            'processo_id' => $processo->id,
            'contratacao_dados' => $dadosContratacao,
            'estoque_apos' => $estoque ? [
                'disponivel' => (float) $estoque->quantidade_disponivel,
                'utilizada' => (float) $estoque->quantidade_utilizada
            ] : null
        ]);

        return [
            'estoque' => $estoque ? $estoque->fresh() : null
        ];
    }

    public function excluirTodasContratacoes(Processo $processo): array
    {
        DB::beginTransaction();

        $contratacoes = LoteContratado::where('processo_id', $processo->id)
            ->where('status', 'PENDENTE')
            ->get();

        if ($contratacoes->isEmpty()) {
            throw new \Exception('Não há contratações pendentes para excluir.');
        }

        $contador = 0;
        $erros = [];

        foreach ($contratacoes as $contratacao) {
            try {
                $estoque = EstoqueLote::where('lote_id', $contratacao->lote_id)
                    ->where('processo_id', $processo->id)
                    ->first();

                if ($estoque) {
                    $estoque->quantidade_disponivel += (float) $contratacao->quantidade_contratada;
                    $estoque->quantidade_utilizada -= (float) $contratacao->quantidade_contratada;

                    if ($estoque->quantidade_utilizada < 0) {
                        $estoque->quantidade_utilizada = 0;
                    }

                    $estoque->save();
                }

                $contratacao->delete();
                $contador++;

            } catch (\Exception $e) {
                $erros[] = [
                    'contratacao_id' => $contratacao->id,
                    'erro' => $e->getMessage()
                ];
                continue;
            }
        }

        DB::commit();

        Log::info('Todas as contratações pendentes excluídas', [
            'processo_id' => $processo->id,
            'excluidas' => $contador,
            'erros' => $erros
        ]);

        $mensagem = "{$contador} contratação(ões) pendente(s) excluída(s) com sucesso!";

        if (!empty($erros)) {
            $mensagem .= " (" . count($erros) . " erro(s) encontrado(s))";
        }

        return [
            'mensagem' => $mensagem,
            'excluidas' => $contador,
            'erros' => $erros
        ];
    }

    public function desfazerContrato(Processo $processo, Documento $documento): void
    {
        DB::beginTransaction();

        if ($documento->processo_id != $processo->id || $documento->tipo_documento != 'contrato') {
            throw new \Exception('Documento inválido ou não pertence a este processo.');
        }

        $contratacoesIds = $documento->contratacoes_selecionadas ?? [];
        if (is_string($contratacoesIds)) {
            $contratacoesIds = json_decode($contratacoesIds, true) ?? [];
        }

        if (empty($contratacoesIds)) {
            throw new \Exception('Contrato não possui contratações vinculadas.');
        }

        LoteContratado::whereIn('id', $contratacoesIds)
            ->where('processo_id', $processo->id)
            ->update(['status' => 'PENDENTE']);

        $contratacoes = LoteContratado::whereIn('id', $contratacoesIds)
            ->where('processo_id', $processo->id)
            ->get();

        foreach ($contratacoes as $contratacao) {
            $estoque = EstoqueLote::where('lote_id', $contratacao->lote_id)
                ->where('processo_id', $processo->id)
                ->first();

            if ($estoque) {
                $estoque->quantidade_disponivel += $contratacao->quantidade_contratada;
                $estoque->quantidade_utilizada -= $contratacao->quantidade_contratada;

                if ($estoque->quantidade_utilizada < 0) {
                    $estoque->quantidade_utilizada = 0;
                }

                $estoque->save();
            }
        }

        $caminhoCompleto = public_path($documento->caminho);
        if (file_exists($caminhoCompleto)) {
            unlink($caminhoCompleto);
        }

        $documento->delete();

        DB::commit();

        Log::info('Contrato desfeito com sucesso', [
            'processo_id' => $processo->id,
            'documento_id' => $documento->id,
            'contratacoes_afetadas' => count($contratacoesIds)
        ]);
    }

    private function atualizarEstoque(Processo $processo, Lote $lote, $quantidade): void
    {
        $estoque = EstoqueLote::where('lote_id', $lote->id)
            ->where('processo_id', $processo->id)
            ->first();

        if ($estoque) {
            $estoque->quantidade_utilizada += $quantidade;
            $estoque->quantidade_disponivel -= $quantidade;
            $estoque->save();
        } else {
            EstoqueLote::create([
                'processo_id' => $processo->id,
                'lote_id' => $lote->id,
                'quantidade_total' => $lote->quantidade,
                'quantidade_utilizada' => $quantidade,
                'quantidade_disponivel' => $lote->quantidade - $quantidade
            ]);
        }
    }
}
