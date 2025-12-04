<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services;

use App\Domains\ContentManagement\Services\ShortcodeParser\ShortcodeParserServiceContract;

class MarkdownRenderService implements MarkdownRenderServiceContract
{
    public function __construct(
        private ShortcodeParserServiceContract $parser
    ) {}

    /**
     * Render markdown content to HTML
     */
    public function render(string $markdown): string
    {
        if (empty($markdown)) {
            return '<div class="text-center text-gray-500 py-12">No content available</div>';
        }

        return $this->parser->parseAndRender($markdown);
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
     * Get the shortcode parser instance
     */
    public function getParser(): ShortcodeParserServiceContract
    {
        return $this->parser;
    }
}
