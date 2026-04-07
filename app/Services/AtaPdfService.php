<?php

namespace App\Services;

use App\Models\Processo;
use App\Models\Documento;
use App\Models\LoteContratado;
use App\Models\EstoqueLote;
use Barryvdh\DomPDF\Facade\Pdf;
use setasign\Fpdi\Tcpdf\Fpdi;
use Illuminate\Support\Facades\Log;

class AtaPdfService
{
    public function gerarESalvarContrato(Processo $processo, array $data): array
    {
        $contratacoesIds = $data['contratacoes_selecionadas'] ?? [];
        $campos = $data['campos'] ?? [];
        $dataSelecionada = $data['data'] ?? now()->format('Y-m-d');
        $assinantes = $data['assinantes'] ?? [];

        $this->validarDadosContrato($processo, $contratacoesIds, $campos, $assinantes);

        $dados = $this->prepararDadosParaPdf($processo, $contratacoesIds, $campos, $assinantes);
        $viewAta = $this->determinarViewContrato($processo);

        $pdf = Pdf::loadView($viewAta, $dados)
            ->setPaper('a4', 'portrait');

        $caminhoTemp = $this->salvarArquivoTemporario($pdf, $processo);
        $caminhoCarimbado = $this->criarContratoCarimbado($caminhoTemp['completo'], $processo);

        if (!$caminhoCarimbado) {
            throw new \Exception('Falha ao aplicar carimbo ao contrato.');
        }

        $caminhoFinal = $this->moverParaDestinoFinal($caminhoCarimbado, $processo);
        $this->salvarDocumento($processo, $caminhoFinal, $contratacoesIds, $dataSelecionada, $campos, $assinantes);

        $this->marcarContratacoesComoContratado($processo, $contratacoesIds);

        $nomeArquivo = basename($caminhoFinal['relativo']);
        $downloadUrl = url("admin/atas/{$processo->id}/download/{$nomeArquivo}");

        Log::info('Contrato gerado com carimbo automático', [
            'processo_id' => $processo->id,
            'numero_contrato' => $campos['numero_contrato'] ?? '',
            'itens_incluidos' => count($contratacoesIds),
            'download_url' => $downloadUrl
        ]);

        return [
            'download_url' => $downloadUrl
        ];
    }

    public function downloadAta(Processo $processo, ?string $nomeArquivo = null)
    {
        if ($nomeArquivo) {
            $caminhoCompleto = public_path("uploads/atas/{$processo->id}/{$nomeArquivo}");

            if (!file_exists($caminhoCompleto)) {
                throw new \Exception('Arquivo não encontrado.');
            }

            return response()->download($caminhoCompleto, $nomeArquivo);
        }

        // Verifica se temos um nome de arquivo específico na requisição
        $request = request();
        if ($request->has('documento_id')) {
            // Busca o documento específico pelo ID
            $documento = Documento::where('processo_id', $processo->id)
                ->where('id', $request->get('documento_id'))
                ->where('tipo_documento', 'contrato')
                ->firstOrFail();
        } elseif ($request->has('nome_arquivo')) {
            // Busca pelo nome do arquivo
            $nomeArquivoBusca = $request->get('nome_arquivo');
            $documento = Documento::where('processo_id', $processo->id)
                ->where('tipo_documento', 'contrato')
                ->where('caminho', 'LIKE', "%{$nomeArquivoBusca}%")
                ->firstOrFail();
        } else {
            // Fallback: pega o mais recente (comportamento original)
            $documento = Documento::where('processo_id', $processo->id)
                ->where('tipo_documento', 'contrato')
                ->latest('gerado_em')
                ->firstOrFail();
        }

        $caminhoCompleto = public_path($documento->caminho);

        if (!file_exists($caminhoCompleto)) {
            throw new \Exception('Arquivo da ata não encontrado.');
        }

        // Usa o nome original do arquivo se disponível, ou gera um novo
        if (isset($documento->nome_arquivo)) {
            $nomeArquivoDownload = $documento->nome_arquivo;
        } else {
            $numeroProcessoLimpo = str_replace(['/', '\\'], '_', $processo->numero_processo);
            $timestamp = $documento->gerado_em ? $documento->gerado_em->format('Ymd_His') : now()->format('Ymd_His');
            $nomeArquivoDownload = "ata_contratacao_{$numeroProcessoLimpo}_{$timestamp}.pdf";
        }

        return response()->download($caminhoCompleto, $nomeArquivoDownload);
    }

