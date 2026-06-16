<?php

namespace Database\Factories;

use App\Models\AssinaturaLog;
use App\Models\DocumentoVersao;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\AssinaturaLog>
 */
class AssinaturaLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'acao'                 => AssinaturaLog::ACAO_CRIADA,
            'documento_versao_id'  => DocumentoVersao::factory(),
            'user_id'              => User::factory(),
            'ip'                   => fake()->ipv4(),
            'user_agent'           => fake()->userAgent(),
            'metadados'            => ['origem' => 'factory'],
        ];
    }
}
