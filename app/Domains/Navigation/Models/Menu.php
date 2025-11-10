<?php

declare(strict_types=1);

namespace App\Domains\Navigation\Models;

use Database\Factories\MenuFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @use HasFactory<MenuFactory>
 */
class Menu extends Model
{
    /** @use HasFactory<MenuFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'location',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get all items for this menu
     *
     * @return HasMany<MenuItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('order');
    }

    /**
     * Get root-level items (no parent)
     *
     * @return HasMany<MenuItem, $this>
     */
    public function rootItems(): HasMany
    {
        return $this->hasMany(MenuItem::class)
            ->whereNull('parent_id')
            ->orderBy('order');
    }

    /**
     * Get the menu structure as a nested array
     *
     * @return array<int, array<string, mixed>>
     */
    public function getStructure(): array
    {
        return $this->rootItems()
            ->with('children')
            ->get()
            ->map(fn(MenuItem $item) => $item->toTree())
            ->toArray();
    }

    /**
     * Duplicate this menu
     */
    public function duplicate(string $newName): self
    {
        $newMenu = $this->replicate();
        $newMenu->name = $newName;
        $newMenu->slug = \Str::slug($newName);
        $newMenu->save();

        // Duplicate all items
        /** @var array<int, int> $itemMap */
        $itemMap = [];
        foreach ($this->items as $item) {
            $newItem = $item->replicate();
            $newItem->setAttribute('menu_id', $newMenu->id);
            $newItem->setAttribute('parent_id', null); // Will be set later
            $newItem->save();
            $itemMap[(int) $item->id] = (int) $newItem->id;
        }

        // Set parent relationships
        foreach ($this->items as $item) {
            if ($item->parent_id && isset($itemMap[(int) $item->parent_id])) {
                $newItem = MenuItem::find($itemMap[(int) $item->id]);
                if ($newItem instanceof MenuItem) {
                    $newItem->setAttribute('parent_id', $itemMap[(int) $item->parent_id]);
                    $newItem->save();
                }
            }
        }

        return $newMenu;
    }
}