    private function validarDadosContrato(Processo $processo, array $contratacoesIds, array $campos, array $assinantes): void
    {
        if (empty($contratacoesIds)) {
            throw new \Exception('❌ Selecione pelo menos uma contratação para gerar o contrato.');
        }

        $camposObrigatorios = ['numero_contrato', 'data_assinatura_contrato', 'comarca', 'fonte_recurso'];
        foreach ($camposObrigatorios as $campo) {
            if (empty($campos[$campo] ?? '')) {
                throw new \Exception("❌ O campo '{$this->getNomeCampo($campo)}' é obrigatório.");
            }
        }

        $numeroContratoExistente = Documento::where('processo_id', $processo->id)
            ->whereJsonContains('campos->numero_contrato', $campos['numero_contrato'] ?? '')
            ->first();

        if ($numeroContratoExistente) {
            throw new \Exception('❌ Este número de contrato já foi utilizado. Use um número diferente.');
        }

        if (empty($assinantes)) {
            throw new \Exception('❌ Adicione pelo menos um assinante ao contrato.');
        }
    }

    private function prepararDadosParaPdf(Processo $processo, array $contratacoesIds, array $campos, array $assinantes): array
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

        $contratacoesSelecionadas = LoteContratado::whereIn('id', $contratacoesIds)
            ->where('processo_id', $processo->id)
            ->get();

        $lotesComContratacoes = $contratacoesSelecionadas->pluck('lote_id')->unique();
        $processo->lotes = $processo->lotes->whereIn('id', $lotesComContratacoes);

        $contratacoes = LoteContratado::where('processo_id', $processo->id)
            ->with(['lote', 'vencedor'])
            ->where('status', 'PENDENTE')
            ->get();

        $valorTotalContrato = $contratacoes->sum('valor_total');
        $contratoSalvo = \App\Models\Contrato::where('processo_id', $processo->id)->first();
        $quantidadeTotalContrato = $contratacoes->sum('quantidade_contratada');

        $dadosAta = Documento::where('processo_id', $processo->id)
            ->where('tipo_documento', 'contrato')
            ->first();

        $assinantesAta = $dadosAta ? ($dadosAta->assinantes ?? []) : [];

        $dados = [
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
            'campos' => $contratoSalvo ? [
                'numero_contrato' => $contratoSalvo->numero_contrato,
                'data_assinatura_contrato' => $contratoSalvo->data_assinatura_contrato,
                'numero_extrato' => $contratoSalvo->numero_extrato,
                'comarca' => $contratoSalvo->comarca,
                'fonte_recurso' => $contratoSalvo->fonte_recurso,
                'subcontratacao' => $contratoSalvo->subcontratacao,
            ] : [],
        ];

        if (!empty($campos)) {
            $dados['campos'] = array_merge($dados['campos'] ?? [], $campos);
        }

        if (!empty($assinantes)) {
            $dados['assinantes'] = $assinantes;
            $dados['hasSelectedAssinantes'] = true;

            $dados['primeiroAssinante'] = [
                'responsavel' => $assinantes[0]['responsavel'] ?? 'Responsável não informado',
                'unidade_nome' => $assinantes[0]['unidade_nome'] ?? 'Unidade não informada',
                'cargo' => $assinantes[0]['cargo'] ?? ''
            ];
        }

        return $dados;
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

            $caminhoCarimbado = tempnam(sys_get_temp_dir(), 'contrato_carimbado_') . '.pdf';
            $paginaInicial = ($processo->contTotalPagePhase1 ?? 0) + ($processo->contTotalPagePhase2 ?? 0);

