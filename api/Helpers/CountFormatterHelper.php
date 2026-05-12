<?php

namespace Shive\BlogManagementSystem\Helpers;

class CountFormatterHelper
{
    public static function format(int|float $number): string
    {
        if ($number < 1000) {
            return (string) $number;
        }

        if ($number < 1000000) {
            return self::formatSuffix($number / 1000) . 'K';
        }

        if ($number < 1000000000) {
            return self::formatSuffix($number / 1000000) . 'M';
        }

        return self::formatSuffix($number / 1000000000) . 'B';
    }

    private static function formatSuffix(float $value): string
    {
        $formatted = number_format($value, 1, '.', '');

        if (str_ends_with($formatted, '.0')) {
            return substr($formatted, 0, -2);
        }

        return $formatted;
    }
}
