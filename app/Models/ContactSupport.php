<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Translatable\HasTranslations;
use App\Traits\AutoTranslateTranslations;

class ContactSupport extends Model
{
    use HasFactory, HasTranslations, AutoTranslateTranslations;

    public array $translatable = ['subject', 'describe_issue'];

    protected $fillable = [
        'email',
        'subject',
        'describe_issue',
        'attachment',
    ];
}
