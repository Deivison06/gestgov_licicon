<?php

namespace Tests\Feature\Assinatura;

use App\Models\AssinaturaLog;
use App\Models\DocumentoVersao;
use App\Models\SolicitacaoAssinatura;
use App\Models\User;
use App\Notifications\Assinatura\DocumentoTotalmenteAssinado;
use App\Notifications\Assinatura\NovaSolicitacaoAssinatura;
use App\Notifications\Assinatura\SolicitacaoExpirando;
use App\Notifications\Assinatura\SolicitacaoRecusada;
use App\Services\Assinatura\AssinaturaService;
use App\Services\Assinatura\SolicitacaoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NotificacaoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'assinante', 'guard_name' => 'web']);
    }

    // ====================================================================
    // Disparos automáticos
    // ====================================================================

    public function test_criar_rodada_paralela_notifica_todos(): void
    {
        Notification::fake();

        $versao = DocumentoVersao::factory()->create();
        $solicitante = User::factory()->create();
        $a1 = User::factory()->assinante()->create();
        $a2 = User::factory()->assinante()->create();

        app(SolicitacaoService::class)->criarRodada(
            $versao,
            [['user_id' => $a1->id], ['user_id' => $a2->id]],
            $solicitante->id
        );

        Notification::assertSentTo($a1, NovaSolicitacaoAssinatura::class);
        Notification::assertSentTo($a2, NovaSolicitacaoAssinatura::class);
    }

    public function test_criar_rodada_sequencial_notifica_apenas_o_primeiro(): void
    {
        Notification::fake();

        $versao = DocumentoVersao::factory()->create();
        $solicitante = User::factory()->create();
        $a1 = User::factory()->assinante()->create();
        $a2 = User::factory()->assinante()->create();
        $a3 = User::factory()->assinante()->create();

        app(SolicitacaoService::class)->criarRodada(
            $versao,
            [
                ['user_id' => $a1->id, 'ordem' => 1],
                ['user_id' => $a2->id, 'ordem' => 2],
                ['user_id' => $a3->id, 'ordem' => 3],
            ],
            $solicitante->id
        );

        Notification::assertSentTo($a1, NovaSolicitacaoAssinatura::class);
        Notification::assertNotSentTo($a2, NovaSolicitacaoAssinatura::class);
        Notification::assertNotSentTo($a3, NovaSolicitacaoAssinatura::class);
    }

    public function test_assinar_em_sequencial_notifica_proximo_da_fila(): void
    {
        Notification::fake();

        $versao = DocumentoVersao::factory()->create();
        $a1 = User::factory()->assinante()->create();
        $a2 = User::factory()->assinante()->create();

        $s1 = SolicitacaoAssinatura::factory()->sequencial(1)->create([
            'documento_versao_id' => $versao->id,
            'assinante_user_id'   => $a1->id,
        ]);
        SolicitacaoAssinatura::factory()->sequencial(2)->create([
            'documento_versao_id' => $versao->id,
            'assinante_user_id'   => $a2->id,
        ]);

        app(AssinaturaService::class)->assinar($s1, $a1, '127.0.0.1', 'ua');

        Notification::assertSentTo($a2, NovaSolicitacaoAssinatura::class);
    }

    public function test_ultima_assinatura_notifica_o_operador(): void
    {
        Notification::fake();

        $operador = User::factory()->create();
        $versao = DocumentoVersao::factory()->create(['gerado_por_user_id' => $operador->id]);
        $assinante = User::factory()->assinante()->create();
        $s = SolicitacaoAssinatura::factory()->create([
            'documento_versao_id' => $versao->id,
            'assinante_user_id'   => $assinante->id,
        ]);

        app(AssinaturaService::class)->assinar($s, $assinante, '127.0.0.1', 'ua');

        Notification::assertSentTo($operador, DocumentoTotalmenteAssinado::class);
    }

    public function test_recusar_notifica_o_solicitante(): void
    {
        Notification::fake();

        $solicitante = User::factory()->create();
        $assinante = User::factory()->assinante()->create();
        $s = SolicitacaoAssinatura::factory()->create([
            'assinante_user_id'      => $assinante->id,
            'solicitado_por_user_id' => $solicitante->id,
        ]);

        app(AssinaturaService::class)->recusar($s, $assinante, 'Valor errado', '127.0.0.1', 'ua');

        Notification::assertSentTo($solicitante, SolicitacaoRecusada::class);
    }

    // ====================================================================
    // Endpoints HTTP do sininho
    // ====================================================================

    public function test_endpoint_index_retorna_count_e_ultimas(): void
    {
        $user = User::factory()->create();

        // Cria solicitações + notifica
        $assinante = User::factory()->assinante()->create();
        $sol = SolicitacaoAssinatura::factory()->create(['assinante_user_id' => $assinante->id]);
        $assinante->notify(new NovaSolicitacaoAssinatura($sol));

        $response = $this->actingAs($assinante)->getJson(route('notificacoes.index'));

        $response->assertOk();
        $response->assertJsonPath('count', 1);
        $response->assertJsonCount(1, 'ultimas');
    }

    public function test_endpoint_marcar_todas_lidas_zera_count(): void
    {
        $assinante = User::factory()->assinante()->create();
        $sol = SolicitacaoAssinatura::factory()->create(['assinante_user_id' => $assinante->id]);
        $assinante->notify(new NovaSolicitacaoAssinatura($sol));

        $this->actingAs($assinante)
            ->postJson(route('notificacoes.marcar-todas-lidas'))
            ->assertOk();

        $this->actingAs($assinante)
            ->getJson(route('notificacoes.index'))
            ->assertJsonPath('count', 0);
    }

    // ====================================================================
    // Command de expiração
    // ====================================================================

    public function test_command_expira_solicitacoes_vencidas(): void
    {
        $assinante = User::factory()->assinante()->create();

        SolicitacaoAssinatura::factory()->count(2)->create([
            'assinante_user_id' => $assinante->id,
            'expires_at'        => now()->subDay(),
        ]);

        // Uma com prazo no futuro — não deve ser expirada
        $futura = SolicitacaoAssinatura::factory()->create([
            'assinante_user_id' => $assinante->id,
            'expires_at'        => now()->addDays(3),
        ]);

        $this->artisan('assinaturas:expirar-pendentes', ['--apenas-expirar' => true])
            ->assertSuccessful();

        $this->assertSame(2, SolicitacaoAssinatura::where('status', SolicitacaoAssinatura::STATUS_EXPIRADA)->count());
        $futura->refresh();
        $this->assertSame(SolicitacaoAssinatura::STATUS_PENDENTE, $futura->status);

        // Log foi criado
        $this->assertSame(2, AssinaturaLog::where('acao', AssinaturaLog::ACAO_EXPIRADA)->count());
    }

    public function test_command_dispara_lembretes_24h_antes(): void
    {
        Notification::fake();

        $assinante = User::factory()->assinante()->create();
        $solExpirandoHoje = SolicitacaoAssinatura::factory()->create([
            'assinante_user_id' => $assinante->id,
            'expires_at'        => now()->addHours(20), // expira em 20h
        ]);

        $this->artisan('assinaturas:expirar-pendentes', ['--apenas-lembrete' => true])
            ->assertSuccessful();

        Notification::assertSentTo($assinante, SolicitacaoExpirando::class);
    }

    public function test_command_lembrete_eh_idempotente(): void
    {
        $assinante = User::factory()->assinante()->create();
        $sol = SolicitacaoAssinatura::factory()->create([
            'assinante_user_id' => $assinante->id,
            'expires_at'        => now()->addHours(12),
        ]);

        // Roda 2x — só 1 notificação deve ser registrada (via log)
        $this->artisan('assinaturas:expirar-pendentes', ['--apenas-lembrete' => true])->run();
        $this->artisan('assinaturas:expirar-pendentes', ['--apenas-lembrete' => true])->run();

        $this->assertSame(1, $assinante->notifications()->count());
    }
}
