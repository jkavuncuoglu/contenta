<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Posts\Models;

use App\Domains\ContentManagement\Posts\Aggregates\PostAggregate;
use App\Domains\ContentManagement\Posts\Models\Category;
use App\Domains\ContentManagement\Posts\Models\Comment;
use App\Domains\ContentManagement\Posts\Models\PostRevision;
use App\Domains\ContentManagement\Posts\Models\PostType;
use App\Domains\ContentManagement\Posts\Models\Tag;
use App\Models\User;
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
    use SoftDeletes;
    use InteractsWithMedia;
    use LogsActivity;

    protected $fillable = [
        'title',
        'slug',
        'content_markdown',
        'content_html',
        'excerpt',
        'status',
        'post_type_id',
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
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'custom_fields' => 'array',
        'structured_data' => 'array',
        'view_count' => 'integer',
        'comment_count' => 'integer',
        'like_count' => 'integer',
        'reading_time_minutes' => 'integer',
        'version' => 'integer',
    ];

    protected static array $logAttributes = ['title', 'status', 'published_at'];
    protected static bool $logOnlyDirty = true;

    // Relationships
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function postType(): BelongsTo
    {
        return $this->belongsTo(PostType::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Post::class, 'parent_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'post_categories');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'post_tags');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(PostRevision::class);
    }

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
            postTypeId: $this->post_type_id,
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
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeByType($query, string $postType)
    {
        return $query->whereHas('postType', function ($q) use ($postType) {
            $q->where('name', $postType);
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll();
    }
}
