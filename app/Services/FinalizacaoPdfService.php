<?php

namespace App\Services;

use App\Models\Processo;
use App\Models\Documento;
use App\Models\Vencedor;
use App\Models\AtaRegistroPreco;
use Barryvdh\DomPDF\Facade\Pdf;
use setasign\Fpdi\Tcpdf\Fpdi;
use App\Models\Homologacao;
use App\Services\Assinatura\DocumentoVersaoService;
use App\Services\Assinatura\PdfWatermarkService;
use App\Services\Assinatura\SolicitacaoService;
use App\Services\FinalizacaoDocumentoService;
use App\Services\HomologacaoService;
use Illuminate\Support\Facades\Log;

class FinalizacaoPdfService
{
    use \App\Services\Assinatura\ResolveLegacyAssinantesTrait;

    public function __construct(
        protected FinalizacaoDocumentoService $documentoService,
        protected HomologacaoService $homologacaoService,
        protected PdfWatermarkService $watermarkService,
        protected DocumentoVersaoService $versaoService,
        protected SolicitacaoService $solicitacaoService,
    ) {}

    public function gerarPdf(Processo $processo, array $requestData): array
    {
        set_time_limit(300);

        Log::info('Iniciando geração de PDF - Finalização', [
            'processo_id' => $processo->id,
            'documento' => $requestData['documento'] ?? null,
            'homologacao_id' => $requestData['homologacao_id'] ?? null,
            'vencedor_id' => $requestData['vencedor_id'] ?? null,
        ]);

        // Normaliza a chave dinâmica `ata_registro_precos_v{id}` em (documento, vencedor_id).
        [$documentoNormalizado, $vencedorIdInferido] = $this->normalizarDocumentoAta(
            $requestData['documento'] ?? null
        );
        if ($documentoNormalizado !== null) {
            $requestData['documento'] = $documentoNormalizado;
        }
        if ($vencedorIdInferido !== null && empty($requestData['vencedor_id'])) {
            $requestData['vencedor_id'] = $vencedorIdInferido;
        }

        $homologacao = $this->resolverHomologacao($processo, $requestData);
        $vencedor = $this->resolverVencedor($processo, $requestData);

        $validatedData = $this->validarRequisicaoPdf($requestData, $processo);
        $validatedData['homologacao'] = $homologacao;
        $validatedData['vencedor'] = $vencedor;
        $data = $this->prepararDadosPdf($processo, $validatedData);
        $view = $this->determinarViewPdf($processo, $validatedData['documento']);

        Log::info('View selecionada para PDF', ['view' => $view]);

        $pdf = Pdf::loadView($view, $data)->setPaper('a4', 'portrait');
        $caminhoCompleto = $this->salvarDocumento($processo, $pdf, $validatedData);

        $this->processarAnexos($processo, $validatedData['documento'], $caminhoCompleto, $homologacao);

        Log::info('PDF gerado com sucesso - Finalização', [
            'processo_id' => $processo->id,
            'documento' => $validatedData['documento'],
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
                    $homologacao,
                    $validatedData['documento'],
                    $caminhoCompleto,
                    $requestData['modo'] ?? 'paralelo',
                    (int) ($requestData['prazo_dias'] ?? 7),
                    $assinantes,
                    (int) ($requestData['solicitado_por_user_id'] ?? auth()->id() ?? 0)
                );
            } catch (\Throwable $e) {
                Log::error('Falha ao iniciar rodada de assinatura — Finalização', [
                    'processo_id' => $processo->id,
                    'documento'   => $validatedData['documento'],
                    'erro'        => $e->getMessage(),
                ]);
                // Não revertemos o PDF — ele já está salvo. A rodada pode ser
                // disparada manualmente depois.
            }
        }

