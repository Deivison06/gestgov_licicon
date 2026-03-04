<?php

namespace App\Services;

use App\Models\Processo;
use App\Models\Documento;
use Barryvdh\DomPDF\Facade\Pdf;
use setasign\Fpdi\Tcpdf\Fpdi;
use App\Services\FinalizacaoDocumentoService;
use Illuminate\Support\Facades\Log;

class FinalizacaoPdfService
{
    protected FinalizacaoDocumentoService $documentoService;

    public function __construct(FinalizacaoDocumentoService $documentoService)
    {
        $this->documentoService = $documentoService;
    }

    public function gerarPdf(Processo $processo, array $requestData): array
    {
        set_time_limit(300);
        
        Log::info('Iniciando geração de PDF - Finalização', [
            'processo_id' => $processo->id,
            'documento' => $requestData['documento'] ?? null,
        ]);

        $validatedData = $this->validarRequisicaoPdf($requestData, $processo);
        $data = $this->prepararDadosPdf($processo, $validatedData);
        $view = $this->determinarViewPdf($processo, $validatedData['documento']);

        Log::info('View selecionada para PDF', ['view' => $view]);

        $pdf = Pdf::loadView($view, $data)->setPaper('a4', 'portrait');
        $caminhoCompleto = $this->salvarDocumento($processo, $pdf, $validatedData);

        $this->processarAnexos($processo, $validatedData['documento'], $caminhoCompleto);

        Log::info('PDF gerado com sucesso - Finalização', [
            'processo_id' => $processo->id,
            'documento' => $validatedData['documento'],
            'caminho' => $caminhoCompleto
        ]);

        return [
            'success' => true,
            'caminho' => $caminhoCompleto,
            'documento' => $validatedData['documento']
        ];
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
        $ordem = $this->documentoService->getOrdemDocumentos($processo);
        $documentos = Documento::where('processo_id', $processo->id)->get()->keyBy('tipo_documento');

        return $this->baixarTodosDocumentosComGhostscript($processo, $ordem, $documentos);
    }

    private function baixarTodosDocumentosComGhostscript(Processo $processo, array $ordem, $documentos)
    {
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

        $nomeArquivo = "processo_finalizacao_" . str_replace(['/', '\\'], '_', $processo->numero_processo) . "_todos_documentos_" . now()->format('Ymd_His') . '.pdf';
        $caminhoArquivo = public_path('uploads/documentos_finalizacao/' . $nomeArquivo);

        $sucesso = $this->mesclarPdfsComGhostscript($arquivos, $caminhoArquivo);

        if ($sucesso) {
            $caminhoCarimbado = $this->adicionarCarimboAoPdfComGhostscript($caminhoArquivo, $processo);

            if ($caminhoCarimbado) {
                return response()->download($caminhoCarimbado)->deleteFileAfterSend(true);
            } else {
                Log::warning('PDF mesclado com Ghostscript sem carimbo - Finalização', ['processo_id' => $processo->id]);
                return response()->download($caminhoArquivo)->deleteFileAfterSend(true);
            }
        } else {
            throw new \Exception('Erro ao mesclar documentos com Ghostscript');
        }
    }

    private function validarRequisicaoPdf(array $requestData, Processo $processo): array
    {
        $documento = $requestData['documento'] ?? 'atos_sessao';
        $dataSelecionada = $requestData['data'] ?? now()->format('Y-m-d');
        $parecerSelecionado = $requestData['parecer'] ?? null;

        $assinantes = $this->processarAssinantes($requestData);

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
            Log::warning("Erro ao decodificar JSON de assinantes - Finalização: " . json_last_error_msg());
            return [];
        }

