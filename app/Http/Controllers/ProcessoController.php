<?php

namespace App\Http\Controllers;

use App\Enums\ProcessoStatusEnum;
use App\Http\Requests\ProcessoRequest;
use App\Models\Documento;
use App\Models\Prefeitura;
use App\Models\Processo;
use App\Services\ProcessoBncExportService;
use App\Services\ProcessoDocumentoService;
use App\Services\ProcessoPdfService;
use App\Services\ProcessoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ProcessoController extends AbstractController
{
    protected ProcessoService $processoService;
    protected ProcessoPdfService $pdfService;
    protected ProcessoDocumentoService $documentoService;
    protected ProcessoBncExportService $bncExportService;

    public function __construct(
        ProcessoService $processoService,
        ProcessoPdfService $pdfService,
        ProcessoDocumentoService $documentoService,
        ProcessoBncExportService $bncExportService
    ) {
        $this->processoService = $processoService;
        $this->pdfService = $pdfService;
        $this->documentoService = $documentoService;
        $this->bncExportService = $bncExportService;
    }

    public function index(Request $request)
    {
        $prefeituras = Prefeitura::withCount('processos')->get();
        // finalizacao/contrato são carregados só para exibir a ETAPA atual na listagem
        // (indicador visual) — não altera nenhuma regra de negócio.
        $query = Processo::with(['prefeitura', 'detalhe', 'user', 'finalizacao', 'contrato']);

        // Filtro por prefeitura
        $prefeituraId = $request->prefeitura_id;
        if ($prefeituraId) {
            $query->where('prefeitura_id', $prefeituraId);
        }

        // Pesquisa unificada: nº do processo, nº do procedimento ou objeto.
        if ($request->filled('search')) {
            $termo = $this->prepararTermoBusca($request->search);
            $query->where(function ($q) use ($termo) {
                $q->where('objeto', 'like', "%{$termo}%")
                  ->orWhere('numero_processo', 'like', "%{$termo}%")
                  ->orWhere('numero_procedimento', 'like', "%{$termo}%");
            });
        }

        // Filtro por responsável
        if ($request->filled('responsavel')) {
            $responsavel = $this->prepararTermoBusca($request->responsavel);
            $query->whereHas('user', function ($q) use ($responsavel) {
                $q->where('name', 'like', "%{$responsavel}%");
            });
        }

        // Filtro por data de criação
        if ($request->filled('data_inicio')) {
            $query->whereDate('created_at', '>=', $request->data_inicio);
        }
        if ($request->filled('data_fim')) {
            $query->whereDate('created_at', '<=', $request->data_fim);
        }

        // Filtro por modalidade
        if ($request->filled('modalidade')) {
            $query->where('modalidade', $request->modalidade);
        }

        // Filtro por status
        // Se o usuário enviou o formulário (qualquer parâmetro de filtro presente),
        // respeitar a escolha dele (inclusive vazio = todos).
        // Se nenhum filtro foi enviado (acesso direto à página), mostrar apenas Em Andamento.
        $filtersSubmitted = $request->hasAny([
            'search', 'modalidade', 'status',
            'data_inicio', 'data_fim', 'responsavel'
        ]);

        if ($filtersSubmitted) {
            // usuário escolheu um status específico
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            // se status vazio ("Todos os Status"), não aplicar nenhum filtro de status
        } else {
            // acesso direto sem filtros — mostrar somente Em Andamento
            $query->where('status', ProcessoStatusEnum::EM_ANDAMENTO->value);
        }

        // Ordenação
        $query->orderBy('created_at', 'desc');

        $processos = $query->paginate(10)->withQueryString();

        // Preparar dados para a view
        foreach ($processos as $processo) {
            if (!$processo->status instanceof ProcessoStatusEnum) {
                $processo->status = ProcessoStatusEnum::tryFrom($processo->status) ?? ProcessoStatusEnum::EM_ANDAMENTO;
            }
        }

        return view('Admin.Processos.index', compact('processos', 'prefeituras', 'prefeituraId'));
    }

    private function prepararTermoBusca(string $term): string
    {
        // Remove espaços extras
        $term = trim($term);

        // Remove caracteres que podem causar problemas em LIKE
        $term = str_replace(['%', '_', '\\'], '', $term);

        return $term;
    }

    public function create(Request $request)
    {
        $prefeituras = Prefeitura::with('unidades')->get();

        $etp = null;
        if ($request->filled('etp_id')) {
            $etp = \App\Models\Etp::where('status', 'aprovado')
                ->whereNull('processo_id')
                ->find($request->integer('etp_id'));
        }

        return view('Admin.Processos.create', compact('prefeituras', 'etp'));
    }

    public function store(ProcessoRequest $request)
    {
        $dados = $request->validated();
        unset($dados['etp_id']);

        // Campos de "dispensa oriunda de certame fracassado" pertencem ao ProcessoDetalhe,
        // não ao Processo — se ficarem em $dados são silenciosamente ignorados no create().
        $isOriundoFracassado  = (bool) ($dados['is_oriundo_fracassado'] ?? false);
        $processoFracassadoId = $dados['processo_fracassado_id'] ?? null;
        unset($dados['is_oriundo_fracassado'], $dados['processo_fracassado_id']);

        $dados['user_id'] = auth()->id();
        $dados['status'] = ProcessoStatusEnum::EM_ANDAMENTO;

        // Gerar número automático se estiver vazio
        if (empty($dados['numero_processo'])) {
            $dados['numero_processo'] = $this->processoService->gerarProximoNumeroProcesso((int)$dados['prefeitura_id']);
        }

        $etp = null;
        if ($request->filled('etp_id')) {
            $etp = \App\Models\Etp::where('status', 'aprovado')
                ->whereNull('processo_id')
                ->where('prefeitura_id', $dados['prefeitura_id'])
                ->find($request->integer('etp_id'));
        }

        DB::beginTransaction();
        try {
            $processo = $this->processoService->create($dados);

            // Materializa a origem "fracassado" no detalhe. É a fonte da flag
            // usada em ProcessoDocumentoService para inserir os documentos
            // "autorizacao_fracassada" e "declaracao_manutencao_fracassada".
            if ($isOriundoFracassado && $processoFracassadoId) {
                $detalhe = $processo->detalhe ?? $processo->detalhe()->create([]);
                $detalhe->update([
                    'is_oriundo_fracassado'  => true,
                    'processo_fracassado_id' => $processoFracassadoId,
                ]);
            }

            if ($etp) {
                $etp->update([
                    'processo_id' => $processo->id,
                    'status' => 'em_processo',
                ]);

                if (!empty($etp->dotacao_orcamentaria)) {
                    $detalhe = $processo->detalhe ?? $processo->detalhe()->create([]);
                    if (empty($detalhe->dotacao_orcamentaria)) {
                        $detalhe->update(['dotacao_orcamentaria' => $etp->dotacao_orcamentaria]);
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar processo a partir de ETP', [
                'etp_id' => $request->input('etp_id'),
                'erro' => $e->getMessage(),
            ]);
            return redirect()->back()->withInput()->with('error', 'Erro ao criar processo: ' . $e->getMessage());
        }

        return redirect()
            ->route('admin.processos.iniciar', $processo->id)
            ->with('success', $etp ? 'Processo criado e vinculado ao ETP com sucesso.' : 'Processo criado com sucesso.');
    }

    public function show(Processo $processo)
    {
        $processo->load(['prefeitura.unidades', 'user', 'detalhe', 'documentos', 'vencedores']);

        return view('Admin.Processos.show', compact('processo'));
    }

    public function edit(Processo $processo)
    {
        $prefeituras = Prefeitura::with('unidades')->get();
        return view('Admin.Processos.edit', compact('processo', 'prefeituras'));
    }

    public function update(ProcessoRequest $request, Processo $processo)
    {
        $this->processoService->update($processo, $request->validated());
        return redirect()->route('admin.processos.index')->with('success', 'Processo atualizado com sucesso.');
    }

    public function destroy(Processo $processo)
    {
        $this->processoService->delete($processo);
        return redirect()->route('admin.processos.index')->with('success', 'Processo removido com sucesso.');
    }

    public function updateStatus(Request $request, Processo $processo)
    {
        $request->validate([
            'status' => 'required|string|in:' . implode(',', ProcessoStatusEnum::values()),
        ]);

        try {
            $novoStatus = ProcessoStatusEnum::tryFrom($request->status);

            if (!$novoStatus) {
                return response()->json([
                    'success' => false,
                    'message' => 'Status inválido.'
                ], 400);
            }

            $processo->update([
                'status' => $novoStatus
            ]);

            // Se for cancelado, pode adicionar data de cancelamento
            if ($novoStatus === ProcessoStatusEnum::CANCELADO && !$processo->data_cancelamento) {
                $processo->update([
                    'data_cancelamento' => now()
                ]);
            }

            // Se for adiado, pode adicionar data de adiamento
            if ($novoStatus === ProcessoStatusEnum::ADIADO && !$processo->data_adiamento) {
                $processo->update([
                    'data_adiamento' => now()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Status atualizado com sucesso!',
                'data' => [
                    'status' => $processo->status->value,
                    'status_label' => $processo->status->label(),
                    'status_color' => $processo->status->color()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getProcessosFracassados(Request $request)
    {
        $prefeituraId = $request->get('prefeitura_id');
        $busca = $request->get('q'); // Termo enviado pela busca do frontend

        if (!$prefeituraId) {
            return response()->json([]);
        }

        $query = Processo::where('prefeitura_id', $prefeituraId);

        /* 
        * 1. AJUSTE DE STATUS: 
        * Verifique quais status no seu Enum/Banco realmente identificam certames que fracassaram.
        * Se você tiver um flag ou outro status, adicione aqui. Se quiser listar todos para seleção, remova o filtro de status.
        */
        $query->whereIn('status', [
            ProcessoStatusEnum::FINALIZADO, 
            ProcessoStatusEnum::CANCELADO,
            // ProcessoStatusEnum::FRACASSADO, // Adicione outros status se existirem no Enum
        ]);

        // 2. BUSCA POR TERMO
        if (!empty($busca)) {
            $query->where(function ($q) use ($busca) {
                $q->where('numero_processo', 'like', "%{$busca}%")
                ->orWhere('numero_procedimento', 'like', "%{$busca}%")
                ->orWhere('nome_resumido', 'like', "%{$busca}%")
                ->orWhere('objeto', 'like', "%{$busca}%");
            });
        }

        // 3. LIMITE DINÂMICO
        // Se o usuário está digitando, expandimos o limite para 50. Se for a carga inicial, trazemos os 30 mais recentes.
        $limite = !empty($busca) ? 50 : 30;

        $processos = $query->latest()
            ->limit($limite)
            ->get()
            ->map(function ($proc) {
                return [
                    'id' => $proc->id,
                    'text' => "Proc. {$proc->numero_processo} - {$proc->modalidade?->getDisplayName()} (Nº {$proc->numero_procedimento})",
                    'objeto' => $proc->objeto,
                    'tipo_procedimento' => $proc->tipo_procedimento?->value ?? $proc->tipo_procedimento,
                ];
            });

        return response()->json($processos);
    }

    public function iniciar(Processo $processo)
    {
        $processo->load('prefeitura.unidades', 'user', 'etp.lotes');
        $processo->loadMissing('documentos');

        // Garantir que o status seja uma instância do Enum
        if (!$processo->status instanceof ProcessoStatusEnum) {
            $processo->status = ProcessoStatusEnum::tryFrom($processo->status) ?? ProcessoStatusEnum::EM_ANDAMENTO;
        }

        $documentos = $this->documentoService->getDocumentosPorModalidade($processo);

        $contrato_iniciar = null;
        if ($processo->modalidade === \App\Enums\ModalidadeEnum::INEXIGIBILIDADE) {
            $contrato_iniciar = \App\Models\Contrato::where('processo_id', $processo->id)->first();
        }

        $podeExportarBnc = $this->bncExportService->podeExportar($processo);

        return view('Admin.Processos.iniciar', compact('processo', 'documentos', 'contrato_iniciar', 'podeExportarBnc'));
    }

    public function storeDetalhe(Request $request, Processo $processo)
    {
        try {
            $detalhe = $this->processoService->salvarDetalhe($processo, $request->all());

            return response()->json([
                'success' => true,
                'data' => $detalhe->toArray()
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao salvar detalhe do processo', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar os dados: ' . $e->getMessage()
            ], 500);
        }
    }

    public function gerarPdf(Request $request, Processo $processo)
    {
        try {
            $resultado = $this->pdfService->gerarPdf($processo, $request->all());

            return response()->json([
                'success' => true,
                'message' => '✅ PDF gerado com sucesso! Clique em "Download" para visualizar o arquivo.',
                'documento' => $request->query('documento')
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao gerar PDF', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => '❌ Ocorreu um erro inesperado ao gerar o PDF: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Exporta a planilha de lotes/itens para importação na Bolsa Nacional de Compras (BNC).
     */
    public function exportarBnc(Processo $processo)
    {
        try {
            $spreadsheet = $this->bncExportService->gerar($processo);
        } catch (\RuntimeException $e) {
            abort(422, $e->getMessage());
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'BNC-' . str_replace('/', '-', $processo->numero_processo) . '.xlsx';

        Log::info('Planilha BNC exportada', [
            'processo_id' => $processo->id,
            'user_id' => auth()->id(),
            'filename' => $filename,
        ]);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function baixarDocumento(Processo $processo, $tipo)
    {
        // Download direto por ID do documento (usado para republicações individuais)
        if (is_numeric($tipo)) {
            $documento = Documento::where('processo_id', $processo->id)
                ->where('id', $tipo)
                ->firstOrFail();

            return $this->servirDocumentoOuAssinado($documento);
        }

        // "Edital" (original) deve baixar o documento original, sem trocar pelo mais recente
        if ($tipo === 'edital') {
            $documento = Documento::where('processo_id', $processo->id)
                ->where('tipo_documento', 'edital')
                ->firstOrFail();

            return $this->servirDocumentoOuAssinado($documento);
        }

        // Caso contrário, baixe o documento pelo tipo informado
        $documento = Documento::where('processo_id', $processo->id)
            ->where('tipo_documento', $tipo)
            ->firstOrFail();

        return $this->servirDocumentoOuAssinado($documento);
    }

    /**
     * Quando o documento já tem versão assinada consolidada, serve o PDF
     * assinado; caso contrário, serve o original.
     */
    private function servirDocumentoOuAssinado(Documento $documento)
    {
        $caminhoAssinado = app(\App\Services\Assinatura\SelecaoAssinantesService::class)
            ->caminhoPdfAssinado($documento);

        if ($caminhoAssinado) {
            return response()->download(
                $caminhoAssinado,
                basename($documento->caminho ?: 'documento.pdf')
            );
        }

        return response()->download(public_path($documento->caminho));
    }

    public function verificarStatusDownloadDocs($token)
    {
        $statusInfo = \Illuminate\Support\Facades\Cache::get("doc_status_{$token}");

        if (!$statusInfo) {
            return response()->json(['status' => 'processando']); // default se o Job ainda n gravou
        }

        return response()->json($statusInfo);
    }

    public function finalizarDownloadDocs($token)
    {
        $statusInfo = \Illuminate\Support\Facades\Cache::get("doc_status_{$token}");

        if (!$statusInfo || $statusInfo['status'] !== 'pronto' || empty($statusInfo['caminho'])) {
            abort(404, 'Arquivo não encontrado ou expirado.');
        }

        return response()
            ->download($statusInfo['caminho'], $statusInfo['nome'])
            ->deleteFileAfterSend(false); // Mantemos no cache por 2 horas, será limpado por cron OS se necessário, mas podemos manter pois o Nginx faz caching
    }

    public function baixarTodosDocumentos(Processo $processo)
    {
        return $this->dispararDownloadEmLote($processo->id, 'iniciar');
    }

    public function republicarEdital(Request $request, Processo $processo)
    {
        try {
            Log::info('Iniciando republicação de edital', ['processo_id' => $processo->id]);

            $request->validate([
                'data' => 'nullable|date',
                'justificativa' => 'nullable|string',
            ]);

            DB::beginTransaction();

            // Buscar edital existente
            $editalExistente = $processo->documentos()
                ->where('tipo_documento', 'edital')
                ->first();

            if (!$editalExistente) {
                throw new \Exception('Edital original não encontrado.');
            }

            // GERAR MINUTA DE REPUBLICAÇÃO (usando a lógica correta)
            $dataMinuta = [
                'processo' => $processo,
                'prefeitura' => $processo->prefeitura,
                'detalhe' => $processo->detalhe,
                'dataGeracao' => now()->format('d/m/Y H:i:s'),
                'dataSelecionada' => $request->input('data', now()->format('Y-m-d')),
                'titulo' => 'REPUBLICAÇÃO DE EDITAL',
                'justificativa' => $request->input('justificativa'),
                'numero_republicacao' => $this->obterNumeroRepublicacao($processo),
            ];

            // Usar o método do serviço para determinar a view correta
            $viewMinuta = $this->pdfService->determinarViewRepublicacao($processo);

            Log::info('Gerando minuta de republicação', [
                'view' => $viewMinuta,
                'processo_id' => $processo->id,
                'modalidade' => $processo->modalidade?->name,
                'procedimento' => $processo->tipo_procedimento?->name,
                'contratacao' => $processo->tipo_contratacao?->name
            ]);

            $pdfMinuta = Pdf::loadView($viewMinuta, $dataMinuta)
                ->setPaper('a4', 'portrait');

            $caminhoMinuta = storage_path('app/temp_minuta_republicacao_' . uniqid() . '.pdf');
            $pdfMinuta->save($caminhoMinuta);

            // Caminho para o edital original
            $caminhoEditalOriginal = public_path($editalExistente->caminho);

            if (!file_exists($caminhoEditalOriginal)) {
                throw new \Exception('Arquivo do edital original não encontrado.');
            }

            // Caminho para o arquivo final (minuta + edital original)
            $numeroProcessoLimpo = str_replace(['/', '\\'], '_', $processo->numero_processo);
            $subpasta = $this->pdfService->gerarSubpasta($processo, 'republicacao_edital');
            $diretorio = public_path("uploads/documentos/{$subpasta}");

            if (!file_exists($diretorio)) {
                mkdir($diretorio, 0777, true);
            }

            $nomeArquivo = "processo_{$numeroProcessoLimpo}_republicacao_edital_" . now()->format('Ymd_His') . '.pdf';
            $caminhoRelativo = "uploads/documentos/{$subpasta}/{$nomeArquivo}";
            $caminhoCompleto = "{$diretorio}/{$nomeArquivo}";

            // Mesclar minuta + edital original
            $sucesso = $this->pdfService->mesclarPdfsComGhostscript(
                [$caminhoMinuta, $caminhoEditalOriginal],
                $caminhoCompleto
            );

            if (!$sucesso || !file_exists($caminhoCompleto)) {
                throw new \Exception('Erro ao mesclar minuta com edital original.');
            }

            // Criar registro do documento de republicação
            $novoEdital = Documento::create([
                'processo_id' => $processo->id,
                'tipo_documento' => 'republicacao_edital',
                'data_selecionada' => $request->input('data', now()->format('Y-m-d')),
                'justificativa' => $request->input('justificativa'),
                'caminho' => $caminhoRelativo,
                'gerado_em' => now(),
            ]);

            // Atualizar status do processo
            $processo->status = ProcessoStatusEnum::REPUBLICADO;
            $processo->save();

            // Limpar arquivos temporários
            if (file_exists($caminhoMinuta)) {
                unlink($caminhoMinuta);
            }

            DB::commit();

            Log::info('Edital republicado com sucesso', [
                'processo_id' => $processo->id,
                'documento_id' => $novoEdital->id,
                'view_usada' => $viewMinuta
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Edital republicado com sucesso!',
                'documento_id' => $novoEdital->id,
                'documento_nome' => $nomeArquivo
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao republicar edital', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao republicar edital: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exclui uma republicação de edital (republicacao_edital / edital_adiado):
     * remove o registro do documento e o respectivo arquivo PDF.
     */
    public function excluirRepublicacao(Processo $processo, Documento $documento)
    {
        try {
            if (
                $documento->processo_id != $processo->id ||
                !in_array($documento->tipo_documento, ['republicacao_edital', 'edital_adiado'], true)
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documento inválido ou não é uma republicação deste processo.'
                ], 422);
            }

            if ($documento->caminho) {
                $arquivo = public_path($documento->caminho);
                if (file_exists($arquivo)) {
                    unlink($arquivo);
                }
            }

            $documento->delete();

            Log::info('Republicação de edital excluída', [
                'processo_id' => $processo->id,
                'documento_id' => $documento->id,
                'tipo' => $documento->tipo_documento,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Republicação excluída com sucesso.'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao excluir republicação', [
                'processo_id' => $processo->id,
                'documento_id' => $documento->id ?? null,
                'erro' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir republicação: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Criar edital com capa de republicação - NOVO MÉTODO
     */
    private function criarEditalComCapaRepublicacao(string $caminhoEdital, Processo $processo, ?string $justificativa = null): ?string
    {
        try {
            // Criar capa de republicação
            $dataCapa = [
                'processo' => $processo,
                'prefeitura' => $processo->prefeitura,
                'dataGeracao' => now()->format('d/m/Y H:i:s'),
                'titulo' => 'REPUBLICAÇÃO DE EDITAL',
                'justificativa' => $justificativa,
                'numero_republicacao' => $this->obterNumeroRepublicacao($processo),
            ];

            $pdfCapa = Pdf::loadView('Admin.Processos.pdf.capa_republicacao', $dataCapa)
                ->setPaper('a4', 'portrait');

            $caminhoCapa = storage_path('app/temp_capa_republicacao_' . uniqid() . '.pdf');
            $pdfCapa->save($caminhoCapa);

            // Caminho para o arquivo final com capa
            $caminhoFinal = str_replace('.pdf', '_com_capa.pdf', $caminhoEdital);

            // Mesclar capa + edital usando Ghostscript
            $this->pdfService->mesclarPdfsComGhostscript([$caminhoCapa, $caminhoEdital], $caminhoFinal);

            // Limpar arquivo temporário da capa
            if (file_exists($caminhoCapa)) {
                unlink($caminhoCapa);
            }

            // Verificar se o arquivo final foi criado
            if (file_exists($caminhoFinal) && filesize($caminhoFinal) > 0) {
                Log::info('Edital com capa criado com sucesso', [
                    'caminho_final' => $caminhoFinal,
                    'tamanho' => filesize($caminhoFinal)
                ]);
                return $caminhoFinal;
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Erro ao criar edital com capa', [
                'caminho_edital' => $caminhoEdital,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Republicar processo completo
     */
    public function republicarProcesso(Request $request, Processo $processo)
    {
        try {
            Log::info('Iniciando republicação de processo', ['processo_original_id' => $processo->id]);

            $request->validate([
                'novo_numero_processo' => 'required|string|',
                'novo_numero_procedimento' => 'required|string',
                'data_publicacao' => 'required|date',
            ]);

            DB::beginTransaction();

            // Duplicar processo usando o serviço
            $novoProcesso = $this->processoService->duplicarProcesso($processo, [
                'numero_processo' => $request->novo_numero_processo,
                'numero_procedimento' => $request->novo_numero_procedimento,
                'user_id' => auth()->id(),
                'status' => ProcessoStatusEnum::EM_ANDAMENTO,
            ]);

            // Marcar como republicado e referenciar original
            $novoProcesso->status = ProcessoStatusEnum::REPUBLICADO;
            $novoProcesso->processo_original_id = $processo->id;

            // Aqui está a correção: Não tente atualizar data_publicacao no ProcessoDetalhe
            // porque esse campo não existe na tabela processo_detalhes
            $novoProcesso->save();

            // Registrar log
            Log::info('Processo republicado', [
                'processo_original_id' => $processo->id,
                'novo_processo_id' => $novoProcesso->id,
                'usuario_id' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Processo republicado com sucesso!',
                'processo_id' => $novoProcesso->id,
                'redirect_url' => route('admin.processos.iniciar', $novoProcesso->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao republicar processo', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao republicar processo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancelar licitação
     */
    public function cancelarLicitacao(Request $request, Processo $processo)
    {
        try {
            Log::info('Iniciando cancelamento de licitação', ['processo_id' => $processo->id]);

            $request->validate([
                'data_cancelamento' => 'required|date',
                'motivo_cancelamento' => 'nullable|string',
            ]);

            DB::beginTransaction();

            $modeloPath = resource_path('views/Admin/Processos/pdf/cancelamento.blade.php');
            if (!file_exists($modeloPath)) {
                throw new \Exception('Modelo de cancelamento não encontrado.');
            }

            $data = [
                'processo' => $processo,
                'prefeitura' => $processo->prefeitura,
                'detalhe' => $processo->detalhe,
                'data_cancelamento' => $request->data_cancelamento,
                'motivo_cancelamento' => $request->motivo_cancelamento,
                'dataGeracao' => now()->format('d/m/Y H:i:s'),
                'usuario' => auth()->user(),
            ];

            $pdf = Pdf::loadView('Admin.Processos.pdf.cancelamento', $data)->setPaper('a4', 'portrait');

            $numeroProcessoLimpo = str_replace(['/', '\\'], '_', $processo->numero_processo);
            $diretorio = public_path("uploads/documentos/cancelamentos");

            if (!file_exists($diretorio)) {
                mkdir($diretorio, 0777, true);
            }

            $nomeArquivo = "processo_{$numeroProcessoLimpo}_cancelamento_" . now()->format('Ymd_His') . '.pdf';
            $caminhoRelativo = "uploads/documentos/cancelamentos/{$nomeArquivo}";
            $caminhoCompleto = "{$diretorio}/{$nomeArquivo}";

            $pdf->save($caminhoCompleto);

            // Registrar no banco (usar campo existente no Documento)
            Documento::create([
                'processo_id' => $processo->id,
                'tipo_documento' => 'minuta_cancelamento',
                'data_selecionada' => $request->data_cancelamento,
                'caminho' => $caminhoRelativo,
                'gerado_em' => now(),
                'justificativa' => $request->motivo_cancelamento,
            ]);

            $processo->status = ProcessoStatusEnum::CANCELADO;
            $processo->data_cancelamento = $request->data_cancelamento;
            $processo->motivo_cancelamento = $request->motivo_cancelamento;
            $processo->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Licitação cancelada com sucesso! Documento gerado.',
                'documento_nome' => $nomeArquivo
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao cancelar licitação', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao cancelar licitação: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reverter cancelamento de processo
     */
    public function reverterCancelamento(Request $request, Processo $processo)
    {
        try {
            Log::info('Iniciando reversão de cancelamento', ['processo_id' => $processo->id]);

            // Verificar se o processo está cancelado
            if ($processo->status !== ProcessoStatusEnum::CANCELADO) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este processo não está cancelado.'
                ], 400);
            }

            DB::beginTransaction();

            // Reverter para status anterior (ou definir um status padrão)
            // Você pode querer salvar o status anterior em um campo separado
            // ou usar uma lógica para determinar qual status reverter
            $novoStatus = ProcessoStatusEnum::EM_ANDAMENTO; // Ou outro status apropriado

            // Atualizar status
            $processo->status = $novoStatus;

            // Limpar dados de cancelamento
            $processo->data_cancelamento = null;
            $processo->motivo_cancelamento = null;

            // Salvar alterações
            $processo->save();

            // Registrar log da ação
            Log::info('Cancelamento revertido', [
                'processo_id' => $processo->id,
                'novo_status' => $novoStatus->value,
                'usuario_id' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cancelamento revertido com sucesso!',
                'data' => [
                    'status' => $processo->status->value,
                    'status_label' => $processo->status->label(),
                    'status_color' => $processo->status->color()
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao reverter cancelamento', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao reverter cancelamento: ' . $e->getMessage()
            ], 500);
        }
    }

    public function adiarLicitacao(Request $request, Processo $processo)
    {
        try {
            Log::info('Iniciando adiamento de licitação', ['processo_id' => $processo->id]);

            $request->validate([
                'nova_data' => 'required|date',
                'novo_horario' => 'required|string',
                'justificativa_adiamento' => 'nullable|string',
            ]);

            DB::beginTransaction();

            $dataHoraAnterior = $processo->detalhe?->data_hora_limite_edital;

            $dataHoraNova = $request->nova_data . ' ' . $request->novo_horario . ':00';

            if ($processo->detalhe) {
                $processo->detalhe->data_hora_limite_edital = $dataHoraNova;
                $processo->detalhe->data_hora_fase_edital = $dataHoraNova;
                $processo->detalhe->save();
            }

            $editalExistente = $processo->documentos()
                ->where('tipo_documento', 'edital')
                ->first();

            if ($editalExistente) {
                $data = $this->pdfService->prepararDadosPdf($processo, [
                    'dataSelecionada' => $request->nova_data,
                    'assinantes' => [],
                    'parecerSelecionado' => null,
                ]);

                $data['adiamento'] = true;
                $data['justificativa_adiamento'] = $request->justificativa_adiamento;
                $data['data_hora_original'] = $dataHoraAnterior ?? 'Não informada';

                $view = $this->pdfService->determinarViewPdf($processo, 'edital');
                $pdf = Pdf::loadView($view, $data)->setPaper('a4', 'portrait');

                $numeroProcessoLimpo = str_replace(['/', '\\'], '_', $processo->numero_processo);
                $subpasta = $this->pdfService->gerarSubpasta($processo, 'edital_adiado');
                $diretorio = public_path("uploads/documentos/{$subpasta}");

                if (!file_exists($diretorio)) {
                    mkdir($diretorio, 0777, true);
                }

                $nomeArquivo = "processo_{$numeroProcessoLimpo}_edital_adiado_" . now()->format('Ymd_His') . '.pdf';
                $caminhoRelativo = "uploads/documentos/{$subpasta}/{$nomeArquivo}";
                $caminhoCompleto = "{$diretorio}/{$nomeArquivo}";

                $pdf->save($caminhoCompleto);

                Documento::create([
                    'processo_id' => $processo->id,
                    'tipo_documento' => 'edital_adiado',
                    'data_selecionada' => $request->nova_data,
                    'caminho' => $caminhoRelativo,
                    'gerado_em' => now(),
                    'justificativa' => $request->justificativa_adiamento,
                ]);
            }

            $processo->status = ProcessoStatusEnum::ADIADO;
            $processo->data_adiamento = $request->nova_data;
            $processo->justificativa_adiamento = $request->justificativa_adiamento;
            $processo->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Licitação adiada com sucesso! Novo edital gerado.',
                'documento_nome' => $nomeArquivo ?? null
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao adiar licitação', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao adiar licitação: ' . $e->getMessage()
            ], 500);
        }
    }

    private function adicionarCapaRepublicacao(string $caminhoPdf, Processo $processo, ?string $justificativa = null): void
    {
        try {
            $data = [
                'processo' => $processo,
                'prefeitura' => $processo->prefeitura,
                'dataGeracao' => now()->format('d/m/Y H:i:s'),
                'titulo' => 'REPUBLICAÇÃO DE EDITAL',
                'justificativa' => $justificativa,
                'numero_republicacao' => $this->obterNumeroRepublicacao($processo),
            ];

            $pdfCapa = Pdf::loadView('Admin.Processos.pdf.capa_republicacao', $data)->setPaper('a4', 'portrait');
            $caminhoCapa = storage_path('app/temp_capa_republicacao_' . uniqid() . '.pdf');
            $pdfCapa->save($caminhoCapa);

            // Mesclar capa com edital
            $this->pdfService->mesclarPdfsComGhostscript([$caminhoCapa, $caminhoPdf], $caminhoPdf);

            // Remover arquivo temporário
            if (file_exists($caminhoCapa)) {
                unlink($caminhoCapa);
            }

        } catch (\Exception $e) {
            Log::error('Erro ao adicionar capa de republicação', [
                'caminho_pdf' => $caminhoPdf,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function obterNumeroRepublicacao(Processo $processo): int
    {
        $republicacoes = Documento::where('processo_id', $processo->id)
            ->where('tipo_documento', 'like', '%republicacao%')
            ->count();

        return $republicacoes + 1;
    }

    private function regenerarDocumentosComNovosDados(Processo $processo, array $dados): void
    {
        // Regenerar documentos com novas numerações
        $tiposDocumentos = ['edital', 'capa', 'formalizacao', 'parecer_juridico'];

        foreach ($tiposDocumentos as $tipo) {
            $documento = $processo->documentos()->where('tipo_documento', $tipo)->first();

            if ($documento && file_exists(public_path($documento->caminho))) {
                // Gerar novo PDF com dados atualizados
                $this->regenerarDocumentoIndividual($processo, $tipo, $dados);
            }
        }
    }

    private function regenerarDocumentoIndividual(Processo $processo, string $tipo, array $dados): void
    {
        try {
            $data = $this->pdfService->prepararDadosPdf($processo, [
                'dataSelecionada' => $dados['data_publicacao'] ?? now()->format('Y-m-d'),
                'assinantes' => [],
                'parecerSelecionado' => null,
            ]);

            $view = $this->pdfService->determinarViewPdf($processo, $tipo);
            $pdf = Pdf::loadView($view, $data)->setPaper('a4', 'portrait');

            // Salvar documento
            $numeroProcessoLimpo = str_replace(['/', '\\'], '_', $processo->numero_processo);
            $subpasta = $this->pdfService->gerarSubpasta($processo, $tipo);
            $diretorio = public_path("uploads/documentos/{$subpasta}");

            if (!file_exists($diretorio)) {
                mkdir($diretorio, 0777, true);
            }

            $nomeArquivo = "processo_{$numeroProcessoLimpo}_{$tipo}_" . now()->format('Ymd_His') . '.pdf';
            $caminhoRelativo = "uploads/documentos/{$subpasta}/{$nomeArquivo}";

            $pdf->save($diretorio . '/' . $nomeArquivo);

            // Atualizar registro no banco
            Documento::updateOrCreate(
                [
                    'processo_id' => $processo->id,
                    'tipo_documento' => $tipo,
                ],
                [
                    'data_selecionada' => $dados['data_publicacao'] ?? now()->format('Y-m-d'),
                    'caminho' => $caminhoRelativo,
                    'gerado_em' => now(),
                ]
            );

        } catch (\Exception $e) {
            Log::warning("Erro ao regenerar documento {$tipo}", [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function byPrefeitura(Request $request)
    {
        $prefeituraId = $request->input('prefeitura_id');
        $processos = Processo::where('prefeitura_id', $prefeituraId)
            ->where('modalidade', \App\Enums\ModalidadeEnum::PREGAO_ELETRONICO)
            ->whereHas('detalhe', function($q) {
                $q->where('tipo_srp', 'sim');
            })
            ->get();

        return response()->json($processos);
    }

    public function desvincularEtp(Processo $processo)
    {
        try {
            $etp = $processo->etp;

            if (!$etp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não há ETP vinculado a este processo.'
                ], 404);
            }

            DB::beginTransaction();

            $etp->update([
                'processo_id' => null,
                'status' => 'aprovado'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'ETP desvinculado com sucesso! O upload de arquivos agora está disponível.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao desvincular ETP', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao desvincular ETP: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove um anexo manual (upload feito diretamente no Processo) para que a
     * geração de PDF volte a usar automaticamente o PDF anexado no ETP vinculado.
     */
    public function removerAnexo(Request $request, Processo $processo)
    {
        $request->validate([
            'campo' => 'required|string|in:projeto_basico_pdf,anexo_pdf_analise_mercado',
        ]);

        try {
            $this->processoService->removerAnexoManual($processo, $request->input('campo'));

            return response()->json([
                'success' => true,
                'message' => 'Anexo removido. O PDF do ETP vinculado será usado novamente.'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao remover anexo manual do processo', [
                'processo_id' => $processo->id,
                'campo' => $request->input('campo'),
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover anexo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function vincularEtp(Request $request, Processo $processo)
    {
        $request->validate([
            'etp_id' => 'required|exists:etps,id'
        ]);

        try {
            $etp = \App\Models\Etp::findOrFail($request->etp_id);

            if ($etp->status !== 'aprovado') {
                return response()->json([
                    'success' => false,
                    'message' => 'Apenas ETPs aprovados podem ser vinculados.'
                ], 400);
            }

            DB::beginTransaction();

            // Desvincular qualquer ETP que já esteja vinculado a este processo (se houver)
            if ($processo->etp) {
                $processo->etp->update([
                    'processo_id' => null,
                    'status' => 'aprovado'
                ]);
            }

            // Realizar o novo vínculo
            $etp->update([
                'processo_id' => $processo->id,
                'status' => 'em_processo'
            ]);

            // Pré-preencher dotacao_orcamentaria do processo com o valor do ETP,
            // mas apenas se o campo ainda estiver vazio no processo.
            if (!empty($etp->dotacao_orcamentaria)) {
                $detalhe = $processo->detalhe ?? $processo->detalhe()->create([]);
                if (empty($detalhe->dotacao_orcamentaria)) {
                    $detalhe->update(['dotacao_orcamentaria' => $etp->dotacao_orcamentaria]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'ETP vinculado com sucesso!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao vincular ETP', [
                'processo_id' => $processo->id,
                'etp_id' => $request->etp_id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao vincular ETP: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getEtpsDisponiveis(Processo $processo)
    {
        try {
            $etps = \App\Models\Etp::with('secretaria')
                ->where('prefeitura_id', $processo->prefeitura_id)
                ->where('status', 'aprovado')
                ->whereNull('processo_id')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($etp) {
                    return [
                        'id' => $etp->id,
                        'identificador' => "ETP-" . str_pad($etp->id, 4, '0', STR_PAD_LEFT) . "/" . $etp->created_at->format('Y'),
                        'secretaria' => $etp->secretaria->nome ?? 'N/A',
                        'objeto' => \Illuminate\Support\Str::limit($etp->objeto_licitacao, 100),
                        'data' => $etp->created_at->format('d/m/Y'),
                        'tipo' => ucfirst($etp->tipo_contratacao),
                        'qtd_itens' => $etp->usaLotes() ? $etp->lotes->count() : $etp->itens->count()
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $etps
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar ETPs: ' . $e->getMessage()
            ], 500);
        }
    }

    public function valorEstimadoPesquisaPreco(Processo $processo)
    {
        $total = $this->pdfService->calcularValorEstimadoTotal($processo);

        if ($total <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhum valor de pesquisa de preço encontrado para este processo.',
            ], 422);
        }

        $valorFormatado = number_format($total, 2, ',', '.');
        $extenso        = \App\Helpers\ValorPorExtenso::escrever($total);

        return response()->json([
            'success' => true,
            'valor'   => $total,
            'texto'   => "{$valorFormatado} ({$extenso})",
        ]);
    }

    public function pesquisaPrecoItens(Processo $processo)
    {
        $fonte = null;
        $etpInfo = null;
        $lotes = null;

        // Agrupa as referências já coletadas
        $coletas = $processo->pesquisaPrecoItens;
        
        // Mapeia coletas por ID de item do ETP (para maior precisão)
        $coletasPorId = $coletas->whereNotNull('etp_item_id')
            ->groupBy('etp_item_id')
            ->map->count();

        // Mapeia coletas por descrição (fallback para itens de XLS ou legados)
        $coletasPorDescricao = $coletas->groupBy(fn($i) => strtolower(trim($i->descricao)))
            ->map->count();

        if ($processo->etp) {
            $etp = $processo->etp;
            $fonte = 'etp';
            $etpInfo = $etp;

            if ($etp->usaLotes()) {
                $lotes = $etp->lotes()->with('itens')->get()->map(function($lote) use ($coletasPorId, $coletasPorDescricao) {
                    return [
                        'nome' => $lote->nome,
                        'itens' => $lote->itens->map(function($item) use ($coletasPorId, $coletasPorDescricao) {
                            // Tenta pelo ID, se não tiver nada tenta pela descrição
                            $count = $coletasPorId[$item->id] ?? null;
                            if ($count === null) {
                                $count = $coletasPorDescricao[strtolower(trim($item->descricao_item))] ?? 0;
                            }

                            return [
                                'id'         => $item->id,
                                'descricao'  => $item->descricao_item,
                                'unidade'    => $item->pivot->unidade,
                                'quantidade' => $item->pivot->quantidade,
                                'refs_count' => $count,
                            ];
                        })
                    ];
                });
                $itens = $lotes->flatMap(fn($l) => $l['itens']);
            } else {
                $itens = $etp->itens->map(function($item) use ($coletasPorId, $coletasPorDescricao) {
                    $count = $coletasPorId[$item->id] ?? null;
                    if ($count === null) {
                        $count = $coletasPorDescricao[strtolower(trim($item->descricao_item))] ?? 0;
                    }

                    return [
                        'id'         => $item->id,
                        'descricao'  => $item->descricao_item,
                        'unidade'    => $item->pivot->unidade,
                        'quantidade' => $item->pivot->quantidade,
                        'refs_count' => $count,
                    ];
                });
            }
        } else {
            $raw = $processo->detalhe->descricao_e_quantitativos_itens_xml ?? [];
            $itens = is_array($raw)
                ? collect($raw)->map(fn($item) => [
                    'descricao'  => $item['descricao'],
                    'unidade'    => $item['und'] ?? null,
                    'quantidade' => $item['quantidade'],
                    'refs_count' => $coletasPorDescricao[strtolower(trim($item['descricao']))] ?? 0,
                ])
                : collect([]);
            $fonte = 'xls';
        }

        return view('Admin.Processos.pesquisa_preco_itens', compact('processo', 'itens', 'fonte', 'etpInfo', 'lotes'));
    }

    public function pesquisaPrecoFornecedorLocal(Processo $processo)
    {
        $itens = collect();

        if ($processo->etp) {
            $etp = $processo->etp;
            if ($etp->usaLotes()) {
                $itens = $etp->lotes()->with('itens')->get()
                    ->flatMap(fn($lote) => $lote->itens->map(fn($item) => [
                        'id'        => $item->id,
                        'descricao' => $item->descricao_item,
                        'unidade'   => $item->pivot->unidade,
                    ]));
            } else {
                $itens = $etp->itens->map(fn($item) => [
                    'id'        => $item->id,
                    'descricao' => $item->descricao_item,
                    'unidade'   => $item->pivot->unidade,
                ]);
            }
        } else {
            $raw = $processo->detalhe->descricao_e_quantitativos_itens_xml ?? [];
            $itens = is_array($raw)
                ? collect($raw)->map(fn($item) => [
                    'id'        => null,
                    'descricao' => $item['descricao'],
                    'unidade'   => $item['und'] ?? null,
                ])
                : collect([]);
        }

        $precosSalvos = $processo->detalhe?->fornecedor_local_precos ?? [];

        return view('Admin.Processos.pesquisa_preco_fornecedor_local', compact('processo', 'itens', 'precosSalvos'));
    }

    public function salvarFornecedorLocalPrecos(\Illuminate\Http\Request $request, Processo $processo)
    {
        $request->validate(['itens' => 'required|array']);

        $precos = collect($request->input('itens'))->map(fn($item) => [
            'etp_item_id' => $item['etp_item_id'] ?? null,
            'descricao'   => $item['descricao'] ?? '',
            'f1_nome'     => $item['f1_nome'] ?? null,
            'f1_preco'    => $item['f1_preco'] !== '' && $item['f1_preco'] !== null ? (float) $item['f1_preco'] : null,
            'f2_nome'     => $item['f2_nome'] ?? null,
            'f2_preco'    => $item['f2_preco'] !== '' && $item['f2_preco'] !== null ? (float) $item['f2_preco'] : null,
            'f3_nome'     => $item['f3_nome'] ?? null,
            'f3_preco'    => $item['f3_preco'] !== '' && $item['f3_preco'] !== null ? (float) $item['f3_preco'] : null,
        ])->values()->all();

        $processo->detalhe()->updateOrCreate(
            ['processo_id' => $processo->id],
            ['fornecedor_local_precos' => $precos]
        );

        return response()->json(['success' => true, 'message' => 'Preços salvos com sucesso.']);
    }

    public function pesquisaPrecoTce(Processo $processo)
    {
        $itens = collect();

        if ($processo->etp) {
            $etp = $processo->etp;
            if ($etp->usaLotes()) {
                $itens = $etp->lotes()->with('itens')->get()
                    ->flatMap(fn($lote) => $lote->itens->map(fn($item) => [
                        'id'        => $item->id,
                        'descricao' => $item->descricao_item,
                        'unidade'   => $item->pivot->unidade,
                    ]));
            } else {
                $itens = $etp->itens->map(fn($item) => [
                    'id'        => $item->id,
                    'descricao' => $item->descricao_item,
                    'unidade'   => $item->pivot->unidade,
                ]);
            }
        }

        $precosSalvos = $processo->detalhe?->painel_preco_tce ?? [];

        return view('Admin.Processos.pesquisa_preco_tce', compact('processo', 'itens', 'precosSalvos'));
    }

    public function salvarTcePrecos(\Illuminate\Http\Request $request, Processo $processo)
    {
        $request->validate(['itens' => 'required|array']);

        $precos = collect($request->input('itens'))->map(function ($item) {
            $v1 = $item['valor_tce_1'] !== '' && $item['valor_tce_1'] !== null ? (float) $item['valor_tce_1'] : null;
            $v2 = $item['valor_tce_2'] !== '' && $item['valor_tce_2'] !== null ? (float) $item['valor_tce_2'] : null;
            $v3 = $item['valor_tce_3'] !== '' && $item['valor_tce_3'] !== null ? (float) $item['valor_tce_3'] : null;
            $vLocalRaw = $item['fornecedor_local_preco'] ?? '';
            $vLocal = $vLocalRaw !== '' && $vLocalRaw !== null ? (float) $vLocalRaw : null;

            $vals  = array_filter([$v1, $v2, $v3, $vLocal], fn($v) => $v !== null);
            $media = count($vals) > 0 ? array_sum($vals) / count($vals) : null;

            return [
                'etp_item_id' => !empty($item['etp_item_id']) ? (int) $item['etp_item_id'] : null,
                'item'        => $item['descricao'] ?? '',
                'valor_tce_1' => $v1 !== null ? number_format($v1, 2, ',', '.') : '',
                'valor_tce_2' => $v2 !== null ? number_format($v2, 2, ',', '.') : '',
                'valor_tce_3' => $v3 !== null ? number_format($v3, 2, ',', '.') : '',
                'fornecedor_local_preco' => $vLocal !== null ? number_format($vLocal, 2, ',', '.') : '',
                'media'       => $media !== null ? number_format($media, 2, ',', '.') : '',
            ];
        })->values()->all();

        $processo->detalhe()->updateOrCreate(
            ['processo_id' => $processo->id],
            ['painel_preco_tce' => $precos]
        );

        return response()->json(['success' => true, 'message' => 'Preços TCE salvos com sucesso.']);
    }

    public function gerarNumeros(Request $request)
    {
        $prefeituraId = $request->input('prefeitura_id');

        if (!$prefeituraId) {
            return response()->json(['error' => 'Prefeitura não informada.'], 400);
        }

        $proximoNumeroProcesso = $this->processoService->gerarProximoNumeroProcesso((int)$prefeituraId);

        return response()->json([
            'success' => true,
            'numero_processo' => $proximoNumeroProcesso
        ]);
    }
}
