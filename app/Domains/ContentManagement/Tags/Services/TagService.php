<?php
namespace App\Domains\ContentManagement\Tags\Services;

use App\Domains\ContentManagement\Tags\Models\Tag;

class TagService implements TagServiceContract
{
    public function create(array $data): Tag
    {
        return Tag::create($data);
    }

    public function update(Tag $tag, array $data): bool
    {
        return $tag->update($data);
    }

    public function delete(Tag $tag): bool
    {
        return $tag->delete();
    }

    public function find(int $id): ?Tag
    {
        return Tag::find($id);
    }

    public function list(array $filters = []): iterable
    {
        $query = Tag::query();
        // Add filter logic here if needed
        return $query->get();
    }
}
