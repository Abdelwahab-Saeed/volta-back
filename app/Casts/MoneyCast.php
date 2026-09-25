<?php

namespace App\Casts;

use App\Support\Money;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Integer piasters in the database and in PHP.
 * Serialized (toArray / JSON) as a pounds string ("250.00") so raw-model API responses keep their old shape.
 */
class MoneyCast implements CastsAttributes, SerializesCastableAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        return $value === null ? null : (int) $value;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Catch any caller that still passes pounds with a fractional part.
        if (!is_numeric($value) || (float) $value != (int) $value) {
            throw new InvalidArgumentException("Money attribute [{$key}] must be integer piasters, got [{$value}].");
        }

        return (int) $value;
    }

    public function serialize(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return Money::toDecimalString($value);
    }
}
