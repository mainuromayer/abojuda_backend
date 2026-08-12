<?php

namespace App\Models;

use App\Helpers\helpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'media',
        'media_type',
        'back_link',
        'status',
    ];

    protected static function booted()
    {
        static::deleting(function ($ad) {
            if ($ad->media) {
                helpers::deleteFile($ad->media, config('app.file_system'));
            }
        });
    }
}
