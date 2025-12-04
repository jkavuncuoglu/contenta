<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\Blocks;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\AbstractBlockRenderer;

class LegalTextRenderer extends AbstractBlockRenderer
{
    public function getTag(): string
    {
        return 'legal-text';
    }

    public function getRequiredAttributes(): array
    {
        return ['documentType'];
    }

    public function getOptionalAttributes(): array
    {
        return [
            'effectiveDate' => '',
            'lastUpdated' => '',
            'jurisdiction' => '',
            'version' => '',
        ];
    }

    public function render(ShortcodeNode $node, string $innerHtml = ''): string
    {
        $documentType = $this->attr($node, 'documentType');
        $effectiveDate = $this->attr($node, 'effectiveDate');
        $lastUpdated = $this->attr($node, 'lastUpdated');
        $jurisdiction = $this->attr($node, 'jurisdiction');
        $version = $this->attr($node, 'version');

        $html = '<article class="legal-document max-w-4xl mx-auto py-12 px-4">';

        $html .= '<header class="mb-8">';
        $html .= sprintf('<h1 class="text-4xl font-bold text-gray-900 mb-4">%s</h1>', $this->e($documentType));

        $html .= '<div class="text-sm text-gray-600 space-y-1">';
        if ($effectiveDate) {
            $html .= sprintf('<p><strong>Effective Date:</strong> %s</p>', $this->e($effectiveDate));
        }
        if ($lastUpdated) {
            $html .= sprintf('<p><strong>Last Updated:</strong> %s</p>', $this->e($lastUpdated));
        }
        if ($jurisdiction) {
            $html .= sprintf('<p><strong>Jurisdiction:</strong> %s</p>', $this->e($jurisdiction));
        }
        if ($version) {
            $html .= sprintf('<p><strong>Version:</strong> %s</p>', $this->e($version));
        }
        $html .= '</div>';
        $html .= '</header>';

        $html .= sprintf('<div class="legal-content prose prose-gray max-w-none">%s</div>', $innerHtml);

        $html .= '</article>';

        return $html;
    }
}
