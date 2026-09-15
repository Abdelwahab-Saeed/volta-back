<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Category extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $fillable = [
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'image',
        'status',
        'category_order',
    ];

    protected array $translatable = ['name', 'description'];

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
