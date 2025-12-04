<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\Blocks;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\AbstractBlockRenderer;

class FeaturesRenderer extends AbstractBlockRenderer
{
    public function getTag(): string
    {
        return 'features';
    }

    public function getOptionalAttributes(): array
    {
        return [
            'title' => '',
            'subtitle' => '',
            'columns' => '3',
        ];
    }

    public function render(ShortcodeNode $node, string $innerHtml = ''): string
    {
        $title = $this->attr($node, 'title');
        $subtitle = $this->attr($node, 'subtitle');
        $columns = $this->attr($node, 'columns');

        $colsClass = match ($columns) {
            '2' => 'grid-cols-1 md:grid-cols-2',
            '3' => 'grid-cols-1 md:grid-cols-3',
            '4' => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4',
            default => 'grid-cols-1 md:grid-cols-3',
        };

        $html = '<section class="features-section py-16 px-4">';
        $html .= '<div class="container mx-auto max-w-6xl">';

        if ($title || $subtitle) {
            $html .= '<div class="text-center mb-12">';
            if ($subtitle) {
                $html .= sprintf('<p class="text-sm font-semibold text-blue-600 uppercase tracking-wide mb-2">%s</p>', $this->e($subtitle));
            }
            if ($title) {
                $html .= sprintf('<h2 class="text-3xl md:text-4xl font-bold text-gray-900">%s</h2>', $this->e($title));
            }
            $html .= '</div>';
        }

        $html .= sprintf('<div class="features-grid grid %s gap-8">%s</div>', $colsClass, $innerHtml);
        $html .= '</div></section>';

        return $html;
    }
}
