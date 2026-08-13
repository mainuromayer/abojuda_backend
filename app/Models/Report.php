<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Post;
use App\Models\User;

use Spatie\Translatable\HasTranslations;
use App\Traits\AutoTranslateTranslations;

class Report extends Model
{
    use HasFactory, HasTranslations, AutoTranslateTranslations;

    public array $translatable = ['reason', 'other_note'];

    protected $fillable = [
        'user_id',
        'post_id',
        'reported_user_id',
        'report_type',
        'reason',
        'other_note',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
