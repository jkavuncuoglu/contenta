<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Pages\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Page Revision Model
 *
 * Stores historical versions of pages for revision tracking.
 */
class PageRevision extends Model
{
    protected $table = 'page_revisions';

    protected $fillable = [
        'page_id',
        'revision_number',
        'title',
        'slug',
        'markdown_content',
        'storage_driver',
        'storage_path',
        'meta_title',
        'meta_description',
        'created_by',
    ];

    protected $casts = [
        'revision_number' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Get the page that owns this revision
     *
     * @return BelongsTo<Page, $this>
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * Get the user who created this revision
     *
     * @return BelongsTo<\App\Domains\Security\UserManagement\Models\User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\Security\UserManagement\Models\User::class, 'created_by');
    }
}
