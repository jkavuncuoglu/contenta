<?php

declare(strict_types=1);

namespace App\Domains\PageBuilder\Http\Controllers\Admin;

use App\Domains\PageBuilder\Models\Layout;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LayoutController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Layout::orderBy('name');

        if ($request->has('active_only') && $request->active_only) {
            $query->where('is_active', true);
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            if (is_string($search)) {
                $query->where('name', 'like', '%' . $search . '%');
            }
        }

        $layouts = $query->get();

        return response()->json($layouts);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pagebuilder_layouts,slug',
            'structure' => 'required|array',
            'structure.areas' => 'required|array|min:1',
            'structure.areas.*' => 'required|string',
            'structure.settings' => 'sometimes|array',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'sometimes|boolean',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);

            // Ensure uniqueness
            $baseSlug = $validated['slug'];
            $counter = 1;
            while (Layout::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $baseSlug . '-' . $counter;
                $counter++;
            }
        }

        $layout = Layout::create($validated);

        return response()->json($layout, 201);
    }

    public function show(Layout $layout): JsonResponse
    {
        return response()->json($layout);
    }

    public function update(Request $request, Layout $layout): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('pagebuilder_layouts', 'slug')->ignore($layout->id)
            ],
            'structure' => 'sometimes|array',
            'structure.areas' => 'sometimes|array|min:1',
            'structure.areas.*' => 'sometimes|string',
            'structure.settings' => 'sometimes|array',
            'description' => 'sometimes|nullable|string|max:1000',
            'is_active' => 'sometimes|boolean',
        ]);

        $layout->update($validated);

        return response()->json($layout->fresh());
    }

    public function destroy(Layout $layout): JsonResponse
    {
        // Check if layout is being used by any pages
        if ($layout->pages()->exists()) {
            return response()->json([
                'error' => 'Cannot delete layout',
                'message' => 'This layout is currently being used by one or more pages'
            ], 422);
        }

        $layout->delete();

        return response()->json(['message' => 'Layout deleted successfully']);
    }
}