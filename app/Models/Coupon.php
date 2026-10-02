<?php

namespace App\Models;

use App\Support\Money;
use App\Casts\FixedOrPercentCast;
use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Coupon extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPES = ['fixed', 'percent'];

    // What the admin list filters by (see state() and scopeInState()).
    public const STATES = ['valid', 'scheduled', 'expired', 'exhausted'];

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
        'value' => FixedOrPercentCast::class, // piasters for fixed, whole percent for percent
        'min_order_amount' => MoneyCast::class,
    ];

    /**
     * Coupons in one of STATES, using the same checks as isValid() (minus the order minimum).
     * Expired wins, then used up, then not started yet. The whereNotNull checks keep whereNot() true for NULL columns.
     */
    public function scopeInState($query, string $state)
    {
        $expired = fn ($q) => $q->whereNotNull('expires_at')->where('expires_at', '<', now());
        $exhausted = fn ($q) => $q->whereNotNull('max_uses')->where('max_uses', '>', 0)->whereColumn('times_used', '>=', 'max_uses');
        $scheduled = fn ($q) => $q->whereNotNull('starts_at')->where('starts_at', '>', now());

        return match ($state) {
            'expired'   => $query->where($expired),
            'exhausted' => $query->whereNot($expired)->where($exhausted),
            'scheduled' => $query->whereNot($expired)->whereNot($exhausted)->where($scheduled),
            'valid'     => $query->whereNot($expired)->whereNot($exhausted)->whereNot($scheduled),
            default     => $query,
        };
    }

    /**
     * One of STATES, for the admin list.
     */
    public function state(): string
    {
        if ($this->expires_at && $this->expires_at->isPast()) return 'expired';
        if ($this->max_uses && $this->times_used >= $this->max_uses) return 'exhausted';
        if ($this->starts_at && $this->starts_at->isFuture()) return 'scheduled';
        return 'valid';
    }

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
