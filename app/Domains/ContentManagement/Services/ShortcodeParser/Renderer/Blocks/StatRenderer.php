<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\Blocks;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\AbstractBlockRenderer;

class StatRenderer extends AbstractBlockRenderer
{
    public function getTag(): string
    {
        return 'stat';
    }

    public function getRequiredAttributes(): array
    {
        return ['value', 'label'];
    }

    public function getOptionalAttributes(): array
    {
        return [
            'description' => '',
        ];
    }

    public function render(ShortcodeNode $node, string $innerHtml = ''): string
    {
        $value = $this->attr($node, 'value');
        $label = $this->attr($node, 'label');
        $description = $this->attr($node, 'description');

        $html = '<div class="stat-item text-center">';
        $html .= sprintf('<div class="stat-value text-4xl md:text-5xl font-bold text-blue-600 mb-2">%s</div>', $this->e($value));
        $html .= sprintf('<div class="stat-label text-sm font-semibold text-gray-900 uppercase tracking-wide">%s</div>', $this->e($label));

        if ($description) {
            $html .= sprintf('<p class="stat-description text-sm text-gray-600 mt-2">%s</p>', $this->e($description));
        }

        $html .= '</div>';

        return $html;
    }
}
