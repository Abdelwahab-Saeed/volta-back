<?php

namespace App\Casts;

use App\Support\Money;
use Illuminate\Database\Eloquent\Model;

/**
 * coupons.value holds piasters for "fixed" coupons and a whole percentage for "percent" coupons.
 */
class CouponValueCast extends MoneyCast
{
    public function serialize(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (($attributes['type'] ?? null) === 'fixed') {
            return Money::toDecimalString($value);
        }

        return $value === null ? null : number_format((int) $value, 2, '.', '');
    }
}
