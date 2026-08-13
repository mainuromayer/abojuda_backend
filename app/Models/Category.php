<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Post;
use Spatie\Translatable\HasTranslations;
use App\Traits\AutoTranslateTranslations;

class Category extends Model
{
    use HasFactory, HasTranslations, AutoTranslateTranslations;

    public array $translatable = ['name', 'shot_desc'];

    protected $fillable = [
        'name',
        'slug',
        'shot_desc',
        'icon',
        'status',
    ];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

}
