<?php

declare(strict_types=1);

namespace App\Domains\Settings\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @property bool $is_active
 * @property string|null $light_primary
 * @property string|null $light_secondary
 * @property string|null $light_accent
 * @property string|null $light_background
 * @property string|null $light_surface
 * @property string|null $light_text
 * @property string|null $light_text_secondary
 * @property string|null $dark_primary
 * @property string|null $dark_secondary
 * @property string|null $dark_accent
 * @property string|null $dark_background
 * @property string|null $dark_surface
 * @property string|null $dark_text
 * @property string|null $dark_text_secondary
 */
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
     *
     * @return array<string, string|null>
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
     *
     * @return array<string, string|null>
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
     *
     * @return array<string, array<string, string|null>>
     */
    public function getAllColors(): array
    {
        return [
            'light' => $this->getLightColors(),
            'dark' => $this->getDarkColors(),
        ];
    }
}
