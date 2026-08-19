<?php

namespace App\Enums;

enum StatusOcorrenciaEnum: string implements DisplayNameable
{
    case RASCUNHO = 'rascunho';
    case REGISTRADA = 'registrada';
    case CONCLUIDA = 'concluida';

    /**
     * Transições válidas a partir deste estado.
     * - Rascunho só avança para Registrada.
     * - Registrada só avança para Concluída (depois do atesto de correção).
     * - Concluída é estado terminal — não volta.
     */
    public function transicoesPermitidas(): array
    {
        return match ($this) {
            self::RASCUNHO => [self::REGISTRADA],
            self::REGISTRADA => [self::CONCLUIDA],
            self::CONCLUIDA => [],
        };
    }

    public function podeTransicionarPara(self $novo): bool
    {
        return in_array($novo, $this->transicoesPermitidas(), true);
    }

    public function getDisplayName(): string
    {
        return match ($this) {
            self::RASCUNHO => 'Rascunho',
            self::REGISTRADA => 'Registrada',
            self::CONCLUIDA => 'Concluída',
        };
    }

    public function getBadgeClass(): string
    {
        return match ($this) {
            self::RASCUNHO => 'bg-gray-100 text-gray-700',
            self::REGISTRADA => 'bg-yellow-100 text-yellow-800',
            self::CONCLUIDA => 'bg-green-100 text-green-800',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
