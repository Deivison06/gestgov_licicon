<?php

namespace App\Services;

use App\Models\Processo;
use App\Models\Vencedor;
use App\Models\Lote;
use App\Imports\LotesImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class FinalizacaoVencedorService
{
    public function salvarVencedores(Processo $processo, array $data): array
    {
        if (isset($data['vencedor_data'])) {
            $this->processarVencedorIndividual($processo, $data);
        } elseif (isset($data['remover_vencedor'])) {
            $this->removerVencedor($data['remover_vencedor']);
        }

        return ['success' => true];
    }

    public function importarExcel(Processo $processo, array $data): array
    {
        $file = $data['excel_file'];
        $vencedorIndex = $data['vencedor_index'];
        $tipoContratacao = $data['tipo_contratacao'];

        Log::info('Importar Excel - Iniciando', [
            'processo_id' => $processo->id,
            'vencedor_index' => $vencedorIndex
        ]);

        $vencedor = $processo->vencedores()->orderBy('ordem')->skip($vencedorIndex)->first();

        if (!$vencedor) {
            throw new \Exception('Vencedor não encontrado para o índice informado.');
        }

        $import = new LotesImport();
        $dados = Excel::toArray($import, $file);

        if (empty($dados) || empty($dados[0])) {
            throw new \Exception('O arquivo Excel está vazio ou não pôde ser processado.');
        }

        $dadosExcel = $dados[0];
        $lotesProcessados = $this->processarDadosExcel($dadosExcel, $tipoContratacao);

        DB::transaction(function () use ($vencedor, $lotesProcessados) {
            Lote::where('vencedor_id', $vencedor->id)->delete();

            foreach ($lotesProcessados as $index => $loteData) {
                Lote::create([
                    'vencedor_id' => $vencedor->id,
                    'lote' => $loteData['lote'] ?? null,
                    'lote_nome' => $loteData['lote_nome'] ?? null,
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

        $vencedor->load('lotes');

        return [
            'vencedor' => $vencedor,
            'lotes' => $lotesProcessados
        ];
    }

    public function getVencedores(Processo $processo)
    {
        return $processo->vencedores()
            ->with('lotes')
            ->orderBy('ordem')
            ->get();
    }

    private function processarVencedorIndividual(Processo $processo, array $data): void
    {
        $vencedorData = $data['vencedor_data'];
        $operacao = $data['operacao'] ?? 'adicionar';

        $this->validarVencedor($vencedorData);

        DB::transaction(function () use ($processo, $vencedorData, $operacao) {
            if ($operacao === 'editar' && !empty($vencedorData['id'])) {
                $this->atualizarVencedor($vencedorData);
            } else {
                $this->criarVencedor($processo, $vencedorData);
            }
        });
    }

    private function validarVencedor(array $vencedorData): void
    {
        $validator = \Illuminate\Support\Facades\Validator::make($vencedorData, [
            'razao_social' => 'required|string|max:255',
            'cnpj' => 'required|string|max:20',
            'representante' => 'required|string|max:255',
            'cpf' => 'required|string|max:14',
            'endereco' => 'required|string|max:255',
            'lotes' => 'sometimes|array',
            'lotes.*.lote' => 'nullable|string|max:50',
            'lotes.*.status' => 'nullable|string|max:100',
            'lotes.*.item' => 'required|string|max:50',
            'lotes.*.descricao' => 'required|string',
            'lotes.*.unidade' => 'required|string|max:20',
            'lotes.*.marca' => 'nullable|string|max:100',
            'lotes.*.modelo' => 'nullable|string|max:100',
            'lotes.*.quantidade' => 'required|numeric|min:0',
            'lotes.*.vl_unit' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }
    }

    private function atualizarVencedor(array $vencedorData): void
    {
        $vencedor = Vencedor::find($vencedorData['id']);
        if (!$vencedor) {
            throw new \Exception('Vencedor não encontrado para atualização.');
        }

        $vencedor->update([
            'razao_social' => $vencedorData['razao_social'],
            'cnpj' => preg_replace('/\D/', '', $vencedorData['cnpj']),
            'representante' => $vencedorData['representante'],
            'cpf' => preg_replace('/\D/', '', $vencedorData['cpf']),
            'endereco' => $vencedorData['endereco'],
        ]);

        $this->processarLotesVencedor($vencedor, $vencedorData['lotes'] ?? []);
    }

    private function criarVencedor(Processo $processo, array $vencedorData): void
    {
        $vencedor = Vencedor::create([
            'processo_id' => $processo->id,
            'razao_social' => $vencedorData['razao_social'],
            'cnpj' => preg_replace('/\D/', '', $vencedorData['cnpj']),
            'representante' => $vencedorData['representante'],
            'cpf' => preg_replace('/\D/', '', $vencedorData['cpf']),
            'endereco' => $vencedorData['endereco'],
            'ordem' => Vencedor::where('processo_id', $processo->id)->count()
        ]);

        $this->processarLotesVencedor($vencedor, $vencedorData['lotes'] ?? []);
    }

    private function processarLotesVencedor(Vencedor $vencedor, array $lotesData): void
    {
        Lote::where('vencedor_id', $vencedor->id)->delete();

        foreach ($lotesData as $loteIndex => $loteData) {
            if (!empty($loteData['item']) && !empty($loteData['descricao'])) {
                Lote::create([
                    'vencedor_id' => $vencedor->id,
                    'lote' => $loteData['lote'] ?? null,
                    'lote_nome' => $loteData['lote_nome'] ?? null,
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

    private function removerVencedor(int $vencedorId): void
    {
        Vencedor::where('id', $vencedorId)->delete();
    }

    private function processarDadosExcel($dados, $tipoContratacao): array
    {
        $processados = [];
        $inicio = $this->detectarCabecalho($dados);
        $loteNomeAtual = null;

        for ($i = $inicio; $i < count($dados); $i++) {
            $linha = $dados[$i];

            if (empty(array_filter($linha, fn($valor) => !is_null($valor) && $valor !== ''))) {
                continue;
            }

            // Verificar se é uma linha de título de lote (ex: LOTE 1 - ADMINSTRAÇÃO)
            $colunaA = $this->obterValorColuna($linha, 0, '');
            if (is_string($colunaA) && str_starts_with(strtoupper($colunaA), 'LOTE ')) {
                // Tenta extrair o nome após o hífen, limitando a 2 partes para evitar truncar se o nome tiver hífens
                $partes = explode('-', $colunaA, 2);
                if (count($partes) > 1) {
                    $loteNomeAtual = trim($partes[1]);
                }
                continue; // Pula a linha de título
            }

            $dado = [
                'lote' => $this->obterValorColuna($linha, 2, ''),
                'lote_nome' => $loteNomeAtual,
                'status' => $this->obterValorColuna($linha, 3, 'HOMOLOGADO'),
                'item' => $this->obterValorColuna($linha, 4, ''),
                'descricao' => $this->obterValorColuna($linha, 5, ''),
                'unidade' => $this->obterValorColuna($linha, 6, 'UN'),
                'marca' => $this->obterValorColuna($linha, 7, ''),
                'modelo' => $this->obterValorColuna($linha, 8, ''),
                'quantidade' => $this->parseFloat($this->obterValorColuna($linha, 9, 0)),
                'vl_unit' => $this->parseFloat($this->obterValorColuna($linha, 10, 0)),
            ];

            if (empty($dado['item']) || empty($dado['descricao']) || $dado['quantidade'] <= 0 || $dado['vl_unit'] <= 0) {
                Log::warning('Linha ignorada por dados incompletos', ['linha' => $i + 1, 'dado' => $dado]);
                continue;
            }

            $dado['vl_total'] = $dado['quantidade'] * $dado['vl_unit'];
            $processados[] = $dado;
        }

        return $processados;
    }

    private function detectarCabecalho(array $dados): int
    {
        if (empty($dados) || !is_array($dados[0])) {
            return 0;
        }

        $primeiraLinha = $dados[0];
        foreach ($primeiraLinha as $celula) {
            if (is_string($celula) && !is_numeric($celula) && !empty(trim($celula))) {
                Log::info('Cabeçalho detectado, pulando primeira linha');
                return 1;
            }
        }

        return 0;
    }

    private function obterValorColuna($linha, $indice, $default = '')
    {
        if (!isset($linha[$indice])) {
            return $default;
        }

        $valor = $linha[$indice];

        if (is_null($valor) || $valor === '') {
            return $default;
        }

        if ($indice === 2) {
            return (string)$valor;
        }

        if (is_numeric($valor)) {
            if ($indice === 1) {
                return (string)$valor;
            }
            return $valor;
        }

        return trim((string)$valor);
    }

    private function parseFloat($value)
    {
        if (is_null($value) || $value === '') {
            return 0.0;
        }

        if (is_float($value)) {
            return $value;
        }

        if (is_int($value)) {
            return floatval($value);
        }

        $stringValue = (string)$value;
        $cleanValue = preg_replace('/[^\d,\-\.]/', '', $stringValue);
        $cleanValue = str_replace(',', '.', str_replace('.', '', $cleanValue));

        return floatval($cleanValue);
    }
}