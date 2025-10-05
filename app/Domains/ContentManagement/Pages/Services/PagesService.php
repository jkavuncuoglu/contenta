<?php
namespace App\Domains\ContentManagement\Pages\Services;

use App\Domains\ContentManagement\Pages\Models\Page;

class PagesService implements PagesServiceContract
{
    public function create(array $data): Page
    {
        return Page::create($data);
    }

    public function save(Page $page): bool
    {
        return $page->save();
    }

    public function update(Page $page, array $data): bool
    {
        return $page->update($data);
    }

    public function delete(Page $page): bool
    {
        return $page->delete();
    }
}
