<?php

namespace App\Casts;

use App\Support\Money;
use Illuminate\Database\Eloquent\Model;

/**
 * A "value" column that holds piasters when the row's type is "fixed" and a whole percentage otherwise
 * (coupons.value, offers.value).
 */
class FixedOrPercentCast extends MoneyCast
{
    public function serialize(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (($attributes['type'] ?? null) === 'fixed') {
            return Money::toDecimalString($value);
        }

        return $value === null ? null : number_format((int) $value, 2, '.', '');
    }
}
