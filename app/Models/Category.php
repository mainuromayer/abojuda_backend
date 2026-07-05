<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Post;

class Category extends Model
{
    use HasFactory;

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
