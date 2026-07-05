<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostHistory extends Model
{
    protected $fillable = ['post_id', 'old_data', 'new_data'];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
