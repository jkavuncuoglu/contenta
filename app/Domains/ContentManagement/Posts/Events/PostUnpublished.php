<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Posts\Events;

use Carbon\Carbon;

readonly class PostUnpublished
{
    public function __construct(
        public ?int $id,
        public string $title,
        public string $slug,
        public ?Carbon $previousPublishedAt
    ) {}
}
