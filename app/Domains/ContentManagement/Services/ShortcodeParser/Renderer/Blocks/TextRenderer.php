<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\Blocks;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\AbstractBlockRenderer;

class TextRenderer extends AbstractBlockRenderer
{
    public function getTag(): string
    {
        return 'text';
    }

    public function getOptionalAttributes(): array
    {
        return [
            'align' => 'left',
            'fontSize' => 'base',
            'className' => '',
        ];
    }

    public function render(ShortcodeNode $node, string $innerHtml = ''): string
    {
        $align = $this->attr($node, 'align');
        $fontSize = $this->attr($node, 'fontSize');
        $className = $this->attr($node, 'className');

        $alignClass = match ($align) {
            'center' => 'text-center',
            'right' => 'text-right',
            'justify' => 'text-justify',
            default => 'text-left',
        };

        $sizeClass = match ($fontSize) {
            'sm' => 'text-sm',
            'lg' => 'text-lg',
            'xl' => 'text-xl',
            default => 'text-base',
        };

        $classes = "{$sizeClass} {$alignClass} text-gray-700";
        if ($className) {
            $classes .= " {$className}";
        }

        return sprintf('<div class="%s">%s</div>', $this->e($classes), $innerHtml);
    }
}
