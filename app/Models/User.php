<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enum\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Post;
use App\Models\Wish;
use App\Models\Report;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, softDeletes, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
        'province',
        'city',
        'address',
        'status',
        'role',
        'blocked_users',
        'new_message',
        'marketing',
        'email_notification',
        'guest_token',
        'guest_expires_at',
        'provider',
        'provider_id',
        'remember_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
            'new_message' => 'boolean',
            'marketing' => 'boolean',
            'email_notification' => 'boolean',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($user) {
            // Generate uuid only if it's not already set
            if (empty($user->uuid)) {
                $lastCode = self::max('uuid') ?? 100000;
                $user->uuid = $lastCode + 1;
            }
        });
    }


    // JWT methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
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
