<?php

namespace Database\Factories;

use App\Models\DocumentoVersao;
use App\Models\SolicitacaoAssinatura;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\AssinaturaDigital>
 */
class AssinaturaDigitalFactory extends Factory
{
    public function definition(): array
    {
        $hashDoc = hash('sha256', Str::random());
        $hashAnterior = null; // primeira assinatura da cadeia

        return [
            'solicitacao_assinatura_id' => SolicitacaoAssinatura::factory(),
            'documento_versao_id'      => DocumentoVersao::factory(),
            'assinante_user_id'        => User::factory()->assinante(),

            'hash_documento_no_momento' => $hashDoc,
            'hash_cadeia_anterior'      => $hashAnterior,
            'hash_proprio'              => hash('sha256', $hashDoc . ($hashAnterior ?? '')),

            'codigo_verificador' => $this->gerarCodigoVerificador(),

            'ip'         => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'assinado_em' => now(),

            'metadados' => [
                'nome'      => fake()->name(),
                'cargo'     => 'Cargo de Teste',
                'matricula' => '000000-0',
            ],
        ];
    }

    private function gerarCodigoVerificador(): string
    {
        // Alinhado ao formato gerado pelo AssinaturaService::gerarCodigoVerificadorUnico:
        // 10 dígitos + 10 chars uppercase alfanuméricos = 20 chars total.
        return str_pad((string) random_int(1, 9_999_999_999), 10, '0', STR_PAD_LEFT)
            . substr(strtoupper(Str::random(20)), 0, 10);
    }
}
