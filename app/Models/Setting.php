<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Spatie\Translatable\HasTranslations;
use App\Traits\AutoTranslateTranslations;

class Setting extends Model
{
    use HasTranslations, AutoTranslateTranslations;

    public array $translatable = ['system_title', 'copyright_text'];
    protected $fillable = [
        'system_title',
        'logo',
        'favicon',
        'copyright_text',
    ];
}
