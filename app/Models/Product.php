<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Support\Money;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Product extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'price',
        'discount',
        'discount_price',
        'cost_price',
        'shipping_cost',
        'stock',
        'image',
        'preview_url',
        'status',
    ];

    protected array $translatable = ['name', 'description'];

    // Pre-piasters backup columns, removed by the drop_legacy_money_columns migration.
    protected $hidden = ['price_legacy', 'discount_price_legacy', 'cost_price_legacy', 'shipping_cost_legacy'];

    protected $casts = [
        'price' => MoneyCast::class,
        'discount' => 'decimal:2',
        'discount_price' => MoneyCast::class,
        'cost_price' => MoneyCast::class,
        'shipping_cost' => MoneyCast::class,
        'status' => 'boolean',
    ];
    
    protected $appends = ['final_price'];


    // 🔗 Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Selling price in piasters.
     */
    public function getFinalPriceAttribute(): ?int
    {
        if ($this->discount_price > 0) {
            return $this->discount_price;
        }
        return $this->price;
    }

    /**
     * Appended attributes skip cast serialization, so final_price is converted to pounds here
     * to match the other money fields in raw-model responses.
     */
    public function toArray()
    {
        $array = parent::toArray();

        if (array_key_exists('final_price', $array)) {
            $array['final_price'] = Money::toDecimalString($this->final_price);
        }

        return $array;
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

