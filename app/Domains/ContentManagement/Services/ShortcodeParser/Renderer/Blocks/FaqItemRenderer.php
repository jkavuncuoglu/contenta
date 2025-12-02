<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\Blocks;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\AbstractBlockRenderer;

class FaqItemRenderer extends AbstractBlockRenderer
{
    public function getTag(): string
    {
        return 'faq-item';
    }

    public function getRequiredAttributes(): array
    {
        return ['question'];
    }

    public function render(ShortcodeNode $node, string $innerHtml = ''): string
    {
        $question = $this->attr($node, 'question');

        $html = '<details class="faq-item bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition">';
        $html .= sprintf('<summary class="font-semibold text-gray-900 cursor-pointer">%s</summary>', $this->e($question));
        $html .= sprintf('<div class="mt-4 text-gray-600">%s</div>', $innerHtml);
        $html .= '</details>';

        return $html;
    }
}
