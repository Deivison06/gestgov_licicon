<?php

namespace App\Enums;

enum ResponsabilidadeAtrasoEnum: string implements DisplayNameable
{
    case IMPUTAVEL_CONTRATADA = 'imputavel_contratada';
    case NAO_IMPUTAVEL_CONTRATADA = 'nao_imputavel_contratada';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::IMPUTAVEL_CONTRATADA => 'Imputável à Contratada',
            self::NAO_IMPUTAVEL_CONTRATADA => 'Não Imputável à Contratada',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
