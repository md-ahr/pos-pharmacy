<?php

namespace App\Support;

use DateTimeInterface;
use Illuminate\Support\Carbon;

class DisplayDateTime
{
    public static function format(?DateTimeInterface $date, ?string $format = null, string $empty = '—'): string
    {
        if ($date === null) {
            return $empty;
        }

        return Carbon::parse($date)->format($format ?? (string) config('pharmacy.display.datetime'));
    }

    public static function time(?DateTimeInterface $date, string $empty = '—'): string
    {
        return self::format($date, (string) config('pharmacy.display.time'), $empty);
    }
}
