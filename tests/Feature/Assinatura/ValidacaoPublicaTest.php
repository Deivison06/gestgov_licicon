<?php

namespace Tests\Feature\Assinatura;

use App\Models\AssinaturaDigital;
use App\Models\ConsultaPublica;
use App\Models\DocumentoVersao;
use App\Services\Assinatura\ValidacaoPublicaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ValidacaoPublicaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush(); // garante state limpo entre testes
    }

    // ====================================================================
    // Service
    // ====================================================================

    public function test_service_retorna_autentico_para_codigo_valido(): void
    {
        $assinatura = AssinaturaDigital::factory()->create();

        $resultado = app(ValidacaoPublicaService::class)->consultar($assinatura->codigo_verificador);

        $this->assertSame('autentico', $resultado['status']);
        $this->assertSame($assinatura->id, $resultado['assinatura_referenciada']->id);
        $this->assertCount(1, $resultado['assinaturas']);
    }

    public function test_service_retorna_nao_encontrado_para_codigo_invalido(): void
    {
        $resultado = app(ValidacaoPublicaService::class)
            ->consultar('CODIGOXXXFALSO000000');

        $this->assertSame('nao_encontrado', $resultado['status']);
    }

    public function test_service_normaliza_codigo_em_uppercase(): void
    {
        $assinatura = AssinaturaDigital::factory()->create();
        $codigoLower = strtolower($assinatura->codigo_verificador);

        $resultado = app(ValidacaoPublicaService::class)->consultar($codigoLower);

        $this->assertSame('autentico', $resultado['status']);
    }

    public function test_service_registra_consulta_publica(): void
    {
        $assinatura = AssinaturaDigital::factory()->create();

        app(ValidacaoPublicaService::class)
            ->consultar($assinatura->codigo_verificador, '192.168.1.1', 'TestUA');

        $consulta = ConsultaPublica::first();
        $this->assertNotNull($consulta);
        $this->assertSame($assinatura->codigo_verificador, $consulta->codigo_verificador);
        $this->assertSame('192.168.1.1', $consulta->ip);
        $this->assertTrue($consulta->sucesso);
    }

    public function test_service_registra_falha_em_codigo_invalido(): void
    {
        app(ValidacaoPublicaService::class)
            ->consultar('CODIGOINEXISTENTE000', '10.0.0.1', 'TestUA');

        $consulta = ConsultaPublica::first();
        $this->assertNotNull($consulta);
        $this->assertFalse($consulta->sucesso);
        $this->assertNull($consulta->documento_versao_id);
    }

    public function test_lista_todas_as_assinaturas_da_versao(): void
    {
        $versao = DocumentoVersao::factory()->create();
        $a1 = AssinaturaDigital::factory()->create(['documento_versao_id' => $versao->id]);
        AssinaturaDigital::factory()->count(2)->create(['documento_versao_id' => $versao->id]);

        $resultado = app(ValidacaoPublicaService::class)->consultar($a1->codigo_verificador);

        $this->assertCount(3, $resultado['assinaturas']);
    }

    // ====================================================================
    // Controller
    // ====================================================================

    public function test_formulario_eh_acessivel_sem_autenticacao(): void
    {
        $this->get(route('autenticar.formulario'))
            ->assertOk()
            ->assertSee('Validar autenticidade');
    }

    public function test_consultar_codigo_valido_mostra_pagina_de_sucesso(): void
    {
        $assinatura = AssinaturaDigital::factory()->create();

        $this->get(route('autenticar.consultar', $assinatura->codigo_verificador))
            ->assertOk()
            ->assertSee('Documento autêntico')
            ->assertSee($assinatura->codigo_verificador);
    }

    public function test_consultar_codigo_invalido_mostra_pagina_de_falha(): void
    {
        $this->get(route('autenticar.consultar', 'XXXFALSO000000XXXXXX'))
            ->assertOk()
            ->assertSee('Código não encontrado');
    }

    public function test_buscar_via_post_redireciona_para_consultar(): void
    {
        $assinatura = AssinaturaDigital::factory()->create();

        $this->post(route('autenticar.buscar'), [
            'codigo' => $assinatura->codigo_verificador,
        ])->assertRedirect(route('autenticar.consultar', $assinatura->codigo_verificador));
    }

    public function test_buscar_valida_codigo_obrigatorio(): void
    {
        $this->post(route('autenticar.buscar'), ['codigo' => ''])
            ->assertSessionHasErrors('codigo');
    }

    public function test_download_404_quando_pdf_nao_foi_consolidado(): void
    {
        $versao = DocumentoVersao::factory()->create([
            'caminho_pdf_assinado' => null,
        ]);
        $a = AssinaturaDigital::factory()->create(['documento_versao_id' => $versao->id]);

        $this->get(route('autenticar.download', $a->codigo_verificador))
            ->assertNotFound();
    }
}
