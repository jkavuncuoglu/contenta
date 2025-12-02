<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\AST;

class TextNode extends Node
{
    public function __construct(
        public readonly string $content,
        ?int $line = null,
        ?int $column = null,
    ) {
        parent::__construct($line, $column);
    }

    public function getType(): string
    {
        return 'text';
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'content' => $this->content,
        ]);
    }
}
