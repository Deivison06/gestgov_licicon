<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Cria a role `assinante` e suas permissions próprias. Idempotente — pode rodar
 * várias vezes sem duplicar nada.
 *
 *  php artisan db:seed --class=AssinanteRoleSeeder
 */
class AssinanteRoleSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'documento.assinar',
            'documento.recusar_assinatura',
            'documento.ver_pendencias',
            'documento.ver_historico_proprio',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate([
                'name'       => $name,
                'guard_name' => 'web',
            ]);
        }

        $role = Role::firstOrCreate([
            'name'       => 'assinante',
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($permissions);

        $this->command->info('Role `assinante` criada/atualizada com ' . count($permissions) . ' permissions.');
    }
}
