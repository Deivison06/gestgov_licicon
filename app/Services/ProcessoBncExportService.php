<?php

namespace App\Services;

use App\Models\Etp;
use App\Models\Processo;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Gera a planilha de importação de lotes/itens para a Bolsa Nacional de Compras (BNC),
 * no formato do modelo usado manualmente pelo setor (docs/Modelo_Global.xlsx):
 * abas "Lotes", "Itens" e "TipoLance".
 *
 * Por ora cobre apenas processos com ETP Inteligente vinculado e organizado em lotes,
 * já que é dali que vêm a descrição/unidade/quantidade de cada item; o valor de
 * referência reaproveita a mesma pesquisa de preço usada nos PDFs do processo
 * (ver ProcessoPdfService::construirPrecoMapId).
 */
class ProcessoBncExportService
{
    private const TIPO_LANCE_GLOBAL = 2;
    private const QUANTIDADE_LOTE_FIXA = 1;
    private const TEXTO_CONFORME_EDITAL = 'CONFORME EDITAL';

    public function __construct(
        private readonly ProcessoPdfService $pdfService
    ) {
    }

    public function podeExportar(Processo $processo): bool
    {
        $etp = $processo->etp;

        return $etp && $etp->usaLotes() && $etp->lotes->isNotEmpty();
    }

    public function gerar(Processo $processo): Spreadsheet
    {
        $processo->loadMissing(['detalhe', 'etp.lotes.itens', 'pesquisaPrecoItens']);

        $etp = $processo->etp;

        if (!$etp || !$etp->usaLotes() || $etp->lotes->isEmpty()) {
            throw new \RuntimeException(
                'Este processo não possui um ETP Inteligente vinculado organizado por lotes — não há dados para exportar.'
            );
        }

        $precoMapId  = $this->pdfService->construirPrecoMapId($processo);
        $exclusivoMe = ($processo->detalhe->participacao_exclusiva_mei_epp ?? 'nao') === 'sim' ? 'Sim' : 'Não';

        // "Margem Lance" fica em branco de propósito: o campo `intervalo_lances` guarda texto
        // livre para compor a prosa do edital (ex.: "R$ 10,00 (dez reais)"), não o número puro
        // que a BNC espera — o usuário preenche manualmente antes de importar a planilha.
        $margemLance = '';

        $spreadsheet = new Spreadsheet();

        $this->preencherAbaLotes($spreadsheet->getActiveSheet(), $etp, $margemLance, $exclusivoMe);
        $this->preencherAbaItens($spreadsheet->createSheet(), $etp, $precoMapId);
        $this->preencherAbaTipoLance($spreadsheet->createSheet());

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    private function preencherAbaLotes(Worksheet $sheet, Etp $etp, string $margemLance, string $exclusivoMe): void
    {
        $sheet->setTitle('Lotes');

        $this->escreverCabecalho($sheet, ['Lote', 'Título', 'Tipo Lance', 'Quantidade', 'Margem Lance', 'Garantia', 'Local Entrega', 'Exclusivo ME']);

        $row = 2;
        foreach ($etp->lotes as $indice => $lote) {
            $sheet->setCellValueByColumnAndRow(1, $row, $indice + 1);
            $sheet->setCellValueByColumnAndRow(2, $row, $lote->nome);
            $sheet->setCellValueByColumnAndRow(3, $row, self::TIPO_LANCE_GLOBAL);
            $sheet->setCellValueByColumnAndRow(4, $row, self::QUANTIDADE_LOTE_FIXA);
            $sheet->setCellValueByColumnAndRow(5, $row, $margemLance);
            $sheet->setCellValueByColumnAndRow(6, $row, self::TEXTO_CONFORME_EDITAL);
            $sheet->setCellValueByColumnAndRow(7, $row, self::TEXTO_CONFORME_EDITAL);
            $sheet->setCellValueByColumnAndRow(8, $row, $exclusivoMe);
            $row++;
        }

        $this->autoSize($sheet, 'A', 'H');
    }

    private function preencherAbaItens(Worksheet $sheet, Etp $etp, array $precoMapId): void
    {
        $sheet->setTitle('Itens');

        $this->escreverCabecalho($sheet, ['Lote', 'Item', 'Descrição', 'Unidade', 'Quantidade', 'Valor Referência', 'Info Detalhada', 'Arquivo requerido']);

        $row = 2;
        foreach ($etp->lotes as $loteIndice => $lote) {
            foreach ($lote->itens as $itemIndice => $item) {
                $valorUnitario = (float) ($precoMapId[$item->id] ?? 0);

                $sheet->setCellValueByColumnAndRow(1, $row, $loteIndice + 1);
                $sheet->setCellValueByColumnAndRow(2, $row, $itemIndice + 1);
                $sheet->setCellValueByColumnAndRow(3, $row, $item->descricao_item);
                $sheet->setCellValueByColumnAndRow(4, $row, $item->pivot->unidade);
                $sheet->setCellValueByColumnAndRow(5, $row, (float) $item->pivot->quantidade);
                $sheet->setCellValueByColumnAndRow(6, $row, round($valorUnitario, 2));
                $sheet->setCellValueByColumnAndRow(7, $row, 'Não');
                $sheet->setCellValueByColumnAndRow(8, $row, $itemIndice === 0 ? 'Sim' : 'Não');
                $row++;
            }
        }

        $this->autoSize($sheet, 'A', 'H');
    }

    private function preencherAbaTipoLance(Worksheet $sheet): void
    {
        $sheet->setTitle('TipoLance');

        $this->escreverCabecalho($sheet, ['idTipoLance', 'Descrição']);

        $row = 2;
        foreach ([1 => 'Unitário', 2 => 'Global', 3 => 'Kit'] as $id => $descricao) {
            $sheet->setCellValueByColumnAndRow(1, $row, $id);
            $sheet->setCellValueByColumnAndRow(2, $row, $descricao);
            $row++;
        }

        $this->autoSize($sheet, 'A', 'B');
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
