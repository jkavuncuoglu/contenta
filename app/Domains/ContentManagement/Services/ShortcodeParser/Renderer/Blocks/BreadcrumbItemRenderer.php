<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\Blocks;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\AbstractBlockRenderer;

class BreadcrumbItemRenderer extends AbstractBlockRenderer
{
    public function getTag(): string
    {
        return 'breadcrumb-item';
    }

    public function getRequiredAttributes(): array
    {
        return ['label'];
    }

    public function getOptionalAttributes(): array
    {
        return [
            'url' => '',
        ];
    }

    public function render(ShortcodeNode $node, string $innerHtml = ''): string
    {
        $label = $this->attr($node, 'label');
        $url = $this->attr($node, 'url');

        $html = '<li class="breadcrumb-item flex items-center">';

        if ($url) {
            $html .= sprintf(
                '<a href="%s" class="hover:text-blue-600 transition">%s</a>',
                $this->e($url),
                $this->e($label)
            );
        } else {
            $html .= sprintf('<span class="font-medium text-gray-900">%s</span>', $this->e($label));
        }

        $html .= '<span class="mx-2 text-gray-400">/</span>';
        $html .= '</li>';

        return $html;
    }
}
