<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\Blocks;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\AbstractBlockRenderer;

class HeadingRenderer extends AbstractBlockRenderer
{
    public function getTag(): string
    {
        return 'heading';
    }

    public function getRequiredAttributes(): array
    {
        return ['level'];
    }

    public function getOptionalAttributes(): array
    {
        return [
            'align' => 'left',
            'className' => '',
        ];
    }

    public function render(ShortcodeNode $node, string $innerHtml = ''): string
    {
        $level = $this->attr($node, 'level', '2');
        $align = $this->attr($node, 'align');
        $className = $this->attr($node, 'className');

        $level = max(1, min(6, (int) $level));

        $alignClass = match ($align) {
            'center' => 'text-center',
            'right' => 'text-right',
            default => 'text-left',
        };

        $sizeClass = match ($level) {
            1 => 'text-4xl md:text-5xl font-bold',
            2 => 'text-3xl md:text-4xl font-bold',
            3 => 'text-2xl md:text-3xl font-semibold',
            4 => 'text-xl md:text-2xl font-semibold',
            5 => 'text-lg md:text-xl font-medium',
            6 => 'text-base md:text-lg font-medium',
            default => 'text-3xl font-bold',
        };

        $classes = "{$sizeClass} {$alignClass}";
        if ($className) {
            $classes .= " {$className}";
        }

        return sprintf('<h%d class="%s">%s</h%d>', $level, $this->e($classes), $innerHtml, $level);
    }
}
