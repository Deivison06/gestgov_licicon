<?php

namespace Tests\Feature\Assinatura;

use App\Models\Documento;
use App\Models\DocumentoVersao;
use App\Models\SolicitacaoAssinatura;
use App\Models\User;
use App\Services\AtaPdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\Feature\Assinatura\Helpers\CriaProcessoMinimoTrait;
use Tests\TestCase;

class AtaPdfServiceAssinaturaTest extends TestCase
{
    use RefreshDatabase;
    use CriaProcessoMinimoTrait;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'assinante', 'guard_name' => 'web']);
    }

    public function test_pipeline_eh_disparado_quando_rodada_assinantes_vem(): void
    {
        $documento = $this->criarDocumentoExistente();
        $solicitante = User::factory()->create();
        $a1 = User::factory()->assinante()->create();
        $a2 = User::factory()->assinante()->create();

        $caminhoPdf = $this->criarPdfFake();
        $service = app(AtaPdfService::class);

        $info = $this->invocarPipelineDireto(
            $service,
            \App\Models\Processo::find($documento->processo_id),
            $caminhoPdf,
            'paralelo',
            7,
            [['id' => $a1->id], ['id' => $a2->id]],
            $solicitante->id
        );

        $this->assertSame(2, $info['total_solicitacoes']);
        $this->assertSame('paralelo', $info['modo']);

        $versao = DocumentoVersao::first();
        $this->assertSame(Documento::class, $versao->documentavel_type);
        $this->assertSame($documento->id, $versao->documentavel_id);

        $this->assertSame(2, SolicitacaoAssinatura::count());
    }

    public function test_pipeline_sequencial_atribui_ordem(): void
    {
        $documento = $this->criarDocumentoExistente();
        $solicitante = User::factory()->create();
        $assinantes = User::factory()->assinante()->count(3)->create();

        $caminhoPdf = $this->criarPdfFake();
        $service = app(AtaPdfService::class);

        $this->invocarPipelineDireto(
            $service,
            \App\Models\Processo::find($documento->processo_id),
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

    private function criarDocumentoExistente(): Documento
    {
        $processoId = $this->criarProcessoMinimo();

        return Documento::create([
            'processo_id'      => $processoId,
            'tipo_documento'   => 'contrato',
            'caminho'          => 'uploads/test/fake.pdf',
            'gerado_em'        => now(),
            'data_selecionada' => now()->format('Y-m-d'),
        ]);
    }

    private function invocarPipelineDireto(
        AtaPdfService $service,
        \App\Models\Processo $processo,
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
            $caminhoPdf,
            $modo,
            $prazoDias,
            $assinantes,
            $solicitanteId
        );
    }
}
