<?php

namespace App\Support;

class VehiclePlate
{
    public static function extract(string $text): ?string
    {
        $display = self::display($text);

        return $display ? self::normalize($display) : null;
    }

    public static function display(string $text): ?string
    {
        $trimmed = trim($text);

        if (self::isStandalone($trimmed)) {
            return self::formatForStorage($trimmed);
        }

        if (preg_match('/\b([A-Z]{1,4}-?[0-9]{2,5}[A-Z]{0,3})\b/i', $text, $matches)) {
            return self::formatForStorage($matches[1]);
        }

        return null;
    }

    public static function isStandalone(string $text): bool
    {
        $text = trim($text);

        if (mb_strlen($text) < 3 || mb_strlen($text) > 20) {
            return false;
        }

        if (! preg_match('/^[A-Z0-9](?:[A-Z0-9.\-\s]*[A-Z0-9])?$/i', $text)) {
            return false;
        }

        $alnum = self::normalize($text);

        if (strlen($alnum) < 4 || strlen($alnum) > 12) {
            return false;
        }

        if (preg_match_all('/[A-Za-z]{2,}/', $text) >= 2) {
            return false;
        }

        return (bool) preg_match('/[A-Z]/i', $alnum) && (bool) preg_match('/\d/', $alnum);
    }

    public static function normalize(string $plate): string
    {
        return strtoupper(preg_replace('/[^A-Z0-9]/i', '', $plate) ?? '');
    }

    public static function formatForStorage(string $plate): string
    {
        $formatted = strtoupper(trim(preg_replace('/\s+/', '-', $plate) ?? $plate));

        return mb_substr($formatted, 0, 20);
    }
}
