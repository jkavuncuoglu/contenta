<?php

declare(strict_types=1);

namespace App\Domains\Themes\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Theme extends Model
{
    use LogsActivity;

    protected $table = 'themes';

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'version',
        'author',
        'screenshot',
        'is_active',
        'path',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'display_name', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the theme's full path
     */
    public function getFullPath(): string
    {
        return storage_path('app/themes/'.$this->path);
    }

    /**
     * Check if theme has a specific view
     */
    public function hasView(string $view): bool
    {
        $viewPath = $this->getFullPath().'/views/'.$view.'.blade.php';

        return file_exists($viewPath);
    }

    /**
     * Get theme screenshot URL
     */
    public function getScreenshotUrl(): ?string
    {
        if (! $this->screenshot) {
            return null;
        }

        if (file_exists($this->getFullPath().'/'.$this->screenshot)) {
            return asset('storage/themes/'.$this->path.'/'.$this->screenshot);
        }

        return null;
    }

    /**
     * Activate this theme (deactivate all others)
     */
    public function activate(): void
    {
        self::query()->update(['is_active' => false]);
        $this->update(['is_active' => true]);
    }

    /**
     * Get the currently active theme
     */
    public static function getActive(): ?self
    {
        return self::where('is_active', true)->first();
    }

    /**
     * Check if this is the active theme
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }
}
