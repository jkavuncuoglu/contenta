<?php

declare(strict_types=1);

namespace App\Domains\PageBuilder\Http\Controllers\Admin;

use App\Domains\PageBuilder\Actions\PublishPageAction;
use App\Domains\PageBuilder\Models\Layout;
use App\Domains\PageBuilder\Models\Page;
use App\Domains\PageBuilder\Models\PageRevision;
use App\Domains\PageBuilder\Services\PageRenderService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    public function __construct(
        private PublishPageAction $publishPageAction
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Page::with(['layout', 'author'])
            ->orderBy('updated_at', 'desc');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search by title
        if ($request->has('search')) {
            $search = $request->input('search');
            if (is_string($search)) {
                $query->where('title', 'like', '%' . $search . '%');
            }
        }

        $perPage = $request->input('per_page', 15);
        $pages = $query->paginate(is_numeric($perPage) ? (int) $perPage : 15);

        return response()->json($pages);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pagebuilder_pages,slug',
            'layout_id' => 'nullable|exists:pagebuilder_layouts,id',
            'data' => 'nullable|array',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'schema_data' => 'nullable|array',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);

            // Ensure uniqueness
            $baseSlug = $validated['slug'];
            $counter = 1;
            while (Page::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $baseSlug . '-' . $counter;
                $counter++;
            }
        }

        $userId = auth()->id();
        $validated['author_id'] = $userId;
        $validated['status'] = Page::STATUS_DRAFT;

        $page = Page::create($validated);

        // Create initial revision
        $this->createRevision($page, is_int($userId) ? $userId : null, 'Initial version');

        return response()->json($page->load(['layout', 'author']), 201);
    }

    public function show(Page $page): JsonResponse
    {
        return response()->json($page->load(['layout', 'author']));
    }

    public function update(Request $request, Page $page): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('pagebuilder_pages', 'slug')->ignore($page->id)
            ],
            'layout_id' => 'sometimes|nullable|exists:pagebuilder_layouts,id',
            'data' => 'sometimes|nullable|array',
            'meta_title' => 'sometimes|nullable|string|max:255',
            'meta_description' => 'sometimes|nullable|string|max:500',
            'meta_keywords' => 'sometimes|nullable|string|max:255',
            'schema_data' => 'sometimes|nullable|array',
        ]);

        // Generate slug if title changed but slug not provided
        if (isset($validated['title']) && !isset($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);

            // Ensure uniqueness
            $baseSlug = $validated['slug'];
            $counter = 1;
            while (Page::where('slug', $validated['slug'])->where('id', '!=', $page->id)->exists()) {
                $validated['slug'] = $baseSlug . '-' . $counter;
                $counter++;
            }
        }

        $page->update($validated);

        // Create revision for this update
        $userId = auth()->id();
        $this->createRevision($page, is_int($userId) ? $userId : null, 'Updated page');

        return response()->json($page->fresh(['layout', 'author']));
    }

    public function destroy(Page $page): JsonResponse
    {
        $page->delete();

        return response()->json(['message' => 'Page deleted successfully']);
    }

    public function publish(Page $page): JsonResponse
    {
        try {
            $publishedPage = $this->publishPageAction->execute($page);

            return response()->json([
                'message' => 'Page published successfully',
                'page' => $publishedPage->load(['layout', 'author'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to publish page',
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function unpublish(Page $page): JsonResponse
    {
        $unpublishedPage = $this->publishPageAction->unpublish($page);

        return response()->json([
            'message' => 'Page unpublished successfully',
            'page' => $unpublishedPage->load(['layout', 'author'])
        ]);
    }

    public function duplicate(Page $page): JsonResponse
    {
        $userId = auth()->id();
        $duplicatedPage = $page->replicate();
        $duplicatedPage->title = $page->title . ' (Copy)';
        $duplicatedPage->slug = $page->slug . '-copy-' . time();
        $duplicatedPage->status = Page::STATUS_DRAFT;
        $duplicatedPage->published_html = null;
        $duplicatedPage->published_at = null;
        // Cast auth()->id() to int|null for type safety
        $duplicatedPage->author_id = $userId !== null ? (int) $userId : null;
        $duplicatedPage->save();

        return response()->json($duplicatedPage->load(['layout', 'author']), 201);
    }

    public function preview(Request $request, Page $page): JsonResponse
    {
        try {
            // If POST request with data, use that for preview (unsaved changes)
            if ($request->isMethod('post') && $request->has('data')) {
                // Create a temporary page instance with the new data
                $tempPage = $page->replicate();
                $tempPage->data = $request->input('data');
                $tempPage->title = $request->input('title', $page->title);
                $tempPage->layout_id = $request->input('layout_id', $page->layout_id);

                $previewHtml = app(PageRenderService::class)->renderPage($tempPage);
            } else {
                // Use existing saved page data
                $previewHtml = app(PageRenderService::class)->renderPage($page);
            }

            return response()->json([
                'html' => $previewHtml
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate preview',
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Create a revision for a page
     */
    private function createRevision(Page $page, ?int $userId, ?string $reason = null): PageRevision
    {
        /** @var PageRevision|null $latestRevision */
        $latestRevision = $page->revisions()->orderBy('revision_number', 'desc')->first();
        $revisionNumber = $latestRevision ? $latestRevision->revision_number + 1 : 1;

        return PageRevision::create([
            'page_id' => $page->id,
            'user_id' => $userId,
            'title' => $page->title,
            'slug' => $page->slug,
            'layout_id' => $page->layout_id,
            'data' => $page->data,
            'status' => $page->status,
            'meta_title' => $page->meta_title,
            'meta_description' => $page->meta_description,
            'meta_keywords' => $page->meta_keywords,
            'schema_data' => $page->schema_data,
            'reason' => $reason,
            'revision_number' => $revisionNumber,
        ]);
    }
}