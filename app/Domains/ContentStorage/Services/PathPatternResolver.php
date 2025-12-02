<?php

declare(strict_types=1);

namespace App\Domains\ContentStorage\Services;

use App\Domains\ContentManagement\Posts\Models\Post;
use App\Domains\ContentStorage\Exceptions\WriteException;
use App\Domains\PageBuilder\Models\Page;
use Illuminate\Database\Eloquent\Model;

/**
 * Path Pattern Resolver
 *
 * Resolves path patterns with tokens to actual file paths.
 * Supports tokens like {type}, {id}, {slug}, {year}, {month}, {day}, {author_id}, {status}.
 *
 * Example patterns:
 * - pages/{slug}.md → pages/about-us.md
 * - posts/{year}/{month}/{slug}.md → posts/2025/12/hello-world.md
 * - {type}/{status}/{slug}.md → posts/published/hello-world.md
 */
class PathPatternResolver
{
    /**
     * Resolve path pattern to actual file path
     *
     * @param string $pattern Path pattern with tokens
     * @param string $contentType Content type (pages|posts)
     * @param Model $model Page or Post model
     * @return string Resolved file path
     * @throws WriteException If path is invalid or unsafe
     */
    public function resolve(string $pattern, string $contentType, Model $model): string
    {
        // Build token map from model
        $tokens = $this->buildTokenMap($contentType, $model);

        // Replace tokens in pattern
        $path = $this->replaceTokens($pattern, $tokens);

        // Validate path safety
        $this->validatePath($path);

        // Ensure .md extension
        if (! str_ends_with($path, '.md')) {
            $path .= '.md';
        }

        return $path;
    }

    /**
     * Get directory path from full file path
     *
     * @param string $path Full file path
     * @return string Directory path (without filename)
     */
    public function getDirectory(string $path): string
    {
        return dirname($path);
    }

    /**
     * Check if pattern contains a specific token
     *
     * @param string $pattern Path pattern
     * @param string $token Token name (without braces)
     * @return bool
     */
    public function hasToken(string $pattern, string $token): bool
    {
        return str_contains($pattern, '{'.$token.'}');
    }

    /**
     * Get all tokens used in a pattern
     *
     * @param string $pattern Path pattern
     * @return array<int, string> Array of token names (without braces)
     */
    public function getTokens(string $pattern): array
    {
        preg_match_all('/\{([a-z_]+)\}/', $pattern, $matches);

        return $matches[1] ?? [];
    }

    /**
     * Build token map from content type and model
     *
     * @param string $contentType Content type (pages|posts)
     * @param Model $model Page or Post model
     * @return array<string, string> Token map
     */
    private function buildTokenMap(string $contentType, Model $model): array
    {
        $tokens = [
            'type' => $contentType,
            'id' => (string) $model->id,
            'slug' => $model->slug ?? 'untitled',
            'status' => $model->status ?? 'draft',
        ];

        // Add author_id if available
        if (isset($model->author_id)) {
            $tokens['author_id'] = (string) $model->author_id;
        }

        // Add date tokens for posts or pages with published_at
        if ($model instanceof Post && $model->published_at) {
            $tokens['year'] = $model->published_at->format('Y');
            $tokens['month'] = $model->published_at->format('m');
            $tokens['day'] = $model->published_at->format('d');
        } elseif (isset($model->created_at)) {
            // Fallback to created_at for pages
            $tokens['year'] = $model->created_at->format('Y');
            $tokens['month'] = $model->created_at->format('m');
            $tokens['day'] = $model->created_at->format('d');
        } else {
            // Default to current date if no dates available
            $now = now();
            $tokens['year'] = $now->format('Y');
            $tokens['month'] = $now->format('m');
            $tokens['day'] = $now->format('d');
        }

        return $tokens;
    }

