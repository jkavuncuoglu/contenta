<?php

declare(strict_types=1);

namespace App\Domains\ContentStorage\Models;

use App\Domains\ContentStorage\Exceptions\ReadException;
use DateTimeImmutable;

/**
 * Content Data Value Object
 *
 * Immutable value object representing content with frontmatter metadata.
 * Used by all storage repositories for consistent content handling.
 */
class ContentData
{
    /**
     * Create a new content data instance
     *
     * @param string $content Raw markdown content (without frontmatter)
     * @param array<string, mixed> $frontmatter Parsed YAML frontmatter
     * @param string|null $hash SHA-256 hash for change detection
     * @param int|null $size Content size in bytes
     * @param DateTimeImmutable|null $modifiedAt Last modification timestamp
     */
    public function __construct(
        public readonly string $content,
        public readonly array $frontmatter = [],
        public readonly ?string $hash = null,
        public readonly ?int $size = null,
        public readonly ?DateTimeImmutable $modifiedAt = null
    ) {
    }

    /**
     * Create ContentData from markdown string with frontmatter
     *
     * Parses YAML frontmatter from markdown content:
     * ---
     * title: "Page Title"
     * slug: "page-slug"
     * ---
     * # Content here...
     *
     * @param string $markdown Full markdown with frontmatter
     * @return self
     * @throws ReadException If frontmatter parsing fails
     */
    public static function fromMarkdown(string $markdown): self
    {
        $frontmatter = [];
        $content = $markdown;

        // Check for YAML frontmatter delimited by ---
        if (preg_match('/^---\s*\n(.*?)\n---\s*\n(.*)$/s', $markdown, $matches)) {
            $yamlContent = trim($matches[1]);
            $content = $matches[2];

            // Only parse if there's actual content between the delimiters
            if (! empty($yamlContent)) {
                try {
                    $frontmatter = self::parseYaml($yamlContent);
                } catch (\Exception $e) {
                    throw ReadException::invalidFrontmatter('markdown', $e->getMessage());
                }
            }
        }

        // Calculate content hash
        $hash = hash('sha256', $content);

        // Calculate size
        $size = strlen($content);

        return new self(
            content: $content,
            frontmatter: $frontmatter,
            hash: $hash,
            size: $size,
            modifiedAt: new DateTimeImmutable
        );
    }

    /**
     * Convert ContentData back to markdown string with frontmatter
     *
     * @return string Full markdown with YAML frontmatter
     */
    public function toMarkdown(): string
    {
        // If no frontmatter, return content as-is
        if (empty($this->frontmatter)) {
            return $this->content;
        }

        // Build YAML frontmatter
        $yaml = $this->buildYaml($this->frontmatter);

        return "---\n{$yaml}\n---\n\n{$this->content}";
    }

    /**
     * Parse YAML string into associative array
     *
     * Simple YAML parser that handles:
     * - Key: value pairs
     * - Quoted strings (single and double)
     * - Numbers (integers and floats)
     * - Booleans (true/false)
     * - Null values
     * - Arrays (basic support)
     * - Comments (lines starting with #)
     *
     * @param string $yaml YAML content
     * @return array<string, mixed> Parsed data
     * @throws \Exception If YAML parsing fails
     */
    private static function parseYaml(string $yaml): array
    {
        $result = [];
        $lines = explode("\n", $yaml);
        $currentKey = null;
        $multilineValue = [];
        $inMultiline = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Skip empty lines and comments
            if (empty($trimmed) || str_starts_with($trimmed, '#')) {
                continue;
            }

            // Handle multiline values (indented lines)
            if ($inMultiline && (str_starts_with($line, '  ') || str_starts_with($line, "\t"))) {
                $multilineValue[] = trim($line);
                continue;
            }

            // Finalize previous multiline value
            if ($inMultiline && $currentKey !== null) {
                $result[$currentKey] = implode("\n", $multilineValue);
                $inMultiline = false;
                $multilineValue = [];
            }

            // Parse key: value pairs
            if (str_contains($trimmed, ':')) {
                [$key, $value] = explode(':', $trimmed, 2);
                $key = trim($key);
                $value = trim($value);

                // Handle empty values (start of multiline)
                if (empty($value)) {
                    $currentKey = $key;
                    $inMultiline = true;
                    $multilineValue = [];
                    continue;
                }

                // Parse the value
                $result[$key] = self::parseYamlValue($value);
                $currentKey = $key;
            }
        }

        // Finalize any remaining multiline value
        if ($inMultiline && $currentKey !== null) {
            $result[$currentKey] = implode("\n", $multilineValue);
        }

