<?php

namespace App\Enums;

enum NaturezaAlteracaoAditivoEnum: string implements DisplayNameable
{
    case ACRESCIMO = 'acrescimo';
    case SUPRESSAO = 'supressao';
    case ACRESCIMO_E_SUPRESSAO = 'acrescimo_e_supressao';

    public function temAcrescimo(): bool
    {
        return $this !== self::SUPRESSAO;
    }

    public function temSupressao(): bool
    {
        return $this !== self::ACRESCIMO;
    }

    public function getDisplayName(): string
    {
        return match ($this) {
            self::ACRESCIMO => 'Acréscimo',
            self::SUPRESSAO => 'Supressão',
            self::ACRESCIMO_E_SUPRESSAO => 'Acréscimo e Supressão',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
