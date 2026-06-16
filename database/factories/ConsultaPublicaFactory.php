<?php

namespace Database\Factories;

use App\Models\DocumentoVersao;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\ConsultaPublica>
 */
class ConsultaPublicaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'codigo_verificador'  => str_pad((string) random_int(1, 9999999999), 10, '0', STR_PAD_LEFT)
                . substr(Str::random(20), 0, 10),
            'documento_versao_id' => DocumentoVersao::factory(),
            'ip'                  => fake()->ipv4(),
            'user_agent'          => fake()->userAgent(),
            'sucesso'             => true,
            'consultado_em'       => now(),
        ];
    }

    public function falha(): static
    {
        return $this->state(fn () => [
            'sucesso'             => false,
            'documento_versao_id' => null,
        ]);
    }
}
