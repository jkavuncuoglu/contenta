<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Parser;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\CommentNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\AST\DocumentNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\AST\MarkdownNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\AST\Node;
use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\AST\TextNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Exceptions\ParseException;
use App\Domains\ContentManagement\Services\ShortcodeParser\Tokenizer\Token;
use App\Domains\ContentManagement\Services\ShortcodeParser\Tokenizer\TokenType;

class Parser
{
    /** @var array<Token> */
    private array $tokens;

    private int $current = 0;

    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
    }

    public function parse(): DocumentNode
    {
        $document = new DocumentNode;

        while (! $this->isAtEnd()) {
            $node = $this->parseNode();
            if ($node !== null) {
                $document->addChild($node);
            }
        }

        return $document;
    }

    private function parseNode(): ?Node
    {
        $token = $this->peek();

        // Skip whitespace at document level
        if ($token->is(TokenType::WHITESPACE)) {
            $this->advance();

            return null;
        }

        // Skip comments (or optionally return CommentNode)
        if ($token->is(TokenType::COMMENT)) {
            $commentToken = $this->advance();

            return new CommentNode($commentToken->value, $commentToken->line, $commentToken->column);
        }

        // Parse shortcode
        if ($token->is(TokenType::SHORTCODE_OPEN)) {
            return $this->parseShortcode();
        }

        // Parse text/markdown
        if ($token->is(TokenType::TEXT)) {
            return $this->parseText();
        }

        // Parse content block (outside shortcode context)
        if ($token->is(TokenType::CONTENT_OPEN)) {
            return $this->parseContentBlock();
        }

        // Unexpected token
        throw new ParseException(
            sprintf('Unexpected token: %s', $token->type->value),
            $token->line,
            $token->column
        );
    }

    private function parseShortcode(): ShortcodeNode
    {
        $openToken = $this->advance(); // consume SHORTCODE_OPEN
        $tag = $openToken->value;

        // Parse attributes
        $attributes = $this->parseAttributes();

        // Check for self-closing
        if ($this->match(TokenType::SHORTCODE_SELF_CLOSE)) {
            return new ShortcodeNode(
                $tag,
                $attributes,
                true,
                $openToken->line,
                $openToken->column
            );
        }

        // Parse shortcode content (children)
        $shortcodeNode = new ShortcodeNode(
            $tag,
            $attributes,
            false,
            $openToken->line,
            $openToken->column
        );

        // Parse children until we hit the closing tag
        while (! $this->isAtEnd()) {
            // Check for closing tag
            if ($this->check(TokenType::SHORTCODE_CLOSE)) {
                $closeToken = $this->advance();
                if ($closeToken->value !== $tag) {
                    throw new ParseException(
                        sprintf(
                            'Mismatched closing tag: expected [/#%s], got [/#%s]',
                            $tag,
                            $closeToken->value
                        ),
                        $closeToken->line,
                        $closeToken->column
                    );
                }

                return $shortcodeNode;
            }

            // Skip whitespace inside shortcode
            if ($this->match(TokenType::WHITESPACE)) {
                continue;
            }

            // Parse content block: {content}
            if ($this->check(TokenType::CONTENT_OPEN)) {
                $this->advance(); // consume CONTENT_OPEN {

                // Parse children inside content block until we hit CONTENT_CLOSE
                while (! $this->isAtEnd() && ! $this->check(TokenType::CONTENT_CLOSE)) {
                    // Skip whitespace
                    if ($this->match(TokenType::WHITESPACE)) {
                        continue;
                    }

                    // Parse nested shortcode
                    if ($this->check(TokenType::SHORTCODE_OPEN)) {
                        $shortcodeNode->addChild($this->parseShortcode());

                        continue;
                    }

                    // Parse text/markdown
                    if ($this->check(TokenType::TEXT)) {
                        $shortcodeNode->addChild($this->parseText());

                        continue;
                    }

                    // Parse comment
                    if ($this->check(TokenType::COMMENT)) {
                        $commentToken = $this->advance();
                        $shortcodeNode->addChild(
                            new CommentNode($commentToken->value, $commentToken->line, $commentToken->column)
                        );

                        continue;
                    }

                    // Unexpected token
                    $token = $this->peek();
                    throw new ParseException(
                        sprintf('Unexpected token inside content block: %s', $token->type->value),
                        $token->line,
                        $token->column
                    );
                }

                // Consume CONTENT_CLOSE }
                if (! $this->match(TokenType::CONTENT_CLOSE)) {
                    throw new ParseException(
                        'Unclosed content block (expected })',
                        $this->peek()->line,
                        $this->peek()->column
                    );
                }

                continue;
            }

            // Parse nested shortcode
            if ($this->check(TokenType::SHORTCODE_OPEN)) {
                $shortcodeNode->addChild($this->parseShortcode());

                continue;
            }

            // Parse text
            if ($this->check(TokenType::TEXT)) {
                $shortcodeNode->addChild($this->parseText());

                continue;
            }

            // Parse comment
            if ($this->check(TokenType::COMMENT)) {
                $commentToken = $this->advance();
                $shortcodeNode->addChild(
                    new CommentNode($commentToken->value, $commentToken->line, $commentToken->column)
                );

                continue;
            }

            // Unexpected token
            $token = $this->peek();
            throw new ParseException(
                sprintf('Unexpected token inside [#%s]: %s', $tag, $token->type->value),
                $token->line,
                $token->column
            );
        }

        throw new ParseException(
            sprintf('Unclosed shortcode tag: [#%s]', $tag),
            $openToken->line,
            $openToken->column
        );
    }

    /**
     * @return array<string, string>
     */
    private function parseAttributes(): array
    {
        $attributes = [];

        while (! $this->isAtEnd()) {
            // Skip whitespace
            if ($this->match(TokenType::WHITESPACE)) {
                continue;
            }

            // Check for end of opening tag
            if ($this->check(TokenType::SHORTCODE_SELF_CLOSE) ||
                $this->check(TokenType::CONTENT_OPEN) ||
                $this->check(TokenType::SHORTCODE_CLOSE) ||
                $this->check(TokenType::TEXT) ||
                $this->check(TokenType::SHORTCODE_OPEN)) {
                break;
            }

            // Parse attribute name
            if (! $this->check(TokenType::ATTRIBUTE_NAME)) {
                break;
            }

            $nameToken = $this->advance();
            $name = $nameToken->value;

            // Skip whitespace
            while ($this->match(TokenType::WHITESPACE)) {
                //
            }

            // Expect equals
            if (! $this->match(TokenType::EQUALS)) {
                throw new ParseException(
                    sprintf('Expected = after attribute name "%s"', $name),
                    $this->peek()->line,
                    $this->peek()->column
                );
            }

            // Skip whitespace
            while ($this->match(TokenType::WHITESPACE)) {
                //
            }

            // Expect value
            if (! $this->check(TokenType::ATTRIBUTE_VALUE)) {
                throw new ParseException(
                    sprintf('Expected value for attribute "%s"', $name),
                    $this->peek()->line,
                    $this->peek()->column
                );
            }

            $valueToken = $this->advance();
            $attributes[$name] = $valueToken->value;
        }

        return $attributes;
    }

    private function parseContentBlock(): ?Node
    {
        if (! $this->match(TokenType::CONTENT_OPEN)) {
            return null;
        }

        $content = '';
        $startToken = $this->previous();

        // Collect all content until closing brace
        while (! $this->isAtEnd() && ! $this->check(TokenType::CONTENT_CLOSE)) {
            $token = $this->advance();

            // Allow nested shortcodes inside content blocks
            if ($token->is(TokenType::TEXT) || $token->is(TokenType::WHITESPACE)) {
                $content .= $token->value;
            } elseif ($token->is(TokenType::SHORTCODE_OPEN)) {
                // Backtrack and parse as shortcode
                $this->current--;
                $node = $this->parseShortcode();
                // For content blocks, we'll convert shortcode to its raw representation
                // This is a simplified approach - in reality, we might want to preserve the node
                $content .= $this->nodeToString($node);
            }
        }

        if (! $this->match(TokenType::CONTENT_CLOSE)) {
            throw new ParseException(
                'Unclosed content block (expected })',
                $startToken->line,
                $startToken->column
            );
        }

        // Determine if content is markdown or plain text
        if ($this->looksLikeMarkdown(trim($content))) {
            return new MarkdownNode(trim($content), $startToken->line, $startToken->column);
        }

        return new TextNode(trim($content), $startToken->line, $startToken->column);
    }

    private function parseText(): TextNode|MarkdownNode
    {
        $token = $this->advance();

        // Check if this text looks like markdown
        if ($this->looksLikeMarkdown($token->value)) {
            return new MarkdownNode($token->value, $token->line, $token->column);
        }

        return new TextNode($token->value, $token->line, $token->column);
    }

    private function looksLikeMarkdown(string $text): bool
    {
        // Simple heuristic to detect markdown
        $patterns = [
            '/^#{1,6}\s/',        // Headers
            '/\*\*[^*]+\*\*/',    // Bold
            '/\*[^*]+\*/',        // Italic
            '/^-\s/',             // Unordered list
            '/^\d+\.\s/',         // Ordered list
            '/\[.+\]\(.+\)/',     // Links
            '/^>/',               // Blockquotes
            '/`[^`]+`/',          // Inline code
            '/^```/',             // Code blocks
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text)) {
                return true;
            }
        }

        return false;
    }

    private function nodeToString(Node $node): string
    {
        if ($node instanceof ShortcodeNode) {
            $attrs = '';
            foreach ($node->attributes as $key => $value) {
                $attrs .= sprintf(' %s="%s"', $key, $value);
            }

            if ($node->selfClosing) {
                return sprintf('[#%s%s /]', $node->tag, $attrs);
            }

            $children = '';
            foreach ($node->getChildren() as $child) {
                $children .= $this->nodeToString($child);
            }

            return sprintf('[#%s%s]%s[/#%s]', $node->tag, $attrs, $children, $node->tag);
        }

        if ($node instanceof TextNode || $node instanceof MarkdownNode) {
            return $node->content;
        }

        return '';
    }

    private function match(TokenType ...$types): bool
    {
        foreach ($types as $type) {
            if ($this->check($type)) {
                $this->advance();

                return true;
            }
        }

        return false;
    }

    private function check(TokenType $type): bool
    {
        if ($this->isAtEnd()) {
            return false;
        }

        return $this->peek()->is($type);
    }

    private function advance(): Token
    {
        if (! $this->isAtEnd()) {
            $this->current++;
        }

        return $this->previous();
    }

    private function isAtEnd(): bool
    {
        return $this->peek()->is(TokenType::EOF);
    }

    private function peek(): Token
    {
        return $this->tokens[$this->current];
    }

    private function previous(): Token
    {
        return $this->tokens[$this->current - 1];
    }
}
