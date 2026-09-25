<?php

namespace App\Support;

/**
 * Money is stored and calculated as integer piasters (قرش).
 * Pounds (جنيه) only exist at the edges: form input, API output and views.
 */
class Money
{
    /**
     * Convert a pounds value (e.g. "12.50" from a form) to integer piasters.
     */
    public static function fromPounds(string|int|float|null $pounds): ?int
    {
        if ($pounds === null || $pounds === '') {
            return null;
        }

        return (int) round(((float) $pounds) * 100);
    }

    /**
     * Convert the given pounds fields of request data to piasters, touching only keys that are present.
     */
    public static function fromPoundsFields(array $data, array $keys): array
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $data)) {
                $data[$key] = self::fromPounds($data[$key]);
            }
        }

        return $data;
    }

    /**
     * Convert integer piasters to pounds.
     */
    public static function toPounds(?int $piasters): ?float
    {
        if ($piasters === null) {
            return null;
        }

        return $piasters / 100;
    }

    /**
     * Pounds as a fixed two-decimal string, same shape the old decimal:2 casts produced ("250.00").
     */
    public static function toDecimalString(?int $piasters): ?string
    {
        if ($piasters === null) {
            return null;
        }

        return number_format($piasters / 100, 2, '.', '');
    }

    /**
     * Human readable pounds for views ("1,250.00"). Accepts SQL aggregate results (string/float) too.
     */
    public static function format(int|float|string|null $piasters): string
    {
        return number_format(((float) ($piasters ?? 0)) / 100, 2);
    }
}
