<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\DocumentNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Exceptions\ParseException;
use App\Domains\ContentManagement\Services\ShortcodeParser\Parser\Parser;
use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\HtmlRenderer;
use App\Domains\ContentManagement\Services\ShortcodeParser\Tokenizer\Tokenizer;

class ShortcodeParserService implements ShortcodeParserServiceContract
{
    private HtmlRenderer $renderer;

    public function __construct()
    {
        $this->renderer = new HtmlRenderer;
    }

    /**
     * Parse markdown with shortcodes to AST
     */
    public function parse(string $content): DocumentNode
    {
        // Extract front matter first
        $extracted = $this->extractFrontMatter($content);
        $content = $extracted['content'];
        $metadata = $extracted['metadata'];

        // Tokenize
        $tokenizer = new Tokenizer($content);
        $tokens = $tokenizer->tokenize();

        // Parse to AST
        $parser = new Parser($tokens);
        $document = $parser->parse();

        // Attach metadata to document
        $document->setMetadata($metadata);

        return $document;
    }

    /**
     * Render AST to HTML
     */
    public function render(DocumentNode $document): string
    {
        return $this->renderer->render($document);
    }

    /**
     * Parse and render in one step
     */
    public function parseAndRender(string $content): string
    {
        $document = $this->parse($content);

        return $this->render($document);
    }

    /**
     * Validate syntax without rendering
     */
    public function validate(string $content): bool
    {
        try {
            $this->parse($content);

            return true;
        } catch (ParseException $e) {
            return false;
        }
    }

    /**
     * Extract front matter from markdown
     *
     * Front matter format:
     * ---
     * key: value
     * another: value
     * ---
     *
     * Content here...
     *
     * @return array{content: string, metadata: array<string, mixed>}
     */
    public function extractFrontMatter(string $content): array
    {
        $metadata = [];

        // Check for YAML front matter
        if (preg_match('/^---\s*\n(.*?)\n---\s*\n(.*)$/s', $content, $matches)) {
            $frontMatter = $matches[1];
            $content = $matches[2];

            // Parse YAML front matter (simple key: value pairs)
            $lines = explode("\n", $frontMatter);
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || ! str_contains($line, ':')) {
                    continue;
                }

                [$key, $value] = explode(':', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Remove quotes from value if present
                if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                    (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                    $value = substr($value, 1, -1);
                }

                $metadata[$key] = $value;
            }
        }

        return [
            'content' => $content,
            'metadata' => $metadata,
        ];
    }

    /**
     * Get the HTML renderer instance
     */
    public function getRenderer(): HtmlRenderer
    {
        return $this->renderer;
    }
}
