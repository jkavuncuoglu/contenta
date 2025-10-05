<?php
namespace App\Domains\ContentManagement\Tags\Services;

use App\Domains\ContentManagement\Tags\Models\Tag;

interface TagServiceContract
{
    public function create(array $data): Tag;
    public function update(Tag $tag, array $data): bool;
    public function delete(Tag $tag): bool;
    public function find(int $id): ?Tag;
    public function list(array $filters = []): iterable;
}
