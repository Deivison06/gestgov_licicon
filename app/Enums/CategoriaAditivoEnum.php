<?php

namespace App\Enums;

/**
 * Espelha os valores já usados como string crua na coluna
 * `incidentes_contratuais.categoria`. NÃO aplicado como cast do Eloquent —
 * ver nota em IncidenteContratual::class. Usado apenas em validações novas
 * (Rule::enum) e no ViewModel de geração de PDF.
 */
enum CategoriaAditivoEnum: string implements DisplayNameable
{
    case COMPRAS_SERVICOS = 'compras_servicos';
    case OBRAS = 'obras';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::COMPRAS_SERVICOS => 'Compras e Serviços',
            self::OBRAS => 'Obras e Serviços de Engenharia',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
