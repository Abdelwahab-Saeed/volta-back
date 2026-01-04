<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['cart_id', 'product_id', 'quantity', 'price_snapshot'];

    protected $casts = [
        'price_snapshot' => 'decimal:2',
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
     * Uses price snapshot if available, otherwise falls back to current product price.
     */
    public function getEffectivePrice(): ?float
    {
        return $this->price_snapshot ?? $this->product->final_price;
    }
}
