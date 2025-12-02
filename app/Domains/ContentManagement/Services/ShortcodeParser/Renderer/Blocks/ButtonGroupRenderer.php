<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\Blocks;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\AbstractBlockRenderer;

class ButtonGroupRenderer extends AbstractBlockRenderer
{
    public function getTag(): string
    {
        return 'button-group';
    }

    public function getOptionalAttributes(): array
    {
        return [
            'align' => 'left',
            'gap' => '4',
        ];
    }

    public function render(ShortcodeNode $node, string $innerHtml = ''): string
    {
        $align = $this->attr($node, 'align');
        $gap = $this->attr($node, 'gap');

        $alignClass = match ($align) {
            'center' => 'justify-center',
            'right' => 'justify-end',
            default => 'justify-start',
        };

        return sprintf(
            '<div class="button-group flex flex-wrap gap-%s %s">%s</div>',
            $gap,
            $alignClass,
            $innerHtml
        );
    }
}
