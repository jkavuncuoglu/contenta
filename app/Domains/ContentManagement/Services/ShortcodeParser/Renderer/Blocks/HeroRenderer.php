<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\Blocks;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\AbstractBlockRenderer;

class HeroRenderer extends AbstractBlockRenderer
{
    public function getTag(): string
    {
        return 'hero';
    }

    public function getOptionalAttributes(): array
    {
        return [
            'title' => '',
            'subtitle' => '',
            'description' => '',
            'primaryButtonText' => '',
            'primaryButtonUrl' => '',
            'secondaryButtonText' => '',
            'secondaryButtonUrl' => '',
            'backgroundColor' => 'bg-gradient-to-b from-blue-50 to-white',
            'textAlign' => 'center',
        ];
    }

    public function render(ShortcodeNode $node, string $innerHtml = ''): string
    {
        $title = $this->attr($node, 'title');
        $subtitle = $this->attr($node, 'subtitle');
        $description = $this->attr($node, 'description');
        $primaryText = $this->attr($node, 'primaryButtonText');
        $primaryUrl = $this->attr($node, 'primaryButtonUrl');
        $secondaryText = $this->attr($node, 'secondaryButtonText');
        $secondaryUrl = $this->attr($node, 'secondaryButtonUrl');
        $bgClass = $this->attr($node, 'backgroundColor');
        $textAlign = $this->attr($node, 'textAlign');

        $alignClass = match ($textAlign) {
            'left' => 'text-left items-start',
            'right' => 'text-right items-end',
            default => 'text-center items-center',
        };

        $html = sprintf('<section class="hero-section %s py-20 px-4">', $this->e($bgClass));
        $html .= sprintf('<div class="container mx-auto max-w-4xl flex flex-col %s">', $alignClass);

        if ($subtitle) {
            $html .= sprintf('<p class="text-sm font-semibold text-blue-600 uppercase tracking-wide mb-3">%s</p>', $this->e($subtitle));
        }

        if ($title) {
            $html .= sprintf('<h1 class="text-5xl md:text-6xl font-bold text-gray-900 mb-6">%s</h1>', $this->e($title));
        }

        if ($description) {
            $html .= sprintf('<p class="text-xl text-gray-600 mb-8 max-w-2xl">%s</p>', $this->e($description));
        }

        if ($primaryText || $secondaryText) {
            $html .= '<div class="flex flex-wrap gap-4">';
            if ($primaryText && $primaryUrl) {
                $html .= sprintf(
                    '<a href="%s" class="btn btn-primary px-8 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">%s</a>',
                    $this->e($primaryUrl),
                    $this->e($primaryText)
                );
            }
            if ($secondaryText && $secondaryUrl) {
                $html .= sprintf(
                    '<a href="%s" class="btn btn-secondary px-8 py-3 bg-white text-blue-600 border border-blue-600 rounded-lg font-semibold hover:bg-blue-50 transition">%s</a>',
                    $this->e($secondaryUrl),
                    $this->e($secondaryText)
                );
            }
            $html .= '</div>';
        }

        $html .= '</div></section>';

        return $html;
    }
}
