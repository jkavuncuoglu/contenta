<?php

declare(strict_types=1);

namespace App\Domains\Navigation\Http\Controllers\Admin;

use App\Domains\Navigation\Models\Menu;
use App\Domains\Navigation\Models\MenuItem;
use App\Domains\Navigation\Services\MenuService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    public function __construct(
        private MenuService $menuService
    ) {}

    /**
     * Get all items for a menu
     */
    public function index(Menu $menu): JsonResponse
    {
        $items = $menu->getStructure();

        return response()->json($items);
    }

    /**
     * Store a new menu item
     */
    public function store(Request $request, Menu $menu): JsonResponse
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:menu_items,id',
            'type' => 'required|string|in:custom,page,post,category,tag',
            'title' => 'required|string|max:255',
            'url' => 'nullable|string|max:500',
            'object_id' => 'nullable|integer',
            'object_type' => 'nullable|string|max:50',
            'target' => 'nullable|string|in:_self,_blank,_parent,_top',
            'css_classes' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'order' => 'nullable|integer',
            'attributes' => 'nullable|array',
            'metadata' => 'nullable|array',
            'is_visible' => 'boolean',
        ]);

        $item = $this->menuService->createMenuItem($menu, $validated);

        return response()->json($item, 201);
    }

    /**
     * Update a menu item
     */
    public function update(Request $request, Menu $menu, MenuItem $item): JsonResponse
    {
        if ($item->menu_id !== $menu->id) {
            return response()->json(['error' => 'Menu item does not belong to this menu'], 404);
        }

        $validated = $request->validate([
            'parent_id' => 'sometimes|nullable|exists:menu_items,id',
            'type' => 'sometimes|string|in:custom,page,post,category,tag',
            'title' => 'sometimes|string|max:255',
            'url' => 'sometimes|nullable|string|max:500',
            'object_id' => 'sometimes|nullable|integer',
            'object_type' => 'sometimes|nullable|string|max:50',
            'target' => 'sometimes|nullable|string|in:_self,_blank,_parent,_top',
            'css_classes' => 'sometimes|nullable|string|max:255',
            'icon' => 'sometimes|nullable|string|max:255',
            'order' => 'sometimes|integer',
            'attributes' => 'sometimes|nullable|array',
            'metadata' => 'sometimes|nullable|array',
            'is_visible' => 'sometimes|boolean',
        ]);

        $item = $this->menuService->updateMenuItem($item, $validated);

        return response()->json($item);
    }

    /**
     * Delete a menu item
     */
    public function destroy(Menu $menu, MenuItem $item): JsonResponse
    {
        if ($item->menu_id !== $menu->id) {
            return response()->json(['error' => 'Menu item does not belong to this menu'], 404);
        }

        $this->menuService->deleteMenuItem($item);

        return response()->json(['message' => 'Menu item deleted successfully']);
    }

    /**
     * Reorder menu items (bulk update)
     */
    public function reorder(Request $request, Menu $menu): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:menu_items,id',
            'items.*.children' => 'sometimes|array',
        ]);

        $this->menuService->reorderItems($menu, $validated['items']);

        return response()->json([
            'message' => 'Menu items reordered successfully',
            'items' => $menu->fresh()->getStructure(),
        ]);
    }

    /**
     * Bulk create menu items from pages/posts/etc
     */
    public function bulkCreate(Request $request, Menu $menu): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string|in:page,post,category,tag',
            'items' => 'required|array',
            'items.*.id' => 'required|integer',
            'items.*.title' => 'required|string',
            'parent_id' => 'nullable|exists:menu_items,id',
        ]);

        $createdItems = [];

        foreach ($validated['items'] as $index => $itemData) {
            $data = [
                'type' => $validated['type'],
                'title' => $itemData['title'],
                'object_id' => $itemData['id'],
                'object_type' => $validated['type'],
                'parent_id' => $validated['parent_id'] ?? null,
                'order' => $index,
            ];

            $createdItems[] = $this->menuService->createMenuItem($menu, $data);
        }

        return response()->json([
            'message' => count($createdItems).' items added successfully',
            'items' => $createdItems,
        ], 201);
    }
}
