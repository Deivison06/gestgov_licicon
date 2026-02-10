<?php

namespace App\Enums;

enum ProcessoStatusEnum: string
{
    case RASCUNHO = 'RASCUNHO';
    case EM_INICIO = 'EM_INICIO';
    case EM_FINALIZACAO = 'EM_FINALIZACAO';
    case EM_CONTRATO = 'EM_CONTRATO';
    case FINALIZADO = 'FINALIZADO';
    case CANCELADO = 'CANCELADO';
    case ADIADO = 'ADIADO';
    case REPUBLICADO = 'REPUBLICADO';

    public function label(): string
    {
        return match ($this) {
            self::RASCUNHO => 'Rascunho',
            self::EM_INICIO => 'Em início',
            self::EM_FINALIZACAO => 'Em finalização',
            self::EM_CONTRATO => 'Em contrato',
            self::FINALIZADO => 'Finalizado',
            self::CANCELADO => 'Cancelado',
            self::ADIADO => 'Adiado',
            self::REPUBLICADO => 'Republicado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::RASCUNHO => 'gray',
            self::EM_INICIO => 'blue',
            self::EM_FINALIZACAO => 'cyan',
            self::EM_CONTRATO => 'indigo',
            self::FINALIZADO => 'green',
            self::CANCELADO => 'red',
            self::ADIADO => 'orange',
            self::REPUBLICADO => 'purple',
        };
    }

    public function isAtivo(): bool
    {
        return match ($this) {
            self::RASCUNHO,
            self::EM_INICIO,
            self::EM_FINALIZACAO,
            self::EM_CONTRATO,
            self::REPUBLICADO => true,
            self::FINALIZADO,
            self::CANCELADO,
            self::ADIADO => false,
        };
    }

    public function podeEditar(): bool
    {
        return match ($this) {
            self::RASCUNHO,
            self::EM_INICIO,
            self::EM_FINALIZACAO,
            self::EM_CONTRATO => true,
            default => false,
        };
    }

    public function podeCancelar(): bool
    {
        return match ($this) {
            self::RASCUNHO,
            self::EM_INICIO,
            self::EM_FINALIZACAO,
            self::EM_CONTRATO,
            self::REPUBLICADO => true,
            default => false,
        };
    }

    // Método para verificar se pode ser republicado
    public function podeRepublicar(): bool
    {
        return match ($this) {
            self::FINALIZADO,
            self::CANCELADO,
            self::ADIADO => true,
            default => false,
        };
    }

    // Método para verificar se pode ser adiado
    public function podeAdiar(): bool
    {
        return match ($this) {
            self::EM_INICIO,
            self::EM_FINALIZACAO,
            self::EM_CONTRATO,
            self::REPUBLICADO => true,
            default => false,
        };
    }

    // Método para verificar se está em andamento
    public function isEmAndamento(): bool
    {
        return match ($this) {
            self::EM_INICIO,
            self::EM_FINALIZACAO,
            self::EM_CONTRATO => true,
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

    // Método para obter status que são considerados "ativos" para exibição
    public static function ativos(): array
    {
        return array_filter(self::cases(), fn($case) => $case->isAtivo());
    }

    // Método para obter status que são considerados "finalizados"
    public static function finalizados(): array
    {
        return [
            self::FINALIZADO,
            self::CANCELADO,
            self::ADIADO,
        ];
    }
}
