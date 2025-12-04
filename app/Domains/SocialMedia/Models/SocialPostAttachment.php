<?php

declare(strict_types=1);

namespace App\Domains\SocialMedia\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SocialPostAttachment extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'social_post_id',
        'media_id',
        'order',
        'created_at',
    ];

    protected $casts = [
        'order' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Get the social post.
     */
    public function socialPost(): BelongsTo
    {
        return $this->belongsTo(SocialPost::class);
    }

    /**
     * Get the media.
     */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    /**
     * Scope to order by order column.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
