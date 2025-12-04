<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services;

use App\Domains\ContentManagement\Services\ShortcodeParser\ShortcodeParserServiceContract;

interface MarkdownRenderServiceContract
{
    /**
     * Render markdown content to HTML
     *
     * @param  string  $markdown  The markdown content with shortcodes
     * @return string The rendered HTML content
     */
    public function render(string $markdown): string;

    /**
     * Validate markdown syntax
     *
     * @param  string  $markdown  The markdown to validate
     * @return bool True if valid
     */
    public function validate(string $markdown): bool;

    /**
     * Get the shortcode parser instance
     */
    public function getParser(): ShortcodeParserServiceContract;
}
