<?php

namespace App\Http\Controllers;


use App\Models\Processo;
use App\Models\Documento;
use App\Models\Prefeitura;
use Illuminate\Http\Request;
use setasign\Fpdi\Tcpdf\Fpdi;
use App\Models\ProcessoDetalhe;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\ProcessoService;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\ProcessoRequest;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProcessoController extends Controller
{
    protected $service;

    // Documentos configuration
    protected $documentos = [
        'capa' => [
            'titulo' => 'Capa do documento',
            'cor' => 'bg-red-500',
            'data_id' => 'data_capa',
            'campos' => [''],
        ],
        'formalizacao' => [
            'titulo' => 'DOCUMENTO DE FORMALIZAÇÃO DE DEMANDA',
            'cor' => 'bg-blue-500',
            'data_id' => 'data_formalizacao',
            'campos' => [
                'secretaria',
                'justificativa',
                'prazo_entrega',
                'local_entrega',
                'contratacoes_anteriores',
                'instrumento_vinculativo',
                'prazo_vigencia',
                'objeto_continuado',
                'descricao_necessidade_autorizacao',
                'responsavel_equipe_planejamento',
            ],
        ],
        'estudo_tecnico' => [
            'titulo' => 'INSTRUMENTOS DE PLANEJAMENTO ETP E MAPA DE RISCOS',
            'cor' => 'bg-purple-500',
            'data_id' => 'data_estudo_tecnico',
            'campos' => [
                'problema_resolvido',
                'descricao_necessidade',
                'inversao_fase',
                'solucoes_disponivel_mercado',
                'incluir_requisito_cada_caso_concreto',
                'solucao_escolhida',
                'justificativa_solucao_escolhida',
                'resultado_pretendidos',
                'impacto_ambiental',
                'riscos_extra',
                'tipo_srp',
                'prevista_plano_anual',
                'encaminhamento_elaborar_editais',
                'encaminhamento_elaborar_projeto_basico',
                'encaminhamento_pesquisa_preco',
                'encaminhamento_doacao_orcamentaria',
                'itens_e_seus_quantitativos_xml',
            ],
        ],
        'projeto_basico' => [
            'titulo' => 'PROJETO BÁSICO',
            'cor' => 'bg-green-500',
            'data_id' => 'data_projeto_basico',
            'campos' => ['projeto_basico_pdf'],
        ],
        'analise_mercado' => [
            'titulo' => 'ANÁLISE DE MERCADO (PESQUISA DE PRECOS)',
            'cor' => 'bg-green-500',
            'data_id' => 'data_analise_mercado',
            'campos' => ['painel_preco_tce', 'anexo_pdf_analise_mercado'],
        ],
        'disponibilidade_orçamento' => [
            'titulo' => 'DISPONIBILIDADE ORÇAMENTÁRIA',
            'cor' => 'bg-yellow-500',
            'data_id' => 'data_disponibilidade_orçamento',
            'campos' => [
                'valor_estimado',
                'dotacao_orcamentaria',
            ],
        ],
        'termo_referencia' => [
            'titulo' => 'TERMO DE REFERÊNCIA',
            'cor' => 'bg-orange-500',
            'data_id' => 'data_termo_referencia',
            'campos' => [
                'encaminhamento_parecer_juridico',
                'encaminhamento_autorizacao_abertura',
                'itens_especificaca_quantitativos_xml',
                'info_extras'
            ],
        ],
        'minutas' => [
            'titulo' => 'MINUTAS',
            'cor' => 'bg-pink-500',
            'data_id' => 'data_minutas',
            'campos' => ['anexar_minuta'],
        ],
        'parecer_juridico' => [
            'titulo' => 'PARECER JURÍDICO',
            'cor' => 'bg-emerald-500',
            'data_id' => 'data_parecer_juridico',
            'campos' => [''],
        ],
        'autorizacao_abertura_procedimento' => [
            'titulo' => 'AUTORIZAÇÃO ABERTURA PROCEDIMENTO LICITATÓRIO',
            'cor' => 'bg-teal-500',
            'data_id' => 'data_autorizacao_abertura_procedimento',
            'campos' => ['tratamento_diferenciado_MEs_eEPPs', 'agente_contratacao'],
        ],
        'abertura_fase_externa' => [
            'titulo' => 'ABERTURA FASE EXTERNA',
            'cor' => 'bg-cyan-500',
            'data_id' => 'data_abertura_fase_externa',
            'campos' => [''],
        ],
        'avisos_licitacao' => [
            'titulo' => 'AVISOS DE LICITAÇÃO',
            'cor' => 'bg-indigo-500',
            'data_id' => 'data_avisos_licitacao',
            'campos' => ['data_hora', 'portal'],
        ],
        'edital' => [
            'titulo' => 'EDITAL',
            'cor' => 'bg-indigo-500',
            'data_id' => 'data_edital',
            'campos' => [
                'exige_atestado',
                'data_hora_limite_edital',
                'data_hora_fase_edital',
                'pregoeiro',
                'intervalo_lances',
                'exigencia_garantia_proposta',
                'exigencia_garantia_contrato',
                'participacao_exclusiva_mei_epp',
                'reserva_cotas_mei_epp',
                'prioridade_contratacao_mei_epp',
                'regularidade_fisica',
                'qualificacao_economica',
                'exigencias_tecnicas',
                'anexo_pdf_minuta_contrato',
                'numero_items',
            ],
        ],
        'publicacoes_avisos_licitacao' => [
            'titulo' => 'PUBLICAÇÕES',
            'cor' => 'bg-indigo-500',
            'data_id' => 'data_publicacoes_avisos_licitacao',
            'campos' => ['anexo_pdf_publicacoes'],
        ],
    ];

    // Mapeamento de anexos
    protected $mapeamentoAnexos = [
        'analise_mercado' => 'anexo_pdf_analise_mercado',
        'minutas' => 'anexar_minuta',
        'publicacoes_avisos_licitacao' => 'anexo_pdf_publicacoes',
        'edital' => ['anexo_pdf_minuta_contrato'],
        'projeto_basico' => 'projeto_basico_pdf',
    ];

    public function __construct(ProcessoService $service)
    {
        $this->service = $service;
    }

    // =========================================================
    // MÉTODOS CRUD PRINCIPAIS
    // =========================================================

    public function index()
    {
        $prefeituras = Prefeitura::withCount('processos')->get();
        $query = Processo::with('prefeitura');

        if (request('prefeitura_id')) {
            $query->where('prefeitura_id', request('prefeitura_id'));
        }

        $processos = $query->paginate(10)->withQueryString();

        return view('Admin.Processos.index', compact('processos', 'prefeituras'));
    }

    public function create()
    {
        $prefeituras = Prefeitura::with('unidades')->get();
        return view('Admin.Processos.create', compact('prefeituras'));
    }

    public function store(ProcessoRequest $request)
    {
        $this->service->create($request->validated());
        return redirect()->route('admin.processos.index')->with('success', 'Processo criado com sucesso.');
    }

    public function show(Processo $processo)
    {
        return view('Admin.Processos.show', compact('processo'));
    }

    public function edit(Processo $processo)
    {
        $prefeituras = Prefeitura::with('unidades')->get();
        return view('Admin.Processos.edit', compact('processo', 'prefeituras'));
    }

    public function update(ProcessoRequest $request, Processo $processo)
    {
        $this->service->update($processo, $request->validated());
        return redirect()->route('admin.processos.index')->with('success', 'Processo atualizado com sucesso.');
    }

    public function destroy(Processo $processo)
    {
        $this->service->delete($processo);
        return redirect()->route('admin.processos.index')->with('success', 'Processo removido com sucesso.');
    }

    // =========================================================
    // MÉTODOS DE INICIALIZAÇÃO DO PROCESSO
    // =========================================================

    public function iniciar(Processo $processo)
    {
        $processo->load('prefeitura.unidades');
        $documentos = $this->documentos;
        return view('Admin.Processos.iniciar', compact('processo', 'documentos'));
    }

    public function storeDetalhe(Request $request, Processo $processo)
    {
        try {
            $detalhe = $processo->detalhe ?? new ProcessoDetalhe();
            $detalhe->processo_id = $processo->id;

            // Processa arquivos
            $this->processarArquivos($request, $detalhe);

            // Salva outros campos
            $dataToSave = $request->except($this->getExcludedFields());
            foreach ($dataToSave as $field => $value) {
                $detalhe->{$field} = $value;
            }

            $detalhe->save();

            return response()->json([
                'success' => true,
                'data' => $detalhe->toArray()
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao salvar detalhe do processo', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar os dados.'
            ], 500);
        }
    }

    // =========================================================
    // MÉTODOS DE GERAÇÃO E DOWNLOAD DE PDF
    // =========================================================

    public function gerarPdf(Request $request, Processo $processo)
    {
        try {
            Log::info('Iniciando geração de PDF', [
                'processo_id' => $processo->id,
                'documento' => $request->query('documento'),
                'request_data' => $request->all()
            ]);

            $validatedData = $this->validarRequisicaoPdf($request, $processo);
            $data = $this->prepararDadosPdf($processo, $validatedData);
            $view = $this->determinarViewPdf($processo, $validatedData['documento']);

            Log::info('View selecionada para PDF', ['view' => $view]);

            $pdf = Pdf::loadView($view, $data)->setPaper('a4', 'portrait');

            $caminhoCompleto = $this->salvarDocumento($processo, $pdf, $validatedData);

            $this->processarAnexos($processo, $validatedData['documento'], $caminhoCompleto);

            Log::info('PDF gerado com sucesso', [
                'processo_id' => $processo->id,
                'documento' => $validatedData['documento'],
                'caminho' => $caminhoCompleto
            ]);

            return response()->json([
                'success' => true,
                'message' => '✅ PDF gerado com sucesso! Clique em "Download" para visualizar o arquivo.',
                'documento' => $validatedData['documento']
            ]);
        } catch (\Throwable $e) {
            Log::error('Erro ao gerar PDF', [
                'processo_id' => $processo->id,
                'documento' => $request->query('documento'),
                'erro' => $e->getMessage(),
                'linha' => $e->getLine(),
                'arquivo' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => '❌ Ocorreu um erro inesperado ao gerar o PDF: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function baixarDocumento(Processo $processo, $tipo)
    {
        $documento = Documento::where('processo_id', $processo->id)
            ->where('tipo_documento', $tipo)
            ->firstOrFail();

        return response()->download(public_path($documento->caminho));
    }

    public function baixarTodosDocumentos(Processo $processo)
    {
        $ordem = $this->getOrdemDocumentos();
        $documentos = Documento::where('processo_id', $processo->id)->get()->keyBy('tipo_documento');

        // Usar Ghostscript diretamente - mais confiável e rápido
        return $this->baixarTodosDocumentosComGhostscript($processo, $ordem, $documentos);
    }

    private function baixarTodosDocumentosComGhostscript(Processo $processo, array $ordem, $documentos)
    {
        // Preparar lista de arquivos na ordem correta
        $arquivos = [];
        foreach ($ordem as $tipo) {
            if (!isset($documentos[$tipo])) continue;
            $caminho = public_path($documentos[$tipo]->caminho);
            if (!file_exists($caminho)) continue;
            $arquivos[] = $caminho;
        }

        if (empty($arquivos)) {
            throw new \Exception('Nenhum documento encontrado para mesclar.');
        }

        $nomeArquivo = "processo_" . str_replace(['/', '\\'], '_', $processo->numero_processo) . "_todos_documentos_" . now()->format('Ymd_His') . '.pdf';
        $caminhoArquivo = public_path('uploads/documentos/' . $nomeArquivo);

        // Mesclar PDFs usando Ghostscript
        $sucesso = $this->mesclarPdfsComGhostscript($arquivos, $caminhoArquivo);

        if ($sucesso) {
            // CONTAR PÁGINAS DO ARQUIVO FINAL E SALVAR NO BANCO
            $totalPaginas = $this->contarPaginasPdf($caminhoArquivo);
            $this->salvarTotalPaginas($processo, $totalPaginas);

            // Adicionar carimbo ao PDF mesclado
            $caminhoCarimbado = $this->adicionarCarimboAoPdfComGhostscript($caminhoArquivo, $processo);

            if ($caminhoCarimbado) {
                return response()->download($caminhoCarimbado)->deleteFileAfterSend(true);
            } else {
                // Se não conseguiu carimbar, retorna o arquivo sem carimbo
                Log::warning('PDF mesclado com Ghostscript sem carimbo', ['processo_id' => $processo->id]);
                return response()->download($caminhoArquivo)->deleteFileAfterSend(true);
            }
        } else {
            throw new \Exception('Erro ao mesclar documentos com Ghostscript');
        }
    }

    private function salvarTotalPaginas(Processo $processo, int $totalPaginas): void
    {
        try {
            // Atualizar o total de páginas no processo
            $processo->contTotalPage = $totalPaginas;
            $processo->save();

            // Atualizar também todos os documentos do processo com o total de páginas
            Documento::where('processo_id', $processo->id)
                ->update(['contTotalPage' => $totalPaginas]);

            Log::info('Total de páginas salvo no banco', [
                'processo_id' => $processo->id,
                'total_paginas' => $totalPaginas
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao salvar total de páginas no banco', [
                'processo_id' => $processo->id,
                'total_paginas' => $totalPaginas,
                'erro' => $e->getMessage()
            ]);
        }
    }

    // =========================================================
    // MÉTODOS PRIVADOS - ARMAZENAMENTO DE DETALHES
    // =========================================================

    private function processarArquivos(Request $request, ProcessoDetalhe $detalhe): void
    {
        $arquivos = [
            'itens_e_seus_quantitativos_xml' => 'processarArquivoItens',
            'itens_especificaca_quantitativos_xml' => 'processarArquivoEspecificacao',
            'painel_preco_tce' => 'processarPainelPrecos',
            'anexo_pdf_analise_mercado' => 'salvarAnexo',
            'anexar_minuta' => 'salvarAnexo',
            'anexo_pdf_publicacoes' => 'salvarAnexo',
            'anexo_pdf_minuta_contrato' => 'salvarAnexo',
            'projeto_basico_pdf' => 'salvarAnexo'
        ];

        foreach ($arquivos as $campo => $metodo) {
            if ($request->hasFile($campo)) {
                $this->{$metodo}($request->file($campo), $detalhe, $campo);
            }
        }
    }

    private function processarArquivoItens($file, ProcessoDetalhe $detalhe, string $campo): void
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $itens = [];
        foreach ($rows as $index => $row) {
            if ($index === 0) continue;
            $itens[] = [
                'numero'     => $row[0] ?? null,
                'descricao'  => $row[1] ?? null,
                'und'        => $row[2] ?? null,
                'quantidade' => $row[3] ?? null,
            ];
        }

        $detalhe->{$campo} = json_encode($itens, JSON_UNESCAPED_UNICODE);
    }

    private function processarArquivoEspecificacao($file, ProcessoDetalhe $detalhe, string $campo): void
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $itens = [];
        foreach ($rows as $index => $row) {
            if ($index === 0) continue;
            $itens[] = [
                'item'              => $row[0] ?? null,
                'especificacoes'    => $row[1] ?? null,
                'unidade'           => $row[2] ?? null,
                'quantidade'        => $row[3] ?? null,
                'valor_unitario'    => $this->normalizarValor($row[4] ?? null),
                'valor_total'       =>$this->normalizarValor($row[5] ?? null),
            ];
        }

        $detalhe->{$campo} = json_encode($itens, JSON_UNESCAPED_UNICODE);
    }

    private function processarPainelPrecos($file, ProcessoDetalhe $detalhe, string $campo): void
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $painelPrecos = [];
        foreach ($rows as $index => $row) {
            if ($index === 0) continue;
            $painelPrecos[] = [
                'item' => $row[0] ?? null,
                'valor_tce_1' => $row[1] ?? null,
                'valor_tce_2' => $this->normalizarValor($row[2] ?? null),
                'valor_tce_3' => $this->normalizarValor($row[3] ?? null),
                'fornecedor_local' => $this->normalizarValor($row[4] ?? null),
                'media' => $this->normalizarValor($row[5] ?? null),
            ];
        }

        $detalhe->{$campo} = json_encode($painelPrecos, JSON_UNESCAPED_UNICODE);
    }

    private function normalizarValor($valor)
    {
        if (is_null($valor)) return null;

        // Remove espaços e converte para string
        $valor = trim((string)$valor);

        // Caso venha número puro (ex: 135)
        if (is_numeric($valor)) {
            return number_format((float)$valor, 2, ',', '.');
        }

        // Se vier no formato americano (1,323.20)
        if (preg_match('/^\d{1,3}(,\d{3})*\.\d{2}$/', $valor)) {
            $valor = str_replace(',', '', $valor); // remove separador de milhar americano
            return number_format((float)$valor, 2, ',', '.');
        }

        // Se vier no formato brasileiro, apenas padroniza
        if (preg_match('/^\d{1,3}(\.\d{3})*,\d{2}$/', $valor)) {
            return $valor;
        }

        // Último caso: troca ponto ↔ vírgula
        $valor = str_replace(['.', ','], ['#', '.'], $valor);
        $valor = str_replace('#', ',', $valor);

        return $valor;
    }


    private function salvarAnexo($file, ProcessoDetalhe $detalhe, string $campo): void
    {
        $filename = $campo . '_' . time() . '.' . $file->getClientOriginalExtension();
        $destinationPath = public_path('uploads/anexos');

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }

        $file->move($destinationPath, $filename);
        $detalhe->{$campo} = 'uploads/anexos/' . $filename;
    }

    private function getExcludedFields(): array
    {
        return [
            '_token',
            'processo_id',
            'itens_e_seus_quantitativos_xml',
            'painel_preco_tce',
            'anexo_pdf_analise_mercado',
            'anexar_minuta',
            'anexo_pdf_publicacoes',
            'itens_especificaca_quantitativos_xml',
            'anexo_pdf_minuta_contrato',
            'projeto_basico_pdf'
        ];
    }

    // =========================================================
    // MÉTODOS PRIVADOS - GERAÇÃO DE PDF
    // =========================================================

    private function validarRequisicaoPdf(Request $request, Processo $processo): array
    {
        $documento = $request->query('documento', 'capa');
        $dataSelecionada = $request->query('data');
        $parecerSelecionado = $request->query('parecer');

        if (empty($dataSelecionada)) {
            throw new \Exception('É necessário selecionar uma data antes de gerar o PDF.');
        }

        $assinantes = $this->processarAssinantes($request);
        $this->validarAssinantes($documento, $assinantes);

        return [
            'documento' => $documento,
            'dataSelecionada' => $dataSelecionada,
            'parecerSelecionado' => $parecerSelecionado,
            'assinantes' => $assinantes
        ];
    }

    private function processarAssinantes(Request $request): array
    {
        $assinantesJson = $request->query('assinantes');

        if (!$assinantesJson) {
            return [];
        }

        $assinantesDecoded = urldecode($assinantesJson);
        $assinantes = json_decode($assinantesDecoded, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("Erro ao decodificar JSON de assinantes: " . json_last_error_msg());
            throw new \Exception('Ocorreu um erro ao processar a lista de assinantes. Tente novamente.');
        }

        return $assinantes;
    }

    private function validarAssinantes(string $documento, array $assinantes): void
    {
        if ($documento === 'capa') {
            return;
        }

        if (empty($assinantes)) {
            throw new \Exception('É necessário adicionar pelo menos um assinante para este documento.');
        }

        $documentosComDoisAssinantes = ['estudo_tecnico'];

        if (in_array($documento, $documentosComDoisAssinantes) && count($assinantes) < 2) {
            throw new \Exception('Este documento requer duas assinaturas obrigatórias (ex.: responsável técnico e jurídico).');
        }
    }

    private function prepararDadosPdf(Processo $processo, array $validatedData): array
    {
        $processo->load(['detalhe', 'prefeitura']);

        return [
            'processo' => $processo,
            'prefeitura' => $processo->prefeitura,
            'detalhe' => $processo->detalhe,
            'dataGeracao' => now()->format('d/m/Y H:i:s'),
            'dataSelecionada' => $validatedData['dataSelecionada'],
            'assinantes' => $validatedData['assinantes'],
            'parecer' => $validatedData['parecerSelecionado'],
        ];
    }

    private function determinarViewPdf(Processo $processo, string $documento): string
    {
        $viewBase = "Admin.Processos.pdf";

        if ($this->isPregaoEletronico($processo)) {
            $procedimento = $this->formatarNomeArquivo($processo->tipo_procedimento?->name ?? '');
            $contratacao = $this->formatarNomeArquivo($processo->tipo_contratacao?->name ?? '');
            $view = "{$viewBase}.pregao_eletronico.{$procedimento}_{$contratacao}.{$documento}";
        } else {
            $modalidade = $this->formatarNomeArquivo($processo->modalidade?->name ?? '');
            $view = "{$viewBase}.{$modalidade}.{$documento}";
        }

        if (!view()->exists($view)) {
            throw new \Exception("O modelo de PDF para o documento '{$documento}' não foi encontrado. View: {$view}");
        }

        return $view;
    }

    private function salvarDocumento(Processo $processo, $pdf, array $validatedData): string
    {
        $numeroProcessoLimpo = str_replace(['/', '\\'], '_', $processo->numero_processo);
        $subpasta = $this->gerarSubpasta($processo, $validatedData['documento']);

        $diretorio = public_path("uploads/documentos/{$subpasta}");
        if (!file_exists($diretorio)) {
            mkdir($diretorio, 0777, true);
        }

        $nomeArquivo = "processo_{$numeroProcessoLimpo}_{$validatedData['documento']}_" . now()->format('Ymd_His') . '.pdf';
        $caminhoRelativo = "uploads/documentos/{$subpasta}/{$nomeArquivo}";
        $caminhoCompleto = "{$diretorio}/{$nomeArquivo}";

        $pdf->save($caminhoCompleto);
        $this->atualizarRegistroDocumento($processo, $validatedData['documento'], $validatedData['dataSelecionada'], $caminhoRelativo);

        return $caminhoCompleto;
    }

    private function gerarSubpasta(Processo $processo, string $documento): string
    {
        if ($this->isPregaoEletronico($processo)) {
            $procedimento = $this->formatarNomeArquivo($processo->tipo_procedimento?->name ?? '');
            $contratacao = $this->formatarNomeArquivo($processo->tipo_contratacao?->name ?? '');
            return "pregao_eletronico/{$procedimento}_{$contratacao}/{$documento}";
        }

        $modalidade = $this->formatarNomeArquivo($processo->modalidade?->name ?? 'sem_modalidade');
        return "{$modalidade}/{$documento}";
    }

    private function atualizarRegistroDocumento(Processo $processo, string $documento, string $dataSelecionada, string $caminhoRelativo): void
    {
        $documentoExistente = Documento::where('processo_id', $processo->id)
            ->where('tipo_documento', $documento)
            ->first();

        if ($documentoExistente) {
            $caminhoAntigo = public_path($documentoExistente->caminho);
            if (file_exists($caminhoAntigo)) {
                unlink($caminhoAntigo);
            }

            $documentoExistente->update([
                'data_selecionada' => $dataSelecionada,
                'caminho' => $caminhoRelativo,
                'gerado_em' => now(),
            ]);
        } else {
            Documento::create([
                'processo_id' => $processo->id,
                'tipo_documento' => $documento,
                'data_selecionada' => $dataSelecionada,
                'caminho' => $caminhoRelativo,
                'gerado_em' => now(),
            ]);
        }
    }

    private function processarAnexos(Processo $processo, string $documento, string $caminhoPrincipal): void
    {
        Log::info("Iniciando processamento de anexos para: {$documento}", [
            'caminho_principal' => $caminhoPrincipal,
            'tamanho_inicial' => file_exists($caminhoPrincipal) ? filesize($caminhoPrincipal) : 0
        ]);

        // Primeiro processa as junções específicas (como termo de referência)
        if ($documento === 'edital') {
            Log::info("Processando junção de termo/projeto básico para edital");
            $this->juntarTermoReferenciaOuProjetoBasico($processo, $caminhoPrincipal);

            // Verificar tamanho após junção
            if (file_exists($caminhoPrincipal)) {
                Log::info("Tamanho após junção termo/projeto: " . filesize($caminhoPrincipal));
            }
        }

        // Depois processa os anexos regulares
        $anexos = $this->obterAnexos($processo, $documento);

        if (!empty($anexos)) {
            Log::info("Processando anexos regulares para documento: {$documento}", [
                'pdf_base' => $caminhoPrincipal,
                'anexos' => $anexos,
                'tamanho_base' => file_exists($caminhoPrincipal) ? filesize($caminhoPrincipal) : 0
            ]);

            $resultado = $this->juntarPdfsComGhostscript($caminhoPrincipal, $anexos);

            if ($resultado) {
                Log::info("Anexos regulares processados com sucesso", [
                    'documento' => $documento,
                    'arquivo_final' => $resultado,
                    'tamanho_final' => filesize($resultado)
                ]);
            } else {
                Log::error("Falha ao processar anexos regulares", [
                    'documento' => $documento,
                    'pdf_base' => $caminhoPrincipal
                ]);
            }
        }

        // Processamento específico para SRP - DEVE SER O ÚLTIMO
        if ($documento === 'edital' && $processo->detalhe->tipo_srp === 'sim') {
            Log::info("Processando ATA de Registro de Preço para SRP");

            // Verificar tamanho antes de adicionar ATA
            if (file_exists($caminhoPrincipal)) {
                Log::info("Tamanho antes da ATA: " . filesize($caminhoPrincipal));
            }

            $this->gerarEJuntarAtaRegistroPreco($processo, $caminhoPrincipal);

            // Verificar tamanho final
            if (file_exists($caminhoPrincipal)) {
                Log::info("Tamanho final após ATA: " . filesize($caminhoPrincipal));
            }
        }

        Log::info("Processamento de anexos concluído para: {$documento}");
    }

    private function juntarTermoReferenciaOuProjetoBasico(Processo $processo, string $caminhoEdital): void
    {
        $tipoDocumento = $processo->modalidade === \App\Enums\ModalidadeEnum::CONCORRENCIA
            ? 'projeto_basico'
            : 'termo_referencia';

        $documento = Documento::where('processo_id', $processo->id)
            ->where('tipo_documento', $tipoDocumento)
            ->first();

        if ($documento && file_exists(public_path($documento->caminho))) {
            $caminhoDocumento = public_path($documento->caminho);

            Log::info("Juntando {$tipoDocumento} com edital", [
                'edital' => $caminhoEdital,
                'documento' => $caminhoDocumento,
                'tamanho_edital' => filesize($caminhoEdital),
                'tamanho_documento' => filesize($caminhoDocumento)
            ]);

            // CORREÇÃO: Usar juntarPdfsComGhostscript em vez de mesclarPdfsComGhostscript
            $sucesso = $this->juntarPdfsComGhostscript($caminhoEdital, [$caminhoDocumento]);

            if ($sucesso) {
                Log::info("{$tipoDocumento} juntado com sucesso ao edital", [
                    'caminho_final' => $caminhoEdital,
                    'tamanho_final' => filesize($caminhoEdital)
                ]);
            } else {
                Log::error('Falha ao juntar termo de referência/projeto básico com edital', [
                    'edital' => $caminhoEdital,
                    'documento' => $caminhoDocumento
                ]);
            }
        } else {
            Log::warning("Documento {$tipoDocumento} não encontrado para junção com edital", [
                'processo_id' => $processo->id,
                'tipo_documento' => $tipoDocumento
            ]);
        }
    }

    private function obterAnexos(Processo $processo, string $documento): array
    {
        $anexos = [];
        $camposAnexo = $this->mapeamentoAnexos[$documento] ?? null;

        if (!$camposAnexo) {
            return $anexos;
        }

        if (is_array($camposAnexo)) {
            foreach ($camposAnexo as $campo) {
                if (!empty($processo->detalhe->$campo)) {
                    $caminho = public_path($processo->detalhe->$campo);
                    $anexos[] = $caminho;
                    Log::info("Anexo encontrado para $documento", ['campo' => $campo, 'caminho' => $caminho, 'existe' => file_exists($caminho)]);
                }
            }
        } else {
            if (!empty($processo->detalhe->$camposAnexo)) {
                $caminho = public_path($processo->detalhe->$camposAnexo);
                $anexos[] = $caminho;
                Log::info("Anexo encontrado para $documento", ['campo' => $camposAnexo, 'caminho' => $caminho, 'existe' => file_exists($caminho)]);
            }
        }

        return $anexos;
    }

    private function juntarPdfsComGhostscript(string $pdfBasePath, array $anexoPaths): ?string
    {
        try {
            // Verificar se o arquivo base existe e é válido
            if (!file_exists($pdfBasePath) || filesize($pdfBasePath) === 0) {
                Log::error('Arquivo base não encontrado ou vazio', ['caminho' => $pdfBasePath]);
                return null;
            }

            // Filtrar apenas anexos válidos
            $anexosValidos = [];
            foreach ($anexoPaths as $anexoPath) {
                if (file_exists($anexoPath) && filesize($anexoPath) > 0) {
                    $anexosValidos[] = $anexoPath;
                    Log::info("Anexo válido encontrado", [
                        'caminho' => $anexoPath,
                        'tamanho' => filesize($anexoPath)
                    ]);
                } else {
                    Log::warning('Anexo ignorado (não existe ou está vazio)', ['caminho' => $anexoPath]);
                }
            }

            // Se não há anexos válidos, retornar o base original
            if (empty($anexosValidos)) {
                Log::info('Nenhum anexo válido para mesclar', ['base' => $pdfBasePath]);
                return $pdfBasePath;
            }

            // Criar arquivo temporário para o resultado
            $tempOutput = tempnam(sys_get_temp_dir(), 'merged_pdf_') . '.pdf';

            // Juntar base + anexos - ORDEM CORRETA: base primeiro, depois anexos
            $todosArquivos = array_merge([$pdfBasePath], $anexosValidos);

            Log::info("Mesclando PDFs com Ghostscript - INÍCIO", [
                'arquivo_base' => $pdfBasePath,
                'tamanho_base' => filesize($pdfBasePath),
                'anexos_validos' => $anexosValidos,
                'total_arquivos' => count($todosArquivos),
                'arquivo_saida_temp' => $tempOutput
            ]);

            $sucesso = $this->mesclarPdfsComGhostscript($todosArquivos, $tempOutput);

            if ($sucesso && file_exists($tempOutput) && filesize($tempOutput) > 0) {
                // Verificar se o arquivo temporário tem conteúdo
                $tamanhoTemp = filesize($tempOutput);
                Log::info("Arquivo temporário gerado com sucesso", [
                    'caminho_temp' => $tempOutput,
                    'tamanho_temp' => $tamanhoTemp
                ]);

                // Substituir o arquivo base pelo resultado mesclado
                copy($tempOutput, $pdfBasePath);
                unlink($tempOutput);

                $tamanhoFinal = filesize($pdfBasePath);
                Log::info("PDFs mesclados com sucesso - FIM", [
                    'arquivo_final' => $pdfBasePath,
                    'tamanho_final' => $tamanhoFinal,
                    'tamanho_esperado' => filesize($pdfBasePath) + array_sum(array_map('filesize', $anexosValidos))
                ]);

                return $pdfBasePath;
            } else {
                Log::error('Falha ao mesclar PDFs com Ghostscript', [
                    'sucesso' => $sucesso,
                    'temp_output_existe' => file_exists($tempOutput),
                    'temp_output_tamanho' => file_exists($tempOutput) ? filesize($tempOutput) : 0,
                    'arquivos_entrada' => $todosArquivos
                ]);

                // Limpar arquivo temporário em caso de erro
                if (file_exists($tempOutput)) {
                    unlink($tempOutput);
                }
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Exceção ao mesclar PDFs com Ghostscript', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'pdf_base' => $pdfBasePath,
                'anexos' => $anexoPaths
            ]);
            return null;
        }
    }

    private function mesclarPdfsComGhostscript(array $arquivos, string $outputPath): bool
    {
        $listaArquivos = null;

        try {
            // VERIFICAÇÃO CRÍTICA: garantir que todos os arquivos existem e são válidos
            $arquivosValidos = [];
            foreach ($arquivos as $index => $arquivo) {
                if (!file_exists($arquivo)) {
                    Log::error('Arquivo não encontrado para mesclagem', [
                        'arquivo' => $arquivo,
                        'index' => $index,
                        'todos_arquivos' => $arquivos
                    ]);
                    return false;
                }

                $tamanho = filesize($arquivo);
                if ($tamanho === 0) {
                    Log::error('Arquivo vazio encontrado', [
                        'arquivo' => $arquivo,
                        'index' => $index,
                        'tamanho' => $tamanho
                    ]);
                    return false;
                }

                $arquivosValidos[] = $arquivo;
                Log::debug("Arquivo validado para mesclagem", [
                    'arquivo' => $arquivo,
                    'tamanho' => $tamanho,
                    'index' => $index
                ]);
            }

            // Criar arquivo de lista para Ghostscript
            $listaArquivos = tempnam(sys_get_temp_dir(), 'gs_list_');
            file_put_contents($listaArquivos, implode("\n", $arquivosValidos));

            $comando = sprintf(
                'gs -dBATCH -dNOPAUSE -q -sDEVICE=pdfwrite -dPDFSETTINGS=/prepress -sOutputFile="%s" @"%s"',
                $outputPath,
                $listaArquivos
            );

            Log::info('Executando Ghostscript - COMANDO', [
                'comando' => $comando,
                'arquivos_entrada' => $arquivosValidos,
                'quantidade_arquivos' => count($arquivosValidos),
                'arquivo_saida' => $outputPath
            ]);

            $output = [];
            $returnCode = 0;
            exec($comando . ' 2>&1', $output, $returnCode);

            // Aguardar um pouco para garantir que o processo terminou
            sleep(2);

            $outputExiste = file_exists($outputPath);
            $outputTamanho = $outputExiste ? filesize($outputPath) : 0;

            if ($returnCode === 0 && $outputExiste && $outputTamanho > 0) {
                Log::info('PDFs mesclados com sucesso usando Ghostscript', [
                    'arquivo_saida' => $outputPath,
                    'tamanho' => $outputTamanho,
                    'return_code' => $returnCode,
                    'output_ghostscript' => implode("\n", array_slice($output, 0, 10)) // Primeiras 10 linhas do output
                ]);
                return true;
            } else {
                Log::error('Erro ao mesclar PDFs com Ghostscript', [
                    'return_code' => $returnCode,
                    'output' => implode("\n", $output),
                    'arquivos_entrada' => $arquivosValidos,
                    'arquivo_saida_existe' => $outputExiste,
                    'arquivo_saida_tamanho' => $outputTamanho
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Exceção ao mesclar PDFs com Ghostscript', [
                'erro' => $e->getMessage(),
                'arquivos' => $arquivos
            ]);
            return false;
        } finally {
            if ($listaArquivos && file_exists($listaArquivos)) {
                unlink($listaArquivos);
            }
        }
    }

    private function gerarEJuntarAtaRegistroPreco(Processo $processo, string $caminhoPrincipal): void
    {
        try {
            Log::info("Gerando ATA de Registro de Preço", ['processo_id' => $processo->id]);

            // Verificar se o arquivo principal ainda existe e é válido
            if (!file_exists($caminhoPrincipal) || filesize($caminhoPrincipal) === 0) {
                Log::error('Arquivo principal não encontrado ou vazio antes de gerar ATA', [
                    'caminho' => $caminhoPrincipal
                ]);
                return;
            }

            $viewAta = $this->determinarViewPdf($processo, 'ata_registro_preco');
            $data = $this->prepararDadosPdf($processo, [
                'dataSelecionada' => now()->format('Y-m-d'),
                'assinantes' => [],
                'parecerSelecionado' => null,
            ]);

            $pdfAta = Pdf::loadView($viewAta, $data)->setPaper('a4', 'portrait');
            $arquivoAta = storage_path('app/temp_ata_' . $processo->id . '_' . uniqid() . '.pdf');
            $pdfAta->save($arquivoAta);

            if (file_exists($arquivoAta) && filesize($arquivoAta) > 0) {
                Log::info("ATA gerada com sucesso", [
                    'caminho_ata' => $arquivoAta,
                    'tamanho_ata' => filesize($arquivoAta),
                    'caminho_principal' => $caminhoPrincipal,
                    'tamanho_principal' => filesize($caminhoPrincipal)
                ]);

                // Juntar principal + ATA (não substituir!)
                $sucesso = $this->juntarPdfsComGhostscript($caminhoPrincipal, [$arquivoAta]);

                if ($sucesso) {
                    Log::info("ATA juntada com sucesso ao edital", [
                        'caminho_final' => $caminhoPrincipal,
                        'tamanho_final' => filesize($caminhoPrincipal)
                    ]);
                } else {
                    Log::error('Falha ao juntar ata de registro de preço', [
                        'principal' => $caminhoPrincipal,
                        'ata' => $arquivoAta
                    ]);
                }

                // Limpar arquivo temporário
                unlink($arquivoAta);
            } else {
                Log::error('ATA não foi gerada corretamente', [
                    'arquivo_ata' => $arquivoAta,
                    'existe' => file_exists($arquivoAta),
                    'tamanho' => file_exists($arquivoAta) ? filesize($arquivoAta) : 0
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao gerar e juntar ATA de registro de preço', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    // =========================================================
    // MÉTODOS PRIVADOS - CARIMBAGEM DE PDF
    // =========================================================

    private function adicionarCarimboAoPdfComGhostscript(string $caminhoPdf, Processo $processo): ?string
    {
        $paginasTemp = [];

        try {
            // Primeiro, contar as páginas do PDF
            $pageCount = $this->contarPaginasPdf($caminhoPdf);

            if ($pageCount === 0) {
                Log::error('PDF vazio ou inválido', ['caminho' => $caminhoPdf]);
                return null;
            }

            $caminhoCarimbado = str_replace('.pdf', '_carimbado.pdf', $caminhoPdf);

            // Para cada página, adicionar carimbo
            for ($pagina = 1; $pagina <= $pageCount; $pagina++) {
                $paginaAtual = $pagina;

                // Criar PDF temporário com carimbo para esta página
                $pdf = new Fpdi();
                $this->configurarFonte($pdf);

                $pdf->setSourceFile($caminhoPdf);
                $tplId = $pdf->importPage($pagina);
                $pdf->AddPage();
                $pdf->useTemplate($tplId);

                // Adicionar carimbo (assumindo que a capa é a primeira página)
                if ($pagina !== 1) {
                    $this->adicionarCarimbo($pdf, $processo, $paginaAtual - 1, $pageCount - 1);
                }

                $tempPath = sys_get_temp_dir() . "/pagina_{$pagina}_" . uniqid() . '.pdf';
                $pdf->Output($tempPath, 'F');
                $paginasTemp[] = $tempPath;
            }

            // Mesclar todas as páginas carimbadas
            $sucesso = $this->mesclarPdfsComGhostscript($paginasTemp, $caminhoCarimbado);

            if ($sucesso && file_exists($caminhoCarimbado) && filesize($caminhoCarimbado) > 0) {
                // Substituir o arquivo original pelo carimbado
                if (file_exists($caminhoPdf)) {
                    unlink($caminhoPdf);
                }
                rename($caminhoCarimbado, $caminhoPdf);
                return $caminhoPdf;
            } else {
                Log::error('Falha ao mesclar páginas carimbadas', [
                    'caminho_original' => $caminhoPdf,
                    'caminho_carimbado' => $caminhoCarimbado,
                    'sucesso' => $sucesso,
                    'arquivo_existe' => file_exists($caminhoCarimbado),
                    'tamanho' => file_exists($caminhoCarimbado) ? filesize($caminhoCarimbado) : 0
                ]);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Erro ao adicionar carimbo ao PDF com Ghostscript', [
                'caminho' => $caminhoPdf,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        } finally {
            // Limpar arquivos temporários
            foreach ($paginasTemp as $tempFile) {
                if (file_exists($tempFile)) {
                    unlink($tempFile);
                }
            }
        }
    }

    // =========================================================
    // MÉTODOS PRIVADOS - DOWNLOAD DE TODOS OS DOCUMENTOS
    // =========================================================

    private function getOrdemDocumentos(): array
    {
        return [
            'capa',
            'formalizacao',
            'autorizacao',
            'estudo_tecnico',
            'projeto_basico',
            'analise_mercado',
            'disponibilidade_orçamento',
            'termo_referencia',
            'minutas',
            'parecer_juridico',
            'autorizacao_abertura_procedimento',
            'abertura_fase_externa',
            'avisos_licitacao',
            'publicacoes_avisos_licitacao',
            'edital'
        ];
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

    private function adicionarCarimbo(Fpdi $pdf, Processo $processo, int $paginaAtual, int $pageCountTotal, int $paginaInicial = 1): void
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

        // CALCULAR PÁGINA ABSOLUTA (inicialização)
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

    // =========================================================
    // MÉTODOS AUXILIARES
    // =========================================================

    private function isPregaoEletronico(Processo $processo): bool
    {
        return $processo->modalidade?->name == '4' ||
            strtoupper($processo->modalidade?->name ?? '') == 'PREGAO ELETRONICO' ||
            stripos($processo->modalidade?->name ?? '', 'pregao') !== false;
    }

    private function formatarNomeArquivo(string $nome): string
    {
        $nome = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $nome));
        return str_replace(' ', '_', $nome);
    }
}