        return $result;
    }

    /**
     * Parse a YAML value into appropriate PHP type
     *
     * @param string $value Raw value string
     * @return mixed Parsed value
     */
    private static function parseYamlValue(string $value): mixed
    {
        $value = trim($value);

        // Remove quotes from strings
        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            return substr($value, 1, -1);
        }

        // Parse booleans
        if (strtolower($value) === 'true') {
            return true;
        }
        if (strtolower($value) === 'false') {
            return false;
        }

        // Parse null
        if (strtolower($value) === 'null' || strtolower($value) === '~') {
            return null;
        }

        // Parse numbers
        if (is_numeric($value)) {
            return str_contains($value, '.') ? (float) $value : (int) $value;
        }

        // Parse basic arrays [item1, item2, item3]
        if (str_starts_with($value, '[') && str_ends_with($value, ']')) {
            $arrayContent = substr($value, 1, -1);
            $items = explode(',', $arrayContent);

            return array_map(fn ($item) => self::parseYamlValue(trim($item)), $items);
        }

        // Return as string
        return $value;
    }

    /**
     * Build YAML string from associative array
     *
     * @param array<string, mixed> $data Data to convert
     * @param int $indent Indentation level
     * @return string YAML content
     */
    private function buildYaml(array $data, int $indent = 0): string
    {
        $yaml = [];
        $indentStr = str_repeat('  ', $indent);

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // Handle arrays
                if (empty($value)) {
                    $yaml[] = "{$indentStr}{$key}: []";
                } else {
                    $yaml[] = "{$indentStr}{$key}:";
                    foreach ($value as $item) {
                        if (is_scalar($item) || $item === null) {
                            $yaml[] = "{$indentStr}  - ".$this->formatYamlValue($item);
                        }
                    }
                }
            } else {
                // Handle scalar values
                $formattedValue = $this->formatYamlValue($value);
                $yaml[] = "{$indentStr}{$key}: {$formattedValue}";
            }
        }

        return implode("\n", $yaml);
    }

    /**
     * Format a value for YAML output
     *
     * @param mixed $value Value to format
     * @return string Formatted value
     */
    private function formatYamlValue(mixed $value): string
    {
        if ($value === null) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        if (is_string($value)) {
            // Quote strings that contain special characters, spaces, or reserved words
            if (preg_match('/[:\n\r\{\}\[\]&*#?|<>=!%@\\\\\s]/', $value) ||
                preg_match('/^\s|\s$/', $value) ||
                in_array(strtolower($value), ['true', 'false', 'null', 'yes', 'no', 'on', 'off'])) {
                return '"'.str_replace('"', '\\"', $value).'"';
            }

            return $value;
        }

        return (string) $value;
    }

    /**
     * Get frontmatter value by key
     *
     * @param string $key Frontmatter key
     * @param mixed $default Default value if key not found
     * @return mixed Value or default
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->frontmatter[$key] ?? $default;
    }

    /**
     * Check if frontmatter has a key
     *
     * @param string $key Frontmatter key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->frontmatter[$key]);
    }

    /**
     * Get the content hash
     *
     * If hash is not set, calculate it from content
     *
     * @return string SHA-256 hash
     */
    public function getHash(): string
    {
        return $this->hash ?? hash('sha256', $this->content);
    }

    /**
     * Get the content size
     *
     * If size is not set, calculate it from content
     *
     * @return int Size in bytes
     */
    public function getSize(): int
    {
        return $this->size ?? strlen($this->content);
    }

    /**
     * Create a copy with updated content
     *
     * @param string $content New content
     * @return self New instance with updated content
     */
    public function withContent(string $content): self
    {
        return new self(
            content: $content,
            frontmatter: $this->frontmatter,
            hash: hash('sha256', $content),
            size: strlen($content),
            modifiedAt: new DateTimeImmutable
        );
    }

    /**
     * Create a copy with updated frontmatter
     *
     * @param array<string, mixed> $frontmatter New frontmatter
     * @return self New instance with updated frontmatter
     */
    public function withFrontmatter(array $frontmatter): self
    {
        return new self(
            content: $this->content,
            frontmatter: $frontmatter,
            hash: $this->hash,
            size: $this->size,
            modifiedAt: new DateTimeImmutable
        );
    }

    /**
     * Create a copy with merged frontmatter
     *
     * @param array<string, mixed> $frontmatter Frontmatter to merge
     * @return self New instance with merged frontmatter
     */
    public function mergeFrontmatter(array $frontmatter): self
    {
        return new self(
            content: $this->content,
            frontmatter: array_merge($this->frontmatter, $frontmatter),
            hash: $this->hash,
            size: $this->size,
            modifiedAt: new DateTimeImmutable
        );
    }
}
