<?php

declare(strict_types=1);

namespace App\Domains\Settings\Models;

use Illuminate\Database\Eloquent\Model;

class ThemeSettings extends Model
{
    protected $fillable = [
        'name',
        'is_active',
        'light_primary',
        'light_secondary',
        'light_accent',
        'light_background',
        'light_surface',
        'light_text',
        'light_text_secondary',
        'dark_primary',
        'dark_secondary',
        'dark_accent',
        'dark_background',
        'dark_surface',
        'dark_text',
        'dark_text_secondary',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the active theme
     */
    public static function active(): ?self
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Get light theme colors as array
     */
    public function getLightColors(): array
    {
        return [
            'primary' => $this->light_primary,
            'secondary' => $this->light_secondary,
            'accent' => $this->light_accent,
            'background' => $this->light_background,
            'surface' => $this->light_surface,
            'text' => $this->light_text,
            'textSecondary' => $this->light_text_secondary,
        ];
    }

    /**
     * Get dark theme colors as array
     */
    public function getDarkColors(): array
    {
        return [
            'primary' => $this->dark_primary,
            'secondary' => $this->dark_secondary,
            'accent' => $this->dark_accent,
            'background' => $this->dark_background,
            'surface' => $this->dark_surface,
            'text' => $this->dark_text,
            'textSecondary' => $this->dark_text_secondary,
        ];
    }

    /**
     * Get all theme colors
     */
    public function getAllColors(): array
    {
        return [
            'light' => $this->getLightColors(),
            'dark' => $this->getDarkColors(),
        ];
    }
}
