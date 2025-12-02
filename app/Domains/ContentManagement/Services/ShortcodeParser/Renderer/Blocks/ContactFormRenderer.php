<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\Blocks;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\AbstractBlockRenderer;

class ContactFormRenderer extends AbstractBlockRenderer
{
    public function getTag(): string
    {
        return 'contact-form';
    }

    public function getOptionalAttributes(): array
    {
        return [
            'title' => 'Get in Touch',
            'submitText' => 'Send Message',
            'successMessage' => 'Thank you! We\'ll be in touch soon.',
            'recipientEmail' => '',
            'fields' => 'name,email,phone,message',
        ];
    }

    public function render(ShortcodeNode $node, string $innerHtml = ''): string
    {
        $title = $this->attr($node, 'title');
        $submitText = $this->attr($node, 'submitText');

        $html = '<section class="contact-form-section py-16 px-4">';
        $html .= '<div class="container mx-auto max-w-2xl">';

        if ($title) {
            $html .= sprintf('<h2 class="text-3xl font-bold text-center mb-8">%s</h2>', $this->e($title));
        }

        $html .= '<form class="contact-form space-y-6" method="POST" action="/contact">';
        $html .= '<div>';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-2">Name</label>';
        $html .= '<input type="text" name="name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent" />';
        $html .= '</div>';

        $html .= '<div>';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-2">Email</label>';
        $html .= '<input type="email" name="email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent" />';
        $html .= '</div>';

        $html .= '<div>';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>';
        $html .= '<input type="tel" name="phone" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent" />';
        $html .= '</div>';

        $html .= '<div>';
        $html .= '<label class="block text-sm font-medium text-gray-700 mb-2">Message</label>';
        $html .= '<textarea name="message" rows="5" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent"></textarea>';
        $html .= '</div>';

        $html .= sprintf(
            '<button type="submit" class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">%s</button>',
            $this->e($submitText)
        );

        $html .= '</form>';
        $html .= '</div></section>';

        return $html;
    }
}
