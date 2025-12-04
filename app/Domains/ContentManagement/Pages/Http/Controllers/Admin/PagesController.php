<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Pages\Http\Controllers\Admin;

use App\Domains\ContentManagement\Pages\Models\Page;
use App\Domains\ContentManagement\Pages\Services\PageServiceContract;
use App\Domains\ContentManagement\Services\MarkdownRenderServiceContract;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PagesController extends Controller
{
    public function __construct(
        private readonly PageServiceContract $pageService,
        private readonly MarkdownRenderServiceContract $markdownRenderer
    ) {}

    /**
     * Display listing of pages
     */
    public function index(Request $request): InertiaResponse
    {
        $query = Page::with('author')
            ->orderBy('updated_at', 'desc');

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search by title
        if ($request->has('search')) {
            $search = $request->input('search');
            if (is_string($search) && ! empty($search)) {
                $query->where('title', 'like', '%'.$search.'%');
            }
        }

        $perPage = $request->input('per_page', 15);
        $pages = $query->paginate(is_numeric($perPage) ? (int) $perPage : 15);

        return Inertia::render('admin/content/pages/Index', [
            'pages' => $pages,
        ]);
    }

    /**
     * Show create page form
     */
    public function create(Request $request): InertiaResponse
    {
        // Get available storage drivers from settings
        $availableDrivers = $this->getAvailableStorageDrivers();

        return Inertia::render('admin/content/pages/Create', [
            'availableDrivers' => $availableDrivers,
        ]);
    }

    /**
     * Show edit page form
     */
    public function edit(Request $request, Page $page): InertiaResponse
    {
        // Load page with content from storage
        $pageData = $page->load('author')->toArray();

        // Get content from storage
        $content = $page->content;
        if ($content) {
            $pageData['content'] = $content->getContent();
            $pageData['frontmatter'] = $content->getFrontmatter();
        }

        // Get available storage drivers
        $availableDrivers = $this->getAvailableStorageDrivers();

        return Inertia::render('admin/content/pages/Edit', [
            'page' => $pageData,
            'availableDrivers' => $availableDrivers,
        ]);
    }

    /**
     * Store a new page
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug',
            'content' => 'nullable|string',
            'storage_driver' => 'required|string|in:database,local,s3,github,azure,gcs',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'schema_data' => 'nullable|array',
            'parent_id' => 'nullable|exists:pages,id',
            'template' => 'nullable|string|max:100',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);

            // Ensure uniqueness
            $baseSlug = $validated['slug'];
            $counter = 1;
            while (Page::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $baseSlug.'-'.$counter;
                $counter++;
            }
        }

        $validated['author_id'] = auth()->id();
        $validated['status'] = Page::STATUS_DRAFT;
        $validated['schema_data'] = $validated['schema_data'] ?? [];

        $page = $this->pageService->createPage($validated);

        return redirect()->route('admin.pages.edit', $page->id)
            ->with('success', 'Page created successfully.');
    }

    /**
     * Show page details (API)
     */
    public function show(Page $page): JsonResponse
    {
        $pageData = $page->load('author')->toArray();

        // Get content from storage
        $content = $page->content;
        if ($content) {
            $pageData['content'] = $content->getContent();
            $pageData['frontmatter'] = $content->getFrontmatter();
        }

        return response()->json($pageData);
    }

    /**
     * Get pages list for API (used by menu selector, etc.)
     */
    public function api(Request $request): JsonResponse
    {
        $query = Page::query()
            ->select(['id', 'title', 'slug', 'status', 'storage_driver'])
            ->orderBy('title', 'asc');

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search by title
        if ($request->has('search')) {
            $search = $request->input('search');
            if (is_string($search) && ! empty($search)) {
                $query->where('title', 'like', '%'.$search.'%');
            }
        }

        $perPage = $request->input('per_page', 50);
        $pages = $query->paginate(is_numeric($perPage) ? (int) $perPage : 50);

        return response()->json($pages);
    }

    /**
     * Update a page
     */
    public function update(Request $request, Page $page)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('pages', 'slug')->ignore($page->id),
            ],
            'content' => 'sometimes|nullable|string',
            'storage_driver' => 'sometimes|string|in:database,local,s3,github,azure,gcs',
            'meta_title' => 'sometimes|nullable|string|max:255',
            'meta_description' => 'sometimes|nullable|string|max:500',
            'meta_keywords' => 'sometimes|nullable|string|max:255',
            'schema_data' => 'sometimes|nullable|array',
            'parent_id' => 'sometimes|nullable|exists:pages,id',
            'template' => 'sometimes|nullable|string|max:100',
        ]);

        // Generate slug if title changed but slug not provided
        if (isset($validated['title']) && ! isset($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);

            // Ensure uniqueness
            $baseSlug = $validated['slug'];
            $counter = 1;
            while (Page::where('slug', $validated['slug'])->where('id', '!=', $page->id)->exists()) {
                $validated['slug'] = $baseSlug.'-'.$counter;
                $counter++;
            }
        }

        $this->pageService->updatePage($page, $validated);

        return redirect()->back()->with('success', 'Page updated successfully.');
    }

    /**
     * Delete a page
     */
    public function destroy(Page $page)
    {
        $this->pageService->deletePage($page);

        return redirect()->route('admin.pages.index')
            ->with('success', 'Page deleted successfully.');
    }

    /**
     * Publish a page
     */
    public function publish(Page $page)
    {
        try {
            $this->pageService->publishPage($page);

            return redirect()->back()->with('success', 'Page published successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to publish page: '.$e->getMessage());
        }
    }

    /**
     * Unpublish a page
     */
    public function unpublish(Page $page)
    {
        $this->pageService->unpublishPage($page);

        return redirect()->back()->with('success', 'Page unpublished successfully.');
    }

    /**
     * Duplicate a page
     */
    public function duplicate(Page $page)
    {
        $duplicatedPage = $this->pageService->duplicatePage($page);

        return redirect()->route('admin.pages.edit', $duplicatedPage->id)
            ->with('success', 'Page duplicated successfully.');
    }

    /**
     * Preview a page
     */
    public function preview(Request $request, Page $page): JsonResponse
    {
        try {
            // If POST request with data, use that for preview (unsaved changes)
            if ($request->isMethod('post')) {
                $markdown = $request->input('content', '');
            } else {
                // Use existing saved page content
                $content = $page->content;
                $markdown = $content ? $content->getContent() : '';
            }

            $previewHtml = $this->markdownRenderer->render($markdown);

            return response()->json([
                'html' => $previewHtml,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate preview',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Validate markdown content
     */
    public function validate(Request $request): JsonResponse
    {
        $content = $request->input('content', '');

        try {
            $errors = [];

            try {
                // Attempt to render - this will trigger validation
                $this->markdownRenderer->render($content);
            } catch (\App\Domains\ContentManagement\Services\ShortcodeParser\Exceptions\RenderException $e) {
                $errors[] = [
                    'type' => 'render',
                    'message' => $e->getMessage(),
                    'line' => null,
                    'column' => null,
                ];
            } catch (\App\Domains\ContentManagement\Services\ShortcodeParser\Exceptions\ParseException $e) {
                // Get context around the error line
                $lines = explode("\n", $content);
                $errorLine = $e->sourceLine ?? 0;
                $contextStart = max(0, $errorLine - 3);
                $contextEnd = min(count($lines) - 1, $errorLine + 2);
                $context = [];

                for ($i = $contextStart; $i <= $contextEnd; $i++) {
                    $lineNum = $i + 1;
                    $prefix = ($lineNum === $errorLine) ? '>>> ' : '    ';
                    $context[] = $prefix."Line {$lineNum}: ".($lines[$i] ?? '');
                }

                $contextText = implode("\n", $context);

                $errors[] = [
                    'type' => 'parse',
                    'message' => $e->getMessage()."\n\nContext:\n".$contextText,
                    'line' => $e->sourceLine,
                    'column' => $e->sourceColumn,
                ];
            } catch (\Exception $e) {
                $errors[] = [
                    'type' => 'unknown',
                    'message' => $e->getMessage(),
                    'line' => null,
                    'column' => null,
                ];
            }

            return response()->json([
                'valid' => empty($errors),
                'errors' => $errors,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'errors' => [[
                    'type' => 'fatal',
                    'message' => $e->getMessage(),
                    'line' => null,
                    'column' => null,
                ]],
            ], 422);
        }
    }

    /**
     * Get revision history for a page
     */
    public function revisions(Page $page, Request $request)
    {
        $page_param = is_numeric($request->input('page')) ? (int) $request->input('page') : 1;
        $perPage = is_numeric($request->input('per_page')) ? (int) $request->input('per_page') : 10;

        $revisions = $page->revisionHistory($page_param, $perPage);

        return response()->json([
            'revisions' => $revisions->toArray(),
            'meta' => [
                'total' => $revisions->total(),
                'current_page' => $revisions->currentPage(),
                'per_page' => $revisions->perPage(),
                'has_more' => $revisions->hasMore(),
            ],
            'supports_revisions' => $page->supportsRevisions(),
            'storage_driver' => $page->storage_driver ?? 'database',
        ]);
    }

    /**
     * Get a specific revision
     */
    public function showRevision(Page $page, string $revisionId)
    {
        $revision = $page->getRevisionById($revisionId);

        if (! $revision) {
            return response()->json([
                'error' => 'Revision not found',
            ], 404);
        }

        return response()->json([
            'revision' => $revision->toArray(),
        ]);
    }

    /**
     * Restore a specific revision
     */
    public function restoreRevision(Page $page, string $revisionId)
    {
        if (! $page->supportsRevisions()) {
            return response()->json([
                'error' => 'Revisions are not supported for this storage driver',
            ], 400);
        }

        $success = $page->restoreRevisionById($revisionId);

        if (! $success) {
            return response()->json([
                'error' => 'Failed to restore revision',
            ], 500);
        }

        // Reload the page to get the restored content
        $page->refresh();
        $content = $page->content;

        return response()->json([
            'success' => true,
            'message' => 'Revision restored successfully',
            'page' => [
                'id' => $page->id,
                'content' => $content ? $content->getContent() : null,
                'frontmatter' => $content ? $content->getFrontmatter() : null,
            ],
        ]);
    }

    /**
     * Get available storage drivers from settings
     *
     * @return array<int, array{value: string, label: string, description: string, configured: bool}>
     */
    private function getAvailableStorageDrivers(): array
    {
        // For now, return all drivers - later this will check settings
        // to determine which are fully configured
        return [
            [
                'value' => 'database',
                'label' => 'Database',
                'description' => 'Store content in database',
                'configured' => true,
            ],
            [
                'value' => 'local',
                'label' => 'Local Filesystem',
                'description' => 'Store content in local files',
                'configured' => true,
            ],
            [
                'value' => 's3',
                'label' => 'Amazon S3',
                'description' => 'Store content in AWS S3',
                'configured' => false, // Will check settings
            ],
            [
                'value' => 'github',
                'label' => 'GitHub',
                'description' => 'Store content in GitHub repository',
                'configured' => false,
            ],
            [
                'value' => 'azure',
                'label' => 'Azure Blob Storage',
                'description' => 'Store content in Microsoft Azure',
                'configured' => false,
            ],
            [
                'value' => 'gcs',
                'label' => 'Google Cloud Storage',
                'description' => 'Store content in Google Cloud',
                'configured' => false,
            ],
        ];
    }
}
