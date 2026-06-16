<?php

namespace Tests\Feature\Assinatura;

use App\Models\AssinaturaDigital;
use App\Models\AssinaturaLog;
use App\Models\DocumentoVersao;
use App\Models\SolicitacaoAssinatura;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Cobertura do CRUD HTTP do assinante:
 *   /minhas-assinaturas              [GET]    index — lista pendentes + histórico
 *   /minhas-assinaturas/{id}         [GET]    show — visualizar + ações
 *   /minhas-assinaturas/{id}/assinar [POST]   integra AssinaturaService::assinar
 *   /minhas-assinaturas/{id}/recusar [POST]   integra AssinaturaService::recusar
 */
class AssinaturaControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $assinante;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'assinante', 'guard_name' => 'web']);
        $this->assinante = User::factory()->assinante()->create([
            'email_verified_at' => now(),
        ]);
    }

    public function test_index_requer_autenticacao(): void
    {
        $this->get(route('minhas-assinaturas.index'))->assertRedirect(route('login'));
    }

    public function test_index_lista_apenas_solicitacoes_proprias(): void
    {
        $outro = User::factory()->assinante()->create();

        SolicitacaoAssinatura::factory()->count(2)->create(['assinante_user_id' => $this->assinante->id]);
        SolicitacaoAssinatura::factory()->count(3)->create(['assinante_user_id' => $outro->id]);

        $response = $this->actingAs($this->assinante)->get(route('minhas-assinaturas.index'));

        $response->assertOk();
        $response->assertViewHas('pendentes', function ($paginator) {
            return $paginator->total() === 2;
        });
    }

    public function test_show_acessivel_apenas_ao_assinante_da_solicitacao(): void
    {
        $outro = User::factory()->assinante()->create();
        $solicitacao = SolicitacaoAssinatura::factory()->create(['assinante_user_id' => $outro->id]);

        $this->actingAs($this->assinante)
            ->get(route('minhas-assinaturas.show', $solicitacao->id))
            ->assertForbidden();
    }

    public function test_show_grava_log_de_visualizacao(): void
    {
        $solicitacao = SolicitacaoAssinatura::factory()->create([
            'assinante_user_id' => $this->assinante->id,
        ]);

        $this->actingAs($this->assinante)
            ->get(route('minhas-assinaturas.show', $solicitacao->id))
            ->assertOk();

        $this->assertSame(1, AssinaturaLog::where('acao', AssinaturaLog::ACAO_VISUALIZADA)
            ->where('solicitacao_assinatura_id', $solicitacao->id)->count());
    }

    public function test_assinar_endpoint_registra_assinatura(): void
    {
        $solicitacao = SolicitacaoAssinatura::factory()->create([
            'assinante_user_id' => $this->assinante->id,
        ]);

        $this->actingAs($this->assinante)
            ->post(route('minhas-assinaturas.assinar', $solicitacao->id))
            ->assertRedirect(route('minhas-assinaturas.index'));

        $this->assertSame(1, AssinaturaDigital::where('solicitacao_assinatura_id', $solicitacao->id)->count());
        $solicitacao->refresh();
        $this->assertSame(SolicitacaoAssinatura::STATUS_ASSINADA, $solicitacao->status);
    }

    public function test_assinar_de_outro_dispara_403(): void
    {
        $outro = User::factory()->assinante()->create();
        $solicitacao = SolicitacaoAssinatura::factory()->create(['assinante_user_id' => $outro->id]);

        $this->actingAs($this->assinante)
            ->post(route('minhas-assinaturas.assinar', $solicitacao->id))
            ->assertForbidden();

        $this->assertSame(0, AssinaturaDigital::count());
    }

    public function test_recusar_endpoint_requer_motivo(): void
    {
        $solicitacao = SolicitacaoAssinatura::factory()->create([
            'assinante_user_id' => $this->assinante->id,
        ]);

        $this->actingAs($this->assinante)
            ->post(route('minhas-assinaturas.recusar', $solicitacao->id), ['motivo' => ''])
            ->assertSessionHasErrors('motivo');

        $solicitacao->refresh();
        $this->assertSame(SolicitacaoAssinatura::STATUS_PENDENTE, $solicitacao->status);
    }

    public function test_recusar_endpoint_cancela_rodada(): void
    {
        $versao = DocumentoVersao::factory()->create();
        $outroAssinante = User::factory()->assinante()->create();

        $s1 = SolicitacaoAssinatura::factory()->create([
            'documento_versao_id' => $versao->id,
            'assinante_user_id'   => $this->assinante->id,
        ]);
        $s2 = SolicitacaoAssinatura::factory()->create([
            'documento_versao_id' => $versao->id,
            'assinante_user_id'   => $outroAssinante->id,
        ]);

        $this->actingAs($this->assinante)
            ->post(route('minhas-assinaturas.recusar', $s1->id), ['motivo' => 'Valor incorreto na cláusula 3'])
            ->assertRedirect(route('minhas-assinaturas.index'));

        $s1->refresh(); $s2->refresh();
        $this->assertSame(SolicitacaoAssinatura::STATUS_RECUSADA, $s1->status);
        $this->assertSame(SolicitacaoAssinatura::STATUS_CANCELADA, $s2->status);
        $this->assertSame('Valor incorreto na cláusula 3', $s1->motivo_recusa);
    }

    public function test_pdf_endpoint_retorna_404_se_arquivo_nao_existe(): void
    {
        $solicitacao = SolicitacaoAssinatura::factory()->create([
            'assinante_user_id' => $this->assinante->id,
        ]);
        // versão criada pelo factory tem caminho falso (uploads/test/...)

        $this->actingAs($this->assinante)
            ->get(route('minhas-assinaturas.pdf', $solicitacao->id))
            ->assertNotFound();
    }
}
