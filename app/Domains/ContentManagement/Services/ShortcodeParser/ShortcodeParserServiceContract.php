<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\DocumentNode;

interface ShortcodeParserServiceContract
{
    /**
     * Parse markdown with shortcodes to AST
     */
    public function parse(string $content): DocumentNode;

    /**
     * Render AST to HTML
     */
    public function render(DocumentNode $document): string;

    /**
     * Parse and render in one step
     */
    public function parseAndRender(string $content): string;

    /**
     * Validate syntax without rendering
     */
    public function validate(string $content): bool;

    /**
     * Extract front matter from markdown
     *
     * @return array{content: string, metadata: array<string, mixed>}
     */
    public function extractFrontMatter(string $content): array;
}
