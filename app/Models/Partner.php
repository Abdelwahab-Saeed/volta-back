<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use HasTranslations;

    public const TYPE_PARTNER = 'partner';
    public const TYPE_CLIENT = 'client';
    public const TYPES = [self::TYPE_PARTNER, self::TYPE_CLIENT];

    protected $fillable = [
        'name_ar',
        'name_en',
        'type',
        'logo',
        'website_url',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected array $translatable = ['name'];

    /** Active rows in the order the admin chose. */
    public function scopeShown(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }
}
