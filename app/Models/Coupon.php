<?php

namespace App\Models;

use App\Support\Money;
use App\Casts\CouponValueCast;
use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order_amount',
        'starts_at',
        'expires_at',
        'max_uses',
        'times_used',
    ];

    // Pre-piasters backup columns, removed by the drop_legacy_money_columns migration.
    protected $hidden = ['value_legacy', 'min_order_amount_legacy'];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'value' => CouponValueCast::class, // piasters for fixed, whole percent for percent
        'min_order_amount' => MoneyCast::class,
    ];

    public function isValid($totalAmount)
    {
        if ($this->starts_at && Carbon::now()->lt($this->starts_at)) {
            return false;
        }

        if ($this->expires_at && Carbon::now()->gt($this->expires_at)) {
            return false;
        }

        if ($this->max_uses && $this->times_used >= $this->max_uses) {
            return false;
        }

        if ($this->min_order_amount && $totalAmount < $this->min_order_amount) {
            return false;
        }

        return true;
    }
    
    /**
     * Convert form/API input (pounds) to stored units. "value" is money only for fixed coupons.
     */
    public static function fromInput(array $data, ?string $currentType = null): array
    {
        $type = $data['type'] ?? $currentType;

        if ($type === 'fixed') {
            $data = Money::fromPoundsFields($data, ['value']);
        }

        return Money::fromPoundsFields($data, ['min_order_amount']);
    }

    /**
     * Discount in piasters for a total in piasters.
     */
    public function calculateDiscount(int $totalAmount): int
    {
        if ($this->type === 'fixed') {
            return min($this->value, $totalAmount);
        } elseif ($this->type === 'percent') {
            return (int) round($totalAmount * $this->value / 100);
        }
        return 0;
    }

    /**
     * Users who have used this coupon
     */
    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('order_id')
            ->withTimestamps();
    }

    /**
     * Check if a specific user has already used this coupon
     */
    public function hasBeenUsedByUser($userId): bool
    {
        return $this->users()->where('user_id', $userId)->exists();
    }
}
