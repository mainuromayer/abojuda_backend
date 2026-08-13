<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Post;

use App\Helpers\helpers;

class BusinessHour extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'sunday',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
    ];

    protected $casts = [
        'sunday' => 'array',
        'monday' => 'array',
        'tuesday' => 'array',
        'wednesday' => 'array',
        'thursday' => 'array',
        'friday' => 'array',
        'saturday' => 'array',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function getSundayAttribute($value)
    {
        return helpers::translateBusinessHourDay($this->castAttribute('sunday', $value), app()->getLocale());
    }

    public function getMondayAttribute($value)
    {
        return helpers::translateBusinessHourDay($this->castAttribute('monday', $value), app()->getLocale());
    }

    public function getTuesdayAttribute($value)
    {
        return helpers::translateBusinessHourDay($this->castAttribute('tuesday', $value), app()->getLocale());
    }

    public function getWednesdayAttribute($value)
    {
        return helpers::translateBusinessHourDay($this->castAttribute('wednesday', $value), app()->getLocale());
    }

    public function getThursdayAttribute($value)
    {
        return helpers::translateBusinessHourDay($this->castAttribute('thursday', $value), app()->getLocale());
    }

    public function getFridayAttribute($value)
    {
        return helpers::translateBusinessHourDay($this->castAttribute('friday', $value), app()->getLocale());
    }

    public function getSaturdayAttribute($value)
    {
        return helpers::translateBusinessHourDay($this->castAttribute('saturday', $value), app()->getLocale());
    }
}
