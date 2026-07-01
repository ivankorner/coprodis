<?php

namespace App\Helpers;

class Sanitizer
{
    public static function string(string $value): string
    {
        return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8', false);
    }

    public static function email(string $value): string
    {
        return filter_var(trim($value), FILTER_SANITIZE_EMAIL);
    }

    public static function url(string $value): string
    {
        return filter_var(trim($value), FILTER_SANITIZE_URL);
    }

    public static function int($value): int
    {
        return (int)filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    public static function float($value): float
    {
        return (float)filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    public static function phone(string $value): string
    {
        return preg_replace('/[^\d\s\-\+\(\)]/', '', trim($value));
    }

    public static function dni(string $value): string
    {
        return preg_replace('/[^\d]/', '', trim($value));
    }

    public static function text(string $value): string
    {
        $value = strip_tags($value, '<br><p><b><i><u><strong><em><a><ul><ol><li>');
        return trim($value);
    }

    public static function filename(string $value): string
    {
        $value = preg_replace('/[^\w\s\.\-]/', '', $value);
        $value = preg_replace('/\s+/', '_', $value);
        return strtolower(trim($value));
    }

    public static function slug(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9\-]/', '-', $value);
        $value = preg_replace('/-+/', '-', $value);
        return trim($value, '-');
    }

    public static function html(array $allowedTags = []): void
    {
        // Placeholder for HTML Purifier integration if needed
    }
}
