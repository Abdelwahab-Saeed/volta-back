<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['cart_id', 'product_id', 'quantity', 'price_snapshot'];

    // Pre-piasters backup columns, removed by the drop_legacy_money_columns migration.
    protected $hidden = ['price_snapshot_legacy'];

    protected $casts = [
        'price_snapshot' => MoneyCast::class,
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the effective price for this cart item.
     * Uses price snapshot if available, otherwise falls back to current product price (piasters).
     */
    public function getEffectivePrice(): ?int
    {
        return $this->price_snapshot ?? $this->product->final_price;
    }
}