        return $assinantes;
    }

    private function prepararDadosPdf(Processo $processo, array $validatedData): array
    {
        $processo->load(['finalizacao', 'prefeitura', 'vencedores.lotes']);

        $hasSelectedAssinantes = !empty($validatedData['assinantes']);

        return [
            'processo' => $processo,
            'prefeitura' => $processo->prefeitura,
            'finalizacao' => $processo->finalizacao,
            'vencedores' => $processo->vencedores,
            'dataGeracao' => now()->format('d/m/Y H:i:s'),
            'dataSelecionada' => $validatedData['dataSelecionada'],
            'assinantes' => $validatedData['assinantes'],
            'hasSelectedAssinantes' => $hasSelectedAssinantes,
            'parecer' => $validatedData['parecerSelecionado'],
        ];
    }

    private function determinarViewPdf(Processo $processo, string $documento): string
    {
        $viewBase = "Admin.Processos.pdf-finalizacao";
        $modalidade = $this->formatarNomeArquivo($processo->modalidade?->name ?? '');

        if ($processo->modalidade === \App\Enums\ModalidadeEnum::DISPENSA) {
            $tipoProcedimento = $processo->tipo_procedimento ?? null;
            if ($tipoProcedimento == \App\Enums\TipoProcedimentoEnum::OBRA) {
                $view = "{$viewBase}.{$modalidade}.obra.{$documento}";
            } else {
                $view = "{$viewBase}.{$modalidade}.{$documento}";
            }
        } else {
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

        $diretorio = public_path("uploads/documentos_finalizacao/{$subpasta}");
        if (!file_exists($diretorio)) {
            mkdir($diretorio, 0777, true);
        }

        $nomeArquivo = "processo_finalizacao_{$numeroProcessoLimpo}_{$validatedData['documento']}_" . now()->format('Ymd_His') . '.pdf';
        $caminhoRelativo = "uploads/documentos_finalizacao/{$subpasta}/{$nomeArquivo}";
        $caminhoCompleto = "{$diretorio}/{$nomeArquivo}";

        $pdf->save($caminhoCompleto);
        $this->atualizarRegistroDocumento($processo, $validatedData['documento'], $validatedData['dataSelecionada'], $caminhoRelativo);

        return $caminhoCompleto;
    }

    private function gerarSubpasta(Processo $processo, string $documento): string
    {
        if ($processo->modalidade === \App\Enums\ModalidadeEnum::DISPENSA) {
            $tipoProcedimento = $processo->tipo_procedimento ?? null;
            if ($tipoProcedimento == \App\Enums\TipoProcedimentoEnum::OBRA->value) {
                return "finalizacao/dispensa_obra/{$documento}";
            } else {
                return "finalizacao/dispensa_compras_servicos/{$documento}";
            }
        }

        return "finalizacao/{$documento}";
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
        Log::info("🔍 INICIANDO PROCESSAMENTO DE ANEXOS - Finalização: {$documento}", [
            'caminho_principal' => $caminhoPrincipal,
            'tamanho_inicial' => file_exists($caminhoPrincipal) ? filesize($caminhoPrincipal) : 0
        ]);

        $anexos = $this->obterAnexos($processo, $documento);

        if (!empty($anexos)) {
            Log::info("📎 Anexos encontrados para documento: {$documento}", [
                'quantidade' => count($anexos),
                'anexos' => $anexos
            ]);

            if ($documento === 'atos_sessao') {
                $resultado = $this->juntarPdfsComCapa($caminhoPrincipal, $anexos);
            } else {
                $resultado = $this->juntarPdfsComGhostscript($caminhoPrincipal, $anexos);
            }

            if ($resultado && file_exists($resultado)) {
                Log::info("✅ Anexos processados com SUCESSO - Finalização", [
                    'documento' => $documento,
                    'arquivo_final' => $resultado,
                    'tamanho_final' => filesize($resultado),
                    'anexos_mesclados' => count($anexos),
                    'metodo_usado' => $documento === 'atos_sessao' ? 'com capa' : 'padrão'
                ]);
            } else {
                Log::error("❌ Falha ao processar anexos - Finalização", [
                    'documento' => $documento,
                    'pdf_base' => $caminhoPrincipal,
                    'anexos' => $anexos
                ]);
            }
        } else {
            Log::info("ℹ️ Nenhum anexo encontrado para o documento: {$documento}");
        }

        Log::info("🏁 PROCESSAMENTO DE ANEXOS CONCLUÍDO - Finalização: {$documento}");
    }

    private function juntarPdfsComCapa(string $pdfBasePath, array $anexoPaths): ?string
    {
        try {
            set_time_limit(180);
            
            Log::info("INICIANDO JUNÇÃO DE PDFs COM CAPA ESPECIAL - Finalização", [
                'pdf_base' => $pdfBasePath,
                'anexos' => $anexoPaths
            ]);

            if (empty($anexoPaths)) {
                return $pdfBasePath;
            }

            if (!file_exists($pdfBasePath)) {
                Log::error('Arquivo base não encontrado - Finalização', ['caminho' => $pdfBasePath]);
                return null;
            }

            $anexosValidos = [];
            foreach ($anexoPaths as $index => $anexoPath) {
                if (file_exists($anexoPath) && filesize($anexoPath) > 0) {
                    $anexosValidos[] = $anexoPath;
                }
            }

            if (empty($anexosValidos)) {
                return $pdfBasePath;
            }

            $tempOutput = tempnam(sys_get_temp_dir(), 'capa_special_') . '.pdf';
            $primeiraPaginaTemp = tempnam(sys_get_temp_dir(), 'primeira_pagina_capa_') . '.pdf';
            $restoPaginasTemp = null;

            $this->extrairPaginasPdf($pdfBasePath, $primeiraPaginaTemp, 1, 1);

            $pageCount = $this->contarPaginasPdf($pdfBasePath);

            if ($pageCount > 1) {
                $restoPaginasTemp = tempnam(sys_get_temp_dir(), 'resto_paginas_capa_') . '.pdf';
                $this->extrairPaginasPdf($pdfBasePath, $restoPaginasTemp, 2, $pageCount);

                $todosArquivos = array_merge([$primeiraPaginaTemp], $anexosValidos, [$restoPaginasTemp]);
            } else {
                $todosArquivos = array_merge([$primeiraPaginaTemp], $anexosValidos);
            }

            Log::info("Estrutura de mesclagem com capa", [
                'total_arquivos' => count($todosArquivos),
                'arquivos' => $todosArquivos,
                'estrutura' => 'Capa (página 1) → Anexos → Resto do conteúdo',
                'page_count' => $pageCount
            ]);

            $sucesso = $this->mesclarPdfsComGhostscript($todosArquivos, $tempOutput);

            if ($sucesso && file_exists($tempOutput) && filesize($tempOutput) > 0) {
                copy($tempOutput, $pdfBasePath);
                unlink($tempOutput);

                if (file_exists($primeiraPaginaTemp)) unlink($primeiraPaginaTemp);
                if ($restoPaginasTemp && file_exists($restoPaginasTemp)) unlink($restoPaginasTemp);

                Log::info("PDFs mesclados com estrutura de capa - SUCESSO", [
                    'arquivo_final' => $pdfBasePath,
                    'tamanho_final' => filesize($pdfBasePath),
                    'anexos_mesclados' => count($anexosValidos),
                    'estrutura' => 'Capa (página 1) + Anexos + Resto do conteúdo'
                ]);

                return $pdfBasePath;
            }

            if (file_exists($tempOutput)) unlink($tempOutput);
            if (file_exists($primeiraPaginaTemp)) unlink($primeiraPaginaTemp);
            if ($restoPaginasTemp && file_exists($restoPaginasTemp)) unlink($restoPaginasTemp);

            return null;
        } catch (\Exception $e) {
            Log::error('Erro ao mesclar PDFs com capa', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    private function obterAnexos(Processo $processo, string $documento): array
    {
        $anexos = [];
        $mapeamentoAnexos = $this->documentoService->getMapeamentoAnexos();
        $campoAnexo = $mapeamentoAnexos[$documento] ?? null;

        if (!$campoAnexo) {
            Log::info("Nenhum mapeamento de anexo encontrado para documento: {$documento}");
            return $anexos;
        }

        if (!empty($processo->finalizacao->$campoAnexo)) {
            $caminhoRelativo = $processo->finalizacao->$campoAnexo;
            $caminho = public_path($caminhoRelativo);

            if (file_exists($caminho)) {
                $anexos[] = $caminho;
                Log::info("Anexo encontrado para finalização $documento", [
                    'campo' => $campoAnexo,
                    'caminho_relativo' => $caminhoRelativo,
                    'caminho_absoluto' => $caminho,
                    'existe' => file_exists($caminho),
                    'tamanho' => filesize($caminho)
                ]);
            } else {
                Log::warning("Anexo não encontrado no sistema de arquivos", [
                    'campo' => $campoAnexo,
                    'caminho_relativo' => $caminhoRelativo,
                    'caminho_absoluto' => $caminho
                ]);
            }
        } else {
            Log::info("Campo de anexo vazio para documento: {$documento}", [
                'campo' => $campoAnexo,
                'finalizacao_existe' => !is_null($processo->finalizacao)
            ]);
        }

        return $anexos;
    }

    private function juntarPdfsComGhostscript(string $pdfBasePath, array $anexoPaths): ?string
    {
        try {
            Log::info("INICIANDO JUNÇÃO DE PDFs COM PÁGINA DE CAPA - Finalização", [
                'pdf_base' => $pdfBasePath,
                'anexos_recebidos' => $anexoPaths,
                'base_existe' => file_exists($pdfBasePath),
                'base_tamanho' => file_exists($pdfBasePath) ? filesize($pdfBasePath) : 0
            ]);

            if (!file_exists($pdfBasePath)) {
                Log::error('❌ ARQUIVO BASE NÃO ENCONTRADO - Finalização', ['caminho' => $pdfBasePath]);
                return null;
            }

            $tamanhoBase = filesize($pdfBasePath);
            if ($tamanhoBase === 0 || $tamanhoBase === false) {
                Log::error('❌ ARQUIVO BASE VAZIO OU INVÁLIDO - Finalização', [
                    'caminho' => $pdfBasePath,
                    'tamanho' => $tamanhoBase
                ]);
                return null;
            }

            $anexosValidos = [];
            foreach ($anexoPaths as $index => $anexoPath) {
                if (file_exists($anexoPath) && filesize($anexoPath) > 0) {
                    $anexosValidos[] = $anexoPath;
                    Log::info("✅ Anexo válido confirmado - Finalização", [
                        'indice' => $index,
                        'anexo' => $anexoPath,
                        'tamanho' => filesize($anexoPath)
                    ]);
                } else {
                    Log::warning('⚠️ Anexo ignorado (não existe ou está vazio) - Finalização', [
                        'indice' => $index,
                        'anexo' => $anexoPath,
                        'existe' => file_exists($anexoPath),
                        'tamanho' => file_exists($anexoPath) ? filesize($anexoPath) : 0
                    ]);
                }
            }

            if (empty($anexosValidos)) {
                Log::info("ℹ️ Nenhum anexo válido para mesclar - retornando arquivo base original", [
                    'pdf_base' => $pdfBasePath
                ]);
                return $pdfBasePath;
            }

            $tempOutput = tempnam(sys_get_temp_dir(), 'merged_pdf_finalizacao_') . '.pdf';
            $primeiraPaginaTemp = tempnam(sys_get_temp_dir(), 'primeira_pagina_') . '.pdf';
            $restoPaginasTemp = tempnam(sys_get_temp_dir(), 'resto_paginas_') . '.pdf()';

            $this->extrairPaginasPdf($pdfBasePath, $primeiraPaginaTemp, 1, 1);

            $pageCount = $this->contarPaginasPdf($pdfBasePath);
            if ($pageCount > 1) {
                $this->extrairPaginasPdf($pdfBasePath, $restoPaginasTemp, 2, $pageCount);

                $todosArquivos = array_merge(
                    [$primeiraPaginaTemp], 
                    $anexosValidos, 
                    [$restoPaginasTemp]
                );
            } else {
                $todosArquivos = array_merge([$primeiraPaginaTemp], $anexosValidos);
            }

            Log::info("🔄 Iniciando mesclagem com estrutura de capa - Finalização", [
                'total_arquivos' => count($todosArquivos),
                'arquivos' => $todosArquivos,
                'arquivo_saida_temp' => $tempOutput,
                'pagina_capa' => $primeiraPaginaTemp,
                'resto_paginas' => $restoPaginasTemp ?? 'N/A'
            ]);

            $sucesso = $this->mesclarPdfsComGhostscript($todosArquivos, $tempOutput);

            if ($sucesso && file_exists($tempOutput) && filesize($tempOutput) > 0) {
                $tamanhoTemp = filesize($tempOutput);

                Log::info("✅ Arquivo temporário gerado com sucesso - Finalização", [
                    'caminho_temp' => $tempOutput,
                    'tamanho_temp' => $tamanhoTemp,
                    'estrutura' => 'Capa → Anexos → Resto do conteúdo'
                ]);

                if (copy($tempOutput, $pdfBasePath)) {
                    $tamanhoFinal = filesize($pdfBasePath);
                    Log::info("🎉 PDFs mesclados com SUCESSO - Finalização", [
                        'arquivo_final' => $pdfBasePath,
                        'tamanho_final' => $tamanhoFinal,
                        'anexos_mesclados' => count($anexosValidos),
                        'estrutura_final' => 'Capa (página 1) + Anexos + Resto do conteúdo'
                    ]);

                    unlink($tempOutput);
                    if (file_exists($primeiraPaginaTemp)) unlink($primeiraPaginaTemp);
                    if (file_exists($restoPaginasTemp)) unlink($restoPaginasTemp);

                    return $pdfBasePath;
                } else {
                    Log::error('❌ Falha ao copiar arquivo temporário para destino - Finalização');
                }
            } else {
                Log::error('❌ Falha na mesclagem com Ghostscript - Finalização', [
                    'sucesso' => $sucesso,
                    'temp_output_existe' => file_exists($tempOutput),
                    'temp_output_tamanho' => file_exists($tempOutput) ? filesize($tempOutput) : 0
                ]);
            }

            if (file_exists($tempOutput)) unlink($tempOutput);
            if (file_exists($primeiraPaginaTemp)) unlink($primeiraPaginaTemp);
            if (file_exists($restoPaginasTemp)) unlink($restoPaginasTemp);

            return null;
        } catch (\Exception $e) {
            Log::error('💥 EXCEÇÃO ao mesclar PDFs com Ghostscript - Finalização', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'pdf_base' => $pdfBasePath,
                'anexos' => $anexoPaths
            ]);
            return null;
        }
    }

    private function extrairPaginasPdf(string $inputPath, string $outputPath, int $startPage, int $endPage): bool
    {
        try {
            $comando = sprintf(
                'gs -dBATCH -dNOPAUSE -q -sDEVICE=pdfwrite -dPDFSETTINGS=/prepress -dFirstPage=%d -dLastPage=%d -sOutputFile="%s" "%s"',
                $startPage,
                $endPage,
                $outputPath,
                $inputPath
            );

            $output = [];
            $returnCode = 0;
            exec($comando . ' 2>&1', $output, $returnCode);

            if ($returnCode === 0 && file_exists($outputPath) && filesize($outputPath) > 0) {
                Log::info('Páginas extraídas com sucesso', [
                    'arquivo_entrada' => $inputPath,
                    'arquivo_saida' => $outputPath,
                    'pagina_inicio' => $startPage,
                    'pagina_fim' => $endPage,
                    'tamanho_saida' => filesize($outputPath)
                ]);
                return true;
            } else {
                Log::error('Erro ao extrair páginas', [
                    'return_code' => $returnCode,
                    'saida' => implode("\n", $output)
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Exceção ao extrair páginas', ['erro' => $e->getMessage()]);
            return false;
        }
    }

    private function mesclarPdfsComGhostscript(array $arquivos, string $outputPath): bool
    {
        $listaArquivos = null;

        try {
            $arquivosValidos = [];
            foreach ($arquivos as $index => $arquivo) {
                if (!file_exists($arquivo)) {
                    Log::error('Arquivo não encontrado para mesclagem - Finalização', ['arquivo' => $arquivo]);
                    return false;
                }

                $tamanho = filesize($arquivo);
                if ($tamanho === 0) {
                    Log::error('Arquivo vazio encontrado - Finalização', ['arquivo' => $arquivo]);
                    return false;
                }

                $arquivosValidos[] = $arquivo;
            }

            $listaArquivos = tempnam(sys_get_temp_dir(), 'gs_list_finalizacao_');
            file_put_contents($listaArquivos, implode("\n", $arquivosValidos));

            $comando = sprintf(
                'gs -dBATCH -dNOPAUSE -q -sDEVICE=pdfwrite -dPDFSETTINGS=/prepress -sOutputFile="%s" @"%s"',
                $outputPath,
                $listaArquivos
            );

            Log::info('Executando Ghostscript - Finalização', [
                'comando' => $comando,
                'quantidade_arquivos' => count($arquivosValidos)
            ]);

            $output = [];
            $returnCode = 0;
            exec($comando . ' 2>&1', $output, $returnCode);

            sleep(2);

            $outputExiste = file_exists($outputPath);
            $outputTamanho = $outputExiste ? filesize($outputPath) : 0;

            if ($returnCode === 0 && $outputExiste && $outputTamanho > 0) {
                Log::info('PDFs mesclados com sucesso usando Ghostscript - Finalização', [
                    'arquivo_saida' => $outputPath,
                    'tamanho' => $outputTamanho
                ]);
                return true;
            } else {
                Log::error('Erro ao mesclar PDFs com Ghostscript - Finalização', [
                    'return_code' => $returnCode,
                    'arquivo_saida_existe' => $outputExiste,
                    'arquivo_saida_tamanho' => $outputTamanho
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Exceção ao mesclar PDFs com Ghostscript - Finalização', [
                'erro' => $e->getMessage()
            ]);
            return false;
        } finally {
            if ($listaArquivos && file_exists($listaArquivos)) {
                unlink($listaArquivos);
            }
        }
    }

    private function adicionarCarimboAoPdfComGhostscript(string $caminhoPdf, Processo $processo): ?string
    {
        $chunksTemp = [];

        try {
            $pageCount = $this->contarPaginasPdf($caminhoPdf);

            if ($pageCount === 0) {
                Log::error('PDF vazio ou inválido - Finalização', ['caminho' => $caminhoPdf]);
                return null;
            }

            $caminhoCarimbado = str_replace('.pdf', '_carimbado.pdf', $caminhoPdf);
            $chunkSize = 50; // Processar em blocos de 50 páginas para equilibrar memória e performance

            for ($i = 1; $i <= $pageCount; $i += $chunkSize) {
                $pdf = new Fpdi();
                $this->configurarFonte($pdf);
                $pdf->setSourceFile($caminhoPdf);

                $fimChunk = min($i + $chunkSize - 1, $pageCount);
                
                Log::info("Processando chunk de carimbo: {$i} até {$fimChunk}", [
                    'processo_id' => $processo->id,
                    'total_paginas' => $pageCount
                ]);

                for ($pagina = $i; $pagina <= $fimChunk; $pagina++) {
                    $tplId = $pdf->importPage($pagina);
                    $pdf->AddPage();
                    $pdf->useTemplate($tplId);

                    // Aplica o carimbo em todas as páginas
                    $this->adicionarCarimbo($pdf, $processo, $pagina, $pageCount);
                }

                $tempPath = sys_get_temp_dir() . "/chunk_finalizacao_{$i}_" . uniqid() . '.pdf';
                $pdf->Output($tempPath, 'F');
                $chunksTemp[] = $tempPath;
                
                // Força limpeza de memória
                unset($pdf);
            }

            $sucesso = $this->mesclarPdfsComGhostscript($chunksTemp, $caminhoCarimbado);

            if ($sucesso && file_exists($caminhoCarimbado) && filesize($caminhoCarimbado) > 0) {
                $this->atualizarContadorContrato($processo, $pageCount);

                if (file_exists($caminhoPdf)) {
                    unlink($caminhoPdf);
                }
                rename($caminhoCarimbado, $caminhoPdf);
                return $caminhoPdf;
            } else {
                Log::error('Falha ao mesclar chunks carimbados - Finalização');
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Erro ao adicionar carimbo ao PDF com Ghostscript - Finalização', [
                'caminho' => $caminhoPdf,
                'erro' => $e->getMessage()
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

    private function atualizarContadorContrato(Processo $processo, int $paginasFinalizacao): void
    {
        try {
            $processo->contTotalPagePhase2 = $paginasFinalizacao;
            $processo->save();

            Log::info('Contador atualizado para contrato', [
                'processo_id' => $processo->id,
                'paginas_inicializacao' => $processo->contTotalPage ?? 0,
                'paginas_finalizacao' => $paginasFinalizacao,
                'total_para_contrato' => $totalPaginas
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar contador para contrato', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage()
            ]);
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

    private function adicionarCarimbo(Fpdi $pdf, Processo $processo, int $paginaAtual, int $pageCountTotal): void
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

        $paginaInicial = $processo->contTotalPagePhase1 ?? 0;

        $paginaAbsoluta = $paginaInicial + $paginaAtual;
        $totalAbsoluto = $paginaInicial + $pageCountTotal;

        $codigoAutenticacao = $processo->prefeitura->id . now()->format('HisdmY');
        $textoCarimbo = "Processo numerado por: {$processo->responsavel_numeracao} " .
            "Cargo: {$processo->unidade_numeracao} " .
            "Portaria nº {$processo->portaria_numeracao} " .
            "Pág. {$paginaAbsoluta} de {$totalAbsoluto} - " .
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
            Log::error('Erro ao contar páginas do PDF - Finalização', [
                'caminho' => $caminhoPdf,
                'erro' => $e->getMessage()
            ]);
            return 0;
        }
    }

    private function formatarNomeArquivo(string $nome): string
    {
        $nome = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $nome));
        return str_replace(' ', '_', $nome);
    }
}