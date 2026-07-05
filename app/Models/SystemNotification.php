<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemNotification extends Model
{
    use HasFactory;

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
