<?php

declare(strict_types=1);

namespace App\Domains\Navigation\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'menu_id',
        'parent_id',
        'type',
        'title',
        'url',
        'object_id',
        'object_type',
        'target',
        'css_classes',
        'icon',
        'order',
        'attributes',
        'metadata',
        'is_visible',
    ];

    protected $casts = [
        'attributes' => 'array',
        'metadata' => 'array',
        'is_visible' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get the menu this item belongs to
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * Get the parent menu item
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * Get all children menu items
     */
    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('order');
    }

    /**
     * Get the URL for this menu item
     */
    public function getResolvedUrl(): ?string
    {
        if ($this->url) {
            return $this->url;
        }

        // Resolve URL based on object type
        if ($this->object_type && $this->object_id) {
            return match ($this->object_type) {
                'page' => '/page/' . $this->object_id,
                'post' => '/post/' . $this->object_id,
                'category' => '/category/' . $this->object_id,
                'tag' => '/tag/' . $this->object_id,
                default => null,
            };
        }

        return null;
    }

    /**
     * Convert this item and its children to a tree structure
     */
    public function toTree(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'url' => $this->getResolvedUrl(),
            'type' => $this->type,
            'target' => $this->target,
            'css_classes' => $this->css_classes,
            'icon' => $this->icon,
            'is_visible' => $this->is_visible,
            'attributes' => $this->attributes,
            'metadata' => $this->metadata,
            'children' => $this->children->map(fn($child) => $child->toTree())->toArray(),
        ];
    }

    /**
     * Reorder items
     */
    public static function reorder(array $items, ?int $parentId = null): void
    {
        foreach ($items as $index => $item) {
            self::where('id', $item['id'])->update([
                'order' => $index,
                'parent_id' => $parentId,
            ]);

            if (!empty($item['children'])) {
                self::reorder($item['children'], $item['id']);
            }
        }
    }
}
