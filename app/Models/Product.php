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
        'stock',
        'image',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'status' => 'boolean',
    ];
    
    protected $appends = ['final_price'];

    // 🔗 Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getFinalPriceAttribute() {
        return $this->price - ($this->price * $this->discount / 100);
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
}

