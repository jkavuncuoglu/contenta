<?php

namespace App\Domains\Security\UserManagement\Models;

use app\Domains\ContentManagement\Models\Post;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasApiTokens, HasRoles, LogsActivity;

    /**
     * Ensure Spatie Permission uses the expected guard and morph type.
     */
    protected string $guard_name = 'web';

    /**
     * Force the morph class to match the base App\Models\User so existing pivot rows resolve.
     */
    public function getMorphClass(): string
    {
        return \App\Models\User::class;
    }


    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'password',
        'first_name',
        'last_name',
        'username',
        'bio',
        'avatar',
        'timezone',
        'language',
        'is_active',
        'last_login_at',
        'last_login_ip',
        'preferences',
        'social_links',
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

    protected $with = ['emails'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'preferences' => 'array',
            'social_links' => 'array',
        ];
    }

    protected static array $logAttributes = ['name', 'is_active'];
    protected static bool $logOnlyDirty = true;

    /**
     * Relationships
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    /**
     * Get the user's email addresses
     */
    public function emails(): HasMany
    {
        return $this->hasMany(UserEmail::class);
    }

    /**
     * Get the user's primary email address
     */
    public function primaryEmail(): HasMany
    {
        return $this->emails()->where('is_primary', true);
    }

    /**
     * Get full name attribute
     */
    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? '')) ?: $this->name;
    }

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(['super-admin', 'admin']);
    }

    /**
     * Check if user can edit posts
     */
    public function canEditPosts(): bool
    {
        return $this->hasAnyRole(['super-admin', 'admin', 'editor', 'author']);
    }

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'is_active'])
            ->logOnlyDirty();
    }
}