        return array_filter([
            'success'    => true,
            'caminho'    => $caminhoCompleto,
            'documento'  => $validatedData['documento'],
            'assinatura' => $infoAssinatura,
        ], fn ($v) => $v !== null);
    }

    /**
     * Aplica marca d'água, cria DocumentoVersao + rodada de SolicitacaoAssinatura.
     *
     * O documentavel é o Documento (a row criada por salvarDocumento). Cada PDF
     * gerado pelo flow de finalização vira uma versão independente — mesmo se
     * regerado, mantém histórico completo.
     */
    private function iniciarRodadaAssinatura(
        Processo $processo,
        ?Homologacao $homologacao,
        string $tipoDocumento,
        string $caminhoPdfOriginal,
        string $modo,
        int $prazoDias,
        array $assinantes,
        int $solicitadoPorUserId
    ): array {
        // 1) Marca d'água
        $caminhoAbsoluto = $this->resolverCaminhoAbsoluto($caminhoPdfOriginal);
        $caminhoRascunho = $this->watermarkService->aplicarMarcaDagua(
            $caminhoAbsoluto,
            'AGUARDANDO ASSINATURAS'
        );

        // 2) Documentavel = a row Documento gravada por salvarDocumento
        $documento = Documento::query()
            ->where('processo_id', $processo->id)
            ->where('tipo_documento', $tipoDocumento)
            ->when($homologacao, fn ($q) => $q->where('homologacao_id', $homologacao->id))
            ->latest('id')
            ->firstOrFail();

        // 3) Versão
        $versao = $this->versaoService->criarRascunho(
            $documento,
            $caminhoRascunho,
            $solicitadoPorUserId
        );

        // 4) Normaliza assinantes
        $listaAssinantes = collect($assinantes)
            ->map(fn ($a, $idx) => [
                'user_id' => (int) ($a['id'] ?? $a['user_id'] ?? 0),
                'ordem'   => $modo === 'sequencial' ? (int) ($a['ordem'] ?? $idx + 1) : 0,
            ])
            ->filter(fn ($a) => $a['user_id'] > 0)
            ->values()
            ->all();

        // 5) Rodada
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

    /**
     * Resolve caminho absoluto. Aceita tanto path absoluto quanto relativo a public_path().
     */
    private function resolverCaminhoAbsoluto(string $caminho): string
    {
        if (is_file($caminho)) {
            return $caminho;
        }
        $candidato = public_path($caminho);
        return is_file($candidato) ? $candidato : $caminho;
    }

    /**
     * Quando a request vem com chave dinâmica `ata_registro_precos_v{id}`, retorna
     * ['ata_registro_precos', $vencedorId]. Caso contrário retorna [null, null].
     */
    private function normalizarDocumentoAta(?string $documento): array
    {
        if (!$documento || strpos($documento, 'ata_registro_precos_v') !== 0) {
            return [null, null];
        }

        $vencedorId = (int) substr($documento, strlen('ata_registro_precos_v'));
        if ($vencedorId <= 0) {
            return [null, null];
        }

        return ['ata_registro_precos', $vencedorId];
    }

    private function resolverVencedor(Processo $processo, array $requestData): ?Vencedor
    {
        $vencedorId = $requestData['vencedor_id'] ?? null;
        if (!$vencedorId) {
            return null;
        }

        $vencedor = Vencedor::where('processo_id', $processo->id)
            ->where('id', $vencedorId)
            ->first();

        if (!$vencedor) {
            throw new \DomainException('Vencedor não pertence a este processo.');
        }

        return $vencedor;
    }

    public function baixarDocumento(Processo $processo, string $tipo, ?int $homologacaoId = null, ?int $vencedorId = null)
    {
        // Chave dinâmica ata_registro_precos_v{id} — extrai vencedor_id
        if (strpos($tipo, 'ata_registro_precos_v') === 0) {
            $vencedorId = $vencedorId ?: (int) substr($tipo, strlen('ata_registro_precos_v'));
            $tipo = 'ata_registro_precos';
        }

        // Ata por vencedor: baixa direto da tabela própria.
        if ($tipo === 'ata_registro_precos' && $homologacaoId && $vencedorId) {
            $ata = AtaRegistroPreco::where('processo_id', $processo->id)
                ->where('homologacao_id', $homologacaoId)
                ->where('vencedor_id', $vencedorId)
                ->first();

            return $this->responderDownload($processo, $tipo, $ata?->caminho, $homologacaoId, $vencedorId);
        }

        // Alinha o lookup à lógica de gravação (resolverHomologacao): tipos que não são
        // "por-homologação" são SEMPRE persistidos a nível-processo (homologacao_id null),
        // mesmo quando exibidos dentro de um bloco de homologação (homologação única).
        // Sem isso, o filtro `where('homologacao_id', X)` não encontra o registro e o
        // download falha com uma página de erro.
        if (!$this->homologacaoService->ehTipoPorHomologacao($tipo)) {
            $homologacaoId = null;
        }

        $query = Documento::where('processo_id', $processo->id)
            ->where('tipo_documento', $tipo);

        if ($homologacaoId !== null) {
            $query->where('homologacao_id', $homologacaoId);
        } else {
            $query->whereNull('homologacao_id');
        }

        $documento = $query->first();

        return $this->responderDownload($processo, $tipo, $documento?->caminho, $homologacaoId, $vencedorId);
    }

    /**
     * Devolve a resposta de download validando a existência do registro e do arquivo
     * físico. Lança DomainException (com mensagem amigável) em vez de deixar o
     * response()->download estourar uma página de erro — que o navegador salvaria
     * como "Baixar.html".
     */
    private function responderDownload(Processo $processo, string $tipo, ?string $caminho, ?int $homologacaoId, ?int $vencedorId)
    {
        if (!$caminho) {
            throw new \DomainException('Este documento ainda não foi gerado. Clique em "Gerar PDF" antes de baixá-lo.');
        }

        $caminhoAbsoluto = public_path($caminho);
        if (!file_exists($caminhoAbsoluto)) {
            Log::warning('Arquivo do documento não encontrado no disco ao baixar - Finalização', [
                'processo_id' => $processo->id,
                'tipo' => $tipo,
                'homologacao_id' => $homologacaoId,
                'vencedor_id' => $vencedorId,
                'caminho' => $caminho,
            ]);

            throw new \DomainException('O arquivo deste documento não foi encontrado no servidor. Gere o PDF novamente para baixá-lo.');
        }

        return response()->download($caminhoAbsoluto);
        // Se houver versão assinada consolidada, serve o PDF assinado
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

    public function gerarCaminhoTodosDocumentos(Processo $processo): string
    {
        $ordem = $this->documentoService->getOrdemDocumentos($processo);
        $tiposPorHomologacao = $this->homologacaoService->tiposPorHomologacao();

        $documentos = Documento::where('processo_id', $processo->id)
            ->where(function ($query) use ($tiposPorHomologacao) {
                $query->whereNotIn('tipo_documento', $tiposPorHomologacao)
                      ->orWhereNotNull('homologacao_id');
            })
            ->get();

        return $this->baixarTodosDocumentosComGhostscript($processo, $ordem, $documentos);
    }

    public function baixarTodosDocumentos(Processo $processo)
    {
        $caminho = $this->gerarCaminhoTodosDocumentos($processo);
        return response()->download($caminho)->deleteFileAfterSend(true);
    }

    private function baixarTodosDocumentosComGhostscript(Processo $processo, array $ordem, $documentos): string
    {
        // Pré-carrega TODAS as Atas por vencedor (homologação × vencedor) já geradas.
        // Inclui ordenado por (homologacao_id, vencedor_id) pra dar ordem estável.
        $atasPorVencedor = AtaRegistroPreco::where('processo_id', $processo->id)
            ->whereNotNull('caminho')
            ->orderBy('homologacao_id')
            ->orderBy('vencedor_id')
            ->get();

        $documentosPorTipo = $documentos->groupBy('tipo_documento');

        $arquivos = [];
        foreach ($ordem as $tipo) {
            // Quando bate em `ata_registro_precos`, expande para todas as Atas por vencedor.
            if ($tipo === 'ata_registro_precos') {
                foreach ($atasPorVencedor as $ata) {
                    $caminho = public_path($ata->caminho);
                    if (file_exists($caminho)) {
                        $arquivos[] = $caminho;
                    }
                }
                // Mantém também a Ata antiga (legado em `documentos`) se ainda existir.
                if (isset($documentosPorTipo[$tipo])) {
                    foreach ($documentosPorTipo[$tipo] as $doc) {
                        $caminho = public_path($doc->caminho);
                        if (file_exists($caminho)) {
                            $arquivos[] = $caminho;
                        }
                    }
                }
                continue;
            }

            if (!isset($documentosPorTipo[$tipo])) continue;

            foreach ($documentosPorTipo[$tipo] as $doc) {
                $caminho = public_path($doc->caminho);
                if (!file_exists($caminho)) continue;
                $arquivos[] = $caminho;
            }
        }

        if (empty($arquivos)) {
            throw new \Exception('Nenhum documento encontrado para mesclar.');
        }

        $nomeArquivo = "processo_finalizacao_" . str_replace(['/', '\\'], '_', $processo->numero_processo) . "_todos_documentos_" . now()->format('Ymd_His') . '.pdf';
        $caminhoArquivo = public_path('uploads/documentos_finalizacao/' . $nomeArquivo);

        $caminhoCarimbado = $this->mesclarECarimbarEmLote($arquivos, $caminhoArquivo, $processo, 'finalizar');

        if ($caminhoCarimbado) {
            $pageCount = $this->contarPaginasPdf($caminhoCarimbado);
            $this->atualizarContadorContrato($processo, $pageCount);
            return $caminhoCarimbado;
        }

        $sucesso = $this->mesclarPdfsComGhostscript($arquivos, $caminhoArquivo);

        if ($sucesso) {
            Log::warning('PDF mesclado com Ghostscript sem carimbo - Finalização', ['processo_id' => $processo->id]);
            return $caminhoArquivo;
        } else {
            throw new \Exception('Erro ao mesclar documentos com Ghostscript');
        }
    }

    private function resolverHomologacao(Processo $processo, array $requestData): ?Homologacao
    {
        $documento = $requestData['documento'] ?? null;
        $homologacaoId = $requestData['homologacao_id'] ?? null;

        // Modalidades que não usam homologação (Dispensa de Obra): todo documento é
        // tratado como nível-processo (homologacao_id = null no banco).
        if (!$this->homologacaoService->usaHomologacao($processo)) {
            return null;
        }

        if (!$documento || !$this->homologacaoService->ehTipoPorHomologacao($documento)) {
            return null;
        }

        if (!$homologacaoId) {
            // Quando ainda não existe nenhuma homologação, criamos a primeira
            // automaticamente para que os documentos do bloco placeholder
            // possam ser gerados diretamente.
            if (!$this->homologacaoService->temHomologacao($processo)) {
                $homologacao = $this->homologacaoService->criarNovaHomologacao($processo);
                Log::info('Primeira homologação criada automaticamente ao gerar PDF', [
                    'processo_id' => $processo->id,
                    'homologacao_id' => $homologacao->id,
                    'documento' => $documento,
                ]);
                return $homologacao;
            }

            // Modalidades de homologação efetivamente única (Concorrência, Inexigibilidade,
            // Dispensa de Obra): se já existe uma homologação e nenhuma foi informada via
            // request, usa a única existente — não faz sentido exigir id.
            if (!$this->homologacaoService->exigeLotesPendentes($processo)) {
                $homologacao = $processo->homologacoes()->orderBy('id')->first();
                if ($homologacao) {
                    return $homologacao;
                }
            }

            throw new \DomainException('homologacao_id é obrigatório para gerar este tipo de documento.');
        }

        $homologacao = Homologacao::where('processo_id', $processo->id)
            ->where('id', $homologacaoId)
            ->first();

        if (!$homologacao) {
            throw new \DomainException('Homologação não pertence a este processo.');
        }

        return $homologacao;
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
        $homologacao = $validatedData['homologacao'] ?? null;
        $vencedor = $validatedData['vencedor'] ?? null;

        // Para tipos por-homologação, exibir apenas os lotes daquela homologação;
        // os dados de cabeçalho (orgão/empresa) também vêm da homologação.
        $vencedores = $processo->vencedores;
        if ($homologacao) {
            $homologacao->loadMissing('lotes');
            $loteIds = $homologacao->lotes->pluck('id');
            $vencedores = $processo->vencedores->map(function ($v) use ($loteIds) {
                $v->setRelation('lotes', $v->lotes->whereIn('id', $loteIds)->values());
                return $v;
            })->filter(fn($v) => $v->lotes->isNotEmpty())->values();
        }

        // Ata por vencedor: filtra para 1 vencedor só.
        if ($validatedData['documento'] === 'ata_registro_precos' && $vencedor) {
            $vencedores = $vencedores->filter(fn($v) => $v->id === $vencedor->id)->values();
        }

        // Resgata Ata persistida (para puxar número/cargo no template).
        $ataRegistroPreco = null;
        if ($validatedData['documento'] === 'ata_registro_precos' && $homologacao && $vencedor) {
            $ataRegistroPreco = AtaRegistroPreco::where('homologacao_id', $homologacao->id)
                ->where('vencedor_id', $vencedor->id)
                ->first();
        }

        return [
            'processo' => $processo,
            'prefeitura' => $processo->prefeitura,
            'finalizacao' => $homologacao ?: $processo->finalizacao,
            'homologacao' => $homologacao,
            'vencedor' => $vencedor,
            'ataRegistroPreco' => $ataRegistroPreco,
            'vencedores' => $vencedores,
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
        $homologacao = $validatedData['homologacao'] ?? null;
        $vencedor = $validatedData['vencedor'] ?? null;
        $subpasta = $this->gerarSubpasta($processo, $validatedData['documento'], $homologacao);

        $diretorio = public_path("uploads/documentos_finalizacao/{$subpasta}");
        if (!file_exists($diretorio)) {
            mkdir($diretorio, 0777, true);
        }

        $sufixoHomologacao = $homologacao ? "_homol{$homologacao->numero_sequencial}" : '';
        $sufixoVencedor = ($validatedData['documento'] === 'ata_registro_precos' && $vencedor)
            ? "_v{$vencedor->id}"
            : '';
        $nomeArquivo = "processo_finalizacao_{$numeroProcessoLimpo}_{$validatedData['documento']}{$sufixoHomologacao}{$sufixoVencedor}_" . now()->format('Ymd_His') . '.pdf';
        $caminhoRelativo = "uploads/documentos_finalizacao/{$subpasta}/{$nomeArquivo}";
        $caminhoCompleto = "{$diretorio}/{$nomeArquivo}";

        $pdf->save($caminhoCompleto);

        // Ata por vencedor: persiste na tabela própria.
        if ($validatedData['documento'] === 'ata_registro_precos' && $homologacao && $vencedor) {
            $this->atualizarAtaRegistroPreco(
                $processo,
                $homologacao,
                $vencedor,
                $validatedData['dataSelecionada'],
                $caminhoRelativo,
                $validatedData['assinantes'] ?? []
            );
        } else {
            $this->atualizarRegistroDocumento(
                $processo,
                $validatedData['documento'],
                $validatedData['dataSelecionada'],
                $caminhoRelativo,
                $homologacao
            );
        }

        return $caminhoCompleto;
    }

    private function atualizarAtaRegistroPreco(
        Processo $processo,
        Homologacao $homologacao,
        Vencedor $vencedor,
        string $dataSelecionada,
        string $caminhoRelativo,
        array $assinantes
    ): void {
        $ata = AtaRegistroPreco::where('homologacao_id', $homologacao->id)
            ->where('vencedor_id', $vencedor->id)
            ->first();

        if ($ata) {
            $caminhoAntigo = public_path($ata->caminho);
            if ($ata->caminho && file_exists($caminhoAntigo)) {
                unlink($caminhoAntigo);
            }

            $ata->update([
                'data_selecionada' => $dataSelecionada,
                'caminho' => $caminhoRelativo,
                'assinantes' => $assinantes,
                'gerado_em' => now(),
            ]);
        } else {
            AtaRegistroPreco::create([
                'processo_id' => $processo->id,
                'homologacao_id' => $homologacao->id,
                'vencedor_id' => $vencedor->id,
                'data_selecionada' => $dataSelecionada,
                'caminho' => $caminhoRelativo,
                'assinantes' => $assinantes,
                'gerado_em' => now(),
            ]);
        }
    }

    private function gerarSubpasta(Processo $processo, string $documento, ?Homologacao $homologacao = null): string
    {
        $base = 'finalizacao';

        if ($processo->modalidade === \App\Enums\ModalidadeEnum::DISPENSA) {
            $tipoProcedimento = $processo->tipo_procedimento ?? null;
            if ($tipoProcedimento == \App\Enums\TipoProcedimentoEnum::OBRA->value) {
                $base = 'finalizacao/dispensa_obra';
            } else {
                $base = 'finalizacao/dispensa_compras_servicos';
            }
        }

        if ($homologacao) {
            return "{$base}/homologacao_{$homologacao->numero_sequencial}/{$documento}";
        }

        return "{$base}/{$documento}";
    }

    private function atualizarRegistroDocumento(
        Processo $processo,
        string $documento,
        string $dataSelecionada,
        string $caminhoRelativo,
        ?Homologacao $homologacao = null
    ): void {
        $query = Documento::where('processo_id', $processo->id)
            ->where('tipo_documento', $documento);

        if ($homologacao) {
            $query->where('homologacao_id', $homologacao->id);
        } else {
            $query->whereNull('homologacao_id');
        }

        $documentoExistente = $query->first();

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
                'homologacao_id' => $homologacao?->id,
                'tipo_documento' => $documento,
                'data_selecionada' => $dataSelecionada,
                'caminho' => $caminhoRelativo,
                'gerado_em' => now(),
            ]);
        }
    }

    private function processarAnexos(Processo $processo, string $documento, string $caminhoPrincipal, ?Homologacao $homologacao = null): void
    {
        Log::info("🔍 INICIANDO PROCESSAMENTO DE ANEXOS - Finalização: {$documento}", [
            'caminho_principal' => $caminhoPrincipal,
            'tamanho_inicial' => file_exists($caminhoPrincipal) ? filesize($caminhoPrincipal) : 0
        ]);

        $anexos = $this->obterAnexos($processo, $documento, $homologacao);

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

    private function obterAnexos(Processo $processo, string $documento, ?Homologacao $homologacao = null): array
    {
        $anexos = [];
        $mapeamentoAnexos = $this->documentoService->getMapeamentoAnexos();
        $campoAnexo = $mapeamentoAnexos[$documento] ?? null;

        if (!$campoAnexo) {
            Log::info("Nenhum mapeamento de anexo encontrado para documento: {$documento}");
            return $anexos;
        }

        // Para tipos por-homologação cujo anexo é da Homologação, lê de lá; caso contrário, da Finalizacao.
        $fonteAnexo = $homologacao && isset($homologacao->{$campoAnexo})
            ? $homologacao
            : $processo->finalizacao;

        if (!empty($fonteAnexo?->$campoAnexo)) {
            $caminhoRelativo = $fonteAnexo->$campoAnexo;
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

    private function mesclarECarimbarEmLote(array $arquivos, string $caminhoSaida, Processo $processo, string $fase = 'finalizar'): ?string
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

                        $this->adicionarCarimbo($pdf, $processo, $paginaAtual, $pageCountTotal);

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
            Log::error('Erro ao mesclar e carimbar PDFs em lote - Finalização', [
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

    private function atualizarContadorContrato(Processo $processo, int $paginasFinalizacao): void
    {
        try {
            $processo->contTotalPagePhase2 = $paginasFinalizacao;
            $processo->save();

            Log::info('Contador atualizado para contrato', [
                'processo_id' => $processo->id,
                'paginas_inicializacao' => $processo->contTotalPage ?? 0,
                'paginas_finalizacao' => $paginasFinalizacao,
                'total_para_contrato' => ($processo->contTotalPage ?? 0) + $paginasFinalizacao
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