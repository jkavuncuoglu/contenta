<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\Blocks;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\AbstractBlockRenderer;

class NewsletterRenderer extends AbstractBlockRenderer
{
    public function getTag(): string
    {
        return 'newsletter';
    }

    public function getOptionalAttributes(): array
    {
        return [
            'title' => 'Subscribe to Our Newsletter',
            'description' => 'Get the latest updates delivered to your inbox',
            'placeholder' => 'Enter your email',
            'buttonText' => 'Subscribe',
            'provider' => '',
            'listId' => '',
        ];
    }

    public function render(ShortcodeNode $node, string $innerHtml = ''): string
    {
        $title = $this->attr($node, 'title');
        $description = $this->attr($node, 'description');
        $placeholder = $this->attr($node, 'placeholder');
        $buttonText = $this->attr($node, 'buttonText');

        $html = '<section class="newsletter-section bg-blue-50 py-12 px-4">';
        $html .= '<div class="container mx-auto max-w-2xl text-center">';

        if ($title) {
            $html .= sprintf('<h3 class="text-2xl font-bold text-gray-900 mb-2">%s</h3>', $this->e($title));
        }

        if ($description) {
            $html .= sprintf('<p class="text-gray-600 mb-6">%s</p>', $this->e($description));
        }

        $html .= '<form class="newsletter-form flex flex-col sm:flex-row gap-3 max-w-lg mx-auto" method="POST" action="/newsletter/subscribe">';
        $html .= sprintf(
            '<input type="email" name="email" required placeholder="%s" class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent" />',
            $this->e($placeholder)
        );
        $html .= sprintf(
            '<button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">%s</button>',
            $this->e($buttonText)
        );
        $html .= '</form>';

        $html .= '</div></section>';

        return $html;
    }
}
