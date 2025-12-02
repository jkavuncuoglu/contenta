<?php

declare(strict_types=1);

namespace App\Domains\PageBuilder\Http\Controllers\Admin;

use App\Domains\PageBuilder\Actions\PublishPageAction;
use App\Domains\PageBuilder\Models\Layout;
use App\Domains\PageBuilder\Models\Page;
use App\Domains\PageBuilder\Models\PageRevision;
use App\Domains\PageBuilder\Services\PageRenderService;
use App\Domains\PageBuilder\Services\MarkdownRenderServiceContract;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PageController extends Controller
{
    public function __construct(
        private PublishPageAction $publishPageAction
    ) {}

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
            if (is_string($search) && !empty($search)) {
                $query->where('title', 'like', '%' . $search . '%');
            }
        }

        $perPage = $request->input('per_page', 15);
        $pages = $query->paginate(is_numeric($perPage) ? (int) $perPage : 15);

        return Inertia::render('Admin/Pages/Index', [
            'pages' => $pages,
        ]);
    }

    public function create(Request $request): InertiaResponse
    {
        return Inertia::render('Admin/Pages/Create');
    }

    public function edit(Request $request, Page $page): InertiaResponse
    {
        // Load relations expected by the frontend
        $pageData = $page->load('author')->toArray();

        return Inertia::render('Admin/Pages/Edit', [
            'page' => $pageData,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug',
            'content_type' => 'nullable|in:legacy,markdown',
            'layout_id' => 'nullable|exists:pagebuilder_layouts,id',
            'layout_template' => 'nullable|string|max:255',
            'data' => 'nullable|array',
            'markdown_content' => 'nullable|string',
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
        $validated['content_type'] = 'markdown'; // Set default content type to markdown

        // Ensure data and schema_data are never null (use empty array)
        $validated['data'] = $validated['data'] ?? [];
        $validated['schema_data'] = $validated['schema_data'] ?? [];

        $page = Page::create($validated);

        // Create initial revision
        $this->createRevision($page, is_int($userId) ? $userId : null, 'Initial version');

        return redirect()->route('admin.pages.edit', $page->id)
            ->with('success', 'Page created successfully.');
    }

    public function show(Page $page): JsonResponse
    {
        return response()->json($page->load(['layout', 'author']));
    }

    /**
     * Get pages list for API (used by menu selector, etc.)
     */
    public function api(Request $request): JsonResponse
    {
        $query = Page::query()
            ->select(['id', 'title', 'slug', 'status'])
            ->orderBy('title', 'asc');

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search by title
        if ($request->has('search')) {
            $search = $request->input('search');
            if (is_string($search) && !empty($search)) {
                $query->where('title', 'like', '%' . $search . '%');
            }
        }

        $perPage = $request->input('per_page', 50);
        $pages = $query->paginate(is_numeric($perPage) ? (int) $perPage : 50);

        return response()->json($pages);
    }

    public function update(Request $request, Page $page)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('pages', 'slug')->ignore($page->id)
            ],
            'content_type' => 'sometimes|in:legacy,markdown',
            'layout_id' => 'sometimes|nullable|exists:pagebuilder_layouts,id',
            'layout_template' => 'sometimes|nullable|string|max:255',
            'data' => 'sometimes|nullable|array',
            'markdown_content' => 'sometimes|nullable|string',
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

        return redirect()->back()->with('success', 'Page updated successfully.');
    }

    public function destroy(Page $page)
    {
        $page->delete();

        return redirect()->route('admin.pages.index')
            ->with('success', 'Page deleted successfully.');
    }

    public function publish(Page $page)
    {
        try {
            $this->publishPageAction->execute($page);

            return redirect()->back()->with('success', 'Page published successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to publish page: ' . $e->getMessage());
        }
    }

    public function unpublish(Page $page)
    {
        $this->publishPageAction->unpublish($page);

        return redirect()->back()->with('success', 'Page unpublished successfully.');
    }

    public function duplicate(Page $page)
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

        return redirect()->route('admin.pages.edit', $duplicatedPage->id)
            ->with('success', 'Page duplicated successfully.');
    }

    public function preview(Request $request, Page $page): JsonResponse
    {
        try {
            // If POST request with data, use that for preview (unsaved changes)
            if ($request->isMethod('post')) {
                $tempPage = $page->replicate();

                if ($page->isMarkdown()) {
                    // Markdown page preview
                    if ($request->has('markdown_content')) {
                        $tempPage->markdown_content = $request->input('markdown_content');
                    }
                    if ($request->has('layout_template')) {
                        $tempPage->layout_template = $request->input('layout_template');
                    }
                    $tempPage->title = $request->input('title', $page->title);

                    // Use renderWithLayout with layout enabled for preview
                    $markdownService = app(MarkdownRenderServiceContract::class);
                    $previewHtml = $markdownService->renderWithLayout(
                        $tempPage->markdown_content ?? '',
                        $tempPage->getLayoutTemplateName(),
                        $tempPage,
                        true // Enable full HTML layout for preview
                    );
                } else {
                    // Legacy page builder preview
                    if ($request->has('data')) {
                        $tempPage->data = $request->input('data');
                    }
                    $tempPage->title = $request->input('title', $page->title);
                    $tempPage->layout_id = $request->input('layout_id', $page->layout_id);

                    $previewHtml = app(PageRenderService::class)->renderPage($tempPage);
                }
            } else {
                // Use existing saved page data
                if ($page->isMarkdown()) {
                    // Use renderWithLayout with layout enabled for preview
                    $markdownService = app(MarkdownRenderServiceContract::class);
                    $previewHtml = $markdownService->renderWithLayout(
                        $page->markdown_content ?? '',
                        $page->getLayoutTemplateName(),
                        $page,
                        true // Enable full HTML layout for preview
                    );
                } else {
                    $previewHtml = app(PageRenderService::class)->renderPage($page);
                }
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

    public function validate(Request $request): JsonResponse
    {
        $content = $request->input('markdown_content', '');

        try {
            // Use the ShortcodeParserService to validate the content
            $parserService = app(MarkdownRenderServiceContract::class);

            // Try to parse the content - this will throw exceptions if there are errors
            // We need to catch both parse and render errors
            $errors = [];

            try {
                // Create a temporary page object for validation
                $tempPage = new Page();
                $tempPage->markdown_content = $content;
                $tempPage->title = 'Validation';
                $tempPage->layout_template = 'default';

                // Attempt to render - this will trigger validation
                $parserService->renderPage($tempPage, true);
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
                    $context[] = $prefix . "Line {$lineNum}: " . ($lines[$i] ?? '');
                }

                $contextText = implode("\n", $context);

                $errors[] = [
                    'type' => 'parse',
                    'message' => $e->getMessage() . "\n\nContext:\n" . $contextText,
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
            'data' => $page->data ?? [],  // Use empty array if data is null
            'content_type' => $page->content_type,
            'markdown_content' => $page->markdown_content,
            'layout_template' => $page->layout_template,
            'status' => $page->status,
            'meta_title' => $page->meta_title,
            'meta_description' => $page->meta_description,
            'meta_keywords' => $page->meta_keywords,
            'schema_data' => $page->schema_data ?? [],  // Use empty array if schema_data is null
            'reason' => $reason,
            'revision_number' => $revisionNumber,
        ]);
    }
}
