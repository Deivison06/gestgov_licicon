<?php

namespace App\Assinatura\Infrastructure\Pdf;

use setasign\Fpdi\Tcpdf\Fpdi;

/**
 * Renderização FPDI/TCPDF do selo de autenticação: acrescenta, como primeira página
 * do documento, um selo com o código verificador + QR — carimbado já na geração do
 * rascunho, antes de qualquer assinatura.
 *
 * O código gerado aqui é o mesmo, depois, usado no selo de assinatura (irmão desta
 * classe: PaginaAssinaturasRenderer) — um único QR para os dois estágios do documento.
 *
 * Responsabilidade ÚNICA de desenho — separada da orquestração (geração de código,
 * hash, persistência) que vive em DocumentoVersaoService.
 */
class PaginaAutenticacaoRenderer
{
    private string $urlValidacaoBase;

    public function __construct()
    {
        try {
            $this->urlValidacaoBase = rtrim(route('autenticar.formulario'), '/');
        } catch (\Throwable $e) {
            $this->urlValidacaoBase = rtrim(config('app.url'), '/') . '/autenticar';
        }
    }

    /**
     * Gera, em $caminhoSaida, uma cópia de $caminhoEntrada com o selo de autenticação
     * como primeira página. Não modifica $caminhoEntrada.
     */
    public function gerar(
        string $caminhoEntrada,
        string $caminhoSaida,
        string $codigoVerificador,
        string $nomeGerador,
        \DateTimeInterface $geradoEm,
        int $versaoNumero
    ): void {
        $pdf = new Fpdi();
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 20);

        // 1) Página do selo, primeiro.
        $pdf->AddPage('P', 'A4');
        $this->renderizarSelo($pdf, $codigoVerificador, $nomeGerador, $geradoEm, $versaoNumero);

        // 2) Depois, todas as páginas do documento original, preservando orientação.
        $totalPaginas = $pdf->setSourceFile($caminhoEntrada);
        for ($i = 1; $i <= $totalPaginas; $i++) {
            $tplId = $pdf->importPage($i);
            $size  = $pdf->getTemplateSize($tplId);
            $orientation = $size['width'] > $size['height'] ? 'L' : 'P';
            $pdf->AddPage($orientation, [$size['width'], $size['height']]);
            $pdf->useTemplate($tplId);
        }

        $pdf->Output($caminhoSaida, 'F');
    }

    private function renderizarSelo(
        Fpdi $pdf,
        string $codigoVerificador,
        string $nomeGerador,
        \DateTimeInterface $geradoEm,
        int $versaoNumero
    ): void {
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 8, 'SELO DE AUTENTICAÇÃO DO DOCUMENTO', 0, 1, 'C');

        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(80, 80, 80);
        $pdf->MultiCell(0, 5,
            'Este documento foi gerado eletronicamente pelo sistema e possui um código '
            . 'verificador único, que autentica sua origem e conteúdo desde a geração. '
            . 'Caso o documento venha a ser assinado digitalmente, o mesmo código passará '
            . 'a validar também a(s) assinatura(s).',
            0, 'C'
        );
        $pdf->Ln(5);

        // Caixa com os dados de geração.
        $yInicial = $pdf->GetY();
        $xInicial = $pdf->GetX();
        $largura  = $pdf->getPageWidth() - 30;

        $pdf->SetFillColor(248, 250, 252);
        $pdf->SetDrawColor(200, 200, 200);
        $pdf->SetLineWidth(0.2);
        $pdf->Rect($xInicial, $yInicial, $largura, 18, 'DF');

        $pdf->SetXY($xInicial + 3, $yInicial + 2);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell($largura - 6, 4, sprintf('Documento gerado por %s', strtoupper($nomeGerador)), 0, 1);

        $pdf->SetX($xInicial + 3);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell($largura - 6, 4,
            sprintf('Versão %d, em %s, conforme horário oficial de Brasília',
                $versaoNumero,
                $geradoEm->format('d/m/Y \à\s H:i')
            ), 0, 1);

        $pdf->SetX($xInicial + 3);
        $pdf->SetFont('courier', '', 8);
        $pdf->SetTextColor(0, 116, 124); // teal
        $pdf->Cell($largura - 6, 4, "Código verificador: {$codigoVerificador}", 0, 1);

        $pdf->Ln(10);

        $urlValidacao = $this->urlValidacaoBase . '/' . $codigoVerificador;

        $pdf->SetDrawColor(200, 200, 200);
        $pdf->Line(15, $pdf->GetY(), $pdf->getPageWidth() - 15, $pdf->GetY());
        $pdf->Ln(5);

        $estiloQr = [
            'border'  => 0,
            'padding' => 0,
            'fgcolor' => [0, 0, 0],
            'bgcolor' => false,
        ];
        $yQr = $pdf->GetY();
        $pdf->write2DBarcode($urlValidacao, 'QRCODE,M', 18, $yQr, 30, 30, $estiloQr);

        $pdf->SetXY(52, $yQr + 2);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(51, 65, 85);
        $pdf->MultiCell(
            $pdf->getPageWidth() - 70,
            4.5,
            "A autenticidade deste documento pode ser conferida no site\n"
            . $urlValidacao . "\n"
            . "informando o código verificador {$codigoVerificador}.",
            0,
            'L'
        );
    }
}
