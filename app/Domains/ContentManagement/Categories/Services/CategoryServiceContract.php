<?php

namespace App\Domains\ContentManagement\Categories\Services;

use App\Domains\ContentManagement\Categories\Models\Category;
use Illuminate\Database\Eloquent\Collection;

interface CategoryServiceContract
{
    /**
     * @return Collection<int, Category>
     */
    public function getAllCategories(): Collection;

    public function findCategoryById(int $id): ?Category;

    /**
     * @param  array<string, mixed>  $data
     */
    public function createCategory(array $data): Category;

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateCategory(int $id, array $data): Category;

    public function deleteCategory(int $id): ?bool;
}
