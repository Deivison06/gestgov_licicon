<?php

namespace Tests\Unit\Assinatura;

use App\Models\SolicitacaoAssinatura;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cobertura mínima da state machine de SolicitacaoAssinatura:
 * - constantes + estados finalizados
 * - scopes pendentes/ativas
 * - podeSerAssinada / estaExpirada
 */
class SolicitacaoAssinaturaTest extends TestCase
{
    use RefreshDatabase;

    public function test_constantes_de_status_cobrem_state_machine(): void
    {
        $this->assertCount(5, SolicitacaoAssinatura::STATUSES);
        $this->assertContains('pendente',  SolicitacaoAssinatura::STATUSES);
        $this->assertContains('assinada',  SolicitacaoAssinatura::STATUSES);
        $this->assertContains('recusada',  SolicitacaoAssinatura::STATUSES);
        $this->assertContains('cancelada', SolicitacaoAssinatura::STATUSES);
        $this->assertContains('expirada',  SolicitacaoAssinatura::STATUSES);

        $this->assertCount(4, SolicitacaoAssinatura::STATUSES_FINALIZADOS);
        $this->assertNotContains('pendente', SolicitacaoAssinatura::STATUSES_FINALIZADOS);
    }

    public function test_scope_pendentes_filtra_corretamente(): void
    {
        SolicitacaoAssinatura::factory()->count(3)->create();
        SolicitacaoAssinatura::factory()->assinada()->count(2)->create();

        $this->assertSame(3, SolicitacaoAssinatura::pendentes()->count());
    }

    public function test_scope_ativas_exclui_estados_finais(): void
    {
        SolicitacaoAssinatura::factory()->count(2)->create();              // pendentes
        SolicitacaoAssinatura::factory()->assinada()->create();
        SolicitacaoAssinatura::factory()->recusada()->create();
        SolicitacaoAssinatura::factory()->expirada()->create();

        $this->assertSame(2, SolicitacaoAssinatura::ativas()->count());
    }

    public function test_pode_ser_assinada_quando_pendente_e_nao_expirada(): void
    {
        $solicitacao = SolicitacaoAssinatura::factory()->create([
            'expires_at' => now()->addDays(3),
        ]);

        $this->assertTrue($solicitacao->podeSerAssinada());
    }

    public function test_nao_pode_ser_assinada_quando_ja_assinada(): void
    {
        $solicitacao = SolicitacaoAssinatura::factory()->assinada()->create();

        $this->assertFalse($solicitacao->podeSerAssinada());
    }

    public function test_esta_expirada_quando_prazo_passou_mesmo_pendente(): void
    {
        $solicitacao = SolicitacaoAssinatura::factory()->create([
            'expires_at' => now()->subDay(),
        ]);

        $this->assertTrue($solicitacao->estaExpirada());
        $this->assertFalse($solicitacao->podeSerAssinada());
    }
}
