<?php

namespace App\Assinatura\Infrastructure\Pdf;

use App\Models\DocumentoVersao;
use setasign\Fpdi\Tcpdf\Fpdi;

/**
 * Renderização FPDI/TCPDF do PDF consolidado: copia o rascunho página a página e
 * acrescenta a página final de assinaturas (blocos estilo SEI + QR + código
 * verificador).
 *
 * Responsabilidade ÚNICA de desenho — separada da orquestração de consolidação
 * (idempotência, hash, persistência, log) que vive em AssinaturaConsolidacaoService.
 */
class PaginaAssinaturasRenderer
{
    /**
     * URL base da página pública de validação. Usada no QR/rodapé do PDF.
     */
    private string $urlValidacaoBase;

    public function __construct()
    {
        // Usa route helper se disponível, senão constrói manualmente. Útil em testes
        // onde a rota pode ainda não ter sido carregada.
        try {
            $this->urlValidacaoBase = rtrim(route('autenticar.formulario'), '/');
        } catch (\Throwable $e) {
            $this->urlValidacaoBase = rtrim(config('app.url'), '/') . '/autenticar';
        }
    }

    /**
     * Gera o PDF consolidado da versão em $caminhoSaida.
     */
    public function gerar(DocumentoVersao $versao, string $caminhoSaida): void
    {
        $pdf = new Fpdi();
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 20);

        // Se o rascunho tiver o sufixo _watermark, usa o original limpo para a consolidação
        $caminhoBase = $versao->caminho_pdf;
        $caminhoLimpo = preg_replace('/_watermark\.pdf$/i', '.pdf', $caminhoBase);
        
        if ($caminhoLimpo !== $caminhoBase && file_exists($caminhoLimpo)) {
            $caminhoBase = $caminhoLimpo;
        }

        // 1) Copia todas as páginas do arquivo preservando orientação
        $totalPaginas = $pdf->setSourceFile($caminhoBase);
        for ($i = 1; $i <= $totalPaginas; $i++) {
            $tplId = $pdf->importPage($i);
            $size  = $pdf->getTemplateSize($tplId);
            $orientation = $size['width'] > $size['height'] ? 'L' : 'P';
            $pdf->AddPage($orientation, [$size['width'], $size['height']]);
            $pdf->useTemplate($tplId);
        }

        // 2) Página final com blocos de assinatura + QR
        $pdf->AddPage('P', 'A4');
        $this->renderizarPaginaAssinaturas($pdf, $versao);

