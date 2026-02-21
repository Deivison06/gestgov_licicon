<?php

namespace App\Services;

use App\Models\Processo;
use App\Models\Documento;
use Barryvdh\DomPDF\Facade\Pdf;
use setasign\Fpdi\Tcpdf\Fpdi;
use App\Services\ProcessoDocumentoService;
use Illuminate\Support\Facades\Log;

class ProcessoPdfService
{
    protected ProcessoDocumentoService $documentoService;

    public function __construct(ProcessoDocumentoService $documentoService)
    {
        $this->documentoService = $documentoService;
    }

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

        return [
            'success' => true,
            'caminho' => $caminhoCompleto,
            'documento' => $documentoSolicitado
        ];
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

    public function baixarTodosDocumentos(Processo $processo)
    {
        $ordem = $this->documentoService->getOrdemDocumentosParaDownload($processo);
        $documentos = Documento::where('processo_id', $processo->id)->get()->keyBy('tipo_documento');

        return $this->baixarTodosDocumentosComGhostscript($processo, $ordem, $documentos);
    }

    private function baixarTodosDocumentosComGhostscript(Processo $processo, array $ordem, $documentos)
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

        $sucesso = $this->mesclarPdfsComGhostscript($arquivos, $caminhoArquivo);

        if (!$sucesso) {
            throw new \Exception('Erro ao mesclar documentos com Ghostscript');
        }

        $totalPaginas = $this->contarPaginasPdf($caminhoArquivo);
        $this->salvarTotalPaginas($processo, $totalPaginas);

        $caminhoCarimbado = $this->adicionarCarimboAoPdfComGhostscript($caminhoArquivo, $processo);

        return response()
            ->download($caminhoCarimbado ?? $caminhoArquivo)
            ->deleteFileAfterSend(true);
    }


    // Métodos privados mantidos do original, mas organizados...
    private function validarRequisicaoPdf(array $requestData, Processo $processo): array
    {
        $documento = $requestData['documento'] ?? 'capa';
        $dataSelecionada = $requestData['data'] ?? null;
        $parecerSelecionado = $requestData['parecer'] ?? null;

        if (empty($dataSelecionada)) {
            throw new \Exception('É necessário selecionar uma data antes de gerar o PDF.');
        }

        $assinantes = $this->processarAssinantes($requestData);
        $this->validarAssinantes($documento, $assinantes);

        return [
            'documento' => $documento,
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

        $this->atualizarRegistroDocumento($processo, $tipoPersistencia, $validatedData['dataSelecionada'], $caminhoRelativo);

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

            sleep(2);

            $outputExiste = file_exists($outputPath);
            $outputTamanho = $outputExiste ? filesize($outputPath) : 0;

            if ($returnCode === 0 && $outputExiste && $outputTamanho > 0) {
                Log::info('PDFs mesclados com sucesso usando Ghostscript', [
                    'arquivo_saida' => $outputPath,
                    'tamanho' => $outputTamanho,
                    'return_code' => $returnCode,
                    'output_ghostscript' => implode("\n", array_slice($output, 0, 10))
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

    private function adicionarCarimboAoPdfComGhostscript(string $caminhoPdf, Processo $processo): ?string
    {
        $paginasTemp = [];

        try {
            $pageCount = $this->contarPaginasPdf($caminhoPdf);

            if ($pageCount === 0) {
                Log::error('PDF vazio ou inválido', ['caminho' => $caminhoPdf]);
                return null;
            }

            $caminhoCarimbado = str_replace('.pdf', '_carimbado.pdf', $caminhoPdf);

            for ($pagina = 1; $pagina <= $pageCount; $pagina++) {
                $paginaAtual = $pagina;

                $pdf = new Fpdi();
                $this->configurarFonte($pdf);

                $pdf->setSourceFile($caminhoPdf);
                $tplId = $pdf->importPage($pagina);
                $pdf->AddPage();
                $pdf->useTemplate($tplId);

                if ($pagina !== 1) {
                    $this->adicionarCarimbo($pdf, $processo, $paginaAtual - 1, $pageCount - 1);
                }

                $tempPath = sys_get_temp_dir() . "/pagina_{$pagina}_" . uniqid() . '.pdf';
                $pdf->Output($tempPath, 'F');
                $paginasTemp[] = $tempPath;
            }

            $sucesso = $this->mesclarPdfsComGhostscript($paginasTemp, $caminhoCarimbado);

            if ($sucesso && file_exists($caminhoCarimbado) && filesize($caminhoCarimbado) > 0) {
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
            foreach ($paginasTemp as $tempFile) {
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

    private function salvarTotalPaginas(Processo $processo, int $totalPaginas): void
    {
        try {
            $processo->contTotalPage = $totalPaginas;
            $processo->save();

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

    private function formatarNomeArquivo(string $nome): string
    {
        $nome = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $nome));
        return str_replace(' ', '_', $nome);
    }
}
