<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\Blocks;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\AbstractBlockRenderer;

class HtmlRenderer extends AbstractBlockRenderer
{
    public function getTag(): string
    {
        return 'html';
    }

    public function render(ShortcodeNode $node, string $innerHtml = ''): string
    {
        // WARNING: This renders raw HTML without sanitization
        // Should only be used for trusted content
        // In production, you might want to use HTMLPurifier or similar
        return $innerHtml;
    }
}
