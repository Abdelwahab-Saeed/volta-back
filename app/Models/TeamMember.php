<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeamMember extends Model
{
    use HasTranslations, SoftDeletes;

    protected $fillable = [
        'name_ar',
        'name_en',
        'role_ar',
        'role_en',
        'bio_ar',
        'bio_en',
        'photo',
        'linkedin_url',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected array $translatable = ['name', 'role', 'bio'];

    /** Active rows in the order the admin chose. */
    public function scopeShown(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }
}
