<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'discount',
        'discount_price',
        'cost_price',
        'shipping_cost',
        'stock',
        'image',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'status' => 'boolean',
    ];
    
    protected $appends = ['final_price']; // Append translated values if we override getAttribute, but for API we might rely on Resource


    // 🔗 Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getFinalPriceAttribute() {
        if ($this->discount_price > 0) {
            return $this->discount_price;
        }
        return $this->price;
    }

    public function wishlistedBy()
    {
        return $this->belongsToMany(User::class, 'wishlists')->withTimestamps();
    }

    public function comparedBy()
    {
        return $this->belongsToMany(User::class, 'comparisons')->withTimestamps();
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function bundleOffers()
    {
        return $this->hasMany(ProductBundleOffer::class)->orderBy('quantity', 'asc');
    }

    public function features()
    {
        return $this->hasMany(ProductFeature::class);
    }

    public function extraImages()
    {
        return $this->hasMany(ProductImage::class);
    }
}

