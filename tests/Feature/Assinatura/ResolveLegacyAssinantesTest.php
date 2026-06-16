<?php

namespace Tests\Feature\Assinatura;

use App\Models\User;
use App\Services\Assinatura\ResolveLegacyAssinantesTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Cobertura da trait que detecta o formato dos assinantes (novo vs legado da
 * UI "Seleção de Assinantes") e devolve a lista normalizada para o
 * SolicitacaoService::criarRodada.
 */
class ResolveLegacyAssinantesTest extends TestCase
{
    use RefreshDatabase;

    /** Subject under test — classe anônima usando a trait. */
    private object $sut;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'assinante', 'guard_name' => 'web']);

        $this->sut = new class {
            use ResolveLegacyAssinantesTrait;
            public function chamarExtrair(array $r): array { return $this->extrairListaAssinantesDaRequest($r); }
            public function chamarResolver(array $e, string $modo = 'paralelo'): array {
                return $this->resolverAssinantesParaRodada($e, $modo);
            }
        };
    }

    public function test_formato_novo_passa_direto(): void
    {
        $a1 = User::factory()->assinante()->create();
        $a2 = User::factory()->assinante()->create();

        $resultado = $this->sut->chamarResolver([
            ['user_id' => $a1->id, 'ordem' => 0],
            ['user_id' => $a2->id, 'ordem' => 0],
        ]);

        $this->assertCount(2, $resultado);
        $this->assertSame($a1->id, $resultado[0]['user_id']);
        $this->assertSame($a2->id, $resultado[1]['user_id']);
    }

    public function test_formato_legado_da_ui_de_selecao_resolve_por_nome(): void
    {
        $assinante = User::factory()->assinante()->create([
            'name' => 'João Silva',
        ]);

        // Formato que vem do <x-form-field name="assinante_responsavel[]">
        $legado = [
            [
                'unidade_id'      => 138,
                'unidade_nome'    => 'Prefeito Municipal',
                'responsavel'     => 'João Silva',
                'cargo'           => null,
                'numero_portaria' => '002/2025',
                'data_portaria'   => '2025-01-02',
            ],
        ];

        $resultado = $this->sut->chamarResolver($legado);

        $this->assertCount(1, $resultado);
        $this->assertSame($assinante->id, $resultado[0]['user_id']);
    }

    public function test_resolucao_eh_case_insensitive(): void
    {
        $assinante = User::factory()->assinante()->create(['name' => 'Maria Santos']);

        $resultado = $this->sut->chamarResolver([
            ['responsavel' => 'MARIA SANTOS'],
        ]);

        $this->assertCount(1, $resultado);
        $this->assertSame($assinante->id, $resultado[0]['user_id']);
    }

    public function test_entradas_sem_match_no_banco_sao_descartadas(): void
    {
        User::factory()->assinante()->create(['name' => 'Existe Aqui']);

        $resultado = $this->sut->chamarResolver([
            ['responsavel' => 'Existe Aqui'],
            ['responsavel' => 'Não Existe Nesse Sistema'],
        ]);

        // Só 1 — o que tem match
        $this->assertCount(1, $resultado);
    }

    public function test_user_sem_flag_assinante_nao_resolve(): void
    {
        // User existe mas NÃO é assinante (is_assinante=false default)
        User::factory()->create(['name' => 'Não Sou Assinante']);

        $resultado = $this->sut->chamarResolver([
            ['responsavel' => 'Não Sou Assinante'],
        ]);

        $this->assertCount(0, $resultado);
    }

    public function test_modo_sequencial_atribui_ordem_incremental(): void
    {
        User::factory()->assinante()->create(['name' => 'A1']);
        User::factory()->assinante()->create(['name' => 'A2']);
        User::factory()->assinante()->create(['name' => 'A3']);

        $resultado = $this->sut->chamarResolver([
            ['responsavel' => 'A1'],
            ['responsavel' => 'A2'],
            ['responsavel' => 'A3'],
        ], 'sequencial');

        $this->assertSame([1, 2, 3], array_column($resultado, 'ordem'));
    }

    public function test_extrair_prioriza_rodada_assinantes_sobre_assinantes(): void
    {
        $novoFormato = User::factory()->assinante()->create(['name' => 'Novo']);
        User::factory()->assinante()->create(['name' => 'Legado']);

        $resultado = $this->sut->chamarExtrair([
            'rodada_assinantes' => [['user_id' => $novoFormato->id]],
            'assinantes'        => [['responsavel' => 'Legado']],
        ]);

        // Só o novo formato foi usado
        $this->assertCount(1, $resultado);
        $this->assertSame($novoFormato->id, $resultado[0]['user_id']);
    }

    public function test_extrair_cai_para_assinantes_legado_quando_rodada_assinantes_ausente(): void
    {
        $assinante = User::factory()->assinante()->create(['name' => 'Pedro']);

        $resultado = $this->sut->chamarExtrair([
            'assinantes' => [['responsavel' => 'Pedro']],
        ]);

        $this->assertCount(1, $resultado);
        $this->assertSame($assinante->id, $resultado[0]['user_id']);
    }

    public function test_request_sem_nenhuma_chave_retorna_vazio(): void
    {
        $resultado = $this->sut->chamarExtrair(['outra_coisa' => 'x']);
        $this->assertSame([], $resultado);
    }
}
