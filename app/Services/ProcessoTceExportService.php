<?php

namespace App\Services;

use App\Enums\TipoContratacaoEnum;
use App\Models\Processo;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Gera a planilha de importação de itens homologados no sistema "Licitações Web"
 * do Tribunal de Contas do Estado (TCE), no formato do modelo enviado pelo cliente
 * (docs/Exportacao_de_dados_TCE/exemplo_importacao_itens.xlsx): colunas Número,
 * Descrição, Qtd, Unidade de Medida, Valor Unitário Previsto, Valor Unitário
 * Homologado, Doc Vencedor e Reservado.
 *
 * A exportação é sempre feita lote a lote (é assim que o TCE recebe a planilha).
 * Número/Descrição/Qtd/Unidade/Valor Homologado e Doc Vencedor vêm direto dos
 * itens já homologados (tabela `lotes` + `vencedores`, criados na finalização).
 * O Valor Unitário Previsto vem do ETP Inteligente (lado "inicial"/TR), casado
 * por descrição do item — não existe FK entre o item vencedor e o item do ETP.
 *
 * "Reservado" é preenchido a partir do nome do lote homologado (`lote_nome`):
 * se ele termina com o sufixo de Cota Reservada ME/EPP (ver
 * App\Services\CotaReservadaService), sai "S", senão "N". É uma checagem
 * textual best-effort — não há FK entre o lote homologado e o ETP — então
 * depende de quem digita/importa o vencedor copiar o nome do lote exatamente
 * como saiu no edital/TR.
 */
class ProcessoTceExportService
{
    public function __construct(
        private readonly ProcessoPdfService $pdfService,
        private readonly CotaReservadaService $cotaReservadaService
    ) {}

    public function podeExportar(Processo $processo): bool
    {
        return $processo->tipo_contratacao === TipoContratacaoEnum::LOTE
            && $processo->vencedores->flatMap->lotes->isNotEmpty();
    }

    /**
     * Lista os lotes distintos já homologados neste processo, para alimentar
     * o <select> do modal "Exportar Planilha para o TCE".
     *
     * @return Collection<int, array{valor: string, label: string}>
     */
    public function listarLotesDisponiveis(Processo $processo): Collection
    {
        return $processo->vencedores->flatMap->lotes
            ->groupBy('lote')
            ->map(function (Collection $itensDoLote, $lote) {
                $nome = $itensDoLote->first()->lote_nome;

                return [
                    'valor' => (string) $lote,
                    'label' => 'Lote '.$lote.($nome ? ' — '.$nome : ''),
                ];
            })
            ->sortBy('valor', SORT_NATURAL)
            ->values();
    }

    /**
     * @return array{spreadsheet: Spreadsheet, itensSemPrecoPrevisto: array}
     */
    public function gerar(Processo $processo, string $lote): array
    {
        if (! $this->podeExportar($processo)) {
            throw new \RuntimeException(
                'Este processo não é do tipo "por Lote" ou ainda não possui itens homologados — não há dados para exportar.'
            );
        }

        $processo->loadMissing(['detalhe', 'etp.lotes.itens', 'vencedores.lotes.vencedor']);

        $itensDoLote = $processo->vencedores->flatMap->lotes
            ->where('lote', $lote)
            ->sortBy('ordem')
            ->values();

        if ($itensDoLote->isEmpty()) {
            throw new \RuntimeException("Nenhum item homologado encontrado para o lote \"{$lote}\".");
        }

        $precoMapId = $this->pdfService->construirPrecoMapId($processo);
        $loteNomeHomologado = $itensDoLote->first()->lote_nome;
        $poolEtpItens = $this->poolDeItensDoEtp($processo, $loteNomeHomologado);
        $reservado = $this->cotaReservadaService->nomeIndicaCotaReservada($loteNomeHomologado) ? 'S' : 'N';

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('TCE');

        $this->escreverCabecalho($sheet, [
            'Número', 'Descrição', 'Qtd', 'Unidade de Medida',
            'Valor Unitário Previsto', 'Valor Unitário Homologado', 'Doc Vencedor', 'Reservado',
        ]);

        $itensSemPrecoPrevisto = [];
        $row = 2;

        foreach ($itensDoLote as $itemLote) {
            $etpItem = $this->encontrarEtpItemPorDescricao($poolEtpItens, $itemLote->descricao);
            $valorPrevisto = $etpItem ? ($precoMapId[$etpItem->id] ?? null) : null;

            $sheet->setCellValueByColumnAndRow(1, $row, $itemLote->item);
            $sheet->setCellValueByColumnAndRow(2, $row, $itemLote->descricao);
            $sheet->setCellValueByColumnAndRow(3, $row, (float) $itemLote->quantidade);
            $sheet->setCellValueByColumnAndRow(4, $row, $itemLote->unidade);

            if ($valorPrevisto !== null && $valorPrevisto > 0) {
                $sheet->setCellValueByColumnAndRow(5, $row, round((float) $valorPrevisto, 2));
            } else {
                $itensSemPrecoPrevisto[] = [
                    'item' => $itemLote->item,
                    'descricao' => $itemLote->descricao,
                ];
            }

            $sheet->setCellValueByColumnAndRow(6, $row, round((float) $itemLote->vl_unit, 2));
            $sheet->setCellValueByColumnAndRow(7, $row, $itemLote->vencedor->cnpj_formatado ?? '');
            $sheet->setCellValueByColumnAndRow(8, $row, $reservado);
            $row++;
        }

        $this->autoSize($sheet, 'A', 'H');

        return [
            'spreadsheet' => $spreadsheet,
            'itensSemPrecoPrevisto' => $itensSemPrecoPrevisto,
        ];
    }

    /**
     * Reúne os itens do ETP a serem usados na busca por descrição: prioriza os
     * itens do lote do ETP com o mesmo nome do lote homologado (`lote_nome`) e,
     * se não achar correspondência de nome, cai para todos os itens do ETP.
     *
     * @return Collection<int, \App\Models\EtpItem>
     */
    private function poolDeItensDoEtp(Processo $processo, ?string $loteNome): Collection
    {
        $etp = $processo->etp;

        if (! $etp) {
            return collect();
        }

        if ($loteNome && $etp->usaLotes()) {
            // O lote_nome digitado na Finalização pode vir sufixado (Ampla
            // Concorrência/Cota Reservada) quando o lote passou pela divisão —
            // remove o sufixo antes de comparar com o nome "base" do ETP.
            $nomeAlvo = $this->normalizar($this->cotaReservadaService->removerSufixos($loteNome));
            $etpLote = $etp->lotes->first(fn ($l) => $this->normalizar($l->nome) === $nomeAlvo);

            if ($etpLote) {
                return $etpLote->itens;
            }
        }

        return $etp->all_itens;
    }

    private function encontrarEtpItemPorDescricao(Collection $pool, ?string $descricao)
    {
        $alvo = $this->normalizar($descricao);

        if ($alvo === '') {
            return null;
        }

        return $pool->first(fn ($item) => $this->normalizar($item->descricao_item) === $alvo);
    }

    private function normalizar(?string $texto): string
    {
        return trim(mb_strtolower((string) $texto));
    }

    private function escreverCabecalho(Worksheet $sheet, array $headers): void
    {
        foreach ($headers as $col => $header) {
            $sheet->setCellValueByColumnAndRow($col + 1, 1, $header);
            $style = $sheet->getStyleByColumnAndRow($col + 1, 1);
            $style->getFont()->setBold(true);
            $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');
        }
    }

    private function autoSize(Worksheet $sheet, string $de, string $ate): void
    {
        foreach (range($de, $ate) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
}
