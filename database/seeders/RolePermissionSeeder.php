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
            'etp inteligente',
            'fiscalizar contratos',
            'atas e contratacoes', // 👈 nova permissão
        ];

        // Cria todas as permissões (sem duplicar)
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Define quais permissões cada role possui
        $roles = [
            'diretor_licicon'    => $permissions, // tudo
            'gerente_licicon'    => [
                'criar processos',
                'dar seguimento processos'
                
                
                
                ,
                'assinar processos',
                'contratos',
                'etp inteligente',
                'fiscalizar contratos',
                'atas e contratacoes',
            ],
            'colaborador_licicon' => ['dar seguimento processos', 'atas e contratacoes'],
            'prefeitura'          => ['assinar processos', 'contratos', 'etp inteligente', 'atas e contratacoes'],
        ];

        // Sincroniza permissões de cada role
        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate([
                'name'       => $roleName,
                'guard_name' => 'web',
            ]);
            $role->syncPermissions($rolePermissions);
        }

        $this->command->info('Roles e permissões criadas com sucesso!');
    }
}
