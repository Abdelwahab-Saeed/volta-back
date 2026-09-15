<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductFeature extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = ['product_id', 'name_ar', 'name_en'];

    protected array $translatable = ['name'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
