<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = ['user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Load the items the customer can still buy. Lines of a hidden or deleted product stay in the table
     * (they come back if the product does) but are never shown or charged.
     */
    public function loadAvailableItems(): static
    {
        return $this->load(['items' => fn ($items) => $items
            ->whereHas('product', fn ($product) => $product->visible())
            ->with('product')]);
    }
}
