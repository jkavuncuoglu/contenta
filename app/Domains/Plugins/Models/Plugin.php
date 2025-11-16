<?php

declare(strict_types=1);

namespace App\Domains\Plugins\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plugin extends Model
{
    use HasFactory;

    public const TYPE_FRONTEND = 'frontend';

    public const TYPE_ADMIN = 'admin';

    public const TYPE_UNIVERSAL = 'universal';

    protected $fillable = [
        'slug',
        'name',
        'description',
        'version',
        'author',
        'author_url',
        'metadata',
        'entry_point',
        'plugin_type',
        'is_enabled',
        'is_verified',
        'scanned_at',
        'scan_results',
        'installed_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'scan_results' => 'array',
        'is_enabled' => 'boolean',
        'is_verified' => 'boolean',
        'scanned_at' => 'datetime',
        'installed_at' => 'datetime',
    ];

    /**
     * Get the plugin directory path
     */
    public function getDirectoryPath(): string
    {
        return storage_path("app/plugins/{$this->slug}");
    }

    /**
     * Get the full entry point path
     */
    public function getEntryPointPath(): ?string
    {
        if (! $this->entry_point) {
            return null;
        }

        return $this->getDirectoryPath().'/'.$this->entry_point;
    }

    /**
     * Check if plugin has security issues
     */
    public function hasSecurityIssues(): bool
    {
        if (! $this->scan_results) {
            return false;
        }

        return ! empty($this->scan_results['threats']) ||
               ! empty($this->scan_results['warnings']);
    }

    /**
     * Check if plugin should load in frontend context
     */
    public function shouldLoadInFrontend(): bool
    {
        return in_array($this->plugin_type, [self::TYPE_FRONTEND, self::TYPE_UNIVERSAL]);
    }

    /**
     * Check if plugin should load in admin context
     */
    public function shouldLoadInAdmin(): bool
    {
        return in_array($this->plugin_type, [self::TYPE_ADMIN, self::TYPE_UNIVERSAL]);
    }

    /**
     * Check if plugin is frontend only
     */
    public function isFrontendOnly(): bool
    {
        return $this->plugin_type === self::TYPE_FRONTEND;
    }

    /**
     * Check if plugin is admin only
     */
    public function isAdminOnly(): bool
    {
        return $this->plugin_type === self::TYPE_ADMIN;
    }

    /**
     * Check if plugin is universal
     */
    public function isUniversal(): bool
    {
        return $this->plugin_type === self::TYPE_UNIVERSAL;
    }
}
