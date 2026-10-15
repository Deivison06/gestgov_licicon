<?php

namespace App\Enums;

enum LimiteLegalObraEnum: string implements DisplayNameable
{
    case VINTE_CINCO = '25';
    case CINQUENTA = '50';

    public function percentual(): float
    {
        return (float) $this->value;
    }

    public function getDisplayName(): string
    {
        return match ($this) {
            self::VINTE_CINCO => '25% (art. 125, caput, da Lei nº 14.133/2021)',
            self::CINQUENTA => '50% (reforma de edifício/equipamento — art. 125, §1º, da Lei nº 14.133/2021)',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
