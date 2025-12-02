<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Tokenizer;

use App\Domains\ContentManagement\Services\ShortcodeParser\Exceptions\TokenizerException;

class Tokenizer
{
    private string $input;

    private int $position = 0;

    private int $line = 1;

    private int $column = 1;

    private int $length;

    /** @var array<Token> */
    private array $tokens = [];

    public function __construct(string $input)
    {
        $this->input = $input;
        $this->length = mb_strlen($input);
    }

    /**
     * Tokenize the input string
     *
     * @return array<Token>
     */
    public function tokenize(): array
    {
        $this->tokens = [];
        $this->position = 0;
        $this->line = 1;
        $this->column = 1;

        while (! $this->isAtEnd()) {
            $this->scanToken();
        }

        $this->addToken(TokenType::EOF, '');

        return $this->tokens;
    }

    private function scanToken(): void
    {
        $char = $this->peek();

        // Check for shortcode syntax
        if ($char === '[') {
            $this->scanShortcode();

            return;
        }

        // Check for content block
        if ($char === '{') {
            $this->addToken(TokenType::CONTENT_OPEN, $this->advance());

            return;
        }

        if ($char === '}') {
            $this->addToken(TokenType::CONTENT_CLOSE, $this->advance());

            return;
        }

        // Check for attribute equals
        if ($char === '=') {
            $this->addToken(TokenType::EQUALS, $this->advance());

            return;
        }

        // Check for whitespace (inside shortcode tags)
        if ($this->isWhitespace($char)) {
            $this->scanWhitespace();

            return;
        }

        // Check for quoted string (attribute value)
        if ($char === '"' || $char === "'") {
            $this->scanQuotedString($char);

            return;
        }

        // Check for attribute name or text
        $this->scanText();
    }

    private function scanShortcode(): void
    {
        // Save position for potential text token
        $startLine = $this->line;
        $startColumn = $this->column;

        // Check for opening bracket
        if ($this->peek() !== '[') {
            return;
        }

        $this->advance(); // consume '['

        // Check for comment: [#!--
        if ($this->peek() === '#' && $this->peekNext() === '!' && $this->peekAhead(2) === '-' && $this->peekAhead(3) === '-') {
            $this->scanComment();

            return;
        }

        // Check for closing tag: [/#tag]
        if ($this->peek() === '/') {
            $this->advance(); // consume '/'
            if ($this->peek() !== '#') {
                throw new TokenizerException('Invalid closing tag syntax', $this->line, $this->column);
            }
            $this->advance(); // consume '#'
            $tag = $this->scanTagName();
            $this->consumeWhitespace();
            if ($this->peek() !== ']') {
                throw new TokenizerException('Expected ] after closing tag', $this->line, $this->column);
            }
            $this->advance(); // consume ']'
            $this->addToken(TokenType::SHORTCODE_CLOSE, $tag, $startLine, $startColumn);

            return;
        }

        // Check for opening tag: [#tag
        if ($this->peek() === '#') {
            $this->advance(); // consume '#'
            $tag = $this->scanTagName();
            $this->addToken(TokenType::SHORTCODE_OPEN, $tag, $startLine, $startColumn);

            // Now scan attributes
            $this->scanAttributes();

            // Check for self-closing
            $this->consumeWhitespace();
            if ($this->peek() === '/' && $this->peekNext() === ']') {
                $this->advance(); // consume '/'
                $this->advance(); // consume ']'
                $this->addToken(TokenType::SHORTCODE_SELF_CLOSE, '/]');

                return;
            }

            // Regular closing bracket
            if ($this->peek() === ']') {
                $this->advance(); // consume ']'

                return;
            }

            throw new TokenizerException('Expected ] or /] after shortcode tag', $this->line, $this->column);
        }

        // Not a shortcode, treat as text
        $this->position--; // backtrack
        $this->column--;
    }

    private function scanComment(): void
    {
        $startLine = $this->line;
        $startColumn = $this->column;

        // consume '#!--'
        $this->advance();
        $this->advance();
        $this->advance();
        $this->advance();

        $comment = '';

        // Scan until --]
        while (! $this->isAtEnd()) {
            if ($this->peek() === '-' && $this->peekNext() === '-' && $this->peekAhead(2) === ']') {
                $this->advance(); // -
                $this->advance(); // -
                $this->advance(); // ]
                break;
            }

            $comment .= $this->advance();
        }

        $this->addToken(TokenType::COMMENT, trim($comment), $startLine, $startColumn);
    }

    private function scanTagName(): string
    {
        $tag = '';

        while (! $this->isAtEnd() && $this->isTagNameChar($this->peek())) {
            $tag .= $this->advance();
        }

        if ($tag === '') {
            throw new TokenizerException('Expected tag name after #', $this->line, $this->column);
        }

        return $tag;
    }

