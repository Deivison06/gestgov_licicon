<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\DocumentoVersao>
 */
class DocumentoVersaoFactory extends Factory
{
    public function definition(): array
    {
        // documentavel_id único por instância para evitar colisão com a constraint
        // (documentavel_type, documentavel_id, versao). Testes que precisam de um documentavel
        // real (Processo, Contrato) devem sobrescrever esses campos explicitamente.
        return [
            'documentavel_type'  => 'App\\Models\\Processo',
            'documentavel_id'    => fake()->unique()->numberBetween(1, 9_999_999),
            'versao'             => 1,
            'caminho_pdf'        => 'uploads/test/' . Str::random(8) . '.pdf',
            'hash_sha256'        => hash('sha256', Str::random(64)),
            'gerado_por_user_id' => User::factory(),
            'gerado_em'          => now(),
        ];
    }

    public function consolidada(): static
    {
        return $this->state(fn () => [
            'caminho_pdf_assinado'        => 'uploads/test/' . Str::random(8) . '_assinado.pdf',
            'hash_pdf_assinado'           => hash('sha256', Str::random(64)),
            'assinaturas_consolidadas_em' => now(),
        ]);
    }
}
