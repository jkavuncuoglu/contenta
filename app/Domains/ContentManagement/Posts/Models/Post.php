<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Posts\Models;

use App\Domains\ContentManagement\Categories\Models\Category;
use App\Domains\ContentManagement\ContentStorage\ContentStorageManager;
use App\Domains\ContentManagement\ContentStorage\Contracts\RevisionProviderInterface;
use App\Domains\ContentManagement\ContentStorage\Factories\RevisionProviderFactory;
use App\Domains\ContentManagement\ContentStorage\Models\ContentData as RepositoryContentData;
use App\Domains\ContentManagement\ContentStorage\ValueObjects\ContentData;
use App\Domains\ContentManagement\ContentStorage\ValueObjects\RevisionCollection;
use App\Domains\ContentManagement\Posts\Aggregates\PostAggregate;
use App\Domains\ContentManagement\Tags\Models\Tag;
use App\Domains\Security\UserManagement\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Post extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use LogsActivity;
    use SoftDeletes;

    /**
     * Create a new factory instance for the model
     */
    protected static function newFactory(): \Database\Factories\PostFactory
    {
        return \Database\Factories\PostFactory::new();
    }

    protected $fillable = [
        'title',
        'slug',
        'content_markdown',
        'content_html',
        'table_of_contents',
        'excerpt',
        'status',
        'author_id',
        'parent_id',
        'featured_image_id',
        'published_at',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'open_graph_title',
        'open_graph_description',
        'twitter_title',
        'twitter_description',
        'structured_data',
        'custom_fields',
        'view_count',
        'comment_count',
        'like_count',
        'reading_time_minutes',
        'language',
        'template',
        'version',
        // ContentStorage fields
        'storage_driver',
        'storage_path',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'custom_fields' => 'array',
        'structured_data' => 'array',
        'table_of_contents' => 'array',
        'view_count' => 'integer',
        'comment_count' => 'integer',
        'like_count' => 'integer',
        'reading_time_minutes' => 'integer',
        'version' => 'integer',
    ];

    /**
     * @var array<int, string>
     */
    protected static array $logAttributes = ['title', 'status', 'published_at'];

    protected static bool $logOnlyDirty = true;

    // Relationships
    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @return BelongsTo<Post, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'parent_id');
    }

    /**
     * @return HasMany<Post, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Post::class, 'parent_id');
    }

    /**
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'post_categories');
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'post_tags');
    }

    /**
     * @return HasMany<PostRevision, $this>
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(PostRevision::class);
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    // Domain methods
    public function toAggregate(): PostAggregate
    {
        return new PostAggregate(
            id: $this->id,
            title: $this->title,
            slug: $this->slug,
            contentMarkdown: $this->content_markdown,
            contentHtml: $this->content_html,
            status: $this->status,
            authorId: $this->author_id,
            publishedAt: $this->published_at,
            customFields: $this->custom_fields ?? [],
            version: $this->version
        );
    }

    public function updateFromAggregate(PostAggregate $aggregate): void
    {
        $this->update([
            'title' => $aggregate->getTitle(),
            'slug' => $aggregate->getSlug(),
            'content_markdown' => $aggregate->getContentMarkdown(),
            'content_html' => $aggregate->getContentHtml(),
            'status' => $aggregate->getStatus(),
            'version' => $aggregate->getVersion(),
            'custom_fields' => $aggregate->getCustomFields(),
        ]);
    }

    // Media collections
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_images')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

        $this->addMediaCollection('attachments')
            ->acceptsMimeTypes(['application/pdf', 'application/msword', 'text/plain']);
    }

    // Scopes
    /**
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    /**
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', 'scheduled')
            ->whereNotNull('published_at');
    }

    /**
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll();
    }

    // ContentStorage Integration

    /**
     * Get content from storage backend
     */
    public function getContent(): ?ContentData
    {
        // If using database storage, return from database fields
        if ($this->storage_driver === 'database' || $this->storage_driver === null) {
            return new ContentData(
                markdown: $this->content_markdown,
                html: $this->content_html,
                tableOfContents: $this->table_of_contents,
            );
        }

        // Otherwise, fetch from ContentStorage
        if (! $this->storage_path) {
            return null;
        }

        /** @var ContentStorageManager $storageManager */
        $storageManager = app(ContentStorageManager::class);

        try {
            $driver = $storageManager->driver($this->storage_driver);
            $rawContent = $driver->read($this->storage_path);
            $data = json_decode($rawContent, true);

            return new ContentData(
                markdown: $data['markdown'] ?? '',
                html: $data['html'] ?? null,
                tableOfContents: $data['table_of_contents'] ?? null,
            );
        } catch (\Exception $e) {
            \Log::error('Failed to read post content from storage', [
                'post_id' => $this->id,
                'storage_driver' => $this->storage_driver,
                'storage_path' => $this->storage_path,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Set content to storage backend
     *
     * @param  ContentData  $content  The content to store
     * @param  array<string, mixed>  $metadata  Optional metadata (e.g., commit_message for Git storage)
     */
    public function setContent(ContentData $content, array $metadata = []): void
    {
        // If using database storage, set database fields
        if ($this->storage_driver === 'database' || $this->storage_driver === null) {
            $this->content_markdown = $content->markdown;
            $this->content_html = $content->html;
            $this->table_of_contents = $content->tableOfContents;

            return;
        }

        // Otherwise, write to ContentStorage
        if (! $this->storage_path) {
            $this->storage_path = $this->generateStoragePath();
        }

        // For Git-based storage, ensure commit message is set
        if (in_array($this->storage_driver, ['github', 'gitlab', 'bitbucket'])) {
            if (! isset($metadata['commit_message'])) {
                $metadata['commit_message'] = "Update: {$this->title}";
            }
        }

        /** @var ContentStorageManager $storageManager */
        $storageManager = app(ContentStorageManager::class);

        try {
            $driver = $storageManager->driver($this->storage_driver);

            // Convert to repository ContentData format (markdown with frontmatter)
            $frontmatter = [];
            if ($content->html !== null) {
                $frontmatter['html'] = $content->html;
            }
            if ($content->tableOfContents !== null) {
                $frontmatter['table_of_contents'] = $content->tableOfContents;
            }

            // Add metadata (e.g., commit_message) to frontmatter for Git storage
            if (! empty($metadata)) {
                $frontmatter = array_merge($frontmatter, $metadata);
            }

            $repositoryContent = new RepositoryContentData(
                content: $content->markdown,
                frontmatter: $frontmatter
            );

            $driver->write($this->storage_path, $repositoryContent);

            // Clear database fields when using cloud storage
            $this->content_markdown = null;
            $this->content_html = null;
            $this->table_of_contents = null;

        } catch (\Exception $e) {
            \Log::error('Failed to write post content to storage', [
                'post_id' => $this->id,
                'storage_driver' => $this->storage_driver,
                'storage_path' => $this->storage_path,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Generate storage path for post content
     */
    public function generateStoragePath(): string
    {
        $date = $this->published_at ?? $this->created_at ?? now();
        $slug = $this->slug ?? \Illuminate\Support\Str::slug($this->title);

        return sprintf(
            'posts/%s/%s/%s.md',
            $date->format('Y'),
            $date->format('m'),
            $slug
        );
    }

    /**
     * Accessor: Get content_markdown from storage if needed
     */
    public function getContentMarkdownAttribute($value): ?string
    {
        // Return database value if using database storage
        if ($this->storage_driver === 'database' || $this->storage_driver === null) {
            return $value;
        }

        // Otherwise, fetch from ContentStorage
        $content = $this->getContent();

        return $content?->markdown;
    }

    /**
     * Accessor: Get content_html from storage if needed
     */
    public function getContentHtmlAttribute($value): ?string
    {
        // Return database value if using database storage
        if ($this->storage_driver === 'database' || $this->storage_driver === null) {
            return $value;
        }

        // Otherwise, fetch from ContentStorage
        $content = $this->getContent();

        return $content?->html;
    }

    /**
     * Accessor: Get table_of_contents from storage if needed
     */
    public function getTableOfContentsAttribute($value): ?array
    {
        // Return database value if using database storage
        if ($this->storage_driver === 'database' || $this->storage_driver === null) {
            return is_string($value) ? json_decode($value, true) : $value;
        }

        // Otherwise, fetch from ContentStorage
        $content = $this->getContent();

        return $content?->tableOfContents;
    }

    // Cloud-Native Revision System

    /**
     * Get revision provider for this post
     */
    public function getRevisionProvider(): RevisionProviderInterface
    {
        /** @var RevisionProviderFactory $factory */
        $factory = app(RevisionProviderFactory::class);

        return $factory->forModel($this);
    }

    /**
     * Get paginated revision history
     *
     * @param  int  $page  Page number (1-indexed)
     * @param  int  $perPage  Items per page (default: 10)
     */
    public function revisionHistory(int $page = 1, int $perPage = 10): RevisionCollection
    {
        if (! $this->storage_path) {
            // No cloud storage path, return empty collection
            return new RevisionCollection([], 0, $page, $perPage, false);
        }

        return $this->getRevisionProvider()->getRevisions($this->storage_path, $page, $perPage);
    }

    /**
     * Get a specific revision
     *
     * @param  string  $revisionId  Version ID, commit hash, or DB revision ID
     * @return \App\Domains\ContentManagement\ContentStorage\ValueObjects\Revision|null
     */
    public function getRevisionById(string $revisionId)
    {
        if (! $this->storage_path) {
            return null;
        }

        return $this->getRevisionProvider()->getRevision($this->storage_path, $revisionId);
    }

    /**
     * Restore a specific revision
     *
     * @param  string  $revisionId  Version ID or commit hash
     */
    public function restoreRevisionById(string $revisionId): bool
    {
        if (! $this->storage_path) {
            return false;
        }

        return $this->getRevisionProvider()->restoreRevision($this->storage_path, $revisionId);
    }

    /**
     * Check if this post's storage driver supports revisions
     */
    public function supportsRevisions(): bool
    {
        return $this->getRevisionProvider()->supportsRevisions();
    }
}
