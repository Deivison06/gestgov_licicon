<?php

namespace App\Enums;

enum TipoObjetoContratoEnum: string implements DisplayNameable
{
    case COMPRA = 'compra';
    case SERVICO = 'servico';

    /**
     * Palavra usada nos parágrafos condicionais dos templates de aditivo
     * (ex.: "fornecedor"/"prestador", "fornecimento"/"serviço").
     */
    public function substantivoParte(): string
    {
        return match ($this) {
            self::COMPRA => 'fornecedor',
            self::SERVICO => 'prestador',
        };
    }

    public function substantivoAtividade(): string
    {
        return match ($this) {
            self::COMPRA => 'fornecimento',
            self::SERVICO => 'serviço',
        };
    }

    public function getDisplayName(): string
    {
        return match ($this) {
            self::COMPRA => 'Compra (Fornecimento)',
            self::SERVICO => 'Serviço',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
