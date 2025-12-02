<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\Blocks;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\AbstractBlockRenderer;

class ContainerRenderer extends AbstractBlockRenderer
{
    public function getTag(): string
    {
        return 'container';
    }

    public function getOptionalAttributes(): array
    {
        return [
            'width' => 'lg',
            'padding' => '8',
            'backgroundColor' => '',
            'className' => '',
        ];
    }

    public function render(ShortcodeNode $node, string $innerHtml = ''): string
    {
        $width = $this->attr($node, 'width');
        $padding = $this->attr($node, 'padding');
        $bgColor = $this->attr($node, 'backgroundColor');
        $className = $this->attr($node, 'className');

        $widthClass = match ($width) {
            'sm' => 'max-w-2xl',
            'md' => 'max-w-4xl',
            'lg' => 'max-w-6xl',
            'xl' => 'max-w-7xl',
            '2xl' => 'max-w-screen-2xl',
            'full' => 'max-w-full',
            default => 'max-w-6xl',
        };

        $classes = "container mx-auto {$widthClass} p-{$padding}";
        if ($bgColor) {
            $classes .= " {$bgColor}";
        }
        if ($className) {
            $classes .= " {$className}";
        }

        return sprintf('<div class="%s">%s</div>', $this->e($classes), $innerHtml);
    }
}
