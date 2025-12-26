<?php

namespace App\Http\Controllers;

use App\Models\Lote;
use App\Models\Processo;
use App\Models\Documento;
use App\Models\Prefeitura;
use App\Models\EstoqueLote;
use Illuminate\Http\Request;
use App\Models\LoteContratado;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class AtaController extends Controller
{
    protected $documentoConfig = [
        'contrato' => [
            'titulo' => 'CONTRATO',
            'cor' => 'bg-blue-500',
            'campos' => ['numero_contrato', 'data_assinatura_contrato', 'numero_extrato', 'comarca', 'fonte_recurso', 'subcontratacao'],
            'requer_assinatura' => true,
        ]
    ];

    /**
     * Listar processos para geração de atas
     */
    public function index(Request $request)
    {
        $prefeituras = Prefeitura::with(['processos' => function($query) {
            $query->orderBy('created_at', 'desc');
        }])->get();

        $prefeituraId = $request->get('prefeitura_id');
        $processoId = $request->get('processo_id');
        
        $query = Processo::query()
            ->whereHas('lotes')
            ->when($prefeituraId, function($query) use ($prefeituraId) {
                return $query->where('prefeitura_id', $prefeituraId);
            })
            ->when($processoId, function($query) use ($processoId) {
                return $query->where('id', $processoId);
            });

        $processos = $query->with([
                'prefeitura',
                'lotesContratados.lote.vencedor',
                'lotes.vencedor'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('Admin.Atas.index', compact('prefeituras', 'processos', 'prefeituraId', 'processoId'));
    }

    /**
     * Visualizar ata de um processo
     */
    public function show(Processo $processo)
    {
        $processo->load([
            'prefeitura',
            'prefeitura.unidades',
            'lotes.vencedor',
            'lotes.contratados' => function ($query) use ($processo) {
                $query->where('processo_id', $processo->id);
            },
            'vencedores',
            'finalizacao'
        ]);

        // Preparar dados da ata
        $dadosAtas = $this->prepararDadosAtaTodosLotes($processo);
        
        // Carregar contratações agrupadas por vencedor (APENAS PENDENTES)
        $contratacoes = $this->carregarContratacoesPendentes($processo);
        
        // Carregar dados da ata salva
        $dadosAta = Documento::where('processo_id', $processo->id)
            ->where('tipo_documento', 'contrato')
            ->first();

        // Carregar dados do contrato
        $contrato = \App\Models\Contrato::where('processo_id', $processo->id)->first();

        return view('Admin.Atas.show', compact(
            'processo', 
            'dadosAtas', 
            'contratacoes',
            'dadosAta',
            'contrato'
        ));
    }

    public function getLotesDisponiveis(Processo $processo, $vencedorId)
    {
        try {
            Log::info('Buscando lotes disponíveis', [
                'processo_id' => $processo->id,
                'vencedor_id' => $vencedorId
            ]);

            // Verificar se o vencedor pertence ao processo
            $vencedor = \App\Models\Vencedor::where('id', $vencedorId)
                ->where('processo_id', $processo->id)
                ->first();

            if (!$vencedor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vencedor não encontrado neste processo.'
                ], 404);
            }

            // Buscar lotes do vencedor
            $lotes = Lote::where('vencedor_id', $vencedorId)
                ->with(['estoque' => function($query) use ($processo) {
                    $query->where('processo_id', $processo->id);
                }])
                ->get()
                ->map(function($lote) use ($processo) {
                    // Calcular quantidade total contratada para este lote no processo
                    $quantidadeContratada = LoteContratado::where('lote_id', $lote->id)
                        ->where('processo_id', $processo->id)
                        ->whereIn('status', ['PENDENTE', 'CONTRATADO'])
                        ->sum('quantidade_contratada');
                    
                    // Calcular quantidade disponível
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
                    // Filtrar apenas lotes com quantidade disponível > 0
                    return $lote['quantidade_disponivel'] > 0;
                })
                ->values();

            Log::info('Lotes disponíveis encontrados', [
                'processo_id' => $processo->id,
                'vencedor_id' => $vencedorId,
                'quantidade' => $lotes->count()
            ]);

            return response()->json([
                'success' => true,
                'lotes' => $lotes,
                'vencedor' => [
                    'id' => $vencedor->id,
                    'razao_social' => $vencedor->razao_social,
                    'cnpj' => $vencedor->cnpj
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao obter lotes disponíveis', [
                'processo_id' => $processo->id,
                'vencedor_id' => $vencedorId,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter lotes disponíveis: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Criar contratação direta do modal
     */
    public function criarContratacaoDireta(Request $request, Processo $processo)
    {
        try {
            $request->validate([
                'vencedor_id' => 'required|exists:vencedores,id',
                'lote_id' => 'required|exists:lotes,id',
                'quantidade' => 'required|numeric|min:0.01'
            ]);

            $vencedorId = $request->input('vencedor_id');
            $loteId = $request->input('lote_id');
            $quantidade = (float) $request->input('quantidade');

            // Verificar se o lote pertence ao vencedor
            $lote = Lote::where('id', $loteId)
                ->where('vencedor_id', $vencedorId)
                ->firstOrFail();

            // Calcular quantidade já contratada
            $quantidadeContratada = LoteContratado::where('lote_id', $loteId)
                ->where('processo_id', $processo->id)
                ->whereIn('status', ['PENDENTE', 'CONTRATADO'])
                ->sum('quantidade_contratada');

            $quantidadeDisponivel = max(0, (float) $lote->quantidade - (float) $quantidadeContratada);
            
            if ($quantidade > $quantidadeDisponivel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quantidade solicitada excede o disponível. Disponível: ' . number_format($quantidadeDisponivel, 2, ',', '.')
                ], 400);
            }

            // Criar contratação
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

            // Atualizar estoque (se existir)
            $this->atualizarEstoque($processo, $lote, $quantidade);

            Log::info('Contratação criada com sucesso', [
                'processo_id' => $processo->id,
                'lote_id' => $loteId,
                'quantidade' => $quantidade,
                'contratacao_id' => $contratacao->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contratação criada com sucesso!',
                'contratacao' => [
                    'id' => $contratacao->id,
                    'item' => $lote->item,
                    'quantidade' => $contratacao->quantidade_contratada,
                    'valor_total' => $contratacao->valor_total
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao criar contratação', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar contratação: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar status da contratação para CONTRATADO
     */
    public function marcarComoContratado(Request $request, Processo $processo)
    {
        try {
            $contratacaoIds = $request->input('contratacoes', []);
            
            LoteContratado::whereIn('id', $contratacaoIds)
                ->where('processo_id', $processo->id)
                ->update(['status' => 'CONTRATADO']);

            Log::info('Contratações marcadas como CONTRATADO', [
                'processo_id' => $processo->id,
                'quantidade' => count($contratacaoIds)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contratações marcadas como CONTRATADO!'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao marcar contratações como CONTRATADO', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Salvar campo individual da ata (mesmo do contrato)
     */
    public function salvarCampoContrato(Request $request, Processo $processo)
    {
        try {
            $request->validate([
                'campo' => 'required|string',
                'valor' => 'nullable|string'
            ]);

            $campo = $request->input('campo');
            $valor = $request->input('valor');

            // Usar os mesmos campos permitidos do contrato
            $camposPermitidos = [
                'numero_contrato',
                'data_assinatura_contrato',
                'numero_extrato', 
                'comarca',
                'fonte_recurso',
                'subcontratacao'
            ];

            if (!in_array($campo, $camposPermitidos)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Campo não permitido.'
                ], 400);
            }

            // Verificar se já existe um contrato para este processo
            $contrato = \App\Models\Contrato::where('processo_id', $processo->id)->first();

            if (!$contrato) {
                $contrato = \App\Models\Contrato::create([
                    'processo_id' => $processo->id
                ]);
            }

            // Processar campo específico (data)
            if ($campo === 'data_assinatura_contrato' && $valor) {
                $valor = \Carbon\Carbon::parse($valor)->format('Y-m-d');
            }

            // Atualizar o campo
            $contrato->update([$campo => $valor]);

            Log::info('Campo da ata salvo com sucesso', [
                'processo_id' => $processo->id,
                'campo' => $campo,
                'valor' => $valor
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Campo salvo com sucesso.',
                'data' => [$campo => $valor]
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao salvar campo da ata', [
                'processo_id' => $processo->id,
                'campo' => $request->input('campo'),
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar campo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter dados salvos do contrato/ata
     */
    public function getDadosAta(Processo $processo)
    {
        try {
            $contrato = \App\Models\Contrato::where('processo_id', $processo->id)->first();

            if (!$contrato) {
                return response()->json([
                    'success' => true,
                    'dados' => []
                ]);
            }

            return response()->json([
                'success' => true,
                'dados' => [
                    'numero_contrato' => $contrato->numero_contrato,
                    'data_assinatura_contrato' => $contrato->data_assinatura_contrato,
                    'numero_extrato' => $contrato->numero_extrato,
                    'comarca' => $contrato->comarca,
                    'fonte_recurso' => $contrato->fonte_recurso,
                    'subcontratacao' => $contrato->subcontratacao,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao obter dados do contrato/ata', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter dados.'
            ], 500);
        }
    }

    /**
     * Salvar assinantes da ata
     */
    public function salvarAssinantesAta(Request $request, Processo $processo)
    {
        try {
            $assinantes = $request->input('assinantes', []);
            
            // Buscar documento existente
            $documento = Documento::where('processo_id', $processo->id)
                ->where('tipo_documento', 'contrato')
                ->first();
                
            if ($documento) {
                $documento->update([
                    'assinantes' => json_encode($assinantes)
                ]);
            } else {
                Documento::create([
                    'processo_id' => $processo->id,
                    'tipo_documento' => 'contrato',
                    'assinantes' => json_encode($assinantes),
                    'gerado_em' => now()
                ]);
            }

            Log::info('Assinantes da ata salvos com sucesso', [
                'processo_id' => $processo->id,
                'quantidade' => count($assinantes)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Assinantes salvos com sucesso.'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao salvar assinantes da ata', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar assinantes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Salvar contratações selecionadas
     */
    public function salvarContratacoesSelecionadas(Request $request, Processo $processo)
    {
        try {
            $contratacoesSelecionadas = $request->input('contratacoes_selecionadas', []);
            
            // Buscar documento existente
            $documento = Documento::where('processo_id', $processo->id)
                ->where('tipo_documento', 'contrato')
                ->first();
                
            if ($documento) {
                $documento->update([
                    'contratacoes_selecionadas' => json_encode($contratacoesSelecionadas)
                ]);
            } else {
                Documento::create([
                    'processo_id' => $processo->id,
                    'tipo_documento' => 'contrato',
                    'contratacoes_selecionadas' => json_encode($contratacoesSelecionadas),
                    'gerado_em' => now()
                ]);
            }

            Log::info('Contratações selecionadas salvas com sucesso', [
                'processo_id' => $processo->id,
                'quantidade' => count($contratacoesSelecionadas)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contratações selecionadas salvas com sucesso.'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao salvar contratações selecionadas', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar contratações: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Gerar e salvar ata (sem download)
     */
    public function gerarESalvarAta(Processo $processo, Request $request)
    {
        try {
            $contratacoesIds = $request->input('contratacoes_selecionadas', []);
            $campos = $request->input('campos', []);
            $dataSelecionada = $request->input('data') ?? now()->format('Y-m-d');
            $assinantes = $request->input('assinantes', []);
            
            // Preparar dados
            $dados = $this->prepararDadosParaPdf($processo, $contratacoesIds);
            
            // Adicionar campos personalizados
            if (!empty($campos)) {
                $dados['campos'] = array_merge($dados['campos'] ?? [], $campos);
            }
            
            // Adicionar assinantes aos dados
            if (!empty($assinantes)) {
                $dados['assinantes'] = $assinantes;
                $dados['hasSelectedAssinantes'] = true;
                
                // Garantir que o primeiro assinante existe
                $dados['primeiroAssinante'] = [
                    'responsavel' => $assinantes[0]['responsavel'] ?? 'Responsável não informado',
                    'unidade_nome' => $assinantes[0]['unidade_nome'] ?? 'Unidade não informada',
                    'cargo' => $assinantes[0]['cargo'] ?? ''
                ];
            }
            
            $viewAta = $this->determinarViewContrato($processo);

            // Gerar PDF
            $pdf = Pdf::loadView($viewAta, $dados)
                ->setPaper('a4', 'portrait');

            // Salvar arquivo
            $caminho = $this->salvarArquivo($processo, $pdf);
            
            // Salvar no banco (incluindo assinantes)
            $this->salvarDocumento($processo, $caminho, $contratacoesIds, $dataSelecionada, $campos, $assinantes);
            
            // Atualizar status das contratações para CONTRATADO
            LoteContratado::whereIn('id', $contratacoesIds)
                ->where('processo_id', $processo->id)
                ->update(['status' => 'CONTRATADO']);
            
            Log::info('Ata gerada com sucesso e contratações atualizadas', ['processo_id' => $processo->id]);

            return response()->json([
                'success' => true,
                'message' => '✅ Ata gerada com sucesso!',
                'documento' => 'contrato',
                'download_url' => url("admin/atas/{$processo->id}/download")
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao gerar ata', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => '❌ Erro ao gerar Ata: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Dashboard de atas
     */
    public function dashboard(Request $request)
    {
        $prefeituraId = $request->get('prefeitura_id');
        
        $query = Processo::query()
            ->with([
                'prefeitura',
                'lotesContratados' => function($query) {
                    $query->whereIn('status', ['PENDENTE', 'CONTRATADO']);
                }
            ]);

        if ($prefeituraId) {
            $query->where('prefeitura_id', $prefeituraId);
        }

        $processos = $query->orderBy('created_at', 'desc')->get();

        $estatisticas = $this->calcularEstatisticas($processos);
        $prefeituras = Prefeitura::all();

        return view('Admin.Atas.dashboard', compact('processos', 'estatisticas', 'prefeituras', 'prefeituraId'));
    }

    /**
     * Download da Ata
     */
    public function downloadAta(Processo $processo)
    {
        try {
            $documento = Documento::where('processo_id', $processo->id)
                ->where('tipo_documento', 'contrato')
                ->firstOrFail();

            $caminhoCompleto = public_path($documento->caminho);

            if (!file_exists($caminhoCompleto)) {
                throw new \Exception('Arquivo da ata não encontrado.');
            }

            $numeroProcessoLimpo = str_replace(['/', '\\'], '_', $processo->numero_processo);
            $nomeArquivo = "ata_contratacao_{$numeroProcessoLimpo}.pdf";

            return response()->download($caminhoCompleto, $nomeArquivo);

        } catch (\Exception $e) {
            Log::error('Erro ao baixar Ata', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage()
            ]);

            return back()->with('error', 'Erro ao baixar Ata: ' . $e->getMessage());
        }
    }

    private function carregarContratacoesPendentes(Processo $processo)
    {
        return LoteContratado::where('processo_id', $processo->id)
            ->where('status', 'PENDENTE')
            ->with(['lote', 'vencedor'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('vencedor_id');
    }

    private function atualizarEstoque(Processo $processo, Lote $lote, $quantidade)
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

    /**
     * Obter contratações pendentes para a aba de gerar contrato
     */
    public function getContratacoesPendentes(Processo $processo)
    {
        try {
            $contratacoes = LoteContratado::where('processo_id', $processo->id)
                ->where('status', 'PENDENTE')
                ->with(['lote', 'vencedor'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'contratacoes' => $contratacoes
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao obter contratações pendentes', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter contratações pendentes.'
            ], 500);
        }
    }

    /**
     * Obter contratações atualizadas (para atualizar a aba)
     */
    public function getContratacoesAtualizadas(Processo $processo)
    {
        try {
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

            return response()->json([
                'success' => true,
                'html' => $html,
                'totalItens' => LoteContratado::where('processo_id', $processo->id)
                    ->where('status', 'PENDENTE')
                    ->count(),
                'valorTotal' => LoteContratado::where('processo_id', $processo->id)
                    ->where('status', 'PENDENTE')
                    ->sum('valor_total')
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao obter contratações atualizadas', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter contratações atualizadas.'
            ], 500);
        }
    }

    private function prepararDadosParaPdf(Processo $processo, array $contratacoesIds = [])
    {
        $processo->load([
            'prefeitura',
            'lotes.vencedor',
            'lotes.contratados' => function ($query) use ($processo) {
                $query->where('processo_id', $processo->id);
            },
            'vencedores',
            'finalizacao'
        ]);

        // Filtrar contratações selecionadas
        $contratacoes = collect();
        if (!empty($contratacoesIds)) {
            $contratacoesSelecionadas = LoteContratado::whereIn('id', $contratacoesIds)
                ->where('processo_id', $processo->id)
                ->get();
            
            $lotesComContratacoes = $contratacoesSelecionadas->pluck('lote_id')->unique();
            $processo->lotes = $processo->lotes->whereIn('id', $lotesComContratacoes);
            
            $contratacoes = LoteContratado::where('processo_id', $processo->id)
                ->with(['lote', 'vencedor'])
                ->where('status', 'PENDENTE')
                ->get();
        }

        $valorTotalContrato = $contratacoes->sum('valor_total');
        $contratoSalvo = \App\Models\Contrato::where('processo_id', $processo->id)->first();
        $quantidadeTotalContrato = $contratacoes->sum('quantidade_contratada');
        
        // Carregar dados da ata salva
        $dadosAta = Documento::where('processo_id', $processo->id)
            ->where('tipo_documento', 'contrato')
            ->first();
        
        // Decodificar assinantes se existirem
        $assinantesAta = $dadosAta ? json_decode($dadosAta->assinantes ?? '[]', true) : [];

        return [
            'processo' => $processo,
            'prefeitura' => $processo->prefeitura,
            'dadosAta' => $this->prepararDadosAtaApenasSelecionados($processo, $contratacoesIds),
            'dataGeracao' => now()->format('d/m/Y H:i:s'),
            'dataSelecionada' => now()->format('Y-m-d'),
            'temContratacoesSelecionadas' => !empty($contratacoesIds),
            'contratacoes' => $contratacoes,
            'itensTabela' => $this->prepararItensParaTabela($contratacoes),
            'valorTotalContrato' => $valorTotalContrato,
            'quantidadeTotalContrato' => $quantidadeTotalContrato,
            'valorTotalPorExtenso' => $this->escreverValorPorExtenso($valorTotalContrato),
            'dadosContratante' => $this->prepararDadosContratante($processo),
            'dadosContratado' => $this->prepararDadosContratado($processo, $contratacoes),
            
            // DADOS DO CONTRATO (REUSADOS)
            'contratoSalvo' => $contratoSalvo,
            'dataAssinaturaFormatada' => $contratoSalvo && $contratoSalvo->data_assinatura_contrato 
                ? \Carbon\Carbon::parse($contratoSalvo->data_assinatura_contrato)->format('d/m/Y')
                : null,
            'assinantes' => $assinantesAta,
            'primeiroAssinante' => count($assinantesAta) > 0 ? [
                'responsavel' => $assinantesAta[0]['responsavel'] ?? 'Responsável não informado',
                'unidade_nome' => $assinantesAta[0]['unidade_nome'] ?? 'Unidade não informada'
            ] : [
                'responsavel' => $processo->finalizacao->responsavel ?? $processo->prefeitura->autoridade_competente ?? 'Responsável não informado',
                'unidade_nome' => $processo->finalizacao->orgao_responsavel ?? $processo->prefeitura->cidade ?? 'Unidade não informada'
            ],
            'hasSelectedAssinantes' => count($assinantesAta) > 0,
            
            // CAMPOS DO CONTRATO (REUSADOS)
            'campos' => $contratoSalvo ? [
                'numero_contrato' => $contratoSalvo->numero_contrato,
                'data_assinatura_contrato' => $contratoSalvo->data_assinatura_contrato,
                'numero_extrato' => $contratoSalvo->numero_extrato,
                'comarca' => $contratoSalvo->comarca,
                'fonte_recurso' => $contratoSalvo->fonte_recurso,
                'subcontratacao' => $contratoSalvo->subcontratacao,
            ] : [],
        ];
    }

    private function prepararDadosAtaApenasSelecionados(Processo $processo, array $contratacoesIds = []): array
    {
        if (empty($contratacoesIds)) {
            return [];
        }

        $contratacoesSelecionadas = LoteContratado::whereIn('id', $contratacoesIds)
            ->where('processo_id', $processo->id)
            ->with('lote.vencedor')
            ->get();

        $dados = [];
        foreach ($contratacoesSelecionadas as $contratacao) {
            $lote = $contratacao->lote;
            if (!$lote) continue;

            $estoque = EstoqueLote::where('lote_id', $lote->id)
                ->where('processo_id', $processo->id)
                ->first();
            
            $quantidadeDisponivel = $estoque ? (float) $estoque->quantidade_disponivel : (float) $lote->quantidade;
            $quantidadeUtilizada = $estoque ? (float) $estoque->quantidade_utilizada : 0;
            $quantidadeContratada = (float) $contratacao->quantidade_contratada;
            
            $dados[] = [
                'vencedor' => $lote->vencedor?->razao_social ?? 'Não definido',
                'item' => $lote->item,
                'descricao' => $lote->descricao,
                'unidade' => $lote->unidade,
                'quantidade_total' => (float) $lote->quantidade,
                'quantidade_contratada' => $quantidadeContratada,
                'quantidade_disponivel' => $quantidadeDisponivel,
                'quantidade_utilizada' => $quantidadeUtilizada,
                'valor_unitario' => (float) $lote->vl_unit,
                'valor_total_contratado' => $quantidadeContratada * (float) $lote->vl_unit,
                'valor_total_disponivel' => $quantidadeDisponivel * (float) $lote->vl_unit,
                'percentual_utilizado' => (float) $lote->quantidade > 0 
                    ? round(($quantidadeUtilizada / (float) $lote->quantidade) * 100, 2)
                    : 0,
                'status' => $quantidadeDisponivel > 0 ? 'PARCIAL' : 'ESGOTADO',
                'tem_contratacao' => $quantidadeUtilizada > 0,
                'contratacao_id' => $contratacao->id,
                'lote_id' => $lote->id,
                'vencedor_id' => $lote->vencedor_id,
            ];
        }

        usort($dados, function($a, $b) {
            if ($a['vencedor'] === $b['vencedor']) {
                return strcmp($a['item'], $b['item']);
            }
            return strcmp($a['vencedor'], $b['vencedor']);
        });

        return $dados;
    }

    private function prepararDadosAtaTodosLotes(Processo $processo): array
    {
        $dados = [];
        
        foreach ($processo->lotes as $lote) {
            $quantidadeContratada = $lote->contratados
                ->where('processo_id', $processo->id)
                ->sum('quantidade_contratada');
            
            $estoque = EstoqueLote::where('lote_id', $lote->id)
                ->where('processo_id', $processo->id)
                ->first();
            
            $quantidadeDisponivel = $estoque ? (float) $estoque->quantidade_disponivel : (float) $lote->quantidade;
            $quantidadeUtilizada = $estoque ? (float) $estoque->quantidade_utilizada : 0;
            
            if ($quantidadeContratada == 0) {
                $quantidadeContratada = (float) $lote->quantidade;
            }
            
            $dados[] = [
                'vencedor' => $lote->vencedor?->razao_social ?? 'Não definido',
                'item' => $lote->item,
                'descricao' => $lote->descricao,
                'unidade' => $lote->unidade,
                'quantidade_total' => (float) $lote->quantidade,
                'quantidade_contratada' => $quantidadeContratada,
                'quantidade_disponivel' => $quantidadeDisponivel,
                'quantidade_utilizada' => $quantidadeUtilizada,
                'valor_unitario' => (float) $lote->vl_unit,
                'valor_total_contratado' => $quantidadeContratada * (float) $lote->vl_unit,
                'valor_total_disponivel' => $quantidadeDisponivel * (float) $lote->vl_unit,
                'percentual_utilizado' => (float) $lote->quantidade > 0 
                    ? round(($quantidadeUtilizada / (float) $lote->quantidade) * 100, 2)
                    : 0,
                'status' => $quantidadeDisponivel > 0 ? 'PARCIAL' : 'ESGOTADO',
                'tem_contratacao' => $quantidadeUtilizada > 0,
            ];
        }

        usort($dados, function($a, $b) {
            if ($a['vencedor'] === $b['vencedor']) {
                return strcmp($a['item'], $b['item']);
            }
            return strcmp($a['vencedor'], $b['vencedor']);
        });

        return $dados;
    }

    private function salvarArquivo(Processo $processo, $pdf)
    {
        $numeroProcessoLimpo = str_replace(['/', '\\'], '_', $processo->numero_processo);
        $diretorio = public_path("uploads/atas/{$processo->id}");
        
        if (!file_exists($diretorio)) {
            mkdir($diretorio, 0777, true);
        }
        
        $nomeArquivo = "ata_{$numeroProcessoLimpo}_" . now()->format('Ymd_His') . '.pdf';
        $caminhoCompleto = "{$diretorio}/{$nomeArquivo}";
        $caminhoRelativo = "uploads/atas/{$processo->id}/{$nomeArquivo}";
        
        $pdf->save($caminhoCompleto);

        return [
            'completo' => $caminhoCompleto,
            'relativo' => $caminhoRelativo,
            'nome' => $nomeArquivo
        ];
    }

    private function salvarDocumento(Processo $processo, $caminho, $contratacoesIds = [], $dataSelecionada = null, $campos = [], $assinantes = [])
    {
        $dataSelecionada = $dataSelecionada ?? now()->format('Y-m-d');

        $documentoExistente = Documento::where('processo_id', $processo->id)
            ->where('tipo_documento', 'contrato')
            ->first();

        $dadosDocumento = [
            'processo_id' => $processo->id,
            'tipo_documento' => 'contrato',
            'data_selecionada' => $dataSelecionada,
            'caminho' => $caminho['relativo'],
            'gerado_em' => now(),
        ];

        // Salvar campos adicionais se existirem
        if (!empty($campos)) {
            $dadosDocumento['campos'] = json_encode($campos);
        }
        
        // Salvar assinantes se existirem
        if (!empty($assinantes)) {
            $dadosDocumento['assinantes'] = json_encode($assinantes);
        }

        // Salvar contratações selecionadas
        if (!empty($contratacoesIds)) {
            $dadosDocumento['contratacoes_selecionadas'] = json_encode($contratacoesIds);
        }

        if ($documentoExistente) {
            $caminhoAntigo = public_path($documentoExistente->caminho);
            if (file_exists($caminhoAntigo)) {
                unlink($caminhoAntigo);
            }

            $documentoExistente->update($dadosDocumento);
        } else {
            Documento::create($dadosDocumento);
        }
    }

    private function calcularEstatisticas($processos)
    {
        return [
            'total_processos' => $processos->count(),
            'total_contratacoes' => $processos->sum(function($processo) {
                return $processo->lotesContratados->count();
            }),
            'total_valor_contratado' => $processos->sum(function($processo) {
                return $processo->lotesContratados->sum('valor_total');
            }),
            'total_quantidade_contratada' => $processos->sum(function($processo) {
                return $processo->lotesContratados->sum('quantidade_contratada');
            }),
            'total_lotes' => $processos->sum(function($processo) {
                return $processo->lotes->count();
            }),
        ];
    }

    /**
     * Métodos auxiliares para dados do contrato
     */
    private function prepararDadosContratante(Processo $processo): array
    {
        $dados = [
            'orgao' => $processo->finalizacao->orgao_responsavel ?? $processo->prefeitura->cidade,
            'cidade' => $processo->prefeitura->cidade,
            'uf' => $processo->prefeitura->uf,
            'endereco' => $processo->prefeitura->endereco,
            'cnpj' => $processo->finalizacao->cnpj ?? $processo->prefeitura->cnpj,
            'responsavel' => $processo->finalizacao->responsavel ?? $processo->prefeitura->autoridade_competente,
            'cargo_responsavel' => $processo->finalizacao->cargo_responsavel ?? 'Prefeito Municipal',
            'cpf_responsavel' => $processo->finalizacao->cpf_responsavel ?? null,
        ];
        
        $dados['cnpj_formatado'] = $this->formatarCNPJ($dados['cnpj']);
        $dados['cpf_responsavel_formatado'] = $dados['cpf_responsavel'] 
            ? $this->formatarCPF($dados['cpf_responsavel'])
            : null;

        return $dados;
    }

    private function prepararDadosContratado(Processo $processo, $contratacoes): array
    {
        if ($processo->finalizacao && $processo->finalizacao->cnpj_empresa_vencedora) {
            return [
                'razao_social' => $processo->finalizacao->razao_social ?? 'XXXXXXXXXXXXX',
                'cnpj' => $processo->finalizacao->cnpj_empresa_vencedora,
                'cnpj_formatado' => $this->formatarCNPJ($processo->finalizacao->cnpj_empresa_vencedora),
                'endereco' => $processo->finalizacao->endereco ?? 'Endereço não informado',
                'representante' => $processo->finalizacao->representante_legal_empresa ?? 'Representante não informado',
                'cpf_representante' => $processo->finalizacao->cpf_representante ?? null,
                'cpf_representante_formatado' => $processo->finalizacao->cpf_representante 
                    ? $this->formatarCPF($processo->finalizacao->cpf_representante)
                    : null,
                'fonte_dados' => 'finalizacao',
            ];
        }
        
        if ($contratacoes->count() > 0) {
            $primeiroVencedor = $contratacoes->first()->vencedor;
            
            if ($primeiroVencedor) {
                return [
                    'razao_social' => $primeiroVencedor->razao_social,
                    'cnpj' => $primeiroVencedor->cnpj,
                    'cnpj_formatado' => $this->formatarCNPJ($primeiroVencedor->cnpj),
                    'endereco' => $primeiroVencedor->endereco,
                    'representante' => $primeiroVencedor->representante ?? 'Representante não informado',
                    'cpf_representante' => $primeiroVencedor->cpf ?? null,
                    'cpf_representante_formatado' => $primeiroVencedor->cpf 
                        ? $this->formatarCPF($primeiroVencedor->cpf)
                        : null,
                    'fonte_dados' => 'vencedor',
                ];
            }
        }
        
        return [
            'razao_social' => 'XXXXXXXXXXXXX',
            'cnpj' => 'XX.XXX.XXX/XXXX-XX',
            'cnpj_formatado' => 'XX.XXX.XXX/XXXX-XX',
            'endereco' => 'Endereço não informado',
            'representante' => 'Representante não informado',
            'cpf_representante' => 'XXX.XXX.XXX-XX',
            'cpf_representante_formatado' => 'XXX.XXX.XXX-XX',
            'fonte_dados' => 'fallback',
        ];
    }

    private function prepararItensParaTabela($contratacoes): array
    {
        $itens = [];
        $itemNumero = 1;
        
        foreach ($contratacoes as $contratacao) {
            if ($contratacao->lote) {
                $itens[] = [
                    'item' => $itemNumero++,
                    'especificacao' => $contratacao->lote->descricao ?? 'Não especificado',
                    'unidade_medida' => $contratacao->lote->unidade ?? '',
                    'quantidade' => number_format($contratacao->quantidade_contratada, 2, ',', '.'),
                    'valor_unitario' => 'R$ ' . number_format($contratacao->valor_unitario, 2, ',', '.'),
                    'valor_total' => 'R$ ' . number_format($contratacao->valor_total, 2, ',', '.'),
                ];
            }
        }
        
        return $itens;
    }

    private function escreverValorPorExtenso($valor): string
    {
        if (is_string($valor)) {
            $valor = preg_replace('/[^0-9,.]/', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }
        
        $valor = floatval($valor);
        
        if (class_exists(\App\Helpers\ValorPorExtenso::class)) {
            return \App\Helpers\ValorPorExtenso::escrever($valor);
        }
        
        return number_format($valor, 2, ',', '.') . ' reais';
    }

    private function determinarViewContrato(Processo $processo): string
    {
        $viewBase = "Admin.Processos.contrato";
        $modalidade = $this->formatarNomeArquivo($processo->modalidade?->name ?? '');
        $view = "{$viewBase}.{$modalidade}.contrato";

        if (!view()->exists($view)) {
            throw new \Exception("Modelo de contrato para '{$modalidade}' não encontrado.");
        }

        return $view;
    }

    private function formatarNomeArquivo(string $nome): string
    {
        $nome = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $nome));
        return str_replace(' ', '_', $nome);
    }

    private function formatarCNPJ($cnpj): string
    {
        if (!$cnpj) return 'XX.XXX.XXX/XXXX-XX';
        
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        
        if (strlen($cnpj) === 14) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpj);
        }
        
        return $cnpj;
    }

    private function formatarCPF($cpf): string
    {
        if (!$cpf) return 'XXX.XXX.XXX-XX';
        
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        if (strlen($cpf) === 11) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
        }
        
        return $cpf;
    }
}