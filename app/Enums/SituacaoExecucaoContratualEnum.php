<?php

namespace App\Enums;

enum SituacaoExecucaoContratualEnum: string implements DisplayNameable
{
    case ANDAMENTO_REGULAR = 'andamento_regular';
    case COM_ATRASO = 'com_atraso';
    case PARALISADA = 'paralisada';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::ANDAMENTO_REGULAR => 'Em andamento regular',
            self::COM_ATRASO => 'Em andamento, com atraso',
            self::PARALISADA => 'Paralisada',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
