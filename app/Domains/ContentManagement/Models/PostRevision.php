<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Models;

use app\Domains\ContentManagement\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostRevision extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'title',
        'content_markdown',
        'content_html',
        'version',
        'author_id',
    ];

    protected $casts = [
        'version' => 'integer',
    ];

    // Relationships
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
