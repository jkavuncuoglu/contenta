<?php

declare(strict_types=1);

namespace App\Domains\ContentStorage\Repositories;

use App\Domains\ContentManagement\Posts\Models\Post;
use App\Domains\ContentStorage\Contracts\ContentRepositoryContract;
use App\Domains\ContentStorage\Exceptions\ReadException;
use App\Domains\ContentStorage\Exceptions\WriteException;
use App\Domains\ContentStorage\Models\ContentData;
use App\Domains\PageBuilder\Models\Page;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Database Repository
 *
 * Maintains backward compatibility with current database storage.
 * Stores content in `pages.markdown_content` or `posts.content_markdown` columns.
 * Frontmatter is dynamically built from model attributes.
 */
class DatabaseRepository implements ContentRepositoryContract
{
    /**
     * Content type (pages or posts)
     */
    private string $contentType;

    /**
     * Create a new database repository
     *
     * @param string $contentType Content type (pages|posts)
     */
    public function __construct(string $contentType = 'pages')
    {
        $this->contentType = $contentType;
    }

    /**
     * {@inheritdoc}
     */
    public function read(string $path): ContentData
    {
        // Extract ID from path (e.g., "pages/123" -> 123)
        $id = $this->extractIdFromPath($path);

        // If no valid ID found in path, this is not a database path
        if ($id === 0) {
            throw ReadException::notFound($path);
        }

        // Find the model
        $model = $this->findModel($id);

        if (! $model) {
            throw ReadException::notFound($path);
        }

        // Get markdown content from model
        $content = $this->getMarkdownContent($model);

        // Build frontmatter from model attributes
        $frontmatter = $this->buildFrontmatter($model);

        // Calculate hash and size
        $hash = hash('sha256', $content);
        $size = strlen($content);

        // Get modified timestamp
        $modifiedAt = $model->updated_at ?
            DateTimeImmutable::createFromMutable($model->updated_at) :
            new DateTimeImmutable;

        return new ContentData(
            content: $content,
            frontmatter: $frontmatter,
            hash: $hash,
            size: $size,
            modifiedAt: $modifiedAt
        );
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $path, ContentData $data): bool
    {
        try {
            DB::beginTransaction();

            // Try to extract ID from path
            $id = $this->extractIdFromPath($path);

            // Find or create the model
            $model = $this->findModel($id);

            if (! $model) {
                // Create new model from frontmatter
                $model = $this->createModelFromData($data);
            }

            // Update markdown content
            $this->setMarkdownContent($model, $data->content);

            // Update model attributes from frontmatter
            $this->updateModelFromFrontmatter($model, $data->frontmatter);

            // Save the model
            $model->save();

            DB::commit();

            Log::info("Content written to database", [
                'driver' => 'database',
                'content_type' => $this->contentType,
                'id' => $model->id ?? $id,
                'size' => $data->getSize(),
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to write content to database", [
                'driver' => 'database',
                'content_type' => $this->contentType,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            throw WriteException::networkFailure('database', $e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function exists(string $path): bool
    {
        try {
            $id = $this->extractIdFromPath($path);
            $model = $this->findModel($id);

            return $model !== null;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $path): bool
    {
        try {
            $id = $this->extractIdFromPath($path);
            $model = $this->findModel($id);

            if (! $model) {
                throw WriteException::invalidPath($path, 'Model not found');
            }

            // Soft delete the model
            $model->delete();

            Log::info("Content deleted from database", [
                'driver' => 'database',
                'content_type' => $this->contentType,
                'id' => $id,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to delete content from database", [
                'driver' => 'database',
                'content_type' => $this->contentType,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            throw new WriteException("Failed to delete content: {$e->getMessage()}");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function list(string $directory = ''): array
    {
        $query = $this->contentType === 'pages'
            ? Page::query()
            : Post::query();

        $models = $query->get();

        return $models->map(function ($model) {
            return $this->buildPath($model->id);
        })->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function testConnection(): bool
    {
        try {
            // Test database connection
            DB::connection()->getPdo();

            // Test table existence
            $table = $this->contentType === 'pages' ? 'pages' : 'posts';
            $exists = DB::getSchemaBuilder()->hasTable($table);

            return $exists;
        } catch (\Exception $e) {
            Log::error("Database connection test failed", [
                'driver' => 'database',
                'content_type' => $this->contentType,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDriverName(): string
    {
        return 'database';
    }

    /**
     * Extract ID from path
     *
     * Path format: "pages/123" or "posts/456"
     * For paths without IDs (like "posts/2025/12/slug.md"), returns 0
     *
     * @param string $path Path string
     * @return int Model ID or 0 if no ID found
     */
    private function extractIdFromPath(string $path): int
    {
        // Remove .md extension if present
        $path = preg_replace('/\.md$/', '', $path);

        $parts = explode('/', $path);
        $lastPart = end($parts);

        // Try to extract numeric ID from last part
        $id = (int) $lastPart;

        // Return 0 if no valid numeric ID found
        return ($id > 0) ? $id : 0;
    }

    /**
     * Build path from model ID
     *
     * @param int $id Model ID
     * @return string Path (e.g., "pages/123")
     */
    private function buildPath(int $id): string
    {
        return "{$this->contentType}/{$id}";
    }

    /**
     * Find model by ID
     *
     * @param int $id Model ID
     * @return Model|null
     */
    private function findModel(int $id): ?Model
    {
        if ($this->contentType === 'pages') {
            return Page::find($id);
        }

        return Post::find($id);
    }

    /**
     * Create new model from ContentData
     *
     * @param ContentData $data Content data with frontmatter
     * @return Model New model instance
     */
    private function createModelFromData(ContentData $data): Model
    {
        if ($this->contentType === 'pages') {
            $model = new Page;
        } else {
            $model = new Post;
            // Posts require author_id - use first user or create placeholder
            $model->author_id = $data->get('author_id', 1);
        }

        // Set required fields from frontmatter
        $model->slug = $data->get('slug', 'untitled-'.time());
        $model->title = $data->get('title', 'Untitled');
        $model->status = $data->get('status', 'draft');

        return $model;
    }

    /**
     * Get markdown content from model
     *
     * @param Model $model Page or Post model
     * @return string Markdown content
     */
    private function getMarkdownContent(Model $model): string
    {
        if ($model instanceof Page) {
            return $model->markdown_content ?? '';
        }

        if ($model instanceof Post) {
            return $model->content_markdown ?? '';
        }

        return '';
    }

    /**
     * Set markdown content on model
     *
     * @param Model $model Page or Post model
     * @param string $content Markdown content
     */
    private function setMarkdownContent(Model $model, string $content): void
    {
        if ($model instanceof Page) {
            $model->markdown_content = $content;
        } elseif ($model instanceof Post) {
            $model->content_markdown = $content;
        }
    }

    /**
     * Build frontmatter from model attributes
     *
     * Creates YAML frontmatter compatible with ContentData
     *
     * @param Model $model Page or Post model
     * @return array<string, mixed> Frontmatter data
     */
    private function buildFrontmatter(Model $model): array
    {
        if ($model instanceof Page) {
            return $this->buildPageFrontmatter($model);
        }

        if ($model instanceof Post) {
            return $this->buildPostFrontmatter($model);
        }

        return [];
    }

    /**
     * Build frontmatter for Page model
     *
     * @param Page $model
     * @return array<string, mixed>
     */
    private function buildPageFrontmatter(Page $model): array
    {
        $frontmatter = [
            'title' => $model->title,
            'slug' => $model->slug,
            'status' => $model->status,
            'content_type' => $model->content_type,
        ];

        if ($model->author_id) {
            $frontmatter['author_id'] = $model->author_id;
        }

        if ($model->layout_template) {
            $frontmatter['layout_template'] = $model->layout_template;
        }

        // SEO metadata
        if ($model->meta_title) {
            $frontmatter['meta_title'] = $model->meta_title;
        }

        if ($model->meta_description) {
            $frontmatter['meta_description'] = $model->meta_description;
        }

        if ($model->meta_keywords) {
            $frontmatter['meta_keywords'] = $model->meta_keywords;
        }

        return $frontmatter;
    }

    /**
     * Build frontmatter for Post model
     *
     * @param Post $model
     * @return array<string, mixed>
     */
    private function buildPostFrontmatter(Post $model): array
    {
        $frontmatter = [
            'title' => $model->title,
            'slug' => $model->slug,
            'status' => $model->status,
        ];

        if ($model->author_id) {
            $frontmatter['author_id'] = $model->author_id;
        }

        if ($model->published_at) {
            $frontmatter['published_at'] = $model->published_at->toIso8601String();
        }

        if ($model->excerpt) {
            $frontmatter['excerpt'] = $model->excerpt;
        }

        // SEO metadata
        if ($model->meta_title) {
            $frontmatter['meta_title'] = $model->meta_title;
        }

        if ($model->meta_description) {
            $frontmatter['meta_description'] = $model->meta_description;
        }

        if ($model->meta_keywords) {
            $frontmatter['meta_keywords'] = $model->meta_keywords;
        }

        // Additional post-specific fields
        if ($model->template) {
            $frontmatter['template'] = $model->template;
        }

        if ($model->language) {
            $frontmatter['language'] = $model->language;
        }

        return $frontmatter;
    }

    /**
     * Update model attributes from frontmatter
     *
     * @param Model $model Page or Post model
     * @param array<string, mixed> $frontmatter Frontmatter data
     */
    private function updateModelFromFrontmatter(Model $model, array $frontmatter): void
    {
        if ($model instanceof Page) {
            $this->updatePageFromFrontmatter($model, $frontmatter);
        } elseif ($model instanceof Post) {
            $this->updatePostFromFrontmatter($model, $frontmatter);
        }
    }

    /**
     * Update Page model from frontmatter
     *
     * @param Page $model
     * @param array<string, mixed> $frontmatter
     */
    private function updatePageFromFrontmatter(Page $model, array $frontmatter): void
    {
        if (isset($frontmatter['title'])) {
            $model->title = $frontmatter['title'];
        }

        if (isset($frontmatter['slug'])) {
            $model->slug = $frontmatter['slug'];
        }

        if (isset($frontmatter['status'])) {
            $model->status = $frontmatter['status'];
        }

        if (isset($frontmatter['layout_template'])) {
            $model->layout_template = $frontmatter['layout_template'];
        }

        // SEO metadata
        if (isset($frontmatter['meta_title'])) {
            $model->meta_title = $frontmatter['meta_title'];
        }

        if (isset($frontmatter['meta_description'])) {
            $model->meta_description = $frontmatter['meta_description'];
        }

        if (isset($frontmatter['meta_keywords'])) {
            $model->meta_keywords = $frontmatter['meta_keywords'];
        }
    }

    /**
     * Update Post model from frontmatter
     *
     * @param Post $model
     * @param array<string, mixed> $frontmatter
     */
    private function updatePostFromFrontmatter(Post $model, array $frontmatter): void
    {
        if (isset($frontmatter['title'])) {
            $model->title = $frontmatter['title'];
        }

        if (isset($frontmatter['slug'])) {
            $model->slug = $frontmatter['slug'];
        }

        if (isset($frontmatter['status'])) {
            $model->status = $frontmatter['status'];
        }

        if (isset($frontmatter['published_at'])) {
            $model->published_at = new \DateTime($frontmatter['published_at']);
        }

        if (isset($frontmatter['excerpt'])) {
            $model->excerpt = $frontmatter['excerpt'];
        }

        // SEO metadata
        if (isset($frontmatter['meta_title'])) {
            $model->meta_title = $frontmatter['meta_title'];
        }

        if (isset($frontmatter['meta_description'])) {
            $model->meta_description = $frontmatter['meta_description'];
        }

        if (isset($frontmatter['meta_keywords'])) {
            $model->meta_keywords = $frontmatter['meta_keywords'];
        }

        // Additional fields
        if (isset($frontmatter['template'])) {
            $model->template = $frontmatter['template'];
        }

        if (isset($frontmatter['language'])) {
            $model->language = $frontmatter['language'];
        }
    }
}
