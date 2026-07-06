<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enum\Role;
use App\Helpers\helpers;
use App\Models\Post;
use App\Models\Report;
use App\Models\Wish;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;

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
        'avatar',
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
            'blocked_users' => 'array',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($user) {
            // Generate uuid only if it's not already set
            if (empty($user->uuid)) {
                $lastCode = self::withTrashed()->max('uuid') ?? 100000;
                $user->uuid = $lastCode + 1;
            }
        });

        static::deleting(function ($user) {
            if ($user->avatar) {
                helpers::deleteFile($user->avatar, config('app.file_system'));
            }

            // Delete posts (triggers post deleting observer to clean files/wishes/reports)
            foreach ($user->posts as $post) {
                $post->delete();
            }

            // Delete wishes
            $user->wishes()->delete();

            // Delete reports
            $user->reports()->delete();
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

    public function getBlockedUserIds(): array
    {
        $blocked = is_array($this->blocked_users) ? $this->blocked_users : [];
        $blockedIds = array_map(fn($item) => (int) ($item['user_id'] ?? 0), $blocked);

        $blockedBy = self::whereJsonContains('blocked_users', ['user_id' => $this->id])
            ->pluck('id')
            ->toArray();

        return array_unique(array_merge($blockedIds, $blockedBy));
    }

    public function reportsAgainst()
    {
        return $this->hasMany(Report::class, 'reported_user_id');
    }

    public function firebaseTokens()
    {
        return $this->hasMany(FirebaseToken::class);
    }
}
