<?php
const ES_DAYS = [
    'Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado',
];
const ES_MONTHS = [
    1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
    'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre',
];

/** "Jueves, 29 de mayo de 2026" */
function formatDate(DateTime $date): string {
    $dow   = (int) $date->format('w');
    $day   = $date->format('j');
    $month = ES_MONTHS[(int) $date->format('n')];
    $year  = $date->format('Y');
    return ES_DAYS[$dow] . ", {$day} de {$month} de {$year}";
}

/** "Mayo 29, 2026" */
function formatDateShort(DateTime $date): string {
    $month = ES_MONTHS[(int) $date->format('n')];
    $cap   = mb_strtoupper(mb_substr($month, 0, 1)) . mb_substr($month, 1);
    return "{$cap} {$date->format('j')}, {$date->format('Y')}";
}

/** Returns array of 3 DateTimes: today+1yr, +2yr, +3yr */
function getYearDates(DateTime $today): array {
    $dates = [];
    for ($i = 1; $i <= 3; $i++) {
        $d = clone $today;
        $d->modify("+{$i} year");
        $dates[] = $d;
    }
    return $dates;
}
