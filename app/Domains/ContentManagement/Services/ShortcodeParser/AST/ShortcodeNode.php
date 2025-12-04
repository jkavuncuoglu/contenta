<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\AST;

class ShortcodeNode extends Node
{
    /**
     * @param  array<string, string>  $attributes
     */
    public function __construct(
        public readonly string $tag,
        public readonly array $attributes = [],
        public readonly bool $selfClosing = false,
        ?int $line = null,
        ?int $column = null,
    ) {
        parent::__construct($line, $column);
    }

    public function getType(): string
    {
        return 'shortcode';
    }

    public function getAttribute(string $name, ?string $default = null): ?string
    {
        return $this->attributes[$name] ?? $default;
    }

    public function hasAttribute(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'tag' => $this->tag,
            'attributes' => $this->attributes,
            'selfClosing' => $this->selfClosing,
        ]);
    }
}