    /**
     * Replace tokens in pattern with values
     *
     * @param string $pattern Path pattern
     * @param array<string, string> $tokens Token map
     * @return string Path with tokens replaced
     */
    private function replaceTokens(string $pattern, array $tokens): string
    {
        $path = $pattern;

        foreach ($tokens as $token => $value) {
            $path = str_replace('{'.$token.'}', $value, $path);
        }

        // Check for any remaining unreplaced tokens
        if (preg_match('/\{[a-z_]+\}/', $path, $matches)) {
            // Log warning about unreplaced token
            \Log::warning("Path pattern contains unreplaced token: {$matches[0]}");
        }

        return $path;
    }

    /**
     * Validate path for security and correctness
     *
     * @param string $path File path to validate
     * @throws WriteException If path is invalid
     */
    private function validatePath(string $path): void
    {
        // Check for directory traversal attempts
        if (str_contains($path, '..')) {
            throw WriteException::invalidPath($path, 'Directory traversal not allowed');
        }

        // Check for absolute paths (should be relative)
        if (str_starts_with($path, '/') || preg_match('/^[a-zA-Z]:/', $path)) {
            throw WriteException::invalidPath($path, 'Absolute paths not allowed');
        }

        // Check for dangerous characters
        if (preg_match('/[<>:"|?*\x00-\x1F]/', $path)) {
            throw WriteException::invalidPath($path, 'Path contains invalid characters');
        }

        // Check path length (255 chars is typical filesystem limit)
        if (strlen($path) > 255) {
            throw WriteException::invalidPath($path, 'Path too long (max 255 characters)');
        }

        // Check for empty path
        if (empty(trim($path))) {
            throw WriteException::invalidPath($path, 'Path cannot be empty');
        }
    }

    /**
     * Sanitize a path component (filename or directory name)
     *
     * @param string $component Path component to sanitize
     * @return string Sanitized component
     */
    public function sanitizeComponent(string $component): string
    {
        // Remove or replace dangerous characters
        $sanitized = preg_replace('/[<>:"|?*\x00-\x1F]/', '', $component);

        // Replace spaces with hyphens
        $sanitized = str_replace(' ', '-', $sanitized);

        // Remove consecutive hyphens
        $sanitized = preg_replace('/-+/', '-', $sanitized);

        // Remove leading/trailing hyphens and dots
        $sanitized = trim($sanitized, '-.');

        // Ensure not empty after sanitization
        if (empty($sanitized)) {
            $sanitized = 'untitled';
        }

        return $sanitized;
    }

    /**
     * Build default pattern for content type
     *
     * @param string $contentType Content type (pages|posts)
     * @return string Default pattern
     */
    public static function getDefaultPattern(string $contentType): string
    {
        return match ($contentType) {
            'pages' => 'pages/{slug}.md',
            'posts' => 'posts/{year}/{month}/{slug}.md',
            default => '{type}/{slug}.md',
        };
    }

    /**
     * Get available tokens with descriptions
     *
     * @return array<string, string> Token name => description
     */
    public static function getAvailableTokens(): array
    {
        return [
            'type' => 'Content type (pages/posts)',
            'id' => 'Model ID',
            'slug' => 'URL slug',
            'year' => 'Publish year (YYYY)',
            'month' => 'Publish month (MM)',
            'day' => 'Publish day (DD)',
            'author_id' => 'Author ID',
            'status' => 'Content status (draft/published/archived)',
        ];
    }

    /**
     * Preview path pattern with sample data
     *
     * @param string $pattern Path pattern
     * @param string $contentType Content type
     * @return string Preview path
     */
    public static function preview(string $pattern, string $contentType): string
    {
        // Sample tokens
        $tokens = [
            'type' => $contentType,
            'id' => '123',
            'slug' => 'example-post',
            'year' => '2025',
            'month' => '12',
            'day' => '02',
            'author_id' => '1',
            'status' => 'published',
        ];

        $path = $pattern;
        foreach ($tokens as $token => $value) {
            $path = str_replace('{'.$token.'}', $value, $path);
        }

        if (! str_ends_with($path, '.md')) {
            $path .= '.md';
        }

        return $path;
    }
}
