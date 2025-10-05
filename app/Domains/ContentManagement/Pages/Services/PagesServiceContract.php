<?php
namespace App\Domains\ContentManagement\Pages\Services;

use App\Domains\ContentManagement\Pages\Models\Page;

interface PagesServiceContract
{
    public function create(array $data): Page;
    public function save(Page $page): bool;
    public function update(Page $page, array $data): bool;
    public function delete(Page $page): bool;
}

