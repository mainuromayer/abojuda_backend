<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTranslations;

class DynamicPage extends Model
{
    use HasTranslations;

    public array $translatable = ['title', 'content'];

    protected $fillable = [
        'title',
        'slug',
        'content',
    ];
}
