<?php

declare(strict_types=1);

namespace App\Domains\Plugins\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Plugin extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'version',
        'author',
        'author_url',
        'metadata',
        'entry_point',
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
        if (!$this->entry_point) {
            return null;
        }

        return $this->getDirectoryPath() . '/' . $this->entry_point;
    }

    /**
     * Check if plugin has security issues
     */
    public function hasSecurityIssues(): bool
    {
        if (!$this->scan_results) {
            return false;
        }

        return !empty($this->scan_results['threats']) ||
               !empty($this->scan_results['warnings']);
    }
}
