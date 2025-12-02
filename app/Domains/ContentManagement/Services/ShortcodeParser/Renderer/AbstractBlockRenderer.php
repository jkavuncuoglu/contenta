<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Exceptions\RenderException;

abstract class AbstractBlockRenderer implements BlockRendererInterface
{
    abstract public function getTag(): string;

    abstract public function render(ShortcodeNode $node, string $innerHtml = ''): string;

    public function validate(array $attributes): bool
    {
        foreach ($this->getRequiredAttributes() as $required) {
            if (! isset($attributes[$required])) {
                throw new RenderException(
                    sprintf('Required attribute "%s" missing for [#%s] shortcode', $required, $this->getTag())
                );
            }
        }

        return true;
    }

    public function getRequiredAttributes(): array
    {
        return [];
    }

    public function getOptionalAttributes(): array
    {
        return [];
    }

    /**
     * Merge attributes with defaults
     *
     * @param array<string, string> $attributes
     * @return array<string, mixed>
     */
    protected function mergeAttributes(array $attributes): array
    {
        return array_merge($this->getOptionalAttributes(), $attributes);
    }

    /**
     * Get attribute value with default
     */
    protected function attr(ShortcodeNode $node, string $name, mixed $default = null): mixed
    {
        return $node->getAttribute($name) ?? $this->getOptionalAttributes()[$name] ?? $default;
    }

    /**
     * Escape HTML
     */
    protected function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Build HTML tag
     *
     * @param array<string, string> $attributes
     */
    protected function tag(string $tagName, array $attributes = [], ?string $content = null): string
    {
        $attrs = '';
        foreach ($attributes as $key => $value) {
            if ($value !== null && $value !== '') {
                $attrs .= sprintf(' %s="%s"', $key, $this->e((string) $value));
            }
        }

        if ($content === null) {
            return sprintf('<%s%s />', $tagName, $attrs);
        }

        return sprintf('<%s%s>%s</%s>', $tagName, $attrs, $content, $tagName);
    }
}
