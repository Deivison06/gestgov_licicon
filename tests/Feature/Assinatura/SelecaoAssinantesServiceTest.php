<?php

namespace Tests\Feature\Assinatura;

use App\Models\Documento;
use App\Models\DocumentoSelecaoAssinantes;
use App\Models\DocumentoVersao;
use App\Models\Processo;
use App\Models\SolicitacaoAssinatura;
use App\Models\User;
use App\Services\Assinatura\SelecaoAssinantesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\Feature\Assinatura\Helpers\CriaProcessoMinimoTrait;
use Tests\TestCase;

class SelecaoAssinantesServiceTest extends TestCase
{
    use RefreshDatabase;
    use CriaProcessoMinimoTrait;

    private SelecaoAssinantesService $service;
    private User $operador;
    private Processo $processo;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'assinante', 'guard_name' => 'web']);
        $this->service  = app(SelecaoAssinantesService::class);
        $this->operador = User::factory()->create();
        $this->processo = Processo::find($this->criarProcessoMinimo());
    }

    // ====================================================================
    // Persistência (salvar/obter)
    // ====================================================================

    public function test_salvar_persiste_selecao_via_upsert(): void
    {
        $this->service->salvar(
            $this->processo,
            'edital',
            null,
            null,
            [
                'modo'       => 'paralelo',
                'prazo_dias' => 10,
                'assinantes' => [
                    ['responsavel' => 'Maria', 'unidade_nome' => 'Procuradoria'],
                ],
            ],
            $this->operador->id
        );

        $this->assertSame(1, DocumentoSelecaoAssinantes::count());

        // Reupsert — não cria nova row
        $this->service->salvar(
            $this->processo,
            'edital',
            null,
            null,
            [
                'modo'       => 'sequencial',
                'prazo_dias' => 5,
                'assinantes' => [['responsavel' => 'Maria']],
            ],
            $this->operador->id
        );

        $this->assertSame(1, DocumentoSelecaoAssinantes::count());
        $registro = DocumentoSelecaoAssinantes::first();
        $this->assertSame('sequencial', $registro->modo);
        $this->assertSame(5, $registro->prazo_dias);
    }

    public function test_obter_retorna_null_quando_nao_existe(): void
    {
        $resultado = $this->service->obter($this->processo, 'edital');
        $this->assertNull($resultado);
    }

    public function test_obter_retorna_selecao_salva(): void
    {
        $this->service->salvar(
            $this->processo,
            'termo_referencia',
            null,
            null,
            [
                'modo'       => 'paralelo',
                'prazo_dias' => 7,
                'assinantes' => [['responsavel' => 'João']],
            ],
            $this->operador->id
        );

        $selecao = $this->service->obter($this->processo, 'termo_referencia');
        $this->assertNotNull($selecao);
        $this->assertCount(1, $selecao->assinantes);
    }

    public function test_selecoes_para_documentos_diferentes_sao_independentes(): void
    {
        $this->service->salvar($this->processo, 'edital',           null, null,
            ['assinantes' => [['responsavel' => 'A']]], $this->operador->id);
        $this->service->salvar($this->processo, 'termo_referencia', null, null,
            ['assinantes' => [['responsavel' => 'B']]], $this->operador->id);

        $this->assertSame(2, DocumentoSelecaoAssinantes::count());
    }

    // ====================================================================
    // solicitarAssinatura
    // ====================================================================

    public function test_solicitar_dispara_excecao_se_nao_houver_selecao(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Nenhuma seleção');

        $this->service->solicitarAssinatura(
            $this->processo, 'edital', null, null, $this->operador->id
        );
    }

    public function test_solicitar_dispara_excecao_se_nenhum_assinante_encontrado_no_sistema(): void
    {
        $this->service->salvar(
            $this->processo,
            'edital',
            null,
            null,
            ['assinantes' => [['responsavel' => 'Não Existe no Sistema']]],
            $this->operador->id
        );

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Nenhum assinante da seleção foi encontrado');

        $this->service->solicitarAssinatura(
            $this->processo, 'edital', null, null, $this->operador->id
        );
    }

    public function test_solicitar_dispara_excecao_se_pdf_nao_foi_gerado(): void
    {
        $assinante = User::factory()->assinante()->create(['name' => 'Pedro']);

        $this->service->salvar(
            $this->processo,
            'edital',
            null,
            null,
            ['assinantes' => [['responsavel' => 'Pedro']]],
            $this->operador->id
        );

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('PDF do documento ainda não foi gerado');

        $this->service->solicitarAssinatura(
            $this->processo, 'edital', null, null, $this->operador->id
        );
    }

    public function test_solicitar_cria_versao_e_solicitacoes_quando_tudo_certo(): void
    {
        $assinante1 = User::factory()->assinante()->create(['name' => 'Carlos']);
        $assinante2 = User::factory()->assinante()->create(['name' => 'Diana']);

        // Cria um Documento gerado + PDF no disco
        $caminhoPdf = $this->criarPdfFake();
        $documento = Documento::create([
            'processo_id'      => $this->processo->id,
            'tipo_documento'   => 'edital',
            'caminho'          => $caminhoPdf,
            'gerado_em'        => now(),
            'data_selecionada' => now()->format('Y-m-d'),
        ]);

        $this->service->salvar(
            $this->processo,
            'edital',
            null,
            null,
            [
                'modo'       => 'paralelo',
                'prazo_dias' => 7,
                'assinantes' => [
                    ['responsavel' => 'Carlos'],
                    ['responsavel' => 'Diana'],
                ],
            ],
            $this->operador->id
        );

        $info = $this->service->solicitarAssinatura(
            $this->processo, 'edital', null, null, $this->operador->id
        );

        $this->assertSame(2, $info['total_solicitacoes']);
        $this->assertSame(0, $info['ignorados']);
        $this->assertSame(1, DocumentoVersao::count());
        $this->assertSame(2, SolicitacaoAssinatura::count());
    }

    public function test_solicitar_conta_ignorados_quando_nem_todos_existem(): void
    {
        User::factory()->assinante()->create(['name' => 'Existe']);

        $caminhoPdf = $this->criarPdfFake();
        Documento::create([
            'processo_id'      => $this->processo->id,
            'tipo_documento'   => 'edital',
            'caminho'          => $caminhoPdf,
            'gerado_em'        => now(),
            'data_selecionada' => now()->format('Y-m-d'),
        ]);

        $this->service->salvar(
            $this->processo,
            'edital',
            null,
            null,
            ['assinantes' => [
                ['responsavel' => 'Existe'],
                ['responsavel' => 'Fantasma'],
            ]],
            $this->operador->id
        );

        $info = $this->service->solicitarAssinatura(
            $this->processo, 'edital', null, null, $this->operador->id
        );

        $this->assertSame(1, $info['total_solicitacoes']);
        $this->assertSame(1, $info['ignorados']);
    }

    public function test_solicitar_bloqueia_se_ja_existe_rodada_ativa(): void
    {
        $assinante = User::factory()->assinante()->create(['name' => 'Eva']);

        $caminhoPdf = $this->criarPdfFake();
        Documento::create([
            'processo_id'      => $this->processo->id,
            'tipo_documento'   => 'edital',
            'caminho'          => $caminhoPdf,
            'gerado_em'        => now(),
            'data_selecionada' => now()->format('Y-m-d'),
        ]);

        $this->service->salvar(
            $this->processo, 'edital', null, null,
            ['assinantes' => [['responsavel' => 'Eva']]],
            $this->operador->id
        );

        // Primeira chamada ok
        $this->service->solicitarAssinatura($this->processo, 'edital', null, null, $this->operador->id);

        // Segunda chamada bloqueia
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Já existe uma rodada de assinatura em andamento');
        $this->service->solicitarAssinatura($this->processo, 'edital', null, null, $this->operador->id);
    }
}
