<?php

declare(strict_types=1);

namespace App\Enums;

enum Gejala: string
{
    case Pusing      = 'Pusing';
    case SakitKepala = 'Sakit Kepala';
    case Flu         = 'Flu';
    case Demam       = 'Demam';

    /** Biaya pemeriksaan per gejala. Data menempel di enum, bukan tersebar di if/switch. */
    public function biaya(): int
    {
        return match ($this) {
            self::Pusing      => 15000,
            self::SakitKepala => 20000,
            self::Flu         => 25000,
            self::Demam       => 30000,
        };
    }

    /** ['Pusing', 'Sakit Kepala', 'Flu', 'Demam'] */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