        $pdf->Output($caminhoSaida, 'F');
    }

    private function renderizarPaginaAssinaturas(Fpdi $pdf, DocumentoVersao $versao): void
    {
        // Título da página
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 8, 'ASSINATURAS DIGITAIS', 0, 1, 'C');

        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(80, 80, 80);
        $pdf->MultiCell(0, 5,
            'Este documento foi assinado eletronicamente pelas pessoas listadas abaixo, '
            . 'conforme Lei nº 14.063/2020. Para conferir a autenticidade, acesse a '
            . 'página pública informando o código verificador correspondente.',
            0, 'C'
        );
        $pdf->Ln(5);

        // Bloco visual para cada assinatura
        foreach ($versao->assinaturas->sortBy('assinado_em') as $assinatura) {
            $this->renderizarBlocoAssinatura($pdf, $assinatura);
        }

        // QR + rodapé de autenticação (centralizados)
        $pdf->Ln(10);
        $this->renderizarRodapeAutenticacao($pdf, $versao);
    }

    private function renderizarBlocoAssinatura(Fpdi $pdf, $assinatura): void
    {
        $meta = is_array($assinatura->metadados) ? $assinatura->metadados : [];
        $nome      = $meta['nome']            ?? optional($assinatura->assinante)->name ?? '—';
        $portaria  = $meta['numero_portaria'] ?? optional($assinatura->assinante)->numero_portaria ?? null;
        $cargo     = $meta['cargo']           ?? null;
        $dataHora  = $assinatura->assinado_em->format('d/m/Y \à\s H:i');

        $linhas = [
            sprintf('Documento assinado eletronicamente por %s', strtoupper($nome)),
        ];

        $rodape = [];
        if ($portaria) $rodape[] = 'Matr./Portaria ' . $portaria;
        if ($cargo)    $rodape[] = $cargo;
        $rodape[] = "em {$dataHora}, conforme horário oficial de Brasília";

        $linhas[] = implode(', ', $rodape) . '.';
        $linhas[] = sprintf(
            'Código verificador: %s   |   CRC: %s',
            $assinatura->codigo_verificador,
            $assinatura->crc_humano
        );

        // Caixa com borda
        $yInicial = $pdf->GetY();
        $xInicial = $pdf->GetX();
        $largura  = $pdf->getPageWidth() - 30;

        $pdf->SetFillColor(248, 250, 252);
        $pdf->SetDrawColor(200, 200, 200);
        $pdf->SetLineWidth(0.2);

        // Calcular altura estimada (3 linhas)
        $altura = 18;
        $pdf->Rect($xInicial, $yInicial, $largura, $altura, 'DF');

        // Conteúdo
        $pdf->SetXY($xInicial + 3, $yInicial + 2);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell($largura - 6, 4, $linhas[0], 0, 1);

        $pdf->SetX($xInicial + 3);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell($largura - 6, 4, $linhas[1], 0, 1);

        $pdf->SetX($xInicial + 3);
        $pdf->SetFont('courier', '', 8);
        $pdf->SetTextColor(0, 116, 124); // teal
        $pdf->Cell($largura - 6, 4, $linhas[2], 0, 1);

        $pdf->Ln(2);
    }

    private function renderizarRodapeAutenticacao(Fpdi $pdf, DocumentoVersao $versao): void
    {
        // Usa o código verificador da primeira assinatura como "código mestre" para a página
        $primeiraAssinatura = $versao->assinaturas->sortBy('assinado_em')->first();
        $codigoMestre = $primeiraAssinatura->codigo_verificador;
        $crcMestre    = $primeiraAssinatura->crc_humano;
        $urlValidacao = $this->urlValidacaoBase . '/' . $codigoMestre;

        // Linha separadora
        $pdf->SetDrawColor(200, 200, 200);
        $pdf->Line(15, $pdf->GetY(), $pdf->getPageWidth() - 15, $pdf->GetY());
        $pdf->Ln(5);

        // QR Code (lado esquerdo) — TCPDF nativo, sem dependência extra
        $estiloQr = [
            'border'  => 0,
            'padding' => 0,
            'fgcolor' => [0, 0, 0],
            'bgcolor' => false,
        ];
        $yQr = $pdf->GetY();
        $pdf->write2DBarcode($urlValidacao, 'QRCODE,M', 18, $yQr, 30, 30, $estiloQr);

        // Texto explicativo (ao lado direito do QR)
        $pdf->SetXY(52, $yQr + 2);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(51, 65, 85);
        $pdf->MultiCell(
            $pdf->getPageWidth() - 70,
            4.5,
            "A autenticidade deste documento pode ser conferida no site\n"
            . $urlValidacao . "\n"
            . "informando o código verificador {$codigoMestre} e o código CRC {$crcMestre}.",
            0,
            'L'
        );

        $pdf->Ln(8);
        $pdf->SetFont('helvetica', 'I', 7);
        $pdf->SetTextColor(148, 163, 184);
        $pdf->Cell(0, 4,
            sprintf('Versão %d gerada em %s — Hash: %s',
                $versao->versao,
                $versao->gerado_em->format('d/m/Y H:i'),
                substr($versao->hash_sha256, 0, 16) . '...'
            ),
            0, 1, 'C'
        );
    }
}
