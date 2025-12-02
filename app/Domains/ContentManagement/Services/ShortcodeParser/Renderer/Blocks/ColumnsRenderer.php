<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\Blocks;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\AbstractBlockRenderer;

class ColumnsRenderer extends AbstractBlockRenderer
{
    public function getTag(): string
    {
        return 'columns';
    }

    public function getOptionalAttributes(): array
    {
        return [
            'ratio' => '1:1',
            'gap' => '8',
            'reverse' => 'false',
            'align' => 'start',
        ];
    }

    public function render(ShortcodeNode $node, string $innerHtml = ''): string
    {
        $ratio = $this->attr($node, 'ratio');
        $gap = $this->attr($node, 'gap');
        $reverse = $this->attr($node, 'reverse') === 'true';
        $align = $this->attr($node, 'align');

        $gridCols = match ($ratio) {
            '1:2' => 'grid-cols-1 md:grid-cols-3',
            '2:1' => 'grid-cols-1 md:grid-cols-3',
            '1:3' => 'grid-cols-1 md:grid-cols-4',
            '3:1' => 'grid-cols-1 md:grid-cols-4',
            default => 'grid-cols-1 md:grid-cols-2',
        };

        $alignClass = match ($align) {
            'center' => 'items-center',
            'end' => 'items-end',
            'stretch' => 'items-stretch',
            default => 'items-start',
        };

        $reverseClass = $reverse ? 'flex-col-reverse md:grid' : '';

        return sprintf(
            '<div class="columns-wrapper grid %s gap-%s %s %s">%s</div>',
            $gridCols,
            $gap,
            $alignClass,
            $reverseClass,
            $innerHtml
        );
    }
}
