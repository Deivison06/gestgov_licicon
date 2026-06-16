<?php

namespace Tests\Feature\Assinatura;

use App\Models\AssinaturaDigital;
use App\Models\AssinaturaLog;
use App\Models\DocumentoVersao;
use App\Models\SolicitacaoAssinatura;
use App\Models\User;
use App\Services\Assinatura\AssinaturaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AssinaturaServiceTest extends TestCase
{
    use RefreshDatabase;

    private AssinaturaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'assinante', 'guard_name' => 'web']);
        $this->service = app(AssinaturaService::class);
    }

    // ====================================================================
    // assinar()
    // ====================================================================

    public function test_assinar_cria_assinatura_digital_e_marca_solicitacao(): void
    {
        $assinante = User::factory()->assinante()->create();
        $solicitacao = SolicitacaoAssinatura::factory()->create([
            'assinante_user_id' => $assinante->id,
        ]);

        $assinatura = $this->service->assinar($solicitacao, $assinante, '127.0.0.1', 'PHPUnit');

        $this->assertInstanceOf(AssinaturaDigital::class, $assinatura);
        $this->assertSame(20, strlen($assinatura->codigo_verificador));
        $this->assertNull($assinatura->hash_cadeia_anterior); // primeira da cadeia
        $this->assertNotNull($assinatura->hash_proprio);

        $solicitacao->refresh();
        $this->assertSame(SolicitacaoAssinatura::STATUS_ASSINADA, $solicitacao->status);
        $this->assertNotNull($solicitacao->processada_em);
    }

    public function test_assinar_loga_acao_com_codigo_verificador(): void
    {
        $assinante = User::factory()->assinante()->create();
        $solicitacao = SolicitacaoAssinatura::factory()->create([
            'assinante_user_id' => $assinante->id,
        ]);

        $assinatura = $this->service->assinar($solicitacao, $assinante, '10.0.0.1', 'TestUA');

        $log = AssinaturaLog::where('acao', AssinaturaLog::ACAO_ASSINADA)
            ->where('solicitacao_assinatura_id', $solicitacao->id)
            ->first();

        $this->assertNotNull($log);
        $this->assertSame($assinatura->codigo_verificador, $log->metadados['codigo_verificador']);
    }

    public function test_assinar_constroi_cadeia_de_hash_corretamente(): void
    {
        $versao = DocumentoVersao::factory()->create();
        $a1 = User::factory()->assinante()->create();
        $a2 = User::factory()->assinante()->create();

        $s1 = SolicitacaoAssinatura::factory()->create([
            'documento_versao_id' => $versao->id,
            'assinante_user_id'   => $a1->id,
            'ordem'               => 0,
        ]);
        $s2 = SolicitacaoAssinatura::factory()->create([
            'documento_versao_id' => $versao->id,
            'assinante_user_id'   => $a2->id,
            'ordem'               => 0,
        ]);

        $ass1 = $this->service->assinar($s1, $a1, '127.0.0.1', 'ua');
        $ass2 = $this->service->assinar($s2, $a2, '127.0.0.1', 'ua');

        // Segunda assinatura tem hash_cadeia_anterior = hash_proprio da primeira
        $this->assertNull($ass1->hash_cadeia_anterior);
        $this->assertSame($ass1->hash_proprio, $ass2->hash_cadeia_anterior);
    }

    public function test_assinar_marca_versao_consolidada_quando_eh_a_ultima(): void
    {
        $versao = DocumentoVersao::factory()->create();
        $a1 = User::factory()->assinante()->create();
        $a2 = User::factory()->assinante()->create();

        $s1 = SolicitacaoAssinatura::factory()->create([
            'documento_versao_id' => $versao->id,
            'assinante_user_id'   => $a1->id,
        ]);
        $s2 = SolicitacaoAssinatura::factory()->create([
            'documento_versao_id' => $versao->id,
            'assinante_user_id'   => $a2->id,
        ]);

        $this->service->assinar($s1, $a1, '127.0.0.1', 'ua');
        $versao->refresh();
        $this->assertNull($versao->assinaturas_consolidadas_em);

        $this->service->assinar($s2, $a2, '127.0.0.1', 'ua');
        $versao->refresh();
        $this->assertNotNull($versao->assinaturas_consolidadas_em);
    }

    public function test_assinar_falha_quando_user_nao_eh_o_assinante_da_solicitacao(): void
    {
        $assinanteCorreto = User::factory()->assinante()->create();
        $outroAssinante   = User::factory()->assinante()->create();

        $solicitacao = SolicitacaoAssinatura::factory()->create([
            'assinante_user_id' => $assinanteCorreto->id,
        ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('não pertence a este usuário');

        $this->service->assinar($solicitacao, $outroAssinante, '127.0.0.1', 'ua');
    }

    public function test_assinar_falha_em_solicitacao_ja_assinada(): void
    {
        $assinante = User::factory()->assinante()->create();
        $solicitacao = SolicitacaoAssinatura::factory()->assinada()->create([
            'assinante_user_id' => $assinante->id,
        ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('status atual: assinada');

        $this->service->assinar($solicitacao, $assinante, '127.0.0.1', 'ua');
    }

    public function test_assinar_falha_em_solicitacao_expirada(): void
    {
        $assinante = User::factory()->assinante()->create();
        $solicitacao = SolicitacaoAssinatura::factory()->create([
            'assinante_user_id' => $assinante->id,
            'expires_at'        => now()->subDay(),
        ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('expirada');

        $this->service->assinar($solicitacao, $assinante, '127.0.0.1', 'ua');
    }

    public function test_assinar_em_sequencial_falha_se_anterior_nao_concluiu(): void
    {
        $versao = DocumentoVersao::factory()->create();
        $a1 = User::factory()->assinante()->create();
        $a2 = User::factory()->assinante()->create();

        SolicitacaoAssinatura::factory()->sequencial(1)->create([
            'documento_versao_id' => $versao->id,
            'assinante_user_id'   => $a1->id,
        ]);
        $s2 = SolicitacaoAssinatura::factory()->sequencial(2)->create([
            'documento_versao_id' => $versao->id,
            'assinante_user_id'   => $a2->id,
        ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('rodada sequencial');

        $this->service->assinar($s2, $a2, '127.0.0.1', 'ua');
    }

    public function test_snapshot_pii_eh_salvo_em_metadados(): void
    {
        $assinante = User::factory()->assinante()->create([
            'name'            => 'João Silva',
            'numero_portaria' => '999/2026',
        ]);
        $solicitacao = SolicitacaoAssinatura::factory()->create([
            'assinante_user_id' => $assinante->id,
        ]);

        $assinatura = $this->service->assinar($solicitacao, $assinante, '127.0.0.1', 'ua');

        $this->assertSame('João Silva',       $assinatura->metadados['nome']);
        $this->assertSame('999/2026',         $assinatura->metadados['numero_portaria']);
    }

    // ====================================================================
    // recusar()
    // ====================================================================

    public function test_recusar_cancela_rodada_inteira(): void
    {
        $versao = DocumentoVersao::factory()->create();
        $a1 = User::factory()->assinante()->create();
        $a2 = User::factory()->assinante()->create();
        $a3 = User::factory()->assinante()->create();

        $s1 = SolicitacaoAssinatura::factory()->create([
            'documento_versao_id' => $versao->id,
            'assinante_user_id'   => $a1->id,
        ]);
        $s2 = SolicitacaoAssinatura::factory()->create([
            'documento_versao_id' => $versao->id,
            'assinante_user_id'   => $a2->id,
        ]);
        $s3 = SolicitacaoAssinatura::factory()->create([
            'documento_versao_id' => $versao->id,
            'assinante_user_id'   => $a3->id,
        ]);

        // a1 assina, a2 recusa
        $this->service->assinar($s1, $a1, '127.0.0.1', 'ua');
        $this->service->recusar($s2, $a2, 'Valor incorreto', '127.0.0.1', 'ua');

        // s2 = recusada → cancela a rodada inteira
        $s1->refresh(); $s2->refresh(); $s3->refresh();
        $this->assertSame(SolicitacaoAssinatura::STATUS_CANCELADA, $s1->status); // foi assinada, mas a rodada é cancelada
        $this->assertSame(SolicitacaoAssinatura::STATUS_RECUSADA,  $s2->status);
        $this->assertSame(SolicitacaoAssinatura::STATUS_CANCELADA, $s3->status);
        $this->assertSame('Valor incorreto', $s2->motivo_recusa);
    }

    public function test_recusar_exige_motivo(): void
    {
        $assinante = User::factory()->assinante()->create();
        $solicitacao = SolicitacaoAssinatura::factory()->create([
            'assinante_user_id' => $assinante->id,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->service->recusar($solicitacao, $assinante, '   ', '127.0.0.1', 'ua');
    }

    public function test_recusar_falha_se_nao_for_o_assinante(): void
    {
        $assinanteCorreto = User::factory()->assinante()->create();
        $outroAssinante   = User::factory()->assinante()->create();
        $s = SolicitacaoAssinatura::factory()->create([
            'assinante_user_id' => $assinanteCorreto->id,
        ]);

        $this->expectException(\DomainException::class);
        $this->service->recusar($s, $outroAssinante, 'qualquer', '127.0.0.1', 'ua');
    }
}
