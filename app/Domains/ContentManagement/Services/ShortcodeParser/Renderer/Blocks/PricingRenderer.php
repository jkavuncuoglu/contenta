<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\Blocks;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\AbstractBlockRenderer;

class PricingRenderer extends AbstractBlockRenderer
{
    public function getTag(): string
    {
        return 'pricing';
    }

    public function getOptionalAttributes(): array
    {
        return [
            'title' => '',
            'subtitle' => '',
        ];
    }

    public function render(ShortcodeNode $node, string $innerHtml = ''): string
    {
        $title = $this->attr($node, 'title');
        $subtitle = $this->attr($node, 'subtitle');

        $html = '<section class="pricing-section py-16 px-4">';
        $html .= '<div class="container mx-auto max-w-6xl">';

        if ($title || $subtitle) {
            $html .= '<div class="text-center mb-12">';
            if ($title) {
                $html .= sprintf('<h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">%s</h2>', $this->e($title));
            }
            if ($subtitle) {
                $html .= sprintf('<p class="text-lg text-gray-600">%s</p>', $this->e($subtitle));
            }
            $html .= '</div>';
        }

        $html .= sprintf('<div class="pricing-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">%s</div>', $innerHtml);
        $html .= '</div></section>';

        return $html;
    }
}
