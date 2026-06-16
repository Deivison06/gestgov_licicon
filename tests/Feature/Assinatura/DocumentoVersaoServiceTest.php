<?php

namespace Tests\Feature\Assinatura;

use App\Models\AssinaturaLog;
use App\Models\DocumentoVersao;
use App\Models\User;
use App\Services\Assinatura\DocumentoVersaoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentoVersaoServiceTest extends TestCase
{
    use RefreshDatabase;

    private DocumentoVersaoService $service;
    private User $user;
    private string $pdfTemp;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(DocumentoVersaoService::class);
        $this->user = User::factory()->create();

        // PDF stub — não precisa ser PDF de verdade pra calcular hash
        $this->pdfTemp = tempnam(sys_get_temp_dir(), 'docver_') . '.pdf';
        file_put_contents($this->pdfTemp, 'CONTEUDO PDF FAKE');
    }

    protected function tearDown(): void
    {
        @unlink($this->pdfTemp);
        parent::tearDown();
    }

    public function test_criar_rascunho_persiste_versao_e_loga(): void
    {
        // Um "documentavel" qualquer — usamos outro DocumentoVersao como mock só pra ter um Model.
        $documentavel = DocumentoVersao::factory()->create();

        $versao = $this->service->criarRascunho($documentavel, $this->pdfTemp, $this->user->id);

        $this->assertNotNull($versao->id);
        $this->assertSame(1, $versao->versao);
        $this->assertSame(64, strlen($versao->hash_sha256));
        $this->assertSame(get_class($documentavel), $versao->documentavel_type);
        $this->assertSame($documentavel->id, $versao->documentavel_id);

        // Log foi criado
        $this->assertSame(1, AssinaturaLog::where('documento_versao_id', $versao->id)
            ->where('acao', AssinaturaLog::ACAO_CRIADA)->count());
    }

    public function test_versao_incrementa_em_geracoes_subsequentes(): void
    {
        $documentavel = DocumentoVersao::factory()->create();

        $v1 = $this->service->criarRascunho($documentavel, $this->pdfTemp, $this->user->id);
        $v2 = $this->service->criarRascunho($documentavel, $this->pdfTemp, $this->user->id);
        $v3 = $this->service->criarRascunho($documentavel, $this->pdfTemp, $this->user->id);

        $this->assertSame(1, $v1->versao);
        $this->assertSame(2, $v2->versao);
        $this->assertSame(3, $v3->versao);
    }

    public function test_versao_eh_independente_por_documentavel(): void
    {
        $doc1 = DocumentoVersao::factory()->create();
        $doc2 = DocumentoVersao::factory()->create();

        $v1 = $this->service->criarRascunho($doc1, $this->pdfTemp, $this->user->id);
        $v2 = $this->service->criarRascunho($doc2, $this->pdfTemp, $this->user->id);

        $this->assertSame(1, $v1->versao);
        $this->assertSame(1, $v2->versao);
    }

    public function test_pdf_inexistente_dispara_excecao(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $documentavel = DocumentoVersao::factory()->create();
        $this->service->criarRascunho($documentavel, '/tmp/nao-existe.pdf', $this->user->id);
    }

    public function test_marcar_como_regerada_gera_log(): void
    {
        $documentavel = DocumentoVersao::factory()->create();
        $versao = $this->service->criarRascunho($documentavel, $this->pdfTemp, $this->user->id);

        $this->service->marcarComoRegerada($versao, $this->user->id);

        $this->assertSame(1, AssinaturaLog::where('documento_versao_id', $versao->id)
            ->where('acao', AssinaturaLog::ACAO_REGERADA)->count());
    }
}
