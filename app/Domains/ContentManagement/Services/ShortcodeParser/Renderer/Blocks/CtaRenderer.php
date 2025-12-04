<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\Blocks;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\AbstractBlockRenderer;

class CtaRenderer extends AbstractBlockRenderer
{
    public function getTag(): string
    {
        return 'cta';
    }

    public function getRequiredAttributes(): array
    {
        return ['title', 'buttonText', 'buttonUrl'];
    }

    public function getOptionalAttributes(): array
    {
        return [
            'description' => '',
            'backgroundColor' => 'bg-blue-600',
            'textColor' => 'text-white',
        ];
    }

    public function render(ShortcodeNode $node, string $innerHtml = ''): string
    {
        $title = $this->attr($node, 'title');
        $description = $this->attr($node, 'description');
        $buttonText = $this->attr($node, 'buttonText');
        $buttonUrl = $this->attr($node, 'buttonUrl');
        $bgColor = $this->attr($node, 'backgroundColor');
        $textColor = $this->attr($node, 'textColor');

        $html = sprintf('<section class="cta-section %s %s py-16 px-4">', $this->e($bgColor), $this->e($textColor));
        $html .= '<div class="container mx-auto max-w-4xl text-center">';

        $html .= sprintf('<h2 class="text-3xl md:text-4xl font-bold mb-4">%s</h2>', $this->e($title));

        if ($description) {
            $html .= sprintf('<p class="text-lg mb-8 opacity-90">%s</p>', $this->e($description));
        }

        $html .= sprintf(
            '<a href="%s" class="btn inline-block px-8 py-4 bg-white text-blue-600 rounded-lg font-semibold hover:bg-gray-100 transition">%s</a>',
            $this->e($buttonUrl),
            $this->e($buttonText)
        );

        $html .= '</div></section>';

        return $html;
    }
}
