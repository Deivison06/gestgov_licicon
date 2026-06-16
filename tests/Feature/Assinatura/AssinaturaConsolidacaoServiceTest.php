<?php

namespace Tests\Feature\Assinatura;

use App\Models\AssinaturaDigital;
use App\Models\AssinaturaLog;
use App\Models\DocumentoVersao;
use App\Models\User;
use App\Services\Assinatura\AssinaturaConsolidacaoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AssinaturaConsolidacaoServiceTest extends TestCase
{
    use RefreshDatabase;

    private AssinaturaConsolidacaoService $service;
    private string $pdfRascunho;
    private array $arquivosTemporarios = [];

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'assinante', 'guard_name' => 'web']);
        $this->service = app(AssinaturaConsolidacaoService::class);
        $this->pdfRascunho = $this->gerarPdfDeTeste();
        $this->arquivosTemporarios[] = $this->pdfRascunho;
    }

    protected function tearDown(): void
    {
        foreach ($this->arquivosTemporarios as $arq) {
            @unlink($arq);
            @unlink(preg_replace('/\.pdf$/i', '_assinado.pdf', $arq));
        }
        parent::tearDown();
    }

    public function test_consolidar_gera_pdf_assinado_e_atualiza_versao(): void
    {
        $versao = DocumentoVersao::factory()->create([
            'caminho_pdf'  => $this->pdfRascunho,
            'hash_sha256'  => hash_file('sha256', $this->pdfRascunho),
        ]);
        AssinaturaDigital::factory()->count(2)->create(['documento_versao_id' => $versao->id]);

        $caminho = $this->service->consolidar($versao);
        $this->arquivosTemporarios[] = $caminho;

        $this->assertFileExists($caminho);
        $this->assertStringEndsWith('_assinado.pdf', $caminho);

        $versao->refresh();
        $this->assertSame($caminho, $versao->caminho_pdf_assinado);
        $this->assertSame(64, strlen($versao->hash_pdf_assinado));
        $this->assertNotNull($versao->assinaturas_consolidadas_em);
    }

    public function test_consolidar_eh_idempotente(): void
    {
        $versao = DocumentoVersao::factory()->create([
            'caminho_pdf' => $this->pdfRascunho,
            'hash_sha256' => hash_file('sha256', $this->pdfRascunho),
        ]);
        AssinaturaDigital::factory()->create(['documento_versao_id' => $versao->id]);

        $caminho1 = $this->service->consolidar($versao);
        $this->arquivosTemporarios[] = $caminho1;
        $mtime1 = filemtime($caminho1);

        clearstatcache();
        sleep(1); // garante diferença de mtime

        $caminho2 = $this->service->consolidar($versao->refresh());

        $this->assertSame($caminho1, $caminho2);
        $this->assertSame($mtime1, filemtime($caminho2)); // não foi reescrito
    }

    public function test_consolidar_dispara_excecao_se_versao_nao_tem_assinatura(): void
    {
        $versao = DocumentoVersao::factory()->create([
            'caminho_pdf' => $this->pdfRascunho,
        ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('nada para consolidar');

        $this->service->consolidar($versao);
    }

    public function test_consolidar_dispara_excecao_se_pdf_rascunho_nao_existe(): void
    {
        $versao = DocumentoVersao::factory()->create([
            'caminho_pdf' => '/tmp/inexistente.pdf',
        ]);
        AssinaturaDigital::factory()->create(['documento_versao_id' => $versao->id]);

        $this->expectException(\RuntimeException::class);
        $this->service->consolidar($versao);
    }

    public function test_consolidar_grava_log_de_consolidacao(): void
    {
        $versao = DocumentoVersao::factory()->create([
            'caminho_pdf' => $this->pdfRascunho,
            'hash_sha256' => hash_file('sha256', $this->pdfRascunho),
        ]);
        AssinaturaDigital::factory()->count(3)->create(['documento_versao_id' => $versao->id]);

        $this->service->consolidar($versao);
        $this->arquivosTemporarios[] = $versao->refresh()->caminho_pdf_assinado;

        $log = AssinaturaLog::where('documento_versao_id', $versao->id)
            ->whereJsonContains('metadados->tipo', 'consolidacao')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame(3, $log->metadados['total_assinaturas']);
    }

    public function test_pdf_assinado_tem_mais_paginas_que_rascunho(): void
    {
        $versao = DocumentoVersao::factory()->create([
            'caminho_pdf' => $this->pdfRascunho,
            'hash_sha256' => hash_file('sha256', $this->pdfRascunho),
        ]);
        AssinaturaDigital::factory()->create(['documento_versao_id' => $versao->id]);

        $paginasRascunho = $this->contarPaginas($this->pdfRascunho);
        $caminhoAssinado = $this->service->consolidar($versao);
        $this->arquivosTemporarios[] = $caminhoAssinado;
        $paginasAssinado = $this->contarPaginas($caminhoAssinado);

        $this->assertGreaterThan($paginasRascunho, $paginasAssinado);
    }

    // ====================================================================
    // Helpers
    // ====================================================================

    private function gerarPdfDeTeste(): string
    {
        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, 'Documento de teste para consolidacao', 0, 1, 'C');
        $pdf->Cell(0, 10, 'Conteudo da pagina 1', 0, 1, 'L');
        $pdf->AddPage();
        $pdf->Cell(0, 10, 'Conteudo da pagina 2', 0, 1, 'L');

        $caminho = tempnam(sys_get_temp_dir(), 'rascunho_') . '.pdf';
        $pdf->Output($caminho, 'F');

        return $caminho;
    }

    private function contarPaginas(string $caminho): int
    {
        $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
        return $pdf->setSourceFile($caminho);
    }
}
