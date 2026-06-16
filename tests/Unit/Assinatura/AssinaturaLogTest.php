<?php

namespace Tests\Unit\Assinatura;

use App\Models\AssinaturaLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cobertura mínima de AssinaturaLog:
 * - factory + persiste
 * - 8 ações definidas como constantes
 * - tabela é append-only (não há coluna updated_at)
 * - metadados são castados para array
 */
class AssinaturaLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_cria_log_valido(): void
    {
        $log = AssinaturaLog::factory()->create([
            'acao' => AssinaturaLog::ACAO_ASSINADA,
        ]);

        $this->assertNotNull($log->id);
        $this->assertSame('assinada', $log->acao);
        $this->assertNotNull($log->created_at);
    }

    public function test_oito_acoes_definidas(): void
    {
        $this->assertCount(8, AssinaturaLog::ACOES);
        $this->assertContains('criada',      AssinaturaLog::ACOES);
        $this->assertContains('notificada',  AssinaturaLog::ACOES);
        $this->assertContains('visualizada', AssinaturaLog::ACOES);
        $this->assertContains('assinada',    AssinaturaLog::ACOES);
        $this->assertContains('recusada',    AssinaturaLog::ACOES);
        $this->assertContains('cancelada',   AssinaturaLog::ACOES);
        $this->assertContains('expirada',    AssinaturaLog::ACOES);
        $this->assertContains('regerada',    AssinaturaLog::ACOES);
    }

    public function test_tabela_nao_tem_coluna_updated_at(): void
    {
        // Append-only: a migration explicitamente não cria updated_at.
        $colunas = \Illuminate\Support\Facades\Schema::getColumnListing('assinatura_logs');
        $this->assertNotContains('updated_at', $colunas);
        $this->assertContains('created_at', $colunas);
    }

    public function test_metadados_sao_cast_para_array(): void
    {
        $log = AssinaturaLog::factory()->create([
            'metadados' => ['ip_origem' => '192.168.1.1', 'device' => 'mobile'],
        ]);

        $log->refresh();

        $this->assertIsArray($log->metadados);
        $this->assertSame('192.168.1.1', $log->metadados['ip_origem']);
    }
}
