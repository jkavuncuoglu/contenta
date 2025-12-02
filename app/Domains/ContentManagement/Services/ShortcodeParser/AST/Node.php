<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\AST;

abstract class Node
{
    /** @var array<Node> */
    protected array $children = [];

    public function __construct(
        public readonly ?int $line = null,
        public readonly ?int $column = null,
    ) {}

    abstract public function getType(): string;

    public function addChild(Node $node): void
    {
        $this->children[] = $node;
    }

    /**
     * @return array<Node>
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    public function hasChildren(): bool
    {
        return count($this->children) > 0;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->getType(),
            'line' => $this->line,
            'column' => $this->column,
            'children' => array_map(fn (Node $child) => $child->toArray(), $this->children),
        ];
    }
}
