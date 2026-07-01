<?php

namespace App\Helpers;

class DateHelper
{
    public static function now(): string
    {
        return date('Y-m-d H:i:s');
    }

    public static function today(): string
    {
        return date('Y-m-d');
    }

    public static function format(string $date, string $format = 'd/m/Y H:i'): string
    {
        if (empty($date) || $date === '0000-00-00 00:00:00') {
            return '-';
        }
        return date($format, strtotime($date));
    }

    public static function formatDate(string $date): string
    {
        return self::format($date, 'd/m/Y');
    }

    public static function formatTime(string $date): string
    {
        return self::format($date, 'H:i');
    }

    public static function formatDateTime(string $date): string
    {
        return self::format($date, 'd/m/Y H:i');
    }

    public static function diffForHumans(string $date): string
    {
        if (empty($date)) return '-';

        $now = time();
        $time = strtotime($date);
        $diff = $now - $time;

        if ($diff < 0) return 'En el futuro';

        $intervals = [
            31536000 => 'año',
            2592000 => 'mes',
            604800 => 'semana',
            86400 => 'día',
            3600 => 'hora',
            60 => 'minuto',
            1 => 'segundo',
        ];

        foreach ($intervals as $seconds => $label) {
            $count = floor($diff / $seconds);
            if ($count >= 1) {
                $plural = $count > 1 ? 's' : '';
                return "Hace {$count} {$label}{$plural}";
            }
        }

        return 'Ahora';
    }

    public static function isToday(string $date): bool
    {
        return date('Y-m-d', strtotime($date)) === date('Y-m-d');
    }

    public static function isThisMonth(string $date): bool
    {
        return date('Y-m', strtotime($date)) === date('Y-m');
    }

    public static function monthsBetween(string $start, string $end): array
    {
        $months = [];
        $start = strtotime($start);
        $end = strtotime($end);

        while ($start <= $end) {
            $months[] = date('Y-m', $start);
            $start = strtotime('+1 month', $start);
        }

        return $months;
    }

    public static function daysInMonth(int $year, int $month): int
    {
        return cal_days_in_month(CAL_GREGORIAN, $month, $year);
    }

    public static function age(string $birthDate): int
    {
        $birth = new \DateTime($birthDate);
        $now = new \DateTime();
        return (int)$birth->diff($now)->y;
    }
}
