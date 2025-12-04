<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Tokenizer;

readonly class Token
{
    public function __construct(
        public TokenType $type,
        public string $value,
        public int $line,
        public int $column,
        public int $position,
    ) {}

    public function is(TokenType $type): bool
    {
        return $this->type === $type;
    }

    public function isOneOf(TokenType ...$types): bool
    {
        foreach ($types as $type) {
            if ($this->type === $type) {
                return true;
            }
        }

        return false;
    }

    public function __toString(): string
    {
        return sprintf(
            '%s("%s") at line %d, column %d',
            $this->type->value,
            $this->value,
            $this->line,
            $this->column
        );
    }
}
