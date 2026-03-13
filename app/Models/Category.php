<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'image',
        'status',
        'category_order',
    ];

    protected $casts = [
        'status' => 'boolean',
        'category_order' => 'integer',
    ];

    protected $appends = [];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
