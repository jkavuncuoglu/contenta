<?php

declare(strict_types=1);

namespace App\Domains\Navigation\Services;

use App\Domains\Navigation\Models\Menu;
use App\Domains\Navigation\Models\MenuItem;

interface MenuServiceContract
{
    /**
     * Create a new menu
     */
    public function createMenu(array $data): Menu;

    /**
     * Update a menu
     */
    public function updateMenu(Menu $menu, array $data): Menu;

    /**
     * Delete a menu and all its items
     */
    public function deleteMenu(Menu $menu): bool;

    /**
     * Duplicate a menu with all its items
     */
    public function duplicateMenu(Menu $menu, string $newName): Menu;

    /**
     * Create a menu item
     */
    public function createMenuItem(Menu $menu, array $data): MenuItem;

    /**
     * Update a menu item
     */
    public function updateMenuItem(MenuItem $item, array $data): MenuItem;

    /**
     * Delete a menu item
     */
    public function deleteMenuItem(MenuItem $item): bool;

    /**
     * Reorder menu items
     */
    public function reorderItems(Menu $menu, array $items): void;

    /**
     * Export menu to JSON
     */
    public function exportMenu(Menu $menu): array;

    /**
     * Import menu from JSON
     */
    public function importMenu(array $data): Menu;

    /**
     * Get available menu locations
     */
    public function getAvailableLocations(): array;

    /**
     * Get menu by location
     */
    public function getMenuByLocation(string $location): ?Menu;
}
