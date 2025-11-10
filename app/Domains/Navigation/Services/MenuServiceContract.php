<?php

declare(strict_types=1);

namespace App\Domains\Navigation\Services;

use App\Domains\Navigation\Models\Menu;
use App\Domains\Navigation\Models\MenuItem;

interface MenuServiceContract
{
    /**
     * Create a new menu
     *
     * @param array<string, mixed> $data
     */
    public function createMenu(array $data): Menu;

    /**
     * Update a menu
     *
     * @param array<string, mixed> $data
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
     *
     * @param array<string, mixed> $data
     */
    public function createMenuItem(Menu $menu, array $data): MenuItem;

    /**
     * Update a menu item
     *
     * @param array<string, mixed> $data
     */
    public function updateMenuItem(MenuItem $item, array $data): MenuItem;

    /**
     * Delete a menu item
     */
    public function deleteMenuItem(MenuItem $item): bool;

    /**
     * Reorder menu items
     *
     * @param array<int, array<string, mixed>> $items
     */
    public function reorderItems(Menu $menu, array $items): void;

    /**
     * Export menu to JSON
     *
     * @return array<string, mixed>
     */
    public function exportMenu(Menu $menu): array;

    /**
     * Import menu from JSON
     *
     * @param array<string, mixed> $data
     */
    public function importMenu(array $data): Menu;

    /**
     * Get available menu locations
     *
     * @return array<string, string>
     */
    public function getAvailableLocations(): array;

    /**
     * Get menu by location
     */
    public function getMenuByLocation(string $location): ?Menu;
}
