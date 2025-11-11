<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Posts\Events;

use Carbon\Carbon;

readonly class PostCreated
{
    public function __construct(
        public string $title,
        public string $slug,
        public int $authorId,
        public Carbon $createdAt = new Carbon()
    ) {}
}
