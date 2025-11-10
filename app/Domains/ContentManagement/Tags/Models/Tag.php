<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Tags\Models;

use Database\Factories\TagFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $color
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read int|null $posts_count
 *
 * @use HasFactory<TagFactory>
 */
class Tag extends Model
{
    /** @use HasFactory<TagFactory> */
    use HasFactory;
    use SoftDeletes;
    use LogsActivity;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'meta_title',
        'meta_description',
    ];

    // Relationships
    /**
     * @return BelongsToMany<\App\Domains\ContentManagement\Posts\Models\Post, $this>
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(\App\Domains\ContentManagement\Posts\Models\Post::class, 'post_tags');
    }

    // Scopes
    /**
     * @param Builder<Tag> $query
     * @return Builder<Tag>
     */
    public function scopePopular(Builder $query, int $limit = 10): Builder
    {
        return $query->withCount('posts')
            ->orderBy('posts_count', 'desc')
            ->limit($limit);
    }

    /**
     * @param Builder<Tag> $query
     * @return Builder<Tag>
     */
    public function scopeWithPostCount(Builder $query): Builder
    {
        return $query->withCount('posts');
    }

    // Activity logging
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'slug'])
            ->logOnlyDirty();
    }
}
