<?php

namespace Tests\Feature\Assinatura;

use App\Models\Prefeitura;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Cobertura do endpoint JSON consumido pelo modal:
 *   GET /admin/assinantes/disponiveis?prefeitura_id=X&search=Y
 */
class AssinantesDisponiveisEndpointTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Prefeitura $prefeitura;
    private Prefeitura $outraPrefeitura;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'assinante', 'guard_name' => 'web']);
        // Papel administrativo exigido pelo middleware das rotas /admin/assinantes.
        Role::firstOrCreate(['name' => 'diretor_licicon', 'guard_name' => 'web']);
        $this->prefeitura      = $this->criarPrefeitura('Prefeitura A');
        $this->outraPrefeitura = $this->criarPrefeitura('Prefeitura B');
        $this->admin = User::factory()->create(['prefeitura_id' => $this->prefeitura->id]);
        $this->admin->assignRole('diretor_licicon');
    }

    public function test_endpoint_requer_autenticacao(): void
    {
        // getJson envia Accept: application/json → Laravel retorna 401 em vez de redirecionar.
        $this->getJson(route('admin.assinantes.disponiveis', ['prefeitura_id' => $this->prefeitura->id]))
            ->assertStatus(401);
    }

    public function test_endpoint_filtra_por_prefeitura(): void
    {
        User::factory()->assinante()->count(2)->create(['prefeitura_id' => $this->prefeitura->id]);
        User::factory()->assinante()->count(3)->create(['prefeitura_id' => $this->outraPrefeitura->id]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.assinantes.disponiveis', ['prefeitura_id' => $this->prefeitura->id]));

        $response->assertOk();
        $response->assertJsonPath('meta.total', 2);
    }

    public function test_endpoint_filtra_por_search(): void
    {
        User::factory()->assinante()->create([
            'name' => 'João da Silva', 'prefeitura_id' => $this->prefeitura->id,
        ]);
        User::factory()->assinante()->create([
            'name' => 'Maria Santos', 'prefeitura_id' => $this->prefeitura->id,
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('admin.assinantes.disponiveis', [
            'prefeitura_id' => $this->prefeitura->id,
            'search'        => 'João',
        ]));

        $response->assertOk();
        $response->assertJsonPath('meta.total', 1);
        $response->assertJsonPath('data.0.name', 'João da Silva');
    }

    public function test_endpoint_ignora_users_nao_assinantes(): void
    {
        User::factory()->assinante()->create(['prefeitura_id' => $this->prefeitura->id]);
        User::factory()->create([
            'prefeitura_id' => $this->prefeitura->id,
            'is_assinante' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.assinantes.disponiveis', ['prefeitura_id' => $this->prefeitura->id]));

        $response->assertJsonPath('meta.total', 1);
    }

    public function test_endpoint_valida_prefeitura_id_obrigatorio(): void
    {
        $this->actingAs($this->admin)
            ->getJson(route('admin.assinantes.disponiveis'))
            ->assertStatus(422);
    }

    private function criarPrefeitura(string $nome): Prefeitura
    {
        // Sufixo aleatório por instância evita conflito em colunas UNIQUE
        // (nome, cnpj, etc.) entre múltiplas prefeituras no mesmo teste.
        $sufixo = (string) random_int(100000, 999999);
        $colunas = collect(DB::select("PRAGMA table_info(prefeituras)"));
        $dados = [];
        foreach ($colunas as $col) {
            if (!$col->notnull || $col->pk) continue;
            $dados[$col->name] = match (true) {
                str_contains($col->name, 'nome')   => "{$nome}-{$sufixo}",
                str_contains($col->name, 'cidade') => "Cidade-{$sufixo}",
                default                            => "T{$sufixo}",
            };
        }
        $id = DB::table('prefeituras')->insertGetId($dados);
        return Prefeitura::find($id);
    }
}
