<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductQuantityDiscount extends Model
{
    protected $fillable = [
        'product_id',
        'min_quantity',
        'discount_percentage',
        'is_active',
    ];

    protected $casts = [
        'discount_percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
