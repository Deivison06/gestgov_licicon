<?php

namespace App\Enums;

enum TipoFiscalizacaoEnum: string implements DisplayNameable
{
    case COMPRAS = 'compras';
    case SERVICOS = 'servicos';
    case OBRAS = 'obras';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::COMPRAS => 'Compras',
            self::SERVICOS => 'Serviços',
            self::OBRAS => 'Obras e Serviços de Engenharia',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
