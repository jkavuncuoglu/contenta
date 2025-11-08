<?php

declare(strict_types=1);

namespace App\Domains\Navigation\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use HasFactory, SoftDeletes;

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
     */
    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('order');
    }

    /**
     * Get root-level items (no parent)
     */
    public function rootItems(): HasMany
    {
        return $this->hasMany(MenuItem::class)
            ->whereNull('parent_id')
            ->orderBy('order');
    }

    /**
     * Get the menu structure as a nested array
     */
    public function getStructure(): array
    {
        return $this->rootItems()
            ->with('children')
            ->get()
            ->map(fn($item) => $item->toTree())
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
        $itemMap = [];
        foreach ($this->items as $item) {
            $newItem = $item->replicate();
            $newItem->menu_id = $newMenu->id;
            $newItem->parent_id = null; // Will be set later
            $newItem->save();
            $itemMap[$item->id] = $newItem->id;
        }

        // Set parent relationships
        foreach ($this->items as $item) {
            if ($item->parent_id && isset($itemMap[$item->parent_id])) {
                $newItem = MenuItem::find($itemMap[$item->id]);
                $newItem->parent_id = $itemMap[$item->parent_id];
                $newItem->save();
            }
        }

        return $newMenu;
    }
}
