<?php

declare(strict_types=1);

namespace App\Domains\PageBuilder\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Layout extends Model
{
    use HasFactory;

    protected $table = 'pagebuilder_layouts';

    protected $fillable = [
        'name',
        'slug',
        'structure',
        'description',
        'is_active',
    ];

    protected $casts = [
        'structure' => 'array',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'is_active' => true,
        'structure' => '{"areas": ["header", "main", "footer"]}',
    ];

    /**
     * Get the pages using this layout
     */
    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }

    /**
     * Scope for active layouts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get default layout areas
     */
    public function getAreasAttribute(): array
    {
        return $this->structure['areas'] ?? ['main'];
    }

    /**
     * Check if layout has specific area
     */
    public function hasArea(string $area): bool
    {
        return in_array($area, $this->areas);
    }

    /**
     * Get layout configuration
     */
    public function getConfigAttribute(): array
    {
        return [
            'areas' => $this->areas,
            'settings' => $this->structure['settings'] ?? [],
            'css_classes' => $this->structure['css_classes'] ?? [],
        ];
    }
}