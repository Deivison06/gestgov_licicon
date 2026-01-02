<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Prefeitura;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PrefeituraUsersSeeder extends Seeder
{
    public function run()
    {
        $prefeituras = Prefeitura::whereNotNull('cidade')->get();

        foreach ($prefeituras as $prefeitura) {

            /**
             * Exemplo cidade: "Corrente - PI"
             * Resultado: corrente.pi@gestgov.com
             */
            $cidade = Str::ascii($prefeitura->cidade); // remove acentos
            $cidade = strtolower($cidade);
            $cidade = str_replace([' - ', '-'], '.', $cidade);
            $cidade = str_replace(' ', '', $cidade);
            $cidade = preg_replace('/[^a-z0-9.]/', '', $cidade);

            $email = $cidade . '@gestgov.com';

            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $prefeitura->nome,
                    'cpf' => null,
                    'password' => Hash::make('senha123'),
                    'prefeitura_id' => $prefeitura->id
                ]
            )->syncRoles(['prefeitura']);
        }

        $this->command->info('Usuários das prefeituras criados com sucesso!');
    }
}
