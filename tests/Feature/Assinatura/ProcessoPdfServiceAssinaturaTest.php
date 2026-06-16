<?php

namespace Tests\Feature\Assinatura;

use App\Models\Documento;
use App\Models\DocumentoVersao;
use App\Models\SolicitacaoAssinatura;
use App\Models\User;
use App\Services\ProcessoPdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\Feature\Assinatura\Helpers\CriaProcessoMinimoTrait;
use Tests\TestCase;

class ProcessoPdfServiceAssinaturaTest extends TestCase
{
    use RefreshDatabase;
    use CriaProcessoMinimoTrait;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'assinante', 'guard_name' => 'web']);
    }

    public function test_pipeline_dispara_e_grava_documentavel_em_documento(): void
    {
        $documento = $this->criarDocumentoExistente('edital');
        $solicitante = User::factory()->create();
        $a1 = User::factory()->assinante()->create();

        $caminhoPdf = $this->criarPdfFake();
        $service = app(ProcessoPdfService::class);

        $info = $this->invocarPipelineDireto(
            $service,
            \App\Models\Processo::find($documento->processo_id),
            $documento->tipo_documento,
            $caminhoPdf,
            'paralelo',
            5,
            [['id' => $a1->id]],
            $solicitante->id
        );

        $this->assertNotNull($info);
        $this->assertSame(1, $info['total_solicitacoes']);

        $versao = DocumentoVersao::first();
        $this->assertSame(Documento::class, $versao->documentavel_type);
        $this->assertSame($documento->id, $versao->documentavel_id);

        $this->assertSame(1, SolicitacaoAssinatura::count());
    }

    public function test_pipeline_respeita_modo_sequencial(): void
    {
        $documento = $this->criarDocumentoExistente('edital');
        $solicitante = User::factory()->create();
        $assinantes = User::factory()->assinante()->count(3)->create();

        $caminhoPdf = $this->criarPdfFake();
        $service = app(ProcessoPdfService::class);

        $this->invocarPipelineDireto(
            $service,
            \App\Models\Processo::find($documento->processo_id),
            $documento->tipo_documento,
            $caminhoPdf,
            'sequencial',
            7,
            $assinantes->map(fn ($u) => ['id' => $u->id])->all(),
            $solicitante->id
        );

        $this->assertSame([1, 2, 3], SolicitacaoAssinatura::orderBy('ordem')->pluck('ordem')->all());
    }

    // ====================================================================
    // Helpers
    // ====================================================================

    private function criarDocumentoExistente(string $tipo): Documento
    {
        $processoId = $this->criarProcessoMinimo();

        return Documento::create([
            'processo_id'      => $processoId,
            'tipo_documento'   => $tipo,
            'caminho'          => 'uploads/test/fake.pdf',
            'gerado_em'        => now(),
            'data_selecionada' => now()->format('Y-m-d'),
        ]);
    }

    private function invocarPipelineDireto(
        ProcessoPdfService $service,
        \App\Models\Processo $processo,
        string $tipo,
        string $caminhoPdf,
        string $modo,
        int $prazoDias,
        array $assinantes,
        int $solicitanteId
    ): array {
        $refl = new \ReflectionMethod($service, 'iniciarRodadaAssinatura');
        $refl->setAccessible(true);

        return $refl->invoke(
            $service,
            $processo,
            $tipo,
            $caminhoPdf,
            $modo,
            $prazoDias,
            $assinantes,
            $solicitanteId
        );
    }
}
