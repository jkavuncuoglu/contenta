<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\Blocks;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\AbstractBlockRenderer;

class BreadcrumbRenderer extends AbstractBlockRenderer
{
    public function getTag(): string
    {
        return 'breadcrumb';
    }

    public function getOptionalAttributes(): array
    {
        return [
            'separator' => '/',
        ];
    }

    public function render(ShortcodeNode $node, string $innerHtml = ''): string
    {
        return sprintf(
            '<nav class="breadcrumb text-sm text-gray-600" aria-label="Breadcrumb"><ol class="flex items-center space-x-2">%s</ol></nav>',
            $innerHtml
        );
    }
}
