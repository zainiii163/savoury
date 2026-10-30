<?php

namespace App\Support;

final class Money
{
    public static function scale(): int
    {
        return (int) config('savoury.money_scale', 100);
    }

    /**
     * Convert major PKR units (e.g. 375) to minor units (37500).
     */
    public static function toMinor(int|float|string $major): int
    {
        return (int) round(((float) $major) * self::scale());
    }

    /**
     * Convert minor units to major PKR for display/API contracts.
     */
    public static function toMajor(int $minor): int|float
    {
        $scale = self::scale();

        if ($minor % $scale === 0) {
            return intdiv($minor, $scale);
        }

        return $minor / $scale;
    }
}
