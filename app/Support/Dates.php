<?php

namespace App\Support;

use Carbon\CarbonInterface;

class Dates
{
    public const DAYS = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

    public const MONTHS = [
        1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
        'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre',
    ];

    /** "Jueves, 29 de mayo de 2026" */
    public static function long(CarbonInterface $date): string
    {
        $day   = self::DAYS[(int) $date->format('w')];
        $month = self::MONTHS[(int) $date->format('n')];

        return "{$day}, {$date->format('j')} de {$month} de {$date->format('Y')}";
    }

    /** "Mayo 29, 2026" */
    public static function short(CarbonInterface $date): string
    {
        $month = self::MONTHS[(int) $date->format('n')];
        $cap   = mb_strtoupper(mb_substr($month, 0, 1)) . mb_substr($month, 1);

        return "{$cap} {$date->format('j')}, {$date->format('Y')}";
    }

    /**
     * Same calendar date +1, +2, +3 years.
     *
     * @return array<int, CarbonInterface>
     */
    public static function nextYears(CarbonInterface $today): array
    {
        return collect([1, 2, 3])
            ->map(fn (int $n) => $today->copy()->addYears($n))
            ->all();
    }
}
