<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;

interface BlockRendererInterface
{
    /**
     * Get the block tag this renderer handles
     */
    public function getTag(): string;

    /**
     * Render the shortcode node to HTML
     */
    public function render(ShortcodeNode $node, string $innerHtml = ''): string;

    /**
     * Validate the shortcode attributes
     *
     * @param array<string, string> $attributes
     */
    public function validate(array $attributes): bool;

    /**
     * Get required attributes for this block
     *
     * @return array<string>
     */
    public function getRequiredAttributes(): array;

    /**
     * Get optional attributes with defaults
     *
     * @return array<string, mixed>
     */
    public function getOptionalAttributes(): array;
}
