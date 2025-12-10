<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\Processo;
use App\Models\Documento;
use Illuminate\Http\Request;
use setasign\Fpdi\Tcpdf\Fpdi;
use App\Models\LoteContratado;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class ContratoProcessoController extends Controller
{
    // Configuração única para contrato
    protected $documentoConfig = [
        'contrato' => [
            'titulo' => 'CONTRATO',
            'cor' => 'bg-blue-500',
            'campos' => ['numero_contrato', 'data_assinatura_contrato', 'numero_extrato', 'comarca', 'fonte_recurso', 'subcontratacao'],
            'requer_assinatura' => true,
        ]
    ];

    public function contrato(Processo $processo)
    {
        $processo->load(['prefeitura.unidades', 'detalhe', 'vencedores.lotes.contratados']);

        // Carregar dados do contrato se existirem
        $contrato = Contrato::where('processo_id', $processo->id)->first();

        // Carregar contratações
        $contratacoes = LoteContratado::where('processo_id', $processo->id)
            ->with(['lote', 'vencedor'])
            ->get()
            ->groupBy('vencedor_id');

        $documentos = $this->documentoConfig;

        return view('Admin.Processos.contrato', compact('processo', 'documentos', 'contrato', 'contratacoes'));
    }

    /**
     * Salvar campo individual do contrato
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

            // Verificar se o campo é válido
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
            $contrato = Contrato::where('processo_id', $processo->id)->first();

            if (!$contrato) {
                $contrato = Contrato::create([
                    'processo_id' => $processo->id
                ]);
            }

            // Processar campo específico
            if ($campo === 'data_assinatura_contrato' && $valor) {
                $valor = \Carbon\Carbon::parse($valor)->format('Y-m-d');
            }

            // Atualizar o campo
            $contrato->update([$campo => $valor]);

            Log::info('Campo do contrato salvo com sucesso', [
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
            Log::error('Erro ao salvar campo do contrato', [
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
     * Obter dados salvos do contrato
     */
    public function obterDadosContrato(Processo $processo)
    {
        try {
            $contrato = Contrato::where('processo_id', $processo->id)->first();

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
            Log::error('Erro ao obter dados do contrato', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter dados do contrato.'
            ], 500);
        }
    }

    /**
     * Gera o PDF do contrato
     */
    public function gerarPdf(Request $request, Processo $processo)
    {
        try {
            Log::info('Iniciando geração de PDF - Contrato', [
                'processo_id' => $processo->id,
                'request_data' => $request->all()
            ]);

            $validatedData = $this->validarRequisicaoPdf($request, $processo);
            $data = $this->prepararDadosPdf($processo, $validatedData);

            // SALVAR OS CAMPOS DO CONTRATO NO BANCO DE DADOS
            $this->salvarCamposContrato($processo->id, $validatedData['campos']);

            $view = $this->determinarViewContrato($processo);

            Log::info('View selecionada para contrato', ['view' => $view]);

            $pdf = Pdf::loadView($view, $data)->setPaper('a4', 'portrait');

            $caminhoCompleto = $this->salvarDocumento($processo, $pdf, $validatedData);

            Log::info('Contrato gerado com sucesso', [
                'processo_id' => $processo->id,
                'caminho' => $caminhoCompleto
            ]);

            return response()->json([
                'success' => true,
                'message' => '✅ Contrato gerado com sucesso! Clique em "Download" para visualizar o arquivo.',
                'documento' => 'contrato'
            ]);

        } catch (\Throwable $e) {
            Log::error('Erro ao gerar contrato', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage(),
                'linha' => $e->getLine(),
                'arquivo' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => '❌ Ocorreu um erro inesperado ao gerar o contrato: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Salva ou atualiza os campos do contrato no banco de dados
     */
    private function salvarCamposContrato($processoId, array $campos): void
    {
        try {
            // Verificar se já existe um registro para este processo
            $contrato = Contrato::where('processo_id', $processoId)->first();

            // Preparar dados para salvar
            $dadosContrato = [
                'processo_id' => $processoId,
                'numero_contrato' => $campos['numero_contrato'] ?? null,
                'data_assinatura_contrato' => !empty($campos['data_assinatura_contrato'])
                    ? \Carbon\Carbon::parse($campos['data_assinatura_contrato'])->format('Y-m-d')
                    : null,
                'numero_extrato' => $campos['numero_extrato'] ?? null,
                'comarca' => $campos['comarca'] ?? null,
                'fonte_recurso' => $campos['fonte_recurso'] ?? null,
                'subcontratacao' => $campos['subcontratacao'] ?? null,
            ];

            if ($contrato) {
                // Atualizar registro existente
                $contrato->update($dadosContrato);
                Log::info('Contrato atualizado com sucesso', [
                    'processo_id' => $processoId,
                    'campos' => $dadosContrato
                ]);
            } else {
                // Criar novo registro
                Contrato::create($dadosContrato);
                Log::info('Contrato criado com sucesso', [
                    'processo_id' => $processoId,
                    'campos' => $dadosContrato
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Erro ao salvar campos do contrato', [
                'processo_id' => $processoId,
                'erro' => $e->getMessage(),
                'campos' => $campos
            ]);
            throw $e;
        }
    }

    /**
     * Download do contrato COM CARIMBO
     */
    public function baixarContrato(Processo $processo)
    {
        try {
            $documento = Documento::where('processo_id', $processo->id)
                ->where('tipo_documento', 'contrato')
                ->firstOrFail();

            $caminhoOriginal = public_path($documento->caminho);

            if (!file_exists($caminhoOriginal)) {
                throw new \Exception('Arquivo do contrato não encontrado.');
            }

            Log::info('Iniciando download com carimbo - Contrato', [
                'processo_id' => $processo->id,
                'caminho_original' => $caminhoOriginal
            ]);

            // Criar uma cópia carimbada para download
            $caminhoCarimbado = $this->criarContratoCarimbado($caminhoOriginal, $processo);

            if ($caminhoCarimbado && file_exists($caminhoCarimbado)) {
                Log::info('Download com carimbo realizado com sucesso', [
                    'processo_id' => $processo->id,
                    'caminho_carimbado' => $caminhoCarimbado
                ]);

                // Fazer download do arquivo carimbado e depois excluí-lo
                return response()->download($caminhoCarimbado,
                    $this->gerarNomeDownload($processo, 'contrato_carimbado.pdf'))
                    ->deleteFileAfterSend(true);
            } else {
                // Se não conseguiu carimbar, baixa o original
                Log::warning('Falha ao carimbar contrato, baixando original', [
                    'processo_id' => $processo->id
                ]);
                return response()->download($caminhoOriginal,
                    $this->gerarNomeDownload($processo, 'contrato.pdf'));
            }

        } catch (\Exception $e) {
            Log::error('Erro ao baixar contrato', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao baixar contrato: ' . $e->getMessage()
            ], 500);
        }
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

                // Aplicar carimbo em todas as páginas (incluindo a primeira)
                // Passar $paginaInicial para calcular página absoluta corretamente
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
                    'paginas' => $pageCount,
                    'pagina_inicial' => $paginaInicial,
                    'pagina_final' => $paginaInicial + $pageCount
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
        }
    }

    /**
     * Gera nome do arquivo para download
     */
    private function gerarNomeDownload(Processo $processo, string $sufixo = 'contrato.pdf'): string
    {
        $numeroProcessoLimpo = str_replace(['/', '\\'], '_', $processo->numero_processo);
        return "contrato_{$numeroProcessoLimpo}_{$sufixo}";
    }

    // =========================================================
    // MÉTODOS PRIVADOS - GERAÇÃO DE PDF
    // =========================================================

    private function validarRequisicaoPdf(Request $request, Processo $processo): array
    {
        // Data não é mais obrigatória - usa data atual se não for fornecida
        $dataSelecionada = $request->query('data', now()->format('Y-m-d'));

        // Assinantes não são mais obrigatórios - processa se existirem
        $assinantes = $this->processarAssinantes($request);

        // Campos do contrato
        $campos = $this->processarCamposContrato($request);

        return [
            'documento' => 'contrato',
            'dataSelecionada' => $dataSelecionada,
            'assinantes' => $assinantes,
            'campos' => $campos
        ];
    }

    private function processarCamposContrato(Request $request): array
    {
        $camposJson = $request->query('campos');

        if (!$camposJson) {
            // Se não vier campos na requisição, buscar do banco de dados
            $contrato = Contrato::where('processo_id', $request->route('processo')->id)->first();
            
            if ($contrato) {
                return [
                    'numero_contrato' => $contrato->numero_contrato,
                    'data_assinatura_contrato' => $contrato->data_assinatura_contrato,
                    'numero_extrato' => $contrato->numero_extrato,
                    'comarca' => $contrato->comarca,
                    'fonte_recurso' => $contrato->fonte_recurso,
                    'subcontratacao' => $contrato->subcontratacao,
                ];
            }
            
            return [];
        }

        $camposDecoded = urldecode($camposJson);
        $campos = json_decode($camposDecoded, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning("Erro ao decodificar JSON de campos - Contrato: " . json_last_error_msg());
            return [];
        }

        return $campos;
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
            Log::warning("Erro ao decodificar JSON de assinantes - Contrato: " . json_last_error_msg());
            return [];
        }

        return $assinantes;
    }

    private function prepararDadosPdf(Processo $processo, array $validatedData): array
    {
        $processo->load(['prefeitura', 'vencedores.lotes.contratados', 'finalizacao']);

        // Carregar todas as contratações
        $contratacoes = LoteContratado::where('processo_id', $processo->id)
            ->with(['lote', 'vencedor'])
            ->whereIn('status', ['PENDENTE', 'CONTRATADO'])
            ->get();
        
        // ==============================================
        // DADOS DO CONTRATANTE (PREFEITURA)
        // ==============================================
        $dadosContratante = [
            'orgao' => $processo->finalizacao->orgao_responsavel ?? $processo->prefeitura->cidade,
            'cidade' => $processo->prefeitura->cidade,
            'uf' => $processo->prefeitura->uf,
            'endereco' => $processo->prefeitura->endereco,
            'cnpj' => $processo->finalizacao->cnpj ?? $processo->prefeitura->cnpj,
            'responsavel' => $processo->finalizacao->responsavel ?? $processo->prefeitura->autoridade_competente,
            'cargo_responsavel' => $processo->finalizacao->cargo_responsavel ?? 'Prefeito Municipal',
            'cpf_responsavel' => $processo->finalizacao->cpf_responsavel ?? null,
        ];
        
        // Formatando CNPJ e CPF
        $dadosContratante['cnpj_formatado'] = $this->formatarCNPJ($dadosContratante['cnpj']);
        $dadosContratante['cpf_responsavel_formatado'] = $dadosContratante['cpf_responsavel'] 
            ? $this->formatarCPF($dadosContratante['cpf_responsavel'])
            : null;

        // ==============================================
        // DADOS DO CONTRATADO (EMPRESA VENCEDORA)
        // ==============================================
        $dadosContratado = $this->prepararDadosContratado($processo, $contratacoes);

        // ==============================================
        // DADOS DA TABELA DE ITENS
        // ==============================================
        $itensTabela = $this->prepararItensParaTabela($contratacoes);
        
        // Calcular totais
        $valorTotalContrato = $contratacoes->sum('valor_total');
        $quantidadeTotalContrato = $contratacoes->sum('quantidade_contratada');

        // Carregar dados salvos do contrato
        $contratoSalvo = Contrato::where('processo_id', $processo->id)->first();

        return [
            'processo' => $processo,
            'prefeitura' => $processo->prefeitura,
            'contratacoes' => $contratacoes,
            'itensTabela' => $itensTabela,
            'valorTotalContrato' => $valorTotalContrato,
            'quantidadeTotalContrato' => $quantidadeTotalContrato,
            
            // VALOR POR EXTENSO
            'valorTotalPorExtenso' => $this->escreverValorPorExtenso($valorTotalContrato),
            
            // Dados formatados
            'dadosContratante' => $dadosContratante,
            'dadosContratado' => $dadosContratado,
            
            // Dados do contrato salvos
            'contratoSalvo' => $contratoSalvo,
            
            // Dados gerais
            'dataGeracao' => now()->format('d/m/Y H:i:s'),
            'dataSelecionada' => $validatedData['dataSelecionada'],
            'assinantes' => $validatedData['assinantes'],
            'hasSelectedAssinantes' => !empty($validatedData['assinantes']),
            'campos' => $validatedData['campos'],
            'dataAssinaturaFormatada' => !empty($validatedData['campos']['data_assinatura_contrato']) 
                ? \Carbon\Carbon::parse($validatedData['campos']['data_assinatura_contrato'])->format('d/m/Y')
                : ($contratoSalvo && $contratoSalvo->data_assinatura_contrato 
                    ? \Carbon\Carbon::parse($contratoSalvo->data_assinatura_contrato)->format('d/m/Y')
                    : null),
        ];
    }

    // Método para escrever valor por extenso
    private function escreverValorPorExtenso($valor): string
    {
        // Remover formatação se existir
        if (is_string($valor)) {
            $valor = preg_replace('/[^0-9,.]/', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }
        
        $valor = floatval($valor);
        
        // Usar a classe helper
        return \App\Helpers\ValorPorExtenso::escrever($valor);
    }

    // ==============================================
    // MÉTODO PARA PREPARAR DADOS DO CONTRATADO
    // ==============================================
    private function prepararDadosContratado(Processo $processo, $contratacoes): array
    {
        // Se houver dados na finalização, usa eles
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
        
        // Se não, tenta pegar do primeiro vencedor com contratações
        if ($contratacoes->count() > 0) {
            $primeiroVencedor = $contratacoes->first()->vencedor;
            
            if ($primeiroVencedor) {
                return [
                    'razao_social' => $primeiroVencedor->razao_social,
                    'cnpj' => $primeiroVencedor->cnpj,
                    'cnpj_formatado' => $this->formatarCNPJ($primeiroVencedor->cnpj),
                    'endereco' => 'Endereço da empresa', // Precisa adicionar campo no modelo Vencedor
                    'representante' => $primeiroVencedor->representante ?? 'Representante não informado',
                    'cpf_representante' => $primeiroVencedor->cpf ?? null,
                    'cpf_representante_formatado' => $primeiroVencedor->cpf 
                        ? $this->formatarCPF($primeiroVencedor->cpf)
                        : null,
                    'fonte_dados' => 'vencedor',
                ];
            }
        }
        
        // Fallback
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

    // ==============================================
    // MÉTODOS AUXILIARES
    // ==============================================
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

    // Novo método para preparar itens para a tabela
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

    private function determinarViewContrato(Processo $processo): string
    {
        $viewBase = "Admin.Processos.contrato";

        $modalidade = $this->formatarNomeArquivo($processo->modalidade?->name ?? '');
        $view = "{$viewBase}.{$modalidade}.contrato";

        if (!view()->exists($view)) {
            throw new \Exception("O modelo de contrato para a modalidade '{$modalidade}' não foi encontrado. View: {$view}");
        }

        return $view;
    }

    private function formatarNomeArquivo(string $nome): string
    {
        $nome = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $nome));
        return str_replace(' ', '_', $nome);
    }

    private function salvarDocumento(Processo $processo, $pdf, array $validatedData): string
    {
        $numeroProcessoLimpo = str_replace(['/', '\\'], '_', $processo->numero_processo);
        $subpasta = $this->gerarSubpasta($processo);

        $diretorio = public_path("uploads/contratos/{$subpasta}");
        if (!file_exists($diretorio)) {
            mkdir($diretorio, 0777, true);
        }

        $nomeArquivo = "contrato_{$numeroProcessoLimpo}_" . now()->format('Ymd_His') . '.pdf';
        $caminhoRelativo = "uploads/contratos/{$subpasta}/{$nomeArquivo}";
        $caminhoCompleto = "{$diretorio}/{$nomeArquivo}";

        $pdf->save($caminhoCompleto);
        $this->atualizarRegistroDocumento($processo, $validatedData['dataSelecionada'], $caminhoRelativo);

        return $caminhoCompleto;
    }

    private function gerarSubpasta(Processo $processo): string
    {
        return "contratos/{$processo->id}";
    }

    private function atualizarRegistroDocumento(Processo $processo, string $dataSelecionada, string $caminhoRelativo): void
    {
        $documentoExistente = Documento::where('processo_id', $processo->id)
            ->where('tipo_documento', 'contrato')
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
                'tipo_documento' => 'contrato',
                'data_selecionada' => $dataSelecionada,
                'caminho' => $caminhoRelativo,
                'gerado_em' => now(),
            ]);
        }
    }

    // =========================================================
    // MÉTODOS PRIVADOS - CARIMBAGEM (Ghostscript)
    // =========================================================

    private function mesclarPdfsComGhostscript(array $arquivos, string $outputPath): bool
    {
        $listaArquivos = null;

        try {
            $arquivosValidos = [];
            foreach ($arquivos as $index => $arquivo) {
                if (!file_exists($arquivo)) {
                    Log::error('Arquivo não encontrado para mesclagem - Contrato', ['arquivo' => $arquivo]);
                    return false;
                }

                $tamanho = filesize($arquivo);
                if ($tamanho === 0) {
                    Log::error('Arquivo vazio encontrado - Contrato', ['arquivo' => $arquivo]);
                    return false;
                }

                $arquivosValidos[] = $arquivo;
            }

            $listaArquivos = tempnam(sys_get_temp_dir(), 'gs_list_contrato_');
            file_put_contents($listaArquivos, implode("\n", $arquivosValidos));

            $comando = sprintf(
                'gs -dBATCH -dNOPAUSE -q -sDEVICE=pdfwrite -dPDFSETTINGS=/prepress -sOutputFile="%s" @"%s"',
                $outputPath,
                $listaArquivos
            );

            Log::info('Executando Ghostscript - Contrato', [
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
                Log::info('PDFs mesclados com sucesso usando Ghostscript - Contrato', [
                    'arquivo_saida' => $outputPath,
                    'tamanho' => $outputTamanho
                ]);
                return true;
            } else {
                Log::error('Erro ao mesclar PDFs com Ghostscript - Contrato', [
                    'return_code' => $returnCode,
                    'arquivo_saida_existe' => $outputExiste,
                    'arquivo_saida_tamanho' => $outputTamanho
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Exceção ao mesclar PDFs com Ghostscript - Contrato', [
                'erro' => $e->getMessage()
            ]);
            return false;
        } finally {
            if ($listaArquivos && file_exists($listaArquivos)) {
                unlink($listaArquivos);
            }
        }
    }

    // =========================================================
    // MÉTODOS AUXILIARES
    // =========================================================

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

        // OBTER PÁGINA INICIAL DO CONTRATO (continuação da finalização)
        $paginaInicial = $processo->contTotalPage ?? 0;

        // CALCULAR PÁGINA ABSOLUTA (contrato)
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
            Log::error('Erro ao contar páginas do contrato', [
                'caminho' => $caminhoPdf,
                'erro' => $e->getMessage()
            ]);
            return 0;
        }
    }
}