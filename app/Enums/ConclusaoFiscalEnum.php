<?php

namespace App\Enums;

enum ConclusaoFiscalEnum: int implements DisplayNameable
{
    case CONFORME = 1;
    case INCONSISTENCIAS = 2;
    case FALHAS_GRAVES = 3;

    public function getDisplayName(): string
    {
        return match ($this) {
            self::CONFORME => 'Conforme',
            self::INCONSISTENCIAS => 'Inconsistências',
            self::FALHAS_GRAVES => 'Falhas Graves',
        };
    }

    public function getTextoCompleto(): string
    {
        return match ($this) {
            self::CONFORME => 'O contrato está sendo executado em plena conformidade com os termos pactuados.',
            self::INCONSISTENCIAS => 'O contrato apresenta pequenas inconsistências, mas foram sanadas ou estão em processo de correção.',
            self::FALHAS_GRAVES => 'O contrato apresenta falhas graves que comprometem a execução e demandam intervenção da Administração.',
        };
    }

    public function getBadgeClass(): string
    {
        return match ($this) {
            self::CONFORME => 'bg-green-100 text-green-800',
            self::INCONSISTENCIAS => 'bg-yellow-100 text-yellow-800',
            self::FALHAS_GRAVES => 'bg-red-100 text-red-800',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
