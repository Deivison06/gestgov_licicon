<?php

namespace Tests\Feature\Assinatura;

use App\Models\AssinaturaDigital;
use App\Models\AssinaturaLog;
use App\Models\DocumentoVersao;
use App\Models\SolicitacaoAssinatura;
use App\Models\User;
use App\Services\Assinatura\SolicitacaoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SolicitacaoServiceTest extends TestCase
{
    use RefreshDatabase;

    private SolicitacaoService $service;
    private User $solicitante;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'assinante', 'guard_name' => 'web']);
        $this->service = app(SolicitacaoService::class);
        $this->solicitante = User::factory()->create();
    }

    public function test_criar_rodada_paralela_cria_n_solicitacoes_pendentes(): void
    {
        $versao = DocumentoVersao::factory()->create();
        $assinantes = User::factory()->assinante()->count(3)->create();

        $criadas = $this->service->criarRodada(
            $versao,
            $assinantes->map(fn ($u) => ['user_id' => $u->id])->all(),
            $this->solicitante->id
        );

        $this->assertCount(3, $criadas);
        foreach ($criadas as $sol) {
            $this->assertSame(SolicitacaoAssinatura::STATUS_PENDENTE, $sol->status);
            $this->assertSame(0, $sol->ordem); // paralelo
            $this->assertNotNull($sol->token_acesso);
            $this->assertSame(64, strlen($sol->token_acesso));
        }
    }

    public function test_criar_rodada_sequencial_respeita_ordem(): void
    {
        $versao = DocumentoVersao::factory()->create();
        $assinantes = User::factory()->assinante()->count(3)->create();

        $criadas = $this->service->criarRodada(
            $versao,
            [
                ['user_id' => $assinantes[0]->id, 'ordem' => 1],
                ['user_id' => $assinantes[1]->id, 'ordem' => 2],
                ['user_id' => $assinantes[2]->id, 'ordem' => 3],
            ],
            $this->solicitante->id
        );

        $this->assertSame(1, $criadas[0]->ordem);
        $this->assertSame(2, $criadas[1]->ordem);
        $this->assertSame(3, $criadas[2]->ordem);
    }

    public function test_user_sem_flag_assinante_dispara_excecao(): void
    {
        $versao = DocumentoVersao::factory()->create();
        $userComum = User::factory()->create(['is_assinante' => false]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('não está marcado como assinante');

        $this->service->criarRodada(
            $versao,
            [['user_id' => $userComum->id]],
            $this->solicitante->id
        );
    }

    public function test_versao_com_assinatura_nao_aceita_nova_rodada(): void
    {
        $versao = DocumentoVersao::factory()->create();
        AssinaturaDigital::factory()->create(['documento_versao_id' => $versao->id]);

        $assinante = User::factory()->assinante()->create();

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('crie uma nova versão');

        $this->service->criarRodada(
            $versao,
            [['user_id' => $assinante->id]],
            $this->solicitante->id
        );
    }

    public function test_cada_solicitacao_gera_log(): void
    {
        $versao = DocumentoVersao::factory()->create();
        $assinantes = User::factory()->assinante()->count(2)->create();

        $this->service->criarRodada(
            $versao,
            $assinantes->map(fn ($u) => ['user_id' => $u->id])->all(),
            $this->solicitante->id
        );

        $this->assertSame(2, AssinaturaLog::where('documento_versao_id', $versao->id)
            ->where('acao', AssinaturaLog::ACAO_CRIADA)->count());
    }

    public function test_cancelar_rodada_marca_pendentes_como_canceladas(): void
    {
        $versao = DocumentoVersao::factory()->create();
        $assinantes = User::factory()->assinante()->count(3)->create();

        $this->service->criarRodada(
            $versao,
            $assinantes->map(fn ($u) => ['user_id' => $u->id])->all(),
            $this->solicitante->id
        );

        $afetadas = $this->service->cancelarRodada($versao, $this->solicitante->id, 'teste');

        $this->assertSame(3, $afetadas);
        $this->assertSame(3, SolicitacaoAssinatura::where('documento_versao_id', $versao->id)
            ->where('status', SolicitacaoAssinatura::STATUS_CANCELADA)->count());
    }

    public function test_lista_vazia_dispara_excecao(): void
    {
        $versao = DocumentoVersao::factory()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->service->criarRodada($versao, [], $this->solicitante->id);
    }
}
