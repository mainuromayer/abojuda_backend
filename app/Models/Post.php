<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Category;
use App\Models\BusinessHour;
use App\Models\Wish;
use App\Models\Report;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'company_name',
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
        'phone',
        'email',
        'whatsapp',
        'website',
        'is_app_chat',
        'is_featured',
        'status',
    ];

    protected $casts = [
        'condition' => 'array',
        'job_type' => 'array',
        'service_area' => 'array',
        'photos' => 'array',
        'is_app_chat' => 'boolean',
        'is_featured' => 'boolean',
        'price' => 'decimal:2',
    ];

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
}