    private function scanAttributes(): void
    {
        while (! $this->isAtEnd()) {
            $this->consumeWhitespace();

            // Check for end of tag
            if ($this->peek() === ']' || ($this->peek() === '/' && $this->peekNext() === ']')) {
                break;
            }

            // Scan attribute name
            $name = $this->scanAttributeName();
            if ($name === '') {
                break;
            }

            $this->addToken(TokenType::ATTRIBUTE_NAME, $name);

            $this->consumeWhitespace();

            // Expect equals sign
            if ($this->peek() !== '=') {
                throw new TokenizerException("Expected = after attribute name '{$name}'", $this->line, $this->column);
            }

            $this->addToken(TokenType::EQUALS, $this->advance());

            $this->consumeWhitespace();

            // Scan attribute value
            $quote = $this->peek();
            if ($quote !== '"' && $quote !== "'") {
                throw new TokenizerException('Expected quoted string for attribute value', $this->line, $this->column);
            }

            $value = $this->scanQuotedString($quote);
            $this->addToken(TokenType::ATTRIBUTE_VALUE, $value);
        }
    }

    private function scanAttributeName(): string
    {
        $name = '';

        while (! $this->isAtEnd() && $this->isAttributeNameChar($this->peek())) {
            $name .= $this->advance();
        }

        return $name;
    }

    private function scanQuotedString(string $quote): string
    {
        $this->advance(); // consume opening quote

        $value = '';

        while (! $this->isAtEnd() && $this->peek() !== $quote) {
            // Handle escape sequences
            if ($this->peek() === '\\') {
                $this->advance(); // consume backslash
                if (! $this->isAtEnd()) {
                    $value .= $this->advance(); // add escaped character
                }
            } else {
                $value .= $this->advance();
            }
        }

        if ($this->isAtEnd()) {
            throw new TokenizerException("Unterminated string (expected {$quote})", $this->line, $this->column);
        }

        $this->advance(); // consume closing quote

        return $value;
    }

    private function scanWhitespace(): void
    {
        $whitespace = '';

        while (! $this->isAtEnd() && $this->isWhitespace($this->peek())) {
            $whitespace .= $this->advance();
        }

        $this->addToken(TokenType::WHITESPACE, $whitespace);
    }

    private function scanText(): void
    {
        $text = '';
        $startLine = $this->line;
        $startColumn = $this->column;

        while (! $this->isAtEnd()) {
            $char = $this->peek();

            // Stop at shortcode syntax
            if ($char === '[' && ($this->peekNext() === '#' || $this->peekNext() === '/')) {
                break;
            }

            // Stop at content delimiters
            if ($char === '{' || $char === '}') {
                break;
            }

            // Handle escaped characters
            if ($char === '\\') {
                $this->advance(); // consume backslash
                if (! $this->isAtEnd()) {
                    $text .= $this->advance(); // add escaped character literally
                }
            } else {
                $text .= $this->advance();
            }
        }

        if ($text !== '') {
            // Determine if it's markdown or plain text
            // For now, we'll mark everything as TEXT and let the parser handle markdown detection
            $this->addToken(TokenType::TEXT, $text, $startLine, $startColumn);
        }
    }

    private function consumeWhitespace(): void
    {
        while (! $this->isAtEnd() && $this->isWhitespace($this->peek())) {
            $this->advance();
        }
    }

    private function isWhitespace(string $char): bool
    {
        return $char === ' ' || $char === "\t" || $char === "\n" || $char === "\r";
    }

    private function isTagNameChar(string $char): bool
    {
        return ctype_alnum($char) || $char === '-' || $char === '_';
    }

    private function isAttributeNameChar(string $char): bool
    {
        return ctype_alnum($char) || $char === '-' || $char === '_' || $char === ':';
    }

    private function peek(): string
    {
        if ($this->isAtEnd()) {
            return "\0";
        }

        return mb_substr($this->input, $this->position, 1);
    }

    private function peekNext(): string
    {
        if ($this->position + 1 >= $this->length) {
            return "\0";
        }

        return mb_substr($this->input, $this->position + 1, 1);
    }

    private function peekAhead(int $offset): string
    {
        if ($this->position + $offset >= $this->length) {
            return "\0";
        }

        return mb_substr($this->input, $this->position + $offset, 1);
    }

    private function advance(): string
    {
        $char = mb_substr($this->input, $this->position, 1);
        $this->position++;

        if ($char === "\n") {
            $this->line++;
            $this->column = 1;
        } else {
            $this->column++;
        }

        return $char;
    }

    private function isAtEnd(): bool
    {
        return $this->position >= $this->length;
    }

    private function addToken(TokenType $type, string $value, ?int $line = null, ?int $column = null): void
    {
        $this->tokens[] = new Token(
            $type,
            $value,
            $line ?? $this->line,
            $column ?? $this->column,
            $this->position
        );
    }
}
