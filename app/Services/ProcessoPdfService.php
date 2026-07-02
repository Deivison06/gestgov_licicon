<?php

namespace App\Services;

use App\Models\Processo;
use App\Models\Documento;
use Barryvdh\DomPDF\Facade\Pdf;
use setasign\Fpdi\Tcpdf\Fpdi;
use App\Services\ProcessoDocumentoService;
use Illuminate\Support\Facades\Log;

class ProcessoPdfService extends AbstractService
{
    use \App\Services\Assinatura\ResolveLegacyAssinantesTrait;

    public function __construct(
        protected ProcessoDocumentoService $documentoService,
        protected \App\Services\Assinatura\PdfWatermarkService $watermarkService,
        protected \App\Services\Assinatura\DocumentoVersaoService $versaoService,
        protected \App\Services\Assinatura\SolicitacaoService $solicitacaoService,
    ) {}

    public function gerarPdf(Processo $processo, array $requestData): array
    {
        Log::info('Iniciando geração de PDF', [
            'processo_id' => $processo->id,
            'documento' => $requestData['documento'] ?? null,
        ]);

        $validatedData = $this->validarRequisicaoPdf($requestData, $processo);
        $data = $this->prepararDadosPdf($processo, $validatedData);

        $documentoSolicitado = $validatedData['documento'];
        $documentoParaView = $this->normalizarDocumentoParaView($documentoSolicitado);

        $view = $this->determinarViewPdf($processo, $documentoParaView);

        Log::info('View selecionada para PDF', ['view' => $view]);

        $pdf = Pdf::loadView($view, $data)->setPaper('a4', 'portrait');

        $caminhoCompleto = $this->salvarDocumento($processo, $pdf, $validatedData);

        // Processa anexos como se fosse "edital" quando for "edital_republicado"
        $documentoParaAnexos = $this->normalizarDocumentoParaAnexos($documentoSolicitado);
        $this->processarAnexos($processo, $documentoParaAnexos, $caminhoCompleto);

        // Capa específica para "Edital Republicado"
        if ($documentoSolicitado === 'edital_republicado') {
            $this->adicionarCapaRepublicacaoAoPdf($caminhoCompleto, $processo, null);
        }

        Log::info('PDF gerado com sucesso', [
            'processo_id' => $processo->id,
            'documento' => $documentoSolicitado,
            'caminho' => $caminhoCompleto
        ]);

        // =====================================================================
        // Auto-trigger DESATIVADO — rodada apenas via "Solicitar Assinatura".
        // =====================================================================
        $assinantes = [];
        $infoAssinatura = null;

        if (count($assinantes) > 0) {
            try {
                $infoAssinatura = $this->iniciarRodadaAssinatura(
                    $processo,
                    $documentoSolicitado,
                    $caminhoCompleto,
                    $requestData['modo'] ?? 'paralelo',
                    (int) ($requestData['prazo_dias'] ?? 7),
                    $assinantes,
                    (int) ($requestData['solicitado_por_user_id'] ?? auth()->id() ?? 0)
                );
            } catch (\Throwable $e) {
                Log::error('Falha ao iniciar rodada de assinatura — Inicial', [
                    'processo_id' => $processo->id,
                    'documento'   => $documentoSolicitado,
                    'erro'        => $e->getMessage(),
                ]);
            }
        }

        return array_filter([
            'success'    => true,
            'caminho'    => $caminhoCompleto,
            'documento'  => $documentoSolicitado,
            'assinatura' => $infoAssinatura,
        ], fn ($v) => $v !== null);
    }

    /**
     * Pipeline assinatura: marca d'água → DocumentoVersao polimórfico em Documento → rodada.
     */
    private function iniciarRodadaAssinatura(
        Processo $processo,
        string $tipoDocumento,
        string $caminhoPdfOriginal,
        string $modo,
        int $prazoDias,
        array $assinantes,
        int $solicitadoPorUserId
    ): array {
        $caminhoAbsoluto = $this->resolverCaminhoAbsoluto($caminhoPdfOriginal);
        $caminhoRascunho = $this->watermarkService->aplicarMarcaDagua(
            $caminhoAbsoluto,
            'AGUARDANDO ASSINATURAS'
        );

        $documento = Documento::query()
            ->where('processo_id', $processo->id)
            ->where('tipo_documento', $tipoDocumento)
            ->latest('id')
            ->firstOrFail();

        $versao = $this->versaoService->criarRascunho(
            $documento,
            $caminhoRascunho,
            $solicitadoPorUserId
        );

        $listaAssinantes = collect($assinantes)
            ->map(fn ($a, $idx) => [
                'user_id' => (int) ($a['id'] ?? $a['user_id'] ?? 0),
                'ordem'   => $modo === 'sequencial' ? (int) ($a['ordem'] ?? $idx + 1) : 0,
            ])
            ->filter(fn ($a) => $a['user_id'] > 0)
            ->values()
            ->all();

        $solicitacoes = $this->solicitacaoService->criarRodada(
            $versao,
            $listaAssinantes,
            $solicitadoPorUserId,
            now()->addDays(max(1, $prazoDias))
        );

        return [
            'versao_id'          => $versao->id,
            'total_solicitacoes' => $solicitacoes->count(),
            'modo'               => $modo,
            'prazo_dias'         => $prazoDias,
        ];
    }

