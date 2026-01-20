<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBundleOffer extends Model
{
    protected $fillable = [
        'product_id',
        'quantity',
        'bundle_price',
        'is_active',
    ];

    protected $casts = [
        'bundle_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
