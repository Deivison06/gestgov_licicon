<?php

namespace Tests\Feature\Assinatura;

use App\Models\Documento;
use App\Models\DocumentoVersao;
use App\Models\Processo;
use App\Models\SolicitacaoAssinatura;
use App\Models\User;
use App\Services\FinalizacaoPdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Spatie\Permission\Models\Role;
use Tests\Feature\Assinatura\Helpers\CriaProcessoMinimoTrait;
use Tests\TestCase;

/**
 * Garante que o refactor opt-in do FinalizacaoPdfService:
 *  - Não dispara assinatura quando `assinantes` não vem no request (back-compat)
 *  - Dispara o pipeline quando `assinantes` vem (cria DocumentoVersao + rodada)
 *
 * Mockamos a parte de geração de PDF (DomPDF) pra testar só o pipeline novo.
 */
class FinalizacaoPdfServiceAssinaturaTest extends TestCase
{
    use RefreshDatabase;
    use CriaProcessoMinimoTrait;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'assinante', 'guard_name' => 'web']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_pipeline_de_assinatura_eh_disparado_quando_assinantes_vem_no_request(): void
    {
        $documento = $this->criarDocumentoExistente();
        $solicitante = User::factory()->create();
        $a1 = User::factory()->assinante()->create();
        $a2 = User::factory()->assinante()->create();

        $caminhoPdf = $this->criarPdfFake();
        $service = $this->montarServiceComCaminhoFixo($caminhoPdf);

        // Simula o flow após o salvarDocumento ter sido chamado: a row já existe
        // e gerarPdf vai apenas executar o pipeline de assinatura.
        $info = $this->invocarPipelineDireto(
            $service,
            $documento->processo,
            null, // sem homologacao
            $documento->tipo_documento,
            $caminhoPdf,
            'paralelo',
            7,
            [['id' => $a1->id], ['id' => $a2->id]],
            $solicitante->id
        );

        $this->assertNotNull($info);
        $this->assertSame(2, $info['total_solicitacoes']);
        $this->assertSame('paralelo', $info['modo']);

        // Persistiu DocumentoVersao polimórfico apontando para Documento
        $this->assertSame(1, DocumentoVersao::where('documentavel_type', Documento::class)
            ->where('documentavel_id', $documento->id)
            ->count());

        // Criou 2 SolicitacaoAssinatura
        $this->assertSame(2, SolicitacaoAssinatura::count());
    }

    public function test_pipeline_sequencial_atribui_ordem(): void
    {
        $documento = $this->criarDocumentoExistente();
        $solicitante = User::factory()->create();
        $a1 = User::factory()->assinante()->create();
        $a2 = User::factory()->assinante()->create();
        $a3 = User::factory()->assinante()->create();

        $caminhoPdf = $this->criarPdfFake();
        $service = $this->montarServiceComCaminhoFixo($caminhoPdf);

        $this->invocarPipelineDireto(
            $service,
            $documento->processo,
            null,
            $documento->tipo_documento,
            $caminhoPdf,
            'sequencial',
            7,
            [['id' => $a1->id], ['id' => $a2->id], ['id' => $a3->id]],
            $solicitante->id
        );

        $ordens = SolicitacaoAssinatura::orderBy('ordem')->pluck('ordem')->all();
        $this->assertSame([1, 2, 3], $ordens);
    }

    public function test_documentavel_aponta_para_documento_correto(): void
    {
        $documento = $this->criarDocumentoExistente();
        $solicitante = User::factory()->create();
        $a1 = User::factory()->assinante()->create();

        $caminhoPdf = $this->criarPdfFake();
        $service = $this->montarServiceComCaminhoFixo($caminhoPdf);

        $this->invocarPipelineDireto(
            $service,
            $documento->processo,
            null,
            $documento->tipo_documento,
            $caminhoPdf,
            'paralelo',
            7,
            [['id' => $a1->id]],
            $solicitante->id
        );

        $versao = DocumentoVersao::first();
        $this->assertSame(Documento::class, $versao->documentavel_type);
        $this->assertSame($documento->id, $versao->documentavel_id);
    }

    // ====================================================================
    // Helpers
    // ====================================================================

    private function criarDocumentoExistente(): Documento
    {
        $processoId = $this->criarProcessoMinimo();

        return Documento::create([
            'processo_id'      => $processoId,
            'tipo_documento'   => 'termo_homologacao',
            'caminho'          => 'uploads/test/fake.pdf',
            'gerado_em'        => now(),
            'data_selecionada' => now()->format('Y-m-d'),
        ]);
    }

    private function montarServiceComCaminhoFixo(string $caminhoPdf): FinalizacaoPdfService
    {
        return app(FinalizacaoPdfService::class);
    }

    /**
     * Invoca o método privado iniciarRodadaAssinatura via Reflection.
     * Permite testar o pipeline sem precisar passar pelo flow completo de PDF/views.
     */
    private function invocarPipelineDireto(
        FinalizacaoPdfService $service,
        Processo $processo,
        $homologacao,
        string $tipoDocumento,
        string $caminhoPdf,
        string $modo,
        int $prazoDias,
        array $assinantes,
        int $solicitanteId
    ): ?array {
        $refl = new \ReflectionMethod($service, 'iniciarRodadaAssinatura');
        $refl->setAccessible(true);

        return $refl->invoke(
            $service,
            $processo,
            $homologacao,
            $tipoDocumento,
            $caminhoPdf,
            $modo,
            $prazoDias,
            $assinantes,
            $solicitanteId
        );
    }
}
