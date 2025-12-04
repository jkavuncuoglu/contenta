<?php

namespace App\Domains\Security\UserManagement\Models;

use App\Domains\ContentManagement\Posts\Models\Post;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laragear\WebAuthn\Contracts\WebAuthnAuthenticatable;
use Laragear\WebAuthn\WebAuthnAuthentication;
use Laragear\WebAuthn\WebAuthnData;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property-read array<string, bool> $can
 * @property \Illuminate\Support\Carbon|null $recovery_codes_regeneration_expires_at
 */
class User extends Authenticatable implements MustVerifyEmail, WebAuthnAuthenticatable
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use HasRoles;
    use LogsActivity;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use WebAuthnAuthentication;

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
     * Create a new factory instance for the model
     */
    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
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

    // Eager-load emails and roles so the frontend receives role info
    protected $with = ['emails', 'roles'];

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
            'recovery_codes_regeneration_expires_at' => 'datetime',
        ];
    }

    protected static array $logAttributes = ['name', 'is_active'];

    protected static bool $logOnlyDirty = true;

    // Append permission-related attributes to serialized output for Inertia
    protected $appends = [
        'direct_permissions',
        'permissions_via_roles',
        'permission_names',
    ];

    /**
     * Relationships
     *
     * @return HasMany<Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    /**
     * Get the user's email addresses
     *
     * @return HasMany<UserEmail, $this>
     */
    public function emails(): HasMany
    {
        return $this->hasMany(UserEmail::class);
    }

    /**
     * Get the user's primary email address
     *
     * @return HasMany<UserEmail, $this>
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
        return trim(($this->first_name ?? '').' '.($this->last_name ?? '')) ?: $this->name;
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
     *
     * @param  \Illuminate\Database\Eloquent\Builder<User>  $query
     * @return \Illuminate\Database\Eloquent\Builder<User>
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

    /**
     * Serialized attribute: direct permissions assigned to the user.
     * Query the model_has_permissions pivot directly to avoid relying on trait helper methods.
     *
     * @return array<int, string>
     */
    public function getDirectPermissionsAttribute(): array
    {
        $modelTypes = [User::class, self::class];

        $permissionIds = DB::table('model_has_permissions')
            ->whereIn('model_type', $modelTypes)
            ->where('model_id', $this->getKey())
            ->pluck('permission_id')
            ->toArray();

        if (empty($permissionIds)) {
            return [];
        }

        return Permission::whereIn('id', $permissionIds)->pluck('name')->toArray();
    }

    /**
     * Compatibility alias: expose direct permissions as `permission_names` (renamed from `permissions`).
     *
     * @return array<int, string>
     */
    public function getPermissionNamesAttribute(): array
    {
        return $this->getDirectPermissionsAttribute();
    }

    /**
     * Serialized attribute: permissions the user has via roles.
     * Query role_has_permissions pivot using the user's roles.
     *
     * @return array<int, string>
     */
    public function getPermissionsViaRolesAttribute(): array
    {
        $roleIds = $this->roles->pluck('id')->toArray();

        if (empty($roleIds)) {
            return [];
        }

        $permissionIds = DB::table('role_has_permissions')
            ->whereIn('role_id', $roleIds)
            ->pluck('permission_id')
            ->toArray();

        if (empty($permissionIds)) {
            return [];
        }

        return Permission::whereIn('id', $permissionIds)->pluck('name')->unique()->toArray();
    }

    /**
     * Compatibility alias: expose permissions via roles as `permissions_via_role`.
     *
     * @return array<int, string>
     */
    public function getPermissionsViaRoleAttribute(): array
    {
        return $this->getPermissionsViaRolesAttribute();
    }

    /**
     * Serialized attribute: all permissions (role + direct) as names.
     * Combine direct and role-derived permissions.
     *
     * @return array<int, string>
     */
    public function getAllPermissionsAttribute(): array
    {
        $direct = $this->getDirectPermissionsAttribute();
        $viaRoles = $this->getPermissionsViaRolesAttribute();

        return array_values(array_unique(array_merge($direct, $viaRoles)));
    }

    /**
     * Serialized attribute: map of permission name => boolean (has permission).
     *
     * @return array<string, bool>
     */
    public function getCanAttribute(): array
    {
        $allPermissionNames = Permission::pluck('name')->toArray();
        $userPermissions = $this->getAllPermissionsAttribute();
        $userPermissionsMap = array_flip($userPermissions);

        $map = [];
        foreach ($allPermissionNames as $name) {
            $map[$name] = isset($userPermissionsMap[$name]);
        }

        return $map;
    }

    /**
     * Check if the user has Two-Factor Authentication enabled.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return ! is_null($this->two_factor_secret) && ! is_null($this->two_factor_confirmed_at);
    }

    /**
     * Get the available recovery codes count.
     */
    public function getAvailableRecoveryCodesCount(): int
    {
        $totalCodes = collect($this->recoveryCodes())->count();
        $usedCodes = collect($this->two_factor_used_recovery_codes ?? [])->count();

        return $totalCodes - $usedCodes;
    }

    /**
     * Check if recovery codes warning should be shown.
     */
    public function shouldShowRecoveryCodesWarning(): bool
    {
        return $this->hasTwoFactorEnabled() && $this->getAvailableRecoveryCodesCount() < 2;
    }

    /**
     * Mark a recovery code as used.
     */
    public function markRecoveryCodeAsUsed(string $code): void
    {
        $usedCodes = $this->two_factor_used_recovery_codes ?? [];
        $usedCodes[] = $code;

        $this->update([
            'two_factor_used_recovery_codes' => array_unique($usedCodes),
        ]);
    }

    /**
     * Check if recovery codes have been viewed.
     */
    public function hasViewedRecoveryCodes(): bool
    {
        return ! is_null($this->two_factor_recovery_codes_viewed_at);
    }

    /**
     * Mark recovery codes as viewed.
     */
    public function markRecoveryCodesAsViewed(): void
    {
        $this->update([
            'two_factor_recovery_codes_viewed_at' => now(),
        ]);
    }

    /**
     * Generate a token for recovery codes regeneration.
     */
    public function generateRecoveryCodesRegenerationToken(): string
    {
        $token = bin2hex(random_bytes(32));

        $this->update([
            'recovery_codes_regeneration_token' => $token,
            'recovery_codes_regeneration_expires_at' => now()->addHour(),
        ]);

        return $token;
    }

    /**
     * Validate recovery codes regeneration token.
     */
    public function validateRecoveryCodesRegenerationToken(string $token): bool
    {
        return $this->recovery_codes_regeneration_token === $token
            && $this->recovery_codes_regeneration_expires_at
            && $this->recovery_codes_regeneration_expires_at->isFuture();
    }

    /**
     * Clear recovery codes regeneration token.
     */
    public function clearRecoveryCodesRegenerationToken(): void
    {
        $this->update([
            'recovery_codes_regeneration_token' => null,
            'recovery_codes_regeneration_expires_at' => null,
        ]);
    }

    /**
     * Get the column name for the primary key (used by WebAuthn).
     * This should return the column name, not the value.
     */
    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    /**
     * Get the user's display name for WebAuthn.
     */
    public function getAuthDisplayName(): string
    {
        return trim($this->first_name.' '.$this->last_name) ?: ($this->username ?? $this->email ?? '');
    }

    /**
     * Returns displayable data to be used to create WebAuthn Credentials.
     * Override the trait method to use first_name and last_name instead of name.
     */
    public function webAuthnData(): WebAuthnData
    {
        $displayName = trim($this->first_name.' '.$this->last_name) ?: ($this->username ?? $this->email ?? 'User');

        return WebAuthnData::make($this->email ?? '', $displayName);
    }

    /**
     * Return Permission models directly assigned to the user.
     * Provides a trait-like method `getDirectPermissions()` for callers.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission>
     */
    public function getDirectPermissions(): \Illuminate\Database\Eloquent\Collection
    {
        $modelTypes = [\App\Models\User::class, self::class];

        $permissionIds = DB::table('model_has_permissions')
            ->whereIn('model_type', $modelTypes)
            ->where('model_id', $this->getKey())
            ->pluck('permission_id')
            ->toArray();

        if (empty($permissionIds)) {
            return Permission::query()->whereRaw('0 = 1')->get();
        }

        return Permission::whereIn('id', $permissionIds)->get();
    }

    /**
     * Return Permission models assigned via the user's roles.
     * Provides a trait-like method `getPermissionsViaRoles()` for callers.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission>
     */
    public function getPermissionsViaRoles(): \Illuminate\Database\Eloquent\Collection
    {
        $roleIds = $this->roles->pluck('id')->toArray();

        if (empty($roleIds)) {
            return Permission::query()->whereRaw('0 = 1')->get();
        }

        $permissionIds = DB::table('role_has_permissions')
            ->whereIn('role_id', $roleIds)
            ->pluck('permission_id')
            ->toArray();

        if (empty($permissionIds)) {
            return Permission::query()->whereRaw('0 = 1')->get();
        }

        return Permission::whereIn('id', $permissionIds)->get();
    }

    /**
     * Return combined Permission models (direct + via roles).
     * Provides a trait-like method `getAllPermissions()` for callers.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission>
     */
    public function getAllPermissions(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->getDirectPermissions()->merge($this->getPermissionsViaRoles())->unique('id')->values();
    }

    /**
     * Defensive static call handler: allow accidental static calls to permission accessors
     * to be handled gracefully. If an instance or id is provided as the first argument
     * we'll forward the call to that instance; otherwise return a sensible default.
     */
    public static function __callStatic($method, $parameters)
    {
        $permissionAccessors = [
            'getDirectPermissionsAttribute',
            'getPermissionsViaRolesAttribute',
            'getAllPermissionsAttribute',
            'getCanAttribute',
            'getPermissionNamesAttribute',
            'getPermissionsViaRoleAttribute',
        ];

        if (! in_array($method, $permissionAccessors, true)) {
            // Let PHP handle other static calls (will trigger usual error)
            return null;
        }

        // If the first argument is an instance of this class, call the instance method
        if (! empty($parameters) && $parameters[0] instanceof self) {
            $instance = $parameters[0];
            // Call the instance method (if exists)
            if (method_exists($instance, $method)) {
                return $instance->{$method}(...array_slice($parameters, 1));
            }
        }

        // If the first argument is a numeric id, try to load the user and forward
        if (! empty($parameters) && is_numeric($parameters[0])) {
            $instance = self::find($parameters[0]);
            if ($instance && method_exists($instance, $method)) {
                return $instance->{$method}(...array_slice($parameters, 1));
            }
        }

        // Fallback sensible defaults
        if ($method === 'getCanAttribute') {
            return [];
        }

        return [];
    }

    /**
     * Handle dynamic instance method calls for permission-related accessors.
     * This forwards calls like getPermissionsViaRolesAttribute() to the
     * appropriate accessor or method so callers (including older code) don't
     * trigger undefined method errors.
     */
    public function __call($method, $parameters)
    {
        $map = [
            'getDirectPermissionsAttribute' => fn () => $this->getDirectPermissionsAttribute(),
            'getPermissionsViaRolesAttribute' => fn () => $this->getPermissionsViaRolesAttribute(),
            'getAllPermissionsAttribute' => fn () => $this->getAllPermissionsAttribute(),
            'getCanAttribute' => fn () => $this->getCanAttribute(),
            'getPermissionNamesAttribute' => fn () => $this->getPermissionNamesAttribute(),
            'getPermissionsViaRoleAttribute' => fn () => $this->getPermissionsViaRoleAttribute(),
        ];

        if (isset($map[$method])) {
            return $map[$method](...$parameters);
        }

        return parent::__call($method, $parameters);
    }
}
