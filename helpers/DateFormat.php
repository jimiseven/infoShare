<?php
declare(strict_types=1);

class DateFormat
{
    public static function dueEs(?string $datetime): string
    {
        if ($datetime === null || trim($datetime) === '') {
            return '-';
        }

        $ts = strtotime($datetime);
        if ($ts === false) {
            return '-';
        }

        $days = [
            'Monday' => 'lunes',
            'Tuesday' => 'martes',
            'Wednesday' => 'miercoles',
            'Thursday' => 'jueves',
            'Friday' => 'viernes',
            'Saturday' => 'sabado',
            'Sunday' => 'domingo',
        ];

        $dayEn = date('l', $ts);
        $dayEs = $days[$dayEn] ?? strtolower($dayEn);

        $months = [
            1 => 'enero',
            2 => 'febrero',
            3 => 'marzo',
            4 => 'abril',
            5 => 'mayo',
            6 => 'junio',
            7 => 'julio',
            8 => 'agosto',
            9 => 'septiembre',
            10 => 'octubre',
            11 => 'noviembre',
            12 => 'diciembre',
        ];

        $monthEs = $months[(int)date('n', $ts)] ?? date('m', $ts);

        return $dayEs . ' - ' . date('d', $ts) . ' ' . $monthEs;
    }
}
