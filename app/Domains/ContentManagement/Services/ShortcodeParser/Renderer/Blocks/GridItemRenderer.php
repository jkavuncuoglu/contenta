<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\Blocks;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\AbstractBlockRenderer;

class GridItemRenderer extends AbstractBlockRenderer
{
    public function getTag(): string
    {
        return 'grid-item';
    }

    public function render(ShortcodeNode $node, string $innerHtml = ''): string
    {
        return sprintf('<div class="grid-item">%s</div>', $innerHtml);
    }
}
