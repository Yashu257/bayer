<?php

declare(strict_types=1);

namespace Core\Security;

/**
 * Sanitizer — XSS prevention helpers.
 * Used by middleware before data reaches controllers.
 */
final class Sanitizer
{
    /**
     * Escape a value for HTML output.
     * Always use this when echoing user-supplied data in views.
     */
    public static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Recursively sanitise an array of inputs (trim + strip tags).
     * Does NOT run htmlspecialchars — that is a view responsibility.
     */
    public static function cleanArray(array $data): array
    {
        return array_map(function (mixed $value): mixed {
            if (is_array($value)) {
                return self::cleanArray($value);
            }
            if (is_string($value)) {
                return strip_tags(trim($value));
            }
            return $value;
        }, $data);
    }

    public static function cleanString(string $value): string
    {
        return strip_tags(trim($value));
    }

    /** Allow only alphanumeric, hyphens, underscores. */
    public static function slug(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9\-_]/', '-', $value) ?? '';
        return preg_replace('/-{2,}/', '-', $value) ?? '';
    }

    /** Strip everything except digits. */
    public static function digits(string $value): string
    {
        return preg_replace('/\D/', '', $value) ?? '';
    }

    public static function email(string $value): string|false
    {
        return filter_var(trim($value), FILTER_SANITIZE_EMAIL);
    }

    public static function url(string $value): string|false
    {
        return filter_var(trim($value), FILTER_SANITIZE_URL);
    }
}
