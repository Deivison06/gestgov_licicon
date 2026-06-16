<?php

namespace App\Services\Assinatura;

use setasign\Fpdi\Tcpdf\Fpdi;

/**
 * Aplica marca d'água em PDFs (ex.: "AGUARDANDO ASSINATURAS" enquanto a rodada
 * de assinatura está em curso). Não modifica o PDF original — gera um novo.
 */
class PdfWatermarkService
{
    /**
     * @param string      $caminhoPdfOriginal Caminho absoluto do PDF original
     * @param string      $texto              Texto a estampar (ex.: "AGUARDANDO ASSINATURAS")
     * @param string|null $caminhoSaida       Se null, gera no mesmo dir com sufixo _watermark.pdf
     * @return string Caminho do PDF resultante
     */
    public function aplicarMarcaDagua(
        string $caminhoPdfOriginal,
        string $texto,
        ?string $caminhoSaida = null
    ): string {
        if (!file_exists($caminhoPdfOriginal)) {
            throw new \InvalidArgumentException("PDF original não encontrado: {$caminhoPdfOriginal}");
        }

        if ($caminhoSaida === null) {
            $caminhoSaida = preg_replace('/\.pdf$/i', '_watermark.pdf', $caminhoPdfOriginal);
        }

        $pdf = new Fpdi();
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false, 0);
        // Remove cabeçalho/rodapé padrão do TCPDF
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $totalPaginas = $pdf->setSourceFile($caminhoPdfOriginal);

        for ($i = 1; $i <= $totalPaginas; $i++) {
            $tplId = $pdf->importPage($i);
            $size  = $pdf->getTemplateSize($tplId);
            $orientation = $size['width'] > $size['height'] ? 'L' : 'P';

            $pdf->AddPage($orientation, [$size['width'], $size['height']]);
            $pdf->useTemplate($tplId);

            $this->estamparTextoDiagonal($pdf, $texto, $size['width'], $size['height']);
        }

        $pdf->Output($caminhoSaida, 'F');

        return $caminhoSaida;
    }

    /**
     * Estampa o texto rotacionado 45° no centro da página, em cinza claro grande.
     */
    private function estamparTextoDiagonal(Fpdi $pdf, string $texto, float $w, float $h): void
    {
        $pdf->SetFont('helvetica', 'B', 60);
        $pdf->SetTextColor(220, 220, 220);   // cinza claro
        $pdf->SetAlpha(0.35);                 // levemente transparente

        $cx = $w / 2;
        $cy = $h / 2;

        $pdf->StartTransform();
        $pdf->Rotate(-45, $cx, $cy);
        // MultiCell centralizado a partir do canto superior esquerdo da bounding box
        $textWidth = $pdf->GetStringWidth($texto);
        $pdf->SetXY($cx - $textWidth / 2, $cy - 10);
        $pdf->Cell($textWidth, 20, $texto, 0, 0, 'C');
        $pdf->StopTransform();

        $pdf->SetAlpha(1.0); // restaura
    }
}
