<?php

namespace App\Assinatura\Infrastructure\Pdf;

use App\Assinatura\Application\Support\AssinanteResolver;
use App\Models\DocumentoSelecaoAssinantes;
use App\Models\DocumentoVersao;
use Illuminate\Support\Collection;
use setasign\Fpdi\Tcpdf\Fpdi;

/**
 * Renderização FPDI/TCPDF do PDF consolidado: copia o rascunho página a página e
 * acrescenta a(s) página(s) de assinaturas (blocos estilo SEI + QR + código).
 *
 * Quando o documento tem assinantes DIFERENTES por subdocumento (ex.: estudo_tecnico
 * = ETP assinado por um, Mapa de Riscos por outro), gera UMA página de assinatura
 * por subdocumento — cada uma com o título do subdocumento e apenas a assinatura do
 * seu respectivo assinante. O mapeamento é posicional: assinante[0] -> 1º subdocumento,
 * assinante[1] -> 2º, etc. (mesma ordem usada nos templates), lido da seleção salva
 * (DocumentoSelecaoAssinantes). Para os demais documentos, gera uma única página com
 * todas as assinaturas.
 *
 * Responsabilidade ÚNICA de desenho — separada da orquestração de consolidação
 * (idempotência, hash, persistência, log) que vive em AssinaturaConsolidacaoService.
 */
class PaginaAssinaturasRenderer
{
    /**
     * Títulos dos subdocumentos (na ORDEM dos assinantes) para tipos de documento
     * que reúnem mais de um documento assinado por pessoas diferentes no mesmo PDF.
     * Cada subdocumento gera UMA página de assinatura própria, todas acrescentadas
     * ao FINAL do documento consolidado (na ordem dos assinantes).
     */
    private const SUBDOCUMENTOS = [
        'estudo_tecnico' => [
            'INSTRUMENTOS DE PLANEJAMENTO / ESTUDO TÉCNICO PRELIMINAR (ETP)',
            'MAPA DE GERENCIAMENTO DE RISCOS',
        ],
    ];

    /**
     * URL base da página pública de validação. Usada no QR/rodapé do PDF.
     */
    private string $urlValidacaoBase;

    public function __construct(
        private readonly AssinanteResolver $assinanteResolver
    ) {
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

        // 2) Página(s) de assinatura: uma por subdocumento (assinantes diferentes) ou
        //    uma única com todas as assinaturas.
        $paginas = $this->montarPaginasAssinatura($versao);
        foreach ($paginas as $pagina) {
            $pdf->AddPage('P', 'A4');
            $this->renderizarPaginaAssinaturas($pdf, $versao, $pagina['assinaturas'], $pagina['titulo']);
        }

        $pdf->Output($caminhoSaida, 'F');
    }

    /**
     * Define as páginas de assinatura a renderizar.
     *
     * @return array<int, array{titulo: ?string, assinaturas: Collection}>
     */
    private function montarPaginasAssinatura(DocumentoVersao $versao): array
    {
        $porSubdocumento = $this->paginasPorSubdocumento($versao);
        if ($porSubdocumento !== null) {
            return $porSubdocumento;
        }

        // Padrão: uma página com todas as assinaturas.
        return [[
            'titulo'      => null,
            'assinaturas' => $versao->assinaturas->sortBy('assinado_em')->values(),
        ]];
    }

    /**
     * Quando o documento reúne subdocumentos com assinantes diferentes, retorna uma
     * página por subdocumento (título + assinatura do respectivo assinante). Caso
     * contrário (tipo sem config, ou só um assinante), retorna null.
     *
     * @return array<int, array{titulo: string, assinaturas: Collection}>|null
     */
    private function paginasPorSubdocumento(DocumentoVersao $versao): ?array
    {
        $versao->loadMissing('documentavel');
        $documento = $versao->documentavel;

        $tipo = $documento->tipo_documento ?? null;
        if (!$tipo || !isset(self::SUBDOCUMENTOS[$tipo])) {
            return null;
        }

        $selecao = DocumentoSelecaoAssinantes::query()
            ->where('processo_id', $documento->processo_id)
            ->where('tipo_documento', $tipo)
            ->where('homologacao_id', $documento->homologacao_id ?? null)
            ->first();

        $selecionados = ($selecao && is_array($selecao->assinantes))
            ? array_values($selecao->assinantes)
            : [];

        // Só faz sentido dividir quando há mais de um assinante distinto.
        if (count($selecionados) < 2) {
            return null;
        }

        $titulos = self::SUBDOCUMENTOS[$tipo];
        $paginas = [];

        foreach ($titulos as $i => $titulo) {
            if (!isset($selecionados[$i])) {
                break;
            }
            $assinatura = $this->assinaturaDoSelecionado($versao, $selecionados[$i]);
            if (!$assinatura) {
                continue;
            }
            $paginas[] = [
                'titulo'      => $titulo,
                'assinaturas' => collect([$assinatura]),
            ];
        }

        // Se não conseguimos mapear ao menos 2 subdocumentos, cai no padrão (página única).
        return count($paginas) >= 2 ? $paginas : null;
    }

    /**
     * Resolve a AssinaturaDigital correspondente a um assinante selecionado
     * (por user_id explícito ou pelo nome do responsável).
     */
    private function assinaturaDoSelecionado(DocumentoVersao $versao, array $selecionado)
    {
        $userId = $selecionado['user_id'] ?? $selecionado['id'] ?? null;

        if (!$userId && !empty($selecionado['responsavel'])) {
            $user = $this->assinanteResolver->resolverUserPorNome($selecionado['responsavel']);
            $userId = $user?->id;
        }

        if (!$userId) {
            return null;
        }

        return $versao->assinaturas->firstWhere('assinante_user_id', (int) $userId);
    }

    private function renderizarPaginaAssinaturas(Fpdi $pdf, DocumentoVersao $versao, Collection $assinaturas, ?string $titulo = null): void
    {
        // Título da página
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 8, 'ASSINATURAS DIGITAIS', 0, 1, 'C');

        // Subtítulo com o nome do subdocumento (quando aplicável)
        if ($titulo) {
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetTextColor(0, 116, 124);
            $pdf->MultiCell(0, 5, $titulo, 0, 'C');
            $pdf->Ln(1);
        }

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
        foreach ($assinaturas->sortBy('assinado_em') as $assinatura) {
            $this->renderizarBlocoAssinatura($pdf, $assinatura);
        }

        // QR + rodapé de autenticação (centralizados)
        $pdf->Ln(10);
        $this->renderizarRodapeAutenticacao($pdf, $versao, $assinaturas);
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

    private function renderizarRodapeAutenticacao(Fpdi $pdf, DocumentoVersao $versao, Collection $assinaturas): void
    {
        // Usa o código verificador da primeira assinatura DESTA PÁGINA como "código mestre".
        $primeiraAssinatura = $assinaturas->sortBy('assinado_em')->first();
        if (!$primeiraAssinatura) {
            return;
        }
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
