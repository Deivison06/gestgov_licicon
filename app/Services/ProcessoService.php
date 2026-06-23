<?php

namespace App\Services;

use App\Models\Documento;
use App\Models\Lote;
use App\Models\Processo;
use App\Models\ProcessoDetalhe;
use App\Models\Reserva;
use App\Models\Vencedor;
use Illuminate\Support\Facades\DB;
use Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProcessoService
{
    protected array $excludedFields = [
        '_token',
        'processo_id',
        'itens_e_seus_quantitativos_xml',
        'descricao_e_quantitativos_itens_xml',
        'painel_preco_tce',
        'anexo_pdf_analise_mercado',
        'anexar_minuta',
        'anexo_pdf_publicacoes',
        'itens_especificaca_quantitativos_xml',
        'anexo_pdf_minuta_contrato',
        'projeto_basico_pdf',
        'empresa_vencedora_pdf',  // ← DEVE permanecer aqui para o loop não sobrescrever com UploadedFile
        // Campos de Contrato
        'numero_contrato',
        'data_assinatura_contrato',
        'numero_extrato',
        'comarca',
        'fonte_recurso',
        'subcontratacao'
    ];

    protected array $arquivosConfig = [
        'itens_e_seus_quantitativos_xml' => 'processarArquivoItens',
        'itens_especificaca_quantitativos_xml' => 'processarArquivoEspecificacao',
        'descricao_e_quantitativos_itens_xml' => 'processarArquivoItens',
        'painel_preco_tce' => 'processarPainelPrecos',
        'anexo_pdf_analise_mercado' => 'salvarAnexo',
        'anexar_minuta' => 'salvarAnexo',
        'anexo_pdf_publicacoes' => 'salvarAnexo',
        'anexo_pdf_minuta_contrato' => 'salvarAnexo',
        'projeto_basico_pdf' => 'salvarAnexo',
        'empresa_vencedora_pdf' => 'salvarAnexo'
    ];

    public function create(array $data): Processo
    {
        return Processo::create($data);
    }

    public function update(Processo $processo, array $data): Processo
    {
        $processo->update($data);
        return $processo;
    }

    public function delete(Processo $processo): void
    {
        $processo->delete();
    }

    public function gerarProximoNumeroProcesso(int $prefeituraId): string
    {
        $anoAtual = now()->year;

        // Busca o último processo da prefeitura no ano atual que siga o padrão NNN/YYYY
        $ultimoProcesso = Processo::where('prefeitura_id', $prefeituraId)
            ->where('numero_processo', 'like', "%/{$anoAtual}")
            ->orderByRaw('CAST(SUBSTRING_INDEX(numero_processo, "/", 1) AS UNSIGNED) DESC')
            ->first();

        $proximoNumero = 1;
        if ($ultimoProcesso) {
            $partes = explode('/', $ultimoProcesso->numero_processo);
            if (count($partes) === 2 && is_numeric($partes[0])) {
                $proximoNumero = (int) $partes[0] + 1;
            }
        }

        return str_pad($proximoNumero, 3, '0', STR_PAD_LEFT) . '/' . $anoAtual;
    }

    public function salvarDetalhe(Processo $processo, array $data): ProcessoDetalhe
    {
        $detalhe = $processo->detalhe ?? new ProcessoDetalhe();
        $detalhe->processo_id = $processo->id;

        $this->processarArquivos($data, $detalhe);
        
        // Tratar dados do contrato caso seja Inexigibilidade
        if ($processo->modalidade === \App\Enums\ModalidadeEnum::INEXIGIBILIDADE) {
            $dadosContrato = [];
            $camposContrato = [
                'numero_contrato',
                'data_assinatura_contrato',
                'numero_extrato',
                'comarca',
                'fonte_recurso',
                'subcontratacao'
            ];

            foreach ($camposContrato as $campo) {
                if (isset($data[$campo])) {
                    $dadosContrato[$campo] = $data[$campo];
                }
            }

            if (!empty($dadosContrato)) {
                $dadosContrato['processo_id'] = $processo->id;
                \App\Models\Contrato::updateOrCreate(
                    ['processo_id' => $processo->id],
                    $dadosContrato
                );
            }
        }

        foreach ($data as $field => $value) {
            // Se o campo for uma data de documento (ex: data_doc_capa)
            if (strpos($field, 'data_doc_') === 0) {
                $tipoDocumento = substr($field, 9);
                Documento::updateOrCreate(
                    ['processo_id' => $processo->id, 'tipo_documento' => $tipoDocumento],
                    ['data_selecionada' => $value]
                );
                continue;
            }

            if (!in_array($field, $this->excludedFields)) {
                $detalhe->{$field} = $value;
            }
        }

        $detalhe->save();

        if (!empty($data['data_hora'])) {
            $this->sincronizarDataAberturaPlanejamento($processo, $data['data_hora']);
        }

        return $detalhe;
    }

    private function sincronizarDataAberturaPlanejamento(Processo $processo, string $dataHora): void
    {
        $dataAbertura = \Carbon\Carbon::parse($dataHora);
        $updates = ['planejamento_data_abertura' => $dataAbertura];

        if ($processo->planejamento_status === 'em_elaboracao') {
            $updates['planejamento_status'] = 'aguardando_sessao';
        }

        $processo->update($updates);
    }

    private function processarArquivos(array $data, ProcessoDetalhe $detalhe): void
    {
        foreach ($this->arquivosConfig as $campo => $metodo) {
            if (isset($data[$campo]) && $data[$campo] instanceof \Illuminate\Http\UploadedFile) {
                $this->{$metodo}($data[$campo], $detalhe, $campo);
            }
        }
    }

    private function processarArquivoItens($file, ProcessoDetalhe $detalhe, string $campo): void
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $itens = [];
        $linhasEmBranco = [];
        $linhasInvalidas = [];

        foreach ($rows as $index => $row) {

            // Ignorar cabeçalho
            if ($index === 0) {
                continue;
            }

            /**
             * 1️⃣ Verificar se a linha está completamente vazia
             */
            $linhaVazia = true;
            foreach ($row as $celula) {
                if (!empty(trim((string) ($celula ?? '')))) {
                    $linhaVazia = false;
                    break;
                }
            }

            if ($linhaVazia) {
                $linhasEmBranco[] = $index + 1;
                continue;
            }

            /**
             * 2️⃣ Normalizar campos
             */
            $numero     = trim((string) ($row[0] ?? ''));
            $descricao  = trim((string) ($row[1] ?? ''));
            $unidade    = trim((string) ($row[2] ?? ''));
            $quantidade = trim((string) ($row[3] ?? ''));

            /**
             * 3️⃣ Validar linha mínima
             * Regra de negócio:
             * - descrição obrigatória
             * - quantidade numérica e > 0
             */
            if (
                $descricao === '' ||
                !is_numeric(str_replace(',', '.', $quantidade)) ||
                (float) str_replace(',', '.', $quantidade) <= 0
            ) {
                $linhasInvalidas[] = $index + 1;
                continue;
            }

            /**
             * 4️⃣ Salvar item válido
             */
            $itens[] = [
                'numero'     => $numero !== '' ? $numero : null,
                'descricao'  => $descricao,
                'und'        => $unidade !== '' ? $unidade : null,
                'quantidade' => (float) str_replace(',', '.', $quantidade),
            ];
        }

        /**
         * 5️⃣ Persistir apenas linhas válidas
         */
        $detalhe->{$campo} = json_encode($itens, JSON_UNESCAPED_UNICODE);

        /**
         * 6️⃣ Log estruturado (auditoria)
         */
        if (!empty($linhasEmBranco) || !empty($linhasInvalidas)) {
            Log::warning('Processamento de itens com linhas ignoradas', [
                'campo'             => $campo,
                'linhas_em_branco'  => $linhasEmBranco,
                'linhas_invalidas'  => $linhasInvalidas,
                'total_linhas'      => count($rows),
                'linhas_salvas'     => count($itens),
                'processo_id'       => $detalhe->processo_id ?? null,
            ]);
        }
    }

    private function processarArquivoEspecificacao($file, ProcessoDetalhe $detalhe, string $campo): void
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $itens = [];
        $linhasEmBranco = [];
        $loteAtual = null;

        foreach ($rows as $index => $row) {
            // Pular cabeçalho da planilha
            if ($index === 0) continue;

            // Verificar se a linha está em branco
            $linhaVazia = true;
            foreach ($row as $celula) {
                if (!empty(trim($celula ?? ''))) {
                    $linhaVazia = false;
                    break;
                }
            }

            // Se a linha estiver vazia, registrar e pular
            if ($linhaVazia) {
                $linhasEmBranco[] = $index + 1;
                continue;
            }

            // Identificar se a linha é um cabeçalho de Lote
            // Padrão: Primeira coluna contém a palavra "Lote" e colunas de quantidade/valor estão vazias
            $col0 = trim((string)($row[0] ?? ''));
            $isLoteHeader = (stripos($col0, 'Lote') === 0) && 
                            empty(trim((string)($row[3] ?? ''))) && 
                            empty(trim((string)($row[4] ?? '')));

            if ($isLoteHeader) {
                $loteAtual = $col0;
                continue; // Não adiciona o cabeçalho como um item
            }

            $itens[] = [
                'lote'              => $loteAtual,
                'item'              => $row[0] ?? null,
                'especificacoes'    => $row[1] ?? null,
                'unidade'           => $row[2] ?? null,
                'quantidade'        => $row[3] ?? null,
                'valor_unitario'    => $this->normalizarValor($row[4] ?? null),
                'valor_total'       => $this->normalizarValor($row[5] ?? null),
            ];
        }

        // Salvar dados
        $detalhe->{$campo} = json_encode($itens, JSON_UNESCAPED_UNICODE);

        // Logar linhas em branco encontradas
        if (!empty($linhasEmBranco)) {
            Log::warning('Linhas em branco identificadas no arquivo de especificação', [
                'campo' => $campo,
                'linhas' => $linhasEmBranco,
                'total_linhas' => count($rows),
                'processo_id' => $detalhe->processo_id ?? null
            ]);
        }
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

        $valor = trim((string)$valor);

        if (is_numeric($valor)) {
            return number_format((float)$valor, 2, ',', '.');
        }

        if (preg_match('/^\d{1,3}(,\d{3})*\.\d{2}$/', $valor)) {
            $valor = str_replace(',', '', $valor);
            return number_format((float)$valor, 2, ',', '.');
        }

        if (preg_match('/^\d{1,3}(\.\d{3})*,\d{2}$/', $valor)) {
            return $valor;
        }

        $valor = str_replace(['.', ','], ['#', '.'], $valor);
        $valor = str_replace('#', ',', $valor);

        return $valor;
    }

    private function salvarAnexo($file, ProcessoDetalhe $detalhe, string $campo): void
    {
        // Deletar arquivo antigo se existir
        if (!empty($detalhe->{$campo}) && file_exists(public_path($detalhe->{$campo}))) {
            unlink(public_path($detalhe->{$campo}));
        }

        $filename = $campo . '_' . time() . '.' . $file->getClientOriginalExtension();
        $destinationPath = public_path('uploads/anexos');

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }

        $file->move($destinationPath, $filename);
        
        $caminho = 'uploads/anexos/' . $filename;
        $detalhe->{$campo} = $caminho;

        Log::info('Anexo salvo', [
            'campo'   => $campo,
            'caminho' => $caminho,
            'existe'  => file_exists(public_path($caminho)),
        ]);
    }

    /**
     * Duplicar processo completo com todos os relacionamentos
     */
    public function duplicarProcesso(Processo $processoOriginal, array $novosDados): Processo
    {
        DB::beginTransaction();

        try {
            // Duplicar processo principal
            $novoProcesso = $processoOriginal->replicate();
            $novoProcesso->numero_processo = $novosDados['numero_processo'];
            $novoProcesso->numero_procedimento = $novosDados['numero_procedimento'];
            $novoProcesso->status = $novosDados['status'] ?? 'EM_ANDAMENTO';
            $novoProcesso->user_id = $novosDados['user_id'] ?? auth()->id();
            $novoProcesso->created_at = now();
            $novoProcesso->updated_at = now();
            $novoProcesso->save();

            // Duplicar detalhes
            if ($processoOriginal->detalhe) {
                $novoDetalhe = $processoOriginal->detalhe->replicate();
                $novoDetalhe->processo_id = $novoProcesso->id;
                $novoDetalhe->created_at = now();
                $novoDetalhe->updated_at = now();
                $novoDetalhe->save();
            }

            // Duplicar documentos
            $this->duplicarDocumentos($processoOriginal, $novoProcesso);

            // Duplicar vencedores
            $this->duplicarVencedores($processoOriginal, $novoProcesso);

            DB::commit();

            return $novoProcesso;

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erro ao duplicar processo', [
                'processo_original_id' => $processoOriginal->id,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function duplicarDocumentos(Processo $processoOriginal, Processo $novoProcesso): void
    {
        foreach ($processoOriginal->documentos as $documento) {
            try {
                $novoDocumento = $documento->replicate();
                $novoDocumento->processo_id = $novoProcesso->id;
                $novoDocumento->gerado_em = now();
                $novoDocumento->created_at = now();
                $novoDocumento->updated_at = now();

                // Copiar arquivo físico se existir
                if (file_exists(public_path($documento->caminho))) {
                    $extensao = pathinfo($documento->caminho, PATHINFO_EXTENSION);

                    // Gerar novo nome de arquivo com novo número de processo
                    $numeroOriginalLimpo = str_replace(['/', '\\'], '_', $processoOriginal->numero_processo);
                    $numeroNovoLimpo = str_replace(['/', '\\'], '_', $novoProcesso->numero_processo);

                    $novoCaminho = str_replace(
                        $numeroOriginalLimpo,
                        $numeroNovoLimpo,
                        $documento->caminho
                    );

                    // Verificar se já existe arquivo com esse nome
                    if (file_exists(public_path($novoCaminho))) {
                        $novoCaminho = preg_replace(
                            '/\.' . $extensao . '$/',
                            '_' . time() . '.' . $extensao,
                            $novoCaminho
                        );
                    }

                    $diretorioNovo = dirname(public_path($novoCaminho));
                    if (!file_exists($diretorioNovo)) {
                        mkdir($diretorioNovo, 0777, true);
                    }

                    // Copiar arquivo
                    copy(public_path($documento->caminho), public_path($novoCaminho));
                    $novoDocumento->caminho = $novoCaminho;
                }

                $novoDocumento->save();

            } catch (\Exception $e) {
                \Log::warning('Erro ao duplicar documento', [
                    'documento_id' => $documento->id,
                    'erro' => $e->getMessage()
                ]);
                continue;
            }
        }
    }

    private function duplicarVencedores(Processo $processoOriginal, Processo $novoProcesso): void
    {
        foreach ($processoOriginal->vencedores as $vencedor) {
            try {
                $novoVencedor = $vencedor->replicate();
                $novoVencedor->processo_id = $novoProcesso->id;
                $novoVencedor->created_at = now();
                $novoVencedor->updated_at = now();
                $novoVencedor->save();

                // Duplicar lotes do vencedor
                foreach ($vencedor->lotes as $lote) {
                    $novoLote = $lote->replicate();
                    $novoLote->vencedor_id = $novoVencedor->id;
                    $novoLote->save();
                }

            } catch (\Exception $e) {
                \Log::warning('Erro ao duplicar vencedor', [
                    'vencedor_id' => $vencedor->id,
                    'erro' => $e->getMessage()
                ]);
                continue;
            }
        }
    }
}
