<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\Blocks;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\AbstractBlockRenderer;

class FaqRenderer extends AbstractBlockRenderer
{
    public function getTag(): string
    {
        return 'faq';
    }

    public function getOptionalAttributes(): array
    {
        return [
            'title' => 'Frequently Asked Questions',
            'openFirst' => 'false',
        ];
    }

    public function render(ShortcodeNode $node, string $innerHtml = ''): string
    {
        $title = $this->attr($node, 'title');

        $html = '<section class="faq-section py-16 px-4">';
        $html .= '<div class="container mx-auto max-w-3xl">';

        if ($title) {
            $html .= sprintf('<h2 class="text-3xl font-bold text-center mb-12">%s</h2>', $this->e($title));
        }

        $html .= sprintf('<div class="faq-items space-y-4">%s</div>', $innerHtml);
        $html .= '</div></section>';

        return $html;
    }
}
