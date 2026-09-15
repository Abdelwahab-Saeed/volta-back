<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasTranslations;

    protected $fillable = ['title_ar', 'title_en', 'description_ar', 'description_en', 'image'];

    protected array $translatable = ['title', 'description'];
}
