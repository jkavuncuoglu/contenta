<?php

declare(strict_types=1);

namespace App\Domains\PageBuilder\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class Layout
 *
 * Represents a layout definition used by the legacy Page Builder.
 */
class Layout extends Model
{
    use HasFactory;

    protected $table = 'pagebuilder_layouts';

    protected $fillable = [
        'name',
        'slug',
        'config',
        'is_active',
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Pages using this layout
     *
     * @return HasMany<Page>
     */
    public function pages(): HasMany
    {
        return $this->hasMany(Page::class, 'layout_id');
    }

    /**
     * Scope active layouts
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Return configured areas for this layout
     *
     * @return array<int, string>
     */
    public function getAreasAttribute(): array
    {
        return $this->config['areas'] ?? [];
    }

    /**
     * Check whether layout has a specific area
     */
    public function hasArea(string $area): bool
    {
        return in_array($area, $this->getAreasAttribute(), true);
    }

    /**
     * Ensure config attribute is always returned as array
     */
    public function getConfigAttribute($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }
}
