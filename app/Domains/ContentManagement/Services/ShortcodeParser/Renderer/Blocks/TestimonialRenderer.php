<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\Blocks;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\AbstractBlockRenderer;

class TestimonialRenderer extends AbstractBlockRenderer
{
    public function getTag(): string
    {
        return 'testimonial';
    }

    public function getRequiredAttributes(): array
    {
        return ['author'];
    }

    public function getOptionalAttributes(): array
    {
        return [
            'role' => '',
            'image' => '',
            'rating' => '',
        ];
    }

    public function render(ShortcodeNode $node, string $innerHtml = ''): string
    {
        $author = $this->attr($node, 'author');
        $role = $this->attr($node, 'role');
        $image = $this->attr($node, 'image');
        $rating = $this->attr($node, 'rating');

        $html = '<div class="testimonial bg-white p-6 rounded-lg shadow-md">';

        if ($rating) {
            $stars = str_repeat('⭐', min(5, (int) $rating));
            $html .= sprintf('<div class="testimonial-rating text-yellow-500 mb-4">%s</div>', $stars);
        }

        $html .= sprintf('<div class="testimonial-content text-gray-700 mb-6 italic">"%s"</div>', $innerHtml);

        $html .= '<div class="testimonial-author flex items-center">';
        if ($image) {
            $html .= sprintf(
                '<img src="%s" alt="%s" class="w-12 h-12 rounded-full mr-4 object-cover" />',
                $this->e($image),
                $this->e($author)
            );
        }
        $html .= '<div>';
        $html .= sprintf('<div class="font-semibold text-gray-900">%s</div>', $this->e($author));
        if ($role) {
            $html .= sprintf('<div class="text-sm text-gray-600">%s</div>', $this->e($role));
        }
        $html .= '</div>';
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }
}
