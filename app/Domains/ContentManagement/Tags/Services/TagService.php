<?php

namespace App\Domains\ContentManagement\Tags\Services;

use App\Domains\ContentManagement\Tags\Models\Tag;
use Illuminate\Database\Eloquent\Collection;

class TagService implements TagServiceContract
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Tag
    {
        return Tag::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Tag $tag, array $data): bool
    {
        return $tag->update($data);
    }

    public function delete(Tag $tag): bool
    {
        return (bool) $tag->delete();
    }

    public function find(int $id): ?Tag
    {
        return Tag::find($id);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Tag>
     */
    public function list(array $filters = []): Collection
    {
        $query = Tag::query();

        // Add filter logic here if needed
        return $query->get();
    }
}
