<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\Blocks;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\AbstractBlockRenderer;

class ButtonRenderer extends AbstractBlockRenderer
{
    public function getTag(): string
    {
        return 'button';
    }

    public function getRequiredAttributes(): array
    {
        return ['url'];
    }

    public function getOptionalAttributes(): array
    {
        return [
            'variant' => 'primary',
            'size' => 'md',
            'target' => '_self',
            'className' => '',
        ];
    }

    public function render(ShortcodeNode $node, string $innerHtml = ''): string
    {
        $url = $this->attr($node, 'url');
        $variant = $this->attr($node, 'variant');
        $size = $this->attr($node, 'size');
        $target = $this->attr($node, 'target');
        $className = $this->attr($node, 'className');

        $variantClass = match ($variant) {
            'secondary' => 'bg-gray-600 hover:bg-gray-700 text-white',
            'outline' => 'bg-transparent border-2 border-blue-600 text-blue-600 hover:bg-blue-50',
            'ghost' => 'bg-transparent text-blue-600 hover:bg-blue-50',
            default => 'bg-blue-600 hover:bg-blue-700 text-white',
        };

        $sizeClass = match ($size) {
            'sm' => 'px-4 py-2 text-sm',
            'lg' => 'px-8 py-4 text-lg',
            default => 'px-6 py-3 text-base',
        };

        $classes = "btn inline-block rounded-lg font-semibold transition {$variantClass} {$sizeClass}";
        if ($className) {
            $classes .= " {$className}";
        }

        return $this->tag('a', [
            'href' => $url,
            'target' => $target,
            'class' => $classes,
        ], $innerHtml);
    }
}
