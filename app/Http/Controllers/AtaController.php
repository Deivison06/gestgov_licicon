<?php

namespace App\Http\Controllers;

use App\Models\Lote;
use App\Models\Processo;
use App\Models\Documento;
use App\Models\Prefeitura;
use App\Models\EstoqueLote;
use Illuminate\Http\Request;
use App\Models\LoteContratado;
use setasign\Fpdi\Tcpdf\Fpdi;
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
        
        // Carregar documentos (contratos) já gerados
        $documentos = Documento::where('processo_id', $processo->id)
            ->where('tipo_documento', 'contrato')
            ->orderBy('gerado_em', 'desc')
            ->get();
        
        // Carregar dados da ata salva
        $dadosAta = Documento::where('processo_id', $processo->id)
            ->where('tipo_documento', 'contrato')
            ->first();

        // Carregar dados do contrato
        $contrato = \App\Models\Contrato::where('processo_id', $processo->id)->first();
        
        // Calcular estatísticas para os cards
        $totalContratacoes = LoteContratado::where('processo_id', $processo->id)->count();
        $valorTotalContratado = LoteContratado::where('processo_id', $processo->id)->sum('valor_total');
        $totalContratos = $documentos->count();

        return view('Admin.Atas.show', compact(
            'processo', 
            'dadosAtas', 
            'contratacoes',
            'documentos',
            'dadosAta',
            'contrato',
            'totalContratacoes',
            'valorTotalContratado',
            'totalContratos'
        ));
    }
    public function getItensContrato(Processo $processo, $documentoId)
    {
        try {
            $documento = Documento::where('id', $documentoId)
                ->where('processo_id', $processo->id)
                ->where('tipo_documento', 'contrato')
                ->firstOrFail();
            
            $contratacoesIds = json_decode($documento->contratacoes_selecionadas ?? '[]', true);
            
            $contratacoes = LoteContratado::whereIn('id', $contratacoesIds)
                ->where('processo_id', $processo->id)
                ->with(['lote', 'vencedor'])
                ->get();
            
            $itens = [];
            $totalContrato = 0;
            
            foreach ($contratacoes as $contratacao) {
                if ($contratacao->lote) {
                    $totalContrato += $contratacao->valor_total;
                    $itens[] = [
                        'item' => $contratacao->lote->item,
                        'descricao' => $contratacao->lote->descricao,
                        'vencedor' => $contratacao->vencedor->razao_social,
                        'quantidade' => number_format($contratacao->quantidade_contratada, 2, ',', '.'),
                        'valor_unitario' => 'R$ ' . number_format($contratacao->valor_unitario, 2, ',', '.'),
                        'valor_total' => 'R$ ' . number_format($contratacao->valor_total, 2, ',', '.'),
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'itens' => $itens,
                'total_contrato' => 'R$ ' . number_format($totalContrato, 2, ',', '.')
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao obter itens do contrato', [
                'processo_id' => $processo->id,
                'documento_id' => $documentoId,
                'erro' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter itens do contrato.'
            ], 500);
        }
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

    public function gerarESalvarAta(Processo $processo, Request $request)
    {
        try {
            $contratacoesIds = $request->input('contratacoes_selecionadas', []);
            $campos = $request->input('campos', []);
            $dataSelecionada = $request->input('data') ?? now()->format('Y-m-d');
            $assinantes = $request->input('assinantes', []);
            
            // VALIDAÇÃO 1: Verificar se há contratações selecionadas
            if (empty($contratacoesIds)) {
                return response()->json([
                    'success' => false,
                    'message' => '❌ Selecione pelo menos uma contratação para gerar o contrato.'
                ], 400);
            }
            
            // VALIDAÇÃO 2: Verificar campos obrigatórios
            $camposObrigatorios = ['numero_contrato', 'data_assinatura_contrato', 'comarca', 'fonte_recurso'];
            foreach ($camposObrigatorios as $campo) {
                if (empty($campos[$campo] ?? '')) {
                    return response()->json([
                        'success' => false,
                        'message' => "❌ O campo '{$this->getNomeCampo($campo)}' é obrigatório."
                    ], 400);
                }
            }
            
            // VALIDAÇÃO 3: Verificar se o número do contrato já existe
            $numeroContratoExistente = Documento::where('processo_id', $processo->id)
                ->whereJsonContains('campos->numero_contrato', $campos['numero_contrato'] ?? '')
                ->first();
                
            if ($numeroContratoExistente) {
                return response()->json([
                    'success' => false,
                    'message' => '❌ Este número de contrato já foi utilizado. Use um número diferente.'
                ], 400);
            }
            
            // VALIDAÇÃO 4: Verificar assinantes
            if (empty($assinantes)) {
                return response()->json([
                    'success' => false,
                    'message' => '❌ Adicione pelo menos um assinante ao contrato.'
                ], 400);
            }
            
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

            // Salvar arquivo TEMPORÁRIO
            $caminhoTemp = $this->salvarArquivoTemporario($pdf, $processo);
            
            // Aplicar carimbo automaticamente
            $caminhoCarimbado = $this->criarContratoCarimbado($caminhoTemp['completo'], $processo);
            
            if (!$caminhoCarimbado) {
                throw new \Exception('Falha ao aplicar carimbo ao contrato.');
            }
            
            // Mover o arquivo carimbado para o destino final
            $caminhoFinal = $this->moverParaDestinoFinal($caminhoCarimbado, $processo);
            
            // Salvar no banco (incluindo assinantes)
            $this->salvarDocumento($processo, $caminhoFinal, $contratacoesIds, $dataSelecionada, $campos, $assinantes);
            
            // Atualizar status das contratações para CONTRATADO
            LoteContratado::whereIn('id', $contratacoesIds)
                ->where('processo_id', $processo->id)
                ->update(['status' => 'CONTRATADO']);
            
            // Criar URL para download automático
            $nomeArquivo = basename($caminhoFinal['relativo']);
            $downloadUrl = url("admin/atas/{$processo->id}/download/{$nomeArquivo}");
            
            Log::info('Contrato gerado com carimbo automático', [
                'processo_id' => $processo->id,
                'numero_contrato' => $campos['numero_contrato'] ?? '',
                'itens_incluidos' => count($contratacoesIds),
                'download_url' => $downloadUrl
            ]);

            return response()->json([
                'success' => true,
                'message' => '✅ Contrato gerado com sucesso! O download começará automaticamente.',
                'documento' => 'contrato',
                'download_url' => $downloadUrl,
                'refresh' => true,
                'auto_download' => true // Flag para download automático no frontend
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao gerar contrato', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => '❌ Erro ao gerar Contrato: ' . $e->getMessage()
            ], 500);
        }
    }

    private function salvarArquivoTemporario($pdf, Processo $processo): array
    {
        $numeroProcessoLimpo = str_replace(['/', '\\'], '_', $processo->numero_processo);
        $diretorioTemp = sys_get_temp_dir() . '/atas_temp';
        
        if (!file_exists($diretorioTemp)) {
            mkdir($diretorioTemp, 0777, true);
        }
        
        $nomeArquivo = "temp_ata_{$numeroProcessoLimpo}_" . now()->format('Ymd_His') . '.pdf';
        $caminhoCompleto = "{$diretorioTemp}/{$nomeArquivo}";
        
        $pdf->save($caminhoCompleto);

        return [
            'completo' => $caminhoCompleto,
            'nome' => $nomeArquivo
        ];
    }
    private function criarContratoCarimbado(string $caminhoOriginal, Processo $processo): ?string
    {
        $paginasTemp = [];

        try {
            $pageCount = $this->contarPaginasPdf($caminhoOriginal);

            if ($pageCount === 0) {
                Log::error('PDF vazio ou inválido para carimbo - Contrato', ['caminho' => $caminhoOriginal]);
                return null;
            }

            // Criar arquivo temporário para o resultado carimbado
            $caminhoCarimbado = tempnam(sys_get_temp_dir(), 'contrato_carimbado_') . '.pdf';

            // OBTER PÁGINA INICIAL DO CONTRATO
            $paginaInicial = $processo->contTotalPage ?? 0;

            for ($pagina = 1; $pagina <= $pageCount; $pagina++) {
                $paginaAtual = $pagina;

                $pdf = new Fpdi();
                $this->configurarFonte($pdf);

                $pdf->setSourceFile($caminhoOriginal);
                $tplId = $pdf->importPage($pagina);
                $pdf->AddPage();
                $pdf->useTemplate($tplId);

                // Aplicar carimbo em todas as páginas
                $this->adicionarCarimbo($pdf, $processo, $paginaAtual, $pageCount, $paginaInicial);

                $tempPath = sys_get_temp_dir() . "/pagina_contrato_{$pagina}_" . uniqid() . '.pdf';
                $pdf->Output($tempPath, 'F');
                $paginasTemp[] = $tempPath;
            }

            $sucesso = $this->mesclarPdfsComGhostscript($paginasTemp, $caminhoCarimbado);

            if ($sucesso && file_exists($caminhoCarimbado) && filesize($caminhoCarimbado) > 0) {
                Log::info('Contrato carimbado criado com sucesso', [
                    'caminho_carimbado' => $caminhoCarimbado,
                    'tamanho' => filesize($caminhoCarimbado),
                    'paginas' => $pageCount
                ]);
                return $caminhoCarimbado;
            } else {
                Log::error('Falha ao criar contrato carimbado');
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Erro ao criar contrato carimbado', [
                'caminho_original' => $caminhoOriginal,
                'erro' => $e->getMessage()
            ]);
            return null;
        } finally {
            // Limpar arquivos temporários
            foreach ($paginasTemp as $tempFile) {
                if (file_exists($tempFile)) {
                    unlink($tempFile);
                }
            }
            // Limpar arquivo original temporário
            if (file_exists($caminhoOriginal)) {
                unlink($caminhoOriginal);
            }
        }
    }

    private function moverParaDestinoFinal(string $caminhoCarimbado, Processo $processo): array
    {
        $numeroProcessoLimpo = str_replace(['/', '\\'], '_', $processo->numero_processo);
        $diretorioFinal = public_path("uploads/atas/{$processo->id}");
        
        if (!file_exists($diretorioFinal)) {
            mkdir($diretorioFinal, 0777, true);
        }
        
        $nomeArquivo = "ata_carimbada_{$numeroProcessoLimpo}_" . now()->format('Ymd_His') . '.pdf';
        $caminhoFinal = "{$diretorioFinal}/{$nomeArquivo}";
        $caminhoRelativo = "uploads/atas/{$processo->id}/{$nomeArquivo}";
        
        // Mover arquivo carimbado para destino final
        rename($caminhoCarimbado, $caminhoFinal);

        return [
            'completo' => $caminhoFinal,
            'relativo' => $caminhoRelativo,
            'nome' => $nomeArquivo
        ];
    }

    private function adicionarCarimbo(Fpdi $pdf, Processo $processo, int $paginaAtual, int $pageCountTotal, int $paginaInicial = 0): void
    {
        $pageWidth = $pdf->GetPageWidth();
        $pageHeight = $pdf->GetPageHeight();

        $boxWidth = 8;
        $boxHeight = 150;

        $x = $pageWidth - $boxWidth - 1;
        $y = ($pageHeight - $boxHeight) / 2;

        $pdf->SetDrawColor(0, 0, 0);
        $pdf->Rect($x, $y, $boxWidth, $boxHeight, 'D');
        $pdf->SetTextColor(0, 0, 0);

        // CALCULAR PÁGINA ABSOLUTA
        $paginaAbsoluta = $paginaInicial + $paginaAtual;
        $totalAbsoluto = $paginaInicial + $pageCountTotal;

        $codigoAutenticacao = $processo->prefeitura->id . now()->format('HisdmY');
        $textoCarimbo = "Processo numerado por: {$processo->responsavel_numeracao} " .
            "Cargo: {$processo->unidade_numeracao} " .
            "Portaria nº {$processo->portaria_numeracao} " .
            "Pág. {$paginaAbsoluta} / {$totalAbsoluto} - " .
            "Documento gerado na Plataforma GestGov - Licenciado para Prefeitura de {$processo->prefeitura->cidade}. " .
            "Cod. de Autenticação: {$codigoAutenticacao} - Para autenticar acesse gestgov.com.br/autenticacao";

        $pdf->StartTransform();
        $rotateX = $x + ($boxWidth / 2);
        $rotateY = $y + ($boxHeight / 2);
        $pdf->Rotate(90, $rotateX, $rotateY);

        $textX = $rotateX - ($boxHeight / 2);
        $textY = $rotateY - ($boxWidth / 2);
        $pdf->SetXY($textX, $textY);

        $pdf->MultiCell($boxHeight, $boxWidth, $textoCarimbo, 0, 'C', false, 1, '', '', true, 0, false, true, 0, 'T', false);
        $pdf->StopTransform();
    }

    private function contarPaginasPdf(string $caminhoPdf): int
    {
        try {
            $pdf = new Fpdi();
            return $pdf->setSourceFile($caminhoPdf);
        } catch (\Exception $e) {
            Log::error('Erro ao contar páginas do PDF', [
                'caminho' => $caminhoPdf,
                'erro' => $e->getMessage()
            ]);
            return 0;
        }
    }

    private function configurarFonte(Fpdi $pdf): void
    {
        $fontPath = public_path('storage/app/public/fonts/Aptos.ttf');
        if (file_exists($fontPath)) {
            $pdf->AddFont('Aptos', '', 'Aptos.ttf', true);
            $pdf->SetFont('Aptos', '', 8);
        } else {
            $pdf->SetFont('helvetica', '', 6);
        }
    }

    private function mesclarPdfsComGhostscript(array $arquivos, string $outputPath): bool
    {
        $listaArquivos = null;

        try {
            $arquivosValidos = [];
            foreach ($arquivos as $index => $arquivo) {
                if (!file_exists($arquivo)) {
                    Log::error('Arquivo não encontrado para mesclagem', ['arquivo' => $arquivo]);
                    return false;
                }

                $tamanho = filesize($arquivo);
                if ($tamanho === 0) {
                    Log::error('Arquivo vazio encontrado', ['arquivo' => $arquivo]);
                    return false;
                }

                $arquivosValidos[] = $arquivo;
            }

            $listaArquivos = tempnam(sys_get_temp_dir(), 'gs_list_');
            file_put_contents($listaArquivos, implode("\n", $arquivosValidos));

            $comando = sprintf(
                'gs -dBATCH -dNOPAUSE -q -sDEVICE=pdfwrite -dPDFSETTINGS=/prepress -sOutputFile="%s" @"%s"',
                $outputPath,
                $listaArquivos
            );

            $output = [];
            $returnCode = 0;
            exec($comando . ' 2>&1', $output, $returnCode);

            sleep(1);

            $outputExiste = file_exists($outputPath);
            $outputTamanho = $outputExiste ? filesize($outputPath) : 0;

            if ($returnCode === 0 && $outputExiste && $outputTamanho > 0) {
                return true;
            } else {
                Log::error('Erro ao mesclar PDFs com Ghostscript', [
                    'return_code' => $returnCode,
                    'arquivo_saida_existe' => $outputExiste,
                    'arquivo_saida_tamanho' => $outputTamanho
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Exceção ao mesclar PDFs com Ghostscript', [
                'erro' => $e->getMessage()
            ]);
            return false;
        } finally {
            if ($listaArquivos && file_exists($listaArquivos)) {
                unlink($listaArquivos);
            }
        }
    }

    private function getNomeCampo($campo): string
    {
        $nomes = [
            'numero_contrato' => 'Número do Contrato',
            'data_assinatura_contrato' => 'Data de Assinatura',
            'comarca' => 'Comarca',
            'fonte_recurso' => 'Fonte de Recurso'
        ];
        
        return $nomes[$campo] ?? $campo;
    }

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

    public function downloadAta(Processo $processo, $nomeArquivo = null)
    {
        try {
            if ($nomeArquivo) {
                // Download de arquivo específico (usado para auto-download)
                $caminhoCompleto = public_path("uploads/atas/{$processo->id}/{$nomeArquivo}");
                
                if (!file_exists($caminhoCompleto)) {
                    throw new \Exception('Arquivo não encontrado.');
                }
                
                return response()->download($caminhoCompleto, $nomeArquivo);
            }
            
            // Download do último arquivo (compatibilidade)
            $documento = Documento::where('processo_id', $processo->id)
                ->where('tipo_documento', 'contrato')
                ->latest('gerado_em')
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

    private function salvarDocumento(Processo $processo, $caminho, $contratacoesIds = [], $dataSelecionada = null, $campos = [], $assinantes = [])
    {
        $dataSelecionada = $dataSelecionada ?? now()->format('Y-m-d');
        
        Log::info('Salvando documento com carimbo', [
            'processo_id' => $processo->id,
            'campos_recebidos' => $campos,
            'numero_contrato_recebido' => $campos['numero_contrato'] ?? 'NÃO RECEBIDO',
            'quantidade_campos' => count($campos),
            'contratacoes_ids' => $contratacoesIds,
            'caminho_relativo' => $caminho['relativo']
        ]);
        
        // Calcular valor total do contrato
        $valorTotalContrato = LoteContratado::whereIn('id', $contratacoesIds)
            ->where('processo_id', $processo->id)
            ->sum('valor_total');
        
        // Calcular quantidade de itens
        $quantidadeItens = count($contratacoesIds);
        
        // Salvar dados no modelo Contrato também
        if (!empty($campos) && isset($campos['numero_contrato'])) {
            $contrato = \App\Models\Contrato::where('processo_id', $processo->id)->first();
            
            if (!$contrato) {
                $contrato = \App\Models\Contrato::create([
                    'processo_id' => $processo->id,
                    'numero_contrato' => $campos['numero_contrato'] ?? null,
                    'data_assinatura_contrato' => $campos['data_assinatura_contrato'] ?? null,
                    'numero_extrato' => $campos['numero_extrato'] ?? null,
                    'comarca' => $campos['comarca'] ?? null,
                    'fonte_recurso' => $campos['fonte_recurso'] ?? null,
                    'subcontratacao' => $campos['subcontratacao'] ?? null,
                ]);
                
                Log::info('Contrato criado', [
                    'processo_id' => $processo->id,
                    'numero_contrato' => $campos['numero_contrato'],
                ]);
            } else {
                $contrato->update([
                    'numero_contrato' => $campos['numero_contrato'] ?? $contrato->numero_contrato,
                    'data_assinatura_contrato' => $campos['data_assinatura_contrato'] ?? $contrato->data_assinatura_contrato,
                    'numero_extrato' => $campos['numero_extrato'] ?? $contrato->numero_extrato,
                    'comarca' => $campos['comarca'] ?? $contrato->comarca,
                    'fonte_recurso' => $campos['fonte_recurso'] ?? $contrato->fonte_recurso,
                    'subcontratacao' => $campos['subcontratacao'] ?? $contrato->subcontratacao,
                ]);
                
                Log::info('Contrato atualizado', [
                    'processo_id' => $processo->id,
                    'numero_contrato' => $campos['numero_contrato'],
                ]);
            }
        }

        $dadosDocumento = [
            'processo_id' => $processo->id,
            'tipo_documento' => 'contrato',
            'data_selecionada' => $dataSelecionada,
            'caminho' => $caminho['relativo'],
            'gerado_em' => now(),
            'valor_total' => $valorTotalContrato,
            'quantidade_itens' => $quantidadeItens,
        ];

        // Salvar campos no JSON
        if (!empty($campos)) {
            $dadosDocumento['campos'] = json_encode($campos);
            Log::info('Campos salvos no JSON do documento', [
                'processo_id' => $processo->id,
                'campos_json' => $campos,
            ]);
        }
        
        // Salvar assinantes se existirem
        if (!empty($assinantes)) {
            $dadosDocumento['assinantes'] = json_encode($assinantes);
        }

        // Salvar contratações selecionadas
        if (!empty($contratacoesIds)) {
            $dadosDocumento['contratacoes_selecionadas'] = json_encode($contratacoesIds);
        }

        // Verificar se já existe um documento para evitar duplicação
        $documentoExistente = Documento::where('processo_id', $processo->id)
            ->where('tipo_documento', 'contrato')
            ->when(!empty($campos['numero_contrato']), function($query) use ($campos) {
                return $query->whereJsonContains('campos->numero_contrato', $campos['numero_contrato']);
            })
            ->first();

        if ($documentoExistente) {
            // Remove o arquivo antigo
            $caminhoAntigo = public_path($documentoExistente->caminho);
            if (file_exists($caminhoAntigo)) {
                unlink($caminhoAntigo);
            }

            $documentoExistente->update($dadosDocumento);
            Log::info('Documento existente atualizado', $dadosDocumento);
        } else {
            // Criar novo documento
            Documento::create($dadosDocumento);
            Log::info('Novo documento criado', $dadosDocumento);
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

    public function debugContratos(Processo $processo)
    {
        $documentos = Documento::where('processo_id', $processo->id)
            ->where('tipo_documento', 'contrato')
            ->get();
        
        $contratos = \App\Models\Contrato::where('processo_id', $processo->id)->get();
        
        // Verificar também o que está sendo enviado no request quando gera contrato
        $requestData = request()->all();
        
        return response()->json([
            'documentos' => $documentos->map(function($doc) {
                return [
                    'id' => $doc->id,
                    'campos' => $doc->campos,
                    'campos_json' => json_decode($doc->campos ?? '{}', true),
                    'numero_contrato_campos' => json_decode($doc->campos ?? '{}', true)['numero_contrato'] ?? 'não encontrado em JSON',
                    'valor_total' => $doc->valor_total,
                    'quantidade_itens' => $doc->quantidade_itens,
                    'gerado_em' => $doc->gerado_em,
                    'caminho' => $doc->caminho,
                ];
            }),
            'contratos' => $contratos->map(function($contrato) {
                return [
                    'id' => $contrato->id,
                    'numero_contrato' => $contrato->numero_contrato,
                    'data_assinatura_contrato' => $contrato->data_assinatura_contrato,
                    'processo_id' => $contrato->processo_id,
                ];
            }),
            'request_data' => $requestData,
            'total_documentos' => $documentos->count(),
            'total_contratos' => $contratos->count(),
        ]);
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