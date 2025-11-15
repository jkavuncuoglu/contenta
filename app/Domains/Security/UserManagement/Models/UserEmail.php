<?php

namespace App\Domains\Security\UserManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class UserEmail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email',
        'verified_at',
        'is_primary',
        'verification_token',
        'email_verification_code',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'is_primary' => 'boolean',
    ];

    /**
     * Get the user that owns the email address
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Boot the model
     */
    protected static function boot(): void
    {
        parent::boot();

        // Generate verification token when creating
        static::creating(function ($userEmail) {
            if (empty($userEmail->verification_token)) {
                $userEmail->verification_token = Str::random(60);
            }
        });

        // Ensure only one primary email per user
        static::saving(function ($userEmail) {
            if ($userEmail->is_primary) {
                static::where('user_id', $userEmail->user_id)
                    ->where('id', '!=', $userEmail->id)
                    ->update(['is_primary' => false]);
            }
        });
    }

    /**
     * Send email verification notification
     *
     * @return array<string, mixed>
     */
    public function sendEmailVerificationNotification(): array
    {
        // Implementation would send verification email
        return [
            'status' => 'success',
            'message' => 'Verification email sent',
        ];
    }

    /**
     * Verify email with hash
     *
     * @return array<string, mixed>
     */
    public function verifyEmail(string $hash): array
    {
        if ($this->verification_token === $hash) {
            $this->verified_at = now();
            $this->save();

            return [
                'status' => 'success',
                'message' => 'Email verified successfully',
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Invalid verification hash',
        ];
    }

    /**
     * Check if email is verified
     */
    public function hasVerifiedEmail(): bool
    {
        return $this->verified_at !== null;
    }
}
