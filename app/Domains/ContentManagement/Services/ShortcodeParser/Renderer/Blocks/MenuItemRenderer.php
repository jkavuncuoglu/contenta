<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\Blocks;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\AbstractBlockRenderer;

class MenuItemRenderer extends AbstractBlockRenderer
{
    public function getTag(): string
    {
        return 'menu-item';
    }

    public function getRequiredAttributes(): array
    {
        return ['url', 'label'];
    }

    public function getOptionalAttributes(): array
    {
        return [
            'icon' => '',
            'target' => '_self',
        ];
    }

    public function render(ShortcodeNode $node, string $innerHtml = ''): string
    {
        $url = $this->attr($node, 'url');
        $label = $this->attr($node, 'label');
        $icon = $this->attr($node, 'icon');
        $target = $this->attr($node, 'target');

        $content = '';
        if ($icon) {
            $content .= sprintf('<span class="menu-icon">%s</span> ', $this->e($icon));
        }
        $content .= $this->e($label);

        return $this->tag('a', [
            'href' => $url,
            'target' => $target,
            'class' => 'menu-item text-gray-700 hover:text-blue-600 font-medium transition',
        ], $content);
    }
}
