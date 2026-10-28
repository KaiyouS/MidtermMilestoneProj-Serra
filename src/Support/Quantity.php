<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Ingredient amounts. Members type things like "2", "1.5", "1/2" or "1 1/2";
 * we store a decimal so the servings scaler can multiply it, and show it back
 * as a kitchen-friendly fraction. public/assets/js/app.js has the same formatter.
 */
final class Quantity
{
    /** Accepted input formats, without anchors so it can also go in an HTML pattern attribute. */
    public const PATTERN = '\d{1,4}(?:\.\d{1,3})?|\d{1,3}/\d{1,3}|\d{1,4} \d{1,3}/\d{1,3}';

    public const MAX = 9999;

    private const FRACTIONS = [
        [1 / 8, '⅛', '1/8'],
        [1 / 4, '¼', '1/4'],
        [1 / 3, '⅓', '1/3'],
        [3 / 8, '⅜', '3/8'],
        [1 / 2, '½', '1/2'],
        [5 / 8, '⅝', '5/8'],
        [2 / 3, '⅔', '2/3'],
        [3 / 4, '¾', '3/4'],
        [7 / 8, '⅞', '7/8'],
    ];

    /** Returns the amount as a number, or null if the text isn't a valid positive amount. */
    public static function parse(string $raw): ?float
    {
        $raw = trim((string) preg_replace('/\s+/', ' ', $raw));
        if (!preg_match('~^(?:' . self::PATTERN . ')$~', $raw)) {
            return null;
        }

        $value = 0.0;
        foreach (explode(' ', $raw) as $part) {
            if (str_contains($part, '/')) {
                [$top, $bottom] = array_map('intval', explode('/', $part));
                if ($bottom === 0) {
                    return null;
                }
                $value += $top / $bottom;
            } else {
                $value += (float) $part;
            }
        }

        return ($value > 0 && $value <= self::MAX) ? round($value, 3) : null;
    }

    /**
     * 1.5 → "1½" for display, or "1 1/2" when $plain is true (for refilling the edit form).
     */
    public static function format(float $value, bool $plain = false): string
    {
        $whole = (int) floor($value);
        $rest = $value - $whole;

        if ($rest < 0.02) {
            return (string) $whole;
        }
        if ($rest > 0.98) {
            return (string) ($whole + 1);
        }

        foreach (self::FRACTIONS as [$amount, $glyph, $ascii]) {
            if (abs($rest - $amount) < 0.02) {
                if ($plain) {
                    return $whole > 0 ? "{$whole} {$ascii}" : $ascii;
                }
                return ($whole > 0 ? (string) $whole : '') . $glyph;
            }
        }

        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }
}
