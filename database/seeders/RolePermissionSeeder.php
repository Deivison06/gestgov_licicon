<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            'gerenciar usuarios',
            'criar processos',
            'dar seguimento processos',
            'assinar processos',
            'contratos',
            'etp inteligente'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $roles = [
            'diretor_licicon' => $permissions,
            'gerente_licicon' => ['criar processos', 'dar seguimento processos', 'assinar processos', 'contratos', 'etp inteligente'], // tudo menos gerenciar usuários
            'colaborador_licicon' => ['dar seguimento processos'], // só dar seguimento
            'prefeitura' => ['assinar processos', 'contratos', 'etp inteligente'], // só assinar e contratos
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web' // 👈 IMPORTANTE
            ]);
        }

        $this->command->info('Roles e permissões criadas com sucesso!');
    }
}
