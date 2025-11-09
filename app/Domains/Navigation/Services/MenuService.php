<?php

declare(strict_types=1);

namespace App\Domains\Navigation\Services;

use App\Domains\Navigation\Models\Menu;
use App\Domains\Navigation\Models\MenuItem;
use Illuminate\Support\Str;

class MenuService implements MenuServiceContract
{
    /**
     * Create a new menu
     */
    public function createMenu(array $data): Menu
    {
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        return Menu::create($data);
    }

    /**
     * Update a menu
     */
    public function updateMenu(Menu $menu, array $data): Menu
    {
        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $menu->update($data);
        return $menu->fresh();
    }

    /**
     * Delete a menu and all its items
     */
    public function deleteMenu(Menu $menu): bool
    {
        return $menu->delete();
    }

    /**
     * Duplicate a menu with all its items
     */
    public function duplicateMenu(Menu $menu, string $newName): Menu
    {
        return $menu->duplicate($newName);
    }

    /**
     * Create a menu item
     */
    public function createMenuItem(Menu $menu, array $data): MenuItem
    {
        $data['menu_id'] = $menu->id;

        // Auto-generate order if not provided
        if (!isset($data['order'])) {
            $maxOrder = MenuItem::where('menu_id', $menu->id)
                ->where('parent_id', $data['parent_id'] ?? null)
                ->max('order');
            $data['order'] = ($maxOrder ?? -1) + 1;
        }

        return MenuItem::create($data);
    }

    /**
     * Update a menu item
     */
    public function updateMenuItem(MenuItem $item, array $data): MenuItem
    {
        $item->update($data);
        return $item->fresh();
    }

    /**
     * Delete a menu item
     */
    public function deleteMenuItem(MenuItem $item): bool
    {
        return $item->delete();
    }

    /**
     * Reorder menu items
     */
    public function reorderItems(Menu $menu, array $items): void
    {
        MenuItem::reorder($items);
    }

    /**
     * Export menu to JSON
     */
    public function exportMenu(Menu $menu): array
    {
        return [
            'name' => $menu->name,
            'description' => $menu->description,
            'location' => $menu->location,
            'settings' => $menu->settings,
            'items' => $menu->getStructure(),
        ];
    }

    /**
     * Import menu from JSON
     */
    public function importMenu(array $data): Menu
    {
        $menuData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'location' => $data['location'] ?? null,
            'settings' => $data['settings'] ?? null,
        ];

        $menu = $this->createMenu($menuData);

        if (!empty($data['items'])) {
            $this->importMenuItems($menu, $data['items']);
        }

        return $menu;
    }

    /**
     * Import menu items recursively
     */
    private function importMenuItems(Menu $menu, array $items, ?int $parentId = null, int $order = 0): void
    {
        foreach ($items as $index => $itemData) {
            $children = $itemData['children'] ?? [];
            unset($itemData['children'], $itemData['id']);

            $itemData['menu_id'] = $menu->id;
            $itemData['parent_id'] = $parentId;
            $itemData['order'] = $order + $index;

            $item = MenuItem::create($itemData);

            if (!empty($children)) {
                $this->importMenuItems($menu, $children, $item->id);
            }
        }
    }

    /**
     * Get available menu locations
     */
    public function getAvailableLocations(): array
    {
        return [
            'primary' => 'Primary Navigation',
            'footer' => 'Footer Navigation',
            'sidebar' => 'Sidebar Navigation',
            'mobile' => 'Mobile Navigation',
        ];
    }

    /**
     * Get menu by location
     */
    public function getMenuByLocation(string $location): ?Menu
    {
        return Menu::where('location', $location)
            ->where('is_active', true)
            ->first();
    }
}
