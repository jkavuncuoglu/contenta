<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Posts\Events;

use Carbon\Carbon;

readonly class PostPublished
{
    public function __construct(
        public ?int $id,
        public string $title,
        public string $slug,
        public Carbon $publishedAt,
        public bool $wasScheduled = false
    ) {}
}
