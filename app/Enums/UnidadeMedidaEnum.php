<?php

namespace App\Enums;

enum UnidadeMedidaEnum: string implements DisplayNameable
{
    case AMP = 'AMP';
    case BLD = 'BLD';
    case BAR = 'BAR';
    case BLC = 'BLC';
    case CX = 'CX';
    case CAP = 'CAP';
    case CRT = 'CRT';
    case CMT = 'CMT';
    case CEN = 'CEN';
    case CMP = 'CMP';
    case DIA = 'DIA';
    case DUZ = 'DUZ';
    case FDO = 'FDO';
    case FL = 'FL';
    case FSC = 'FSC';
    case G = 'G';
    case HHR = 'HHR';
    case HRA = 'HRA';
    case JGO = 'JGO';
    case KIT = 'KIT';
    case LTA = 'LTA';
    case L = 'L';
    case MCO = 'MÇO';
    case MES = 'MES';
    case MT = 'MT';
    case MT3 = 'MT3';
    case MT2 = 'MT2';
    case MIL = 'MIL';
    case MG = 'MG';
    case ML = 'ML';
    case PCT = 'PCT';
    case PAR = 'PAR';
    case KG = 'KG';
    case KM = 'KM';
    case RES = 'RES';
    case SCH = 'SCH';
    case SAC = 'SAC';
    case SRG = 'SRG';
    case SV = 'SV';
    case TON = 'TON';
    case UND = 'UND';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::AMP => 'Ampola',
            self::BLD => 'Balde',
            self::BAR => 'Barra',
            self::BLC => 'Bloco',
            self::CX => 'Caixa',
            self::CAP => 'Capsula',
            self::CRT => 'Cartela',
            self::CMT => 'Centímetro',
            self::CEN => 'Cento',
            self::CMP => 'Comprimido',
            self::DIA => 'Diária',
            self::DUZ => 'Dúzia',
            self::FDO => 'Fardo',
            self::FL => 'Folha',
            self::FSC => 'Frasco',
            self::G => 'Grama',
            self::HHR => 'Homem/Hora',
            self::HRA => 'Hora',
            self::JGO => 'Jogo',
            self::KIT => 'Kit',
            self::LTA => 'Lata',
            self::L => 'Litro',
            self::MCO => 'Maço',
            self::MES => 'Mês',
            self::MT => 'Metro',
            self::MT3 => 'Metro Cúbico',
            self::MT2 => 'Metro Quadrado',
            self::MIL => 'Milheiro',
            self::MG => 'Miligrama',
            self::ML => 'Mililitro',
            self::PCT => 'Pacote',
            self::PAR => 'Par',
            self::KG => 'Quilograma',
            self::KM => 'Quilômetro',
            self::RES => 'Resma',
            self::SCH => 'Sachê',
            self::SAC => 'Saco',
            self::SRG => 'Seringa',
            self::SV => 'Serviço',
            self::TON => 'Tonelada',
            self::UND => 'Unidade',
        };
    }

    public static function casesWithLabels(): array
    {
        $cases = [];
        foreach (self::cases() as $case) {
            $cases[$case->value] = "{$case->value} ({$case->getDisplayName()})";
        }
        return $cases;
    }
}
