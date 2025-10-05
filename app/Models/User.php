<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'pending_recovery_codes_regeneration',
        'two_factor_used_recovery_codes',
        'two_factor_recovery_codes_viewed_at',
        'recovery_codes_regeneration_token',
        'recovery_codes_regeneration_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_used_recovery_codes',
        'recovery_codes_regeneration_token',
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
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_used_recovery_codes' => 'array',
            'two_factor_recovery_codes_viewed_at' => 'datetime',
            'recovery_codes_regeneration_expires_at' => 'datetime',
        ];
    }

    /**
     * Check if the user has Two-Factor Authentication enabled.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return !is_null($this->two_factor_secret) && !is_null($this->two_factor_confirmed_at);
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
            'two_factor_used_recovery_codes' => array_unique($usedCodes)
        ]);
    }

    /**
     * Check if recovery codes have been viewed.
     */
    public function hasViewedRecoveryCodes(): bool
    {
        return !is_null($this->two_factor_recovery_codes_viewed_at);
    }

    /**
     * Mark recovery codes as viewed.
     */
    public function markRecoveryCodesAsViewed(): void
    {
        $this->update([
            'two_factor_recovery_codes_viewed_at' => now()
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
            'recovery_codes_regeneration_expires_at' => now()->addHour()
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
            'recovery_codes_regeneration_expires_at' => null
        ]);
    }
}
