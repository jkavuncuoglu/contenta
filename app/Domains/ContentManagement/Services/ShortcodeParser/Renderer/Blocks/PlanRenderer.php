<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\Blocks;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\AbstractBlockRenderer;

class PlanRenderer extends AbstractBlockRenderer
{
    public function getTag(): string
    {
        return 'plan';
    }

    public function getRequiredAttributes(): array
    {
        return ['name', 'price'];
    }

    public function getOptionalAttributes(): array
    {
        return [
            'period' => 'month',
            'description' => '',
            'highlighted' => 'false',
            'buttonText' => 'Get Started',
            'buttonUrl' => '#',
        ];
    }

    public function render(ShortcodeNode $node, string $innerHtml = ''): string
    {
        $name = $this->attr($node, 'name');
        $price = $this->attr($node, 'price');
        $period = $this->attr($node, 'period');
        $description = $this->attr($node, 'description');
        $highlighted = $this->attr($node, 'highlighted') === 'true';
        $buttonText = $this->attr($node, 'buttonText');
        $buttonUrl = $this->attr($node, 'buttonUrl');

        $highlightClass = $highlighted ? 'border-blue-600 shadow-xl scale-105' : 'border-gray-200';

        $html = sprintf('<div class="pricing-plan border-2 %s rounded-lg p-8 bg-white">', $highlightClass);

        if ($highlighted) {
            $html .= '<div class="text-xs font-semibold text-blue-600 uppercase tracking-wide mb-4">Most Popular</div>';
        }

        $html .= sprintf('<h3 class="text-2xl font-bold text-gray-900 mb-2">%s</h3>', $this->e($name));

        if ($description) {
            $html .= sprintf('<p class="text-sm text-gray-600 mb-6">%s</p>', $this->e($description));
        }

        $html .= '<div class="price mb-6">';
        $html .= sprintf('<span class="text-4xl font-bold text-gray-900">%s</span>', $this->e($price));
        if ($period) {
            $html .= sprintf('<span class="text-gray-600">/%s</span>', $this->e($period));
        }
        $html .= '</div>';

        $html .= sprintf('<div class="features text-sm text-gray-600 mb-8">%s</div>', $innerHtml);

        $buttonClass = $highlighted ? 'bg-blue-600 text-white hover:bg-blue-700' : 'bg-gray-100 text-gray-900 hover:bg-gray-200';
        $html .= sprintf(
            '<a href="%s" class="btn block w-full text-center px-6 py-3 rounded-lg font-semibold transition %s">%s</a>',
            $this->e($buttonUrl),
            $buttonClass,
            $this->e($buttonText)
        );

        $html .= '</div>';

        return $html;
    }
}
