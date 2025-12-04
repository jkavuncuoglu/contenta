<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\Blocks;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\AbstractBlockRenderer;

class MenuRenderer extends AbstractBlockRenderer
{
    public function getTag(): string
    {
        return 'menu';
    }

    public function getOptionalAttributes(): array
    {
        return [
            'id' => '',
            'orientation' => 'horizontal',
            'className' => '',
        ];
    }

    public function render(ShortcodeNode $node, string $innerHtml = ''): string
    {
        $orientation = $this->attr($node, 'orientation');
        $className = $this->attr($node, 'className');

        $orientationClass = $orientation === 'vertical' ? 'flex-col space-y-2' : 'flex-row space-x-6';

        $classes = "menu flex {$orientationClass}";
        if ($className) {
            $classes .= " {$className}";
        }

        return sprintf('<nav class="%s">%s</nav>', $this->e($classes), $innerHtml);
    }
}
