<?php

namespace Database\Factories;

use App\Models\DocumentoVersao;
use App\Models\SolicitacaoAssinatura;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\SolicitacaoAssinatura>
 */
class SolicitacaoAssinaturaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'documento_versao_id'    => DocumentoVersao::factory(),
            'assinante_user_id'      => User::factory()->assinante(),
            'solicitado_por_user_id' => User::factory(),
            'status'                 => SolicitacaoAssinatura::STATUS_PENDENTE,
            'ordem'                  => 0,
            'obrigatoria'            => true,
            'solicitado_em'          => now(),
            'expires_at'             => now()->addDays(7),
            'token_acesso'           => Str::random(64),
        ];
    }

    public function assinada(): static
    {
        return $this->state(fn () => [
            'status'        => SolicitacaoAssinatura::STATUS_ASSINADA,
            'processada_em' => now(),
        ]);
    }

    public function recusada(string $motivo = 'Conteúdo precisa de revisão'): static
    {
        return $this->state(fn () => [
            'status'        => SolicitacaoAssinatura::STATUS_RECUSADA,
            'motivo_recusa' => $motivo,
            'processada_em' => now(),
        ]);
    }

    public function expirada(): static
    {
        return $this->state(fn () => [
            'status'     => SolicitacaoAssinatura::STATUS_EXPIRADA,
            'expires_at' => now()->subDay(),
        ]);
    }

    public function sequencial(int $ordem): static
    {
        return $this->state(fn () => ['ordem' => $ordem]);
    }
}