    private function resolverCaminhoAbsoluto(string $caminho): string
    {
        if (is_file($caminho)) {
            return $caminho;
        }
        $candidato = public_path($caminho);
        return is_file($candidato) ? $candidato : $caminho;
    }

    private function normalizarDocumentoParaView(string $documento): string
    {
        if ($documento === 'edital_republicado') {
            return 'edital';
        }

        return $documento;
    }

    private function normalizarDocumentoParaAnexos(string $documento): string
    {
        if ($documento === 'edital_republicado') {
            return 'edital';
        }

        return $documento;
    }

    private function normalizarDocumentoParaPersistencia(string $documento): string
    {
        if ($documento === 'edital_republicado') {
            return 'republicacao_edital';
        }

        return $documento;
    }
    private function adicionarCapaRepublicacaoAoPdf(string $caminhoPdf, Processo $processo, ?string $justificativa = null): void
    {
        try {
            // Usar a função determinarViewRepublicacao para obter a view correta
            $viewCapa = $this->determinarViewRepublicacao($processo);

            Log::debug('Teste:', [
                'view' => $viewCapa,
            ]);

            // Preparar os dados usando o método existente prepararDadosPdf
            // para garantir que todas as variáveis necessárias estejam disponíveis
            $dadosBase = $this->prepararDadosPdf($processo, [
                'dataSelecionada' => now()->format('Y-m-d'),
                'assinantes' => [],
                'parecerSelecionado' => null,
            ]);

            // Adicionar os dados específicos da capa de republicação
            $dataCapa = array_merge($dadosBase, [
                'titulo' => 'REPUBLICAÇÃO DE EDITAL',
                'justificativa' => $justificativa,
                'numero_republicacao' => Documento::where('processo_id', $processo->id)
                        ->where('tipo_documento', 'like', '%republicacao%')
                        ->count() + 1,
            ]);

            // Usar a view determinada dinamicamente
            $pdfCapa = Pdf::loadView($viewCapa, $dataCapa)
                ->setPaper('a4', 'portrait');

            $caminhoCapa = storage_path('app/temp_capa_republicacao_' . uniqid() . '.pdf');
            $pdfCapa->save($caminhoCapa);

            $this->mesclarPdfsComGhostscript([$caminhoCapa, $caminhoPdf], $caminhoPdf);

            if (file_exists($caminhoCapa)) {
                unlink($caminhoCapa);
            }

            Log::info('Capa de republicação adicionada com sucesso', [
                'processo_id' => $processo->id,
                'view_usada' => $viewCapa,
                'caminho_pdf' => $caminhoPdf
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao adicionar capa de republicação (service)', [
                'processo_id' => $processo->id,
                'caminho_pdf' => $caminhoPdf,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Opcional: relançar a exceção se quiser que o erro seja tratado externamente
            // throw $e;
        }
    }

    public function baixarDocumento(Processo $processo, string $tipo)
    {
        $documento = Documento::where('processo_id', $processo->id)
            ->where('tipo_documento', $tipo)
            ->firstOrFail();

        return response()->download(public_path($documento->caminho));
    }

    public function gerarCaminhoTodosDocumentos(Processo $processo): string
    {
        $ordem = $this->documentoService->getOrdemDocumentosParaDownload($processo);
        $documentos = Documento::where('processo_id', $processo->id)->get()->keyBy('tipo_documento');

        return $this->baixarTodosDocumentosComGhostscript($processo, $ordem, $documentos);
    }

    public function baixarTodosDocumentos(Processo $processo)
    {
        $caminho = $this->gerarCaminhoTodosDocumentos($processo);
        
        return response()
            ->download($caminho)
            ->deleteFileAfterSend(true);
    }

    private function baixarTodosDocumentosComGhostscript(Processo $processo, array $ordem, $documentos): string
    {
        $arquivos = [];

        foreach ($ordem as $tipo) {

            // 🔹 Regra especial para EDITAL (pega o mais recente)
            if ($tipo === 'edital') {
                $editalMaisRecente = Documento::where('processo_id', $processo->id)
                    ->where(function ($q) {
                        $q->where('tipo_documento', 'republicacao_edital')
                            ->orWhere('tipo_documento', 'edital_adiado')
                            ->orWhere('tipo_documento', 'edital');
                    })
                    ->latest('gerado_em')
                    ->first();

                if ($editalMaisRecente) {
                    $caminho = public_path($editalMaisRecente->caminho);
                    if (file_exists($caminho)) {
                        $arquivos[] = $caminho;
                    }
                }

                continue;
            }

            // 🔹 Demais documentos
            if (!isset($documentos[$tipo])) {
                continue;
            }

            $caminho = public_path($documentos[$tipo]->caminho);
            if (!file_exists($caminho)) {
                continue;
            }

            $arquivos[] = $caminho;
        }

        if (empty($arquivos)) {
            throw new \Exception('Nenhum documento encontrado para mesclar.');
        }

        $nomeArquivo = "processo_" .
            str_replace(['/', '\\'], '_', $processo->numero_processo) .
            "_todos_documentos_" . now()->format('Ymd_His') . '.pdf';

        $caminhoArquivo = public_path('uploads/documentos/' . $nomeArquivo);

        $caminhoCarimbado = $this->mesclarECarimbarEmLote($arquivos, $caminhoArquivo, $processo, 'iniciar');

        if ($caminhoCarimbado) {
            $totalPaginas = $this->contarPaginasPdf($caminhoCarimbado);
            $this->salvarTotalPaginas($processo, $totalPaginas);
            return $caminhoCarimbado;
        }

        $sucesso = $this->mesclarPdfsComGhostscript($arquivos, $caminhoArquivo);

        if (!$sucesso) {
            throw new \Exception('Erro ao mesclar documentos com Ghostscript');
        }

        $totalPaginas = $this->contarPaginasPdf($caminhoArquivo);
        $this->salvarTotalPaginas($processo, $totalPaginas);

        return $caminhoArquivo;
    }


    // Métodos privados mantidos do original, mas organizados...
    private function validarRequisicaoPdf(array $requestData, Processo $processo): array
    {
        $documento = $requestData['documento'] ?? 'capa';
        $dataSelecionada = $requestData['data'] ?? null;
        $parecerSelecionado = $requestData['parecer'] ?? null;
        $documentoId = isset($requestData['documento_id']) && $requestData['documento_id'] !== ''
            ? (int) $requestData['documento_id']
            : null;

        if (empty($dataSelecionada)) {
            throw new \Exception('É necessário selecionar uma data antes de gerar o PDF.');
        }

        $assinantes = $this->processarAssinantes($requestData);
        $this->validarAssinantes($documento, $assinantes);

        return [
            'documento' => $documento,
            'documento_id' => $documentoId,
            'dataSelecionada' => $dataSelecionada,
            'parecerSelecionado' => $parecerSelecionado,
            'assinantes' => $assinantes
        ];
    }

    private function processarAssinantes(array $requestData): array
    {
        $assinantesJson = $requestData['assinantes'] ?? null;

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

    public function prepararDadosPdf(Processo $processo, array $validatedData): array
    {
        $processo->load(['detalhe', 'prefeitura', 'etp.itens', 'etp.lotes.itens', 'pesquisaPrecoItens']);

        // Se houver um ETP vinculado, priorizamos os dados dinâmicos do ETP para as tabelas de itens
        if ($processo->etp) {
            $itensDinamicos = $processo->etp->transformarItensParaFormatoPdf();

            if ($processo->detalhe) {
                $processo->detalhe->itens_e_seus_quantitativos_xml        = $itensDinamicos;
                $processo->detalhe->descricao_e_quantitativos_itens_xml   = $itensDinamicos;
                $processo->detalhe->itens_especificaca_quantitativos_xml  = $this->construirItensTr($processo, $itensDinamicos);
            }
        }

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

    public function determinarViewPdf(Processo $processo, string $documento): string
    {
        $viewBase = "Admin.Processos.pdf";
        $procedimento = $this->formatarNomeArquivo($processo->tipo_procedimento?->name ?? '');
        $contratacao = $this->formatarNomeArquivo($processo->tipo_contratacao?->name ?? '');

        if ($processo->modalidade === \App\Enums\ModalidadeEnum::PREGAO_ELETRONICO) {
            $view = "{$viewBase}.pregao_eletronico.{$procedimento}_{$contratacao}.{$documento}";
        } elseif ($processo->modalidade === \App\Enums\ModalidadeEnum::DISPENSA) {
            if ($processo->tipo_procedimento === \App\Enums\TipoProcedimentoEnum::OBRA) {
                $view = "{$viewBase}.dispensa.{$procedimento}.{$documento}";
            } else {
                $view = "{$viewBase}.dispensa.{$procedimento}_{$contratacao}.{$documento}";
            }
        } elseif ($processo->modalidade === \App\Enums\ModalidadeEnum::CONCORRENCIA) {
            $view = "{$viewBase}.concorrencia.{$documento}";
        } else {
            if ($documento === 'contrato') {
                // Ex: Admin.Processos.pdf.inexigibilidade.tecnico
                $view = "Admin.Processos.contrato.inexigibilidade.{$contratacao}";
            } else {
                // Caso existam outros documentos para inexigibilidade que não seguem a regra do tipo
                $view = "{$viewBase}.inexigibilidade.{$contratacao}.{$documento}";
            }
        }

        if (!view()->exists($view)) {
            throw new \Exception("Modelo de PDF não encontrado. View: {$view}");
        }

        return $view;
    }

    /**
     * Determinar a view para minuta de republicação usando a mesma lógica do determinarViewPdf
     */
    public function determinarViewRepublicacao(Processo $processo): string
    {
        $viewBase = "Admin.Processos.pdf";
        $procedimento = $this->formatarNomeArquivo($processo->tipo_procedimento?->name ?? '');
        $contratacao = $this->formatarNomeArquivo($processo->tipo_contratacao?->name ?? '');

        if ($processo->modalidade === \App\Enums\ModalidadeEnum::PREGAO_ELETRONICO) {
            $view = "{$viewBase}.pregao_eletronico.{$procedimento}_{$contratacao}.capa_republicacao";
        } elseif ($processo->modalidade === \App\Enums\ModalidadeEnum::DISPENSA) {
            if ($processo->tipo_procedimento === \App\Enums\TipoProcedimentoEnum::OBRA) {
                $view = "{$viewBase}.dispensa.{$procedimento}.capa_republicacao";
            } else {
                $view = "{$viewBase}.dispensa.{$procedimento}_{$contratacao}.capa_republicacao";
            }
        } elseif ($processo->modalidade === \App\Enums\ModalidadeEnum::CONCORRENCIA) {
            $view = "{$viewBase}.concorrencia.capa_republicacao";
        } else {
            $modalidade = $this->formatarNomeArquivo($processo->modalidade?->name ?? '');
            $view = "{$viewBase}.{$modalidade}.capa_republicacao";
        }

        // Se não existir a view específica de capa_republicacao, tentar a view padrão
        if (!view()->exists($view)) {
            $view = "{$viewBase}.modelos.capa_republicacao";

            if (!view()->exists($view)) {
                throw new \Exception("Modelo de minuta de republicação não encontrado. View: {$view}");
            }
        }

        return $view;
    }

    private function salvarDocumento(Processo $processo, $pdf, array $validatedData): string
    {
        $numeroProcessoLimpo = str_replace(['/', '\\'], '_', $processo->numero_processo);

        $documentoSolicitado = $validatedData['documento'];
        $tipoPersistencia = $this->normalizarDocumentoParaPersistencia($documentoSolicitado);

        $subpasta = $this->gerarSubpasta($processo, $tipoPersistencia);

        $diretorio = public_path("uploads/documentos/{$subpasta}");
        if (!file_exists($diretorio)) {
            mkdir($diretorio, 0777, true);
        }

        $nomeArquivo = "processo_{$numeroProcessoLimpo}_{$tipoPersistencia}_" . now()->format('Ymd_His') . '.pdf';
        $caminhoRelativo = "uploads/documentos/{$subpasta}/{$nomeArquivo}";
        $caminhoCompleto = "{$diretorio}/{$nomeArquivo}";

        $pdf->save($caminhoCompleto);

        $this->atualizarRegistroDocumento(
            $processo,
            $tipoPersistencia,
            $validatedData['dataSelecionada'],
            $caminhoRelativo,
            $validatedData['documento_id'] ?? null
        );

        return $caminhoCompleto;
    }

    public function gerarSubpasta(Processo $processo, string $documento): string
    {
        if ($processo->modalidade === \App\Enums\ModalidadeEnum::DISPENSA) {
            $procedimento = $this->formatarNomeArquivo($processo->tipo_procedimento?->name ?? '');
            $contratacao = $this->formatarNomeArquivo($processo->tipo_contratacao?->name ?? '');
            return "dispensa/{$procedimento}_{$contratacao}/{$documento}";
        }

        if ($processo->modalidade === \App\Enums\ModalidadeEnum::PREGAO_ELETRONICO) {
            $procedimento = $this->formatarNomeArquivo($processo->tipo_procedimento?->name ?? '');
            $contratacao = $this->formatarNomeArquivo($processo->tipo_contratacao?->name ?? '');
            return "pregao_eletronico/{$procedimento}_{$contratacao}/{$documento}";
        }

        if ($processo->modalidade === \App\Enums\ModalidadeEnum::CONCORRENCIA) {
            $procedimento = $this->formatarNomeArquivo($processo->tipo_procedimento?->name ?? '');
            $contratacao = $this->formatarNomeArquivo($processo->tipo_contratacao?->name ?? '');
            return "concorrencia/{$procedimento}_{$contratacao}/{$documento}";
        }
        if ($processo->modalidade === \App\Enums\ModalidadeEnum::INEXIGIBILIDADE) {
            $procedimento = $this->formatarNomeArquivo($processo->tipo_procedimento?->name ?? '');
            $contratacao = $this->formatarNomeArquivo($processo->tipo_contratacao?->name ?? '');
            return "inexigibilidade/{$procedimento}_{$contratacao}/{$documento}";
        }

        $modalidade = $this->formatarNomeArquivo($processo->modalidade?->name ?? 'sem_modalidade');
        return "{$modalidade}/{$documento}";
    }

    private function atualizarRegistroDocumento(
        Processo $processo,
        string $documento,
        string $dataSelecionada,
        string $caminhoRelativo,
        ?int $documentoId = null
    ): void {
        // Quando o front envia documento_id (caso das republicações dinâmicas),
        // atualizamos exatamente aquele registro em vez de "o primeiro do tipo".
        if ($documentoId) {
            $documentoExistente = Documento::where('processo_id', $processo->id)
                ->where('id', $documentoId)
                ->first();
        } else {
            $documentoExistente = Documento::where('processo_id', $processo->id)
                ->where('tipo_documento', $documento)
                ->first();
        }

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

        if ($documento === 'edital') {
            Log::info("Processando junção de termo/projeto básico para edital");
            $this->juntarTermoReferenciaOuProjetoBasico($processo, $caminhoPrincipal);
        }

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

        if ($documento === 'edital' && $processo->detalhe && $processo->detalhe->tipo_srp === 'sim') {
            Log::info("Processando ATA de Registro de Preço para SRP");
            $this->gerarEJuntarAtaRegistroPreco($processo, $caminhoPrincipal);
        }
         // Quando geramos o PDF da MINUTA, embutimos automaticamente o Edital
        // entre páginas separadoras ("INÍCIO DO EDITAL" / "FIM DO EDITAL").
        // Substitui o antigo upload manual anexar_minuta.
        if ($documento === 'minutas') {
            $this->gerarEJuntarEditalNaMinuta($processo, $caminhoPrincipal);
        }

        Log::info("Processamento de anexos concluído para: {$documento}");
    }

    private function juntarTermoReferenciaOuProjetoBasico(Processo $processo, string $caminhoEdital): void
    {
        $tipoDocumento = $processo->modalidade === \App\Enums\ModalidadeEnum::CONCORRENCIA
            || ($processo->modalidade === \App\Enums\ModalidadeEnum::DISPENSA
                && $processo->tipo_procedimento === \App\Enums\TipoProcedimentoEnum::OBRA)
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
        $mapeamentoAnexos = $this->documentoService->getMapeamentoAnexos();
        $camposAnexo = $mapeamentoAnexos[$documento] ?? null;

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
            if (!file_exists($pdfBasePath) || filesize($pdfBasePath) === 0) {
                Log::error('Arquivo base não encontrado ou vazio', ['caminho' => $pdfBasePath]);
                return null;
            }

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

            if (empty($anexosValidos)) {
                Log::info('Nenhum anexo válido para mesclar', ['base' => $pdfBasePath]);
                return $pdfBasePath;
            }

            $tempOutput = tempnam(sys_get_temp_dir(), 'merged_pdf_') . '.pdf';
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
                $tamanhoTemp = filesize($tempOutput);
                Log::info("Arquivo temporário gerado com sucesso", [
                    'caminho_temp' => $tempOutput,
                    'tamanho_temp' => $tamanhoTemp
                ]);

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

    public function mesclarPdfsComGhostscript(array $arquivos, string $outputPath): bool
    {
        $listaArquivos = null;

        try {
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

            $listaArquivos = tempnam(sys_get_temp_dir(), 'gs_list_');
            file_put_contents($listaArquivos, implode("\n", $arquivosValidos));

            // Tenta pdfunite primeiro (Extremamente mais rápido, não re-encoda)
            $returnCode = 0;
            exec('command -v pdfunite', $out, $returnCode);
            if ($returnCode === 0) {
                $arquivosStr = implode(' ', array_map('escapeshellarg', $arquivosValidos));
                $cmdUnite = "pdfunite {$arquivosStr} " . escapeshellarg($outputPath);
                Log::info('Executando pdfunite (Alta velocidade)', ['comando' => $cmdUnite]);
                exec($cmdUnite . ' 2>&1', $output, $returnCode);
                if ($returnCode === 0 && file_exists($outputPath) && filesize($outputPath) > 0) {
                    return true;
                }
            }

            // Fallback Ghostscript (Otimizado, sem prepress para ser mais rápido)
            $comando = sprintf(
                'gs -dBATCH -dNOPAUSE -q -sDEVICE=pdfwrite -dFastWebView -dCompatibilityLevel=1.4 -sOutputFile="%s" @"%s"',
                $outputPath,
                $listaArquivos
            );

            Log::info('Executando Ghostscript (Fallback)', [
                'comando' => $comando,
                'arquivos_entrada' => $arquivosValidos,
                'quantidade_arquivos' => count($arquivosValidos)
            ]);

            $output = [];
            $returnCode = 0;
            exec($comando . ' 2>&1', $output, $returnCode);

            $outputExiste = file_exists($outputPath);
            $outputTamanho = $outputExiste ? filesize($outputPath) : 0;

            if ($returnCode === 0 && $outputExiste && $outputTamanho > 0) {
                Log::info('PDFs mesclados com sucesso', [
                    'arquivo_saida' => $outputPath,
                    'tamanho' => $outputTamanho
                ]);
                return true;
            } else {
                Log::error('Erro ao mesclar PDFs', [
                    'return_code' => $returnCode,
                    'output' => implode("\n", $output)
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
    /**
     * Gera o Edital da modalidade do processo e o embute no PDF da Minuta,
     * cercado por páginas separadoras "INÍCIO DO EDITAL" e "FIM DO EDITAL".
     *
     * Substitui o antigo upload manual de `anexar_minuta`.
     */
    private function gerarEJuntarEditalNaMinuta(Processo $processo, string $caminhoPrincipal): void
    {
        $arquivosTemp = [];

        try {
            Log::info('Gerando Edital embutido para a Minuta', [
                'processo_id' => $processo->id,
            ]);

            if (!file_exists($caminhoPrincipal) || filesize($caminhoPrincipal) === 0) {
                Log::error('PDF base da Minuta não encontrado/vazio antes de embutir Edital', [
                    'caminho' => $caminhoPrincipal,
                ]);
                return;
            }

            // Resolve a view do Edital correspondente à modalidade do processo
            try {
                $viewEdital = $this->determinarViewPdf($processo, 'edital');
            } catch (\Throwable $e) {
                Log::warning('View de Edital não encontrada para esta modalidade — Minuta seguirá sem embutimento.', [
                    'processo_id' => $processo->id,
                    'erro' => $e->getMessage(),
                ]);
                return;
            }

            $dados = $this->prepararDadosPdf($processo, [
                'dataSelecionada' => now()->format('Y-m-d'),
                'assinantes' => [],
                'parecerSelecionado' => null,
            ]);

            // Flag consumida pelos templates do Edital para mostrar "XXXX..." nos
            // campos que NÃO devem aparecer preenchidos quando o Edital é embutido
            // dentro da Minuta (data limite, data fase, pregoeiro). Esses campos
            // só fazem sentido quando o Edital "real" é gerado lá na frente.
            $dados['embutidoMinuta'] = true;

            // 1) INÍCIO DO EDITAL
            $pdfInicio = Pdf::loadView('Admin.Processos.pdf-separadores.inicio-edital', $dados)
                ->setPaper('a4', 'portrait');
            $arquivoInicio = storage_path('app/temp_inicio_edital_' . $processo->id . '_' . uniqid() . '.pdf');
            $pdfInicio->save($arquivoInicio);
            $arquivosTemp[] = $arquivoInicio;

            // 2) EDITAL completo
            $pdfEdital = Pdf::loadView($viewEdital, $dados)->setPaper('a4', 'portrait');
            $arquivoEdital = storage_path('app/temp_edital_embutido_' . $processo->id . '_' . uniqid() . '.pdf');
            $pdfEdital->save($arquivoEdital);
            $arquivosTemp[] = $arquivoEdital;

            // 3) FIM DO EDITAL
            $pdfFim = Pdf::loadView('Admin.Processos.pdf-separadores.fim-edital', $dados)
                ->setPaper('a4', 'portrait');
            $arquivoFim = storage_path('app/temp_fim_edital_' . $processo->id . '_' . uniqid() . '.pdf');
            $pdfFim->save($arquivoFim);
            $arquivosTemp[] = $arquivoFim;

            // Validação básica dos 3 arquivos
            foreach ([$arquivoInicio, $arquivoEdital, $arquivoFim] as $f) {
                if (!file_exists($f) || filesize($f) === 0) {
                    Log::error('Arquivo temporário do Edital embutido vazio/ausente', ['arquivo' => $f]);
                    return;
                }
            }

            $sucesso = $this->juntarPdfsComGhostscript($caminhoPrincipal, [
                $arquivoInicio,
                $arquivoEdital,
                $arquivoFim,
            ]);

            if ($sucesso) {
                Log::info('Edital embutido na Minuta com sucesso', [
                    'caminho_final' => $caminhoPrincipal,
                    'tamanho_final' => filesize($caminhoPrincipal),
                ]);
            } else {
                Log::error('Falha ao juntar Edital embutido na Minuta', [
                    'minuta' => $caminhoPrincipal,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Erro ao gerar Edital embutido para a Minuta', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        } finally {
            foreach ($arquivosTemp as $f) {
                if (file_exists($f)) {
                    @unlink($f);
                }
            }
        }
    }

    private function mesclarECarimbarEmLote(array $arquivos, string $caminhoSaida, Processo $processo, string $fase = 'iniciar'): ?string
    {
        $chunksTemp = [];
        $paginaAtual = 1;

        try {
            Log::info("Iniciando mesclarECarimbarEmLote - {$fase}", [
                'processo_id' => $processo->id,
                'total_arquivos' => count($arquivos)
            ]);

            $pageCountTotal = 0;
            foreach ($arquivos as $arquivo) {
                if (file_exists($arquivo) && filesize($arquivo) > 0) {
                    $pageCountTotal += $this->contarPaginasPdf($arquivo);
                }
            }

            Log::info("Total de páginas a carimbar calculado: {$pageCountTotal}");

            if ($pageCountTotal === 0) {
                return null;
            }

            $chunkSize = 50; // Processar em blocos para equilibrar memória e performance

            foreach ($arquivos as $arquivo) {
                if (!file_exists($arquivo) || filesize($arquivo) == 0) continue;

                $pageCount = $this->contarPaginasPdf($arquivo);
                if ($pageCount === 0) continue;

                Log::info("Carimbando arquivo", ['arquivo' => basename($arquivo), 'paginas' => $pageCount]);

                for ($i = 1; $i <= $pageCount; $i += $chunkSize) {
                    $pdf = new Fpdi();
                    $this->configurarFonte($pdf);
                    $pdf->setSourceFile($arquivo);

                    $fimChunk = min($i + $chunkSize - 1, $pageCount);

                    for ($pagina = $i; $pagina <= $fimChunk; $pagina++) {
                        $tplId = $pdf->importPage($pagina);
                        $size = $pdf->getTemplateSize($tplId);
                        $orientation = $size['width'] > $size['height'] ? 'L' : 'P';
                        $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                        $pdf->useTemplate($tplId);

                        if ($paginaAtual !== 1) {
                            $this->adicionarCarimbo($pdf, $processo, $paginaAtual, $pageCountTotal, 0);
                        }

                        $paginaAtual++;
                    }

                    $tempPath = sys_get_temp_dir() . "/chunk_{$fase}_" . uniqid() . '.pdf';
                    $pdf->Output($tempPath, 'F');
                    $chunksTemp[] = $tempPath;
                    unset($pdf);
                }
            }

            Log::info("Mesclando os chunks gerados via Ghostscript", ['total_chunks' => count($chunksTemp)]);
            $sucesso = $this->mesclarPdfsComGhostscript($chunksTemp, $caminhoSaida);

            if ($sucesso && file_exists($caminhoSaida) && filesize($caminhoSaida) > 0) {
                Log::info("PDF mesclado e carimbado gerado com sucesso", ['caminho_saida' => $caminhoSaida]);
                return $caminhoSaida;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Erro ao mesclar e carimbar PDFs em lote', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        } finally {
            foreach ($chunksTemp as $tempFile) {
                if (file_exists($tempFile)) {
                    unlink($tempFile);
                }
            }
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

        $paginaAbsoluta = $paginaInicial + $paginaAtual;
        $totalAbsoluto = $paginaInicial + $pageCountTotal;

        $codigoAutenticacao = $processo->prefeitura->id . now()->format('HisdmY');
        $textoCarimbo = "Processo numerado por: {$processo->responsavel_numeracao} " .
            "Cargo: {$processo->unidade_numeracao} " .
            "Portaria nº {$processo->portaria_numeracao} " .
            "Pág. {$paginaAbsoluta} - " .
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

    private function salvarTotalPaginas(Processo $processo, int $totalPaginas): void
    {
        try {
            $processo->contTotalPagePhase1 = $totalPaginas;
            $processo->save();

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

    private function construirItensTr(Processo $processo, array $itensDinamicos): array
    {
        $detalhe       = $processo->detalhe;
        $tipoRelatorio = $detalhe->tipo_relatorio_analise_mercado ?? 'tce';

        // Mapa principal: etp_item_id (int) => valor_unitario (float)
        $precoMapId = [];

        if ($tipoRelatorio === 'tce') {
            $painelPrecoTce = $detalhe->painel_preco_tce;
            // Legado: alguns registros ficaram com JSON codificado em dobro; decodifica de novo se ainda vier como string.
            $painelPrecoTce = is_string($painelPrecoTce) ? (json_decode($painelPrecoTce, true) ?? []) : ($painelPrecoTce ?? []);
            foreach ($painelPrecoTce as $p) {
                if (!empty($p['etp_item_id']) && ($p['media'] ?? '') !== '') {
                    $media = (float) str_replace(',', '.', str_replace('.', '', $p['media']));
                    if ($media > 0) {
                        $precoMapId[(int) $p['etp_item_id']] = $media;
                    }
                }
            }
        } elseif ($tipoRelatorio === 'fornecedor_local') {
            foreach ($detalhe->fornecedor_local_precos ?? [] as $p) {
                if (empty($p['etp_item_id'])) continue;
                $vals = array_filter(
                    [$p['f1_preco'] ?? null, $p['f2_preco'] ?? null, $p['f3_preco'] ?? null],
                    fn($v) => $v !== null && $v !== ''
                );
                $avg = count($vals) > 0 ? array_sum($vals) / count($vals) : 0;
                if ($avg > 0) {
                    $precoMapId[(int) $p['etp_item_id']] = $avg;
                }
            }
        } else {
            // pncp / cesta_preco — usa etp_item_id do PesquisaPrecoItem
            foreach ($processo->pesquisaPrecoItens->groupBy('etp_item_id') as $etpItemId => $grupo) {
                if ($etpItemId) {
                    $precoMapId[(int) $etpItemId] = $grupo->avg('valor_unitario');
                }
            }
        }

        $etp    = $processo->etp;
        $result = [];
        $fmt    = fn($v) => $v > 0 ? 'R$ ' . number_format($v, 2, ',', '.') : '';

        $processarItem = function ($item, $loteNome = null) use ($precoMapId, $fmt, &$result) {
            $valorUnitario = $precoMapId[$item->id] ?? 0;
            $quantidade    = (float) ($item->pivot->quantidade ?? 0);
            $valorTotal    = $valorUnitario > 0 ? $valorUnitario * $quantidade : 0;

            $result[] = [
                'lote'           => $loteNome,
                'item'           => $item->descricao_item,
                'especificacoes' => $item->descricao_item,
                'unidade'        => $item->pivot->unidade ?? '',
                'quantidade'     => $item->pivot->quantidade ?? '',
                'valor_unitario' => $fmt($valorUnitario),
                'valor_total'    => $fmt($valorTotal),
            ];
        };

        if ($etp->tipo_contratacao === 'lote') {
            foreach ($etp->lotes as $lote) {
                foreach ($lote->itens as $item) {
                    $processarItem($item, $lote->nome);
                }
            }
        } else {
            foreach ($etp->itens as $item) {
                $processarItem($item);
            }
        }

        return $result;
    }

    private function formatarNomeArquivo(string $nome): string
    {
        $nome = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $nome));
        return str_replace(' ', '_', $nome);
    }
}
