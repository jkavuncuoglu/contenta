<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\Blocks;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\AbstractBlockRenderer;

class GridRenderer extends AbstractBlockRenderer
{
    public function getTag(): string
    {
        return 'grid';
    }

    public function getOptionalAttributes(): array
    {
        return [
            'columns' => '3',
            'gap' => '6',
            'responsive' => 'true',
        ];
    }

    public function render(ShortcodeNode $node, string $innerHtml = ''): string
    {
        $columns = $this->attr($node, 'columns');
        $gap = $this->attr($node, 'gap');
        $responsive = $this->attr($node, 'responsive') === 'true';

        if ($responsive) {
            $colsClass = match ($columns) {
                '2' => 'grid-cols-1 md:grid-cols-2',
                '3' => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
                '4' => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4',
                '5' => 'grid-cols-2 md:grid-cols-3 lg:grid-cols-5',
                '6' => 'grid-cols-2 md:grid-cols-3 lg:grid-cols-6',
                default => 'grid-cols-1 md:grid-cols-3',
            };
        } else {
            $colsClass = "grid-cols-{$columns}";
        }

        return sprintf(
            '<div class="grid %s gap-%s">%s</div>',
            $colsClass,
            $gap,
            $innerHtml
        );
    }
}
