<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\Blocks;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\AbstractBlockRenderer;

class ListRenderer extends AbstractBlockRenderer
{
    public function getTag(): string
    {
        return 'list';
    }

    public function getOptionalAttributes(): array
    {
        return [
            'type' => 'unordered',
            'icon' => '',
            'className' => '',
        ];
    }

    public function render(ShortcodeNode $node, string $innerHtml = ''): string
    {
        $type = $this->attr($node, 'type');
        $icon = $this->attr($node, 'icon');
        $className = $this->attr($node, 'className');

        $tag = $type === 'ordered' ? 'ol' : 'ul';
        $classes = 'list-inside space-y-2';

        if ($type === 'ordered') {
            $classes .= ' list-decimal';
        } elseif ($type === 'checklist') {
            $classes .= ' list-none';
        } else {
            $classes .= ' list-disc';
        }

        if ($className) {
            $classes .= " {$className}";
        }

        // If icon is provided, we'll need to parse the list items
        // For now, just render the content
        return sprintf('<%s class="%s">%s</%s>', $tag, $this->e($classes), $innerHtml, $tag);
    }
}
