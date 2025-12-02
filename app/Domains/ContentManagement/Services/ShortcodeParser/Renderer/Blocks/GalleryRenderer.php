<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\Blocks;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\AbstractBlockRenderer;

class GalleryRenderer extends AbstractBlockRenderer
{
    public function getTag(): string
    {
        return 'gallery';
    }

    public function getOptionalAttributes(): array
    {
        return [
            'columns' => '3',
            'gap' => '4',
            'rounded' => 'md',
            'aspectRatio' => 'square',
        ];
    }

    public function render(ShortcodeNode $node, string $innerHtml = ''): string
    {
        $columns = $this->attr($node, 'columns');
        $gap = $this->attr($node, 'gap');
        $rounded = $this->attr($node, 'rounded');

        $colsClass = match ($columns) {
            '2' => 'grid-cols-2',
            '3' => 'grid-cols-1 sm:grid-cols-2 md:grid-cols-3',
            '4' => 'grid-cols-2 md:grid-cols-4',
            '5' => 'grid-cols-2 md:grid-cols-5',
            '6' => 'grid-cols-3 md:grid-cols-6',
            default => 'grid-cols-3',
        };

        return sprintf(
            '<div class="gallery-grid grid %s gap-%s">%s</div>',
            $colsClass,
            $gap,
            $innerHtml
        );
    }
}
