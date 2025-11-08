<?php

declare(strict_types=1);

namespace App\Domains\Navigation\Http\Controllers\Admin;

use App\Domains\Navigation\Models\Menu;
use App\Domains\Navigation\Services\MenuService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class MenuController extends Controller
{
    public function __construct(
        private MenuService $menuService
    ) {}

    /**
     * Display menu management page
     */
    public function index(Request $request): Response
    {
        $menus = Menu::withCount('items')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($menu) {
                return [
                    'id' => $menu->id,
                    'name' => $menu->name,
                    'slug' => $menu->slug,
                    'description' => $menu->description,
                    'location' => $menu->location,
                    'is_active' => $menu->is_active,
                    'items_count' => $menu->items_count,
                    'created_at' => $menu->created_at->toISOString(),
                    'updated_at' => $menu->updated_at->toISOString(),
                ];
            });

        return Inertia::render('Admin/Navigation/Index', [
            'menus' => $menus,
            'locations' => $this->menuService->getAvailableLocations(),
        ]);
    }

    /**
     * Show create menu page
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Navigation/Create', [
            'locations' => $this->menuService->getAvailableLocations(),
        ]);
    }

    /**
     * Show edit menu page
     */
    public function edit(Menu $menu): Response
    {
        return Inertia::render('Admin/Navigation/Edit', [
            'menu' => [
                'id' => $menu->id,
                'name' => $menu->name,
                'slug' => $menu->slug,
                'description' => $menu->description,
                'location' => $menu->location,
                'settings' => $menu->settings,
                'is_active' => $menu->is_active,
            ],
            'items' => $menu->getStructure(),
            'locations' => $this->menuService->getAvailableLocations(),
        ]);
    }

    /**
     * Store a new menu (API)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:menus,name',
            'slug' => 'nullable|string|max:255|unique:menus,slug',
            'description' => 'nullable|string|max:500',
            'location' => 'nullable|string|max:50',
            'settings' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $menu = $this->menuService->createMenu($validated);

        return response()->json($menu->load('items'), 201);
    }

    /**
     * Update a menu (API)
     */
    public function update(Request $request, Menu $menu): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('menus')->ignore($menu->id)],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('menus')->ignore($menu->id)],
            'description' => 'sometimes|nullable|string|max:500',
            'location' => 'sometimes|nullable|string|max:50',
            'settings' => 'sometimes|nullable|array',
            'is_active' => 'sometimes|boolean',
        ]);

        $menu = $this->menuService->updateMenu($menu, $validated);

        return response()->json($menu->load('items'));
    }

    /**
     * Delete a menu (API)
     */
    public function destroy(Menu $menu): JsonResponse
    {
        $this->menuService->deleteMenu($menu);

        return response()->json(['message' => 'Menu deleted successfully']);
    }

    /**
     * Duplicate a menu (API)
     */
    public function duplicate(Request $request, Menu $menu): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:menus,name',
        ]);

        $newMenu = $this->menuService->duplicateMenu($menu, $validated['name']);

        return response()->json($newMenu->load('items'), 201);
    }

    /**
     * Export menu to JSON
     */
    public function export(Menu $menu): JsonResponse
    {
        $data = $this->menuService->exportMenu($menu);

        return response()->json($data);
    }

    /**
     * Import menu from JSON
     */
    public function import(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:menus,name',
            'description' => 'nullable|string|max:500',
            'location' => 'nullable|string|max:50',
            'settings' => 'nullable|array',
            'items' => 'nullable|array',
        ]);

        $menu = $this->menuService->importMenu($validated);

        return response()->json($menu->load('items'), 201);
    }
}
