<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\Blocks;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\AbstractBlockRenderer;

class TestimonialsRenderer extends AbstractBlockRenderer
{
    public function getTag(): string
    {
        return 'testimonials';
    }

    public function getOptionalAttributes(): array
    {
        return [
            'title' => '',
            'columns' => '2',
        ];
    }

    public function render(ShortcodeNode $node, string $innerHtml = ''): string
    {
        $title = $this->attr($node, 'title');
        $columns = $this->attr($node, 'columns');

        $colsClass = match ($columns) {
            '1' => 'grid-cols-1',
            '2' => 'grid-cols-1 md:grid-cols-2',
            '3' => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
            default => 'grid-cols-1 md:grid-cols-2',
        };

        $html = '<section class="testimonials-section py-16 px-4">';
        $html .= '<div class="container mx-auto max-w-6xl">';

        if ($title) {
            $html .= sprintf('<h2 class="text-3xl font-bold text-center mb-12">%s</h2>', $this->e($title));
        }

        $html .= sprintf('<div class="testimonials-grid grid %s gap-8">%s</div>', $colsClass, $innerHtml);
        $html .= '</div></section>';

        return $html;
    }
}
