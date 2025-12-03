<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Pages\Models;

use App\Domains\ContentManagement\ContentStorage\Models\ContentData;
use App\Domains\ContentManagement\ContentStorage\Services\ContentStorageManager;
use App\Domains\ContentManagement\ContentStorage\Factories\RevisionProviderFactory;
use App\Domains\ContentManagement\ContentStorage\Contracts\RevisionProviderInterface;
use App\Domains\ContentManagement\ContentStorage\ValueObjects\RevisionCollection;
use App\Domains\Security\UserManagement\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Page Model
 *
 * Represents a static page in the CMS using ContentStorage backend.
 * Content is stored via configurable storage drivers (database, local, S3, GitHub, etc.)
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $storage_driver
 * @property string|null $storage_path
 * @property string $status
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_keywords
 * @property array|null $schema_data
 * @property int|null $author_id
 * @property \Carbon\Carbon|null $published_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @use HasFactory<\Database\Factories\PageFactory>
 */
class Page extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'pages';

    /**
     * Create a new factory instance for the model
     */
    protected static function newFactory(): \Database\Factories\PageFactory
    {
        return \Database\Factories\PageFactory::new();
    }

    protected $fillable = [
        'title',
        'slug',
        'storage_driver',
        'storage_path',
        'status',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'schema_data',
        'author_id',
        'published_at',
        'parent_id',
        'template',
    ];

    protected $casts = [
        'schema_data' => 'array',
        'published_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'draft',
        'storage_driver' => 'database',
    ];

    /**
     * Boot the model
     */
    protected static function booted(): void
    {
        static::observe(\App\Domains\ContentManagement\Pages\Observers\PageObserver::class);
    }

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_ARCHIVED = 'archived';

    /**
     * Get the page content from storage
     */
    public function getContentAttribute(): ?ContentData
    {
        if (!$this->storage_path) {
            return null;
        }

        try {
            $storage = app(ContentStorageManager::class)->driver($this->storage_driver);
            return $storage->read($this->storage_path);
        } catch (\Exception $e) {
            \Log::error('Failed to read page content', [
                'page_id' => $this->id,
                'storage_driver' => $this->storage_driver,
                'storage_path' => $this->storage_path,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Set the page content to storage
     */
    public function setContentAttribute(ContentData $content): void
    {
        if (!$this->storage_path) {
            // Generate storage path on first save
            $this->storage_path = $this->generateStoragePath();
        }

        $storage = app(ContentStorageManager::class)->driver($this->storage_driver);
        $storage->write($this->storage_path, $content);
    }

    /**
     * Generate storage path for this page
     */
    protected function generateStoragePath(): string
    {
        // Pattern: pages/{slug}.md
        // Example: pages/about-us.md
        // Nested: pages/company/team.md (if parent_id exists)

        if ($this->parent_id) {
            $parent = static::find($this->parent_id);
            if ($parent && $parent->storage_path) {
                $parentPath = dirname($parent->storage_path);
                return "{$parentPath}/{$this->slug}.md";
            }
        }

        return "pages/{$this->slug}.md";
    }

    /**
     * Get the author of the page
     *
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the parent page
     *
     * @return BelongsTo<Page, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    /**
     * Get child pages
     *
     * @return HasMany<Page, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Page::class, 'parent_id');
    }

    /**
     * Get the revisions for the page
     *
     * @return HasMany<PageRevision, $this>
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(PageRevision::class)->orderBy('revision_number', 'desc');
    }

    /**
     * Scope for published pages
     *
     * @param Builder<Page> $query
     * @return Builder<Page>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /**
     * Scope for draft pages
     *
     * @param Builder<Page> $query
     * @return Builder<Page>
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Scope for archived pages
     *
     * @param Builder<Page> $query
     * @return Builder<Page>
     */
    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ARCHIVED);
    }

    /**
     * Get activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'slug', 'status', 'storage_driver'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Publish the page
     */
    public function publish(): bool
    {
        $this->status = self::STATUS_PUBLISHED;
        $this->published_at = now();
        return $this->save();
    }

    /**
     * Unpublish the page
     */
    public function unpublish(): bool
    {
        $this->status = self::STATUS_DRAFT;
        return $this->save();
    }

    /**
     * Archive the page
     */
    public function archive(): bool
    {
        $this->status = self::STATUS_ARCHIVED;
        return $this->save();
    }

    // Cloud-Native Revision System

    /**
     * Get revision provider for this page
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
     * @param int $page Page number (1-indexed)
     * @param int $perPage Items per page (default: 10)
     * @return RevisionCollection
     */
    public function revisionHistory(int $page = 1, int $perPage = 10): RevisionCollection
    {
        if (!$this->storage_path) {
            // No cloud storage path, return empty collection
            return new RevisionCollection([], 0, $page, $perPage, false);
        }

        return $this->getRevisionProvider()->getRevisions($this->storage_path, $page, $perPage);
    }

    /**
     * Get a specific revision
     *
     * @param string $revisionId Version ID, commit hash, or DB revision ID
     * @return \App\Domains\ContentManagement\ContentStorage\ValueObjects\Revision|null
     */
    public function getRevisionById(string $revisionId)
    {
        if (!$this->storage_path) {
            return null;
        }

        return $this->getRevisionProvider()->getRevision($this->storage_path, $revisionId);
    }

    /**
     * Restore a specific revision
     *
     * @param string $revisionId Version ID or commit hash
     * @return bool
     */
    public function restoreRevisionById(string $revisionId): bool
    {
        if (!$this->storage_path) {
            return false;
        }

        return $this->getRevisionProvider()->restoreRevision($this->storage_path, $revisionId);
    }

    /**
     * Check if this page's storage driver supports revisions
     */
    public function supportsRevisions(): bool
    {
        return $this->getRevisionProvider()->supportsRevisions();
    }
}
