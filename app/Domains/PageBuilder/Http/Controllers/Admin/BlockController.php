<?php

declare(strict_types=1);

namespace App\Domains\PageBuilder\Http\Controllers\Admin;

use App\Domains\PageBuilder\Models\Block;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BlockController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Block::orderBy('category')->orderBy('name');

        if ($request->has('active_only') && $request->active_only) {
            $query->where('is_active', true);
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            if (is_string($search)) {
                $query->where('name', 'like', '%'.$search.'%');
            }
        }

        $blocks = $query->get();

        return response()->json($blocks);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255|unique:pagebuilder_blocks,type',
            'category' => ['required', 'string', Rule::in(array_keys(Block::getCategories()))],
            'config_schema' => 'required|array',
            'component_path' => 'required|string|max:255',
            'preview_image' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'sometimes|boolean',
        ]);

        $block = Block::create($validated);

        return response()->json($block, 201);
    }

    public function show(Block $block): JsonResponse
    {
        return response()->json($block);
    }

    public function update(Request $request, Block $block): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('pagebuilder_blocks', 'type')->ignore($block->id),
            ],
            'category' => ['sometimes', 'string', Rule::in(array_keys(Block::getCategories()))],
            'config_schema' => 'sometimes|array',
            'component_path' => 'sometimes|string|max:255',
            'preview_image' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|nullable|string|max:1000',
            'is_active' => 'sometimes|boolean',
        ]);

        $block->update($validated);

        return response()->json($block->fresh());
    }

    public function destroy(Block $block): JsonResponse
    {
        $block->delete();

        return response()->json(['message' => 'Block deleted successfully']);
    }

    public function categories(): JsonResponse
    {
        return response()->json(Block::getCategories());
    }

    public function validateConfig(Request $request, Block $block): JsonResponse
    {
        $config = $request->validate([
            'config' => 'required|array',
        ])['config'];

        $errors = $block->validateConfig($config);

        return response()->json([
            'valid' => empty($errors),
            'errors' => $errors,
        ]);
    }
}
