<?php

namespace App\Enums;

enum SituacaoOcorrenciaEnum: string implements DisplayNameable
{
    case REGULARIZADA = 'regularizada';
    case NAO_REGULARIZADA = 'nao_regularizada';
    case ENCAMINHADA_GESTOR = 'encaminhada_gestor';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::REGULARIZADA => 'Regularizada',
            self::NAO_REGULARIZADA => 'Não regularizada',
            self::ENCAMINHADA_GESTOR => 'Encaminhada ao gestor',
        };
    }

    public function getBadgeClass(): string
    {
        return match ($this) {
            self::REGULARIZADA => 'bg-green-100 text-green-800',
            self::NAO_REGULARIZADA => 'bg-red-100 text-red-800',
            self::ENCAMINHADA_GESTOR => 'bg-yellow-100 text-yellow-800',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
