<?php

namespace App\Http\Controllers;

use App\Models\Lote;
use App\Models\Processo;
use App\Models\Vencedor;
use App\Models\Documento;
use App\Models\Finalizacao;
use App\Imports\LotesImport;
use Illuminate\Http\Request;
use setasign\Fpdi\Tcpdf\Fpdi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class FinalizacaoProcessoController extends Controller
{
    // Documentos configuration
    protected $documentos = [
        'atos_sessao' => [
            'titulo' => 'ATOS DA SESSÃO',
            'cor' => 'bg-red-500',
            'campos' => ['anexo_atos_sessao'],
        ],
        'proposta' => [
            'titulo' => 'PROPOSTAS',
            'cor' => 'bg-blue-500',
            'campos' => ['anexo_proposta'],
        ],
        'proposta_readequada' => [
            'titulo' => 'PROPOSTA VENCEDORA READEQUADA',
            'cor' => 'bg-purple-500',
            'campos' => ['anexo_proposta_readequada'],
        ],
        'documento_habilitacao_empresa_vencedora' => [
            'titulo' => 'DOCUMENTOS DE HABILITAÇÃO EMPRESA VENCEDORA',
            'cor' => 'bg-green-500',
            'campos' => ['anexo_habilitacao'],
        ],
        'recurso_contratacoes_decisao_recursos' => [
            'titulo' => 'RECURSOS, CONTRARAZÕES E DECISÃO DOS RECURSOS',
            'cor' => 'bg-green-500',
            'campos' => ['anexo_recurso_contratacoes'],
        ],
        'termo_ajusdicacao' => [
            'titulo' => 'TERMO DE ADJUDICAÇÃO',
            'cor' => 'bg-yellow-500',
            'campos' => ['anexo_planilha'],
        ],
        'parecer_controle_interno' => [
            'titulo' => 'PARECER DO CONTROLE INTERNO',
            'cor' => 'bg-orange-500',
            'campos' => [],
        ],
        'termo_homologacao' => [
            'titulo' => 'TERMO DE HOMOLOGAÇÃO',
            'cor' => 'bg-pink-500',
            'campos' => [],
        ]
    ];

    // Mapeamento de anexos
    protected $mapeamentoAnexos = [
        'atos_sessao' => 'anexo_atos_sessao',
        'proposta' => 'anexo_proposta',
        'proposta_readequada' => 'anexo_proposta_readequada',
        'documento_habilitacao_empresa_vencedora' => 'anexo_habilitacao',
        'recurso_contratacoes_decisao_recursos' => 'anexo_recurso_contratacoes',
        'termo_ajusdicacao' => 'anexo_planilha',
    ];

    public function finalizar(Processo $processo)
    {
        $processo->load('prefeitura.unidades');
        $documentos = $this->documentos;
        return view('Admin.Processos.finalizar', compact('processo', 'documentos'));
    }

    public function storeFinalizacao(Request $request, Processo $processo)
    {
        try {
            $finalizacao = $processo->finalizacao ?? new Finalizacao();
            $finalizacao->processo_id = $processo->id;

            // Processa arquivos
            $this->processarArquivos($request, $finalizacao);

            // Salva outros campos
            $dataToSave = $request->except($this->getExcludedFields());
            foreach ($dataToSave as $field => $value) {
                $finalizacao->{$field} = $value;
            }

            $finalizacao->save();

            return response()->json([
                'success' => true,
                'data' => $finalizacao->toArray()
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao salvar finalizacao do processo', [
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

    public function storeVencedores(Request $request, Processo $processo)
    {
        try {
            $request->validate([
                'vencedores' => 'sometimes|array',
                'vencedores.*.razao_social' => 'required|string|max:255',
                'vencedores.*.cnpj' => 'required|string|max:20',
                'vencedores.*.representante' => 'required|string|max:255',
                'vencedores.*.cpf' => 'required|string|max:14',
                'vencedores.*.lotes' => 'sometimes|array',
                'vencedores.*.lotes.*.lote' => 'nullable|string|max:50',
                'vencedores.*.lotes.*.status' => 'nullable|string|max:100',
                'vencedores.*.lotes.*.item' => 'required|string|max:50',
                'vencedores.*.lotes.*.descricao' => 'required|string',
                'vencedores.*.lotes.*.unidade' => 'required|string|max:20',
                'vencedores.*.lotes.*.marca' => 'nullable|string|max:100',
                'vencedores.*.lotes.*.modelo' => 'nullable|string|max:100',
                'vencedores.*.lotes.*.quantidade' => 'required|numeric|min:0',
                'vencedores.*.lotes.*.vl_unit' => 'required|numeric|min:0',
            ]);

            DB::transaction(function () use ($processo, $request) {
                // Se está enviando vencedores específicos
                if ($request->has('vencedores')) {
                    $vencedoresIds = [];
                    $vencedoresExistentes = $processo->vencedores()->pluck('id')->toArray();

                    foreach ($request->vencedores as $index => $vencedorData) {
                        // Verifica se é um vencedor existente ou novo
                        if (isset($vencedorData['id']) && !empty($vencedorData['id'])) {
                            // Atualizar vencedor existente
                            $vencedor = Vencedor::find($vencedorData['id']);
                            if ($vencedor) {
                                $vencedor->update([
                                    'razao_social' => $vencedorData['razao_social'],
                                    'cnpj' => preg_replace('/\D/', '', $vencedorData['cnpj']),
                                    'representante' => $vencedorData['representante'],
                                    'cpf' => preg_replace('/\D/', '', $vencedorData['cpf']),
                                    'ordem' => $index
                                ]);

                                $vencedoresIds[] = $vencedor->id;

                                // Remover da lista de existentes para não excluir depois
                                $vencedoresExistentes = array_diff($vencedoresExistentes, [$vencedor->id]);
                            }
                        } else {
                            // Criar novo vencedor
                            $vencedor = Vencedor::create([
                                'processo_id' => $processo->id,
                                'razao_social' => $vencedorData['razao_social'],
                                'cnpj' => preg_replace('/\D/', '', $vencedorData['cnpj']),
                                'representante' => $vencedorData['representante'],
                                'cpf' => preg_replace('/\D/', '', $vencedorData['cpf']),
                                'ordem' => $index
                            ]);

                            $vencedoresIds[] = $vencedor->id;
                        }

                        // Processar lotes do vencedor (apenas se o vencedor foi criado/atualizado com sucesso)
                        if (isset($vencedor) && isset($vencedorData['lotes']) && is_array($vencedorData['lotes'])) {
                            // Remover lotes existentes do vencedor
                            Lote::where('vencedor_id', $vencedor->id)->delete();

                            foreach ($vencedorData['lotes'] as $loteIndex => $loteData) {
                                // Validar dados do lote antes de salvar
                                if (!empty($loteData['item']) && !empty($loteData['descricao'])) {
                                    Lote::create([
                                        'vencedor_id' => $vencedor->id,
                                        'lote' => $loteData['lote'] ?? null,
                                        'status' => $loteData['status'] ?? 'HOMOLOGADO',
                                        'item' => $loteData['item'],
                                        'descricao' => $loteData['descricao'],
                                        'unidade' => $loteData['unidade'],
                                        'marca' => $loteData['marca'] ?? null,
                                        'modelo' => $loteData['modelo'] ?? null,
                                        'quantidade' => floatval($loteData['quantidade']),
                                        'vl_unit' => floatval($loteData['vl_unit']),
                                        'vl_total' => floatval($loteData['quantidade']) * floatval($loteData['vl_unit']),
                                        'ordem' => $loteIndex
                                    ]);
                                }
                            }
                        }
                    }

                    // Remover apenas vencedores que não estão mais na lista E não foram atualizados
                    // Isso preserva vencedores existentes que não foram enviados no request
                    if (!empty($vencedoresExistentes)) {
                        Vencedor::whereIn('id', $vencedoresExistentes)->delete();
                    }
                }
                // Se está removendo um vencedor específico
                elseif ($request->has('remover_vencedor')) {
                    $vencedorId = $request->remover_vencedor;
                    Vencedor::where('id', $vencedorId)->delete();
                }
                // Se está adicionando um único vencedor (sem enviar a lista completa)
                elseif ($request->has('vencedor_index') && $request->vencedor_index === '') {
                    // Isso significa que é um novo vencedor sendo adicionado individualmente
                    // Não fazemos nada aqui, pois a lógica acima já cuida disso
                }
            });

            // Recarregar os vencedores atualizados
            $processo->load('vencedores.lotes');

            return response()->json([
                'success' => true,
                'message' => 'Vencedores e lotes salvos com sucesso!',
                'vencedores' => $processo->vencedores
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao salvar vencedores e lotes', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar vencedores e lotes: ' . $e->getMessage()
            ], 500);
        }
    }

    public function importarExcel(Request $request, Processo $processo)
    {
        try {
            Log::info('Importar Excel - Iniciando', [
                'processo_id' => $processo->id,
                'vencedor_index' => $request->vencedor_index
            ]);

            $request->validate([
                'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
                'tipo_contratacao' => 'required|string',
                'vencedor_index' => 'required|integer',
            ]);

            $file = $request->file('excel_file');
            $vencedorIndex = $request->vencedor_index;

            // Buscar o vencedor específico
            $vencedor = $processo->vencedores()->orderBy('ordem')->skip($vencedorIndex)->first();

            if (!$vencedor) {
                throw new \Exception('Vencedor não encontrado para o índice informado.');
            }

            Log::info('Vencedor encontrado para importação', [
                'vencedor_id' => $vencedor->id,
                'razao_social' => $vencedor->razao_social
            ]);

            // Importar dados do Excel
            $import = new LotesImport();
            $dados = Excel::toArray($import, $file);

            if (empty($dados) || empty($dados[0])) {
                throw new \Exception('O arquivo Excel está vazio ou não pôde ser processado.');
            }

            $dadosExcel = $dados[0];
            Log::info('Dados extraídos do Excel', ['quantidade_linhas' => count($dadosExcel)]);

            // Processar dados do Excel
            $lotesProcessados = $this->processarDadosExcel($dadosExcel, $request->tipo_contratacao);

            Log::info('Dados processados', [
                'quantidade' => count($lotesProcessados),
                'vencedor_id' => $vencedor->id
            ]);

            // Salvar lotes no banco de dados
            DB::transaction(function () use ($vencedor, $lotesProcessados) {
                // Remover lotes existentes do vencedor
                Lote::where('vencedor_id', $vencedor->id)->delete();

                foreach ($lotesProcessados as $index => $loteData) {
                    Lote::create([
                        'vencedor_id' => $vencedor->id,
                        'lote' => $loteData['lote'] ?? null,
                        'status' => $loteData['status'] ?? 'HOMOLOGADO',
                        'item' => $loteData['item'],
                        'descricao' => $loteData['descricao'],
                        'unidade' => $loteData['unidade'],
                        'marca' => $loteData['marca'] ?? null,
                        'modelo' => $loteData['modelo'] ?? null,
                        'quantidade' => floatval($loteData['quantidade']),
                        'vl_unit' => floatval($loteData['vl_unit']),
                        'vl_total' => floatval($loteData['vl_total']),
                        'ordem' => $index
                    ]);
                }
            });

            // Recarregar os lotes salvos
            $vencedor->load('lotes');

            Log::info('Lotes salvos com sucesso', [
                'vencedor_id' => $vencedor->id,
                'lotes_salvos' => count($lotesProcessados)
            ]);

            return response()->json([
                'success' => true,
                'message' => '✅ Arquivo processado com sucesso! ' . count($lotesProcessados) . ' itens importados.',
                'lotes' => $lotesProcessados,
                'vencedor' => $vencedor
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao importar Excel', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage(),
                'arquivo' => $e->getFile(),
                'linha' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao importar arquivo: ' . $e->getMessage()
            ], 500);
        }
    }

    private function processarDadosExcel($dados, $tipoContratacao)
    {
        $processados = [];

        // Pular cabeçalho se existir
        $inicio = 0;
        if (!empty($dados) && is_array($dados[0])) {
            $primeiraLinha = $dados[0];
            $isCabecalho = false;
            foreach ($primeiraLinha as $celula) {
                if (is_string($celula) && !is_numeric($celula) && !empty(trim($celula))) {
                    $isCabecalho = true;
                    break;
                }
            }
            if ($isCabecalho) {
                $inicio = 1;
                Log::info('Cabeçalho detectado, pulando primeira linha');
            }
        }

        for ($i = $inicio; $i < count($dados); $i++) {
            $linha = $dados[$i];

            // Pular linhas vazias
            if (empty(array_filter($linha, function($valor) {
                return !is_null($valor) && $valor !== '';
            }))) {
                continue;
            }

            Log::info('Processando linha Excel', ['linha' => $i + 1, 'dados' => $linha]);

            // CORREÇÃO: Mapeamento correto baseado na estrutura do seu Excel
            // Baseado nos logs, o Excel tem esta estrutura:
            // [0] => Razão Social, [1] => CPF/CNPJ, [2] => Lote, [3] => Status, [4] => Item, [5] => Descrição,
            // [6] => Unidade, [7] => Marca, [8] => Modelo, [9] => Quantidade, [10] => Vl. Unit., [11] => Vl. Total

            $dado = [
                'lote' => $this->obterValorColuna($linha, 2, ''),
                'status' => $this->obterValorColuna($linha, 3, 'HOMOLOGADO'), // Coluna 3: Status
                'item' => $this->obterValorColuna($linha, 4, ''), // Coluna 4: Item
                'descricao' => $this->obterValorColuna($linha, 5, ''), // Coluna 5: Descrição
                'unidade' => $this->obterValorColuna($linha, 6, 'UN'), // Coluna 6: Unidade
                'marca' => $this->obterValorColuna($linha, 7, ''), // Coluna 7: Marca
                'modelo' => $this->obterValorColuna($linha, 8, ''), // Coluna 8: Modelo
                'quantidade' => $this->parseFloat($this->obterValorColuna($linha, 9, 0)), // Coluna 9: Quantidade
                'vl_unit' => $this->parseFloat($this->obterValorColuna($linha, 10, 0)), // Coluna 10: Vl. Unit.
            ];

            // // Adicionar lote se for do tipo LOTE
            // if ($tipoContratacao === 'LOTE') {
            //     $dado['lote'] = $this->obterValorColuna($linha, 2, ''); // Coluna 2: Lote
            // }

            // Validar dados obrigatórios
            if (empty($dado['item']) || empty($dado['descricao']) || $dado['quantidade'] <= 0 || $dado['vl_unit'] <= 0) {
                Log::warning('Linha ignorada por dados incompletos', ['linha' => $i + 1, 'dado' => $dado]);
                continue;
            }

            // Calcular total
            $dado['vl_total'] = $dado['quantidade'] * $dado['vl_unit'];

            $processados[] = $dado;
            Log::info('Linha processada com sucesso', [
                'linha' => $i + 1,
                'dado' => $dado,
                'lote_valor' => $dado['lote'] // Log específico para debug do lote
            ]);
        }

        Log::info('Processamento concluído', ['linhas_processadas' => count($processados)]);

        return $processados;
    }

    private function obterValorColuna($linha, $indice, $default = '')
    {
        if (!isset($linha[$indice])) {
            return $default;
        }

        $valor = $linha[$indice];

        // Se for nulo ou string vazia, retorna default
        if (is_null($valor) || $valor === '') {
            return $default;
        }

        // CORREÇÃO: Para campo lote, preservar como string mesmo se for numérico
        if ($indice === 2) { // Coluna do lote
            return (string)$valor;
        }

        // Se for numérico, converte para string para manter consistência
        if (is_numeric($valor)) {
            // Para CNPJ/CPF, preserva como string sem formatação
            if ($indice === 1) { // Coluna CPF/CNPJ
                return (string)$valor;
            }
            // Para outros números, converte normalmente
            return $valor;
        }

        // Remove espaços extras de strings
        return trim((string)$valor);
    }

    /**
     * Converter para float - VERSÃO MELHORADA
     */
    private function parseFloat($value)
    {
        if (is_null($value) || $value === '') {
            return 0.0;
        }

        // Se já for float, retorna diretamente
        if (is_float($value)) {
            return $value;
        }

        // Se for inteiro, converte para float
        if (is_int($value)) {
            return floatval($value);
        }

        $stringValue = (string)$value;

        // Remove caracteres não numéricos, exceto ponto, vírgula e sinal negativo
        $cleanValue = preg_replace('/[^\d,\-\.]/', '', $stringValue);

        // Converte vírgula para ponto (formato brasileiro)
        $cleanValue = str_replace(',', '.', str_replace('.', '', $cleanValue));

        $result = floatval($cleanValue);

        // Log para debug
        Log::debug('Conversão de valor', [
            'original' => $value,
            'tipo_original' => gettype($value),
            'limpo' => $cleanValue,
            'resultado' => $result
        ]);

        return $result;
    }


    /**
     * Buscar vencedores do processo
     */
    public function getVencedores(Processo $processo)
    {
        try {
            $vencedores = $processo->vencedores()
                ->with('lotes')
                ->orderBy('ordem')
                ->get();

            return response()->json([
                'success' => true,
                'vencedores' => $vencedores
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar vencedores: ' . $e->getMessage()
            ], 500);
        }
    }
    // =========================================================
    // MÉTODOS DE GERAÇÃO E DOWNLOAD DE PDF
    // =========================================================

    public function gerarPdf(Request $request, Processo $processo)
    {
        try {
            Log::info('Iniciando geração de PDF - Finalização', [
                'processo_id' => $processo->id,
                'documento' => $request->query('documento'),
                'request_data' => $request->all()
            ]);

            $validatedData = $this->validarRequisicaoPdf($request, $processo);
            $data = $this->prepararDadosPdf($processo, $validatedData);
            $view = $this->determinarViewPdf($validatedData['documento']);

            Log::info('View selecionada para PDF', ['view' => $view]);

            $pdf = Pdf::loadView($view, $data)->setPaper('a4', 'portrait');

            $caminhoCompleto = $this->salvarDocumento($processo, $pdf, $validatedData);

            $this->processarAnexos($processo, $validatedData['documento'], $caminhoCompleto);

            Log::info('PDF gerado com sucesso - Finalização', [
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
            Log::error('Erro ao gerar PDF - Finalização', [
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

        $nomeArquivo = "processo_finalizacao_" . str_replace(['/', '\\'], '_', $processo->numero_processo) . "_todos_documentos_" . now()->format('Ymd_His') . '.pdf';
        $caminhoArquivo = public_path('uploads/documentos_finalizacao/' . $nomeArquivo);

        // Mesclar PDFs usando Ghostscript
        $sucesso = $this->mesclarPdfsComGhostscript($arquivos, $caminhoArquivo);

        if ($sucesso) {
            // Adicionar carimbo ao PDF mesclado
            $caminhoCarimbado = $this->adicionarCarimboAoPdfComGhostscript($caminhoArquivo, $processo);

            if ($caminhoCarimbado) {
                return response()->download($caminhoCarimbado)->deleteFileAfterSend(true);
            } else {
                // Se não conseguiu carimbar, retorna o arquivo sem carimbo
                Log::warning('PDF mesclado com Ghostscript sem carimbo - Finalização', ['processo_id' => $processo->id]);
                return response()->download($caminhoArquivo)->deleteFileAfterSend(true);
            }
        } else {
            throw new \Exception('Erro ao mesclar documentos com Ghostscript');
        }
    }

    // =========================================================
    // MÉTODOS PRIVADOS - ARMAZENAMENTO DE DADOS
    // =========================================================

    private function processarArquivos(Request $request, Finalizacao $finalizacao): void
    {
        $arquivos = [
            'anexo_atos_sessao' => 'salvarAnexo',
            'anexo_proposta' => 'salvarAnexo',
            'anexo_proposta_readequada' => 'salvarAnexo',
            'anexo_habilitacao' => 'salvarAnexo',
            'anexo_recurso_contratacoes' => 'salvarAnexo',
            'anexo_planilha' => 'salvarAnexo'
        ];

        foreach ($arquivos as $campo => $metodo) {
            if ($request->hasFile($campo)) {
                $this->{$metodo}($request->file($campo), $finalizacao, $campo);
            }
        }
    }

    private function salvarAnexo($file, Finalizacao $finalizacao, string $campo): void
    {
        $filename = $campo . '_' . time() . '.' . $file->getClientOriginalExtension();
        $destinationPath = public_path('uploads/anexos_finalizacao');

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }

        $file->move($destinationPath, $filename);
        $finalizacao->{$campo} = 'uploads/anexos_finalizacao/' . $filename;

        Log::info("Arquivo salvo - Finalização: {$finalizacao->{$campo}}");
    }

    private function getExcludedFields(): array
    {
        return [
            '_token',
            'processo_id',
            'anexo_atos_sessao',
            'anexo_proposta',
            'anexo_proposta_readequada',
            'anexo_habilitacao',
            'anexo_recurso_contratacoes',
            'anexo_planilha'
        ];
    }

    // =========================================================
    // MÉTODOS PRIVADOS - GERAÇÃO DE PDF
    // =========================================================

    private function validarRequisicaoPdf(Request $request, Processo $processo): array
    {
        $documento = $request->query('documento', 'atos_sessao');

        // Data não é mais obrigatória - usa data atual se não for fornecida
        $dataSelecionada = $request->query('data', now()->format('Y-m-d'));

        // Assinantes não são mais obrigatórios - processa se existirem
        $assinantes = $this->processarAssinantes($request);

        return [
            'documento' => $documento,
            'dataSelecionada' => $dataSelecionada,
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
            Log::warning("Erro ao decodificar JSON de assinantes - Finalização: " . json_last_error_msg());
            return [];
        }

        return $assinantes;
    }

    private function prepararDadosPdf(Processo $processo, array $validatedData): array
    {
        $processo->load(['finalizacao', 'prefeitura', 'vencedores.lotes']);

        return [
            'processo' => $processo,
            'prefeitura' => $processo->prefeitura,
            'finalizacao' => $processo->finalizacao,
            'vencedores' => $processo->vencedores, // ADICIONE ESTA LINHA
            'dataGeracao' => now()->format('d/m/Y H:i:s'),
            'dataSelecionada' => $validatedData['dataSelecionada'],
            'assinantes' => $validatedData['assinantes'],
        ];
    }

    private function determinarViewPdf(string $documento): string
    {
        $view = "Admin.Processos.pdf-finalizacao.{$documento}";

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

        // Obter anexos
        $anexos = $this->obterAnexos($processo, $documento);

        if (!empty($anexos)) {
            Log::info("📎 Anexos encontrados para documento: {$documento}", [
                'quantidade' => count($anexos),
                'anexos' => $anexos
            ]);

            $resultado = $this->juntarPdfsComGhostscript($caminhoPrincipal, $anexos);

            if ($resultado && file_exists($resultado)) {
                Log::info("✅ Anexos processados com SUCESSO - Finalização", [
                    'documento' => $documento,
                    'arquivo_final' => $resultado,
                    'tamanho_final' => filesize($resultado),
                    'anexos_mesclados' => count($anexos)
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

    private function obterAnexos(Processo $processo, string $documento): array
    {
        $anexos = [];
        $campoAnexo = $this->mapeamentoAnexos[$documento] ?? null;

        if (!$campoAnexo) {
            Log::info("Nenhum mapeamento de anexo encontrado para documento: {$documento}");
            return $anexos;
        }

        // Verifica se o campo existe e tem valor
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

    // =========================================================
    // MÉTODOS PRIVADOS - MESCLAGEM E CARIMBAGEM (Ghostscript)
    // =========================================================

    private function juntarPdfsComGhostscript(string $pdfBasePath, array $anexoPaths): ?string
    {
        try {
            Log::info("INICIANDO JUNÇÃO DE PDFs - Finalização", [
                'pdf_base' => $pdfBasePath,
                'anexos_recebidos' => $anexoPaths,
                'base_existe' => file_exists($pdfBasePath),
                'base_tamanho' => file_exists($pdfBasePath) ? filesize($pdfBasePath) : 0
            ]);

            // Verificação CRÍTICA do arquivo base
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

            // Filtrar apenas anexos válidos
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

            // Se não há anexos válidos, retornar o arquivo base original
            if (empty($anexosValidos)) {
                Log::info("ℹ️ Nenhum anexo válido para mesclar - retornando arquivo base original", [
                    'pdf_base' => $pdfBasePath
                ]);
                return $pdfBasePath;
            }

            // Criar arquivo temporário para o resultado
            $tempOutput = tempnam(sys_get_temp_dir(), 'merged_pdf_finalizacao_') . '.pdf';

            // Ordem: base + anexos
            $todosArquivos = array_merge([$pdfBasePath], $anexosValidos);

            Log::info("🔄 Iniciando mesclagem com Ghostscript - Finalização", [
                'total_arquivos' => count($todosArquivos),
                'arquivos' => $todosArquivos,
                'arquivo_saida_temp' => $tempOutput
            ]);

            // Mesclar usando Ghostscript
            $sucesso = $this->mesclarPdfsComGhostscript($todosArquivos, $tempOutput);

            if ($sucesso && file_exists($tempOutput) && filesize($tempOutput) > 0) {
                $tamanhoTemp = filesize($tempOutput);

                Log::info("✅ Arquivo temporário gerado com sucesso - Finalização", [
                    'caminho_temp' => $tempOutput,
                    'tamanho_temp' => $tamanhoTemp
                ]);

                // Substituir o arquivo base pelo resultado mesclado
                if (copy($tempOutput, $pdfBasePath)) {
                    $tamanhoFinal = filesize($pdfBasePath);
                    Log::info("🎉 PDFs mesclados com SUCESSO - Finalização", [
                        'arquivo_final' => $pdfBasePath,
                        'tamanho_final' => $tamanhoFinal,
                        'anexos_mesclados' => count($anexosValidos)
                    ]);

                    // Limpar arquivo temporário
                    unlink($tempOutput);
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

            // Limpeza em caso de erro
            if (file_exists($tempOutput)) {
                unlink($tempOutput);
            }

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
        $paginasTemp = [];

        try {
            $pageCount = $this->contarPaginasPdf($caminhoPdf);

            if ($pageCount === 0) {
                Log::error('PDF vazio ou inválido - Finalização', ['caminho' => $caminhoPdf]);
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

                $tempPath = sys_get_temp_dir() . "/pagina_finalizacao_{$pagina}_" . uniqid() . '.pdf';
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
                Log::error('Falha ao mesclar páginas carimbadas - Finalização');
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Erro ao adicionar carimbo ao PDF com Ghostscript - Finalização', [
                'caminho' => $caminhoPdf,
                'erro' => $e->getMessage()
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

    // =========================================================
    // MÉTODOS AUXILIARES
    // =========================================================

    private function getOrdemDocumentos(): array
    {
        return [
            'atos_sessao',
            'proposta',
            'proposta_readequada',
            'documento_habilitacao_empresa_vencedora',
            'recurso_contratacoes_decisao_recursos',
            'termo_ajusdicacao',
            'parecer_controle_interno',
            'termo_homologacao'
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

        $codigoAutenticacao = $processo->prefeitura->id . now()->format('HisdmY');
        $textoCarimbo = "Processo numerado por: {$processo->responsavel_numeracao} " .
            "Cargo: {$processo->unidade_numeracao} " .
            "Portaria nº {$processo->portaria_numeracao} " .
            "Pág. {$paginaAtual} / {$pageCountTotal} - " .
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
}
