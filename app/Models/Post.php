<?php

namespace App\Models;

use App\Helpers\helpers;
use App\Models\BusinessHour;
use App\Models\Category;
use App\Models\Report;
use App\Models\User;
use App\Models\Wish;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use App\Traits\AutoTranslateTranslations;

class Post extends Model
{
    use HasFactory, HasTranslations, AutoTranslateTranslations;

    public array $translatable = ['title', 'description', 'company_name', 'business_name', 'province', 'city', 'address', 'business_category'];

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'company_name',
        'business_name',
        'business_category',
        'description',
        'price',
        'condition',
        'job_type',
        'service_area',
        'logo',
        'thumbnail',
        'photos',
        'province',
        'city',
        'address',
        'latitude',
        'longitude',
        'phone',
        'email',
        'whatsapp',
        'website',
        'is_app_chat',
        'is_featured',
        'status',
        'views',
    ];

    protected static function booted()
    {
        static::deleting(function ($post) {
            if ($post->thumbnail) {
                helpers::deleteFile($post->thumbnail, config('app.file_system'));
            }
            if ($post->logo) {
                helpers::deleteFile($post->logo, config('app.file_system'));
            }
            if (is_array($post->photos)) {
                foreach ($post->photos as $path) {
                    helpers::deleteFile($path, config('app.file_system'));
                }
            }
            $post->wishes()->delete();
            $post->reports()->delete();
        });
    }

    protected $appends = [
        'is_wish',
    ];

    public function getIsWishAttribute(): bool
    {
        $userId = auth('api')->id();
        if (!$userId) {
            return false;
        }
        return $this->wishes()->where('user_id', $userId)->exists();
    }

    protected $casts = [
        'condition' => 'array',
        'job_type' => 'array',
        'service_area' => 'array',
        'photos' => 'array',
        'is_app_chat' => 'boolean',
        'is_featured' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function setPriceAttribute($value): void
    {
        if ($value !== null && is_string($value)) {
            $value = str_replace(
                ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩', '٫'],
                ['0','1','2','3','4','5','6','7','8','9', '.'],
                $value
            );
        }
        $this->attributes['price'] = $value;
    }

    public function getPriceAttribute($value)
    {
        if ($value !== null && is_string($value)) {
            $value = str_replace(
                ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩', '٫'],
                ['0','1','2','3','4','5','6','7','8','9', '.'],
                $value
            );
        }
        return $value;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function businessHours()
    {
        return $this->hasOne(BusinessHour::class);
    }

    public function wishes()
    {
        return $this->hasMany(Wish::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function histories()
    {
        return $this->hasMany(PostHistory::class, 'post_id')->latest();
    }
}
