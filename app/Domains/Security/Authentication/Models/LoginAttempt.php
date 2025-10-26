<?php


namespace App\Domains\Security\Authentication\Models;

use App\Domains\Security\UserManagement\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginAttempt extends Model
{
    protected $fillable = [
        'user_id',
        'username_attempted',
        'ip_address',
        'device_fingerprint',
        'user_agent',
        'success',
        'failed_attempts',
        'escalation_level',
        'blocked_until',
        'permanent_block',
        'metadata',
    ];

    protected $casts = [
        'success' => 'boolean',
        'permanent_block' => 'boolean',
        'blocked_until' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Relationship with User model
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the IP/device is currently blocked
     */
    public function isBlocked(): bool
    {
        if ($this->permanent_block) {
            return true;
        }

        if ($this->blocked_until && $this->blocked_until->isFuture()) {
            return true;
        }

        return false;
    }

    /**
     * Get escalation duration in minutes based on escalation level
     */
    public static function getEscalationDuration(int $level): int
    {
        return match ($level) {
            1 => 60,     // 1 hour
            2 => 120,    // 2 hours
            3 => 240,    // 4 hours
            4 => 480,    // 8 hours
            5 => 0,      // Permanent (handled separately)
            default => 0,
        };
    }

    /**
     * Calculate next escalation level
     */
    public function getNextEscalationLevel(): int
    {
        return min($this->escalation_level + 1, 5);
    }

    /**
     * Apply escalation and set block duration
     */
    public function escalate(): void
    {
        $this->escalation_level = $this->getNextEscalationLevel();

        if ($this->escalation_level >= 5) {
            $this->permanent_block = true;
            $this->blocked_until = null;
        } else {
            $duration = self::getEscalationDuration($this->escalation_level);
            $this->blocked_until = Carbon::now()->addMinutes($duration);
        }

        $this->save();
    }

    /**
     * Reset escalation (e.g., after successful login)
     */
    public function resetEscalation(): void
    {
        $this->failed_attempts = 0;
        $this->escalation_level = 0;
        $this->blocked_until = null;
        $this->permanent_block = false;
        $this->save();
    }

    /**
     * Scope for active blocks
     */
    public function scopeBlocked($query)
    {
        return $query->where(function ($q) {
            $q->where('permanent_block', true)
                ->orWhere('blocked_until', '>', Carbon::now());
        });
    }

    /**
     * Scope for specific IP address
     */
    public function scopeByIp($query, string $ip)
    {
        return $query->where('ip_address', $ip);
    }

    /**
     * Scope for specific device fingerprint
     */
    public function scopeByDevice($query, string $fingerprint)
    {
        return $query->where('device_fingerprint', $fingerprint);
    }

    /**
     * Scope for specific username
     */
    public function scopeByUsername($query, string $username)
    {
        return $query->where('username_attempted', $username);
    }
}
