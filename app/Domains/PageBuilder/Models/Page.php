<?php

declare(strict_types=1);

namespace App\Domains\PageBuilder\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @use HasFactory<\Database\Factories\PageBuilder\PageFactory>
 */
class Page extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'pagebuilder_pages';

    protected $fillable = [
        'title',
        'slug',
        'layout_id',
        'data',
        'published_html',
        'status',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'schema_data',
        'author_id',
    ];

    protected $casts = [
        'data' => 'array',
        'published_html' => 'string',
        'schema_data' => 'array',
    ];

    protected $attributes = [
        'status' => 'draft',
        'data' => '{"layout": "default", "sections": []}',
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';

    const STATUS_PUBLISHED = 'published';

    const STATUS_ARCHIVED = 'archived';

    /**
     * Get the layout that the page belongs to
     *
     * @return BelongsTo<Layout, $this>
     */
    public function layout(): BelongsTo
    {
        return $this->belongsTo(Layout::class);
    }

    /**
     * Get the author of the page
     *
     * @return BelongsTo<\App\Domains\Security\UserManagement\Models\User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\Security\UserManagement\Models\User::class, 'author_id');
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
     * @param  Builder<Page>  $query
     * @return Builder<Page>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /**
     * Scope for draft pages
     *
     * @param  Builder<Page>  $query
     * @return Builder<Page>
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Check if page is published
     */
    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    /**
     * Check if page is draft
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Get page URL
     */
    public function getUrlAttribute(): string
    {
        return url("/pages/{$this->slug}");
    }

    /**
     * Get available statuses
     *
     * @return array<string, string>
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PUBLISHED => 'Published',
            self::STATUS_ARCHIVED => 'Archived',
        ];
    }

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'slug', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
