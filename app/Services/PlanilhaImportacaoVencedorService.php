<?php

namespace App\Services;

use App\Models\Processo;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Gera a planilha, já pré-preenchida, no formato que
 * FinalizacaoVencedorService::processarDadosExcel() espera para importar os
 * itens de um vencedor na Finalização (título "LOTE N - Nome" por lote,
 * status/item/descrição/unidade/quantidade prontos, só a coluna "Valor
 * Unitário Homologado" fica em branco para o admin editar).
 *
 * Vive na Finalização (não no ETP) de propósito: os nomes de lote usados aqui
 * são os mesmos "lotes efetivos" (já com o sufixo de Ampla Concorrência/Cota
 * Reservada quando aplicável) produzidos por CotaReservadaService — a mesma
 * fonte usada no TR/Edital/BNC. Isso garante que o `lote_nome` que acaba
 * gravado na tabela `lotes` (pós-homologação) bate automaticamente com o
 * nome publicado, sem depender de alguém digitar/copiar certo — fechando o
 * ponto de erro humano que motivou mover isso pra cá.
 */
class PlanilhaImportacaoVencedorService
{
    public function __construct(
        private readonly CotaReservadaService $cotaReservadaService
    ) {
    }

    public function podeExportar(Processo $processo): bool
    {
        return $processo->etp !== null;
    }

    /**
     * Lista os lotes efetivos do ETP (já com a divisão de Cota Reservada
     * aplicada, se houver) para alimentar o <select> do modal. Só faz
     * sentido quando o ETP é organizado por lote — em modo item não há o
     * que escolher, a planilha sai com todos os itens de uma vez.
     *
     * @return Collection<int, array{valor: string, label: string}>
     */
    public function listarLotesDisponiveis(Processo $processo): Collection
    {
        $etp = $processo->etp;

        if (! $etp || ! $etp->usaLotes()) {
            return collect();
        }

        $loteIdsReservados = $this->cotaReservadaService->loteIdsAtivos($processo->detalhe?->lotes_cota_reservada ?? null);
        $lotesEfetivos = $this->cotaReservadaService->dividirItensPorLote($etp->lotes, $loteIdsReservados);

        return collect($lotesEfetivos)->values()->map(fn ($lote, $indice) => [
            'valor' => (string) ($indice + 1),
            'label' => $lote['nome'],
        ]);
    }

    /**
     * @param  string|null  $loteSelecionado  Índice (1-based) dentro da lista de
     *                                        listarLotesDisponiveis() — null exporta todos os lotes de uma vez.
     */
    public function gerar(Processo $processo, ?string $loteSelecionado = null): Spreadsheet
    {
        $etp = $processo->etp;

        if (! $etp) {
            throw new \RuntimeException('Este processo não possui um ETP Inteligente vinculado — não há itens para exportar.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Itens Vencedor');

        $cabecalho = ['↓ Preencha o Valor Unitário Homologado antes de importar', '', 'Lote', 'Status', 'Item', 'Descrição', 'Unidade', 'Marca', 'Modelo', 'Quantidade', 'Valor Unitário Homologado (R$)'];
        foreach ($cabecalho as $col => $texto) {
            $sheet->setCellValueByColumnAndRow($col + 1, 1, $texto);
            $sheet->getStyleByColumnAndRow($col + 1, 1)->getFont()->setBold(true);
            $sheet->getStyleByColumnAndRow($col + 1, 1)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');
        }

        $row = 2;
        $escreverItem = function ($numeroLote, array $item) use ($sheet, &$row) {
            $sheet->setCellValueByColumnAndRow(3, $row, $numeroLote !== null ? (string) $numeroLote : '');
            $sheet->setCellValueByColumnAndRow(4, $row, 'HOMOLOGADO');
            $sheet->setCellValueByColumnAndRow(5, $row, (string) ($item['numero'] ?? ''));
            $sheet->setCellValueByColumnAndRow(6, $row, $item['descricao_item']);
            $sheet->setCellValueByColumnAndRow(7, $row, $item['unidade']);
            $sheet->setCellValueByColumnAndRow(10, $row, (float) $item['quantidade']);
            // Valor unitário fica em branco — é o campo que o admin precisa preencher.
            $row++;
        };

        if ($etp->usaLotes() && $etp->lotes->isNotEmpty()) {
            $loteIdsReservados = $this->cotaReservadaService->loteIdsAtivos($processo->detalhe?->lotes_cota_reservada ?? null);
            $lotesEfetivos = collect($this->cotaReservadaService->dividirItensPorLote($etp->lotes, $loteIdsReservados))->values();

            if ($loteSelecionado !== null) {
                $lotesEfetivos = $lotesEfetivos->filter(
                    fn ($lote, $indice) => (string) ($indice + 1) === $loteSelecionado
                )->values();

                if ($lotesEfetivos->isEmpty()) {
                    throw new \RuntimeException("Lote \"{$loteSelecionado}\" não encontrado.");
                }
            }

            foreach ($lotesEfetivos as $loteIndice => $lote) {
                $numeroLote = $loteIndice + 1;

                // Alguns ETPs já guardam o nome do lote com o prefixo "LOTE N - " incluso
                // (ex.: "LOTE 1 - MATERIAIS DE INFORMÁTICA") — não duplica o prefixo nesse caso.
                $tituloLote = str_starts_with(strtoupper(trim($lote['nome'])), 'LOTE ')
                    ? $lote['nome']
                    : "LOTE {$numeroLote} - {$lote['nome']}";

                $sheet->setCellValueByColumnAndRow(1, $row, $tituloLote);
                $sheet->getStyleByColumnAndRow(1, $row)->getFont()->setBold(true);
                $sheet->getStyleByColumnAndRow(1, $row)->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0F2FE');
                $row++;

                foreach ($lote['itens'] as $itemIndice => $item) {
                    $escreverItem($numeroLote, $item + ['numero' => $itemIndice + 1]);
                }
            }
        } else {
            foreach ($etp->itens as $itemIndice => $item) {
                $escreverItem(null, [
                    'numero' => $itemIndice + 1,
                    'descricao_item' => $item->descricao_item,
                    'unidade' => $item->pivot->unidade,
                    'quantidade' => (float) $item->pivot->quantidade,
                ]);
            }
        }

        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }
}
