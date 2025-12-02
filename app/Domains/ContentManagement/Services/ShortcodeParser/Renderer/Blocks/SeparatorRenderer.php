<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\Blocks;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\AbstractBlockRenderer;

class SeparatorRenderer extends AbstractBlockRenderer
{
    public function getTag(): string
    {
        return 'separator';
    }

    public function getOptionalAttributes(): array
    {
        return [
            'style' => 'solid',
            'thickness' => '1',
            'color' => 'gray-200',
            'spacing' => '8',
        ];
    }

    public function render(ShortcodeNode $node, string $innerHtml = ''): string
    {
        $style = $this->attr($node, 'style');
        $thickness = $this->attr($node, 'thickness');
        $color = $this->attr($node, 'color');
        $spacing = $this->attr($node, 'spacing');

        $styleClass = match ($style) {
            'dashed' => 'border-dashed',
            'dotted' => 'border-dotted',
            default => 'border-solid',
        };

        return sprintf(
            '<hr class="separator my-%s border-%s border-t-%s %s" />',
            $spacing,
            $color,
            $thickness,
            $styleClass
        );
    }
}
