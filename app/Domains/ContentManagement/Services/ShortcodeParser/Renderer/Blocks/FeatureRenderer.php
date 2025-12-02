<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\Blocks;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\AbstractBlockRenderer;

class FeatureRenderer extends AbstractBlockRenderer
{
    public function getTag(): string
    {
        return 'feature';
    }

    public function getOptionalAttributes(): array
    {
        return [
            'icon' => '',
            'title' => '',
        ];
    }

    public function render(ShortcodeNode $node, string $innerHtml = ''): string
    {
        $icon = $this->attr($node, 'icon');
        $title = $this->attr($node, 'title');

        $html = '<div class="feature-item text-center">';

        if ($icon) {
            $html .= sprintf('<div class="feature-icon text-4xl mb-4">%s</div>', $this->e($icon));
        }

        if ($title) {
            $html .= sprintf('<h3 class="text-xl font-semibold text-gray-900 mb-2">%s</h3>', $this->e($title));
        }

        $html .= sprintf('<div class="feature-content text-gray-600">%s</div>', $innerHtml);
        $html .= '</div>';

        return $html;
    }
}
