<?php

namespace App\Http\Controllers;

use App\Models\Lote;
use App\Models\LoteContratado;
use App\Models\EstoqueLote;
use App\Models\Processo;
use App\Models\Vencedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContratacaoController extends Controller
{
    /**
     * Verificar disponibilidade antes de tudo
     */
    public function verificarDisponibilidade(Request $request, Processo $processo)
    {
        $request->validate([
            'lote_id' => 'required|exists:lotes,id',
            'quantidade_desejada' => 'required|numeric|min:0.01'
        ]);

        try {
            $lote = Lote::findOrFail($request->lote_id);

            // Buscar estoque atual
            $estoque = EstoqueLote::where('lote_id', $request->lote_id)
                ->where('processo_id', $processo->id)
                ->first();

            // Se não existe estoque, criar com quantidade total do lote
            if (!$estoque) {
                $estoque = EstoqueLote::create([
                    'lote_id' => $lote->id,
                    'processo_id' => $processo->id,
                    'quantidade_disponivel' => $lote->quantidade,
                    'quantidade_utilizada' => 0
                ]);
            }

            $disponivel = $estoque->quantidade_disponivel;
            $disponivelApos = $disponivel - $request->quantidade_desejada;

            return response()->json([
                'success' => true,
                'disponivel' => (float) $disponivel,
                'disponivel_apos' => (float) $disponivelApos,
                'suficiente' => $disponivel >= $request->quantidade_desejada,
                'estoque' => $estoque
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao verificar disponibilidade', [
                'processo_id' => $processo->id,
                'lote_id' => $request->lote_id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao verificar disponibilidade: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Salvar contratação COM CONTROLE DE ESTOQUE
     */
    public function store(Request $request, Processo $processo)
    {
        $request->validate([
            'vencedor_id' => 'required|exists:vencedores,id',
            'lote_id' => 'required|exists:lotes,id',
            'quantidade_contratada' => 'required|numeric|min:0.01',
            'observacao' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $lote = Lote::findOrFail($request->lote_id);

            // 1. VERIFICAR SE LOTE PERTENCE AO VENCEDOR
            if ($lote->vencedor_id != $request->vencedor_id) {
                throw new \Exception("O lote não pertence ao vencedor selecionado.");
            }

            // 2. BUSCAR/CRIAR ESTOQUE
            $estoque = EstoqueLote::firstOrCreate(
                [
                    'lote_id' => $lote->id,
                    'processo_id' => $processo->id
                ],
                [
                    'quantidade_disponivel' => $lote->quantidade,
                    'quantidade_utilizada' => 0
                ]
            );

            // 3. VERIFICAR DISPONIBILIDADE
            if ((float) $request->quantidade_contratada > (float) $estoque->quantidade_disponivel) {
                throw new \Exception(
                    "Quantidade solicitada excede a disponível. " .
                    "Disponível: " . number_format($estoque->quantidade_disponivel, 2, ',', '.') . " " .
                    "Solicitado: " . number_format($request->quantidade_contratada, 2, ',', '.')
                );
            }

            // 4. CALCULAR DISPONIBILIDADE APÓS ESTE CONTRATO
            $disponivelApos = $estoque->quantidade_disponivel - $request->quantidade_contratada;

            // 5. CRIAR CONTRATAÇÃO
            $contratacao = LoteContratado::create([
                'lote_id' => $request->lote_id,
                'vencedor_id' => $request->vencedor_id,
                'processo_id' => $processo->id,
                'quantidade_contratada' => $request->quantidade_contratada,
                'quantidade_disponivel_pos_contrato' => $disponivelApos,
                'valor_unitario' => $lote->vl_unit,
                'valor_total' => $request->quantidade_contratada * $lote->vl_unit,
                'observacao' => $request->observacao,
                'status' => 'PENDENTE',
            ]);

            // 6. ATUALIZAR ESTOQUE (RESERVAR QUANTIDADE)
            $estoque->reservarQuantidade($request->quantidade_contratada);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Contratação criada com sucesso!',
                'contratacao' => $contratacao,
                'estoque_atualizado' => $estoque->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao salvar contratação', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage(),
                'dados' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar contratação: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Atualizar contratação existente
     */
    public function update(Request $request, Processo $processo, LoteContratado $contratacao)
    {
        $request->validate([
            'quantidade_contratada' => 'required|numeric|min:0.01',
            'observacao' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // 1. BUSCAR ESTOQUE
            $estoque = EstoqueLote::where('lote_id', $contratacao->lote_id)
                ->where('processo_id', $processo->id)
                ->firstOrFail();

            // 2. CALCULAR DIFERENÇA
            $diferenca = $request->quantidade_contratada - $contratacao->quantidade_contratada;

            // 3. VERIFICAR SE TEM ESTOQUE PARA AUMENTAR
            if ($diferenca > 0 && $diferenca > $estoque->quantidade_disponivel) {
                throw new \Exception(
                    "Não há estoque suficiente para aumentar a quantidade. " .
                    "Disponível: " . number_format($estoque->quantidade_disponivel, 2, ',', '.')
                );
            }

            // 4. ATUALIZAR ESTOQUE
            if ($diferenca > 0) {
                // Aumentando quantidade - reservar mais
                $estoque->reservarQuantidade($diferenca);
            } elseif ($diferenca < 0) {
                // Diminuindo quantidade - liberar
                $estoque->liberarQuantidade(abs($diferenca));
            }

            // 5. CALCULAR NOVA DISPONIBILIDADE APÓS CONTRATO
            $disponivelApos = $estoque->quantidade_disponivel - $diferenca;

            // 6. ATUALIZAR CONTRATAÇÃO
            $contratacao->update([
                'quantidade_contratada' => $request->quantidade_contratada,
                'quantidade_disponivel_pos_contrato' => $disponivelApos,
                'valor_total' => $request->quantidade_contratada * $contratacao->valor_unitario,
                'observacao' => $request->observacao,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Contratação atualizada com sucesso!',
                'estoque' => $estoque->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar contratação', [
                'contratacao_id' => $contratacao->id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar contratação: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancelar/remover contratação (libera estoque)
     */
    public function destroy(Processo $processo, LoteContratado $contratacao)
    {
        try {
            DB::beginTransaction();

            // 1. BUSCAR ESTOQUE
            $estoque = EstoqueLote::where('lote_id', $contratacao->lote_id)
                ->where('processo_id', $processo->id)
                ->first();

            // 2. LIBERAR ESTOQUE SE CONTRATAÇÃO ESTIVER ATIVA
            if ($estoque && $contratacao->status !== 'CANCELADO') {
                $estoque->liberarQuantidade($contratacao->quantidade_contratada);
            }

            // 3. REMOVER CONTRATAÇÃO
            $contratacao->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Contratação removida e estoque liberado com sucesso!',
                'estoque' => $estoque ? $estoque->fresh() : null
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao remover contratação', [
                'contratacao_id' => $contratacao->id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover contratação: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Listar estoque disponível para um vencedor
     */
    public function estoqueDisponivel(Processo $processo, Vencedor $vencedor)
    {
        $lotes = $vencedor->lotes()
            ->with(['estoque' => function($query) use ($processo) {
                $query->where('processo_id', $processo->id);
            }])
            ->get()
            ->map(function ($lote) use ($processo) {
                $estoque = $lote->estoque->first();
                $quantidadeDisponivel = $estoque ? (float) $estoque->quantidade_disponivel : (float) $lote->quantidade;
                $quantidadeUtilizada = $estoque ? (float) $estoque->quantidade_utilizada : 0;

                return [
                    'id' => $lote->id,
                    'item' => $lote->item,
                    'lote' => $lote->lote,
                    'descricao' => $lote->descricao,
                    'unidade' => $lote->unidade,
                    'marca' => $lote->marca,
                    'modelo' => $lote->modelo,
                    'quantidade_total' => (float) $lote->quantidade,
                    'quantidade_disponivel' => $quantidadeDisponivel,
                    'quantidade_utilizada' => $quantidadeUtilizada,
                    'vl_unit' => (float) $lote->vl_unit,
                    'vl_total' => (float) $lote->vl_total,
                    'estoque_id' => $estoque ? $estoque->id : null,
                ];
            })
            ->filter(function ($lote) {
                return $lote['quantidade_disponivel'] > 0;
            });

        return response()->json([
            'success' => true,
            'lotes' => $lotes->values(),
        ]);
    }

    /**
     * Dashboard de estoque
     */
    public function dashboardEstoque(Processo $processo)
    {
        $estoque = EstoqueLote::where('processo_id', $processo->id)
            ->with(['lote.vencedor'])
            ->get()
            ->groupBy('lote.vencedor_id');

        $resumo = [
            'total_itens' => $estoque->count(),
            'quantidade_total_disponivel' => $estoque->sum('quantidade_disponivel'),
            'quantidade_total_utilizada' => $estoque->sum('quantidade_utilizada'),
            'valor_total_disponivel' => $estoque->sum(function($item) {
                return (float) $item->quantidade_disponivel * (float) $item->lote->vl_unit;
            }),
            'valor_total_utilizado' => $estoque->sum(function($item) {
                return (float) $item->quantidade_utilizada * (float) $item->lote->vl_unit;
            })
        ];

        return response()->json([
            'success' => true,
            'estoque' => $estoque,
            'resumo' => $resumo
        ]);
    }

    public function edit(Processo $processo, LoteContratado $contratacao)
    {
        // Verificar se a contratação pertence ao processo
        if ($contratacao->processo_id != $processo->id) {
            return response()->json([
                'success' => false,
                'message' => 'Contratação não pertence a este processo.'
            ], 403);
        }

        $contratacao->load(['lote', 'vencedor']);

        // Buscar estoque atual
        $estoque = EstoqueLote::where('lote_id', $contratacao->lote_id)
            ->where('processo_id', $processo->id)
            ->first();

        return response()->json([
            'success' => true,
            'contratacao' => $contratacao,
            'estoque' => $estoque,
            'disponivel_atual' => $estoque ? (float) $estoque->quantidade_disponivel : (float) $contratacao->lote->quantidade
        ]);
    }

    /**
     * Confirmar contratação (mudar status para CONTRATADO)
     */
    public function confirmar(Processo $processo, LoteContratado $contratacao)
    {
        try {
            DB::beginTransaction();

            // Verificar se pode confirmar
            if (!$contratacao->podeConfirmar()) {
                throw new \Exception('Esta contratação não pode ser confirmada.');
            }

            // Atualizar status
            $contratacao->confirmar();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Contratação confirmada com sucesso!',
                'contratacao' => $contratacao->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao confirmar contratação', [
                'contratacao_id' => $contratacao->id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao confirmar contratação: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Listar contratações do processo
     */
    public function listar(Processo $processo)
    {
        $contratacoes = LoteContratado::where('processo_id', $processo->id)
            ->with(['lote', 'vencedor'])
            ->orderBy('vencedor_id')
            ->orderBy('created_at', 'desc')
            ->get();

        // Calcular totais
        $totais = [
            'total' => $contratacoes->count(),
            'quantidade' => (float) $contratacoes->sum('quantidade_contratada'),
            'valor' => (float) $contratacoes->sum('valor_total'),
            'pendentes' => $contratacoes->where('status', 'PENDENTE')->count(),
            'contratados' => $contratacoes->where('status', 'CONTRATADO')->count(),
        ];

        return response()->json([
            'success' => true,
            'contratacoes' => $contratacoes,
            'totais' => $totais,
        ]);
    }

    /**
     * Obter lotes disponíveis para um vencedor (USANDO ESTOQUE)
     */
    public function lotesDisponiveis(Processo $processo, Vencedor $vencedor)
    {
        try {
            $lotes = $vencedor->lotes()
                ->with(['estoque' => function($query) use ($processo) {
                    $query->where('processo_id', $processo->id);
                }])
                ->get()
                ->map(function ($lote) use ($processo) {
                    $estoque = $lote->estoque->first();
                    $quantidadeDisponivel = $estoque ? (float) $estoque->quantidade_disponivel : (float) $lote->quantidade;
                    $quantidadeUtilizada = $estoque ? (float) $estoque->quantidade_utilizada : 0;

                    return [
                        'id' => $lote->id,
                        'item' => $lote->item,
                        'lote' => $lote->lote,
                        'descricao' => $lote->descricao,
                        'unidade' => $lote->unidade,
                        'marca' => $lote->marca,
                        'modelo' => $lote->modelo,
                        'quantidade_total' => (float) $lote->quantidade,
                        'quantidade_disponivel' => $quantidadeDisponivel,
                        'quantidade_utilizada' => $quantidadeUtilizada,
                        'vl_unit' => (float) $lote->vl_unit,
                        'vl_total' => (float) $lote->vl_total,
                        'estoque_id' => $estoque ? $estoque->id : null,
                    ];
                })
                ->filter(function ($lote) {
                    return $lote['quantidade_disponivel'] > 0;
                })
                ->values();

            return response()->json([
                'success' => true,
                'lotes' => $lotes,
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao carregar lotes disponíveis', [
                'processo_id' => $processo->id,
                'vencedor_id' => $vencedor->id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar lotes disponíveis: ' . $e->getMessage(),
                'lotes' => []
            ], 500);
        }
    }

    /**
     * Contratação em lote - COM CONTROLE DE ESTOQUE
     */
    public function storeEmLote(Request $request, Processo $processo)
    {
        try {
            $contratacoes = $request->input('contratacoes', []);
            $salvas = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($contratacoes as $index => $contratacaoData) {
                try {
                    // Validar dados básicos
                    if (empty($contratacaoData['vencedor_id']) || empty($contratacaoData['lote_id']) || empty($contratacaoData['quantidade_contratada'])) {
                        $errors[] = "Contratação #" . ($index + 1) . ": Dados incompletos";
                        continue;
                    }

                    // Buscar detalhes do lote
                    $lote = Lote::find($contratacaoData['lote_id']);

                    if (!$lote) {
                        $errors[] = "Contratação #" . ($index + 1) . ": Lote não encontrado";
                        continue;
                    }

                    // Verificar se o lote pertence ao vencedor
                    if ($lote->vencedor_id != $contratacaoData['vencedor_id']) {
                        $errors[] = "Contratação #" . ($index + 1) . ": O lote não pertence ao vencedor selecionado";
                        continue;
                    }

                    // Buscar/criar estoque
                    $estoque = EstoqueLote::firstOrCreate(
                        [
                            'lote_id' => $lote->id,
                            'processo_id' => $processo->id
                        ],
                        [
                            'quantidade_disponivel' => $lote->quantidade,
                            'quantidade_utilizada' => 0
                        ]
                    );

                    // Verificar disponibilidade
                    if ((float) $contratacaoData['quantidade_contratada'] > (float) $estoque->quantidade_disponivel) {
                        $errors[] = "Contratação #" . ($index + 1) . ": Quantidade excede a disponível para '{$lote->item}' (Máx: " . number_format($estoque->quantidade_disponivel, 2, ',', '.') . ")";
                        continue;
                    }

                    // Calcular disponibilidade após este contrato
                    $disponivelApos = $estoque->quantidade_disponivel - $contratacaoData['quantidade_contratada'];

                    // Verificar se já existe contratação para este lote e vencedor (pendente)
                    $existente = LoteContratado::where('lote_id', $contratacaoData['lote_id'])
                        ->where('vencedor_id', $contratacaoData['vencedor_id'])
                        ->where('processo_id', $processo->id)
                        ->where('status', 'PENDENTE')
                        ->first();

                    if ($existente) {
                        // Liberar quantidade anterior
                        $estoque->liberarQuantidade($existente->quantidade_contratada);

                        // Atualizar contratação existente
                        $existente->update([
                            'quantidade_contratada' => $contratacaoData['quantidade_contratada'],
                            'quantidade_disponivel_pos_contrato' => $disponivelApos,
                            'valor_total' => $contratacaoData['quantidade_contratada'] * $existente->valor_unitario,
                            'observacao' => $contratacaoData['observacao'] ?? $existente->observacao,
                        ]);
                    } else {
                        // Criar nova contratação
                        LoteContratado::create([
                            'lote_id' => $contratacaoData['lote_id'],
                            'vencedor_id' => $contratacaoData['vencedor_id'],
                            'processo_id' => $processo->id,
                            'quantidade_contratada' => $contratacaoData['quantidade_contratada'],
                            'quantidade_disponivel_pos_contrato' => $disponivelApos,
                            'valor_unitario' => $lote->vl_unit,
                            'valor_total' => $contratacaoData['quantidade_contratada'] * $lote->vl_unit,
                            'observacao' => $contratacaoData['observacao'] ?? null,
                            'status' => 'PENDENTE',
                        ]);
                    }

                    // Atualizar estoque (reservar quantidade)
                    $estoque->reservarQuantidade($contratacaoData['quantidade_contratada']);

                    $salvas++;

                } catch (\Exception $e) {
                    $errors[] = "Contratação #" . ($index + 1) . ": " . $e->getMessage();
                    continue;
                }
            }

            if (!empty($errors)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Erros encontrados ao salvar contratações:',
                    'errors' => $errors,
                    'salvas' => $salvas
                ], 400);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$salvas} contratação(ões) salva(s) com sucesso!",
                'salvas' => $salvas
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao salvar contratações em lote', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar contratações em lote: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vincular contratações a um contrato específico
     */
    public function vincularAoContrato(Request $request, Processo $processo)
    {
        $request->validate([
            'contrato_id' => 'required|exists:contratos,id',
            'contratacao_ids' => 'required|array',
            'contratacao_ids.*' => 'exists:lote_contratados,id'
        ]);

        try {
            DB::beginTransaction();

            // Verificar se as contratações pertencem ao processo
            $contratacoes = LoteContratado::whereIn('id', $request->contratacao_ids)
                ->where('processo_id', $processo->id)
                ->get();

            if ($contratacoes->count() !== count($request->contratacao_ids)) {
                throw new \Exception('Uma ou mais contratações não pertencem a este processo.');
            }

            // Vincular ao contrato
            foreach ($contratacoes as $contratacao) {
                $contratacao->update([
                    'contrato_id' => $request->contrato_id,
                    'status' => 'CONTRATADO' // Ao vincular ao contrato, considera-se confirmado
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Contratações vinculadas ao contrato com sucesso!',
                'total_viculadas' => $contratacoes->count()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao vincular contratações', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao vincular contratações: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Relatório de estoque e contratações
     */
    public function relatorio(Processo $processo)
    {
        $estoque = EstoqueLote::where('processo_id', $processo->id)
            ->with(['lote.vencedor', 'lote.contratados' => function($query) use ($processo) {
                $query->where('processo_id', $processo->id);
            }])
            ->get()
            ->map(function ($estoqueItem) {
                $lote = $estoqueItem->lote;

                return [
                    'lote_id' => $lote->id,
                    'item' => $lote->item,
                    'lote' => $lote->lote,
                    'vencedor' => $lote->vencedor->razao_social,
                    'quantidade_total' => (float) $lote->quantidade,
                    'quantidade_disponivel' => (float) $estoqueItem->quantidade_disponivel,
                    'quantidade_utilizada' => (float) $estoqueItem->quantidade_utilizada,
                    'percentual_utilizado' => $lote->quantidade > 0
                        ? round(((float) $estoqueItem->quantidade_utilizada / (float) $lote->quantidade) * 100, 2)
                        : 0,
                    'contratacoes' => $lote->contratados->map(function ($contratacao) {
                        return [
                            'id' => $contratacao->id,
                            'quantidade' => (float) $contratacao->quantidade_contratada,
                            'status' => $contratacao->status,
                            'valor_total' => (float) $contratacao->valor_total,
                            'observacao' => $contratacao->observacao
                        ];
                    }),
                    'valor_disponivel' => (float) $estoqueItem->quantidade_disponivel * (float) $lote->vl_unit,
                    'valor_utilizado' => (float) $estoqueItem->quantidade_utilizada * (float) $lote->vl_unit,
                ];
            });

        $resumo = [
            'total_itens' => $estoque->count(),
            'quantidade_total' => $estoque->sum('quantidade_total'),
            'quantidade_disponivel' => $estoque->sum('quantidade_disponivel'),
            'quantidade_utilizada' => $estoque->sum('quantidade_utilizada'),
            'valor_total_disponivel' => $estoque->sum('valor_disponivel'),
            'valor_total_utilizado' => $estoque->sum('valor_utilizado'),
            'percentual_utilizado' => $estoque->sum('quantidade_total') > 0
                ? round(($estoque->sum('quantidade_utilizada') / $estoque->sum('quantidade_total')) * 100, 2)
                : 0,
        ];

        return response()->json([
            'success' => true,
            'estoque' => $estoque,
            'resumo' => $resumo,
            'processo' => [
                'id' => $processo->id,
                'numero_processo' => $processo->numero_processo,
                'objeto' => $processo->objeto
            ]
        ]);
    }

    /**
     * Recalcular estoque (para correções)
     */
    public function recalcularEstoque(Processo $processo)
    {
        try {
            DB::beginTransaction();

            // Buscar todos os lotes do processo
            $vencedores = $processo->vencedores()->with('lotes')->get();

            foreach ($vencedores as $vencedor) {
                foreach ($vencedor->lotes as $lote) {
                    // Calcular quantidade total contratada para este lote
                    $quantidadeContratada = (float) LoteContratado::where('lote_id', $lote->id)
                        ->where('processo_id', $processo->id)
                        ->whereIn('status', ['PENDENTE', 'CONTRATADO'])
                        ->sum('quantidade_contratada');

                    // Buscar ou criar estoque
                    $estoque = EstoqueLote::firstOrCreate(
                        [
                            'lote_id' => $lote->id,
                            'processo_id' => $processo->id
                        ],
                        [
                            'quantidade_disponivel' => $lote->quantidade,
                            'quantidade_utilizada' => 0
                        ]
                    );

                    // Atualizar estoque
                    $estoque->quantidade_utilizada = $quantidadeContratada;
                    $estoque->quantidade_disponivel = (float) $lote->quantidade - $quantidadeContratada;
                    $estoque->save();

                    // Atualizar quantidade disponível pós-contrato em cada contratação
                    LoteContratado::where('lote_id', $lote->id)
                        ->where('processo_id', $processo->id)
                        ->each(function ($contratacao) use ($estoque) {
                            $contratacao->quantidade_disponivel_pos_contrato =
                                (float) $estoque->quantidade_disponivel + (float) $contratacao->quantidade_contratada;
                            $contratacao->save();
                        });
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Estoque recalculado com sucesso!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao recalcular estoque', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao recalcular estoque: ' . $e->getMessage()
            ], 500);
        }
    }
}
