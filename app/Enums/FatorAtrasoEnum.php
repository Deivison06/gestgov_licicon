<?php

namespace App\Enums;

enum FatorAtrasoEnum: string implements DisplayNameable
{
    case CONDICOES_CLIMATICAS = 'condicoes_climaticas';
    case ADEQUACOES_TECNICAS = 'adequacoes_tecnicas';
    case INTERFERENCIAS = 'interferencias';
    case ATRASO_INSUMOS = 'atraso_insumos';
    case OUTROS = 'outros';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::CONDICOES_CLIMATICAS => 'Condições climáticas',
            self::ADEQUACOES_TECNICAS => 'Adequações técnicas',
            self::INTERFERENCIAS => 'Interferências não previstas no projeto',
            self::ATRASO_INSUMOS => 'Atrasos no fornecimento de insumos',
            self::OUTROS => 'Outros',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
