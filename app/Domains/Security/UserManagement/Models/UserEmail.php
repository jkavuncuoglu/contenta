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
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Boot the model
     */
    protected static function boot()
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
}
