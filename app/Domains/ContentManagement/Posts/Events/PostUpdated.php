<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Events;

readonly class PostUpdated
{
    public function __construct(
        public ?int $id,
        public string $oldTitle,
        public string $newTitle,
        public string $oldSlug,
        public string $newSlug,
        public int $version
    ) {}
}
