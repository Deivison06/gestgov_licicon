<?php

namespace Tests\Unit\Assinatura;

use App\Models\AssinaturaDigital;
use App\Models\DocumentoVersao;
use App\Models\SolicitacaoAssinatura;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cobertura mínima do model DocumentoVersao:
 * - factory cria registro válido
 * - relations funcionam
 * - isConsolidada() reflete `assinaturas_consolidadas_em`
 * - estaEditavel() reflete ausência de assinaturas vinculadas
 */
class DocumentoVersaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_cria_versao_em_estado_rascunho(): void
    {
        $versao = DocumentoVersao::factory()->create();

        $this->assertNotNull($versao->id);
        $this->assertSame(1, $versao->versao);
        $this->assertNotNull($versao->hash_sha256);
        $this->assertNull($versao->assinaturas_consolidadas_em);
        $this->assertFalse($versao->isConsolidada());
        $this->assertTrue($versao->estaEditavel());
    }

    public function test_state_consolidada_marca_pdf_assinado(): void
    {
        $versao = DocumentoVersao::factory()->consolidada()->create();

        $this->assertTrue($versao->isConsolidada());
        $this->assertNotNull($versao->caminho_pdf_assinado);
        $this->assertNotNull($versao->hash_pdf_assinado);
    }

    public function test_relation_solicitacoes_funciona(): void
    {
        $versao = DocumentoVersao::factory()->create();
        SolicitacaoAssinatura::factory()->count(3)->create([
            'documento_versao_id' => $versao->id,
        ]);

        $this->assertSame(3, $versao->solicitacoes()->count());
    }

    public function test_versao_com_assinatura_nao_eh_editavel(): void
    {
        $versao = DocumentoVersao::factory()->create();
        AssinaturaDigital::factory()->create([
            'documento_versao_id' => $versao->id,
        ]);

        $this->assertFalse($versao->estaEditavel());
    }
}
