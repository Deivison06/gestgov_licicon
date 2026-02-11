<?php

namespace App\Enums;

enum ProcessoStatusEnum: string
{
    case EM_ANDAMENTO = 'EM_ANDAMENTO';
    case FINALIZADO = 'FINALIZADO';
    case CANCELADO = 'CANCELADO';
    case REPUBLICADO = 'REPUBLICADO';
    case ADIADO = 'ADIADO';

    public function label(): string
    {
        return match ($this) {
            self::EM_ANDAMENTO => 'Em andamento',
            self::FINALIZADO => 'Finalizado',
            self::CANCELADO => 'Cancelado',
            self::REPUBLICADO => 'Republicado',
            self::ADIADO => 'Adiado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::EM_ANDAMENTO => 'blue',
            self::FINALIZADO => 'green',
            self::CANCELADO => 'red',
            self::REPUBLICADO => 'purple',
            self::ADIADO => 'orange',
        };
    }

    public function isAtivo(): bool
    {
        return match ($this) {
            self::EM_ANDAMENTO,
            self::REPUBLICADO => true,
            self::FINALIZADO,
            self::CANCELADO,
            self::ADIADO => false,
        };
    }

    public function podeEditar(): bool
    {
        return match ($this) {
            self::EM_ANDAMENTO => true,
            default => false,
        };
    }

    public function podeCancelar(): bool
    {
        return match ($this) {
            self::EM_ANDAMENTO,
            self::REPUBLICADO => true,
            default => false,
        };
    }

    public function podeRepublicar(): bool
    {
        return match ($this) {
            self::FINALIZADO,
            self::CANCELADO,
            self::ADIADO => true,
            default => false,
        };
    }

    public function podeAdiar(): bool
    {
        return match ($this) {
            self::EM_ANDAMENTO,
            self::REPUBLICADO => true,
            default => false,
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function toArray(): array
    {
        $array = [];
        foreach (self::cases() as $case) {
            $array[$case->value] = $case->label();
        }
        return $array;
    }

    public static function ativos(): array
    {
        return array_filter(self::cases(), fn($case) => $case->isAtivo());
    }

    public static function finalizados(): array
    {
        return [
            self::FINALIZADO,
            self::CANCELADO,
            self::ADIADO,
        ];
    }
}
