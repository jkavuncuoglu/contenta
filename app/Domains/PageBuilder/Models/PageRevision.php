<?php

declare(strict_types=1);

namespace App\Domains\PageBuilder\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @use HasFactory<\Database\Factories\PageBuilder\PageRevisionFactory>
 */
class PageRevision extends Model
{
    use HasFactory;

    protected $table = 'pagebuilder_page_revisions';

    protected $fillable = [
        'page_id',
        'user_id',
        'title',
        'slug',
        'layout_id',
        'data',
        'status',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'schema_data',
        'reason',
        'revision_number',
    ];

    protected $casts = [
        'data' => 'array',
        'schema_data' => 'array',
    ];

    /**
     * Get the page that owns the revision
     *
     * @return BelongsTo<Page, $this>
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * Get the user who created the revision
     *
     * @return BelongsTo<\App\Domains\Security\UserManagement\Models\User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\Security\UserManagement\Models\User::class);
    }

    /**
     * Get the layout for this revision
     *
     * @return BelongsTo<Layout, $this>
     */
    public function layout(): BelongsTo
    {
        return $this->belongsTo(Layout::class);
    }
}
