<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasTranslations, SoftDeletes;

    protected $fillable = ['title_ar', 'title_en', 'description_ar', 'description_en', 'image'];

    protected array $translatable = ['title', 'description'];
}
