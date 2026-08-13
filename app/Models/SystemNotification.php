<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Translatable\HasTranslations;
use App\Traits\AutoTranslateTranslations;

class SystemNotification extends Model
{
    use HasFactory, HasTranslations, AutoTranslateTranslations;

    public array $translatable = ['title', 'content'];

    protected $fillable = [
        'type',
        'title',
        'content',
        'is_read',
        'target_url',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];
}
