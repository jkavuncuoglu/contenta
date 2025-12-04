<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\Blocks;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\AbstractBlockRenderer;

class QuoteRenderer extends AbstractBlockRenderer
{
    public function getTag(): string
    {
        return 'quote';
    }

    public function getOptionalAttributes(): array
    {
        return [
            'author' => '',
            'role' => '',
            'cite' => '',
            'className' => '',
        ];
    }

    public function render(ShortcodeNode $node, string $innerHtml = ''): string
    {
        $author = $this->attr($node, 'author');
        $role = $this->attr($node, 'role');
        $cite = $this->attr($node, 'cite');
        $className = $this->attr($node, 'className');

        $classes = 'blockquote border-l-4 border-blue-600 pl-6 py-4 my-6 italic text-gray-700';
        if ($className) {
            $classes .= " {$className}";
        }

        $html = sprintf('<blockquote class="%s"', $this->e($classes));
        if ($cite) {
            $html .= sprintf(' cite="%s"', $this->e($cite));
        }
        $html .= '>';

        $html .= sprintf('<p class="text-lg">%s</p>', $innerHtml);

        if ($author || $role) {
            $html .= '<footer class="mt-4 text-sm not-italic text-gray-600">';
            if ($author) {
                $html .= sprintf('<cite class="font-semibold">%s</cite>', $this->e($author));
            }
            if ($role) {
                $html .= sprintf('<span class="text-gray-500">%s%s</span>', $author ? ', ' : '', $this->e($role));
            }
            $html .= '</footer>';
        }

        $html .= '</blockquote>';

        return $html;
    }
}
