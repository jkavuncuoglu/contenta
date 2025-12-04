<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\Blocks;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\AbstractBlockRenderer;

class MemberRenderer extends AbstractBlockRenderer
{
    public function getTag(): string
    {
        return 'member';
    }

    public function getRequiredAttributes(): array
    {
        return ['name', 'role'];
    }

    public function getOptionalAttributes(): array
    {
        return [
            'image' => '',
            'bio' => '',
            'linkedin' => '',
            'twitter' => '',
            'email' => '',
        ];
    }

    public function render(ShortcodeNode $node, string $innerHtml = ''): string
    {
        $name = $this->attr($node, 'name');
        $role = $this->attr($node, 'role');
        $image = $this->attr($node, 'image');
        $bio = $this->attr($node, 'bio');
        $linkedin = $this->attr($node, 'linkedin');
        $twitter = $this->attr($node, 'twitter');
        $email = $this->attr($node, 'email');

        $html = '<div class="team-member text-center">';

        if ($image) {
            $html .= sprintf(
                '<img src="%s" alt="%s" class="w-32 h-32 rounded-full mx-auto mb-4 object-cover" />',
                $this->e($image),
                $this->e($name)
            );
        }

        $html .= sprintf('<h3 class="text-xl font-semibold text-gray-900">%s</h3>', $this->e($name));
        $html .= sprintf('<p class="text-sm text-blue-600 font-medium mb-3">%s</p>', $this->e($role));

        if ($bio) {
            $html .= sprintf('<p class="text-gray-600 text-sm mb-4">%s</p>', $this->e($bio));
        }

        if ($linkedin || $twitter || $email) {
            $html .= '<div class="social-links flex justify-center gap-3">';
            if ($linkedin) {
                $html .= sprintf('<a href="%s" class="text-gray-600 hover:text-blue-600" target="_blank">LinkedIn</a>', $this->e($linkedin));
            }
            if ($twitter) {
                $html .= sprintf('<a href="https://twitter.com/%s" class="text-gray-600 hover:text-blue-400" target="_blank">Twitter</a>', $this->e($twitter));
            }
            if ($email) {
                $html .= sprintf('<a href="mailto:%s" class="text-gray-600 hover:text-blue-600">Email</a>', $this->e($email));
            }
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }
}
