<?php

declare(strict_types=1);

namespace App\Domains\PageBuilder\Services;

use App\Domains\ContentManagement\Services\ShortcodeParser\ShortcodeParserServiceContract;
use App\Domains\PageBuilder\Models\Page;
use Illuminate\Support\Facades\View;

class MarkdownRenderService implements MarkdownRenderServiceContract
{
    public function __construct(
        private ShortcodeParserServiceContract $parser
    ) {}

    /**
     * Render a markdown page to HTML
     */
    public function renderPage(Page $page, bool $preview = false): string
    {
        // If not preview and we have cached HTML, use it
        if (! $preview && $page->published_html && $page->isPublished()) {
            return $page->published_html;
        }

        // Get markdown content
        $markdown = $page->markdown_content;

        if (! $markdown) {
            return '<div class="text-center text-gray-500 py-12">No content available</div>';
        }

        // Get layout template
        $layoutTemplate = $page->getLayoutTemplateName();

        // Render with layout
        return $this->renderWithLayout($markdown, $layoutTemplate, $page);
    }

    /**
     * Render markdown content to HTML without layout
     */
    public function renderContent(string $markdown): string
    {
        return $this->parser->parseAndRender($markdown);
    }

    /**
     * Render markdown content with a specific layout template
     *
     * For Inertia SPA pages, we only return the parsed content (no full HTML document)
     * For standalone pages/preview, we can wrap in a full layout
     */
    public function renderWithLayout(string $markdown, string $layoutTemplate, Page $page, bool $withLayout = false): string
    {
        // Parse and render the markdown content
        $content = $this->parser->parseAndRender($markdown);

        // If layout is not requested, return content only
        // (for Inertia pages - the Vue component will handle the layout)
        if (!$withLayout) {
            return $content;
        }

        // For preview/standalone rendering, wrap in full HTML layout
        // Check if layout template exists
        $layoutView = "layouts.markdown.{$layoutTemplate}";

        if (! View::exists($layoutView)) {
            // Fallback to default layout
            $layoutView = 'layouts.markdown.default';
        }

        // Render with Blade layout
        return View::make($layoutView, [
            'page' => $page,
            'content' => $content,
        ])->render();
    }

    /**
     * Validate markdown syntax
     */
    public function validate(string $markdown): bool
    {
        try {
            $this->parser->validate($markdown);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get available layout templates
     */
    public function getAvailableLayouts(): array
    {
        return [
            'default' => 'Default (Centered Container)',
            'full-width' => 'Full Width',
            'sidebar-left' => 'Left Sidebar',
            'sidebar-right' => 'Right Sidebar',
        ];
    }

    /**
     * Get the shortcode parser instance
     */
    public function getParser(): ShortcodeParserServiceContract
    {
        return $this->parser;
    }

    /**
     * Render page and cache the HTML
     *
     * @param Page $page
     * @return string The rendered HTML
     */
    public function renderAndCache(Page $page): string
    {
        // Just render the page - the PublishPageAction will handle saving
        return $this->renderPage($page, true);
    }
}
