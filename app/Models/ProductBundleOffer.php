<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Model;

class ProductBundleOffer extends Model
{
    protected $fillable = [
        'product_id',
        'quantity',
        'bundle_price',
        'is_active',
    ];

    // Pre-piasters backup columns, removed by the drop_legacy_money_columns migration.
    protected $hidden = ['bundle_price_legacy'];

    protected $casts = [
        'bundle_price' => MoneyCast::class,
        'is_active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
