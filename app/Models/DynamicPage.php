<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use App\Traits\AutoTranslateTranslations;

class DynamicPage extends Model
{
    use HasTranslations, AutoTranslateTranslations;

    public array $translatable = ['title', 'content'];

    protected $fillable = [
        'title',
        'slug',
        'content',
    ];
}
