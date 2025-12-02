<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\Blocks;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\AbstractBlockRenderer;

class ColumnRenderer extends AbstractBlockRenderer
{
    public function getTag(): string
    {
        return 'column';
    }

    public function render(ShortcodeNode $node, string $innerHtml = ''): string
    {
        return sprintf('<div class="column">%s</div>', $innerHtml);
    }
}
