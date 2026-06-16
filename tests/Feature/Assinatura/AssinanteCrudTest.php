<?php

namespace Tests\Feature\Assinatura;

use App\Models\Prefeitura;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Cobertura mínima do CRUD admin de assinantes (rotas /admin/assinantes).
 * Garante que:
 * - rotas exigem autenticação
 * - listagem mostra apenas users com is_assinante=true
 * - store cria + atribui role + flag
 * - update preserva senha quando campo vazio
 * - destroy é soft-disable (não deleta a row)
 */
class AssinanteCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Prefeitura $prefeitura;

    protected function setUp(): void
    {
        parent::setUp();

        // Cria role assinante (necessária para o controller funcionar)
        Role::firstOrCreate(['name' => 'assinante', 'guard_name' => 'web']);
        // Papel administrativo exigido pelo middleware das rotas /admin/assinantes.
        Role::firstOrCreate(['name' => 'diretor_licicon', 'guard_name' => 'web']);

        // Cria prefeitura preenchendo qualquer coluna NOT NULL com valor placeholder.
        // Robusto a mudanças de schema (qualquer NOT NULL nova ganha o mesmo tratamento).
        $colunas = collect(
            \Illuminate\Support\Facades\DB::select("PRAGMA table_info(prefeituras)")
        );
        $dados = [];
        foreach ($colunas as $col) {
            if (!$col->notnull || $col->pk) continue;
            $dados[$col->name] = match (true) {
                str_contains($col->name, 'nome')   => 'Prefeitura de Teste',
                str_contains($col->name, 'cidade') => 'Teste',
                default                            => 'TESTE',
            };
        }
        $id = \Illuminate\Support\Facades\DB::table('prefeituras')->insertGetId($dados);
        $this->prefeitura = Prefeitura::find($id);

        $this->admin = User::factory()->create([
            'prefeitura_id' => $this->prefeitura->id,
            'email_verified_at' => now(),
        ]);
        $this->admin->assignRole('diretor_licicon');
    }

    public function test_index_requer_autenticacao(): void
    {
        $this->get(route('admin.assinantes.index'))->assertRedirect(route('login'));
    }

    public function test_index_lista_apenas_users_com_flag_assinante(): void
    {
        User::factory()->assinante()->count(3)->create([
            'prefeitura_id' => $this->prefeitura->id,
        ]);
        User::factory()->count(2)->create([
            'prefeitura_id' => $this->prefeitura->id,
            'is_assinante' => false,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.assinantes.index'));

        $response->assertOk();
        $response->assertViewHas('assinantes', function ($paginator) {
            return $paginator->total() === 3;
        });
    }

    public function test_store_cria_user_com_role_e_flag(): void
    {
        $payload = [
            'name'                  => 'João Assinante',
            'email'                 => 'joao.assinante@test.com',
            'password'              => 'senha-forte-12345',
            'password_confirmation' => 'senha-forte-12345',
            'prefeitura_id'         => $this->prefeitura->id,
            'numero_portaria'       => '999/2026',
            'data_portaria'         => '2026-01-15',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.assinantes.store'), $payload);

        $response->assertRedirect(route('admin.assinantes.index'));

        $user = User::where('email', 'joao.assinante@test.com')->firstOrFail();
        $this->assertTrue($user->is_assinante);
        $this->assertTrue($user->hasRole('assinante'));
        $this->assertSame('999/2026', $user->numero_portaria);
    }

    public function test_update_sem_senha_preserva_senha_atual(): void
    {
        $assinante = User::factory()->assinante()->create([
            'prefeitura_id' => $this->prefeitura->id,
            'password' => bcrypt('senha-antiga'),
        ]);
        $hashAntes = $assinante->fresh()->password;

        $response = $this->actingAs($this->admin)->put(
            route('admin.assinantes.update', $assinante->id),
            [
                'name'          => 'Nome Alterado',
                'email'         => $assinante->email,
                'prefeitura_id' => $this->prefeitura->id,
            ]
        );

        $response->assertRedirect(route('admin.assinantes.index'));
        $assinante->refresh();
        $this->assertSame('Nome Alterado', $assinante->name);
        $this->assertSame($hashAntes, $assinante->password);
    }

    public function test_destroy_eh_soft_disable_e_remove_role(): void
    {
        $assinante = User::factory()->assinante()->create([
            'prefeitura_id' => $this->prefeitura->id,
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.assinantes.destroy', $assinante->id))
            ->assertRedirect(route('admin.assinantes.index'));

        $assinante->refresh();
        $this->assertFalse($assinante->is_assinante);
        $this->assertFalse($assinante->hasRole('assinante'));
        // A row continua existindo (preserva assinaturas antigas)
        $this->assertNotNull(User::find($assinante->id));
    }

    public function test_edit_de_user_nao_assinante_retorna_404(): void
    {
        $usuarioComum = User::factory()->create([
            'prefeitura_id' => $this->prefeitura->id,
            'is_assinante' => false,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.assinantes.edit', $usuarioComum->id))
            ->assertNotFound();
    }
}
