<?php

declare(strict_types=1);

namespace App\Domains\PageBuilder\Services;

use App\Domains\PageBuilder\Models\Page;

interface MarkdownRenderServiceContract
{
    /**
     * Render a markdown page to HTML
     *
     * @param Page $page The page to render
     * @param bool $preview Whether this is a preview (don't use cached HTML)
     * @return string The rendered HTML
     */
    public function renderPage(Page $page, bool $preview = false): string;

    /**
     * Render markdown content to HTML without layout
     *
     * @param string $markdown The markdown content with shortcodes
     * @return string The rendered HTML content
     */
    public function renderContent(string $markdown): string;

    /**
     * Render markdown content with a specific layout template
     *
     * @param string $markdown The markdown content
     * @param string $layoutTemplate The layout template name
     * @param Page $page The page model for metadata
     * @param bool $withLayout Whether to wrap in full HTML layout (for preview/standalone)
     * @return string The full rendered HTML with layout
     */
    public function renderWithLayout(string $markdown, string $layoutTemplate, Page $page, bool $withLayout = false): string;

    /**
     * Validate markdown syntax
     *
     * @param string $markdown The markdown to validate
     * @return bool True if valid
     */
    public function validate(string $markdown): bool;

    /**
     * Get available layout templates
     *
     * @return array<string, string> Array of template name => display name
     */
    public function getAvailableLayouts(): array;
}
