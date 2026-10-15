<?php

namespace App\Enums;

/**
 * Espelha os valores já usados como string crua na coluna
 * `incidentes_contratuais.tipo`. NÃO aplicado como cast do Eloquent — ver
 * nota em IncidenteContratual::class. Usado apenas em validações novas
 * (Rule::enum) e no ViewModel de geração de PDF.
 */
enum TipoAditivoEnum: string implements DisplayNameable
{
    case PRAZO = 'prazo';
    case VALOR = 'valor';
    case PRAZO_VALOR = 'prazo_valor';

    public function envolvePrazo(): bool
    {
        return $this !== self::VALOR;
    }

    public function envolveValor(): bool
    {
        return $this !== self::PRAZO;
    }

    public function getDisplayName(): string
    {
        return match ($this) {
            self::PRAZO => 'Prorrogação de Prazo',
            self::VALOR => 'Acréscimo/Supressão de Valor',
            self::PRAZO_VALOR => 'Prorrogação de Prazo e Alteração de Valor',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
