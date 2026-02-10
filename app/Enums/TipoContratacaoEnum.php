<?php

namespace App\Enums;

enum TipoContratacaoEnum: int implements DisplayNameable
{
    case LOTE = 1;
    case ITEM = 2;
    case TECNICO = 3;
    CASE ARTISTICO = 4;
    CASE IMOVEL = 5;
    CASE FORNECEDOR = 6;

    public function getDisplayName(): string
    {
        return match ($this) {
            self::LOTE => 'LOTE',
            self::ITEM => 'ITEM',
            self::TECNICO => 'TECNICO',
            self::ARTISTICO => 'ARTISTICO',
            self::IMOVEL => 'IMOVEL',
            self::FORNECEDOR => 'FORNECEDOR',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
