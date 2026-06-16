<?php

namespace Tests\Unit\Assinatura;

use App\Models\AssinaturaDigital;
use App\Models\DocumentoVersao;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cobertura mínima de AssinaturaDigital:
 * - factory cria + persiste
 * - codigo_verificador é único (constraint do DB)
 * - solicitacao_assinatura_id é único (1 assinatura por solicitação)
 * - getCrcHumanoAttribute formata em 8 chars uppercase
 */
class AssinaturaDigitalTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_cria_assinatura_valida(): void
    {
        $assinatura = AssinaturaDigital::factory()->create();

        $this->assertNotNull($assinatura->id);
        $this->assertSame(64, strlen($assinatura->hash_documento_no_momento));
        $this->assertSame(64, strlen($assinatura->hash_proprio));
        $this->assertSame(20, strlen($assinatura->codigo_verificador));
        $this->assertNotNull($assinatura->assinado_em);
    }

    public function test_codigo_verificador_eh_unico(): void
    {
        $existente = AssinaturaDigital::factory()->create();

        $this->expectException(QueryException::class);
        AssinaturaDigital::factory()->create([
            'codigo_verificador' => $existente->codigo_verificador,
        ]);
    }

    public function test_solicitacao_assinatura_id_eh_unico(): void
    {
        $existente = AssinaturaDigital::factory()->create();

        $this->expectException(QueryException::class);
        AssinaturaDigital::factory()->create([
            'solicitacao_assinatura_id' => $existente->solicitacao_assinatura_id,
        ]);
    }

    public function test_crc_humano_retorna_8_chars_uppercase(): void
    {
        // Cria uma versão consolidada (com hash_pdf_assinado preenchido) e atrela a assinatura.
        $versao = DocumentoVersao::factory()->consolidada()->create([
            'hash_pdf_assinado' => 'a7b8034be1234567890abcdef' . str_repeat('0', 40),
        ]);
        $assinatura = AssinaturaDigital::factory()->create([
            'documento_versao_id' => $versao->id,
        ]);

        $crc = $assinatura->crc_humano;

        $this->assertSame(8, strlen($crc));
        $this->assertSame(strtoupper($crc), $crc);
        $this->assertSame('A7B8034B', $crc);
    }
}