            for ($pagina = 1; $pagina <= $pageCount; $pagina++) {
                $paginaAtual = $pagina;

                $pdf = new Fpdi();
                $this->configurarFonte($pdf);

                $pdf->setSourceFile($caminhoOriginal);
                $tplId = $pdf->importPage($pagina);
                $pdf->AddPage();
                $pdf->useTemplate($tplId);

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
                // Atualizar contador de páginas
                $processo->contTotalPagePhase3 = $pageCount;
                \App\Models\Processo::where('id', $processo->id)
                    ->update(['contTotalPagePhase3' => $pageCount]);

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
            foreach ($paginasTemp as $tempFile) {
                if (file_exists($tempFile)) {
                    unlink($tempFile);
                }
            }
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

    private function salvarDocumento(Processo $processo, array $caminho, array $contratacoesIds, string $dataSelecionada, array $campos, array $assinantes): void
    {
        $valorTotalContrato = LoteContratado::whereIn('id', $contratacoesIds)
            ->where('processo_id', $processo->id)
            ->sum('valor_total');

        $quantidadeItens = count($contratacoesIds);

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

        if (!empty($campos)) {
            $dadosDocumento['campos'] = $campos;
            Log::info('Campos salvos no JSON do documento', [
                'processo_id' => $processo->id,
                'campos_json' => $campos,
            ]);
        }

        if (!empty($assinantes)) {
            $dadosDocumento['assinantes'] = $assinantes;
        }

        if (!empty($contratacoesIds)) {
            $dadosDocumento['contratacoes_selecionadas'] = $contratacoesIds;
        }

        $documentoExistente = Documento::where('processo_id', $processo->id)
            ->where('tipo_documento', 'contrato')
            ->when(!empty($campos['numero_contrato']), function($query) use ($campos) {
                return $query->whereJsonContains('campos->numero_contrato', $campos['numero_contrato']);
            })
            ->first();

        if ($documentoExistente) {
            $caminhoAntigo = public_path($documentoExistente->caminho);
            if (file_exists($caminhoAntigo)) {
                unlink($caminhoAntigo);
            }

            $documentoExistente->update($dadosDocumento);
            Log::info('Documento existente atualizado', $dadosDocumento);
        } else {
            Documento::create($dadosDocumento);
            Log::info('Novo documento criado', $dadosDocumento);
        }
    }

    private function marcarContratacoesComoContratado(Processo $processo, array $contratacoesIds): void
    {
        LoteContratado::whereIn('id', $contratacoesIds)
            ->where('processo_id', $processo->id)
            ->update(['status' => 'CONTRATADO']);
    }

    private function prepararItensParaTabela($contratacoes): array
    {
        $itens = [];

        // Agrupar contratacoes por lote
        $contratacoesAgrupadas = $contratacoes->groupBy(function($contratacao) {
            return $contratacao->lote->lote ?? '0';
        });

        foreach ($contratacoesAgrupadas as $numeroLote => $contratacoesLote) {
            // Adicionar linha do cabeçalho do lote se houver mais de um lote
            if ($contratacoesAgrupadas->count() > 1) {
                $itens[] = [
                    'item' => '',
                    'especificacao' => "LOTE {$numeroLote}",
                    'unidade_medida' => '',
                    'quantidade' => '',
                    'valor_unitario' => '',
                    'valor_total' => '',
                    'is_lote_header' => true // Flag para estilizar diferente
                ];
            }

            // Adicionar itens deste lote
            foreach ($contratacoesLote as $contratacao) {
                if ($contratacao->lote) {
                    $itens[] = [
                        'item' => $contratacao->lote->item ?? '', // Usar o número do item do lote
                        'especificacao' => $contratacao->lote->descricao ?? 'Não especificado',
                        'unidade_medida' => $contratacao->lote->unidade ?? '',
                        'quantidade' => number_format($contratacao->quantidade_contratada, 2, ',', '.'),
                        'valor_unitario' => 'R$ ' . number_format($contratacao->valor_unitario, 2, ',', '.'),
                        'valor_total' => 'R$ ' . number_format($contratacao->valor_total, 2, ',', '.'),
                        'is_lote_header' => false
                    ];
                }
            }

            // Adicionar linha vazia entre lotes (exceto no último)
            if ($numeroLote != $contratacoesAgrupadas->keys()->last()) {
                $itens[] = [
                    'item' => '',
                    'especificacao' => '',
                    'unidade_medida' => '',
                    'quantidade' => '',
                    'valor_unitario' => '',
                    'valor_total' => '',
                    'is_spacer' => true // Flag para linha espaçadora
                ];
            }
        }

        return $itens;
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
    public function gerarPdfSaldoDisponivel(Processo $processo)
    {
        $ataService = app(AtaService::class);
        $dados = $ataService->prepararDadosParaExibicao($processo);
        
        $pdf = Pdf::loadView('Admin.Atas.pdf.saldo_disponivel', [
            'processo' => $processo,
            'prefeitura' => $processo->prefeitura,
            'dadosAtas' => $dados['dadosAtas'],
            'itensSaldo' => $dados['itensSaldo'],
            'itensEsgotados' => $dados['itensEsgotados'],
        ])->setPaper('a4', 'portrait');

        $numeroProcessoLimpo = str_replace(['/', '\\'], '_', $processo->numero_processo);
        $nomeArquivo = "saldo_ata_{$numeroProcessoLimpo}.pdf";

        return $pdf->download($nomeArquivo);
    }
}
