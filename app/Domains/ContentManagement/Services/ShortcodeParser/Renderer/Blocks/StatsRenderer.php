<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\Blocks;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\AbstractBlockRenderer;

class StatsRenderer extends AbstractBlockRenderer
{
    public function getTag(): string
    {
        return 'stats';
    }

    public function getOptionalAttributes(): array
    {
        return [
            'columns' => '3',
            'backgroundColor' => '',
        ];
    }

    public function render(ShortcodeNode $node, string $innerHtml = ''): string
    {
        $columns = $this->attr($node, 'columns');
        $bgColor = $this->attr($node, 'backgroundColor');

        $colsClass = match ($columns) {
            '2' => 'grid-cols-2',
            '3' => 'grid-cols-2 md:grid-cols-3',
            '4' => 'grid-cols-2 md:grid-cols-4',
            default => 'grid-cols-2 md:grid-cols-3',
        };

        $bgClass = $bgColor ?: 'bg-gray-50';

        $html = sprintf('<section class="stats-section %s py-16 px-4">', $this->e($bgClass));
        $html .= '<div class="container mx-auto max-w-6xl">';
        $html .= sprintf('<div class="stats-grid grid %s gap-8">%s</div>', $colsClass, $innerHtml);
        $html .= '</div></section>';

        return $html;
    }
}
