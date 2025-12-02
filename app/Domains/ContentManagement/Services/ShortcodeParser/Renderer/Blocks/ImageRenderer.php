<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\Blocks;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\AbstractBlockRenderer;

class ImageRenderer extends AbstractBlockRenderer
{
    public function getTag(): string
    {
        return 'image';
    }

    public function getRequiredAttributes(): array
    {
        return ['src', 'alt'];
    }

    public function getOptionalAttributes(): array
    {
        return [
            'caption' => '',
            'width' => 'full',
            'rounded' => 'md',
            'shadow' => 'false',
            'className' => '',
        ];
    }

    public function render(ShortcodeNode $node, string $innerHtml = ''): string
    {
        $src = $this->attr($node, 'src');
        $alt = $this->attr($node, 'alt');
        $caption = $this->attr($node, 'caption');
        $width = $this->attr($node, 'width');
        $rounded = $this->attr($node, 'rounded');
        $shadow = $this->attr($node, 'shadow') === 'true';
        $className = $this->attr($node, 'className');

        $widthClass = match ($width) {
            'sm' => 'max-w-sm',
            'md' => 'max-w-md',
            'lg' => 'max-w-lg',
            'xl' => 'max-w-xl',
            'full' => 'w-full',
            default => 'w-full',
        };

        $roundedClass = $rounded !== 'none' ? "rounded-{$rounded}" : '';
        $shadowClass = $shadow ? 'shadow-lg' : '';

        $classes = "{$widthClass} {$roundedClass} {$shadowClass}";
        if ($className) {
            $classes .= " {$className}";
        }

        $html = '<figure class="image-block">';
        $html .= $this->tag('img', [
            'src' => $src,
            'alt' => $alt,
            'class' => trim($classes),
        ]);

        if ($caption) {
            $html .= sprintf('<figcaption class="text-sm text-gray-600 mt-2 text-center">%s</figcaption>', $this->e($caption));
        }

        $html .= '</figure>';

        return $html;
    }
}
