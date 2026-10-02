<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Certificate extends Model
{
    use HasTranslations, SoftDeletes;

    protected $fillable = [
        'title_ar',
        'title_en',
        'issuer_ar',
        'issuer_en',
        'description_ar',
        'description_en',
        'image',
        'issued_year',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'issued_year' => 'integer',
    ];

    protected array $translatable = ['title', 'issuer', 'description'];

    /** Active rows in the order the admin chose. */
    public function scopeShown(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }
}
