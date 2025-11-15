<?php

namespace App\Domains\ContentManagement\Tags\Services;

use App\Domains\ContentManagement\Tags\Models\Tag;
use Illuminate\Database\Eloquent\Collection;

interface TagServiceContract
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Tag;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Tag $tag, array $data): bool;

    public function delete(Tag $tag): bool;

    public function find(int $id): ?Tag;

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Tag>
     */
    public function list(array $filters = []): Collection;
}
