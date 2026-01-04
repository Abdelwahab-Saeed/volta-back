<?php

namespace App\Models;

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

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
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
    
    public function calculateDiscount($totalAmount)
    {
        if ($this->type === 'fixed') {
            return min($this->value, $totalAmount);
        } elseif ($this->type === 'percent') {
            return $totalAmount * ($this->value / 100);
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
